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
use App\Models\Country;
use App\Models\Channel;
use App\Models\Product;
use App\Models\LagerStand;
use App\Models\OrderItem;
use Codexshaper\WooCommerce\Facades\Product as WooProduct;
 

class WooController extends Controller {
    
    public function downloadReport(Request $request, $page = 1){

        $idchannel = request()->channelId;
        if(!$idchannel || !intval($idchannel)){
            abort(404);
        }
        $channel = Channel::find($idchannel);
        
        if(!$channel) return;

        //Price::where('channel_id',$idchannel)->update(['ebayActive'=>0]);

        ini_set('memory_limit', -1);
        ini_set('max_execution_time', 300);
        if($channel){
            if($channel->woo_store_url != null && $channel->woo_store_url != "") {
                config([
                    'woocommerce.store_url' => $channel->woo_store_url,
                    'woocommerce.consumer_key' => $channel->woo_consumer_key,
                    'woocommerce.consumer_secret' => $channel->woo_consumer_secret
                ]);
                Session::put("WOOCOMMERCE_STORE_URL",       $channel->woo_store_url);
                Session::put("WOOCOMMERCE_CONSUMER_KEY",    $channel->woo_consumer_key);
                Session::put("WOOCOMMERCE_CONSUMER_SECRET", $channel->woo_consumer_secret);
                $options = [
                   'per_page' => 100, // Or your desire number
                   'page' => $page
                ];

                $products = WooProduct::all($options, $channel);//dd($products );
                if($products->count() > 0){

                    foreach ($products as $key => $row) {
                        $sku = $row->sku;
                        if($sku !=''){
                            echo "sku: ".$sku.'----------quantity------'.$row->stock_quantity.'------price-------'.$row->price.'<br>';
                           // echo '------------------------------------------'.'<br>';
                            $product = Product::where(['modelcode'=>substr($sku, 0, 5)])->first(); 
                            if($product){
                                $priceRow = Price::where(['channel_id'=>$channel->idchannel, 'country'=> $channel->country, 'sku'=>$sku])->first();

                                //$priceRow->ebayActive = 0;
                                //$priceRow->save();

                                if(!$priceRow){
                                    $created = Price::create([
                                        'product_id'=> $product->productid,
                                        'last_update_date'=> date('Y-m-d H:i:s'),
                                        'online_quentity'=> $row->stock_quantity,
                                        'channel_id'=>$channel->idchannel,
                                        'warehouse_id'=>$channel->warehouse,
                                        'platform_id'=>$channel->platformid,
                                        'country'=>$channel->country,
                                        'sku'=>$sku,
                                        'ean'=>$product->ean,
                                        'asin'=>$product->asin,
                                        'online_price'=>$row->price,
                                        'online_shipping'=>$channel->flat_shipping_costs,
                                        'shipping'=>$channel->flat_shipping_costs,
                                        'last_update_shipping'=> date('Y-m-d H:i:s'),
                                        'price'=>$row->price,
                                        'created_date'=> date('Y-m-d H:i:s'),
                                        'updated_date'=> date('Y-m-d H:i:s'),
                                        'last_update_qty_date'=> date('Y-m-d H:i:s'),
                                        'ebayActive'=>1,
                                        'itemId'=> $row->id,
                                    ]);
                                }else{
                                    $updated = Price::where('price_id', $priceRow->price_id)->update([
                                        'last_update_date'=> date('Y-m-d H:i:s'),
                                        'online_price'=> $row->price,
                                        'price'=> $row->price,
                                        'updated_date'=> date('Y-m-d H:i:s'),
                                        'online_quentity'=> $row->stock_quantity,
                                        'last_update_qty_date'=> date('Y-m-d H:i:s'),
                                        'ebayActive'=>1,
                                        'itemId'=> $row->id,
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

                    $page++;
                   // echo $page.'</br>';
                    $this->downloadReport($request, $page);

                }else{

                }
            }else{
                return response()->json(['invalid channel']);
            }    
        }
    }


    public function prepareAndUpdateDB( $channel, $sku, $country, $online_shipping, $itemID, $online_price, $quantity ){

        $product = Product::where(['modelcode'=>substr($sku, 0, 5)])->first(); 
        if($product){
            $cost = 0;
            if($product->virtualkit == "Yes") {
                $cost = 0;
                for($i=1; $i<10; $i++) {
                    $item = "pcs".$i;
                    $itemProductId  = "productid".$i;
                    $productid      = $product->$itemProductId;
                    if($product->$item != null && $product->$item > 0 && $product->$item != "" && $productid != "" && $productid != null) {
                        $itemProduct = $product = Product::where(['modelcode'=>$productid])->first();
                        if($itemProduct){
                            $cost += $itemProduct->price*$product->$item;
                        }
                    }
                }
            }else{
                $cost = $product->price;
            }

            $priceRow = Price::where(['channel_id'=>$channel->idchannel, 'country'=> $country, 'sku'=>$sku])->first();
            if(!$priceRow){
                $created = Price::create([
                    'itemId'=> $itemID,
                    'country'=> $country,
                    'shipping'=> $online_shipping,
                    'product_id'=> $product->productid,
                    'last_update_shipping'=> date('Y-m-d H:i:s'),
                    'last_update_date'=> date('Y-m-d H:i:s'),
                    'online_shipping'=> $online_shipping,
                    'last_update_qty_date'=> date('Y-m-d H:i:s'),
                    'online_quentity'=> $quantity,
                    'channel_id'=>$channel->idchannel,
                    'warehouse_id'=>$channel->warehouse,
                    'platform_id'=>$channel->platformid,
                    'sku'=>$sku,
                    'online_price'=> $online_price,
                    'ean'=>$product->ean,
                    'asin'=>$product->asin,
                    'price'=>$online_price,
                    'cost'=>$cost,
                    'ebayActive'=> 1,
                    'created_date'=> date('Y-m-d H:i:s'),
                    'updated_date'=> date('Y-m-d H:i:s'),
                ]);
            }else{
                $updated = Price::where('price_id', $priceRow->price_id)->update([
                    'itemId'=> $itemID,
                    'country'=> $country,
                    'shipping'=> $online_shipping,
                    'product_id'=> $product->productid,
                    'last_update_shipping'=> date('Y-m-d H:i:s'),
                    'last_update_date'=> date('Y-m-d H:i:s'),
                    'online_shipping'=> $online_shipping,
                    'last_update_qty_date'=> date('Y-m-d H:i:s'),
                    'online_quentity'=> $quantity,
                    'channel_id'=>$channel->idchannel,
                    'warehouse_id'=>$channel->warehouse,
                    'platform_id'=>$channel->platformid,
                    'sku'=>$sku,
                    'online_price'=> $online_price,
                    'ean'=>$product->ean,
                    'asin'=>$product->asin,
                    'price'=>$online_price,
                    'created_date'=> date('Y-m-d H:i:s'),
                    'updated_date'=> date('Y-m-d H:i:s'),
                    'ebayActive'=> 1,
                    'cost'=>$cost,
                ]);
            }
        }else{
            //product not exists
            $warnmessage   = "Warning: No product for ".$sku." in ".$channel->country." of channel ".$channel->shortname;
            echo $warnmessage.'</br>'; 
            DB::table('tbl_open_activities')->insertGetId(['dateTime'=>date('Y-m-d H:i:s'),'issues'=>$warnmessage]);
        }
    }


    public function getItemByItemId($itemId, $access_token){
        $client = new \GuzzleHttp\Client();
        try {
            $res = $client->get('https://api.ebay.com/buy/browse/v1/item/v1%7C'.$itemId.'%7C0', [
                    'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Authorization' => 'Bearer '.$access_token,
                  ]                    
            ]);
            return json_decode($res->getBody()->getContents()); 
        }catch (\GuzzleHttp\Exception\ClientException $e) {
            return [];
        }
    }


    public function getItem( $channel, $response, $data)
    {
        $endpoint  = 'https://api.ebay.com/ws/api.dll'; // URL to call
        $headers = array(
            'Content-Type: text/xml',
            'X-EBAY-API-COMPATIBILITY-LEVEL:877',
            'X-EBAY-API-DEV-NAME:'.$channel->devid,
            'X-EBAY-API-APP-NAME:'.$channel->appid,
            'X-EBAY-API-CERT-NAME:'.$channel->certid,
            'X-EBAY-API-SITEID:0',
            'X-EBAY-API-CALL-NAME:GetItem'
        );            
        $xml = "<?xml version='1.0' encoding='utf-8'?>
                <GetItemRequest xmlns='urn:ebay:apis:eBLBaseComponents'>
                <RequesterCredentials>
                    <eBayAuthToken>".$response->access_token."</eBayAuthToken>
                </RequesterCredentials>
                <ItemID>".$data['ItemID']."</ItemID>
                </GetItemRequest>";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "xmlRequest=" . $xml);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 300);
        $data = curl_exec($ch);
        curl_close($ch);
        //convert the XML result into array
        $array_data = json_decode(json_encode(simplexml_load_string($data)), true);
        return $array_data;

    }

    public function updatePrice( Request $request, $idchannel ){
        
        if($request->price <= 0) return;
        
        $channel = Channel::find($idchannel);
        $response = $this->getAccessTokenByRefreshtoken($channel);
        if(!empty($response)){
            $endpoint  = 'https://api.ebay.com/ws/api.dll'; // URL to call
            $headers = array(
                'Content-Type: text/xml',
                'X-EBAY-API-COMPATIBILITY-LEVEL:837',
                'X-EBAY-API-DEV-NAME:'.$channel->devid,
                'X-EBAY-API-APP-NAME:'.$channel->appid,
                'X-EBAY-API-CERT-NAME:'.$channel->certid,
                'X-EBAY-API-SITEID:0',
                'X-EBAY-API-CALL-NAME:ReviseItem'
            );
            
            $xml = '<?xml version="1.0" encoding="utf-8"?>
                <ReviseItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">
                    <RequesterCredentials>
                        <eBayAuthToken>'.$response->access_token.'</eBayAuthToken>
                    </RequesterCredentials>
                    <Item ComplexType="ItemType">
                        <ItemID>'.$request->itemId.'</ItemID>
                        <StartPrice>'.$request->price.'</StartPrice>
                    </Item>
                    <MessageID>1</MessageID>
                    <WarningLevel>High</WarningLevel>
                    <Version>837</Version>
                </ReviseItemRequest>';
            echo "<br>";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_URL, $endpoint);
            curl_setopt($ch, CURLOPT_POSTFIELDS, "xmlRequest=" . $xml);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 300);
            $data = curl_exec($ch);
            curl_close($ch);
            //convert the XML result into array
            $array_data = json_decode(json_encode(simplexml_load_string($data)), true);
            return $array_data;
        }
    }

    public function updateQuantity( Request $request, $idchannel ){
        
        if($request->quantity <= 0) return;
        
        $channel = Channel::find($idchannel);
        $response = $this->getAccessTokenByRefreshtoken($channel);
        if(!empty($response)){
            $endpoint  = 'https://api.ebay.com/ws/api.dll'; // URL to call
            $headers = array(
                'Content-Type: text/xml',
                'X-EBAY-API-COMPATIBILITY-LEVEL:837',
                'X-EBAY-API-DEV-NAME:'.$channel->devid,
                'X-EBAY-API-APP-NAME:'.$channel->appid,
                'X-EBAY-API-CERT-NAME:'.$channel->certid,
                'X-EBAY-API-SITEID:0',
                'X-EBAY-API-CALL-NAME:ReviseItem'
            );
            
            $xml = '<?xml version="1.0" encoding="utf-8"?>
                <ReviseItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">
                    <RequesterCredentials>
                        <eBayAuthToken>'.$response->access_token.'</eBayAuthToken>
                    </RequesterCredentials>
                    <Item ComplexType="ItemType">
                        <ItemID>'.$request->itemId.'</ItemID>
                        <Quantity>'.$request->quantity.'</Quantity>
                    </Item>
                    <MessageID>1</MessageID>
                    <WarningLevel>High</WarningLevel>
                    <Version>837</Version>
                </ReviseItemRequest>';
            echo "<br>";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_URL, $endpoint);
            curl_setopt($ch, CURLOPT_POSTFIELDS, "xmlRequest=" . $xml);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 300);
            $data = curl_exec($ch);
            curl_close($ch);
            //convert the XML result into array
            $array_data = json_decode(json_encode(simplexml_load_string($data)), true);
            return $array_data;
        }
    }

    public function saveAttachment($data)
    {
        $tempFilename = tempnam(sys_get_temp_dir(), 'attachment').'.zip';
        $fp = fopen($tempFilename, 'wb');
        if ($fp) {
            fwrite($fp, $data);
            fclose($fp);
            return $tempFilename;
        } else {
            printf("Failed. Cannot open %s to write!\n", $tempFilename);
            return false;
        }
    }

    public function unzipArchive($filename)
    {
        printf("Unzipping %s...", $filename);

        $zip = new ZipArchive();
        if ($zip->open($filename)) {
            /**
             * Assume there is only one file in archives from eBay.
             */
            $xml = $zip->getFromIndex(0);
            if ($xml !== false) {
                print("Done\n");
                return $xml;
            } else {
                printf("Failed. No XML found in %s\n", $filename);
                return false;
            }
        } else {
            printf("Failed. Unable to unzip %s\n", $filename);
            return false;
        }
    }

}
