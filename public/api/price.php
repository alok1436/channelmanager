<?php 
     ini_set('max_execution_time', '300');
     ini_set('memory_limit', -1);
    // ini_set('mysql.connect_timeout', 1500000);
    // ini_set('default_socket_timeout', 1500000);
    // ini_set('mysql.reconnect', 1);
    // ini_set('mysql.wait_timeout', 1500000);
    // ini_set('wait_timeout', 1500000);
    // ini_set('mysql.max_allowed_packet', '2024MB'); 
    // ini_set('innodb_lock_wait_timeout', 1500000); 
    // ini_set('mysql.innodb_lock_wait_timeout', 1500000); 

    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    require(__DIR__ .'/config.php');
    require(__DIR__ .'/vendor/autoload.php');
    require(__DIR__.'/ebay/vendor/autoload.php');
    require(__DIR__.'/ebay/large-merchant-services/utils.php');
    
    use \DTS\eBaySDK\Sdk;
    use \DTS\eBaySDK\Constants;
    use \DTS\eBaySDK\FileTransfer;
    use \DTS\eBaySDK\BulkDataExchange;
    use \DTS\eBaySDK\MerchantData;
    use \DTS\eBaySDK\Inventory\Services;
    use \DTS\eBaySDK\Inventory\Types;
    use \DTS\eBaySDK\Inventory\Enums;

    function pr($array){

            echo '<pre>'; print_r($array); echo '</pre>';  exit();
    }
    $sql        = "SELECT * FROM product";
    $result     = mysqli_query($conn, $sql);
    $products   = array();
    while($row  = mysqli_fetch_object($result)){
        $products[] = $row;
    } 

    $non_existing_products = array();
    $channelforcheckprice = '';
    if(isset($_GET['channelforcheckprice']) && $_GET['channelforcheckprice'] != "") {
        $channelforcheckprice = $_GET['channelforcheckprice'];
        $sql        = "SELECT * FROM channel WHERE idchannel=".$channelforcheckprice;
        $result     = mysqli_query($conn, $sql);
    } else {
        $sql        = "SELECT * FROM channel";
        $result     = mysqli_query($conn, $sql);
    }

    
    $channels   = array();
    while($row  = mysqli_fetch_object($result)){
        $channels[] = $row;
    } 
    
    if(isset($channels[0]) && $channelforcheckprice !='' && intval($channelforcheckprice) > 0 && substr($channels[0]->shortname, 0, 2) == 'AM'){
        ////AMAZON
        $asinArr                = [];
        $productIdArr           = [];
        $productEANArr          = [];
        $productSKUArr          = [];
        $productKitArr          = [];
        $existingProductFlagArr = [];
        $numProducts            = count($products);
        $count                  = 0;
        $lastAsin               = "";
    
        $test = 0;
    
        foreach($products as $product_data) {
            $count++;        
            if(count($asinArr) < 10) {
                array_push($asinArr                 , $product_data->asin);
                array_push($productIdArr            , $product_data->productid);
                array_push($productKitArr           , $product_data->virtualkit);
                array_push($productEANArr           , $product_data->ean);
                array_push($productSKUArr           , $product_data->sku);                       
            }
            
            $sql = "SELECT * FROM tbl_none_product WHERE sku='".$product_data->sku."'";
            $result = mysqli_query($conn, $sql);
            if($result->num_rows > 0) {
                $sql = "DELETE FROM tbl_none_product WHERE sku='".$product_data->sku."'";
                mysqli_query($conn, $sql);
            }
    
            if(count($asinArr) == 10 || $count == $numProducts) {
                $getDataFlag = 0;
                foreach($channels as $channel_data) {
                    $online_price       = 0.00;
                    $online_shipping    = 0.00;
                    
                    foreach($asinArr as $asinitem) {
                        array_push($existingProductFlagArr  , 0);
                    }
                
                    if($channel_data->aws_acc_key_id!='' && $channel_data->aws_secret_key_id!='' && $channel_data->merchant_id!='' && $channel_data->market_place_id!='' && $channel_data->mws_auth_token!=''){

                        mysqli_query($conn, "UPDATE prices SET ebayActive=0 WHERE channel_id=".$channel_data->idchannel."");

                        $marketplaceIds = ['A13V1IB3VIYZZH', 'A1F83G8C2ARO7P', 'A1PA6795UKMFR9', 'A1RKKUPIHCS9HS', 'APJ6JRA9NG5V4'];
                        $countryArr     = ['FR', 'UK', 'DE', 'ES', 'IT'];
                        $existingProductFlag = 0;
                        for($k=0; $k<count($marketplaceIds); $k++) {
                            $client = new MCS\MWSClient([
                                'Marketplace_Id'    => $marketplaceIds[$k],
                                'Seller_Id'         => $channel_data->merchant_id,
                                'Access_Key_ID'     => $channel_data->aws_acc_key_id,
                                'Secret_Access_Key' => $channel_data->aws_secret_key_id,
                                'MWSAuthToken'      => $channel_data->mws_auth_token
                            ]);
                            
                            
                            sleep(1);
                            try { 
                                $prices = $client->GetMyPriceForASIN($asinArr, $ItemCondition = null);
                                echo '<pre>'; print_r($prices); echo '</pre>'; ///exit();
                                for($i=0; $i<count($asinArr); $i++) {     
                                    $asin       = $asinArr[$i];
                                    $ean        = $productEANArr[$i];
                                    $sku        = $productSKUArr[$i];
                                    $productid  = $productIdArr[$i];   
                                                                                                        
                                    if(isset($prices[$asin]) && $prices[$asin] != "" && isset($prices[$asin]['BuyingPrice'])){
                                        $lastAsin                   = $asin;
                                        $online_price               = $prices[$asin]['BuyingPrice']['ListingPrice']['Amount'];
                                        $online_price_curr          = $prices[$asin]['BuyingPrice']['ListingPrice']['CurrencyCode'];
                                        $online_shipping            = $prices[$asin]['BuyingPrice']['Shipping']['Amount'];
                                        $online_shipping_curr       = $prices[$asin]['BuyingPrice']['Shipping']['CurrencyCode'];
                                        $online_quantity            = $prices[$asin]['BuyingPrice']['Shipping']['CurrencyCode'];
                                        
                                        $sql    = "SELECT * FROM prices WHERE channel_id=".$channel_data->idchannel." AND country='".$countryArr[$k]."' AND asin='".$asin."'";
                                        $result = mysqli_query($conn, $sql);
                                        $existingProductFlagArr[$i] = 1;
                                        $current_product = mysqli_fetch_object(mysqli_query($conn, "SELECT * FROM product WHERE productid=".$productid));
                                        if ($result->num_rows == 0) {
                                            if($productKitArr[$i] == "Yes") {
                                                $cost = 0;
                                                for($t=1; $t<10; $t++) {
                                                    $item = "pcs".$t;
                                                    $itemProductId  = "productid".$t;
                                                    $itemProductId  = $current_product->$itemProductId;
                                                    if($current_product->$item != null && $current_product->$item > 0 && $current_product->$item != "" && $current_product != "" && $current_product != null) {
                                                        $itemProduct = mysqli_fetch_object(mysqli_query($conn, "SELECT * FROM product WHERE modelcode=".$itemProductId));
                                                        if(!empty($itemProduct)){
                                                            $cost += $itemProduct->price*$current_product->$item;
                                                        }
                                                    }
                                                }
                                                $sql    = "INSERT INTO prices SET cost=".$cost.", product_id='".$productid."', country='".$countryArr[$k]."', online_price = ".$online_price.", online_shipping= ".$online_shipping.", shipping=".$online_shipping.", last_update_date='".date('Y-m-d H:i:s')."', last_update_shipping='".date('Y-m-d H:i:s')."', channel_id ='".$channel_data->idchannel."',warehouse_id ='".$channel_data->warehouse."',platform_id='".$channel_data->platformid."' ,sku ='".$sku."',ean ='".$ean."',asin ='".$asin."', price='".$online_price."', ebayActive=1 ,created_date='".date('Y-m-d H:i:s')."',updated_date='".date('Y-m-d H:i:s')."'"; 
                                                echo $sql."--------1<br>";
                                            } else {
                                                $sql    = "INSERT INTO prices SET cost=".$current_product->price.", product_id='".$productid."', country='".$countryArr[$k]."', online_price = ".$online_price.", online_shipping= ".$online_shipping.", shipping=".$online_shipping.", last_update_date='".date('Y-m-d H:i:s')."', last_update_shipping='".date('Y-m-d H:i:s')."', channel_id ='".$channel_data->idchannel."',warehouse_id ='".$channel_data->warehouse."',platform_id='".$channel_data->platformid."' ,sku ='".$sku."',ean ='".$ean."',asin ='".$asin."', price='".$online_price."', ebayActive=1 ,created_date='".date('Y-m-d H:i:s')."',updated_date='".date('Y-m-d H:i:s')."'"; 
                                                echo $sql."--------2<br>";
                                            }
                                            $result = mysqli_query($conn, $sql);
                                        } else {
                                            $sql    = "UPDATE prices SET online_price = ".$online_price.", online_shipping= ".$online_shipping.", price='".$online_price."', shipping=".$online_shipping.", last_update_date='".date('Y-m-d H:i:s')."', last_update_shipping='".date('Y-m-d H:i:s')."', updated_date='".date('Y-m-d H:i:s')."', ebayActive=1 WHERE channel_id=".$channel_data->idchannel." AND country='".$countryArr[$k]."' AND asin='".$asin."'";
                                            echo $sql."--------3<br>";
                                            $result = mysqli_query($conn, $sql);
                                        }
    
                                        echo "Get price for product $sku.<br>";
                                    }
                                }
                            }
                            catch (\Exception $e) { 
                                echo $e->getMessage();
                            }
                        }
                        
                    }
                }
                $asinArr                = [];
                $productIdArr           = [];
                $productEANArr          = [];
                $productKitArr          = [];
                $productSKUArr          = [];
            }
        }
    }else{
//echo '<pre>'; print_r($channels); echo '</pre>'; exit();
    ////EBAY
    foreach($channels as $channel_data) {
        set_time_limit(0);
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
            $sql = "UPDATE prices SET ebayActive=0 WHERE channel_id = ".$channel_data->idchannel;
            mysqli_query($conn, $sql);

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
            
            //pr($sdk);

            

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
//pr($startDownloadJobResponse);
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

                            $filename = saveAttachment($attachment['data']);
                            echo "----------fileName----------------".$filename."<br>";
                           // echo '<pre>'; print_r($filename); echo '</pre>'; exit();
                            if ($filename !== false) {
                                $xml = unZipArchive($filename);
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
                                        $kk = 1;                                       
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

                                                    $sql            = "SELECT * FROM product WHERE modelcode='".substr($sku, 0, 5)."'";
                                                    $result         = mysqli_query($conn, $sql);
                                                     $product_data   = mysqli_fetch_object($result);
                                                    //echo '<pre>'; print_r($product_data); echo '</pre>';
                                                    if ($result->num_rows > 0) {
                                                        $product_data   = mysqli_fetch_object($result);
                                                        
                                                        $sql            = "SELECT * FROM prices WHERE channel_id=".$channel_data->idchannel." AND country='".$country."' AND sku='".$sku."'";
                                                        
                                                        $result = mysqli_query($conn, $sql);
                                                        if ($result->num_rows > 0) {
                                                            $sql    = "UPDATE prices SET itemId = '".$itemID."', ebayActive=1, online_price = ".$online_price.", country='".$country."', shipping = ".$online_shipping.", online_shipping = ".$online_shipping.", online_quentity ='".$quantity."', last_update_shipping='".date('Y-m-d H:i:s')."', last_update_qty_date='".date('Y-m-d H:i:s')."', last_update_date='".date('Y-m-d H:i:s')."', updated_date='".date('Y-m-d H:i:s')."' WHERE channel_id=".$channel_data->idchannel." AND country='".$country."' AND sku='".$sku."'";
                                                            //if($product_data->productid == 21) {
                                                                echo $sql."<br>";
                                                            //}
                                                            mysqli_query($conn, $sql);
                                                        } else {
                                                            if($product_data->virtualkit == "Yes") {
                                                                $cost = 0;
                                                                for($i=1; $i<10; $i++) {
                                                                    $item = "pcs".$i;
                                                                    $itemProductId  = "productid".$i;
                                                                    $productid      = $product_data->$itemProductId;
                                                                    if($product_data->$item != null && $product_data->$item > 0 && $product_data->$item != "" && $productid != "" && $productid != null) {
                                                                        $itemProduct = mysqli_fetch_object(mysqli_query($conn, "SELECT * FROM product WHERE modelcode=".$productid));
                                                                        $cost += $itemProduct->price*$product_data->$item;
                                                                    }
                                                                }
                    
                                                                $sql    = "INSERT INTO prices SET itemId = '".$itemID."', country='".$country."', shipping = ".$online_shipping.", last_update_shipping='".date('Y-m-d H:i:s')."', online_shipping = ".$online_shipping.", product_id=".$product_data->productid.", channel_id =".$channel_data->idchannel.", last_update_date='".date('Y-m-d H:i:s')."', last_update_qty_date='".date('Y-m-d H:i:s')."', online_quentity =".$quantity.", warehouse_id ='".$channel_data->warehouse."', platform_id='".$channel_data->platformid."', online_price='".$online_price."', price ='".$online_price."', sku ='".$sku."', ean ='".$product_data->ean."', asin ='".$product_data->asin."', cost='".$cost."',created_date='".date('Y-m-d H:i:s')."',updated_date='".date('Y-m-d H:i:s')."'"; 
                                                            } else {
                                                                $sql    = "INSERT INTO prices SET itemId = '".$itemID."', country='".$country."', shipping = ".$online_shipping.", last_update_shipping='".date('Y-m-d H:i:s')."', online_shipping = ".$online_shipping.", product_id=".$product_data->productid.", channel_id =".$channel_data->idchannel.", last_update_date='".date('Y-m-d H:i:s')."', last_update_qty_date='".date('Y-m-d H:i:s')."', online_quentity =".$quantity.", warehouse_id ='".$channel_data->warehouse."', platform_id='".$channel_data->platformid."', online_price='".$online_price."', price ='".$online_price."', sku ='".$sku."', ean ='".$product_data->ean."', asin ='".$product_data->asin."', cost='".$product_data->price."',created_date='".date('Y-m-d H:i:s')."',updated_date='".date('Y-m-d H:i:s')."'"; 
                                                            }
                                                            mysqli_query($conn, $sql);
                                                        }
                                                    } else {
                                                        $sql    = "SELECT * FROM prices WHERE channel_id=".$channel_data->idchannel." AND country='".$country."' AND sku='".$sku."'";
                                                        $result = mysqli_query($conn, $sql);
                                                        if ($result->num_rows > 0) {
                                                            $sql    = "UPDATE prices SET itemId = '".$itemID."', ebayActive=1, shipping = ".$online_shipping.", country='".$country."', online_price = ".$online_price.", online_shipping = ".$online_shipping.", last_update_shipping='".date('Y-m-d H:i:s')."', online_quentity ='".$quantity."', last_update_qty_date='".date('Y-m-d H:i:s')."', last_update_date='".date('Y-m-d H:i:s')."', updated_date='".date('Y-m-d H:i:s')."' WHERE channel_id=".$channel_data->idchannel." AND country='".$country."' AND sku='".$sku."'";
                                                            mysqli_query($conn, $sql);
                                                        } else {
                                                            $sql            = "SELECT * FROM tbl_none_product WHERE channelId=".$channel_data->idchannel." AND sku='".$sku."'";
                                                            $result         = mysqli_query($conn, $sql);
                                                            if ($result->num_rows > 0) {
                                                                $none_product   = mysqli_fetch_object($result);
                                                                if($none_product->related_modelcode != null || $none_product->related_modelcode != "") {
                                                                    $sql    = "INSERT INTO prices SET itemId = '".$itemID."', shipping = ".$online_shipping.", online_shipping = ".$online_shipping.", country='".$country."', product_id=".$none_product->related_modelcode.", last_update_shipping='".date('Y-m-d H:i:s')."', last_update_date='".date('Y-m-d H:i:s')."', channel_id =".$channel_data->idchannel.", online_quentity =".$quantity.", warehouse_id ='".$channel_data->warehouse."', platform_id='".$channel_data->platformid."', online_price='".$online_price."', price ='".$online_price."', sku ='".$sku."', created_date='".date('Y-m-d H:i:s')."', updated_date='".date('Y-m-d H:i:s')."'"; 
                                                                    mysqli_query($conn, $sql);
                                                                } else {
                                                                    $sql    = "UPDATE tbl_none_product SET online_quantity ='".$quantity."', online_price='".$online_price."', itemId='".$itemID."' , status= 0 WHERE channelId=".$channel_data->idchannel." AND sku='".$sku."'";
                                                                    mysqli_query($conn, $sql);
                                                                }
                                                            } else {
                                                                $sql    = "INSERT INTO tbl_none_product SET channelId ='".$channel_data->idchannel."', online_quantity ='".$quantity."', online_price='".$online_price."', itemId='".$itemID."', sku ='".$sku."'"; 
                                                                mysqli_query($conn, $sql);
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

                                                            $sql            = "SELECT * FROM product WHERE modelcode='".substr($sku, 0, 5)."'";
                                                            $result         = mysqli_query($conn, $sql);
                                                            if ($result->num_rows > 0) {
                                                                $product_data   = mysqli_fetch_object($result);
                                                                $sql            = "SELECT * FROM prices WHERE channel_id=".$channel_data->idchannel." AND country='".$country."' AND sku='".$sku."'";
                                                                $result = mysqli_query($conn, $sql);
                                                                
                                                                if ($result->num_rows > 0) {
                                                                    $sql    = "UPDATE prices SET itemId = '".$itemID."', ebayActive=1, online_price = ".$online_price.", country='".$country."', shipping = ".$online_shipping.", online_shipping = ".$online_shipping.", online_quentity ='".$quantity."', last_update_shipping='".date('Y-m-d H:i:s')."', last_update_qty_date='".date('Y-m-d H:i:s')."', last_update_date='".date('Y-m-d H:i:s')."', updated_date='".date('Y-m-d H:i:s')."' WHERE channel_id=".$channel_data->idchannel." AND country='".$country."' AND sku='".$sku."'";
                                                                    mysqli_query($conn, $sql);
                                                                } else {
                                                                    if($product_data->virtualkit == "Yes") {
                                                                        $cost = 0;
                                                                        for($i=1; $i<10; $i++) {
                                                                            $item = "pcs".$i;
                                                                            $itemProductId  = "productid".$i;
                                                                            $productid      = $product_data->$itemProductId;
                                                                            if($product_data->$item != null && $product_data->$item > 0 && $product_data->$item != "" && $productid != "" && $productid != null) {
                                                                                $itemProduct = mysqli_fetch_object(mysqli_query($conn, "SELECT * FROM product WHERE modelcode=".$productid));
                                                                                $cost += $itemProduct->price*$product_data->$item;
                                                                            }
                                                                        }
                            
                                                                        $sql    = "INSERT INTO prices SET itemId = '".$itemID."', last_update_shipping='".date('Y-m-d H:i:s')."', country='".$country."', shipping = ".$online_shipping.", online_shipping = ".$online_shipping.", product_id=".$product_data->productid.", channel_id =".$channel_data->idchannel.", last_update_date='".date('Y-m-d H:i:s')."', last_update_qty_date='".date('Y-m-d H:i:s')."', online_quentity =".$quantity.", warehouse_id ='".$channel_data->warehouse."', platform_id='".$channel_data->platformid."', online_price='".$online_price."', price ='".$online_price."', sku ='".$sku."', ean ='".$product_data->ean."', asin ='".$product_data->asin."', cost='".$cost."',created_date='".date('Y-m-d H:i:s')."',updated_date='".date('Y-m-d H:i:s')."'"; 
                                                                    } else {
                                                                        $sql    = "INSERT INTO prices SET itemId = '".$itemID."', last_update_shipping='".date('Y-m-d H:i:s')."', country='".$country."', shipping = ".$online_shipping.", online_shipping = ".$online_shipping.", product_id=".$product_data->productid.", channel_id =".$channel_data->idchannel.", last_update_date='".date('Y-m-d H:i:s')."', last_update_qty_date='".date('Y-m-d H:i:s')."', online_quentity =".$quantity.", warehouse_id ='".$channel_data->warehouse."', platform_id='".$channel_data->platformid."', online_price='".$online_price."', price ='".$online_price."', sku ='".$sku."', ean ='".$product_data->ean."', asin ='".$product_data->asin."', cost='".$product_data->price."',created_date='".date('Y-m-d H:i:s')."',updated_date='".date('Y-m-d H:i:s')."'"; 
                                                                    }
                                                                    mysqli_query($conn, $sql);
                                                                }
                                                            } else {
                                                                $sql    = "SELECT * FROM prices WHERE channel_id=".$channel_data->idchannel." AND country='".$country."' AND sku='".$sku."'";
                                                                $result = mysqli_query($conn, $sql);
                                                                if ($result->num_rows > 0) {
                                                                    $sql    = "UPDATE prices SET itemId = '".$itemID."', ebayActive=1, online_price = ".$online_price.", country='".$country."', online_shipping = ".$online_shipping.", shipping = ".$online_shipping.", last_update_shipping='".date('Y-m-d H:i:s')."', online_quentity ='".$quantity."', last_update_qty_date='".date('Y-m-d H:i:s')."', last_update_date='".date('Y-m-d H:i:s')."', updated_date='".date('Y-m-d H:i:s')."' WHERE channel_id=".$channel_data->idchannel." AND country='".$country."' AND sku='".$sku."'";
                                                                    mysqli_query($conn, $sql);
                                                                } else {
                                                                    $sql            = "SELECT * FROM tbl_none_product WHERE channelId=".$channel_data->idchannel." AND sku='".$sku."'";
                                                                    $result         = mysqli_query($conn, $sql);
                                                                    if ($result->num_rows > 0) {
                                                                        $none_product   = mysqli_fetch_object($result);
                                                                        if($none_product->related_modelcode != null || $none_product->related_modelcode != "") {
                                                                            $sql    = "INSERT INTO prices SET itemId = '".$itemID."', country='".$country."', online_shipping = ".$online_shipping.", shipping = ".$online_shipping.", product_id=".$none_product->related_modelcode.", last_update_shipping='".date('Y-m-d H:i:s')."', last_update_date='".date('Y-m-d H:i:s')."', channel_id =".$channel_data->idchannel.", online_quentity =".$quantity.", warehouse_id ='".$channel_data->warehouse."', platform_id='".$channel_data->platformid."', online_price='".$online_price."', price ='".$online_price."', sku ='".$sku."', created_date='".date('Y-m-d H:i:s')."', updated_date='".date('Y-m-d H:i:s')."'"; 
                                                                            mysqli_query($conn, $sql);
                                                                        } else {
                                                                            $sql    = "UPDATE tbl_none_product SET online_quantity ='".$quantity."', online_price='".$online_price."', itemId='".$itemID."' , status= 0 WHERE channelId=".$channel_data->idchannel." AND sku='".$sku."'";
                                                                            mysqli_query($conn, $sql);
                                                                        }
                                                                    } else {
                                                                        $sql    = "INSERT INTO tbl_none_product SET channelId ='".$channel_data->idchannel."', online_quantity ='".$quantity."', online_price='".$online_price."', itemId='".$itemID."', sku ='".$sku."'"; 
                                                                        mysqli_query($conn, $sql);
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

                                    // if(isset($array_data['ActiveList']['ItemArray']) && $array_data['ActiveList']['ItemArray'] != "") {
                                    //     if(isset($array_data['ActiveList']['ItemArray']['Item'])) {
                                    //         foreach($array_data['ActiveList']['ItemArray']['Item'] as $item) {
                                    //             $itemID         = $item['ItemID'];
                                    //             $online_price   = $item['SellingStatus']['CurrentPrice'];
                                    //             $quantity       = $item['QuantityAvailable'];
                                    //             if(isset($item['SKU'])) {
                                    //                 $sku                    = $item['SKU'];
                                    //                 $online_shipping        = $item['ShippingDetails']['ShippingServiceOptions']['ShippingServiceCost'];
                                    //                 $shippingprofileId      = $item['SellerProfiles']['SellerShippingProfile']['ShippingProfileID'];
                                    //                 $reuturnprofileId       = $item['SellerProfiles']['SellerReturnProfile']['ReturnProfileID'];
                                    //                 $shippingprofileName    = $item['SellerProfiles']['SellerShippingProfile']['ShippingProfileName'];
                    
                                    //                 $countrySQL = "SELECT * FROM tbl_return WHERE returnId='".$reuturnprofileId."' AND channelId=".$channel_data->idchannel;
                                    //                 $countryResult = mysqli_query($conn, $countrySQL);
                                    //                 if ($countryResult->num_rows > 0) {
                                    //                     $countryRow = mysqli_fetch_object($countryResult);
                                    //                     $country    = $countryRow->country;
                                    //                 } else {                                    
                                    //                     $country    = "";
                                    //                     if($noreuturnprofileId != $reuturnprofileId) {
                                    //                         $noreuturnprofileId = $reuturnprofileId;
                                    //                         echo "Warning: No country for ".$reuturnprofileId."<br>";
                                    //                         echo $shippingprofileId."-----------".$reuturnprofileId."-----------".$shippingprofileName."<br>";
                                    //                         $warnmessage = "Warning: No country for ".$reuturnprofileId." in ".$channel_data->shortname;
                                    //                         $sql = "INSERT INTO tbl_open_activities SET dateTime='".date('Y-m-d H:i:s')."', issues='".$warnmessage."'";
                                    //                         mysqli_query($conn, $sql);
                                    //                     }
                                    //                 }
                                                    
                                    //                 $sql            = "SELECT * FROM product WHERE modelcode='".substr($sku, 0, 5)."'";
                                    //                 $result         = mysqli_query($conn, $sql);
                                    //                 if ($result->num_rows > 0) {
                                    //                     $product_data   = mysqli_fetch_object($result);
                                                        
                                    //                     $sql            = "SELECT * FROM prices WHERE channel_id=".$channel_data->idchannel." AND country='".$country."' AND sku='".$sku."'";
                                                        
                                    //                     $result = mysqli_query($conn, $sql);
                                    //                     if($product_data->productid == 21) {
                                    //                         echo $sql."<br>";
                                    //                         echo $result->num_rows."<br>";
                                    //                     }
                                    //                     if ($result->num_rows > 0) {
                                    //                         $sql    = "UPDATE prices SET itemId = '".$itemID."', ebayActive=1, online_price = ".$online_price.", country='".$country."', shipping = ".$online_shipping.", online_shipping = ".$online_shipping.", online_quentity ='".$quantity."', last_update_shipping='".date('Y-m-d H:i:s')."', last_update_qty_date='".date('Y-m-d H:i:s')."', last_update_date='".date('Y-m-d H:i:s')."', updated_date='".date('Y-m-d H:i:s')."' WHERE channel_id=".$channel_data->idchannel." AND country='".$country."' AND sku='".$sku."'";
                                    //                         if($product_data->productid == 21) {
                                    //                             echo $sql."<br>";
                                    //                         }
                                    //                         mysqli_query($conn, $sql);
                                    //                     } else {
                                    //                         if($product_data->virtualkit == "Yes") {
                                    //                             $cost = 0;
                                    //                             for($i=1; $i<10; $i++) {
                                    //                                 $item = "pcs".$i;
                                    //                                 $itemProductId  = "productid".$i;
                                    //                                 $productid      = $product_data->$itemProductId;
                                    //                                 if($product_data->$item != null && $product_data->$item > 0 && $product_data->$item != "" && $productid != "" && $productid != null) {
                                    //                                     $itemProduct = mysqli_fetch_object(mysqli_query($conn, "SELECT * FROM product WHERE modelcode=".$productid));
                                    //                                     $cost += $itemProduct->price*$product_data->$item;
                                    //                                 }
                                    //                             }
                    
                                    //                             $sql    = "INSERT INTO prices SET itemId = '".$itemID."', country='".$country."', shipping = ".$online_shipping.", last_update_shipping='".date('Y-m-d H:i:s')."', online_shipping = ".$online_shipping.", product_id=".$product_data->productid.", channel_id =".$channel_data->idchannel.", last_update_date='".date('Y-m-d H:i:s')."', last_update_qty_date='".date('Y-m-d H:i:s')."', online_quentity =".$quantity.", warehouse_id ='".$channel_data->warehouse."', platform_id='".$channel_data->platformid."', online_price='".$online_price."', price ='".$online_price."', sku ='".$sku."', ean ='".$product_data->ean."', asin ='".$product_data->asin."', cost='".$cost."',created_date='".date('Y-m-d H:i:s')."',updated_date='".date('Y-m-d H:i:s')."'"; 
                                    //                         } else {
                                    //                             $sql    = "INSERT INTO prices SET itemId = '".$itemID."', country='".$country."', shipping = ".$online_shipping.", last_update_shipping='".date('Y-m-d H:i:s')."', online_shipping = ".$online_shipping.", product_id=".$product_data->productid.", channel_id =".$channel_data->idchannel.", last_update_date='".date('Y-m-d H:i:s')."', last_update_qty_date='".date('Y-m-d H:i:s')."', online_quentity =".$quantity.", warehouse_id ='".$channel_data->warehouse."', platform_id='".$channel_data->platformid."', online_price='".$online_price."', price ='".$online_price."', sku ='".$sku."', ean ='".$product_data->ean."', asin ='".$product_data->asin."', cost='".$product_data->price."',created_date='".date('Y-m-d H:i:s')."',updated_date='".date('Y-m-d H:i:s')."'"; 
                                    //                         }
                                    //                         mysqli_query($conn, $sql);
                                    //                     }
                                    //                 } else {
                                    //                     $sql    = "SELECT * FROM prices WHERE channel_id=".$channel_data->idchannel." AND country='".$country."' AND sku='".$sku."'";
                                    //                     $result = mysqli_query($conn, $sql);
                                    //                     if ($result->num_rows > 0) {
                                    //                         $sql    = "UPDATE prices SET itemId = '".$itemID."', ebayActive=1, shipping = ".$online_shipping.", country='".$country."', online_price = ".$online_price.", online_shipping = ".$online_shipping.", last_update_shipping='".date('Y-m-d H:i:s')."', online_quentity ='".$quantity."', last_update_qty_date='".date('Y-m-d H:i:s')."', last_update_date='".date('Y-m-d H:i:s')."', updated_date='".date('Y-m-d H:i:s')."' WHERE channel_id=".$channel_data->idchannel." AND country='".$country."' AND sku='".$sku."'";
                                    //                         mysqli_query($conn, $sql);
                                    //                     } else {
                                    //                         $sql            = "SELECT * FROM tbl_none_product WHERE channelId=".$channel_data->idchannel." AND sku='".$sku."'";
                                    //                         $result         = mysqli_query($conn, $sql);
                                    //                         if ($result->num_rows > 0) {
                                    //                             $none_product   = mysqli_fetch_object($result);
                                    //                             if($none_product->related_modelcode != null || $none_product->related_modelcode != "") {
                                    //                                 $sql    = "INSERT INTO prices SET itemId = '".$itemID."', shipping = ".$online_shipping.", online_shipping = ".$online_shipping.", country='".$country."', product_id=".$none_product->related_modelcode.", last_update_shipping='".date('Y-m-d H:i:s')."', last_update_date='".date('Y-m-d H:i:s')."', channel_id =".$channel_data->idchannel.", online_quentity =".$quantity.", warehouse_id ='".$channel_data->warehouse."', platform_id='".$channel_data->platformid."', online_price='".$online_price."', price ='".$online_price."', sku ='".$sku."', created_date='".date('Y-m-d H:i:s')."', updated_date='".date('Y-m-d H:i:s')."'"; 
                                    //                                 mysqli_query($conn, $sql);
                                    //                             } else {
                                    //                                 $sql    = "UPDATE tbl_none_product SET online_quantity ='".$quantity."', online_price='".$online_price."', itemId='".$itemID."' , status= 0 WHERE channelId=".$channel_data->idchannel." AND sku='".$sku."'";
                                    //                                 mysqli_query($conn, $sql);
                                    //                             }
                                    //                         } else {
                                    //                             $sql    = "INSERT INTO tbl_none_product SET channelId ='".$channel_data->idchannel."', online_quantity ='".$quantity."', online_price='".$online_price."', itemId='".$itemID."', sku ='".$sku."'"; 
                                    //                             mysqli_query($conn, $sql);
                                    //                         }
                                    //                     }
                                    //                 }
                                    //             } else if(isset($item['Variations'])) {
                                    //                 $variations             = $item['Variations'];
                                    //                 $online_shipping        = $item['ShippingDetails']['ShippingServiceOptions']['ShippingServiceCost'];
                                    //                 $shippingprofileId      = $item['SellerProfiles']['SellerShippingProfile']['ShippingProfileID'];
                                    //                 $reuturnprofileId       = $item['SellerProfiles']['SellerReturnProfile']['ReturnProfileID'];
                                    //                 $shippingprofileName    = $item['SellerProfiles']['SellerShippingProfile']['ShippingProfileName'];
                                                    
                                    //                 $countrySQL = "SELECT * FROM tbl_return WHERE returnId='".$reuturnprofileId."' AND channelId=".$channel_data->idchannel;
                                    //                 $countryResult = mysqli_query($conn, $countrySQL);
                                    //                 if ($countryResult->num_rows > 0) {
                                    //                     $countryRow = mysqli_fetch_object($countryResult);
                                    //                     $country    = $countryRow->country;
                                    //                 } else {
                                    //                     if($noreuturnprofileId != $reuturnprofileId) {
                                    //                         $noreuturnprofileId = $reuturnprofileId;
                                    //                         echo "Warning: No country for ".$reuturnprofileId."<br>";
                                    //                         echo $shippingprofileId."-----------".$reuturnprofileId."-----------".$shippingprofileName."<br>";
                                    //                         $warnmessage = "Warning: No country for ".$reuturnprofileId." in ".$channel_data->shortname;
                                    //                         $sql = "INSERT INTO tbl_open_activities SET dateTime='".date('Y-m-d H:i:s')."', issues='".$warnmessage."'";
                                    //                         mysqli_query($conn, $sql);
                                    //                     }
                                    //                 }
                    
                                    //                 foreach($variations as $variation) {
                                    //                     if(isset($variation['SKU'])) {
                                    //                         $sku                = $variation['SKU'];
                                    //                         $online_price       = $variation['StartPrice'];
                                    //                         $quantity           = intval($variation['Quantity']) - intval($variation['SellingStatus']['QuantitySold']);                                        
                                    //                         $sql            = "SELECT * FROM product WHERE modelcode='".substr($sku, 0, 5)."'";
                                    //                         $result         = mysqli_query($conn, $sql);
                                    //                         if ($result->num_rows > 0) {
                                    //                             $product_data   = mysqli_fetch_object($result);
                                    //                             $sql            = "SELECT * FROM prices WHERE channel_id=".$channel_data->idchannel." AND country='".$country."' AND sku='".$sku."'";
                                    //                             $result = mysqli_query($conn, $sql);
                                                                
                                    //                             if ($result->num_rows > 0) {
                                    //                                 $sql    = "UPDATE prices SET itemId = '".$itemID."', ebayActive=1, online_price = ".$online_price.", country='".$country."', shipping = ".$online_shipping.", online_shipping = ".$online_shipping.", online_quentity ='".$quantity."', last_update_shipping='".date('Y-m-d H:i:s')."', last_update_qty_date='".date('Y-m-d H:i:s')."', last_update_date='".date('Y-m-d H:i:s')."', updated_date='".date('Y-m-d H:i:s')."' WHERE channel_id=".$channel_data->idchannel." AND country='".$country."' AND sku='".$sku."'";
                                    //                                 mysqli_query($conn, $sql);
                                    //                             } else {
                                    //                                 if($product_data->virtualkit == "Yes") {
                                    //                                     $cost = 0;
                                    //                                     for($i=1; $i<10; $i++) {
                                    //                                         $item = "pcs".$i;
                                    //                                         $itemProductId  = "productid".$i;
                                    //                                         $productid      = $product_data->$itemProductId;
                                    //                                         if($product_data->$item != null && $product_data->$item > 0 && $product_data->$item != "" && $productid != "" && $productid != null) {
                                    //                                             $itemProduct = mysqli_fetch_object(mysqli_query($conn, "SELECT * FROM product WHERE modelcode=".$productid));
                                    //                                             $cost += $itemProduct->price*$product_data->$item;
                                    //                                         }
                                    //                                     }
                            
                                    //                                     $sql    = "INSERT INTO prices SET itemId = '".$itemID."', last_update_shipping='".date('Y-m-d H:i:s')."', country='".$country."', shipping = ".$online_shipping.", online_shipping = ".$online_shipping.", product_id=".$product_data->productid.", channel_id =".$channel_data->idchannel.", last_update_date='".date('Y-m-d H:i:s')."', last_update_qty_date='".date('Y-m-d H:i:s')."', online_quentity =".$quantity.", warehouse_id ='".$channel_data->warehouse."', platform_id='".$channel_data->platformid."', online_price='".$online_price."', price ='".$online_price."', sku ='".$sku."', ean ='".$product_data->ean."', asin ='".$product_data->asin."', cost='".$cost."',created_date='".date('Y-m-d H:i:s')."',updated_date='".date('Y-m-d H:i:s')."'"; 
                                    //                                 } else {
                                    //                                     $sql    = "INSERT INTO prices SET itemId = '".$itemID."', last_update_shipping='".date('Y-m-d H:i:s')."', country='".$country."', shipping = ".$online_shipping.", online_shipping = ".$online_shipping.", product_id=".$product_data->productid.", channel_id =".$channel_data->idchannel.", last_update_date='".date('Y-m-d H:i:s')."', last_update_qty_date='".date('Y-m-d H:i:s')."', online_quentity =".$quantity.", warehouse_id ='".$channel_data->warehouse."', platform_id='".$channel_data->platformid."', online_price='".$online_price."', price ='".$online_price."', sku ='".$sku."', ean ='".$product_data->ean."', asin ='".$product_data->asin."', cost='".$product_data->price."',created_date='".date('Y-m-d H:i:s')."',updated_date='".date('Y-m-d H:i:s')."'"; 
                                    //                                 }
                                    //                                 mysqli_query($conn, $sql);
                                    //                             }
                                    //                         } else {
                                    //                             $sql    = "SELECT * FROM prices WHERE channel_id=".$channel_data->idchannel." AND country='".$country."' AND sku='".$sku."'";
                                    //                             $result = mysqli_query($conn, $sql);
                                    //                             if ($result->num_rows > 0) {
                                    //                                 $sql    = "UPDATE prices SET itemId = '".$itemID."', ebayActive=1, online_price = ".$online_price.", country='".$country."', online_shipping = ".$online_shipping.", shipping = ".$online_shipping.", last_update_shipping='".date('Y-m-d H:i:s')."', online_quentity ='".$quantity."', last_update_qty_date='".date('Y-m-d H:i:s')."', last_update_date='".date('Y-m-d H:i:s')."', updated_date='".date('Y-m-d H:i:s')."' WHERE channel_id=".$channel_data->idchannel." AND country='".$country."' AND sku='".$sku."'";
                                    //                                 mysqli_query($conn, $sql);
                                    //                             } else {
                                    //                                 $sql            = "SELECT * FROM tbl_none_product WHERE channelId=".$channel_data->idchannel." AND sku='".$sku."'";
                                    //                                 $result         = mysqli_query($conn, $sql);
                                    //                                 if ($result->num_rows > 0) {
                                    //                                     $none_product   = mysqli_fetch_object($result);
                                    //                                     if($none_product->related_modelcode != null || $none_product->related_modelcode != "") {
                                    //                                         $sql    = "INSERT INTO prices SET itemId = '".$itemID."', country='".$country."', online_shipping = ".$online_shipping.", shipping = ".$online_shipping.", product_id=".$none_product->related_modelcode.", last_update_shipping='".date('Y-m-d H:i:s')."', last_update_date='".date('Y-m-d H:i:s')."', channel_id =".$channel_data->idchannel.", online_quentity =".$quantity.", warehouse_id ='".$channel_data->warehouse."', platform_id='".$channel_data->platformid."', online_price='".$online_price."', price ='".$online_price."', sku ='".$sku."', created_date='".date('Y-m-d H:i:s')."', updated_date='".date('Y-m-d H:i:s')."'"; 
                                    //                                         mysqli_query($conn, $sql);
                                    //                                     } else {
                                    //                                         $sql    = "UPDATE tbl_none_product SET online_quantity ='".$quantity."', online_price='".$online_price."', itemId='".$itemID."' , status= 0 WHERE channelId=".$channel_data->idchannel." AND sku='".$sku."'";
                                    //                                         mysqli_query($conn, $sql);
                                    //                                     }
                                    //                                 } else {
                                    //                                     $sql    = "INSERT INTO tbl_none_product SET channelId ='".$channel_data->idchannel."', online_quantity ='".$quantity."', online_price='".$online_price."', itemId='".$itemID."', sku ='".$sku."'"; 
                                    //                                     mysqli_query($conn, $sql);
                                    //                                 }
                                    //                             }
                                    //                         }
                                    //                     }
                                    //                 }
                                    //             }
                                    //         }
                                    //     }
                                    // }
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
}
    echo "end";
?>