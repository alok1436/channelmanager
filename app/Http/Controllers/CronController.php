<?php

namespace App\Http\Controllers;
use DB;
use App\Models\Price;
use App\Models\Product;
use App\Models\Channel;
use App\Models\OrderItem;
use App\Models\LagerStand;
use App\Models\Document;
use Illuminate\Http\Request;
use App\Service\PlatformService;
use Codexshaper\WooCommerce\Facades\Product as ProductStore;

class CronController extends Controller
{
    private $platformService;

    public function __construct()
    {   
        $this->platformService  = new PlatformService();
    }

    public function test(Request $request)
    {
        if($request->filled('orderId')){
            $orders = OrderItem::where('idorder', $request->orderId)->get();
        }else{
            $orders = OrderItem::where('is_cron_sync', 0)->orderBy('idorder','DESC')->get();
        }
 
        foreach ($orders as $key => $order) {
         
            $channel = $order->channel;

            $inventory = LagerStand::where(['productid'=>$order->productid, 'idwarehouse'=> $order->idwarehouse])->first();
            //getting the quanrtity for the order

            if(empty($inventory)){
                $quantity = (-1)*$order->quantity;
                $reslager = DB::table('lagerstand')
                        ->insert([
                            "productid"     => $order->productid,
                            "idwarehouse"   => $order->idwarehouse,
                            "quantity"      =>  $quantity 
                        ]);
             }else{
                 $quantity = $inventory->quantity - $order->quantity;
                    DB::table('lagerstand')
                        ->where('productid',    '=', $order->productid)
                        ->where('idwarehouse',  '=', $order->idwarehouse)
                        ->update(['quantity'=>$quantity]);
            }


            $warehouseQnt = LagerStand::where(['productid' => $order->productid, 'idwarehouse'=>$order->idwarehouse])->first();
    
            $price = Price::where(['product_id'=>$order->productid])->first();
            if($price){
 
                if($price->channel && $price->channel->quantity_strategy == 1) {
                    $buffer =  Product::where(['productid'=>$order->productid])->first();
 
                    if(!empty($buffer) && $buffer->min_sell != null) {
                        $price->indicated_quantity = $buffer->min_sell;
                    }
                    $price->can_sell_online = $price->indicated_quantity;

                    if(!empty($warehouseQnt)) {
                        if($warehouseQnt->quantity >= $price->indicated_quantity){
                            $price->can_sell_online = $price->indicated_quantity;
                        }else{
                            $price->can_sell_online = $warehouseQnt->quantity;
                        }
                    }
                }else if($price->channel->quantity_strategy == 3) {
                    if(!empty($warehouseQnt)) {
                        $price->can_sell_online = $warehouseQnt->quantity;
                    }
                }
      
                $quantity =  $price->can_sell_online;
 
                if(isset($channel) && isset($channel->platform)){
 
                    if($channel->platform->platformtype == 'Amazon'){
                        
                        $status = $this->platformService->amazonUpdateQuantity($order, $channel, $quantity); 
 
                    }else if($channel->platform->platformtype == 'Ebay'){
                       
                       $status = $this->platformService->eBayUpdateQuantity($order, $channel, $quantity); 

                    }else if($channel->platform->platformtype == 'Otto'){
                        
                        $status = $this->platformService->OttoQuantityUpdate($order, $channel, $quantity); 

                    }else if($channel->platform->platformtype == 'Woocommerce'){

                        $status = $this->platformService->wooQuantityUpdate($order, $channel, $quantity); 
                    }
                }

                if($status == true){
                    $price->online_quentity = $quantity;
                    $price->save();
                }

                $order->is_cron_sync = 1;
                $order->save();
            }
        }
    }
}
