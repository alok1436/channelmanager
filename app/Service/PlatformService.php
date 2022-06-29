<?php

namespace App\Service;
use App\Models\FBA;
use App\Models\Channel;
use App\Models\OrderItem;
use App\Models\LagerStand;
use App\Http\Controllers\OttoController;
use Codexshaper\WooCommerce\Facades\Product as WooProduct;

class PlatformService
{
    private $otto;
    
    public function __construct()
    {
        $this->otto = new OttoController;
    }

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

    public function amazonUpdateQuantity(OrderItem $order, Channel $channel, $quantity)
    {
        if(!$order->product) return; //check if product is available

        $isFba = FBA::where(['sku'=>$order->product->sku, 'channel'=>$channel->idchannel])->count();
        if($isFba > 0) return;

        $client = new \MCS\MWSClient([
            'Marketplace_Id'    => $this->getMarketPlaceId($channel->country),
            'Seller_Id'         => $channel->merchant_id,
            'Access_Key_ID'     => $channel->aws_acc_key_id,
            'Secret_Access_Key' => $channel->aws_secret_key_id,
            'MWSAuthToken'      => $channel->mws_auth_token // Optional. Only use this key if you are a third party user/developer
        ]);
      
        try {
            $status = $client->updateStock([$order->product->sku => $quantity]);  
            if(isset($status['FeedProcessingStatus']) && $status['FeedProcessingStatus'] == "_SUBMITTED_") {
               return true;
            }
        }catch (\Exception $e) { 
            echo 'Message: ' .$e->getMessage(); 
            return false;
        }  
    }

    public function eBayUpdateQuantity(OrderItem $order, Channel $channel, $quantity)
    {
        if($order->order_item_id != ''){
            $fields = array();
            $fields['sku']  = $order->product->sku;
            $fields['itemId'] = $order->order_item_id;
            $fields['quantity'] = $quantity;
            $fields_string = http_build_query($fields); 
            $ch = curl_init();
            $url = url("ebay/updateQuantity/".$channel->idchannel.'?'.$fields_string);
            $ch = curl_init();
            curl_setopt($ch,CURLOPT_URL, $url);
            $res = curl_exec($ch);
            curl_close($ch);

            return true;
        }
        return false;
    }


    public function OttoQuantityUpdate(OrderItem $order, Channel $channel, $quantity)
    {   
        if($order->product){
            $isFba = FBA::where(['sku'=>$order->product->sku, 'channel'=>$channel->idchannel])->count();
            if($isFba == 0){
                $response = $this->otto->updateQuantityUsingCron($order->product->sku, $quantity, $channel);
                return true;
            }
        }
        return false;
    }

    public function wooQuantityUpdate(OrderItem $order, Channel $channel, $quantity)
    {   

        Session::put("WOOCOMMERCE_STORE_URL",       $channel->woo_store_url);
        Session::put("WOOCOMMERCE_CONSUMER_KEY",    $channel->woo_consumer_key);
        Session::put("WOOCOMMERCE_CONSUMER_SECRET", $channel->woo_consumer_secret);
        config([
            'woocommerce.store_url' => $channel->woo_store_url,
            'woocommerce.consumer_key' => $channel->woo_consumer_key,
            'woocommerce.consumer_secret' => $channel->woo_consumer_secret
        ]);
        if($order->order_item_id > 0){
            try{
                $product = WooProduct::find($request->order_item_id)->toArray();
                if($product){
                    $data = [];
                    if(!$product['manage_stock']){
                        $data['manage_stock'] = true;
                    }

                    $isFba = FBA::where(['sku'=>$order->product->sku, 'channel'=>$channel->idchannel])->count();
                    
                    if($isFba == 0){
                        $data['stock_quantity'] =   $quantity;
                    }

                    if(count($data) > 0){
                        $isupdated = WooProduct::update($order->order_item_id, $data);
                        return true;
                    }
                    
                }
            }catch (\Exception $e) { 
                    echo 'Message: ' .$e->getMessage();
                    return false;
            } 
        }

        return false;
    }
}
