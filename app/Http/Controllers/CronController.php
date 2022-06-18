<?php

namespace App\Http\Controllers;

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

            $quantity = ($quantity < 0) ? 0 : $quantity;

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

            $order->is_cron_sync = 1;
            $order->save();
        }
    }
}
