<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Session;

class DashboardController extends Controller
{
    //
    public function index() {
        $setting    = DB::table('tbl_setting')
                        ->first();

        Session::put("main_back",   $setting->main_background);
        Session::put("logo1",       $setting->logo1);
        Session::put("logo2",       $setting->logo2);
        
        if(Session::has("userID")) {
            $newContainers  = DB::table('newcontainer') 
                            ->leftjoin('product', 'product.productid', '=', 'newcontainer.productId')
                            ->get();

            $zeroProducts = DB::table('product')   
                            ->leftjoin('lagerstand', 'product.productid', '=', 'lagerstand.productid')
                            ->leftjoin('manufacturer', 'manufacturer.manufacturerid', '=', 'product.manufacturerid')
                            ->where('lagerstand.quantity', '=', 0)
                            ->get();

            $warehouses = DB::table('warehouse')
                            ->get();
    
            $manufacturers = DB::table('manufacturer')
                            ->get();
            
            $subcategories = DB::table('subcategory')
                            ->get();

            $products   = DB::table('lagerstand')
                            ->leftjoin('product', 'product.productid', '=', 'lagerstand.productid')
                            ->select('product.price', 'product.subcat', 'lagerstand.quantity', 'lagerstand.idwarehouse')
                            ->get();

            $warnings   = DB::table('tbl_open_activities')
                            ->where('status', '=', '0')
                            ->orderBy('id','desc')
                            ->get();
            foreach($subcategories as $catitem) {
                foreach($products as $productitem) {
                    if($catitem->Namesubcat == $productitem->subcat) {
                        $warehouse = $productitem->idwarehouse;
                        if(isset($catitem->$warehouse)) {
                            $catitem->$warehouse += $productitem->price*$productitem->quantity;
                        } else {
                            $catitem->$warehouse = $productitem->price*$productitem->quantity;
                        }
                    }

                    $totalWarehouse = "total_".$productitem->idwarehouse;
                    if(isset($catitem->$totalWarehouse)) {
                        $catitem->$totalWarehouse += $productitem->price*$productitem->quantity;
                    } else {
                        $catitem->$totalWarehouse = $productitem->price*$productitem->quantity;
                    }
                }
            }

            $update     = DB::table('updates')
                            ->where('id', '=', 1)
                            ->first();
    
            $params['products']         = $products;
            $params['warehouses']       = $warehouses;    
            $params['update']           = $update;
            $params['manufacturers']    = $manufacturers;
            $params['newContainers']    = $newContainers;
            $params['zeroProducts']     = $zeroProducts;
            $params['subcategories']    = $subcategories;
            $params['warnings']         = $warnings;
            return View::make('dashboard', $params);
        } else {
            return redirect()->route('login');
        }
    }

    public function fixWarning($id) {
        DB::table('tbl_open_activities')
            ->where('id', '=', $id)
            ->update([
                'status' => 1
            ]);

        return redirect()->route('dashboard');
    }
}
