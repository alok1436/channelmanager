<?php 
    ini_set('max_execution_time', 1500000);
    ini_set('memory_limit', -1);
    // ini_set('mysql.connect_timeout', 1500000);
    // ini_set('default_socket_timeout', 1500000);
    // ini_set('mysql.reconnect', 1);
    // ini_set('mysql.wait_timeout', 1500000);
    // ini_set('wait_timeout', 1500000);
    // ini_set('mysql.max_allowed_packet', '2024MB'); 
    // ini_set('innodb_lock_wait_timeout', 1500000); 
    // ini_set('mysql.innodb_lock_wait_timeout', 1500000); 
    ini_set('display_errors', 1);
    
    error_reporting(E_ALL);
    require(__DIR__ .'/config.php');
    require(__DIR__ .'/vendor/autoload.php');

    // $sql        = "SELECT * FROM product";
    // $result     = mysqli_query($conn, $sql);
    // $products   = array();
    // while($row  = mysqli_fetch_object($result)){
    //     $products[] = $row;
    // } 

    $non_existing_products = array();

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
    ////AMAZON
    if(!empty($channels)){
        foreach($channels as $channel_data) {
            
            // if($channel_data->aws_acc_key_id!='' && 
            //     $channel_data->aws_secret_key_id!='' && 
            //     $channel_data->merchant_id!='' && 
            //     $channel_data->market_place_id!='' && 
            //     $channel_data->mws_auth_token!=''){
            //         $marketplaceIds = ['A13V1IB3VIYZZH', 'A1F83G8C2ARO7P', 'A1PA6795UKMFR9', 'A1RKKUPIHCS9HS', 'APJ6JRA9NG5V4'];
            //         $countryArr     = ['FR', 'UK', 'DE', 'ES', 'IT'];
            //         $existingProductFlag = 0;
            //         for($k=0; $k<count($marketplaceIds); $k++) {
            //             $client = new MCS\MWSClient([
            //                 'Marketplace_Id'    => $marketplaceIds[$k],
            //                 'Seller_Id'         => $channel_data->merchant_id,
            //                 'Access_Key_ID'     => $channel_data->aws_acc_key_id,
            //                 'Secret_Access_Key' => $channel_data->aws_secret_key_id,
            //                 'MWSAuthToken'      => $channel_data->mws_auth_token
            //             ]);
                        
            //           // echo '<pre>'; print_r($k); echo '</pre>';
                        
            //             try { 
            //             $reportRequest = $client->RequestReport('_GET_MERCHANT_LISTINGS_ALL_DATA_');
            //             $reportRequestStatus = $client->GetReportList(['_GET_MERCHANT_LISTINGS_ALL_DATA_'], $ItemCondition = null);
            //              if(isset($reportRequestStatus['GetReportListResult'])) { 
            //                 // echo '<pre>'; print_r($k); echo '</pre>';
            //                 // echo '<pre>'; print_r($reportRequestStatus); echo '</pre>';
            //                 if(isset($reportRequestStatus['GetReportListResult']['ReportInfo'])) {
            //                     $reportIds = $reportRequestStatus['GetReportListResult']['ReportInfo'];
            //                     foreach($reportIds as $item) {
            //                         $reportId = $item['ReportRequestId'];
            //                         try{
            //                             $reports = $client->GetReport($reportId, $ItemCondition = null);
            //                             if(!empty($reports)) {
            //                                 foreach($reports as $report) {
            //                                     echo '<pre>'; print_r($k); echo '</pre>';
            //                                     echo '<pre>'; print_r($report); echo '</pre>';
            //                                     if(isset($report['seller-sku'])) {
            //                                         $quantity   = $report['quantity'];
            //                                         $price      = $report['price'];
            //                                         $country    = $countryArr[$k];
            //                                         $asinorean  = $report['product-id'];
            //                                         $sku        = $report['seller-sku'];
            //                                       // echo  "SELECT * FROM product WHERE ean='".$asinorean."' OR ASIN='".$asinorean."';"; echo '<br>';
            //                                         $result     = mysqli_query($conn, "SELECT * FROM product WHERE ean='".$asinorean."' OR ASIN='".$asinorean."';");
                                                    
            //                                         echo $sku."---------".$asinorean."---------".$price."-----------".$quantity."<br>";
            //                                         //if($result->num_rows > 0) {
            //                                             //$current_product    = mysqli_fetch_object($result);
            //                                             //$productid          = $current_product->productid;
            //                                             //$ean                = $current_product->ean;
            //                                             echo $sql                = "SELECT * FROM prices WHERE channel_id=".$channel_data->idchannel." AND country='".$country."' AND sku='".$sku."'"; echo '<br>';
            //                                             $result             = mysqli_query($conn, $sql);
                                                        
            //                                             if ($result->num_rows > 0) {
            //                                                 if($quantity == "") {
            //                                                     $quantity = 0;
            //                                                 }
            //                                                 $sql    = "UPDATE prices SET online_quentity= ".$quantity.", last_update_qty_date='".date('Y-m-d H:i:s')."' WHERE channel_id=".$channel_data->idchannel." AND country='".$country."' AND sku='".$sku."'";
            //                                                 echo $sql."<br>";
            //                                                 $result = mysqli_query($conn, $sql);
            //                                             } else {
            //                                                 // $warning    = "Warning: No price for ".$sku." in ".$country." of channel ".$channel_data->shortname;
            //                                                 // $sql        = "SELECT * FROM tbl_open_activities WHERE issues='".$warning."'";
            //                                                 // $result             = mysqli_query($conn, $sql);
            //                                                 // if ($result->num_rows == 0) {
            //                                                 //     $sql = "INSERT INTO tbl_open_activities SET dateTime ='".date('Y-m-d H:i:s')."', issues='".$warning."'";
            //                                                 //     echo $sql."<br>";
            //                                                 //     mysqli_query($conn, $sql);
            //                                                 // }
            //                                             }
            //                                         //} 
            //                                     }
            //                                 }  
            //                             }
            //                         }catch (\Exception $e) { 
            //                             echo 'Message: ' .$e->getMessage(); 
            //                         } 
            //                     }
            //                 }
            //                 }
            //             }catch (\Exception $e) { 
            //                 echo 'Message: ' .$e->getMessage(); 
            //             } 
            //     }
            // }

        if($channel_data->aws_acc_key_id!='' && $channel_data->aws_secret_key_id!='' && $channel_data->merchant_id!='' && $channel_data->market_place_id!='' && $channel_data->mws_auth_token!=''){

            mysqli_query($conn, "UPDATE prices SET ebayActive=0 WHERE channel_id=".$channel_data->idchannel."");

            $marketplaceIds = ['APJ6JRA9NG5V4','A13V1IB3VIYZZH', 'A1F83G8C2ARO7P', 'A1PA6795UKMFR9', 'A1RKKUPIHCS9HS'];
            $countryArr     = ['IT', 'FR', 'UK', 'DE', 'ES'];
            $existingProductFlag = 0;
            for($k=0; $k<count($marketplaceIds); $k++) {
                $client = new MCS\MWSClient([
                    'Marketplace_Id'    => $marketplaceIds[$k],
                    'Seller_Id'         => $channel_data->merchant_id,
                    'Access_Key_ID'     => $channel_data->aws_acc_key_id,
                    'Secret_Access_Key' => $channel_data->aws_secret_key_id,
                    'MWSAuthToken'      => $channel_data->mws_auth_token
                ]);
                
                try{
                    
                    //Get all ReportIds
                    $reportRequest = $client->RequestReport('_GET_MERCHANT_LISTINGS_ALL_DATA_');
                    $reportRequestStatus = $client->GetReportList(['_GET_MERCHANT_LISTINGS_ALL_DATA_'], $ItemCondition = null);
                     
                    // if($reportRequestStatus['GetReportListResult']['HasNext']) {
                    //     $nextToken = $reportRequestStatus['GetReportListResult']['NextToken'];
                    //     $reportRequestStatus = $client->GetReportListByNextToken($nextToken);
    
                    //     while($reportRequestStatus['GetReportListByNextTokenResult']['HasNext']) {
                    //         sleep(2);
                    //         if(isset($reportRequestStatus['GetReportListByNextTokenResult']['NextToken'])) {
                    //             $nextToken = $reportRequestStatus['GetReportListByNextTokenResult']['NextToken'];
                    //             $reportRequestStatus = $client->GetReportListByNextToken($nextToken);
                    //             echo "kkkkkkkkkkkk<br>";
                    //             print_r($reportRequestStatus);
                    //             echo "<br>";
                    //         } else {
                    //             echo "ttttttttttt<br>";
                    //             print_r($reportRequestStatus);
                    //             echo "<br>";
                    //             break;
                    //         }
                    //     }
                    // }
    
    
                    if(isset($reportRequestStatus['GetReportListResult'])) {
                        if(isset($reportRequestStatus['GetReportListResult']['ReportInfo'])) {
                            $reportIds = $reportRequestStatus['GetReportListResult']['ReportInfo'];
                            foreach($reportIds as $item) {
                                $reportId = $item['ReportRequestId'];
                                // if(!in_array($reportId, $checkedReportIds)) {
                                //     array_push($checkedReportIds, $reportId);
                                //     echo $reportId."/////";
                                // }           
                                          //     echo '<pre>'; print_r($reportId); echo '</pre>'; exit();    
                                try { 
                                    $reports = $client->GetReport($reportId, $ItemCondition = null);
                                    //echo '<pre>'; print_r($reports); echo '</pre>';
                                    if(!empty($reports)) {
                                        foreach($reports as $report) {
                                            if(isset($report['seller-sku'])) {
                                                $quantity   = $report['quantity'];
                                                $price      = $report['price'];
                                                $country    = $countryArr[$k];
                                                $asinorean  = $report['product-id'];
                                                $sku        = $report['seller-sku'];
                                                $result     = mysqli_query($conn, "SELECT * FROM product WHERE ean='".$asinorean."' OR ASIN='".$asinorean."';");
                                                
                                                echo $sku."----'.$country.'-----".$asinorean."---------".$price."-----------".$quantity."<br>";
                                                if($result->num_rows > 0) {
                                                    $current_product    = mysqli_fetch_object($result);
                                                    $productid          = $current_product->productid;
                                                    $ean                = $current_product->ean;
                                                    $sql                = "SELECT * FROM prices WHERE channel_id=".$channel_data->idchannel." AND country='".$country."' AND sku='".$sku."'";
    
                                                    $result             = mysqli_query($conn, $sql);
                                                    
                                                    if ($result->num_rows > 0) {
                                                        if($quantity == "") {
                                                            $quantity = 0;
                                                        }
                                                        
                                                        $sql    = "UPDATE prices SET online_quentity= ".$quantity.", last_update_qty_date='".date('Y-m-d H:i:s')."',price ='".$price."', online_price='".$price."',last_update_date='".date('Y-m-d H:i:s')."', ebayActive=1 ,updated_date='".date('Y-m-d H:i:s')."' WHERE channel_id=".$channel_data->idchannel." AND country='".$country."' AND sku='".$sku."'";
                                                        
                                                        $result = mysqli_query($conn, $sql);
                                                        echo $sql.'<br>';
                                                    } else {
                                                        // $warning    = "Warning: No price for ".$sku." in ".$country." of channel ".$channel_data->shortname;
                                                        // $sql        = "SELECT * FROM tbl_open_activities WHERE issues='".$warning."'";
                                                        // $result             = mysqli_query($conn, $sql);
                                                        // if ($result->num_rows == 0) {
                                                        //     $sql = "INSERT INTO tbl_open_activities SET dateTime ='".date('Y-m-d H:i:s')."', issues='".$warning."'";
                                                        //     echo $sql."<br>";
                                                        //     mysqli_query($conn, $sql);
                                                        // }
                                                    }
                                                } 
                                            }
                                        }  
                                    }
                                    sleep(5);
                                } 
                                catch (\Exception $e) { 
                                    echo 'Message: ' .$e->getMessage(); 
                                }                            
                            }
                        }
                    }
                
                }catch (\Exception $e) { 
                    echo 'Message: ' .$e->getMessage(); 
                }
            }
        }
    }
}
    // foreach($channels as $channel_data) {
    //     $online_price       = 0.00;
    //     $online_shipping    = 0.00;
    //     if($channel_data->aws_acc_key_id!='' && $channel_data->aws_secret_key_id!='' && $channel_data->merchant_id!='' && $channel_data->market_place_id!='' && $channel_data->mws_auth_token!=''){
    //         $marketplaceIds = ['A13V1IB3VIYZZH', 'A1F83G8C2ARO7P', 'A1PA6795UKMFR9', 'A1RKKUPIHCS9HS', 'APJ6JRA9NG5V4'];
    //         $countryArr     = ['FR', 'UK', 'DE', 'ES', 'IT'];
    //         $existingProductFlag = 0;
    //         for($k=0; $k<count($marketplaceIds); $k++) {
    //             $client = new MCS\MWSClient([
    //                 'Marketplace_Id'    => $marketplaceIds[$k],
    //                 'Seller_Id'         => $channel_data->merchant_id,
    //                 'Access_Key_ID'     => $channel_data->aws_acc_key_id,
    //                 'Secret_Access_Key' => $channel_data->aws_secret_key_id,
    //                 'MWSAuthToken'      => $channel_data->mws_auth_token
    //             ]);

    //             //Get all ReportIds
    //             // $reportRequestStatus = $client->GetReportList(['_GET_FLAT_FILE_OPEN_LISTINGS_DATA_'], $ItemCondition = null);
    //             // if(isset($reportRequestStatus['GetReportListResult'])) {
    //             //     if(isset($reportRequestStatus['GetReportListResult']['ReportInfo'])) {
    //             //         $reportIds = $reportRequestStatus['GetReportListResult']['ReportInfo'];
    //             //         foreach($reportIds as $item) {
    //             //             $reportId = $item['ReportRequestId'];  
    //             //             try {                                 
    //             //                 $reports = $client->GetReport($reportId, $ItemCondition = null);
    //             //                 echo "---------reportID--------".$reportId."----------number-------".count($reports)."<br>";
    //             //                 foreach($reports as $report) {
    //             //                     $arrKeys = array_keys($report);
    //             //                     echo $report[$arrKeys[0]]."---------------".$report['price']."---------------".$report['quantity']."---------------".$countryArr[$k]."<br>";
    //             //                     $quantity   = $report['quantity'];
    //             //                     if($quantity == "") {
    //             //                         $quantity   = 0;
    //             //                     }

    //             //                     $price      = $report['price'];
    //             //                     $country    = $countryArr[$k];
    //             //                     $sku        = $report[$arrKeys[0]];
    //             //                     $result     = mysqli_query($conn, "SELECT * FROM product WHERE sku='".$sku."';");
                                    
    //             //                     if($result->num_rows > 0) {
    //             //                         $current_product    = mysqli_fetch_object($result);
    //             //                         $productid          = $current_product->productid;
    //             //                         $ean                = $current_product->ean;
    //             //                         $sql                = "SELECT * FROM prices WHERE channel_id=".$channel_data->idchannel." AND country='".$country."' AND sku='".$sku."'";
    //             //                         $result             = mysqli_query($conn, $sql);
                                        
    //             //                         if ($result->num_rows > 0) {
    //             //                             $existingProduct = mysqli_fetch_object($result);
    //             //                             if($existingProduct->online_quentity == null) {
    //             //                                 $sql    = "UPDATE prices SET online_quentity= ".$quantity.", last_update_qty_date='".date('Y-m-d H:i:s')."' WHERE channel_id=".$channel_data->idchannel." AND country='".$country."' AND sku='".$sku."'";
    //             //                                 $result = mysqli_query($conn, $sql);
    //             //                                 echo("Update online quantity for ".$sku."<br>");
    //             //                             } else {
    //             //                                 if($quantity > 0) {
    //             //                                     $sql    = "UPDATE prices SET online_quentity= ".$quantity.", last_update_qty_date='".date('Y-m-d H:i:s')."' WHERE channel_id=".$channel_data->idchannel." AND country='".$country."' AND sku='".$sku."'";
    //             //                                     $result = mysqli_query($conn, $sql);
    //             //                                     echo("Update online quantity for ".$sku."<br>");
    //             //                                 }
    //             //                             }
    //             //                         }
    //             //                     } 
                                    
    //             //                 }  
    //             //                 sleep(60);
    //             //             } 
    //             //             catch (\Exception $e) { 
    //             //                 echo 'Message: ' .$e->getMessage(); 
    //             //             }      
    //             //         }
    //             //     }
    //             // }

    //             // break;
    //         }
    //     }
    // }

    echo "end";
?>