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
    

    public function __construct(){

    }


    public function orderSendToPlatform(Request $request)
    {
        if($request->filled('platform') && $request->platform == 'OTTO'){

            $orderId = $request->orderId;
            $order = OrderItem::find($orderId); //dd($order);
            if($order){
                if($order->channel){
                    $token = $this->getAccessToken( $order->channel );
                    $client = new \GuzzleHttp\Client();
                    try {
                        if($order->trackinguploadedok == 0 ){
                            $result  =    $this->createShipment($order, $token->access_token);
                            if(isset($result->shipmentId)){
                                $order->trackinguploadedok = 1;
                                $order->save();
                                $result['success'] = true;
                                return response()->json($result);
                            }else{
                                return response()->json(['success'=>false,'message'=>'This order already infromed to platform'], 400);
                            }
                        }else{
                            return response()->json(['success'=>false,'message'=>'Something went wrong'], 400);
                        }
                        //$result  =   $this->getShipmentDetails($order->carriername, $order->tracking, $token->access_token);
                        if(isset($result->errors[0]) && $result->errors[0]->title == 'RESOURCE_NOT_FOUND'){

                        }
                    }
                    catch (\GuzzleHttp\Exception\ClientException $e) {
                        
                    return [];
                    }
                }
            }
        }
    }

    public function createShipment($order, $token)
    { //$order->tracking
        try {
            $client = new \GuzzleHttp\Client();
            $body = '{
              "trackingKey": {
                "carrier": "'.$order->carriername.'",
                "trackingNumber": "'.$order->tracking.'"
              },
              "shipDate": "'.date("Y-m-d\TH:i:s.000\Z").'",
              "shipFromAddress": {
                "city": "'.$order->carriername.'",
                "countryCode": "'.( $order->country == 'DE' ? 'DEU' : $order->country ).'",
                "zipCode": "'.$order->plz.'"
              },
              "positionItems": [
                {
                    "positionItemId": "'.$order->order_item_id.'",
                    "salesOrderId": "'.$order->salesOrderId.'",
                    "returnTrackingKey": {
                        "carrier": "'.$order->returnTrackingCarrier.'",
                        "trackingNumber": "'.$order->returnTrackingNumber.'"
                    }
                }
              ]
            }';

            $response = $client->post('https://api.otto.market/v1/shipments', [
              'body' => $body,
              'headers' => [
                'Content-Type' => 'application/json', 
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' .$token,
              ]
            ]);
            $shipments = json_decode($response->getBody()->getContents());
            return $shipments;
        }catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $result =  json_decode($response->getBody()->getContents());
            return $result;
        }
    }

    public function getShipmentDetails($carrier, $trackingNumber, $token)
    {
        try {
            $client = new \GuzzleHttp\Client();
            $args = [
                    'carrier'=> $carrier,
                    'trackingNumber'=> $trackingNumber
                ];
            $queryString = http_build_query($args);
            $response = $client->get("https://api.otto.market/v1/shipments/carriers/{$carrier}/trackingnumbers/{$trackingNumber}",
                    [
                        'headers' => [
                            'Authorization' => 'Bearer ' .$token,
                        ],
                    ]);
            $shipments = json_decode($response->getBody()->getContents());
            return $shipments;
        }catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $result =  json_decode($response->getBody()->getContents());
            return $result;
        }
    }

    public function getAccessToken($channel)
    {
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
            }
            return $res;
        }
        catch (\GuzzleHttp\Exception\ClientException $e) {
            // $response = $e->getResponse();
            // $result =  json_decode($response->getBody()->getContents());
            // return response()->json(['data' => $result]);

            return [];
        }
    }
    public function getOrders(Request $request){
        if(request()->filled('delete') && request()->delete == 1){
            OrderItem::where('referencechannelname','OTTO')->delete();
        }
        $channels  = Channel::query()
                ->join('platform', 'channel.platformid', '=', 'platform.platformid')
                ->where('platform.platformtype','Otto')
                ->select('channel.*','platform.shortname as pShortName')
                ->get();
        foreach($channels as $channel){
            try {
                $res = $this->getAccessToken( $channel );
                if($res){
                   echo 'Getting orders for '.$channel->shortname.'</br>';
                   $this->ottoOrders($res, $channel);
                   echo 'Done orders for '.$channel->shortname.'</br>';
                }    
            }catch (\GuzzleHttp\Exception\ClientException $e) {
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
      //   dd($orders);
        if(!empty($orders)){
            $orders = $orders->resources;
            if(!empty($orders)){
                foreach($orders as $index => $row){
                    $multiorder = 0;
                    set_time_limit(0);
                    echo 'OrderId: <a href="'.url('orderView?is_search=1&keyword='.$row->orderNumber).'" target="_blank">'.$row->orderNumber.'</a></br>';
                    //dd($row->positionItems);
                    $sum = 0;
                    foreach($row->positionItems as $k=>$item2){
                        if(isset($item2->itemValueGrossPrice)){
                            $sum += $item2->itemValueGrossPrice->amount;
                        }
                    }

                    if(isset($row->initialDeliveryFees[0])){
                        $sum  =$sum + $row->initialDeliveryFees[0]->deliveryFeeAmount->amount;
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
                            $orderItem->salesOrderId            = $row->salesOrderId;
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
                            $orderItem->weeksell                = $week;
                            $orderItem->idpayment               = 'Otto';
                            $orderItem->inv_vat                 = $item->product->vatRate;
                            $orderItem->inv_price               = $item->itemValueGrossPrice->amount;
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
                                $orderItem->registeredtolagerstandok    = 1;
                            }
                            
                            $orderItem->datee = $date;
                            $orderItem->save();
                            if(isset($row->initialDeliveryFees[0])){
                                $shippingCost = $row->initialDeliveryFees[0]->deliveryFeeAmount->amount;
                                $orderItem->orderInvoice()->create(['shipping'=> $shippingCost,'vat'=> ($orderItem->channel ? $orderItem->channel->vat : 0) ]);
                            }
                            
                        }else{
                            
                            $isExists->salesOrderId            = $row->salesOrderId;
                            if($item->fulfillmentStatus == 'SENT'){
                                $isExists = $isExists;
                                $isExists->printedshippingok          = 1;
                                $isExists->trackinguploadedok          = 1;
                                $isExists->courierinformedok           = 1;
                                $isExists->registeredtolagerstandok    = 1;
                                $isExists->save();
                            }
                        }
                        $multiorder++;
                    }
                }
            }
        }
       // echo '<pre>'; print_r($orders); echo '</pre>'; 
    }

    public function downloadAssets(Request $request, $type){
        $collection  = Channel::query();
        $collection->join('platform', 'channel.platformid', '=', 'platform.platformid');
        $collection->where('platform.platformtype','Otto');
        $collection->select('channel.*','platform.shortname as pShortName');
        if(request()->filled('channelId')){
            $collection->where('idchannel', request()->channelId);
        }
        $channels   =  $collection->get();

        if($channels->count() >0){
            foreach($channels as $channel){
                try {
                    $res = $this->getAccessToken( $channel );
                    if($res){
                        if($type == 'quantity'){
                           echo 'Getting quantities for '.$channel->shortname.'</br>';
                           $this->getQualities($res, $channel);
                           echo 'Done quantities for '.$channel->shortname.'</br>';
                        }else{
                            if($type == 'price'){
                               echo 'Getting prices for '.$channel->shortname.'</br>';
                               $this->getPrices($res, $channel, 0);
                               echo 'Done prices for '.$channel->shortname.'</br>';
                            }
                        }
                    }    
                }catch (\GuzzleHttp\Exception\ClientException $e) {
                    $response = $e->getResponse();
                    $result =  json_decode($response->getBody()->getContents());
                    return response()->json(['data' => $result]);
                }
            }
        }else{
            echo "Channel not found";
        }
    }

    public function getQualities($response, $channel, $page = 0){
        $client = new \GuzzleHttp\Client();
        $args = [
                'fromOrderDate'=> date('Y-m-d', strtotime('-3 days')).'T01:00:00+02:00',
                'toOrderDate'=> date('Y-m-d'). 'T23:59:59+02:00',
            ];
        $queryString = http_build_query($args);
        $res = $client->get('https://api.otto.market/v2/quantities?page='.$page.'&limit=200',
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $response->access_token,
                    ],
                ]);
        $quantities = json_decode($res->getBody()->getContents());
        if(!empty($quantities)){
            if(isset($quantities->resources->variations) && !empty($quantities->resources->variations)){
                foreach($quantities->resources->variations as $index => $row){
                    $sku = $row->sku;    
                    echo "sku: ".$sku.' - quantity: '.$row->quantity.'<br>';
                    $product = Product::where(['modelcode'=>substr($sku, 0, 5)])->first(); 
                    if($product){
                        $priceRow = Price::where(['channel_id'=>$channel->idchannel, 'country'=> $channel->country, 'sku'=>$sku])->first();
                        if(!$priceRow){
                            $created = Price::create([
                                'product_id'=> $product->productid,
                                'last_update_qty_date'=> date('Y-m-d H:i:s', strtotime($row->lastModified)),
                                'online_quentity'=> $row->quantity,
                                'channel_id'=>$channel->idchannel,
                                'warehouse_id'=>$channel->warehouse,
                                'platform_id'=>$channel->platformid,
                                'country'=>$channel->country,
                                'sku'=>$sku,
                                'online_price'=> 0,
                                'ean'=>$product->ean,
                                'asin'=>$product->asin,
                                'online_price'=>0,
                                'online_shipping'=>0,
                                'price'=>0,
                                'created_date'=> date('Y-m-d H:i:s'),
                                'updated_date'=> date('Y-m-d H:i:s'),
                            ]);
                        }else{
                            $updated = Price::where('price_id', $priceRow->price_id)->update([
                                'last_update_qty_date'=> date('Y-m-d H:i:s', strtotime($row->lastModified)),
                                'online_quentity'=> $row->quantity,
                                'updated_date'=> date('Y-m-d H:i:s'),
                            ]);
                        }
                    }else{
                        //product not exists
                        $warnmessage   = "Warning: No quantities for ".$sku." in ".$channel->country." of channel ".$channel->shortname;
                        echo $warnmessage.'</br>'; 
                        DB::table('tbl_open_activities')->insertGetId(['dateTime'=>date('Y-m-d H:i:s'),'issues'=>$warnmessage]);
                    }
                }
            }
        }


        if(!empty($quantities->links)){
            foreach ($quantities->links as $key => $link) {
                if($link->rel == 'next'){
                    $query_str = parse_url($link->href, PHP_URL_QUERY);
                    parse_str($query_str, $query_params);
                    if(isset($query_params['page'])){
                        $this->getQualities($response, $channel, $query_params['page']);
                    }
                    break;
                }
            }
        }
    }

    public function getPrices($response, $channel, $page = 0){
        $client = new \GuzzleHttp\Client();
        $res = $client->get('https://api.otto.market/v2/products/prices?page='.$page.'&limit=100',
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $response->access_token,
                    ],
                ]);
        $results = json_decode($res->getBody()->getContents());

        if(!empty($results)){
            if(isset($results->variationPrices) && !empty($results->variationPrices)){
                foreach($results->variationPrices as $index => $row){ //dd($row);
                    $sku = $row->sku;   // dd($row->standardPrice);
                    echo "sku: ".$sku.' - price: '.$row->standardPrice->amount.'<br>';
                    $product = Product::where(['modelcode'=>substr($sku, 0, 5)])->first(); 
                    if($product){
                        $priceRow = Price::where(['channel_id'=>$channel->idchannel, 'country'=> $channel->country, 'sku'=>$sku])->first();
                        if(!$priceRow){
                            $created = Price::create([
                                'product_id'=> $product->productid,
                                'last_update_date'=> date('Y-m-d H:i:s'),
                                'online_quentity'=> 0,
                                'channel_id'=>$channel->idchannel,
                                'warehouse_id'=>$channel->warehouse,
                                'platform_id'=>$channel->platformid,
                                'country'=>$channel->country,
                                'sku'=>$sku,
                                'ean'=>$product->ean,
                                'asin'=>$product->asin,
                                'online_price'=>$row->standardPrice->amount,
                                'online_shipping'=>0,
                                'price'=>$row->standardPrice->amount,
                                'created_date'=> date('Y-m-d H:i:s'),
                                'updated_date'=> date('Y-m-d H:i:s'),
                            ]);
                        }else{
                            $updated = Price::where('price_id', $priceRow->price_id)->update([
                                'last_update_date'=> date('Y-m-d H:i:s'),
                                'online_price'=> $row->standardPrice->amount,
                                'price'=> $row->standardPrice->amount,
                                'updated_date'=> date('Y-m-d H:i:s'),
                            ]);
                        }
                    }else{
                        //product not exists
                        $warnmessage   = "Warning: No price for ".$sku." in ".$channel->country." of channel ".$channel->shortname;    
                        echo $warnmessage.'</br>';
                        DB::table('tbl_open_activities')->insertGetId(['dateTime'=>date('Y-m-d H:i:s'),'issues'=>$warnmessage]);
                    }
                }
            }
        }

        if(!empty($results->links)){
            foreach ($results->links as $key => $link) {
                if($link->rel == 'next'){
                    $query_str = parse_url($link->href, PHP_URL_QUERY);
                    parse_str($query_str, $query_params);
                    if(isset($query_params['page'])){
                        $this->getPrices($response, $channel, $query_params['page']);
                    }

                    break;
                }
            }
        }
    }

    public function updatePriceAndQuantity(Request $request, $channel_id)
    {print_r($request->all()); exit();
        $channel = Channel::find($channel_id);
        if($channel){
            $token = $this->getAccessToken( $order->channel );
            $this->updatePrice($request, $token);
            $this->updateQuantity($request, $token);
        }
    }


    public function updatePrice($request, $token)
    {
        try {
            $client = new \GuzzleHttp\Client();
            $body = '[
                  {
                    "sku": "'.$request->sku.'",
                    "standardPrice": {
                      "amount": '.$request->price.',
                      "currency": "EUR"
                    },
                    "sale": {
                      "salePrice": {
                        "amount": '.$request->price.',
                        "currency": "EUR"
                      },
                      "startDate": "'.date("Y-m-d\TH:i:s.000\Z").'",
                      "endDate": "'.date("Y-m-d\TH:i:s.000\Z").'"
                    }
                  }
                ]';

            $response = $client->post('https://api.otto.market/v2/products/prices', [
              'body' => $body,
              'headers' => [
                'Content-Type' => 'application/json', 
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' .$token,
              ]
            ]);
            $response = json_decode($response->getBody()->getContents());
            return $response;
        }catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $result =  json_decode($response->getBody()->getContents());
            return $result;
        }
    }


    public function updateQuantity($request, $token)
    { 
        try {
            $client = new \GuzzleHttp\Client();
            $body = '[
                  {
                    "lastModified": "'.date("Y-m-d\TH:i:s.000\Z").'"
                    "quantity": '.$request->quantity.',
                    "sku": "'.$request->sku.'",
                  }
                ]';

            $response = $client->post('https://api.otto.market/v2/quantities', [
              'body' => $body,
              'headers' => [
                'Content-Type' => 'application/json', 
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' .$token,
              ]
            ]);
            $response = json_decode($response->getBody()->getContents());
            return $response;
        }catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $result =  json_decode($response->getBody()->getContents());
            return $result;
        }
    }
}
