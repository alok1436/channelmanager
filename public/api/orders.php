<?php
    //ini_set('max_execution_time', 1500000);
    ini_set('memory_limit', -1);

    require(__DIR__ .'/config.php');
    require(__DIR__ .'/vendor/autoload.php');
    $sqlFetchAssoc=[];
    $message='';
    if(isset($_GET['channelId']) && $_GET['channelId'] != "") {
        $channelId = $_GET['channelId'];
        $sql    = "SELECT * FROM channel WHERE sync = 'Automatic Synch with: Amazon' AND idchannel='".$channelId."'";
        $sqlNextRecord          = "SELECT * FROM channel WHERE sync = 'Automatic Synch with: Amazon' AND idchannel > {$channelId} LIMIT 1";
        $sqlNextRecordQuery     = mysqli_query($conn, $sqlNextRecord);
        $sqlFetchAssoc          = mysqli_fetch_assoc($sqlNextRecordQuery);
    } else {
        $sql    = "SELECT * FROM channel WHERE sync = 'Automatic Synch with: Amazon'";
    }
    
    //$sql            = "SELECT * FROM channel WHERE sync = 'Automatic Synch with: Amazon' limit 1";
    //$sql            = "SELECT * FROM channel WHERE idchannel = 2";
    $data           = mysqli_query($conn, $sql);
    $countorder1    = 0;
    $chkapi         = 0;
    $arrass         = ['A13V1IB3VIYZZH','A1PA6795UKMFR9','APJ6JRA9NG5V4','A1F83G8C2ARO7P','A1RKKUPIHCS9HS'];
    //echo '<pre>'; print_r($row=mysqli_fetch_object($data)); echo '</pre>'; exit();
    $noneExistingProduct = array();
    while ($row=mysqli_fetch_object($data)) {
        try{
        
        if($row->aws_acc_key_id != NULL || $row->aws_secret_key_id != NULL || $row->merchant_id != NULL || $row->market_place_id != NULL || $row->mws_auth_token != NULL) {  
            $aws_acc_key_id     = $row->aws_acc_key_id;  // these prod keys are different from sandbox keys
            $aws_secret_key_id  = $row->aws_secret_key_id;  // these prod keys are different from sandbox keys
            $merchant_id        = $row->merchant_id;
            $mws_auth_token     = $row->mws_auth_token;
            $shortname          = $row->shortname;   
            $idcompany          = $row->idcompany;    
            $vat                = $row->vat; 
            $platformid         = $row->platformid; 
            $idwarehouse        = $row->warehouse;
            $idchannel          = $row->idchannel; 
            $countryname        = $row->country;
            $warehouse          = $row->warehouse;

            $client = new MCS\MWSClient([
                'Marketplace_Id'    => 'A13V1IB3VIYZZH',
                'Seller_Id'         => $merchant_id,
                'Access_Key_ID'     => $aws_acc_key_id,
                'Secret_Access_Key' => $aws_secret_key_id,
                'MWSAuthToken'      => $row->mws_auth_token // Optional. Only use this key if you are a third party user/developer
            ]);

            $date     = date("Y-m-d", strtotime("-1 week"));
            $fromDate = new DateTime($date);
            
            $orders = $client->ListOrders($fromDate, $allMarketplaces = true, $states = ['Shipped','Unshipped']);
           // echo '<pre>'; print_r($orders); echo '</pre>'; exit();
            if(isset($orders['NextToken'])) {
                $nextToken = $orders['NextToken'];
                $orders    = $orders['ListOrders'];
                $nextTokenFlag = true;
            } else {
                $nextToken = '';
                $nextTokenFlag = false;
            }
           echo 'Getting orders for '.$row->shortname.'</br>';
            foreach ($orders as $order) {
                echo 'order id:'. $order['AmazonOrderId'].'</br>';
                // echo '<pre>'; print_r($orders); echo '</pre>';
                set_time_limit(0);
                if(isset($order['BuyerEmail'])) {
                    $BuyerEmail                  = $order['BuyerEmail'];
                } else {
                    $BuyerEmail                  = "";
                }
                
                $referenceorder         = $order['AmazonOrderId']; 
                $platformname           = $order['SalesChannel']; 
                if(isset($order['OrderTotal'])) {
                    $sum                    = $order['OrderTotal']['Amount'];
                    $currency               = $order['OrderTotal']['CurrencyCode'];
                } else {
                    $sum                    = 0;
                    $currency               = '';
                }
                $id                     = $order['AmazonOrderId'];
                $NumberOfItemsShipped   = $order['NumberOfItemsShipped'];
                $cdate                  = $order['PurchaseDate'];
                $fullstatus             = $order['FulfillmentChannel'];
                $dateweekcell           = date_create($cdate);
                $dateweek               = date_format($dateweekcell,"W");
                $newcdate               = date_create($cdate);
                $newcdateform           = date_format($newcdate,"Y/m/d");
                $QuantityPurchased      = $order['NumberOfItemsUnshipped'];
                $MarketplaceId          = $order['MarketplaceId'];
                if(isset($order['ShippingAddress']['StateOrRegion'])) {
                    $StateOrRegion          = $order['ShippingAddress']['StateOrRegion'];
                } else {
                    $StateOrRegion          = '';
                }

                $tracking               = '';
                $carref                 = '';
                if(isset($order['OrderStatus']) && $order['OrderStatus'] == "Shipped") {
                    $carref = "Shipped";
                }

                $print_shipping         = '';
                $platform               = $order['SalesChannel'];
                if(isset($order['ShippingAddress']['PostalCode'])) {
                    $PostalCode             = str_replace("'", "", $order['ShippingAddress']['PostalCode']);
                } else {
                    $PostalCode             = "";
                }
                if(isset($order['ShippingAddress']['City'])) {
                    $City                   = str_replace("'", "", $order['ShippingAddress']['City']);
                } else {
                    $City                   = "";
                }
                if(isset($order['ShippingAddress']['CountryCode'])) {
                    $country                = $order['ShippingAddress']['CountryCode'];
                } else {
                    $country                = "";
                }
                
                $shippingser            = $order['ShipServiceLevel'];
                $orderstaus             = $order['OrderStatus'];

                if($orderstaus == 'Pending'){
                    continue;
                }

                if(isset($order['TaxRegistrationDetails'])) {
                    $transactionId          = isset($order['TaxRegistrationDetails']['member']['taxRegistrationId']) ? $order['TaxRegistrationDetails']['member']['taxRegistrationId'] :'';
                } else {
                    $transactionId = '';
                }
                $registeredtosolddayok  = 0;            
                $courierinformedok      = 0;           
                $trackinguploadedok     = 0;
                sleep(2);
                //echo $order['AmazonOrderId'];
                $items = $client->ListOrderItems($order['AmazonOrderId']);
                echo '<style>.vdd{display:none;}</style>';
                echo '<pre class="vdd">'; print_r($items); echo '</pre>';
                if($sum == 0) {

                }

                foreach($items as $item) {
                    $asin           = $item['ASIN'];
                    $sku            = $item['SellerSKU'];
                    $orderItemId    = $item['OrderItemId'];
                    $quantity       = $item['QuantityOrdered'];
                    $modelcode      = explode(" ", $sku)[0];
                    ///Check if the product is existing or not.
                    $sql = "SELECT * FROM product WHERE modelcode='".$modelcode."'";
                    $result = mysqli_query($conn, $sql);
                    if($result->num_rows == 0) {
                        echo "Warning: The product ".$sku." is added in Product table. Please fill up the rest of the information.<br>";

                        $warnmessage = "Warning: The product ".$sku." is added in Product table. Please fill up the rest of the information.";

                        $sql = "INSERT INTO tbl_open_activities SET dateTime='".date('Y-m-d H:i:s')."', issues='".$warnmessage."'";
                        mysqli_query($conn, $sql);

                        array_push($noneExistingProduct, $sku);
                        $sql    = "INSERT INTO product SET modelcode ='".$modelcode."', nameshort='".$modelcode."', namelong='".$modelcode."', asin='".$asin."', sku='".$sku."', active='Yes', virtualkit ='No'"; 
                        mysqli_query($conn, $sql);
                        $productId = mysqli_insert_id($conn);
                        $sql    = "INSERT INTO lagerstand SET productid ='".$productId."', idwarehouse='".$idwarehouse."', quantity=0"; 
                        mysqli_query($conn, $sql);
                    } else {
                        $product = mysqli_fetch_object($result);
                        $productId = $product->productid;
                    }
                    
                    $registeredtolagerstandok = 0; 
                    $result = mysqli_query($conn, "SELECT * FROM orderitem WHERE referenceorder = '".$id."' LIMIT 1");
                    $idpayment = "Amazon";
                    if($result->num_rows > 0) {
                        $dts            = mysqli_fetch_object($result);
                        $prodid         = $dts->productid;
                        $orderItemId1   = $dts->productid;
                        $idpayment      = $dts->idpayment;  
                        $quantity       = $item['QuantityOrdered'];
                        $country        = $dts->country;  
                        $sum            = $dts->sum; 
                        $idwarehouse    = $dts->idwarehouse; 
                        $multiorder     = $dts->referenceorder;
                        $cc             = array('IT' => 3 , 'DE' => 4 , 'FR' => 1 );

                        $result = mysqli_query($conn, "SELECT * FROM orderitem WHERE referenceorder = '".$id."' AND order_item_id = '".$orderItemId."' LIMIT 1");
                        if($result->num_rows == 0) {
                            // if( $fullstatus =='AFN') {
                            //     $carref = "FBA";
                            //     $print_shipping = 1;
                            // } else {
                            //     $print_shipping = 0;
                            // }

                            if($sum == 0 || $quantity == 0) {
                                $print_shipping             = 1;
                                $registeredtosolddayok      = 1;
                                $registeredtolagerstandok   = 1;
                                $courierinformedok          = 1;
                                $trackinguploadedok         = 1;
                                $idpayment                  = "Deleted";
                            } else {
                                if($orderstaus == "Shipped") {
                                    $print_shipping             = 1;
                                    $registeredtosolddayok      = 1;
                                    $registeredtolagerstandok   = 1;
                                    $courierinformedok          = 1;
                                    $trackinguploadedok         = 1;
                                } else {
                                    $print_shipping             = 0;
                                    $registeredtosolddayok      = 0;
                                    $registeredtolagerstandok   = 0;
                                    $courierinformedok          = 0;
                                    $trackinguploadedok         = 0;
                                }
                            }
                            $asin           = $item['ASIN'];
                            $sku            = $item['SellerSKU'];
                            $orderItemId    = $item['OrderItemId'];
                            $quantity       = $item['QuantityOrdered'];
                            $modelcode      = explode(" ", $sku)[0];
                            $sql = "INSERT INTO orderitem (idorderplatform, registeredtolagerstandok, multiorder, productid, referenceorder, sync, idcompany, referencechannel, weeksell, datee, quantity, sum, idpayment, idwarehouse, platformname, referencechannelname, country, email, currency, plz, city, region, order_item_id,inv_vat, email1, plz1, ship_service_level, transactionId, registeredtosolddayok, courierinformedok, trackinguploadedok, carriername, printedshippingok)
                                    VALUES ( '".$id."', ".$registeredtolagerstandok.", '".$multiorder."', '".$productId."', '".$id."','Synch with Amazon','".$idcompany."','".$idchannel."','".$dateweek."','".$newcdateform."','".$quantity."' ,'".$sum."','".$idpayment."','".$warehouse."','".$platform."','".$shortname."','".$countryname."','".$BuyerEmail."','".$currency."','".$PostalCode."','".$City."','".$StateOrRegion."','".$orderItemId."','".$vat."','".$BuyerEmail."','".$PostalCode."','".$shippingser."','".$transactionId."'  , ".$registeredtosolddayok."  , ".$courierinformedok."  , ".$trackinguploadedok." , '".$carref."' , ".$print_shipping.")";
                            
                            if($id == "408-1482479-5488308") {
                                echo $city."-----------1";
                            }
                            echo $sql.' hello<br>';
                            mysqli_query($conn, $sql);  
                        } else {
                            if($sum == 0 || $quantity == 0) {
                                $print_shipping             = 1;
                                $registeredtosolddayok      = 1;
                                $registeredtolagerstandok   = 1;
                                $courierinformedok          = 1;
                                $trackinguploadedok         = 1;
                                $idpayment                  = "Deleted";
                            } else {
                                if($orderstaus == "Shipped") {
                                    $print_shipping             = 1;
                                    $registeredtosolddayok      = 1;
                                    $registeredtolagerstandok   = 1;
                                    $courierinformedok          = 1;
                                    $trackinguploadedok         = 1;
                                } else {
                                    $print_shipping             = 0;
                                    $registeredtosolddayok      = 0;
                                    $registeredtolagerstandok   = 0;
                                    $courierinformedok          = 0;
                                    $trackinguploadedok         = 0;
                                }
                            }

                            $sql = "UPDATE orderitem SET sync = 'Synch with Amazon', registeredtosolddayok = '1', courierinformedok = '1', trackinguploadedok = '1', quantity = '".$quantity."' , carriername = '".$carref."', printedshippingok = '".$print_shipping."' WHERE idorderplatform= '".$id."'";
                            if($id == "408-1482479-5488308") {
                                echo $sql."-----------2";
                            }
                            echo $sql.'<br>';
                            mysqli_query($conn, $sql);
                        }
                    } else {
                        $currdate           = date("Y-m-d");
                        $last7date          = date("Y-m-d", strtotime("7 days ago"));
                        $newdateweek        = date("Y-m-d", strtotime($cdate));
                        // if( $fullstatus =='AFN'){
                        //     $carref = "FBA";
                        //     $print_shipping = 1;
                        // } else{
                        //     $print_shipping = 0;
                        // }

                        if($sum == 0 || $quantity == 0) {
                            $print_shipping             = 1;
                            $registeredtosolddayok      = 1;
                            $registeredtolagerstandok   = 1;
                            $courierinformedok          = 1;
                            $trackinguploadedok         = 1;
                            $idpayment                  = "Deleted";
                        } else {
                            if($orderstaus == "Shipped") {
                                $print_shipping             = 1;
                                $registeredtosolddayok      = 1;
                                $registeredtolagerstandok   = 1;
                                $courierinformedok          = 1;
                                $trackinguploadedok         = 1;
                            } else {
                                $print_shipping             = 0;
                                $registeredtosolddayok      = 0;
                                $registeredtolagerstandok   = 0;
                                $courierinformedok          = 0;
                                $trackinguploadedok         = 0;
                            }
                        }

                        $sql = "INSERT INTO orderitem (idorderplatform, tracking, registeredtolagerstandok, productid, referenceorder, sync, idcompany, referencechannel, weeksell, datee, quantity, sum, idpayment, idwarehouse, platformname, referencechannelname, country, email, currency, plz, city, region, order_item_id,inv_vat, email1, plz1, ship_service_level, transactionId, registeredtosolddayok, courierinformedok, trackinguploadedok, carriername, printedshippingok)
                                VALUES ( '".$id."', '".$tracking."', ".$registeredtolagerstandok.", '".$productId."', '".$id."','Synch with Amazon','".$idcompany."','".$idchannel."','".$dateweek."','".$newcdateform."','".$quantity."' ,'".$sum."','".$idpayment."','".$warehouse."','".$platform."','".$shortname."','".$countryname."','".$BuyerEmail."','".$currency."','".$PostalCode."','".$City."','".$StateOrRegion."','".$orderItemId."','".$vat."','".$BuyerEmail."','".$PostalCode."','".$shippingser."','".$transactionId."'  , ".$registeredtosolddayok."  , ".$courierinformedok."  , ".$trackinguploadedok." , '".$carref."' , ". $print_shipping.")";
                        if($id == "408-1482479-5488308") {
                            echo $sql."-----------3";
                        }
                        echo $sql.' world<br>';
                        mysqli_query($conn, $sql);
                        /* ##wtd end */
                        $arrListOrderItemsPayload = array('AmazonOrderId' => "$id");
                        $countorder1++;
                    }
                }
            }

            while($nextTokenFlag) {
                $orders = $client->ListOrdersByNextToken($nextToken);
                if(isset($orders['NextToken'])) {
                    $nextToken = $orders['NextToken'];
                    $orders    = $orders['ListOrders'];
                    $nextTokenFlag = true;
                } else {
                    $nextToken = '';
                    $nextTokenFlag = false;
                }
                foreach ($orders as $order) {                    
                    if(isset($order['BuyerEmail'])) {
                        $BuyerEmail                  = $order['BuyerEmail'];
                    } else {
                        $BuyerEmail                  = "";
                    }
                    
                    $referenceorder         = $order['AmazonOrderId']; 
                    $platformname           = $order['SalesChannel']; 
                    if(isset($order['OrderTotal'])) {
                        $sum                    = $order['OrderTotal']['Amount'];
                        $currency               = $order['OrderTotal']['CurrencyCode'];
                    } else {
                        $sum                    = 0;
                        $currency               = '';
                    }
                    $id                     = $order['AmazonOrderId'];
                    $NumberOfItemsShipped   = $order['NumberOfItemsShipped'];
                    $cdate                  = $order['PurchaseDate'];
                    $fullstatus             = $order['FulfillmentChannel'];
                    $dateweekcell           = date_create($cdate);
                    $dateweek               = date_format($dateweekcell,"W");
                    $newcdate               = date_create($cdate);
                    $newcdateform           = date_format($newcdate,"Y/m/d");
                    $QuantityPurchased      = $order['NumberOfItemsUnshipped'];
                    $MarketplaceId          = $order['MarketplaceId'];
                    if(isset($order['ShippingAddress']['StateOrRegion'])) {
                        $StateOrRegion          = $order['ShippingAddress']['StateOrRegion'];
                    } else {
                        $StateOrRegion          = '';
                    }
                    $carref                 = '';
                    $tracking               = '';
                    if(isset($order['OrderStatus']) && $order['OrderStatus'] == "Shipped") {
                        $carref = "Shipped";
                    }
                    $print_shipping         = '';
                    $platform               = $order['SalesChannel'];
                    if(isset($order['ShippingAddress']['PostalCode'])) {
                        $PostalCode             = $order['ShippingAddress']['PostalCode'];
                    } else {
                        $PostalCode             = "";
                    }
                    if(isset($order['ShippingAddress']['City'])) {
                        $City                   = str_replace("'", "", $order['ShippingAddress']['City']);;
                    } else {
                        $City                   = "";
                    }
                    if(isset($order['ShippingAddress']['CountryCode'])) {
                        $country                   = $order['ShippingAddress']['CountryCode'];
                    } else {
                        $country                   = "";
                    }
                    
                    $shippingser            = $order['ShipServiceLevel'];
                    $orderstaus             = $order['OrderStatus'];
                    if(isset($order['TaxRegistrationDetails'])) {
                        $transactionId          = isset($order['TaxRegistrationDetails']['member']['taxRegistrationId']) ? $order['TaxRegistrationDetails']['member']['taxRegistrationId'] :'';
                    } else {
                        $transactionId = '';
                    }
                    $registeredtosolddayok  = 0;            
                    $courierinformedok      = 0;           
                    $trackinguploadedok     = 0;
                    sleep(2);
                    $items = $client->ListOrderItems($order['AmazonOrderId']);
                    foreach($items as $item) {
                        $asin           = $item['ASIN'];
                        $sku            = $item['SellerSKU'];
                        $orderItemId    = $item['OrderItemId'];
                        $quantity       = $item['QuantityOrdered'];
                        $modelcode      = explode(" ", $sku)[0];
                        ///Check if the product is existing or not.
                        $sql = "SELECT * FROM product WHERE sku='".$sku."'";
                        $result = mysqli_query($conn, $sql);
                        if($result->num_rows == 0) {
                            array_push($noneExistingProduct, $sku);
                            $sql    = "INSERT INTO product SET modelcode ='".$modelcode."', nameshort='".$modelcode."', namelong='".$modelcode."', asin='".$asin."', sku='".$sku."', active='Yes', virtualkit ='No'"; 
                            mysqli_query($conn, $sql);
                            $productId = mysqli_insert_id($conn);
                            $sql    = "INSERT INTO lagerstand SET productid ='".$productId."', idwarehouse='".$idwarehouse."', quantity=0"; 
                            mysqli_query($conn, $sql);
                        } else {
                            $product = mysqli_fetch_object($result);
                            $productId = $product->productid;
                        }
                    
                        $result = mysqli_query($conn, "SELECT * FROM orderitem WHERE referenceorder = '".$id."' LIMIT 1");
                        if($result->num_rows > 0) {
                            $dts            = mysqli_fetch_object($result);
                            $prodid         = $dts->productid;  
                            $idpayment      = $dts->idpayment;  
                            $quantity       = $dts->quantity;  
                            $country        = $dts->country;  
                            $sum            = $dts->sum; 
                            $idwarehouse    = $dts->idwarehouse; 
                            $multiorder     = $dts->referenceorder;
                            $cc             = array('IT' => 3 , 'DE' => 4 , 'FR' => 1 );
    
                            $result = mysqli_query($conn, "SELECT * FROM orderitem WHERE referenceorder = '".$id."' AND order_item_id = '".$orderItemId."' LIMIT 1");
                            if($result->num_rows == 0) {
                                // if( $fullstatus =='AFN') {
                                //     $carref = "FBA";
                                //     $print_shipping = 1;
                                // } else {
                                //     $carref = '';
                                //     $print_shipping = 0;
                                // }
    
                                if($sum == 0 || $quantity == 0) {
                                    $print_shipping             = 1;
                                    $registeredtosolddayok      = 1;
                                    $registeredtolagerstandok   = 1;
                                    $courierinformedok          = 1;
                                    $trackinguploadedok         = 1;
                                    $idpayment                  = "Deleted";
                                } else {
                                    if($orderstaus == "Shipped") {
                                        $print_shipping             = 1;
                                        $registeredtosolddayok      = 1;
                                        $registeredtolagerstandok   = 1;
                                        $courierinformedok          = 1;
                                        $trackinguploadedok         = 1;
                                    } else {
                                        $print_shipping             = 0;
                                        $registeredtosolddayok      = 0;
                                        $registeredtolagerstandok   = 0;
                                        $courierinformedok          = 0;
                                        $trackinguploadedok         = 0;
                                    }
                                }
                                $sql = "INSERT INTO orderitem (idorderplatform, registeredtolagerstandok, multiorder, productid, referenceorder, sync, idcompany, referencechannel, weeksell, datee, quantity, sum, idpayment, idwarehouse, platformname, referencechannelname, country, email, currency, plz, city, region, order_item_id,inv_vat, email1, plz1, ship_service_level, transactionId, registeredtosolddayok, courierinformedok, trackinguploadedok, carriername, printedshippingok)
                                        VALUES ( '".$id."', ".$registeredtolagerstandok.", '".$multiorder."', '".$productId."', '".$id."','Synch with Amazon','".$idcompany."','".$idchannel."','".$dateweek."','".$newcdateform."','".$quantity."' ,'".$sum."','".$idpayment."','".$warehouse."','".$platform."','".$shortname."','".$countryname."','".$BuyerEmail."','".$currency."','".$PostalCode."','".$City."','".$StateOrRegion."','".$orderItemId."','".$vat."','".$BuyerEmail."','".$PostalCode."','".$shippingser."','".$transactionId."'  , ".$registeredtosolddayok."  , ".$courierinformedok."  , ".$trackinguploadedok." , '".$carref."' , ". $print_shipping.")";
                                mysqli_query($conn, $sql);  

                                echo $sql .'<br>';
                            } else {
                                // if( $fullstatus =='AFN') {
                                //     $carref = "FBA";
                                //     $print_shipping = 1;
                                // } else {
                                //     $print_shipping = 0;
                                // }
                                $sql = "UPDATE orderitem SET sync = 'Synch with Amazon', registeredtosolddayok = '1', courierinformedok = '1', trackinguploadedok = '1', carriername = '".$carref."', printedshippingok = '".$print_shipping."' WHERE idorderplatform= '".$id."'";
                                mysqli_query($conn, $sql);
                                echo $sql .'<br>';
                            }
                        } else {
                            $currdate           = date("Y-m-d");
                            $last7date          = date("Y-m-d", strtotime("7 days ago"));
                            $newdateweek        = date("Y-m-d", strtotime($cdate));
                            // if( $fullstatus =='AFN'){
                            //     $carref = "FBA";
                            //     $print_shipping = 1;
                            // } else{
                            //     $carref = "";
                            //     $print_shipping = 0;
                            // }
    
                            if($sum == 0 || $quantity == 0) {
                                $print_shipping             = 1;
                                $registeredtosolddayok      = 1;
                                $registeredtolagerstandok   = 1;
                                $courierinformedok          = 1;
                                $trackinguploadedok         = 1;
                                $idpayment                  = "Deleted";
                            } else {
                                if($orderstaus == "Shipped") {
                                    $print_shipping             = 1;
                                    $registeredtosolddayok      = 1;
                                    $registeredtolagerstandok   = 1;
                                    $courierinformedok          = 1;
                                    $trackinguploadedok         = 1;
                                } else {
                                    $print_shipping             = 0;
                                    $registeredtosolddayok      = 0;
                                    $registeredtolagerstandok   = 0;
                                    $courierinformedok          = 0;
                                    $trackinguploadedok         = 0;
                                }
                            }

                            if($newdateweek >= $last7date){
                                $sql = "INSERT INTO orderitem (idorderplatform, tracking, registeredtolagerstandok, productid, referenceorder, sync, idcompany, referencechannel, weeksell, datee, quantity, sum, idpayment, idwarehouse, platformname, referencechannelname, country, email, currency, plz, city, region, order_item_id,inv_vat, email1, plz1, ship_service_level, transactionId, registeredtosolddayok, courierinformedok, trackinguploadedok, carriername, printedshippingok)
                                        VALUES ( '".$id."', '".$tracking."', ".$registeredtolagerstandok.", '".$productId."', '".$id."','Synch with Amazon','".$idcompany."','".$idchannel."','".$dateweek."','".$newcdateform."','".$quantity."' ,'".$sum."','Amazon','".$warehouse."','".$platform."','".$shortname."','".$countryname."','".$BuyerEmail."','".$currency."','".$PostalCode."','".$City."','".$StateOrRegion."','".$orderItemId."','".$vat."','".$BuyerEmail."','".$PostalCode."','".$shippingser."','".$transactionId."'  , ".$registeredtosolddayok."  , ".$courierinformedok."  , ".$trackinguploadedok." , '".$carref."' , ". $print_shipping.")";
                                mysqli_query($conn, $sql);    
                                echo $sql .'<br>';
                            }
                            /* ##wtd end */
                            $arrListOrderItemsPayload = array('AmazonOrderId' => "$id");
                            $countorder1++;
                        }
                    }
                }
            }
        }
        
        echo 'orders synced for '.$row->shortname.'</br>';
        }catch(Exception $e){
            echo $e->getMessage().$row->shortname.'</br>';
        }
    }

    //$noneExistingProduct['idchannel'] = !empty($sqlFetchAssoc) ? $sqlFetchAssoc['idchannel'] : '';
    //$noneExistingProduct['message']     = $message;
    echo json_encode($noneExistingProduct);
?>