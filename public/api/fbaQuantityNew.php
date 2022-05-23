<?php 
    ini_set('max_execution_time', 1500000);
    ini_set('memory_limit', -1);
    require(__DIR__ .'/config.php');
    require(__DIR__ .'/vendor/autoload.php');

    $sql        = "SELECT * FROM product";
    $result     = mysqli_query($conn, $sql);
    $products   = array();
    while($row  = mysqli_fetch_object($result)){
        $products[] = $row;
    } 

    $sql        = "SELECT * FROM channel WHERE sync = 'Automatic Synch with: Amazon'";
    $result     = mysqli_query($conn, $sql);
    
    $channels   = array();
    while($row  = mysqli_fetch_object($result)){
        $channels[] = $row;
    } 

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
    
    foreach($channels as $channel_data) {
        set_time_limit(0);
        $online_price       = 0.00;
        $online_shipping    = 0.00;

        if($channel_data->aws_acc_key_id!='' && $channel_data->aws_secret_key_id!='' && $channel_data->merchant_id!='' && $channel_data->market_place_id!='' && $channel_data->mws_auth_token!=''){
            mysqli_query($conn, "UPDATE tbl_fba SET active=0 WHERE channel = ".$channel_data->idchannel);
            $marketplaceIds = ['A1PA6795UKMFR9'];
            $countryArr     = ['DE'];
            $existingProductFlag = 0;
            for($k=0; $k<count($marketplaceIds); $k++) {
                $client = new MCS\MWSClient([
                    'Marketplace_Id'    => $marketplaceIds[$k],
                    'Seller_Id'         => $channel_data->merchant_id,
                    'Access_Key_ID'     => $channel_data->aws_acc_key_id,
                    'Secret_Access_Key' => $channel_data->aws_secret_key_id,
                    'MWSAuthToken'      => $channel_data->mws_auth_token
                ]);

                //Get all ReportIds
                $reportRequestStatus = $client->GetReportList(['_GET_FBA_MYI_UNSUPPRESSED_INVENTORY_DATA_'], $ItemCondition = null);
                if(isset($reportRequestStatus['GetReportListResult'])) {
                    if(isset($reportRequestStatus['GetReportListResult']['ReportInfo'])) {
                        $reportIds = $reportRequestStatus['GetReportListResult']['ReportInfo'];
                         $reportArray = [];
                        if(!isset($reportIds[0])){
                            $reportArray[] = $reportIds;
                        }else{
                            $reportArray = $reportIds;
                        }
                        foreach($reportArray as $item) {
                            $reportId = $item['ReportRequestId'];
                            
                            try { 
                                $reports = $client->GetReport($reportId, $ItemCondition = null);     
                                foreach($reports as $report) {
                                    $row = array_keys($report);
                                    $values = array_values($report);
                                    if(isset($values[0])){
                                        
                                        //$key = $row[0];
                                        $row = explode(',', $values[0]);

                                        $result = mysqli_query($conn, "SELECT * FROM tbl_fba WHERE channel=".$channel_data->idchannel." AND asin='".$row[2]."'");
                                        if($result->num_rows == 0 && $row[8] == 'Yes') {

                                            echo "<span style='color: red;'> Warning: The product ".$row[2]." is not FBA more</span><br>";
                                            $result = mysqli_query($conn, "SELECT * FROM product WHERE asin='".$row[2]."'");
                                            if($result->num_rows > 0) {
                                                 $sql    = "INSERT INTO tbl_fba SET channel ='".$channel_data->idchannel."', actuallevel= ".$row[10].", active='".($row[8] == 'Yes' ? 1 : 0)."', dateupdate='".date('Y-m-d H:i:s')."', country='".$country."', productid='".$row[0]."', sellable='".$row[4]."', asin='".$row[2]."', sku ='".$row[0]."'"; 
                                                mysqli_query($conn, $sql);

                                                $warnmessage = "Warning: The product ".$row[0]." is added in FBA table. Please fill up the rest of the information.";
                                                mysqli_query($conn, "INSERT INTO tbl_open_activities SET dateTime='".date('Y-m-d H:i:s')."', issues='".$warnmessage."'");

                                                echo "<span style='color: yellow; background:black'>Warning: The product ".$sku." is not in the FBA table. The product is added in FBA table. Please fill up the rest of the information.</span>".'<br>';
                                            }else{
                                                $warnmessage = "ASIN '.$row[2].' DON\'T FOUND, PLEASE CHECK";
                                                mysqli_query($conn, "INSERT INTO tbl_open_activities SET dateTime='".date('Y-m-d H:i:s')."', issues='".$warnmessage."'");
                                                echo "<span style='color: red;'>".$warnmessage."</span><br>";
                                            }

                                        } else {

                                            $sql    = "UPDATE tbl_fba SET active='".($row[8] == 'Yes' ? 1 : 0)."', actuallevel= ".$row[10].", dateupdate='".date('Y-m-d H:i:s')."' WHERE channel=".$channel_data->idchannel." AND asin='".$row[2]."'";
                                            $result = mysqli_query($conn, $sql);

                                            $warnmessage = "Table fba updated for asin ".$row[2];
                                            echo "<span style='color: green;'>".$warnmessage."</span><br>";
                                        }   
                                       // echo '<pre>'; print_r($values); echo '</pre>'; //exit();
                                    }
                                }
                               // sleep(10);
                            } 
                            catch (\Exception $e) { 
                                echo 'Message: ' .$e->getMessage(); 
                            }      
                            
                            break;
                        }
                    }
                }
            }
        }
    }
    echo "end";
?>