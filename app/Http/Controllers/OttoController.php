<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use Session;
use Redirect;
use Image;
use Carbon\Carbon;
use ZipArchive;
use File;
use PDF;
use Rap2hpoutre\FastExcel\FastExcel;
use App\SimpleXLSX;
use GuzzleHttp\Client;
use DateTime;
use App\Models\Price;
use App\Models\Channel;
use App\Models\Product;
use App\Models\LagerStand;
use App\Models\OrderItem;

 

class OttoController extends Controller {
    
    public function getOrders(){
        $channels  = Channel::query()
                ->join('platform', 'channel.platformid', '=', 'platform.platformid')
                ->where('platform.platformtype','Otto')
                ->select('channel.*','platform.shortname as pShortName')
                ->get();
        foreach($channels as $channel){
        $client = new \GuzzleHttp\Client();
            try {
                $res = $client->post('https://api.otto.market/v1/token', [
                'form_params' => [
                            'username' => $channel->username,
                            'password' => $channel->password,
                            'grant_type' => 'password',
                            'client_id' =>$channel->client_id,
                        ]
                    ]);
        
                $res = json_decode($res->getBody()->getContents());
                if($res){
                   $channel->refresh_token = $res->refresh_token;
                   $channel->save();
                   echo 'Getting orders for '.$channel->shortname.'</br>';
                   $this->ottoOrders($res, $channel);
                }
                
                
                
            }
            catch (\GuzzleHttp\Exception\ClientException $e) {
                $response = $e->getResponse();
                $result =  json_decode($response->getBody()->getContents());
                return response()->json(['data' => $result]);
            }
        }
    }
    
    
    public function ottoOrders($response, $channel){
        $client = new \GuzzleHttp\Client();
        
        $args = [
                'fromOrderDate'=> date('Y-m-d', strtotime('-3 days')).'T01:00:00+02:00',
                'toOrderDate'=> date('Y-m-d'). 'T23:59:59+02:00',
            ];
        
        $queryString = http_build_query($args);
        
        $response = $client->get('https://api.otto.market/v4/orders?'.$queryString,
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $response->access_token,
                    ],
                ]);
        $orders = json_decode($response->getBody()->getContents());
        //dd($orders);
        if(!empty($orders)){
            $orders = $orders->resources;
            if(!empty($orders)){
                foreach($orders as $row){
                    $multiorder = 0;
                    set_time_limit(0);
                    echo 'OrderId: <a href="'.url('orderView?is_search=1&keyword='.$row->orderNumber).'" target="_blank">'.$row->orderNumber.'</a></br>';
                    //dd($row->positionItems);
                    $sum = 0;
                    foreach($row->positionItems as $k=>$item2){
                        if(isset($row->initialDeliveryFees[0])){
                            $sum += $item2->itemValueGrossPrice->amount + $row->initialDeliveryFees[0]->deliveryFeeAmount->amount;
                        }
                    }
                    foreach($row->positionItems as $k=>$item){
                        $sku = $item->product->sku;
                        $modelcode      = explode(" ", $sku)[0];
                        $product = Product::where(['modelcode'=>$modelcode])->first();
                        if(!$product){
                            echo "Warning: The product ".$sku." is added in Product table. Please fill up the rest of the information.<br>";
                            $warnmessage = "Warning: The product ".$sku." is added in Product table. Please fill up the rest of the information.";
                            
                            DB::table('tbl_open_activities')->insertGetId(['dateTime'=>date('Y-m-d H:i:s'),'issues'=>$warnmessage]);
                            
                            $product = new Product();
                            $product->modelcode = $modelcode;
                            $product->nameshort = $modelcode;
                            $product->namelong = $item->product->productTitle;
                            $product->ean = $item->product->ean;
                            $product->sku = $sku;
                            $product->active = 'Yes';
                            $product->virtualkit = 'No';
                            $product->save();
                            
                            $lagerStand = new LagerStand();
                            $lagerStand->productid = $product->productid;
                            $lagerStand->idwarehouse = $channel->warehouse;
                            $lagerStand->save();
                        }
                         
                        $isExists = OrderItem::where(['idorderplatform' => $row->orderNumber, 'order_item_id'=> $item->positionItemId])->first();
                        if(!$isExists){
                           // dd($row);
                            $date = date('Y-m-d', strtotime($row->orderDate));
                            $datetime = new \DateTime($date);
                            $week = $datetime->format("W");
                                                        
                            $total = $item->itemValueGrossPrice->amount;
                            if(isset($row->initialDeliveryFees[0])){
                                $total = $total + $row->initialDeliveryFees[0]->deliveryFeeAmount->amount;
                            }
                            
                            $orderItem = new OrderItem();
                            $orderItem->idorderplatform         = $row->orderNumber;
                            if($multiorder == 0){
                                $orderItem->sum                 = $sum;
                                $orderItem->multiorder          = "0";
                            }else{
                                $orderItem->multiorder          = $row->orderNumber;
                                $orderItem->sum                 = $total;
                            }
                            $orderItem->referenceorder          = $row->orderNumber;
                            $orderItem->order_item_id           = $item->positionItemId;
                            $orderItem->productid               = $product->productid;
                            $orderItem->idcompany               = $channel->idcompany;
                            $orderItem->referencechannel        = $channel->idchannel;
                            $orderItem->idwarehouse             = $channel->warehouse;
                            $orderItem->referencechannelname    = $channel->shortname;
                            $orderItem->platformname            = $channel->pShortName;
                            $orderItem->currency                = $item->itemValueGrossPrice->currency;
                            $orderItem->quantity                = 1;
                            $orderItem->sum                     = $total;
                            $orderItem->weeksell                = $week;
                            $orderItem->idpayment               = 'Otto';
                            $orderItem->inv_vat                 = $item->product->vatRate;
                            if(isset($item->trackingInfo)){
                                $orderItem->carriername             = $item->trackingInfo->carrier;
                                $orderItem->tracking                = $item->trackingInfo->trackingNumber;
                            }
                            $orderItem->sync                    = 'Synch with Otto';
                            
                            if(isset($row->deliveryAddress)){
                                
                                $deladdress = $row->deliveryAddress;
                                $orderItem->customer = $deladdress->lastName.' '.$deladdress->firstName;
                                //$orderItem->customerextra = ;
                                $orderItem->address1 = $deladdress->street.' '. $deladdress->houseNumber;
                                //$orderItem->address2 = ;
                                $orderItem->plz = $deladdress->zipCode;
                                $orderItem->city =$deladdress->city;
                                $orderItem->country = $deladdress->countryCode == 'DEU' ? 'DE' : $deladdress->countryCode;

                            }
                            
                            
                            if(isset($row->invoiceAddress)){
                                
                                $invaddress = $row->invoiceAddress;
                                $orderItem->inv_customer = $invaddress->lastName.' '.$invaddress->firstName;
                                //$orderItem->inv_customerextra = ;
                                $orderItem->inv_address1 =$invaddress->street.' '.$invaddress->houseNumber;
                                //$orderItem->inv_address2 = ;
                                $orderItem->plz1 = $invaddress->zipCode;
                                $orderItem->city1 = $invaddress->city;
                                $orderItem->country1 = $invaddress->countryCode == 'DEU' ? 'DE' : $invaddress->countryCode;

                            }
                            
                            
                            if($item->fulfillmentStatus == 'SENT'){
                                $orderItem->printedshippingok          = 1;
                                $orderItem->trackinguploadedok          = 1;
                                $orderItem->courierinformedok           = 1;
                                $orderItem->trackinguploadedok          = 1;
                                $orderItem->registeredtolagerstandok    = 1;
                            }
                            
                            $orderItem->datee = $date;
                            $orderItem->save();
                            if(isset($row->initialDeliveryFees[0])){
                                $shippingCost = $row->initialDeliveryFees[0]->deliveryFeeAmount->amount;
                                $orderItem->orderInvoice()->create(['shipping'=> $shippingCost,'vat'=> ($orderItem->channel ? $orderItem->channel->vat : 0) ]);
                            }
                            
                        }else{
                            
                            if($item->fulfillmentStatus == 'SENT'){
                                $orderItem = $isExists;
                                $orderItem->printedshippingok          = 1;
                                $orderItem->trackinguploadedok          = 1;
                                $orderItem->courierinformedok           = 1;
                                $orderItem->trackinguploadedok          = 1;
                                $orderItem->registeredtolagerstandok    = 1;
                                $orderItem->save();
                            }
                        }
                        $multiorder++;
                    }
                }
            }
        }
       // echo '<pre>'; print_r($orders); echo '</pre>'; 
    }
}
