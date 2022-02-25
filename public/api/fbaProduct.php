<?php 
    ini_set('max_execution_time', 600000);
    ini_set('memory_limit', '500M');
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
    $noneExistingProduct    = [];
    $noneExistingFBAProduct = [];
    $numProducts            = count($products);
    $count                  = 0;
    $lastAsin               = "";

    $test = 0;
    
    foreach($channels as $channel_data) {
        $online_price       = 0.00;
        $online_shipping    = 0.00;

        if($channel_data->aws_acc_key_id!='' && $channel_data->aws_secret_key_id!='' && $channel_data->merchant_id!='' && $channel_data->market_place_id!='' && $channel_data->mws_auth_token!=''){
            $marketplaceIds = ['A1PA6795UKMFR9'];
            $countryArr     = ['DE'];
            $existingProductFlag = 0;
            mysqli_query($conn, "UPDATE tbl_fba SET active=0 WHERE channel = ".$channel_data->idchannel);
            for($k=0; $k<count($marketplaceIds); $k++) {
                $client = new MCS\MWSClient([
                    'Marketplace_Id'    => $marketplaceIds[$k],
                    'Seller_Id'         => $channel_data->merchant_id,
                    'Access_Key_ID'     => $channel_data->aws_acc_key_id,
                    'Secret_Access_Key' => $channel_data->aws_secret_key_id,
                    'MWSAuthToken'      => $channel_data->mws_auth_token
                ]);
                //Get all ReportIds
                $reportRequestStatus = $client->GetReportList(['_GET_AFN_INVENTORY_DATA_'], $ItemCondition = null);
                if(isset($reportRequestStatus['GetReportListResult'])) {
                    if(isset($reportRequestStatus['GetReportListResult']['ReportInfo'])) {
                        $reportIds = $reportRequestStatus['GetReportListResult']['ReportInfo'];
                        foreach($reportIds as $item) {
                            $reportId = $item['ReportRequestId'];
                            sleep(60);
                            try { 
                                $reports = $client->GetReport($reportId, $ItemCondition = null);     
                                foreach($reports as $report) {
                                    $quantity   = $report['Quantity Available'];
                                    $country    = $countryArr[$k];
                                    $asin       = $report['asin'];
                                    $sku        = $report['seller-sku'];
                                    $sellable   = $report['Warehouse-Condition-code'];
                                    // echo "listing-id/".$report['listing-id']."-------asin1/".$report['asin1']."-------price/".$report['price']."-------expedited-shipping/".$report['expedited-shipping']
                                    //    ."-------quantity/".$report['quantity']."-------product-id/".$report['product-id']."-------fulfillment-channel/".$report['fulfillment-channel']."<br>";
                                    // $current_product        = mysqli_fetch_object(mysqli_query($conn, "SELECT * FROM product WHERE sku='".$sku."'"));

                                    // if(empty($current_product)) {
                                    //     echo "'".$sku."' is not existing in our product list.<br>";
                                    // } 
                                    $result = mysqli_query($conn, "SELECT * FROM tbl_fba WHERE channel=".$channel_data->idchannel." AND asin='".$asin."'");
                                    if($result->num_rows > 0) {
                                        $current_fba_product    = mysqli_fetch_object(mysqli_query($conn, "SELECT * FROM tbl_fba WHERE channel=".$channel_data->idchannel." AND asin='".$asin."'"));
                                        if($sellable == "SELLABLE") {
                                            $sql    = "UPDATE tbl_fba SET actuallevel= ".$quantity.", sellable='".$sellable."', dateupdate='".date('Y-m-d H:i:s')."', active=1 WHERE idfba = ".$current_fba_product->idfba;
                                            $result = mysqli_query($conn, $sql);

                                            $product_result = mysqli_query($conn, "SELECT * FROM product WHERE asin='".$asin."'");
                                            if($product_result->num_rows == 0) {
                                                echo "<span style='color: red;'>Warning: The product ".$sku." is not in the product table. Please check in the table this product.</span> <br>";
                                                $warnmessage = "Warning: The product ".$sku." is not in the product table. Please check in the table this product.";
                                                $sql = "INSERT INTO tbl_open_activities SET dateTime='".date('Y-m-d H:i:s')."', issues='".$warnmessage."'";
                                                mysqli_query($conn, $sql);
                                            }
                                        }
                                    } else {
                                        if($sellable == "SELLABLE" && $quantity > 0) {
                                            $sql    = "INSERT INTO tbl_fba SET channel ='".$channel_data->idchannel."', actuallevel= ".$quantity.", active=1, dateupdate='".date('Y-m-d H:i:s')."', country='".$country."', productid='".$sku."', sellable='".$sellable."', asin='".$asin."', sku ='".$sku."'"; 
                                            mysqli_query($conn, $sql);
                                            echo "Warning: The product ".$sku." is not in the FBA table. The product is added in FBA table. Please fill up the rest of the information. <br>";
                                            $warnmessage = "Warning: The product ".$sku." is added in FBA table. Please fill up the rest of the information.";

                                            $sql = "INSERT INTO tbl_open_activities SET dateTime='".date('Y-m-d H:i:s')."', issues='".$warnmessage."'";
                                            mysqli_query($conn, $sql);

                                            $product_result = mysqli_query($conn, "SELECT * FROM product WHERE asin='".$asin."'");
                                            if($product_result->num_rows == 0) {
                                                echo "<span style='color: red;'>Warning: The product ".$sku." is not in the product table. Please check in the table this product.</span> <br>";
                                                $warnmessage = "Warning: The product ".$sku." is not in the product table. Please check in the table this product.";
                                                $sql = "INSERT INTO tbl_open_activities SET dateTime='".date('Y-m-d H:i:s')."', issues='".$warnmessage."'";
                                                mysqli_query($conn, $sql);
                                            }
                                        }
                                    }                                
                                } 
                            } 
                            catch (\Exception $e) { 
                                echo 'Message: ' .$e->getMessage(); 
                            }     
                            
                            break;
                        }
                    }
                }
            }

            $sql        = "SELECT * FROM tbl_fba WHERE active=0 AND channel=".$channel_data->idchannel;
            $result     = mysqli_query($conn, $sql);

            while($row  = mysqli_fetch_object($result)){
                echo "Warning: The product ".$row->sku." is not FBA more<br>";
            } 
        }
    }
    
    echo "end";
?>