<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Channel;
use App\Models\OrderItem;
use App\Models\LagerStand;
use App\Models\Document;
use Illuminate\Http\Request;
use App\Service\PlatformService;

class CronController extends Controller
{
    private $platformService;

    public function __construct()
    {   
        $this->platformService  = new PlatformService();
    }

    public function test(Request $request)
    {
        
        $orders = OrderItem::orderBy('idorder','ASC')->paginate(3);

        foreach ($orders as $key => $order) {
         
            $channel = $order->channel;

            $inventory = LagerStand::where(['productid'=>$order->productid, 'idwarehouse'=> $order->idwarehouse])->first();

          
            // if(empty($inventory)){
            //     $reslager = DB::table('lagerstand')
            //             ->insert([
            //                 "productid"     => $order->productid,
            //                 "idwarehouse"   => $order->idwarehouse,
            //                 "quantity"      => (-1)*$order->quantity
            //             ]);
            // }else{
            //     $inventory->decrement('quantity', $order->quantity);
            // }

            if(isset($channel) && isset($channel->platform)){

                if($channel->platform->platformtype == 'Amazon'){
                    
                    ///$status = $this->platformService->amazonUpdate($order, $channel); 

                }else if($channel->platform->platformtype == 'Ebay'){
                   
                   $status = $this->platformService->eBayUpdateQuantity($order, $channel); 

                }else if($channel->platform->platformtype == 'Otto'){
                    


                }else if($channel->platform->platformtype == 'Woocommerce'){
                    

                }
            }
        }
    }
}
