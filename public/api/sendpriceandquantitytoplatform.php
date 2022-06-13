<?php
    ini_set('max_execution_time', 30000000000);
    ini_set('display_errors', 1);
    
    error_reporting(E_ALL);
    
    require(__DIR__ .'/config.php');
    require(__DIR__ .'/vendor/autoload.php');

    $productId = "";
    if(isset($_GET['productId']) && $_GET['productId'] != "") {
        $productId = $_GET['productId'];
        $sql    = "SELECT * FROM prices INNER JOIN channel ON prices.channel_id=channel.idchannel WHERE  prices.product_id=".$productId;
        $result = mysqli_query($conn, $sql);
    } else {
        $sql    = "SELECT * FROM prices INNER JOIN channel ON prices.channel_id=channel.idchannel WHERE prices.product_id=".$productId;
        $result = mysqli_query($conn, $sql);
    }
    
    //.' and prices.channel_id=5' //channel.idchannel = 17 AND 
    if($productId != "") {
        $sql    = "SELECT prices.*, channel.*, prices.country AS marketplaceCountry FROM prices INNER JOIN channel ON prices.channel_id=channel.idchannel WHERE  prices.product_id=".$productId;
        $result = mysqli_query($conn, $sql); 

       // echo $result->num_rows; exit();
        if($result->num_rows > 0) {
            while($price  = mysqli_fetch_object($result)) {
               // echo '<pre>'; print_r($price); echo '</pre>'; continue;
                $price->indicated_quantity = 0;
                $price->warehouse_quantity = 0;
                $price->can_sell_online = 0;
                
                $amazonprices       = [];
                $amazonquantities   = [];
                //echo '<pre>'; print_r($price);echo '</pre>';
                if($price->aws_acc_key_id != NULL || $price->aws_secret_key_id != NULL || $price->merchant_id != NULL || $price->market_place_id != NULL || $price->mws_auth_token != NULL) {  
                    $aws_acc_key_id     = $price->aws_acc_key_id;  // these prod keys are different from sandbox keys
                    $aws_secret_key_id  = $price->aws_secret_key_id;  // these prod keys are different from sandbox keys
                    $merchant_id        = $price->merchant_id;
                    $mws_auth_token     = $price->mws_auth_token;
                    $shortname          = $price->shortname;   
                    $idcompany          = $price->idcompany;    
                    $vat                = $price->vat; 
                    $platformid         = $price->platformid; 
                    $idwarehouse        = $price->warehouse;
                    $idchannel          = $price->idchannel; 
                    $countryname        = $price->country;
                    $warehouse          = $price->warehouse;

                    echo "<br><br>";
                    //echo $idchannel."<br><br>";
                    if($price->marketplaceCountry == "DE") {
                        $market_place_id = "A1PA6795UKMFR9";
                    } else if($price->marketplaceCountry == "FR") {
                        $market_place_id = "A13V1IB3VIYZZH";
                    } else if($price->marketplaceCountry == "UK") {
                        $market_place_id = "A1F83G8C2ARO7P";
                    } else if($price->marketplaceCountry == "ES") {
                        $market_place_id = "A1RKKUPIHCS9HS";
                    } else if($price->marketplaceCountry == "IT") {
                        $market_place_id = "APJ6JRA9NG5V4";
                    }
                    if($price->online_price != $price->price) {
                        $newprice = [$price->sku => $price->price];
                        array_push($amazonprices, $newprice);
                        $client = new MCS\MWSClient([
                            'Marketplace_Id'    => $market_place_id,
                            'Seller_Id'         => $merchant_id,
                            'Access_Key_ID'     => $aws_acc_key_id,
                            'Secret_Access_Key' => $aws_secret_key_id,
                            'MWSAuthToken'      => $price->mws_auth_token // Optional. Only use this key if you are a third party user/developer
                        ]);
            
                        try { 
                            $status = $client->updatePrice($newprice); 
                            echo "price<br><br>";
                            print_r($status);
                            echo "<br><br>";
                            if(isset($status['FeedProcessingStatus']) && $status['FeedProcessingStatus'] == "_SUBMITTED_") {
                                mysqli_query($conn, "UPDATE prices SET online_price=".$price->price." WHERE price_id = ".$price->price_id);
                                echo "Success";
                            }
                        } 
                        catch (\Exception $e) { 
                            echo 'Message: ' .$e->getMessage(); 
                        }    
                    }
                    
                    $warehouseQnt = mysqli_fetch_object(mysqli_query($conn, "SELECT * FROM lagerstand WHERE productid=".$productId." AND idwarehouse=".$price->warehouse));

                    if($price->quantity_strategy == 1) {
                        $buffer = mysqli_fetch_object(mysqli_query($conn, "SELECT * FROM product WHERE productid=".$productId));
                        if(!empty($buffer) && $buffer->min_sell != null) {
                            $price->indicated_quantity = $buffer->min_sell;
                        }
                        $price->can_sell_online = $price->indicated_quantity;
                        if(!empty($warehouseQnt)) {
                            if($warehouseQnt->quantity >= $price->indicated_quantity){
                                $price->can_sell_online = $price->indicated_quantity;
                            }else{
                                $price->can_sell_online = $warehouseQnt->quantity;
                            }
                        }
                    }else if($price->quantity_strategy == 3) {
                        if(!empty($warehouseQnt)) {
                            $price->can_sell_online = $warehouseQnt->quantity;
                        }
                    }
                    
                    $newquantity = $price->can_sell_online;

                    if($price->online_quentity != $newquantity && $newquantity >= 0) {
                        $newquantity = [$price->sku => $newquantity];
                        array_push($amazonquantities, $newquantity);
                        $client = new MCS\MWSClient([
                            'Marketplace_Id'    => $market_place_id,
                            'Seller_Id'         => $merchant_id,
                            'Access_Key_ID'     => $aws_acc_key_id,
                            'Secret_Access_Key' => $aws_secret_key_id,
                            'MWSAuthToken'      => $price->mws_auth_token // Optional. Only use this key if you are a third party user/developer
                        ]);
            
                        try { 
                            $status = $client->updateStock($newquantity);  
                            echo "quantity<br><br>";
                            print_r($status);
                            echo "<br><br>";
                            $info = $client->GetFeedSubmissionResult($status['FeedSubmissionId']);
                            echo "info<br><br>";
                            print_r($info);
                            echo "<br><br>";
                            if(isset($status['FeedProcessingStatus']) && $status['FeedProcessingStatus'] == "_SUBMITTED_") {
                                mysqli_query($conn, "UPDATE prices SET online_quentity=".$price->quantity_strategy." WHERE price_id = ".$price->price_id);
                                echo "Success";
                            }
                        } 
                        catch (\Exception $e) { 
                            echo 'Message: ' .$e->getMessage(); 
                        }    
                    }
                    
                } else if($price->devid != NULL || $price->appid != NULL || $price->certid != NULL || $price->refresh_token != NULL) {

                  
                    $devID              = $price->devid;
                    $appID              = $price->appid;
                    $certID             = $price->certid;
                    $userToken          = $price->usertoken; 
                    $shortname          = $price->shortname;   
                    $idcompany          = $price->idcompany;    
                    $vat                = $price->vat; 
                    $platformid         = $price->platformid; 
                    $idwarehouse        = $price->warehouse;
                    $idchannel          = $price->idchannel; 
                    $countryname        = $price->country;
                    $warehouse          = $price->warehouse;
                    $contry             = $price->country;

                    $endpoint  = 'https://api.ebay.com/ws/api.dll'; // URL to call

                    $headers = array(
                        'Content-Type: text/xml',
                        'X-EBAY-API-COMPATIBILITY-LEVEL:837',
                        'X-EBAY-API-DEV-NAME:'.$devID,
                        'X-EBAY-API-APP-NAME:'.$appID,
                        'X-EBAY-API-CERT-NAME:'.$certID,
                        'X-EBAY-API-SITEID:0',
                        'X-EBAY-API-CALL-NAME:ReviseItem'
                    );

                    $warehouseQnt = mysqli_fetch_object(mysqli_query($conn, "SELECT * FROM lagerstand WHERE productid=".$productId." AND idwarehouse=".$price->warehouse));

                    if($price->quantity_strategy == 1) {
                        $buffer = mysqli_fetch_object(mysqli_query($conn, "SELECT * FROM product WHERE productid=".$productId));
                        if(!empty($buffer) && $buffer->min_sell != null) {
                            $price->indicated_quantity = $buffer->min_sell;
                        }
                        $price->can_sell_online = $price->indicated_quantity;
                        if(!empty($warehouseQnt)) {
                            if($warehouseQnt->quantity >= $price->indicated_quantity){
                                $price->can_sell_online = $price->indicated_quantity;
                            }else{
                                $price->can_sell_online = $warehouseQnt->quantity;
                            }
                        }
                    }else if($price->quantity_strategy == 3) {
                        if(!empty($warehouseQnt)) {
                            $price->can_sell_online = $warehouseQnt->quantity;
                        }
                    }
                    
                    $newquantity = $price->can_sell_online;
                    $fields['itemId'] = $price->itemId;
                    if($price->online_price != $price->price) {
                        echo 'Updating Price: '.$price->shortname."45454545".$price->itemId."--------".$price->sku."--------".$price->price."<br>";
                        
                        $fields['price'] = $price->price;
                        $fields_string = http_build_query($fields); 
                        $ch = curl_init();
                        $url = $siteUrl."/ebay/updatePrice/".$price->channel_id.'?'.$fields_string;
                        $ch = curl_init();
                        curl_setopt($ch,CURLOPT_URL, $url);
                        $res = curl_exec($ch);
                        curl_close($ch);
                        
                        mysqli_query($conn, "UPDATE prices SET online_price=".$price->price." WHERE price_id = ".$price->price_id);
                        
                        echo "<br>";
                    }
                   
                    
                   
                    if($newquantity > 0 && $newquantity !='') {
                        echo 'Updating quantity: '.$price->shortname."45454545".$price->itemId."--------".$price->sku."--------".$newquantity."<br>";
                        
                        $fields = array();
                        $fields['itemId'] = $price->itemId;
                        $fields['quantity'] = $newquantity;
                        $fields_string = http_build_query($fields); 
                        $ch = curl_init();
                        $url = $siteUrl."/ebay/updateQuantity/".$price->channel_id.'?'.$fields_string;
                        $ch = curl_init();
                        curl_setopt($ch,CURLOPT_URL, $url);
                        $res = curl_exec($ch);
                        curl_close($ch);
                        
                        //convert the XML result into array
                        $array_data = json_decode(json_encode(simplexml_load_string($data)), true);
                        mysqli_query($conn, "UPDATE prices online_quentity=".$newquantity." WHERE price_id = ".$price->price_id);
                    }
                     
                } else if($price->channel_id == 5) {
                    $warehouseQnt = mysqli_fetch_object(mysqli_query($conn, "SELECT * FROM lagerstand WHERE productid=".$productId." AND idwarehouse=".$price->warehouse));
       
                    if($price->quantity_strategy == 1) {
                        $buffer = mysqli_fetch_object(mysqli_query($conn, "SELECT * FROM product WHERE productid=".$productId));
                        if(!empty($buffer) && $buffer->min_sell != null) {
                            $price->indicated_quantity = $buffer->min_sell;
                        }
                        $price->can_sell_online = $price->indicated_quantity;
                        if(!empty($warehouseQnt)) {
                            if($warehouseQnt->quantity >= $price->indicated_quantity){
                                $price->can_sell_online = $price->indicated_quantity;
                            }else{
                                $price->can_sell_online = $warehouseQnt->quantity;
                            }
                        }
                    }else if($price->quantity_strategy == 3) {
                        if(!empty($warehouseQnt)) {
                            $price->can_sell_online = $warehouseQnt->quantity;
                        }
                    }
                    
                    $newquantity = $price->can_sell_online;
                   
                    $str = "";
                    $online = [];
                    if($price->online_price != $price->price) {
                        $online['online_price'] = $price->price;
                        $str .= 'Price="'.$price->price.'"';
                    }
                    $newquantity  = 1;
                    if($newquantity >= 0){
                        $str .= ' Stock="'.$newquantity.'"';
                        $online['online_quentity'] = $newquantity;
                    }
                   
                    if($str){
                        $newOffer = '<OfferPackage Name="Nom fichier offres" PurgeAndReplace="false" PackageType="StockAndPrice" xmlns="clr-namespace:Cdiscount.Service.OfferIntegration.Pivot;assembly=Cdiscount.Service.OfferIntegration" xmlns:x="http://schemas.microsoft.com/winfx/2006/xaml">';
                        $newOffer .= '    <OfferPackage.Offers>';
                        $newOffer .= '        <OfferCollection Capacity="1">';
                        $newOffer .= '            <Offer SellerProductId="'.$price->sku.'" ProductEan="'.$price->ean.'" '.$str.' />';
                        $newOffer .= '        </OfferCollection>';
                        $newOffer .= '    </OfferPackage.Offers>';
                        $newOffer .= '</OfferPackage>';
                         
                        ///Write to the Offer file
                        $offerFile = fopen("Offers.xml", "w") or die("Unable to open file!");
                        fwrite($offerFile, $newOffer);
                        fclose($offerFile);
                        ///Add the Offer file to zip file
                        $zip = new ZipArchive();
                        $filename = "cDiscount.zip";

                        if ($zip->open($filename, ZIPARCHIVE::CREATE)!==TRUE) {
                            exit("cannot open <$filename>\n");
                        }
                        try{
                            $zip->addFile("Offers.xml","Content/Offers.xml") or die ("ERROR: Could not add file:");

                        }
                        catch (Exception $e){
                            echo  $e->getMessage();exit;
                        }
                        $zip->close();


                        $ch = curl_init();

                        curl_setopt($ch, CURLOPT_URL, 'https://oauth2.cdiscount.com/auth/realms/maas-international-sellers/protocol/openid-connect/token');
                        curl_setopt($ch, CURLOPT_POST, 1);
                        curl_setopt($ch, CURLOPT_POSTFIELDS,
                                    "client_id=Confidence Europe GmbH&client_secret=024d9d1a-8768-40a8-b23c-49c4a7e1a40c&grant_type=client_credentials");
                        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));


                        // receive server response ...
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                        $server_output = curl_exec ($ch);

                        curl_close ($ch);

                        $access_data = json_decode($server_output);

                        $access_token = $access_data->access_token;

                        $curl = curl_init();

                        echo $access_token; 
                        
                        curl_setopt_array($curl, array(
                        CURLOPT_URL => 'https://marketplaceapi.cdiscount.com/offerManagement/offer-integration-packages',
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'POST',
                        CURLOPT_POSTFIELDS =>'"http://channelister.com/newchannelmanager/public/api/cDiscount.zip"',
                        CURLOPT_HTTPHEADER => array(
                            'Content-Type: application/json-patch+json',
                            'Cache-Control: no-cache',
                            'Ocp-Apim-Subscription-Key: 2fec5b686ee3423680799a093345b2a8',
                            'SellerId: 12514',
                            'Authorization: Bearer '.$access_token
                        ),
                        ));

                        $response = curl_exec($curl);
                        if (curl_errno($curl)) {
                            $error_msg = curl_error($curl);
                            print_r($error_msg);
                        }
                        
                        curl_close($curl);
                        echo '</br>';
                        echo 'Cdiscount response: '.'</br>';
                        print_r($response);

                        //update the table data
                        if(count($online) > 0){
                            update_data(array('price_id'=>$price->price_id), $online, 'prices',  $conn);
                        }
                    }
                } else if($price->channel_id == 14) {
                    $warehouseQnt = mysqli_fetch_object(mysqli_query($conn, "SELECT * FROM lagerstand WHERE productid=".$productId." AND idwarehouse=".$price->warehouse));
                    
                    if($price->quantity_strategy == 1) {
                        $buffer = mysqli_fetch_object(mysqli_query($conn, "SELECT * FROM product WHERE productid=".$productId));
                        if(!empty($buffer) && $buffer->min_sell != null) {
                            $price->indicated_quantity = $buffer->min_sell;
                        }
                        $price->can_sell_online = $price->indicated_quantity;
                        if(!empty($warehouseQnt)) {
                            if($warehouseQnt->quantity >= $price->indicated_quantity){
                                $price->can_sell_online = $price->indicated_quantity;
                            }else{
                                $price->can_sell_online = $warehouseQnt->quantity;
                            }
                        }
                    }else if($price->quantity_strategy == 3) {
                        if(!empty($warehouseQnt)) {
                            $price->can_sell_online = $warehouseQnt->quantity;
                        }
                    }
                    
                    $newquantity = $price->can_sell_online;
                    
                    $fields = [];
                    $online = [];
                    $fields['item_id'] = $price->itemId; 
                    if($price->online_price != $price->price) {
                       $online['online_price'] = $fields['price'] = $price->price;
                    }
                    
                    $online['online_quentity'] = $fields['stock_quantity'] = $newquantity;
                    echo 'woocommerce-'.$newquantity.'--'.$price->price.'--'.$price->itemId.'--'.$price->sku.'<br>';
                    $ch = curl_init();
                    $url = $siteUrl."/api/wcUpdateStoreData/".$price->channel_id;
                    $fields_string = http_build_query($fields);
                    $ch = curl_init();
                    curl_setopt($ch,CURLOPT_URL, $url);
                    curl_setopt($ch,CURLOPT_POST, 1);
                    curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
                    $res = curl_exec($ch);
                    curl_close($ch);
                    
                    //update the table data
                    if(count($online) > 0){
                        update_data(array('price_id'=>$price->price_id), $online, 'prices',  $conn);
                    }
                }else if($price->channel_id == 17) {
                    $warehouseQnt = mysqli_fetch_object(mysqli_query($conn, "SELECT * FROM lagerstand WHERE productid=".$productId." AND idwarehouse=".$price->warehouse));
                    
                    if($price->quantity_strategy == 1) {
                        $buffer = mysqli_fetch_object(mysqli_query($conn, "SELECT * FROM product WHERE productid=".$productId));
                        if(!empty($buffer) && $buffer->min_sell != null) {
                            $price->indicated_quantity = $buffer->min_sell;
                        }
                        $price->can_sell_online = $price->indicated_quantity;
                        if(!empty($warehouseQnt)) {
                            if($warehouseQnt->quantity >= $price->indicated_quantity){
                                $price->can_sell_online = $price->indicated_quantity;
                            }else{
                                $price->can_sell_online = $warehouseQnt->quantity;
                            }
                        }
                    }else if($price->quantity_strategy == 3) {
                        if(!empty($warehouseQnt)) {
                            $price->can_sell_online = $warehouseQnt->quantity;
                        }
                    }
                    
                    $newquantity = $price->can_sell_online;
                    
                    $fields = [];
                    $online = [];
                    $fields['item_id'] = $price->itemId; 
                    if($price->online_price != $price->price) {
                       $online['price'] = $fields['price'] = $price->price;
                    }

                    $online['sku'] = $price->sku;
                    $online['quantity'] = $fields['quantity'] = $newquantity;
                    
                    echo 'otto-'.$newquantity.'--'.$price->price.'--'.$price->itemId.'--'.$price->sku.'<br>';
                    
                    $fields_string = http_build_query($fields); 
                    $ch = curl_init();
                    $url = $siteUrl."/ottoUpdateStoreData/".$price->channel_id.'?'.$fields_string;
                    
                    $ch = curl_init();
                    curl_setopt($ch,CURLOPT_URL, $url);
                    //curl_setopt($ch,CURLOPT_POST, 1);
                    //curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
                    $res = curl_exec($ch);
                    curl_close($ch);

                    //update the table data
                    if(count($online) > 0){
                        update_data(array('price_id'=>$price->price_id), $online, 'prices',  $conn);
                    }
                }
            }
        }
    }
    
        
    function update_data(array $id, array $values, $tablename, $conn){
        $sIDColumn  = key($id);
        $sIDValue   = current($id);
        $arrayValues = $values;
        array_walk($values, function(&$value, $key){
            $value = "{$key} = '{$value}'";
        });
        $sUpdate = implode(", ", array_values($values));
        $sql        = "UPDATE {$tablename} SET {$sUpdate} WHERE {$sIDColumn} = '{$sIDValue}'";
        mysqli_query($conn, $sql);
    }
?>