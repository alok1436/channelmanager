<?php 
    ini_set('max_execution_time', 60000);
    ini_set('memory_limit', '500M');
    set_time_limit(0); 
    require(__DIR__ .'/config.php');
    require(__DIR__ .'/vendor/autoload.php');
    
    if(isset($_GET['channelforchecknewprouducts']) && $_GET['channelforchecknewprouducts'] != "") {
        $channelforchecknewprouducts = $_GET['channelforchecknewprouducts'];
        $sql        = "SELECT * FROM channel WHERE idchannel=".$channelforchecknewprouducts;
        $result     = mysqli_query($conn, $sql);
    } else {
        $sql        = "SELECT * FROM channel";
        $result     = mysqli_query($conn, $sql);
    }
    
    $channels   = array();
    while($row  = mysqli_fetch_object($result)){
        $channels[] = $row;
    } 

    foreach($channels as $channel_data) {
        if($channel_data->aws_acc_key_id!='' && $channel_data->aws_secret_key_id!='' && $channel_data->merchant_id!='' && $channel_data->market_place_id!='' && $channel_data->mws_auth_token!=''){
            $marketplaceIds = ['A13V1IB3VIYZZH', 'A1F83G8C2ARO7P', 'A1PA6795UKMFR9', 'A1RKKUPIHCS9HS', 'APJ6JRA9NG5V4'];
            $countryArr     = ['FR', 'UK', 'DE', 'ES', 'IT'];
            for($k=0; $k<count($marketplaceIds); $k++) {
                $client = new MCS\MWSClient([
                    'Marketplace_Id'    => $marketplaceIds[$k],
                    'Seller_Id'         => $channel_data->merchant_id,
                    'Access_Key_ID'     => $channel_data->aws_acc_key_id,
                    'Secret_Access_Key' => $channel_data->aws_secret_key_id,
                    'MWSAuthToken'      => $channel_data->mws_auth_token
                ]);
                $reportRequestStatus = $client->GetReportList(['_GET_MERCHANT_LISTINGS_DATA_'], $ItemCondition = null);
                if(isset($reportRequestStatus['GetReportListResult'])) {
                    if(isset($reportRequestStatus['GetReportListResult']['ReportInfo'])) {
                        $reportIds = $reportRequestStatus['GetReportListResult']['ReportInfo'];
                        foreach($reportIds as $item) {
                            if(isset($item['ReportRequestId'])) {
                                sleep(60);
                                $reportId = $item['ReportRequestId'];
                                echo $reportId."<br>";
                                try { 
                                    $reports = $client->GetReport($reportId, $ItemCondition = null);
                                    foreach($reports as $report) {
                                        $asinorean          = $report['product-id'];
                                        $sku                = $report['seller-sku'];
                                        $country            = $countryArr[$k];
                                        $current_product    = mysqli_fetch_object(mysqli_query($conn, "SELECT * FROM product WHERE ean='".$asinorean."' OR asin='".$asinorean."'"));
                                        if(empty($current_product)) {
                                            $sql        = "SELECT * FROM tbl_none_product WHERE sku='".$sku."'";
                                            echo "sql1".$sql."<br>";
                                            $result     = mysqli_query($conn, $sql);
                                            if ($result->num_rows == 0) {
                                                $sql    = "INSERT INTO tbl_none_product SET channelId ='".$channel_data->idchannel."', asin='".$asinorean."', ean='".$asinorean."', country='".$country."', sku ='".$sku."'"; 
                                                echo "sql2".$sql."<br>";
                                                mysqli_query($conn, $sql);
                                            }
                                        }  
                                    }
                                } catch (\Exception $e) { 
                                    echo 'Message: ' .$e->getMessage()."<br>"; 
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    echo "success";
?>