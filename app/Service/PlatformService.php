<?php

namespace App\Service;
use App\Models\Channel;
use App\Models\OrderItem;
use App\Models\LagerStand;

class PlatformService
{
    public function getMarketPlaceId($countyCode)
    {
        $array = [
            'DE'=>'A1PA6795UKMFR9',
            'FR'=>'A13V1IB3VIYZZH',
            'UK'=>'A1F83G8C2ARO7P',
            'ES'=>'A1RKKUPIHCS9HS',
            'IT'=>'APJ6JRA9NG5V4',
        ];
        return isset($array[$countyCode]) ? $array[$countyCode] : [];
    }

    public function amazonUpdate(OrderItem $order, Channel $channel)
    {
        if(!$order->product) return;
        $client = new \MCS\MWSClient([
            'Marketplace_Id'    => $this->getMarketPlaceId($channel->country),
            'Seller_Id'         => $channel->merchant_id,
            'Access_Key_ID'     => $channel->aws_acc_key_id,
            'Secret_Access_Key' => $channel->aws_secret_key_id,
            'MWSAuthToken'      => $channel->mws_auth_token // Optional. Only use this key if you are a third party user/developer
        ]);
      
        try { 
            $status = $client->updateStock([$order->product->sku => $order->quantity]);  
            if(isset($status['FeedProcessingStatus']) && $status['FeedProcessingStatus'] == "_SUBMITTED_") {
               return true;
            }
        } 
        catch (\Exception $e) { 
            echo 'Message: ' .$e->getMessage(); 
            return false;
        }  
    }

    public function eBayUpdateQuantity(OrderItem $order, Channel $channel)
    {
        $fields = array();
        $fields['itemId'] = 124878402545;
        $fields['quantity'] = 2;
        $fields_string = http_build_query($fields); 
         
        $ch = curl_init();
        $url = url("/ebay/updateQuantity/".$channel->idchannel.'?'.$fields_string);
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, $url);
        $res = curl_exec($ch);
        curl_close($ch);
    }
}
