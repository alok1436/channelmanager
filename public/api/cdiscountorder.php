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
    // ini_set('display_errors', 1);
    
    error_reporting(E_ALL);
    require(__DIR__ .'/config.php');

    $sql            = "SELECT * FROM channel WHERE platformid = 3";
    $data           = mysqli_query($conn, $sql);
    $noneExistingProduct = array();
    while ($row=mysqli_fetch_object($data)) {
        $shortname          = $row->shortname;   
        $idcompany          = $row->idcompany;    
        $vat                = $row->vat; 
        $platformid         = $row->platformid; 
        $idwarehouse        = $row->warehouse;
        $idchannel          = $row->idchannel; 
        $countryname        = $row->country;
        $warehouse          = $row->warehouse;
        
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
        
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://marketplaceapi.cdiscount.com/OrderManagement/orders/search',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{
                "fetch_order_lines": true,
            }',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json-patch+json',
                'Cache-Control: no-cache',
                'Ocp-Apim-Subscription-Key: 2053fa0960204db2ab08f1de9df20c57',
                'SellerId: 12514',
                'Authorization: Bearer '.$access_data->access_token
            ),
        ));

        $response = curl_exec($curl);
        
        curl_close($curl);
        $orderData = json_decode($response);
        
        foreach($orderData as $order) {
           // echo '<pre>'; print_r($order); echo '</pre>';
            echo "<br><br><br>";
            if(isset($order->customer)) {
                $BuyerEmail                  = $order->customer->encrypted_email;
            } else {
                $BuyerEmail                  = "";
            }
            
            $referenceorder         = $order->order_number; 

            $platformname           = "cDiscount"; 
            if(isset($order->validated_total_amount)) {
                $sum                    = $order->validated_total_amount;
                $currency               = '';
            } else {
                $sum                    = 0;
                $currency               = '';
            }
            $id                     = $order->order_number;
            $cdate                  = $order->creation_date;
            $dateweekcell           = date_create($cdate);
            $dateweek               = date_format($dateweekcell,"W");
            $newcdate               = date_create($cdate);
            $newcdateform           = date_format($newcdate,"Y/m/d");
            if(isset($order->shipping_address)) {
                $StateOrRegion          = '';
            } else {
                $StateOrRegion          = '';
            }

            $tracking               = '';
            $carref                 = '';
            if(isset($order->order_state) && $order->order_state == "Shipped") {
                $carref = "Shipped";
            }else if(isset($order->is_c_logistique_order) && $order->is_c_logistique_order == 1) {
                $carref = "Shipped";
            }

            $print_shipping         = '';
            $platform               = 'cDiscount';
            if(isset($order->shipping_address->zip_code)) {
                $PostalCode             = $order->shipping_address->zip_code;
            } else {
                $PostalCode             = "";
            }
            if(isset($order->shipping_address->city)) {
                $City                   = $order->shipping_address->city;
            } else {
                $City                   = "";
            }
            if(isset($order->shipping_address->country)) {
                $country                = $order->shipping_address->country;
            } else {
                $country                = "";
            }
            
            $shippingser            = '';
            $orderstaus             = $order->order_state;
            $is_c_logistique_order  = $order->is_c_logistique_order;

            if(isset($order->TaxRegistrationDetails)) {
                //$transactionId          = isset($order['TaxRegistrationDetails']['member']['taxRegistrationId']) ? $order['TaxRegistrationDetails']['member']['taxRegistrationId'] :'';
            } else {
                $transactionId = '';
            }
            $registeredtosolddayok  = 0;            
            $courierinformedok      = 0;           
            $trackinguploadedok     = 0;
            $items = $order->order_line_list;

            if($sum == 0) {

            }
            $customer               = $order->shipping_address->first_name.' '.$order->shipping_address->last_name;
            $address1               = $order->shipping_address->address1 ? $order->shipping_address->address1 : $order->shipping_address->street;
            $address2               = $order->shipping_address->address2 ? $order->shipping_address->address2 : $order->shipping_address->street;
            $plz1                   = $order->billing_address->zip_code;
            $city1                  = $order->billing_address->city;
            $country1               = $order->billing_address->country;
            $email1                 = $order->customer->encrypted_email;
            $phone1                 = $order->customer->mobile_phone;
            $inv_customer           = $order->billing_address->first_name.' '.$order->billing_address->last_name;
            $inv_address1           = $order->billing_address->street;
            $inv_address2           = $order->billing_address->street;
            
            foreach($items as $item) {
                $ean            = $item->product_ean;
                $sku            = $item->sku;
                $orderItemId    = $item->row_id;
                $quantity       = $item->quantity;
                $modelcode      = $item->sku;
                
                ///Check if the product is existing or not.
                if($sku != "INTERETBCA") {
                    echo $sku."<br>";
                    $sql = "SELECT * FROM product WHERE ean='".$ean."'";
                    $result = mysqli_query($conn, $sql);
                    
                    if($result->num_rows == 0) {
                        
                        echo "Warning: The product ".$sku." is added in Product table. Please fill up the rest of the information.<br>";
    
                        $warnmessage = "Warning: The product ".$sku." is added in Product table. Please fill up the rest of the information.";
    
                        $sql = "INSERT INTO tbl_open_activities SET dateTime='".date('Y-m-d H:i:s')."', issues='".$warnmessage."'";
                        mysqli_query($conn, $sql);
    
                        array_push($noneExistingProduct, $sku);
                        $sql    = "INSERT INTO product SET modelcode ='".$modelcode."', nameshort='".$modelcode."', namelong='".$modelcode."', ean='".$ean."', sku='".$sku."', active='Yes', virtualkit ='No'"; 
                        mysqli_query($conn, $sql);
                        $productId = mysqli_insert_id($conn);
                        $sql    = "INSERT INTO lagerstand SET productid ='".$productId."', idwarehouse='".$idwarehouse."', quantity=0"; 
                        mysqli_query($conn, $sql);
                    } else {
                        $product = mysqli_fetch_object($result);
                        $productId = $product->productid;
                    }
                    
                    $registeredtolagerstandok = 0; 

                    $table = ($is_c_logistique_order == 1) ? 'orderitem_fba' : 'orderitem';

                    $result = mysqli_query($conn, "SELECT * FROM {$table} WHERE referenceorder = '".$id."' LIMIT 1");
                    $idpayment = "cDiscount";
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
                        
                        $result = mysqli_query($conn, "SELECT * FROM {$table} WHERE referenceorder = '".$id."' AND order_item_id = '".$orderItemId."' LIMIT 1");
                        if($result->num_rows == 0) {
                            if($sum == 0 || $quantity == 0) {
                                $print_shipping             = 1;
                                $registeredtosolddayok      = 1;
                                $registeredtolagerstandok   = 1;
                                $courierinformedok          = 1;
                                $trackinguploadedok         = 1;
                                $idpayment                  = "Deleted";
                            } else {
                                if($orderstaus == "Shipped" || $is_c_logistique_order == 1) {
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
                            
                            

                            $sql = "INSERT INTO {$table} (idorderplatform, registeredtolagerstandok, multiorder, productid, referenceorder, sync, idcompany, referencechannel, weeksell, datee, quantity, sum, idpayment, idwarehouse, platformname, referencechannelname, country, email, currency, plz, city, region, order_item_id,inv_vat, email1, plz1, ship_service_level, transactionId, registeredtosolddayok, courierinformedok, trackinguploadedok, carriername, printedshippingok,customer, city1, country1, telefon1, inv_customer, inv_address1, address1)
                                    VALUES ( '".$id."', ".$registeredtolagerstandok.", '".$multiorder."', '".$productId."', '".$id."','Synch with Amazon','".$idcompany."','".$idchannel."','".$dateweek."','".$newcdateform."','".$quantity."' ,'".$sum."','".$idpayment."','".$warehouse."','".$platform."','".$shortname."','".$countryname."','".$BuyerEmail."','".$currency."','".$PostalCode."','".$City."','".$StateOrRegion."','".$orderItemId."','".$vat."','".$BuyerEmail."','".$plz1."','".$shippingser."','".$transactionId."'  , ".$registeredtosolddayok."  , ".$courierinformedok."  , ".$trackinguploadedok." , '".$carref."' , ".$print_shipping.", '".$customer."', '".$city1."', '".$country1."', '".$phone1."', '".$inv_customer."', '".$inv_address1."', '".$address1."')";
                            
                            echo $sql."<br>";
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
                                if($orderstaus == "Shipped" || $is_c_logistique_order == 1) {
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
    
                          echo  $sql = "UPDATE {$table} SET sync = 'Synch with CDiscount', registeredtosolddayok = '1', courierinformedok = '1', trackinguploadedok = '1', carriername = '".$carref."', printedshippingok = '".$print_shipping."', address1='".$address1."' WHERE idorderplatform= '".$id."'";
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
                            if($orderstaus == "Shipped" || $is_c_logistique_order == 1) {
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
                        
                        $sql = "INSERT INTO {$table} (idorderplatform, tracking, registeredtolagerstandok, productid, referenceorder, sync, idcompany, referencechannel, weeksell, datee, quantity, sum, idpayment, idwarehouse, platformname, referencechannelname, country, email, currency, plz, city, region, order_item_id,inv_vat, email1, plz1, ship_service_level, transactionId, registeredtosolddayok, courierinformedok, trackinguploadedok, carriername, printedshippingok,customer, city1, country1, telefon1, inv_customer, inv_address1, address1)
                                VALUES ( '".$id."', '".$tracking."', ".$registeredtolagerstandok.", '".$productId."', '".$id."','Synch with Amazon','".$idcompany."','".$idchannel."','".$dateweek."','".$newcdateform."','".$quantity."' ,'".$sum."','".$idpayment."','".$warehouse."','".$platform."','".$shortname."','".$countryname."','".$BuyerEmail."','".$currency."','".$PostalCode."','".$City."','".$StateOrRegion."','".$orderItemId."','".$vat."','".$BuyerEmail."','".$plz1."','".$shippingser."','".$transactionId."'  , ".$registeredtosolddayok."  , ".$courierinformedok."  , ".$trackinguploadedok." , '".$carref."' , ". $print_shipping.", '".$customer."', '".$city1."', '".$country1."', '".$phone1."', '".$inv_customer."', '".$inv_address1."', '".$address1."')";
                        echo $sql."<br>";
                        mysqli_query($conn, $sql);
                    }
                }
                
            }
        }
    }
?>