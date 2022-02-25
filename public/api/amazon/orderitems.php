<?php
ini_set('max_execution_time', 1500000);
ini_set('memory_limit', -1);

require(__DIR__ .'/../config.php');
require(__DIR__ .'/../vendor/autoload.php');
$post = $_POST;
if(isset($post['channel'])){
    $order = $_POST['order'];
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

    if(isset($order['TaxRegistrationDetails'])) {
        $transactionId          = isset($order['TaxRegistrationDetails']['member']['taxRegistrationId']) ? $order['TaxRegistrationDetails']['member']['taxRegistrationId'] :'';
    } else {
        $transactionId = '';
    }
    $registeredtosolddayok  = 0;            
    $courierinformedok      = 0;           
    $trackinguploadedok     = 0;
    
    
    $client = new MCS\MWSClient([
                'Marketplace_Id'    => 'A13V1IB3VIYZZH',
                'Seller_Id'         => $post['channel']['merchant_id'],
                'Access_Key_ID'     => $post['channel']['aws_acc_key_id'],
                'Secret_Access_Key' => $post['channel']['aws_secret_key_id'],
                'MWSAuthToken'      => $post['channel']['mws_auth_token'],
            ]);
    sleep(2);
    $items = $client->ListOrderItems($order['AmazonOrderId']);
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
                        VALUES ( '".$id."', ".$registeredtolagerstandok.", '".$multiorder."', '".$productId."', '".$id."','Synch with Amazon','".$idcompany."','".$idchannel."','".$dateweek."','".$newcdateform."','".$quantity."' ,'".$sum."','".$idpayment."','".$warehouse."','".$platform."','".$shortname."','".$countryname."','".$BuyerEmail."','".$currency."','".$PostalCode."','".$City."','".$StateOrRegion."','".$orderItemId."','".$vat."','".$BuyerEmail."','".$PostalCode."','".$shippingser."','".$transactionId."'  , ".$registeredtosolddayok."  , ".$courierinformedok."  , ".$trackinguploadedok." , '".$carref."' , ".$print_shipping.")";
                
                echo $sql.'<br>';
                if($id == "408-1482479-5488308") {
                    echo $city."-----------1";
                }
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

                $sql = "UPDATE orderitem SET sync = 'Synch with Amazon', registeredtosolddayok = '1', courierinformedok = '1', trackinguploadedok = '1', carriername = '".$carref."', printedshippingok = '".$print_shipping."' WHERE idorderplatform= '".$id."'";
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
            echo $sql.'<br>';
            mysqli_query($conn, $sql);
            /* ##wtd end */
            $arrListOrderItemsPayload = array('AmazonOrderId' => "$id");
            $countorder1++;
        }
    }
}
