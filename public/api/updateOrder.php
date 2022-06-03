<?php
    ini_set('max_execution_time', 30000000000);

    require(__DIR__ .'/config.php');
    require(__DIR__ .'/vendor/autoload.php');
    
    $orderId = "";
    if(isset($_GET['orderId']) && $_GET['orderId'] != "") {
        $orderId = $_GET['orderId'];
    }
    
    $amazonorders  = [];
    if($orderId != "") {
        $sql    = "SELECT * FROM orderitem INNER JOIN channel ON orderitem.referencechannel=channel.idchannel WHERE carriername != '' AND idorder = ".$orderId;
    } else {
        $sql    = "SELECT * FROM orderitem INNER JOIN channel ON orderitem.referencechannel=channel.idchannel WHERE carriername != ''";
    }

    $result = mysqli_query($conn, $sql);
    if($result->num_rows > 0) {
        while($order  = mysqli_fetch_object($result)) {
            $amazonorders  = [];
            if($order->aws_acc_key_id != NULL || $order->aws_secret_key_id != NULL || $order->merchant_id != NULL || $order->market_place_id != NULL || $order->mws_auth_token != NULL) {  
                $aws_acc_key_id     = $order->aws_acc_key_id;  // these prod keys are different from sandbox keys
                $aws_secret_key_id  = $order->aws_secret_key_id;  // these prod keys are different from sandbox keys
                $merchant_id        = $order->merchant_id;
                $mws_auth_token     = $order->mws_auth_token;
                $shortname          = $order->shortname;   
                $idcompany          = $order->idcompany;    
                $vat                = $order->vat; 
                $platformid         = $order->platformid; 
                $idwarehouse        = $order->warehouse;
                $idchannel          = $order->idchannel; 
                $countryname        = $order->country;
                $warehouse          = $order->warehouse;

                array_push($amazonorders, $order);
                $client = new MCS\MWSClient([
                    'Marketplace_Id'    => $order->market_place_id,
                    'Seller_Id'         => $merchant_id,
                    'Access_Key_ID'     => $aws_acc_key_id,
                    'Secret_Access_Key' => $aws_secret_key_id,
                    'MWSAuthToken'      => $order->mws_auth_token // Optional. Only use this key if you are a third party user/developer
                ]);

                try { 
                    $status = $client->updateOrder($amazonorders);  
                    if(isset($status['FeedProcessingStatus']) && $status['FeedProcessingStatus'] == "_SUBMITTED_") {
                        echo "Success";
                    }
                } 
                catch (\Exception $e) { 
                    echo 'Message: ' .$e->getMessage(); 
                }     
            } else if($order->devid != NULL || $order->appid != NULL || $order->certid != NULL || $order->usertoken != NULL) {
                $devID              = $order->devid;
                $appID              = $order->appid;
                $certID             = $order->certid;
                $userToken          = $order->usertoken; 
                $shortname          = $order->shortname;   
                $idcompany          = $order->idcompany;    
                $vat                = $order->vat; 
                $platformid         = $order->platformid; 
                $idwarehouse        = $order->warehouse;
                $idchannel          = $order->idchannel; 
                $countryname        = $order->country;
                $warehouse          = $order->warehouse;
                $contry             = $order->country;

                $endpoint  = 'https://api.ebay.com/ws/api.dll'; // URL to call

                $headers = array(
                    'Content-Type: text/xml',
                    'X-EBAY-API-COMPATIBILITY-LEVEL:877',
                    'X-EBAY-API-DEV-NAME:'.$devID,
                    'X-EBAY-API-APP-NAME:'.$appID,
                    'X-EBAY-API-CERT-NAME:'.$certID,
                    'X-EBAY-API-SITEID:0',
                    'X-EBAY-API-CALL-NAME:CompleteSale'
                );

                $CreateTime   = gmdate("Y-m-d\TH:i:s");

                $xml = "<?xml version='1.0' encoding='utf-8'?>
                        <CompleteSaleRequest xmlns='urn:ebay:apis:eBLBaseComponents'>
                            <RequesterCredentials>
                                <eBayAuthToken>".$userToken."</eBayAuthToken>
                            </RequesterCredentials>
                            <OrderID>".$order->idorderplatform."</OrderID>
                            <Shipment>
                                <ShipmentTrackingDetails>
                                    <ShipmentTrackingNumber>".$order->tracking."</ShipmentTrackingNumber>
                                    <ShippingCarrierUsed>".$order->carriername."</ShippingCarrierUsed>
                                </ShipmentTrackingDetails>
                                <ShippedTime>".$CreateTime."</ShippedTime>
                            </Shipment>
                            <Shipped>true</Shipped>
                        </CompleteSaleRequest>";

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
            }

            $sql = "UPDATE orderitem SET trackinguploadedok =1 WHERE idorder='".$order->idorder."'";
            mysqli_query($conn, $sql);
        }
    }

    header('Location: /newchannelmanager/public/orderView');
?>