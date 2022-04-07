<?php
    ini_set('memory_limit', -1);
    require(__DIR__ .'/config.php');

    $sql            = "SELECT * FROM channel where devid != ''";
    //$sql            = "SELECT * FROM channel where idchannel = 9";
    $data           = mysqli_query($conn, $sql);

    while($row  = mysqli_fetch_object($data)){
        $channels[] = $row;
    } 
    
    $noneExistingProduct = array();
    foreach($channels as $row) {
        
        if($row->devid != NULL || $row->appid != NULL || $row->certid != NULL || $row->usertoken != NULL) {  
            $devID              = $row->devid;
            $appID              = $row->appid;
            $certID             = $row->certid;
            $userToken          = $row->usertoken; 
            $shortname          = $row->shortname;   
            $idcompany          = $row->idcompany;    
            $vat                = $row->vat; 
            $platformid         = $row->platformid; 
            $idwarehouse        = $row->warehouse;
            $idchannel          = $row->idchannel; 
            $countryname        = $row->country;
            $warehouse          = $row->warehouse;
            $contry             = $row->country;
            $curr               = mysqli_fetch_object(mysqli_query($conn, "SELECT * FROM country WHERE shortname = '".$contry."' "));
            $currencie          = $curr->currency;
            
            $endpoint           = 'https://api.ebay.com/ws/api.dll'; // URL to call

            $headers = array(
                'Content-Type: text/xml',
                'X-EBAY-API-COMPATIBILITY-LEVEL:877',
                'X-EBAY-API-DEV-NAME:'.$devID,
                'X-EBAY-API-APP-NAME:'.$appID,
                'X-EBAY-API-CERT-NAME:'.$certID,
                'X-EBAY-API-SITEID:0',
                'X-EBAY-API-CALL-NAME:GetOrders'
            );

            $CreateTimeTo   = gmdate("Y-m-d\TH:i:s");
            $CreateTimeFrom = gmdate("Y-m-d",strtotime("-8 days")).'T00:00:00Z';

            $xml = "<?xml version='1.0' encoding='utf-8'?>
                    <GetOrdersRequest xmlns='urn:ebay:apis:eBLBaseComponents'>
                    <RequesterCredentials>
                        <eBayAuthToken>".$userToken."</eBayAuthToken>
                    </RequesterCredentials>
                    <CreateTimeFrom>".$CreateTimeFrom."</CreateTimeFrom>
                    <CreateTimeTo>".$CreateTimeTo."</CreateTimeTo>
                    <OrderRole>Seller</OrderRole>
                    </GetOrdersRequest>";

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
            
            if($array_data['OrderArray']) {
                $orders = $array_data['OrderArray']['Order'];                
                foreach($orders as $order) {  
                    
                    echo '<pre>'; print_r($order); echo '</pre>';
                    
                    if(isset($order['OrderID'])) {
                        $id                 = $order['OrderID'] ;
                        if(isset($order['TransactionArray']['Transaction']['CreatedDate'])) {
                            $cdate              = $order['TransactionArray']['Transaction']['CreatedDate'];
                            $dateweekcell       = date_create($cdate);
                            $dateweek           = date_format($dateweekcell,"W");
                            $newcdate           = date_create($cdate);
                            $newcdateform       = date_format($newcdate,"Y/m/d");

                            
                            if(isset($order['TransactionArray']['Transaction']['QuantityPurchased']) && !is_array($order['TransactionArray']['Transaction']['QuantityPurchased'])) {
                                $QuantityPurchased  = $order['TransactionArray']['Transaction']['QuantityPurchased'];
                            } else {
                                $QuantityPurchased  = "";
                            }

                            if(isset($order['TransactionArray']['Transaction']['SellingManagerProductDetails']['ProductName']) && !is_array($order['TransactionArray']['Transaction']['SellingManagerProductDetails']['ProductName'])) {
                                $ProductName        = mysqli_real_escape_string($conn, $order['TransactionArray']['Transaction']['SellingManagerProductDetails']['ProductName']);
                            } else {
                                $ProductName        = "";
                            }

                            if(isset($order['TransactionArray']['Transaction']['SellingManagerProductDetails']['CustomLabel']) && !is_array($order['TransactionArray']['Transaction']['SellingManagerProductDetails']['CustomLabel'])) {
                                $sku                = $order['TransactionArray']['Transaction']['SellingManagerProductDetails']['CustomLabel'];
                            } else {
                                $sku                = "";
                            }
                            echo '<pre>'; print_r($order); echo '</pre>';
                            if(!is_array($sku)) {
                                $modelcode          = explode(" ", $sku)[0];
                                $productfiv         = substr($ProductName,0,5);
                                $PaymentAmount      = isset($order['CheckoutStatus']['PaymentMethod']) ? $order['CheckoutStatus']['PaymentMethod']:'';
                                if($PaymentAmount == "None") {
                                    $PaymentAmount = "";
                                }
                                $Total              = $order['Total'];
                                $userid             = $order['BuyerUserID'];
                                $platform           = $order['TransactionArray']['Transaction']['Platform'];
                                $refid              = isset($order['MonetaryDetails']['Payments']['Payment']['ReferenceID']) ? $order['MonetaryDetails']['Payments']['Payment']['ReferenceID'] :''  ;
                                $name               = filter_var($order['ShippingAddress']['Name'],FILTER_SANITIZE_STRING,FILTER_FLAG_STRIP_HIGH);

                                if(isset($order['UserLastName']) && !is_array($order['UserLastName'])) {
                                    $lastname           = filter_var($order['UserLastName'],FILTER_SANITIZE_STRING,FILTER_FLAG_STRIP_HIGH);
                                } else {
                                    $lastname           = "";
                                }
                                if(is_array($order['ShippingAddress']['Street1'])) {
                                    $Street1            = "";
                                } else {
                                    $Street1            = filter_var($order['ShippingAddress']['Street1'],FILTER_SANITIZE_STRING,FILTER_FLAG_STRIP_HIGH);
                                }
                                if(is_array($order['ShippingAddress']['Street2'])) {
                                    $Street2            = "";
                                } else {
                                    $Street2            = filter_var($order['ShippingAddress']['Street2'],FILTER_SANITIZE_STRING,FILTER_FLAG_STRIP_HIGH);
                                }
                                if(is_array($order['ShippingAddress']['PostalCode'])) {
                                    $postalcode            = "";
                                } else {
                                    $postalcode            = $order['ShippingAddress']['PostalCode'];
                                }
                                if(is_array($order['ShippingAddress']['CityName'])) {
                                    $cityname            = "";
                                } else {
                                    $cityname            = filter_var($order['ShippingAddress']['CityName'],FILTER_SANITIZE_STRING,FILTER_FLAG_STRIP_HIGH);
                                }
                                if(is_array($order['ShippingAddress']['StateOrProvince'])) {
                                    $state            = "";
                                } else {
                                    $state            = filter_var($order['ShippingAddress']['StateOrProvince'],FILTER_SANITIZE_STRING,FILTER_FLAG_STRIP_HIGH);
                                }
                                if(isset($order['ShippingAddress']['Country']) && !is_array($order['ShippingAddress']['Country'])) {
                                    $countryname        = filter_var($order['ShippingAddress']['Country'],FILTER_SANITIZE_STRING,FILTER_FLAG_STRIP_HIGH);
                                } else {
                                    $countryname        = '';
                                }
                                
                                $phone              = '';
                                if(!is_array($order['ShippingAddress']['Phone']) && $order['ShippingAddress']['Phone'] != "Invalid Request") {
                                    $phone             = $order['ShippingAddress']['Phone']; 
                                }
                                $email              = '';

                                //print_r($order['TransactionArray']);

                                // foreach($order['TransactionArray']['Transaction'] as $data) {
                                //     print_r($data);
                                // }

                                if(!is_array($order['TransactionArray']['Transaction']['Buyer']['Email'])) {
                                    $email             = $order['TransactionArray']['Transaction']['Buyer']['Email']; 
                                }
                                $transactid         = '';
                                if(!is_array($order['TransactionArray']['Transaction']['TransactionID'])) {
                                    $transactid             = $order['TransactionArray']['Transaction']['TransactionID']; 
                                }
                                $itemid = '';
                                if(!is_array($order['TransactionArray']['Transaction']['Item']['ItemID'])) {
                                    $itemid             = $order['TransactionArray']['Transaction']['Item']['ItemID']; 
                                } 
                                $shippingser        = '';
                                if(!is_array($order['ShippingDetails']['ShippingServiceOptions']['ShippingService'])) {
                                    $shippingser        = $order['ShippingDetails']['ShippingServiceOptions']['ShippingService'];
                                }

                                $carrier        = '';
                                $tracking       = '';

                                if(isset($order['TransactionArray']['Transaction']['ShippingDetails']['ShipmentTrackingDetails'])) {
                                    if(isset($order['TransactionArray']['Transaction']['ShippingDetails']['ShipmentTrackingDetails']['ShippingCarrierUsed'])) {
                                        $carrier            = $order['TransactionArray']['Transaction']['ShippingDetails']['ShipmentTrackingDetails']['ShippingCarrierUsed'];
                                    }
                                    if(isset($order['TransactionArray']['Transaction']['ShippingDetails']['ShipmentTrackingDetails']['ShipmentTrackingNumber'])) {
                                        $tracking           = $order['TransactionArray']['Transaction']['ShippingDetails']['ShipmentTrackingDetails']['ShipmentTrackingNumber'];
                                    }
                                } else {
                                    $carrier        = '';
                                    $tracking       = '';
                                } 
                                
                                $sql = "SELECT * FROM product WHERE modelcode='".$modelcode."'";                                

                                $result = mysqli_query($conn, $sql);
                                if($result->num_rows == 0) {
                                    echo "Warning: The product ".$sku." is added in Product table. Please fill up the rest of the information.<br>";

                                    $warnmessage = "Warning: The product ".$sku." is added in Product table. Please fill up the rest of the information.";

                                    $sql = "INSERT INTO tbl_open_activities SET dateTime='".date('Y-m-d H:i:s')."', issues='".$warnmessage."'";
                                    echo $sql."<br>";
                                    mysqli_query($conn, $sql);
                                    
                                    array_push($noneExistingProduct, $sku);
                                    $sql    = "INSERT INTO product SET modelcode ='".$modelcode."', nameshort='".$modelcode."', namelong='".$modelcode."', sku='".$sku."', active='Yes', virtualkit ='No'"; 
                                    mysqli_query($conn, $sql);
                                    $productId = mysqli_insert_id($conn);
                                    $sql    = "INSERT INTO lagerstand SET productid ='".$productId."', idwarehouse='".$idwarehouse."', quantity=0"; 
                                    mysqli_query($conn, $sql);
                                } else {
                                    $product = mysqli_fetch_object($result);
                                    $productId = $product->productid;
                                }
        
                                $checkaddress = mysqli_query($conn, "SELECT * FROM orderitem WHERE referenceorder = '".$id."' LIMIT 1");                                

                                if($checkaddress->num_rows > 0){
                                    mysqli_query($conn, "UPDATE orderitem SET idpayment = '".$PaymentAmount."' , address1 = '".$Street1."' , address2 = '".$Street2."'   WHERE idorderplatform = '".$id."' ");
                                    $dts        = mysqli_fetch_object($checkaddress);
                                    $prodid     = $dts->productid;  
                                    $idpayment  = $dts->idpayment;  
                                    $quantity   = $dts->quantity;  
                                    $country    = $dts->country;  
                                    $cc         = array('IT' => 3 , 'DE' => 4 , 'FR' => 1 );
                                } else {
                                    $currdate   = date("Y-m-d");
                                    $last7date  = date("Y-m-d", strtotime("7 days ago"));
                                    $newdateweek = date("Y-m-d", strtotime($newcdateform));
                                    if($carrier != "" && $tracking != ""){
                                        $sp  = 1;
                                    }else{
                                        $sp  = 0;
                                    }
                                
                                    if($Total == 0) {
                                        $sp             = 1;
                                        $PaymentAmount  = "Deleted";
                                        $carrier        = "---";
                                        $tracking       = "---";
                                    } 

                                    /* update by ##wtd start */
                                    //if($newdateweek >= $last7date && $currdate <= $newdateweek){
                                    if($newdateweek >= $last7date){
                                        echo $query = "INSERT INTO orderitem (
                                                idorderplatform, registeredtolagerstandok, productid, sync, idcompany, referencechannel ,weeksell ,datee,quantity,sum,currency,idpayment,idwarehouse,referenceorder,platformname,referencechannelname,customer,customerextra,address1,address2,plz,city,region,country,telefon,email,invoicenr,inv_customer,email1,order_item_id,inv_vat,inv_address1,inv_address2,plz1,city1,region1,country1,telefon1,ship_service_level,carriername,tracking,notes,transactionId , registeredtosolddayok , courierinformedok ,  trackinguploadedok, printedshippingok)
                                        VALUES ( '".$id."', ".$sp.",'".$productId."' ,'Synch with eBay','".$idcompany."','".$idchannel."','".$dateweek."','".$newcdateform."','".$QuantityPurchased."','".$Total."','".$currencie."','".$PaymentAmount."' , '".$warehouse."','".$id."','".$platform."','".$shortname."','".$name."','".$lastname."','".$Street1."', '".$Street2."', '".$postalcode."', '".$cityname."', '".$state."', '".$countryname."', '".$phone."', '".$email."','".$userid."','".$name."','".$email."', '".$itemid."','".$vat."' ,'".$Street1."','".$Street2."','".$postalcode."', '".$cityname."','".$state."','".$countryname."','".$phone."','".$shippingser."', '".$carrier."', '".$tracking."','".$ProductName."', '".$transactid."' , ".$sp.", ".$sp.", ".$sp.", ".$sp." )";
                                        $sql = mysqli_query($conn, $query)  or die(mysqli_error($conn));
                                    }
                                }
                            }
                        } else {
                            for($t = 0; $t < count($order['TransactionArray']['Transaction']); $t++) {
                                $data = $order['TransactionArray']['Transaction'][$t];
                                
                                $cdate              = $data['CreatedDate'];
                                $dateweekcell       = date_create($cdate);
                                $dateweek           = date_format($dateweekcell,"W");
                                $newcdate           = date_create($cdate);
                                $newcdateform       = date_format($newcdate,"Y/m/d");
                                
                                if(isset($data['QuantityPurchased']) && !is_array($data['QuantityPurchased'])) {
                                    $QuantityPurchased  = $data['QuantityPurchased'];
                                } else {
                                    $QuantityPurchased  = "";
                                }

                                if(isset($data['SellingManagerProductDetails']['ProductName']) && !is_array($data['SellingManagerProductDetails']['ProductName'])) {
                                    $ProductName        = $data['SellingManagerProductDetails']['ProductName'];
                                } else {
                                    $ProductName        = "";
                                }

                                if(isset($data['Item']['SKU']) && !is_array($data['Item']['SKU'])) {
                                    $sku    = $data['Item']['SKU'];
                                } else {
                                    $sku                = "";
                                }

                                
                                
                                if(!is_array($sku)) {                                    
                                    $modelcode          = explode(" ", $sku)[0];
                                    $productfiv         = substr($ProductName,0,5);
                                    $PaymentAmount      = isset($order['CheckoutStatus']['PaymentMethod']) ? $order['CheckoutStatus']['PaymentMethod']:'';
                                    if($PaymentAmount == "None") {
                                        $PaymentAmount = "";
                                    }
                                    $Total              = $order['Total'];
                                    $userid             = $order['BuyerUserID'];
                                    $platform           = $data['Platform'];
                                    $refid              = isset($order['MonetaryDetails']['Payments']['Payment']['ReferenceID']) ? $order['MonetaryDetails']['Payments']['Payment']['ReferenceID'] :''  ;
                                    //$name               = $order['ShippingAddress']['Name'];
                                    $name               = filter_var($order['ShippingAddress']['Name'],FILTER_SANITIZE_STRING,FILTER_FLAG_STRIP_HIGH);
                                    if(isset($order['UserLastName']) && !is_array($order['UserLastName'])) {
                                        $lastname           =filter_var($order['UserLastName'],FILTER_SANITIZE_STRING,FILTER_FLAG_STRIP_HIGH);
                                    } else {
                                        $lastname           = "";
                                    }
                                    if(is_array($order['ShippingAddress']['Street1'])) {
                                        $Street1            = "";
                                    } else {
                                        $Street1            = filter_var($order['ShippingAddress']['Street1'],FILTER_SANITIZE_STRING,FILTER_FLAG_STRIP_HIGH);
                                    }
                                    if(is_array($order['ShippingAddress']['Street2'])) {
                                        $Street2            = "";
                                    } else {
                                        $Street2            = filter_var($order['ShippingAddress']['Street2'],FILTER_SANITIZE_STRING,FILTER_FLAG_STRIP_HIGH);
                                    }
                                    if(is_array($order['ShippingAddress']['PostalCode'])) {
                                        $postalcode            = "";
                                    } else {
                                        $postalcode            = $order['ShippingAddress']['PostalCode'];
                                    }
                                    if(is_array($order['ShippingAddress']['CityName'])) {
                                        $cityname            = "";
                                    } else {
                                        $cityname            = filter_var($order['ShippingAddress']['CityName'],FILTER_SANITIZE_STRING,FILTER_FLAG_STRIP_HIGH);
                                    }
                                    if(is_array($order['ShippingAddress']['StateOrProvince'])) {
                                        $state            = "";
                                    } else {
                                        $state            =  filter_var($order['ShippingAddress']['StateOrProvince'],FILTER_SANITIZE_STRING,FILTER_FLAG_STRIP_HIGH);
                                    }
                                    if(isset($order['ShippingAddress']['Country']) && !is_array($order['ShippingAddress']['Country'])) {
                                        $countryname        =  filter_var($order['ShippingAddress']['Country'],FILTER_SANITIZE_STRING,FILTER_FLAG_STRIP_HIGH);
                                    } else {
                                        $countryname        = '';
                                    }
                                    
                                    $phone              = '';
                                    if(!is_array($order['ShippingAddress']['Phone']) && $order['ShippingAddress']['Phone'] != "Invalid Request") {
                                        $phone             = $order['ShippingAddress']['Phone']; 
                                    }
                                    $email              = '';

                                    //print_r($order['TransactionArray']);

                                    // foreach($data as $data) {
                                    //     print_r($data);
                                    // }

                                    if(!is_array($data['Buyer']['Email'])) {
                                        $email             = $data['Buyer']['Email']; 
                                    }
                                    $transactid         = '';
                                    if(!is_array($data['TransactionID'])) {
                                        $transactid             = $data['TransactionID']; 
                                    }
                                    $itemid = '';
                                    if(!is_array($data['OrderLineItemID'])) {
                                        $itemid             = $data['OrderLineItemID']; 
                                    } 

                                    echo $id."---------------".$itemid."------------------------".$sku."<br>";
                                    $shippingser        = '';
                                    if(!is_array($order['ShippingDetails']['ShippingServiceOptions']['ShippingService'])) {
                                        $shippingser        = $order['ShippingDetails']['ShippingServiceOptions']['ShippingService'];
                                    }

                                    $carrier        = '';
                                    $tracking       = '';

                                    if(isset($data['ShippingDetails']['ShipmentTrackingDetails'])) {
                                        if(isset($data['ShippingDetails']['ShipmentTrackingDetails']['ShippingCarrierUsed'])) {
                                            $carrier            = $data['ShippingDetails']['ShipmentTrackingDetails']['ShippingCarrierUsed'];
                                        }
                                        if(isset($data['ShippingDetails']['ShipmentTrackingDetails']['ShipmentTrackingNumber'])) {
                                            $tracking           = $data['ShippingDetails']['ShipmentTrackingDetails']['ShipmentTrackingNumber'];
                                        }
                                    } else {
                                        $carrier        = '';
                                        $tracking       = '';
                                    } 
                                    
                                    $sql = "SELECT * FROM product WHERE modelcode='".$modelcode."'";
                                    $result = mysqli_query($conn, $sql);
                                    if($result->num_rows == 0) {
                                        echo "Warning: The product ".$sku." is added in Product table. Please fill up the rest of the information.<br>";

                                        $warnmessage = "Warning: The product ".$sku." is added in Product table. Please fill up the rest of the information.";

                                        $sql = "INSERT INTO tbl_open_activities SET dateTime='".date('Y-m-d H:i:s')."', issues='".$warnmessage."'";
                                        echo $sql."<br>";
                                        mysqli_query($conn, $sql);
                                        
                                        array_push($noneExistingProduct, $sku);
                                        $sql    = "INSERT INTO product SET modelcode ='".$modelcode."', nameshort='".$modelcode."', namelong='".$modelcode."', sku='".$sku."', active='Yes', virtualkit ='No'"; 
                                        mysqli_query($conn, $sql);
                                        $productId = mysqli_insert_id($conn);
                                        $sql    = "INSERT INTO lagerstand SET productid ='".$productId."', idwarehouse='".$idwarehouse."', quantity=0"; 
                                        mysqli_query($conn, $sql);
                                    } else {
                                        $product = mysqli_fetch_object($result);
                                        $productId = $product->productid;
                                    }
            
                                    $currdate       = date("Y-m-d");
                                    $last7date      = date("Y-m-d", strtotime("7 days ago"));
                                    $newdateweek    = date("Y-m-d", strtotime($newcdateform));
                                    if($carrier != "" && $tracking != ""){
                                        $sp  = 1;
                                    }else{
                                        $sp  = 0;
                                    }
                                
                                    if($Total == 0) {
                                        $sp             = 1;
                                        $PaymentAmount  = "Deleted";
                                        $carrier        = "---";
                                        $tracking       = "---";
                                    } 
                                    
                                    $checkaddress = mysqli_query($conn, "SELECT * FROM orderitem WHERE referenceorder = '".$id."' LIMIT 1");
                                    if($checkaddress->num_rows > 0){
                                        $itemExist = mysqli_query($conn, "SELECT * FROM orderitem WHERE referenceorder = '".$id."' AND order_item_id = '".$itemid."' LIMIT 1");
                                        if($itemExist->num_rows > 0) {
                                            mysqli_query($conn, "UPDATE orderitem SET idpayment = '".$PaymentAmount."' , address1 = '".$Street1."' , address2 = '".$Street2."'   WHERE referenceorder = '".$id."' AND order_item_id = '".$itemid."' ");
                                        } else {
                                            $table = $PaymentAmount !='' ? 'orderitem' : 'order_to_pay';
                                            $sql = mysqli_query($conn, "INSERT INTO {$table} (idorderplatform, registeredtolagerstandok, multiorder, productid, sync, idcompany, referencechannel ,weeksell ,datee,quantity,sum,currency,idpayment,idwarehouse,referenceorder,platformname,referencechannelname,customer,customerextra,address1,address2,plz,city,region,country,telefon,email,invoicenr,inv_customer,email1,order_item_id,inv_vat,inv_address1,inv_address2,plz1,city1,region1,country1,telefon1,ship_service_level,carriername,tracking,notes,transactionId , registeredtosolddayok , courierinformedok ,  trackinguploadedok, printedshippingok)
                                            VALUES ( '".$id."', ".$sp.", '".$id."', '".$productId."', 'Synch with eBay','".$idcompany."','".$idchannel."','".$dateweek."','".$newcdateform."','".$QuantityPurchased."','".$Total."','".$currencie."','".$PaymentAmount."' , '".$warehouse."','".$id."','".$platform."','".$shortname."','".$name."','".$lastname."','".$Street1."', '".$Street2."', '".$postalcode."', '".$cityname."', '".$state."', '".$countryname."', '".$phone."', '".$email."','".$userid."','".$name."','".$email."', '".$itemid."','".$vat."' ,'".$Street1."','".$Street2."','".$postalcode."', '".$cityname."','".$state."','".$countryname."','".$phone."','".$shippingser."', '".$carrier."', '".$tracking."','".$ProductName."', '".$transactid."' , ".$sp.", ".$sp.", ".$sp.", ".$sp." )");
                                            
                                            if($sku == "90170 1804D-422") {
                                                echo "INSERT INTO {$table} (idorderplatform, registeredtolagerstandok, multiorder, productid, sync, idcompany, referencechannel ,weeksell ,datee,quantity,sum,currency,idpayment,idwarehouse,referenceorder,platformname,referencechannelname,customer,customerextra,address1,address2,plz,city,region,country,telefon,email,invoicenr,inv_customer,email1,order_item_id,inv_vat,inv_address1,inv_address2,plz1,city1,region1,country1,telefon1,ship_service_level,carriername,tracking,notes,transactionId , registeredtosolddayok , courierinformedok ,  trackinguploadedok, printedshippingok)
                                                VALUES ( '".$id."', ".$sp.", '".$id."', '".$productId."', 'Synch with eBay','".$idcompany."','".$idchannel."','".$dateweek."','".$newcdateform."','".$QuantityPurchased."','".$Total."','".$currencie."','".$PaymentAmount."' , '".$warehouse."','".$id."','".$platform."','".$shortname."','".$name."','".$lastname."','".$Street1."', '".$Street2."', '".$postalcode."', '".$cityname."', '".$state."', '".$countryname."', '".$phone."', '".$email."','".$userid."','".$name."','".$email."', '".$itemid."','".$vat."' ,'".$Street1."','".$Street2."','".$postalcode."', '".$cityname."','".$state."','".$countryname."','".$phone."','".$shippingser."', '".$carrier."', '".$tracking."','".$ProductName."', '".$transactid."' , ".$sp.", ".$sp.", ".$sp.", ".$sp." )";
                                            }
                                        }
                                    } else {
                                        $table = $PaymentAmount !='' ? 'orderitem' : 'order_to_pay';
                                        $sql = mysqli_query($conn, "INSERT INTO {$table} (idorderplatform, registeredtolagerstandok, productid, sync, idcompany, referencechannel ,weeksell ,datee,quantity,sum,currency,idpayment,idwarehouse,referenceorder,platformname,referencechannelname,customer,customerextra,address1,address2,plz,city,region,country,telefon,email,invoicenr,inv_customer,email1,order_item_id,inv_vat,inv_address1,inv_address2,plz1,city1,region1,country1,telefon1,ship_service_level,carriername,tracking,notes,transactionId , registeredtosolddayok , courierinformedok ,  trackinguploadedok, printedshippingok)
                                        VALUES ( '".$id."', ".$sp.",'".$productId."' ,'Synch with eBay','".$idcompany."','".$idchannel."','".$dateweek."','".$newcdateform."','".$QuantityPurchased."','".$Total."','".$currencie."','".$PaymentAmount."' , '".$warehouse."','".$id."','".$platform."','".$shortname."','".$name."','".$lastname."','".$Street1."', '".$Street2."', '".$postalcode."', '".$cityname."', '".$state."', '".$countryname."', '".$phone."', '".$email."','".$userid."','".$name."','".$email."', '".$itemid."','".$vat."' ,'".$Street1."','".$Street2."','".$postalcode."', '".$cityname."','".$state."','".$countryname."','".$phone."','".$shippingser."', '".$carrier."', '".$tracking."','".$ProductName."', '".$transactid."' , ".$sp.", ".$sp.", ".$sp.", ".$sp." )");

                                        if($sku == "90170 1804D-422") {
                                            echo "INSERT INTO {$table} (idorderplatform, registeredtolagerstandok, productid, sync, idcompany, referencechannel ,weeksell ,datee,quantity,sum,currency,idpayment,idwarehouse,referenceorder,platformname,referencechannelname,customer,customerextra,address1,address2,plz,city,region,country,telefon,email,invoicenr,inv_customer,email1,order_item_id,inv_vat,inv_address1,inv_address2,plz1,city1,region1,country1,telefon1,ship_service_level,carriername,tracking,notes,transactionId , registeredtosolddayok , courierinformedok ,  trackinguploadedok, printedshippingok)
                                            VALUES ( '".$id."', ".$sp.",'".$productId."' ,'Synch with eBay','".$idcompany."','".$idchannel."','".$dateweek."','".$newcdateform."','".$QuantityPurchased."','".$Total."','".$currencie."','".$PaymentAmount."' , '".$warehouse."','".$id."','".$platform."','".$shortname."','".$name."','".$lastname."','".$Street1."', '".$Street2."', '".$postalcode."', '".$cityname."', '".$state."', '".$countryname."', '".$phone."', '".$email."','".$userid."','".$name."','".$email."', '".$itemid."','".$vat."' ,'".$Street1."','".$Street2."','".$postalcode."', '".$cityname."','".$state."','".$countryname."','".$phone."','".$shippingser."', '".$carrier."', '".$tracking."','".$ProductName."', '".$transactid."' , ".$sp.", ".$sp.", ".$sp.", ".$sp." )";
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                $orders = $array_data['OrderArray'];
                foreach($orders as $order) {
                    if(isset($order['OrderID'])) {
                        $id                 = $order['OrderID'] ;
                        if(isset($order['TransactionArray']['Transaction']['CreatedDate'])) {
                            $cdate              = $order['TransactionArray']['Transaction']['CreatedDate'];
                            $dateweekcell       = date_create($cdate);
                            $dateweek           = date_format($dateweekcell,"W");
                            $newcdate           = date_create($cdate);
                            $newcdateform       = date_format($newcdate,"Y/m/d");
                            
                            if(isset($order['TransactionArray']['Transaction']['QuantityPurchased']) && !is_array($order['TransactionArray']['Transaction']['QuantityPurchased'])) {
                                $QuantityPurchased  = $order['TransactionArray']['Transaction']['QuantityPurchased'];
                            } else {
                                $QuantityPurchased  = "";
                            }

                            if(isset($order['TransactionArray']['Transaction']['SellingManagerProductDetails']['ProductName']) && !is_array($order['TransactionArray']['Transaction']['SellingManagerProductDetails']['ProductName'])) {
                                $ProductName        = $order['TransactionArray']['Transaction']['SellingManagerProductDetails']['ProductName'];
                            } else {
                                $ProductName        = "";
                            }

                            if(isset($order['TransactionArray']['Transaction']['SellingManagerProductDetails']['CustomLabel']) && !is_array($order['TransactionArray']['Transaction']['SellingManagerProductDetails']['CustomLabel'])) {
                                $sku                = $order['TransactionArray']['Transaction']['SellingManagerProductDetails']['CustomLabel'];
                            } else {
                                $sku                = "";
                            }
                            
                            if($sku == "23062 2xFH45619") {
                                print_r($order);
                            }
                            
                            if(!is_array($sku)) {
                                $modelcode          = explode(" ", $sku)[0];
                                $productfiv         = substr($ProductName,0,5);
                                $PaymentAmount      = isset($order['CheckoutStatus']['PaymentMethod']) ? $order['CheckoutStatus']['PaymentMethod']:'';
                                if($PaymentAmount == "None") {
                                    $PaymentAmount = "";
                                }
                                $Total              = $order['Total'];
                                $userid             = $order['BuyerUserID'];
                                $platform           = $order['TransactionArray']['Transaction']['Platform'];
                                $refid              = isset($order['MonetaryDetails']['Payments']['Payment']['ReferenceID']) ? $order['MonetaryDetails']['Payments']['Payment']['ReferenceID'] :''  ;
                                $name               =  filter_var($order['ShippingAddress']['Name'],FILTER_SANITIZE_STRING,FILTER_FLAG_STRIP_HIGH);
                                if(isset($order['UserLastName']) && !is_array($order['UserLastName'])) {
                                    $lastname           = filter_var($order['UserLastName'],FILTER_SANITIZE_STRING,FILTER_FLAG_STRIP_HIGH);
                                } else {
                                    $lastname           = "";
                                }
                                if(is_array($order['ShippingAddress']['Street1'])) {
                                    $Street1            = "";
                                } else {
                                    $Street1            =  filter_var($order['ShippingAddress']['Street1'],FILTER_SANITIZE_STRING,FILTER_FLAG_STRIP_HIGH);
                                }
                                if(is_array($order['ShippingAddress']['Street2'])) {
                                    $Street2            = "";
                                } else {
                                    $Street2            = filter_var($order['ShippingAddress']['Street2'],FILTER_SANITIZE_STRING,FILTER_FLAG_STRIP_HIGH);
                                }
                                if(is_array($order['ShippingAddress']['PostalCode'])) {
                                    $postalcode            = "";
                                } else {
                                    $postalcode            = $order['ShippingAddress']['PostalCode'];
                                }
                                if(is_array($order['ShippingAddress']['CityName'])) {
                                    $cityname            = "";
                                } else {
                                    $cityname            = filter_var($order['ShippingAddress']['CityName'],FILTER_SANITIZE_STRING,FILTER_FLAG_STRIP_HIGH);
                                }
                                if(is_array($order['ShippingAddress']['StateOrProvince'])) {
                                    $state            = "";
                                } else {
                                    $state            = filter_var($order['ShippingAddress']['StateOrProvince'],FILTER_SANITIZE_STRING,FILTER_FLAG_STRIP_HIGH);
                                }
                                if(isset($order['ShippingAddress']['Country']) && !is_array($order['ShippingAddress']['Country'])) {
                                    $countryname        = filter_var($order['ShippingAddress']['Country'],FILTER_SANITIZE_STRING,FILTER_FLAG_STRIP_HIGH);
                                } else {
                                    $countryname        = '';
                                }
                                
                                $phone              = '';
                                if(!is_array($order['ShippingAddress']['Phone']) && $order['ShippingAddress']['Phone'] != "Invalid Request") {
                                    $phone             = $order['ShippingAddress']['Phone']; 
                                }
                                $email              = '';
                                if(!is_array($order['TransactionArray']['Transaction']['Buyer']['Email'])) {
                                    $email             = $order['TransactionArray']['Transaction']['Buyer']['Email']; 
                                }
                                $transactid         = '';
                                if(!is_array($order['TransactionArray']['Transaction']['TransactionID'])) {
                                    $transactid             = $order['TransactionArray']['Transaction']['TransactionID']; 
                                }
                                $itemid = '';
                                if(!is_array($order['TransactionArray']['Transaction']['Item']['ItemID'])) {
                                    $itemid             = $order['TransactionArray']['Transaction']['Item']['ItemID']; 
                                } 
                                $shippingser        = '';
                                if(!is_array($order['ShippingDetails']['ShippingServiceOptions']['ShippingService'])) {
                                    $shippingser        = $order['ShippingDetails']['ShippingServiceOptions']['ShippingService'];
                                }

                                $carrier        = '';
                                $tracking       = '';

                                if(isset($order['TransactionArray']['Transaction']['ShippingDetails']['ShipmentTrackingDetails'])) {
                                    if(isset($order['TransactionArray']['Transaction']['ShippingDetails']['ShipmentTrackingDetails']['ShippingCarrierUsed'])) {
                                        $carrier            = $order['TransactionArray']['Transaction']['ShippingDetails']['ShipmentTrackingDetails']['ShippingCarrierUsed'];
                                    }
                                    if(isset($order['TransactionArray']['Transaction']['ShippingDetails']['ShipmentTrackingDetails']['ShipmentTrackingNumber'])) {
                                        $tracking           = $order['TransactionArray']['Transaction']['ShippingDetails']['ShipmentTrackingDetails']['ShipmentTrackingNumber'];
                                    }
                                } else {
                                    $carrier        = '';
                                    $tracking       = '';
                                } 
                                
                                $sql = "SELECT * FROM product WHERE modelcode='".$modelcode."'";
                                $result = mysqli_query($conn, $sql);
                                if($result->num_rows == 0) {
                                    echo "Warning: The product ".$sku." is added in Product table. Please fill up the rest of the information.<br>";

                                    $warnmessage = "Warning: The product ".$sku." is added in Product table. Please fill up the rest of the information.";

                                    $sql = "INSERT INTO tbl_open_activities SET dateTime='".date('Y-m-d H:i:s')."', issues='".$warnmessage."'";
                                    echo $sql."<br>";
                                    mysqli_query($conn, $sql);
                                    
                                    array_push($noneExistingProduct, $sku);
                                    $sql    = "INSERT INTO product SET modelcode ='".$modelcode."', nameshort='".$modelcode."', namelong='".$modelcode."', sku='".$sku."', active='Yes', virtualkit ='No'"; 
                                    mysqli_query($conn, $sql);
                                    $productId = mysqli_insert_id($conn);
                                    $sql    = "INSERT INTO lagerstand SET productid ='".$productId."', idwarehouse='".$idwarehouse."', quantity=0"; 
                                    mysqli_query($conn, $sql);
                                } else {
                                    $product = mysqli_fetch_object($result);
                                    $productId = $product->productid;
                                }
        
                                $checkaddress = mysqli_query($conn, "SELECT * FROM orderitem WHERE referenceorder = '".$id."' LIMIT 1");
                                if($checkaddress->num_rows > 0){
                                    mysqli_query($conn, "UPDATE orderitem SET idpayment = '".$PaymentAmount."' , address1 = '".$Street1."' , address2 = '".$Street2."'   WHERE idorderplatform = '".$id."' ");
                                    $dts        = mysqli_fetch_object($checkaddress);
                                    $prodid     = $dts->productid;  
                                    $idpayment  = $dts->idpayment;  
                                    $quantity   = $dts->quantity;  
                                    $country    = $dts->country;  
                                    $cc         = array('IT' => 3 , 'DE' => 4 , 'FR' => 1 );
                                    if($idpayment != ""){
                                        //echo "UPDATE lagerstand  SET  quantity = quantity - 1 WHERE productid = ".$prodid." AND  idwarehouse = '".$cc[$country]."'";
                                        // $mysqli->query("UPDATE lagerstand  SET  quantity = quantity - ".$quantity." WHERE productid = ".$prodid." AND  idwarehouse = '".$cc[$country]."'");
                                        // $mysqli->query("UPDATE soldweekly  SET  quantity = quantity + ".$quantity." WHERE productid = ".$prodid." AND  country = '".$country."'");
                                    }
                                } else {
                                    $currdate   = date("Y-m-d");
                                    $last7date  = date("Y-m-d", strtotime("7 days ago"));
                                    $newdateweek = date("Y-m-d", strtotime($newcdateform));
                                    if($carrier != "" && $tracking != ""){
                                        $sp  = 1;
                                    }else{
                                        $sp  = 0;
                                    }
                                
                                    if($Total == 0) {
                                        $sp             = 1;
                                        $PaymentAmount  = "Deleted";
                                        $carrier        = "---";
                                        $tracking       = "---";
                                    } 

                                    /* update by ##wtd start */
                                    //if($newdateweek >= $last7date && $currdate <= $newdateweek){
                                    if($newdateweek >= $last7date){
                                        $table = $PaymentAmount !='' ? 'orderitem' : 'order_to_pay';
                                        $sql = mysqli_query($conn, "INSERT INTO {$table} (idorderplatform, registeredtolagerstandok, productid, sync, idcompany, referencechannel ,weeksell ,datee,quantity,sum,currency,idpayment,idwarehouse,referenceorder,platformname,referencechannelname,customer,customerextra,address1,address2,plz,city,region,country,telefon,email,invoicenr,inv_customer,email1,order_item_id,inv_vat,inv_address1,inv_address2,plz1,city1,region1,country1,telefon1,ship_service_level,carriername,tracking,notes,transactionId , registeredtosolddayok , courierinformedok ,  trackinguploadedok, printedshippingok)
                                        VALUES ( '".$id."', ".$sp.",'".$productId."' ,'Synch with eBay','".$idcompany."','".$idchannel."','".$dateweek."','".$newcdateform."','".$QuantityPurchased."','".$Total."','".$currencie."','".$PaymentAmount."' , '".$warehouse."','".$id."','".$platform."','".$shortname."','".$name."','".$lastname."','".$Street1."', '".$Street2."', '".$postalcode."', '".$cityname."', '".$state."', '".$countryname."', '".$phone."', '".$email."','".$userid."','".$name."','".$email."', '".$itemid."','".$vat."' ,'".$Street1."','".$Street2."','".$postalcode."', '".$cityname."','".$state."','".$countryname."','".$phone."','".$shippingser."', '".$carrier."', '".$tracking."','".$ProductName."', '".$transactid."' , ".$sp.", ".$sp.", ".$sp.", ".$sp." )");
                                        
                                        echo "INSERT INTO {$table} (idorderplatform, registeredtolagerstandok, productid, sync, idcompany, referencechannel ,weeksell ,datee,quantity,sum,currency,idpayment,idwarehouse,referenceorder,platformname,referencechannelname,customer,customerextra,address1,address2,plz,city,region,country,telefon,email,invoicenr,inv_customer,email1,order_item_id,inv_vat,inv_address1,inv_address2,plz1,city1,region1,country1,telefon1,ship_service_level,carriername,tracking,notes,transactionId , registeredtosolddayok , courierinformedok ,  trackinguploadedok, printedshippingok)
                                        VALUES ( '".$id."', ".$sp.",'".$productId."' ,'Synch with eBay','".$idcompany."','".$idchannel."','".$dateweek."','".$newcdateform."','".$QuantityPurchased."','".$Total."','".$currencie."','".$PaymentAmount."' , '".$warehouse."','".$id."','".$platform."','".$shortname."','".$name."','".$lastname."','".$Street1."', '".$Street2."', '".$postalcode."', '".$cityname."', '".$state."', '".$countryname."', '".$phone."', '".$email."','".$userid."','".$name."','".$email."', '".$itemid."','".$vat."' ,'".$Street1."','".$Street2."','".$postalcode."', '".$cityname."','".$state."','".$countryname."','".$phone."','".$shippingser."', '".$carrier."', '".$tracking."','".$ProductName."', '".$transactid."' , ".$sp.", ".$sp.", ".$sp.", ".$sp." )";
                                    
                                        echo "<br>"; 
                                    }
                                }
                            }
                        } else {
                            for($t = 0; $t < count($order['TransactionArray']['Transaction']); $t++) {
                                $data = $order['TransactionArray']['Transaction'][$t];
                                
                                $cdate              = $data['CreatedDate'];
                                $dateweekcell       = date_create($cdate);
                                $dateweek           = date_format($dateweekcell,"W");
                                $newcdate           = date_create($cdate);
                                $newcdateform       = date_format($newcdate,"Y/m/d");

                                if(isset($data['QuantityPurchased']) && !is_array($data['QuantityPurchased'])) {
                                    $QuantityPurchased  = $data['QuantityPurchased'];
                                } else {
                                    $QuantityPurchased  = "";
                                }

                                if(isset($data['SellingManagerProductDetails']['ProductName']) && !is_array($data['SellingManagerProductDetails']['ProductName'])) {
                                    $ProductName        = $data['SellingManagerProductDetails']['ProductName'];
                                } else {
                                    $ProductName        = "";
                                }

                                if(isset($data['SellingManagerProductDetails']['CustomLabel']) && !is_array($data['SellingManagerProductDetails']['CustomLabel'])) {
                                    $sku                = $data['SellingManagerProductDetails']['CustomLabel'];
                                } else {
                                    $sku                = "";
                                }
                                
                                if(!is_array($sku)) {                                    
                                    $modelcode          = explode(" ", $sku)[0];
                                    $productfiv         = substr($ProductName,0,5);
                                    $PaymentAmount      = isset($order['CheckoutStatus']['PaymentMethod']) ? $order['CheckoutStatus']['PaymentMethod']:'';
                                    if($PaymentAmount == "None") {
                                        $PaymentAmount = "";
                                    }
                                    $Total              = $order['Total'];
                                    $userid             = $order['BuyerUserID'];
                                    $platform           = $data['Platform'];
                                    $refid              = isset($order['MonetaryDetails']['Payments']['Payment']['ReferenceID']) ? $order['MonetaryDetails']['Payments']['Payment']['ReferenceID'] :''  ;
                                    $name               = filter_var($order['ShippingAddress']['Name'],FILTER_SANITIZE_STRING,FILTER_FLAG_STRIP_HIGH);
                                    if(isset($order['UserLastName']) && !is_array($order['UserLastName'])) {
                                        $lastname           =filter_var($order['UserLastName'],FILTER_SANITIZE_STRING,FILTER_FLAG_STRIP_HIGH);
                                    } else {
                                        $lastname           = "";
                                    }
                                    if(is_array($order['ShippingAddress']['Street1'])) {
                                        $Street1            = "";
                                    } else {
                                        $Street1            = filter_var($order['ShippingAddress']['Street1'],FILTER_SANITIZE_STRING,FILTER_FLAG_STRIP_HIGH);
                                    }
                                    if(is_array($order['ShippingAddress']['Street2'])) {
                                        $Street2            = "";
                                    } else {
                                        $Street2            = filter_var($order['ShippingAddress']['Street2'],FILTER_SANITIZE_STRING,FILTER_FLAG_STRIP_HIGH);
                                    }
                                    if(is_array($order['ShippingAddress']['PostalCode'])) {
                                        $postalcode            = "";
                                    } else {
                                        $postalcode            = $order['ShippingAddress']['PostalCode'];
                                    }
                                    if(is_array($order['ShippingAddress']['CityName'])) {
                                        $cityname            = "";
                                    } else {
                                        $cityname            = filter_var($order['ShippingAddress']['CityName'],FILTER_SANITIZE_STRING,FILTER_FLAG_STRIP_HIGH);
                                    }
                                    if(is_array($order['ShippingAddress']['StateOrProvince'])) {
                                        $state            = "";
                                    } else {
                                        $state            =  filter_var($order['ShippingAddress']['StateOrProvince'],FILTER_SANITIZE_STRING,FILTER_FLAG_STRIP_HIGH);
                                    }
                                    if(isset($order['ShippingAddress']['Country']) && !is_array($order['ShippingAddress']['Country'])) {
                                        $countryname        =  filter_var($order['ShippingAddress']['Country'],FILTER_SANITIZE_STRING,FILTER_FLAG_STRIP_HIGH);
                                    } else {
                                        $countryname        = '';
                                    }
                                    
                                    $phone              = '';
                                    if(!is_array($order['ShippingAddress']['Phone']) && $order['ShippingAddress']['Phone'] != "Invalid Request") {
                                        $phone             = $order['ShippingAddress']['Phone']; 
                                    }
                                    $email              = '';

                                    //print_r($order['TransactionArray']);

                                    // foreach($data as $data) {
                                    //     print_r($data);
                                    // }

                                    if(!is_array($data['Buyer']['Email'])) {
                                        $email             = $data['Buyer']['Email']; 
                                    }
                                    $transactid         = '';
                                    if(!is_array($data['TransactionID'])) {
                                        $transactid             = $data['TransactionID']; 
                                    }
                                    $itemid = '';
                                    if(!is_array($data['Item']['ItemID'])) {
                                        $itemid             = $data['Item']['ItemID']; 
                                    } 

                                    echo $id."---------------".$itemid."------------------------".$sku."<br>";
                                    $shippingser        = '';
                                    if(!is_array($order['ShippingDetails']['ShippingServiceOptions']['ShippingService'])) {
                                        $shippingser        = $order['ShippingDetails']['ShippingServiceOptions']['ShippingService'];
                                    }

                                    $carrier        = '';
                                    $tracking       = '';

                                    if(isset($data['ShippingDetails']['ShipmentTrackingDetails'])) {
                                        if(isset($data['ShippingDetails']['ShipmentTrackingDetails']['ShippingCarrierUsed'])) {
                                            $carrier            = $data['ShippingDetails']['ShipmentTrackingDetails']['ShippingCarrierUsed'];
                                        }
                                        if(isset($data['ShippingDetails']['ShipmentTrackingDetails']['ShipmentTrackingNumber'])) {
                                            $tracking           = $data['ShippingDetails']['ShipmentTrackingDetails']['ShipmentTrackingNumber'];
                                        }
                                    } else {
                                        $carrier        = '';
                                        $tracking       = '';
                                    } 
                                    
                                    $sql = "SELECT * FROM product WHERE modelcode='".$modelcode."'";
                                    $result = mysqli_query($conn, $sql);
                                    if($result->num_rows == 0) {
                                        echo "Warning: The product ".$sku." is added in Product table. Please fill up the rest of the information.<br>";

                                        $warnmessage = "Warning: The product ".$sku." is added in Product table. Please fill up the rest of the information.";

                                        $sql = "INSERT INTO tbl_open_activities SET dateTime='".date('Y-m-d H:i:s')."', issues='".$warnmessage."'";
                                        echo $sql."<br>";
                                        mysqli_query($conn, $sql);
                                        
                                        array_push($noneExistingProduct, $sku);
                                        $sql    = "INSERT INTO product SET modelcode ='".$modelcode."', nameshort='".$modelcode."', namelong='".$modelcode."', sku='".$sku."', active='Yes', virtualkit ='No'"; 
                                        mysqli_query($conn, $sql);
                                        $productId = mysqli_insert_id($conn);
                                        $sql    = "INSERT INTO lagerstand SET productid ='".$productId."', idwarehouse='".$idwarehouse."', quantity=0"; 
                                        mysqli_query($conn, $sql);
                                    } else {
                                        $product = mysqli_fetch_object($result);
                                        $productId = $product->productid;
                                    }
            
                                    $currdate   = date("Y-m-d");
                                    $last7date  = date("Y-m-d", strtotime("7 days ago"));
                                    $newdateweek = date("Y-m-d", strtotime($newcdateform));
                                    if($carrier != "" && $tracking != ""){
                                        $sp  = 1;
                                    }else{
                                        $sp  = 0;
                                    }
                                
                                    if($Total == 0) {
                                        $sp             = 1;
                                        $PaymentAmount  = "Deleted";
                                        $carrier        = "---";
                                        $tracking       = "---";
                                    } 
                                    
                                    $checkaddress = mysqli_query($conn, "SELECT * FROM orderitem WHERE referenceorder = '".$id."' LIMIT 1");
                                    if($checkaddress->num_rows > 0){
                                        $itemExist = mysqli_query($conn, "SELECT * FROM orderitem WHERE referenceorder = '".$id."' AND order_item_id = '".$itemid."' LIMIT 1");
                                        if($itemExist->num_rows > 0) {
                                            mysqli_query($conn, "UPDATE orderitem SET idpayment = '".$PaymentAmount."' , address1 = '".$Street1."' , address2 = '".$Street2."'   WHERE referenceorder = '".$id."' AND order_item_id = '".$itemid."' ");
                                        } else {
                                            $table = $PaymentAmount !='' ? 'orderitem' : 'order_to_pay';
                                            $sql = mysqli_query($conn, "INSERT INTO {$table} (idorderplatform, registeredtolagerstandok, multiorder, productid, sync, idcompany, referencechannel ,weeksell ,datee,quantity,sum,currency,idpayment,idwarehouse,referenceorder,platformname,referencechannelname,customer,customerextra,address1,address2,plz,city,region,country,telefon,email,invoicenr,inv_customer,email1,order_item_id,inv_vat,inv_address1,inv_address2,plz1,city1,region1,country1,telefon1,ship_service_level,carriername,tracking,notes,transactionId , registeredtosolddayok , courierinformedok ,  trackinguploadedok, printedshippingok)
                                            VALUES ( '".$id."', ".$sp.", ".$id.",'".$productId."' ,'Synch with eBay','".$idcompany."','".$idchannel."','".$dateweek."','".$newcdateform."','".$QuantityPurchased."','".$Total."','".$currencie."','".$PaymentAmount."' , '".$warehouse."','".$id."','".$platform."','".$shortname."','".$name."','".$lastname."','".$Street1."', '".$Street2."', '".$postalcode."', '".$cityname."', '".$state."', '".$countryname."', '".$phone."', '".$email."','".$userid."','".$name."','".$email."', '".$itemid."','".$vat."' ,'".$Street1."','".$Street2."','".$postalcode."', '".$cityname."','".$state."','".$countryname."','".$phone."','".$shippingser."', '".$carrier."', '".$tracking."','".$ProductName."', '".$transactid."' , ".$sp.", ".$sp.", ".$sp.", ".$sp." )");

                                            echo "INSERT INTO {$table} (idorderplatform, registeredtolagerstandok, multiorder, productid, sync, idcompany, referencechannel ,weeksell ,datee,quantity,sum,currency,idpayment,idwarehouse,referenceorder,platformname,referencechannelname,customer,customerextra,address1,address2,plz,city,region,country,telefon,email,invoicenr,inv_customer,email1,order_item_id,inv_vat,inv_address1,inv_address2,plz1,city1,region1,country1,telefon1,ship_service_level,carriername,tracking,notes,transactionId , registeredtosolddayok , courierinformedok ,  trackinguploadedok, printedshippingok)
                                            VALUES ( '".$id."', ".$sp.", ".$id.",'".$productId."' ,'Synch with eBay','".$idcompany."','".$idchannel."','".$dateweek."','".$newcdateform."','".$QuantityPurchased."','".$Total."','".$currencie."','".$PaymentAmount."' , '".$warehouse."','".$id."','".$platform."','".$shortname."','".$name."','".$lastname."','".$Street1."', '".$Street2."', '".$postalcode."', '".$cityname."', '".$state."', '".$countryname."', '".$phone."', '".$email."','".$userid."','".$name."','".$email."', '".$itemid."','".$vat."' ,'".$Street1."','".$Street2."','".$postalcode."', '".$cityname."','".$state."','".$countryname."','".$phone."','".$shippingser."', '".$carrier."', '".$tracking."','".$ProductName."', '".$transactid."' , ".$sp.", ".$sp.", ".$sp.", ".$sp." )";

                                            echo "<br>";
                                        }
                                    } else {
                                        $table = $PaymentAmount !='' ? 'orderitem' : 'order_to_pay';
                                        $sql = mysqli_query($conn, "INSERT INTO {$table} (idorderplatform, registeredtolagerstandok, productid, sync, idcompany, referencechannel ,weeksell ,datee,quantity,sum,currency,idpayment,idwarehouse,referenceorder,platformname,referencechannelname,customer,customerextra,address1,address2,plz,city,region,country,telefon,email,invoicenr,inv_customer,email1,order_item_id,inv_vat,inv_address1,inv_address2,plz1,city1,region1,country1,telefon1,ship_service_level,carriername,tracking,notes,transactionId , registeredtosolddayok , courierinformedok ,  trackinguploadedok, printedshippingok)
                                        VALUES ( '".$id."', ".$sp.",'".$productId."' ,'Synch with eBay','".$idcompany."','".$idchannel."','".$dateweek."','".$newcdateform."','".$QuantityPurchased."','".$Total."','".$currencie."','".$PaymentAmount."' , '".$warehouse."','".$id."','".$platform."','".$shortname."','".$name."','".$lastname."','".$Street1."', '".$Street2."', '".$postalcode."', '".$cityname."', '".$state."', '".$countryname."', '".$phone."', '".$email."','".$userid."','".$name."','".$email."', '".$itemid."','".$vat."' ,'".$Street1."','".$Street2."','".$postalcode."', '".$cityname."','".$state."','".$countryname."','".$phone."','".$shippingser."', '".$carrier."', '".$tracking."','".$ProductName."', '".$transactid."' , ".$sp.", ".$sp.", ".$sp.", ".$sp." )");
                                    
                                        echo "INSERT INTO {$table} (idorderplatform, registeredtolagerstandok, productid, sync, idcompany, referencechannel ,weeksell ,datee,quantity,sum,currency,idpayment,idwarehouse,referenceorder,platformname,referencechannelname,customer,customerextra,address1,address2,plz,city,region,country,telefon,email,invoicenr,inv_customer,email1,order_item_id,inv_vat,inv_address1,inv_address2,plz1,city1,region1,country1,telefon1,ship_service_level,carriername,tracking,notes,transactionId , registeredtosolddayok , courierinformedok ,  trackinguploadedok, printedshippingok)
                                        VALUES ( '".$id."', ".$sp.",'".$productId."' ,'Synch with eBay','".$idcompany."','".$idchannel."','".$dateweek."','".$newcdateform."','".$QuantityPurchased."','".$Total."','".$currencie."','".$PaymentAmount."' , '".$warehouse."','".$id."','".$platform."','".$shortname."','".$name."','".$lastname."','".$Street1."', '".$Street2."', '".$postalcode."', '".$cityname."', '".$state."', '".$countryname."', '".$phone."', '".$email."','".$userid."','".$name."','".$email."', '".$itemid."','".$vat."' ,'".$Street1."','".$Street2."','".$postalcode."', '".$cityname."','".$state."','".$countryname."','".$phone."','".$shippingser."', '".$carrier."', '".$tracking."','".$ProductName."', '".$transactid."' , ".$sp.", ".$sp.", ".$sp.", ".$sp." )";
                                        echo "<br>";
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
?>