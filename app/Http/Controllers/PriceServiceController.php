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
use App\Models\Channel;
use App\Models\FBA;
use App\Models\Price;
use App\Models\Product;
use App\Models\NoneProduct;
use \DTS\eBaySDK\Sdk;
use \DTS\eBaySDK\Constants;
use \DTS\eBaySDK\FileTransfer;
use \DTS\eBaySDK\BulkDataExchange;
use \DTS\eBaySDK\MerchantData;
use \DTS\eBaySDK\Inventory\Services;
use \DTS\eBaySDK\Inventory\Types;
use \DTS\eBaySDK\Inventory\Enums;
use Codexshaper\WooCommerce\Facades\Product as ProductStore;

class PriceServiceController extends Controller {
    
    public function wcUpdateStoreData(Request $request,$channel_id) {
        $channel = Channel::with('platform')->find($channel_id);
        if($channel){
            if($channel->woo_store_url != null && $channel->woo_store_url != "") {
                Session::put("WOOCOMMERCE_STORE_URL",       $channel->woo_store_url);
                Session::put("WOOCOMMERCE_CONSUMER_KEY",    $channel->woo_consumer_key);
                Session::put("WOOCOMMERCE_CONSUMER_SECRET", $channel->woo_consumer_secret);
                config([
                    'woocommerce.store_url' => $channel->woo_store_url,
                    'woocommerce.consumer_key' => $channel->woo_consumer_key,
                    'woocommerce.consumer_secret' => $channel->woo_consumer_secret
                ]);
                if($request->item_id > 0){
                    try{
                        
                        $product = ProductStore::find($request->item_id)->toArray();

                        if($product){
                            $data = [];
                            if($request->price > 0){
                                if($product['sale_price']){
                                    $data['sale_price'] = $request->price;
                                }else{
                                    $data['regular_price'] = $request->price;
                                }
                            }
                            
                            if(!$product['manage_stock']){
                                $data['manage_stock'] = true;
                            }

                            $isFba = FBA::where(['sku'=>$request->sku, 'channel'=>$channel->idchannel])->count();
                            
                            if($isFba == 0){
                                $data['stock_quantity'] = $request->stock_quantity;
                            }
            
                            if(count($data) > 0){
                                $isupdated = ProductStore::update($request->item_id, $data);
                                return response()->json(['woocommerce update'=>$data,'count'=>count($isupdated)]);
                            }         
                        }else{
                            return response()->json(['error'=>'Item not found']);
                        }
                    }catch (\Exception $e) { 
                            echo 'Message: ' .$e->getMessage(); 
                    } 
                }else{
                    return response()->json(['error'=>'Item Id not found']);
                }
            }
        }
    }
    
    public function index(Request $request, $channel_id) {
        $channels = $channel_id > 0 && $channel_id != '' ? Channel::where('idchannel',$channel_id)->get() :  Channel::get();
        if($channels->count() > 0){
            foreach($channels as $channel_data) {
                $online_price       = 0.00;
                $online_shipping    = 0.00;
                $pageNum            = 0;
                if($channel_data->devid!='' && $channel_data->appid!='' && $channel_data->certid!='' && $channel_data->usertoken!='') {
                    $devID              = $channel_data->devid;
                    $appID              = $channel_data->appid;
                    $certID             = $channel_data->certid;
                    $userToken          = $channel_data->usertoken;
                    $shortname          = $channel_data->shortname;   
                    $idcompany          = $channel_data->idcompany;    
                    $vat                = $channel_data->vat; 
                    $platformid         = $channel_data->platformid; 
                    $idwarehouse        = $channel_data->warehouse;
                    $idchannel          = $channel_data->idchannel; 
                    $countryname        = $channel_data->country;
                    $warehouse          = $channel_data->warehouse;
                    $contry             = $channel_data->country;

                    Price::where('channel_id', $channel_data->idchannel)->update(['ebayActive'=>0]);
        
                    $credentials = [
                        'devId'     => $channel_data->devid,
                        'appId'     => $channel_data->appid,
                        'certId'    => $channel_data->usertoken
                    ]; 
    
                    $sdk = new Sdk([
                        'credentials' => $credentials,
                        'authToken'   => $channel_data->usertoken,
                        'sandbox'     => false
                    ]);
        
                    $exchangeService    = $sdk->createBulkDataExchange();
                    $transferService    = $sdk->createFileTransfer();
                    $merchantData       = new MerchantData\MerchantData();
       
        
                    $activeInventoryReportFilter = new BulkDataExchange\Types\ActiveInventoryReportFilter();
                    $activeInventoryReportFilter->includeListingType = 'AuctionAndFixedPrice';
                    $activeInventoryReportFilter->fixedPriceItemDetails = new BulkDataExchange\Types\FixedPriceItemDetails();
                    $activeInventoryReportFilter->fixedPriceItemDetails->includeVariations = true;
        
                    $startDownloadJobRequest = new BulkDataExchange\Types\StartDownloadJobRequest();
                    $startDownloadJobRequest->downloadJobType = 'ActiveInventoryReport';
                    $startDownloadJobRequest->UUID = uniqid();
                    $startDownloadJobRequest->downloadRequestFilter = new BulkDataExchange\Types\DownloadRequestFilter();
                    $startDownloadJobRequest->downloadRequestFilter->activeInventoryReportFilter = $activeInventoryReportFilter;
        
                    print('Requesting job Id from eBay...');
                    $startDownloadJobResponse = $exchangeService->startDownloadJob($startDownloadJobRequest);
                    print("Done\n");
        
                    if (isset($startDownloadJobResponse->errorMessage)) {
                        foreach ($startDownloadJobResponse->errorMessage->error as $error) {
                            printf(
                                "%s: %s\n\n",
                                $error->severity === BulkDataExchange\Enums\ErrorSeverity::C_ERROR ? 'Error' : 'Warning',
                                $error->message
                            );
                        }
                    }
        
                    if ($startDownloadJobResponse->ack !== 'Failure') {
                        printf(
                            "JobId [%s]\n",
                            $startDownloadJobResponse->jobId
                        );
        
                        /**
                         * STEP 2 - Poll the API until it reports that the job has been completed.
                         *
                         * Using the job ID returned from the previous step we repeatedly call getJobStatus until it reports that the job is complete.
                         * The response will include a file reference ID that can be used to download the completed report.
                         */
                        $getJobStatusRequest = new BulkDataExchange\Types\GetJobStatusRequest();
                        $getJobStatusRequest->jobId = $startDownloadJobResponse->jobId;
        
                        $done = false;
                        while (!$done) {
                            $getJobStatusResponse = $exchangeService->getJobStatus($getJobStatusRequest);
        
                            if (isset($getJobStatusResponse->errorMessage)) {
                                foreach ($getJobStatusResponse->errorMessage->error as $error) {
                                    printf(
                                        "%s: %s\n\n",
                                        $error->severity === BulkDataExchange\Enums\ErrorSeverity::C_ERROR ? 'Error' : 'Warning',
                                        $error->message
                                    );
                                }
                            }
        
                            if ($getJobStatusResponse->ack !== 'Failure') {
                                printf("Status is %s\n", $getJobStatusResponse->jobProfile[0]->jobStatus);
        
                                switch ($getJobStatusResponse->jobProfile[0]->jobStatus) {
                                    case BulkDataExchange\Enums\JobStatus::C_COMPLETED:
                                        $downloadFileReferenceId = $getJobStatusResponse->jobProfile[0]->fileReferenceId;
                                        $done = true;
                                        break;
                                    case BulkDataExchange\Enums\JobStatus::C_ABORTED:
                                    case BulkDataExchange\Enums\JobStatus::C_FAILED:
                                        $done = true;
                                        break;
                                    default:
                                        sleep(5);
                                        break;
                                }
                            } else {
                                $done = true;
                            }
                        }
                        
                        if (isset($downloadFileReferenceId)) {
                            /**
                            * STEP 3 - Download the job.
                            *
                            * Using the file reference ID from the previous step we can download the report.
                            */
                            $downloadFileRequest = new FileTransfer\Types\DownloadFileRequest();
                            $downloadFileRequest->fileReferenceId = $downloadFileReferenceId;
                            $downloadFileRequest->taskReferenceId = $startDownloadJobResponse->jobId;
        
                            print('Downloading the active inventory report...');
                            $downloadFileResponse = $transferService->downloadFile($downloadFileRequest);
                            print("Done\n");
        
                            if (isset($downloadFileResponse->errorMessage)) {
                                foreach ($downloadFileResponse->errorMessage->error as $error) {
                                    printf(
                                        "%s: %s\n\n",
                                        $error->severity === FileTransfer\Enums\ErrorSeverity::C_ERROR ? 'Error' : 'Warning',
                                        $error->message
                                    );
                                }
                            }
        
                            if ($downloadFileResponse->ack !== 'Failure') {
                                /**
                                * STEP 4 - Parse the results.
                                *
                                * The report is returned as a Zip archive attachment.
                                * Save the attachment and then unzip it to get the report.
                                */
                                if ($downloadFileResponse->hasAttachment()) {
                                    $attachment = $downloadFileResponse->attachment();
        
                                    $filename = $this->saveAttachment($attachment['data']);
                                    echo "----------fileName----------------".$filename."<br>";

                                    if ($filename !== false) {
                                        $xml = $this->unZipArchive($filename);
                                        if ($xml !== false) {
                                            $activeInventoryReport = $merchantData->activeInventoryReport($xml);
                                            //print_r($activeInventoryReport);
                                            if (isset($activeInventoryReport->Errors)) {
                                                foreach ($activeInventoryReport->Errors as $error) {
                                                    printf(
                                                        "%s: %s\n%s\n\n",
                                                        $error->SeverityCode === MerchantData\Enums\SeverityCodeType::C_ERROR ? 'Error' : 'Warning',
                                                        $error->ShortMessage,
                                                        $error->LongMessage
                                                    );
                                                }
                                            }
        
                                            if ($activeInventoryReport->Ack !== 'Failure') { 
                                                $kk = 1;  // dd(count($activeInventoryReport->SKUDetails));
                                                //for ($i=0; $i <= count($activeInventoryReport->SKUDetails) ; $i++) {   
                                                  //  $skuDetails =  $activeInventoryReport->SKUDetails[$i];
                                                foreach ($activeInventoryReport->SKUDetails as $skuDetails) {
                                                    set_time_limit(0);
                                                         
                                                    $itemID = $skuDetails->ItemID;

                                                    echo $itemID."<br>";
                                                    $endpoint  = 'https://api.ebay.com/ws/api.dll'; // URL to call
                                                    $headers = array(
                                                        'Content-Type: text/xml',
                                                        'X-EBAY-API-COMPATIBILITY-LEVEL:877',
                                                        'X-EBAY-API-DEV-NAME:'.$devID,
                                                        'X-EBAY-API-APP-NAME:'.$appID,
                                                        'X-EBAY-API-CERT-NAME:'.$certID,
                                                        'X-EBAY-API-SITEID:0',
                                                        'X-EBAY-API-CALL-NAME:GetItem'
                                                    );            
                                                    $xml = "<?xml version='1.0' encoding='utf-8'?>
                                                            <GetItemRequest xmlns='urn:ebay:apis:eBLBaseComponents'>
                                                            <RequesterCredentials>
                                                                <eBayAuthToken>".$channel_data->usertoken."</eBayAuthToken>
                                                            </RequesterCredentials>
                                                            <ItemID>".$itemID."</ItemID>
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
                                                    //dd($array_data);     
                                                   // dd($skuDetails);  
                                                    if(isset($array_data['Item'])) {
                                                       
                                                        $item       = $array_data['Item'];
                                                        $quantity   = $skuDetails->Quantity;
                                                        $country    = $item['Site'];
                                                        $online_shipping = $item['ShippingDetails']['ShippingServiceOptions']['ShippingServiceCost'];
                                                        echo $country."-------".$online_shipping."<br>";
                                                        if($country == "Germany") {
                                                            $country = "DE";
                                                        } else if($country == "Spain") {
                                                            $country = "ES";
                                                        } else if($country == "France") {
                                                            $country = "FR";
                                                        } else if($country == "Italy") {
                                                            $country = "IT";
                                                        }
        
                                                        if(isset($skuDetails->SKU)) {
                                                            $sku = $skuDetails->SKU;
                                                            echo $kk."--------------".$sku."<br>";
                                                            $kk++;
                                                            if(isset($skuDetails->Price->value)) {
                                                                $online_price = $skuDetails->Price->value;
                                                            } else {
                                                                $online_price = 0;
                                                            }
        
                                                            $product_data = Product::where('modelcode', substr($sku, 0, 5))->get()->first();
                                                            if ($product_data) {
                                                                $isExistPrice = Price::where(['channel_id'=>$channel_data->idchannel, 'country'=>$country,'sku'=>$sku])->first();
                                                                if ($isExistPrice) {
                                                                    Price::where(['channel_id'=>$channel_data->idchannel, 'country'=>$country,'sku'=>$sku])->update([
                                                                        'itemId'=> $itemID,
                                                                        'ebayActive'=>1,
                                                                        'online_price'=> $online_price,
                                                                        'country'=> $country,
                                                                        'shipping'=> $online_shipping,
                                                                        'online_shipping'=> $online_shipping,
                                                                        'online_quentity'=> $quantity,
                                                                        'last_update_shipping'=> date('Y-m-d H:i:s'),
                                                                        'last_update_qty_date'=> date('Y-m-d H:i:s'),
                                                                        'last_update_date'=> date('Y-m-d H:i:s'),
                                                                        'updated_date'=> date('Y-m-d H:i:s'),
                                                                    ]);
                                                                }else{
                                                                    if($product_data->virtualkit == "Yes") {
                                                                        $cost = 0;
                                                                        for($i=1; $i<10; $i++) {
                                                                            $item = "pcs".$i;
                                                                            $itemProductId  = "productid".$i;
                                                                            $productid      = $product_data->$itemProductId;
                                                                            if($product_data->$item != null && $product_data->$item > 0 && $product_data->$item != "" && $productid != "" && $productid != null) {
                                                                                $itemProduct = Product::where('modelcode', $productid)->first();
                                                                                if($itemProduct){
                                                                                    $cost += $itemProduct->price*$product_data->$item;
                                                                                }
                                                                            }
                                                                        }    
                                                                    } else {
                                                                        $cost = $product_data->price;
                                                                    }
                                                                    $insertArr = [
                                                                        'itemId'=> $itemID,
                                                                        'product_id'=>$product_data->productid,
                                                                        'channel_id'=>$channel_data->idchannel,
                                                                        'warehouse_id'=>$channel_data->warehouse,
                                                                        'platform_id'=>$channel_data->platformid,
                                                                        'sku'=>$sku,
                                                                        'ean'=>$product_data->ean,
                                                                        'asin'=>$product_data->asin,
                                                                        'cost'=>$cost,
                                                                        'created_date'=>date('Y-m-d H:i:s'),
                                                                        'price'=> $online_price,
                                                                        'online_price'=> $online_price,
                                                                        'country'=> $country,
                                                                        'shipping'=> $online_shipping,
                                                                        'online_shipping'=> $online_shipping,
                                                                        'online_quentity'=> $quantity,
                                                                        'last_update_shipping'=> date('Y-m-d H:i:s'),
                                                                        'last_update_qty_date'=> date('Y-m-d H:i:s'),
                                                                        'last_update_date'=> date('Y-m-d H:i:s'),
                                                                        'updated_date'=> date('Y-m-d H:i:s'),
                                                                    ];
                                                                    Price::insert($insertArr);
                                                                }
                                                            } else {
                                                                $isExistPrice = Price::where(['channel_id'=>$channel_data->idchannel, 'country'=>$country,'sku'=>$sku])->first();
                                                                if ($isExistPrice) {
                                                                    Price::where(['channel_id'=>$channel_data->idchannel, 'country'=>$country,'sku'=>$sku])->update([
                                                                        'itemId'=> $itemID,
                                                                        'ebayActive'=>1,
                                                                        'online_price'=> $online_price,
                                                                        'country'=> $country,
                                                                        'shipping'=> $online_shipping,
                                                                        'online_shipping'=> $online_shipping,
                                                                        'online_quentity'=> $quantity,
                                                                        'last_update_shipping'=> date('Y-m-d H:i:s'),
                                                                        'last_update_qty_date'=> date('Y-m-d H:i:s'),
                                                                        'last_update_date'=> date('Y-m-d H:i:s'),
                                                                        'updated_date'=> date('Y-m-d H:i:s'),
                                                                    ]);
                                                                } else {
                                                                    $none_product = NoneProduct::where(['channelId'=>$channel_data->idchannel,'sku'=>$sku])->first();
                                                                    if ($none_product) {
                                                                        if($none_product->related_modelcode != null || $none_product->related_modelcode != "") {
                                                                            $insertArr = [
                                                                                'itemId'=> $itemID,
                                                                                'product_id'=>$none_product->related_modelcode,
                                                                                'channel_id'=>$channel_data->idchannel,
                                                                                'warehouse_id'=>$channel_data->warehouse,
                                                                                'platform_id'=>$channel_data->platformid,
                                                                                'sku'=>$sku,
                                                                                'created_date'=>date('Y-m-d H:i:s'),
                                                                                'price'=> $online_price,
                                                                                'online_price'=> $online_price,
                                                                                'country'=> $country,
                                                                                'shipping'=> $online_shipping,
                                                                                'online_shipping'=> $online_shipping,
                                                                                'online_quentity'=> $quantity,
                                                                                'last_update_shipping'=> date('Y-m-d H:i:s'),
                                                                                'last_update_date'=> date('Y-m-d H:i:s'),
                                                                                'updated_date'=> date('Y-m-d H:i:s'),                                                            
                                                                            ];
                                                                            Price::insert($insertArr);
                                                                        }else{
                                                                            NoneProduct::where(['channelId'=>$channel_data->idchannel,'sku'=>$sku])->update(['online_quantity'=>$quantity,'online_price'=>$online_price,'itemId'=>$itemID,'status'=>0]);
                                                                        }
                                                                    } else {
                                                                        NoneProduct::insert(['channelId'=>$channel_data->idchannel,'online_quantity'=>$quantity,'online_price'=>$online_price,'itemId'=>$itemID,'sku'=>$sku]);
                                                                    }
                                                                }
                                                            }
                                                        } else {
                                                            if(isset($skuDetails->Variations)) {
                                                                $variations = $skuDetails->Variations;
                                                                foreach($variations->Variation as $variation) {
                                                                    $quantity   = $variation->Quantity;
                                                                    $sku        = $variation->SKU;
                                                                    echo $kk."--------------".$sku."<br>";
                                                                    $kk++;
                                                                    if(isset($variation->Price->value)) {
                                                                        $online_price      = $variation->Price->value;
                                                                    } else {
                                                                        $online_price      = 0;
                                                                    }
                                                                    $product_data = Product::where('modelcode', substr($sku, 0, 5))->get()->first();
                                                                    if ($product_data) {
                                                                        $isExistPrice = Price::where(['channel_id'=>$channel_data->idchannel, 'country'=>$country,'sku'=>$sku])->first();
                                                                        if ($isExistPrice) {
                                                                            Price::where(['channel_id'=>$channel_data->idchannel, 'country'=>$country,'sku'=>$sku])->update([
                                                                                'itemId'=> $itemID,
                                                                                'ebayActive'=>1,
                                                                                'online_price'=> $online_price,
                                                                                'country'=> $country,
                                                                                'shipping'=> $online_shipping,
                                                                                'online_shipping'=> $online_shipping,
                                                                                'online_quentity'=> $quantity,
                                                                                'last_update_shipping'=> date('Y-m-d H:i:s'),
                                                                                'last_update_qty_date'=> date('Y-m-d H:i:s'),
                                                                                'last_update_date'=> date('Y-m-d H:i:s'),
                                                                                'updated_date'=> date('Y-m-d H:i:s'),
                                                                            ]);
                                                                        }else{
                                                                            if($product_data->virtualkit == "Yes") {
                                                                                $cost = 0;
                                                                                for($i=1; $i<10; $i++) {
                                                                                    $item = "pcs".$i;
                                                                                    $itemProductId  = "productid".$i;
                                                                                    $productid      = $product_data->$itemProductId;
                                                                                    if($product_data->$item != null && $product_data->$item > 0 && $product_data->$item != "" && $productid != "" && $productid != null) {
                                                                                        $itemProduct = Product::where('modelcode', $productid)->first();
                                                                                        if($itemProduct){
                                                                                            $cost += $itemProduct->price*$product_data->$item;
                                                                                        }
                                                                                    }
                                                                                }    
                                                                            } else {
                                                                                $cost = $product_data->price;
                                                                            }
                                                                            $insertArr = [
                                                                                'itemId'=> $itemID,
                                                                                'product_id'=>$product_data->productid,
                                                                                'channel_id'=>$channel_data->idchannel,
                                                                                'warehouse_id'=>$channel_data->warehouse,
                                                                                'platform_id'=>$channel_data->platformid,
                                                                                'sku'=>$sku,
                                                                                'ean'=>$product_data->ean,
                                                                                'asin'=>$product_data->asin,
                                                                                'cost'=>$cost,
                                                                                'created_date'=>date('Y-m-d H:i:s'),
                                                                                'price'=> $online_price,
                                                                                'online_price'=> $online_price,
                                                                                'country'=> $country,
                                                                                'shipping'=> $online_shipping,
                                                                                'online_shipping'=> $online_shipping,
                                                                                'online_quentity'=> $quantity,
                                                                                'last_update_shipping'=> date('Y-m-d H:i:s'),
                                                                                'last_update_qty_date'=> date('Y-m-d H:i:s'),
                                                                                'last_update_date'=> date('Y-m-d H:i:s'),
                                                                                'updated_date'=> date('Y-m-d H:i:s'),
                                                                            ];
                                                                            Price::insert($insertArr);
                                                                        }
                                                                    } else {
                                                                        $isExistPrice = Price::where(['channel_id'=>$channel_data->idchannel, 'country'=>$country,'sku'=>$sku])->first();
                                                                        if ($isExistPrice) {
                                                                            Price::where(['channel_id'=>$channel_data->idchannel, 'country'=>$country,'sku'=>$sku])->update([
                                                                                'itemId'=> $itemID,
                                                                                'ebayActive'=>1,
                                                                                'online_price'=> $online_price,
                                                                                'country'=> $country,
                                                                                'shipping'=> $online_shipping,
                                                                                'online_shipping'=> $online_shipping,
                                                                                'online_quentity'=> $quantity,
                                                                                'last_update_shipping'=> date('Y-m-d H:i:s'),
                                                                                'last_update_qty_date'=> date('Y-m-d H:i:s'),
                                                                                'last_update_date'=> date('Y-m-d H:i:s'),
                                                                                'updated_date'=> date('Y-m-d H:i:s'),
                                                                            ]);
                                                                        } else {
                                                                            $none_product = NoneProduct::where(['channelId'=>$channel_data->idchannel,'sku'=>$sku])->first();
                                                                            if ($none_product) {
                                                                                if($none_product->related_modelcode != null || $none_product->related_modelcode != "") {
                                                                                    $insertArr = [
                                                                                        'itemId'=> $itemID,
                                                                                        'product_id'=>$none_product->related_modelcode,
                                                                                        'channel_id'=>$channel_data->idchannel,
                                                                                        'warehouse_id'=>$channel_data->warehouse,
                                                                                        'platform_id'=>$channel_data->platformid,
                                                                                        'sku'=>$sku,
                                                                                        'created_date'=>date('Y-m-d H:i:s'),
                                                                                        'price'=> $online_price,
                                                                                        'online_price'=> $online_price,
                                                                                        'country'=> $country,
                                                                                        'shipping'=> $online_shipping,
                                                                                        'online_shipping'=> $online_shipping,
                                                                                        'online_quentity'=> $quantity,
                                                                                        'last_update_shipping'=> date('Y-m-d H:i:s'),
                                                                                        'last_update_date'=> date('Y-m-d H:i:s'),
                                                                                        'updated_date'=> date('Y-m-d H:i:s'),                                                            
                                                                                    ];
                                                                                    Price::insert($insertArr);
                                                                                }else{
                                                                                    NoneProduct::where(['channelId'=>$channel_data->idchannel,'sku'=>$sku])->update(['online_quantity'=>$quantity,'online_price'=>$online_price,'itemId'=>$itemID,'status'=>0]);
                                                                                }
                                                                            } else {
                                                                                NoneProduct::insert(['channelId'=>$channel_data->idchannel,'online_quantity'=>$quantity,'online_price'=>$online_price,'itemId'=>$itemID,'sku'=>$sku]);
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }
                                                        
                                                    } else {
                                                        print_r($array_data);
                                                    }
        
                                                    
                                                    echo "<br>";
                                                    
                                                    // printf("Item ID %s \n", $skuDetails->ItemID);
                                                }
                                            }
                                        }
                                    }
        
                                } else {
                                    print("Unable to locate attachment\n\n");
                                }
                            }
                        }
                    }
                }
            } 
            echo "END";
        }else{
            echo "No channels found";
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


