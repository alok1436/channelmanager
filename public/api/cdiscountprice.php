<?php 
    ini_set('max_execution_time', 1500000);
    ini_set('memory_limit', '1024M');
    ini_set('mysql.connect_timeout', 1500000);
    ini_set('default_socket_timeout', 1500000);
    ini_set('mysql.reconnect', 1);
    ini_set('mysql.wait_timeout', 1500000);
    ini_set('wait_timeout', 1500000);
    ini_set('mysql.max_allowed_packet', '2024MB'); 
    ini_set('innodb_lock_wait_timeout', 1500000); 
    ini_set('mysql.innodb_lock_wait_timeout', 1500000); 
    ini_set('display_errors', 1);
    
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
        
        $sql = "UPDATE prices SET ebayActive=0 WHERE channel_id = ".$row->idchannel;
        mysqli_query($conn, $sql);

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
        

        $limit      = 50;
        $page       = 1;
        $page_count = 0;

        $call_count = 0;

        while(1) {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://marketplaceapi.cdiscount.com/offerManagement/offers/search?$limit='.$limit.'&$page='.$page,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS =>'{
                    
                }',
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json-patch+json',
                    'Cache-Control: no-cache',
                    'Ocp-Apim-Subscription-Key: 2fec5b686ee3423680799a093345b2a8',
                    'SellerId: 12514',
                    'Authorization: Bearer '.$access_token
                ),
            ));
    
            $response = curl_exec($curl);
            curl_close($curl);


            $pricedata = json_decode($response);
            
            $page_count = $pricedata->page_count;
            $page_index = $pricedata->page_index;

            $products = $pricedata->items;
            foreach($products as $product) {
                $stocks     = $product->stocks;
                $ean        = $product->product_ean;
                $sku        = $product->seller_product_id;
                $price      = $product->price;
                $quantity   = $stocks[0]->quantity;
                $shipping_information_list = $product->shipping_information_list;

                $online_shipping = 0;
                if(isset($shipping_information_list[0])) {
                    $online_shipping = $shipping_information_list[0]->shipping_charges;
                }

                echo "<br>".$ean."-----".$product->price."-----".$product->price."-----".$stocks[0]->quantity;

                $sql    = "SELECT * FROM product WHERE ean='".$ean."'";
                $result = mysqli_query($conn, $sql);

                if($result->num_rows == 0) {
                    $sql    = "INSERT INTO tbl_open_activities SET issues ='Warning: The product ".$ean." ".$sku." is not found the CDiscount EAN product table. Please add in the missing information.', dateTime ='".date('Y-m-d H:i:s')."'"; 
                    mysqli_query($conn, $sql);
                } else {
                    $sql    = "SELECT * FROM prices WHERE channel_id=".$idchannel." AND ean='".$ean."'";
                    $result = mysqli_query($conn, $sql);
                    $current_product = mysqli_fetch_object(mysqli_query($conn, "SELECT * FROM product WHERE ean='".$ean."'"));

                    $cost = 0;
                    if($current_product->price > 0) {
                        $cost = $current_product->price;
                    }
                    if ($result->num_rows == 0) {
                        $sql    = "INSERT INTO prices SET cost=".$cost.", country='FR', product_id='".$current_product->productid."', online_price = ".$price.", online_quentity= ".$quantity.", last_update_qty_date='".date('Y-m-d H:i:s')."', online_shipping= ".$online_shipping.", shipping=".$online_shipping.", last_update_date='".date('Y-m-d H:i:s')."', last_update_shipping='".date('Y-m-d H:i:s')."', channel_id ='".$idchannel."',warehouse_id ='".$idwarehouse."',platform_id='".$platformid."' ,sku ='".$sku."',ean ='".$ean."',asin ='".$current_product->asin."', price='".$price."' ,created_date='".date('Y-m-d H:i:s')."',updated_date='".date('Y-m-d H:i:s')."', ebayActive=1"; 
                        echo $sql."--------2<br>";
                        $result = mysqli_query($conn, $sql);
                    } else {
                        $sql    = "UPDATE prices SET online_price = ".$price.", country='FR', online_shipping= ".$online_shipping.", price='".$price."', online_quentity= ".$quantity.", last_update_qty_date='".date('Y-m-d H:i:s')."', shipping=".$online_shipping.", last_update_date='".date('Y-m-d H:i:s')."', last_update_shipping='".date('Y-m-d H:i:s')."', updated_date='".date('Y-m-d H:i:s')."', ebayActive=1 WHERE channel_id=".$idchannel." AND ean='".$ean."'";
                        echo $sql."--------3<br>";
                        $result = mysqli_query($conn, $sql);
                    }
                }
            }

            if($page_count == $page_index) {
                break;
            } else {
                $page++;
            }
        }
        
    }
?>