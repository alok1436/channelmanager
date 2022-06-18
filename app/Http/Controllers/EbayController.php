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

 

class EbayController extends Controller {
    

    public function __construct(){

    }


    public function connect(Request $request, $id)
    {
        $channel = Channel::find($id); //dd($channel);
        if($channel){
            session()->put('channel', $channel->idchannel);
            $url = "https://auth.ebay.com/oauth2/authorize?client_id=".$channel->appid."&response_type=code&redirect_uri=ottavio_linzalo-ottaviol-testap-gipawcx&scope=https://api.ebay.com/oauth/api_scope https://api.ebay.com/oauth/api_scope/sell.marketing.readonly https://api.ebay.com/oauth/api_scope/sell.marketing https://api.ebay.com/oauth/api_scope/sell.inventory.readonly https://api.ebay.com/oauth/api_scope/sell.inventory https://api.ebay.com/oauth/api_scope/sell.account.readonly https://api.ebay.com/oauth/api_scope/sell.account https://api.ebay.com/oauth/api_scope/sell.fulfillment.readonly https://api.ebay.com/oauth/api_scope/sell.fulfillment https://api.ebay.com/oauth/api_scope/sell.analytics.readonly https://api.ebay.com/oauth/api_scope/sell.finances https://api.ebay.com/oauth/api_scope/sell.payment.dispute https://api.ebay.com/oauth/api_scope/commerce.identity.readonly https://api.ebay.com/oauth/api_scope/commerce.notification.subscription https://api.ebay.com/oauth/api_scope/commerce.notification.subscription.readonly";
            return redirect($url);
        }
    }

    public function callback(Request $request)
    {   
        if($request->filled('code')){
            $this->getAccessToken($request->code);
            return redirect('channelView')->with('msg','Connected successfully');
        }else{
            return redirect('channelView')->with('msg','Something went wrong.');
        }
    }

    public function getAccessToken( $code)
    {   $idchannel = session()->get('channel');
        $channel = Channel::find($idchannel);
        $client = new \GuzzleHttp\Client();
        try {
            $res = $client->post('https://api.ebay.com/identity/v1/oauth2/token', [
                    'form_params' => [
                        'grant_type' => 'authorization_code',
                        'code' => $code,
                        'redirect_uri' => 'ottavio_linzalo-ottaviol-testap-gipawcx',
                    ],
                    'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Authorization' => 'Basic ' .base64_encode($channel->appid.':'.$channel->certid),
                  ]                    
                ]);
    
            $res = json_decode($res->getBody()->getContents()); 
            if($res){
                
                $channel->refresh_token = $res->refresh_token;
                $channel->accesstoken = $res->access_token;
                $channel->expire = $res->expires_in;
                $channel->data = json_encode($res);
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

    public function getAccessTokenByRefreshtoken(Channel $channel)
    {   
        $client = new \GuzzleHttp\Client();
        try {
            $res = $client->post('https://api.ebay.com/identity/v1/oauth2/token', [
                    'form_params' => [
                        'grant_type'=>'refresh_token',
                        'refresh_token' => $channel->refresh_token,
                    ],
                    'headers' => [
                    
                    'Authorization' => 'Basic ' .base64_encode($channel->appid.':'.$channel->certid),
                  ]                    
                ]);
    
            $res = json_decode($res->getBody()->getContents()); 
            if($res){
                $channel->accesstoken = $res->access_token;
                $channel->expire = $res->expires_in;
                $channel->save();
            }
            return $res;
        }
        catch (\GuzzleHttp\Exception\ClientException $e) {
            return [];
            // $response = $e->getResponse();
            // $result =  json_decode($response->getBody()->getContents());
            // return response()->json(['data' => $result]);
        }
    }

    public function createInventorTask($token)
    {
        try {
            $client = new \GuzzleHttp\Client();
            $body = '{
                  "schemaVersion" : "1.0",
                  "feedType": "LMS_ACTIVE_INVENTORY_REPORT",
                  "filterCriteria": {
                    "creationDateRange": {
                      "from": "",
                      "to": ""
                    },
                    "modifiedDateRange": {
                      "from": "",
                      "to": ""
                    },
                    "listingFormat": "FIXED_PRICE",
                    "listingStatus": "ACTIVE"
                  },
                  "inventoryFileTemplate": "[STANDARD,GTIN,REVISE_PRICE_QUANTITY]"
                }';

            $response = $client->post('https://api.ebay.com/sell/feed/v1/inventory_task', [
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
    public function getInventoryTasks($access_token){
        $client = new \GuzzleHttp\Client();
        try {
            $args = [
                'offset'=> 0,
                'limit'=> 1,
                'feed_type'=> 'LMS_ACTIVE_INVENTORY_REPORT',
                'date_range'=> date('Y-m-d').'T00:00:00.000Z..'.date('Y-m-d'). 'T23:59:59.000Z',
            ];


            $queryString = http_build_query($args);
            $res = $client->get('https://api.ebay.com/sell/feed/v1/inventory_task?'.$queryString, [
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
    public function downloadReport(Request $request){

        $idchannel = request()->channelId;
        if(!$idchannel || !intval($idchannel)){
            abort(404);
        }
        $channel = Channel::find($idchannel);
        
        if(!$channel) return;

        Price::where('channel_id',$idchannel)->update(['ebayActive'=>0]);

        ini_set('memory_limit', -1);
        ini_set('max_execution_time', 300);
        if($channel){
            $response = $this->getAccessTokenByRefreshtoken($channel);
         
            // $inventoryTasksLists = $this->getInventoryTasks($response->access_token);
 
            // if(isset($inventoryTasksLists->tasks) && empty($inventoryTasksLists->tasks)){
            //     $tasks = $this->createInventorTask($response->access_token);
            // }

            $tasks = $this->createInventorTask($response->access_token);
            sleep(2);
            $inventoryTasksLists = $this->getInventoryTasks($response->access_token);
          
            if(isset($response) && isset($inventoryTasksLists->tasks)){
                foreach($inventoryTasksLists->tasks as $tasks){
                    $client = new \GuzzleHttp\Client();
                    try {
                        $res = $client->get('https://api.ebay.com/sell/feed/v1/task/'.$tasks->taskId.'/download_result_file', [
                                'headers' => [
                                'Content-Type' => 'application/x-www-form-urlencoded',
                                'Authorization' => 'Bearer '.$response->access_token,
                              ]                    
                            ]);
                        $headers = $res->getHeaders();
                        //Storage::put('csv.zip', $res->getBody()->getContents());
                        $fileName = explode('=', $headers['content-disposition'][0]);
                        $filename = $this->saveAttachment($res->getBody());
                        if ($filename !== false) {
                            $xml = $this->unZipArchive($filename);
                            if ($xml !== false) {
                                $array_data = json_decode(json_encode(simplexml_load_string($xml)), true);
                                if(isset($array_data['ActiveInventoryReport']['SKUDetails']) && !empty($array_data['ActiveInventoryReport']['SKUDetails'])){
                                        foreach($array_data['ActiveInventoryReport']['SKUDetails'] as $data){
                                            set_time_limit(0);
                                            $itemID = $data['ItemID'];
                                            echo $itemID."<br>";
                                            
                                            $item_data = $this->getItem( $channel, $response, $data );
                                            if(isset($item_data['Item'])) {
                                                $item       = $item_data['Item'];
                                                $quantity   = $data['Quantity'];
                                                $country    = $item['Site'];
                                                $online_shipping = $item['ShippingDetails']['ShippingServiceOptions']['ShippingServiceCost'];
                                                echo 'itemID-----'.$itemID.'-------'.$country."-------online_shipping=".$online_shipping.'-----Quantity='.$quantity."<br>";      
                                                

                                                $countryRow = Country::where('longname', $country)->first();

                                                if($countryRow){
                                                    $country = $countryRow->shortname;
                                                }else{
                                                    if($country == "Germany") {
                                                        $country = "DE";
                                                    } else if($country == "Spain") {
                                                        $country = "ES";
                                                    } else if($country == "France") {
                                                        $country = "FR";
                                                    } else if($country == "Italy") {
                                                        $country = "IT";
                                                    }
                                                }

                                                if(isset($data['Price']) && isset($data['SKU'])) {
                                                    $this->prepareAndUpdateDB( $channel, $data['SKU'], $country, $online_shipping, $itemID, $data['Price'], $data['Quantity']);
                                                }else{
                                                    if(isset($data['Variations'])) {
                                                        $variations = $data['Variations']['Variation'];
                                                        foreach ($variations as $key => $itemRow) {
                                                            $this->prepareAndUpdateDB( $channel, $itemRow['SKU'], $country, $online_shipping, $itemID, $itemRow['Price'] , $itemRow['Quantity'] );
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }else{
                                        echo "<br>";
                                        echo "<span style='color:red;font-size:15px'>No record found in report</span>";
                                    }  
                                }
                            }
                        
                    }catch (\GuzzleHttp\Exception\ClientException $e) {
                        echo $e->getMessage();
                    }
                }//endforeach
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
