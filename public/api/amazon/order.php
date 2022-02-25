<?php
    ini_set('max_execution_time', 1500000);
    ini_set('memory_limit', -1);

    require(__DIR__ .'/../config.php');
    require(__DIR__ .'/../vendor/autoload.php');
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
    //echo '<pre>'; print_r($row=mysqli_fetch_object($data)); echo '</pre>'; exit();
    $noneExistingProduct = array();
    while ($row=mysqli_fetch_object($data)) {
        $message = 'Getting orders for '.$row->shortname.'</br>';
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
            $orders = $client->ListOrders($fromDate);
            echo json_encode(['channel'=>$row,'orders'=>$orders]);
        }
    }
?>