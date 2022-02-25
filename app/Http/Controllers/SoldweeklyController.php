<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use Session;
use Redirect;
use Image;
use Carbon\Carbon;
use ZipArchive;
use File;
use PDF;

class SoldweeklyController extends Controller {
    //
    public function soldweeklyView() {
        if(!Session::has('userID')) {
            return redirect()->route('login');
        }

        if(isset($_GET['sortItem']) && $_GET['sortItem'] != "") {
            if(Session::has('sortItem')) {
                if(Session::get('sortItem') == $_GET['sortItem']) {
                    if(Session::get('sort') == 'desc') {
                        Session::put('sort', 'asc');
                    } else {
                        Session::put('sort', 'desc');
                    }
                } else {
                    Session::put('sortItem', $_GET['sortItem']);
                    Session::put('sort', 'asc');
                }
            } else {
                Session::put('sortItem', $_GET['sortItem']);
                Session::put('sort', 'asc');
            }
        } else {
            if(!Session::has('sortItem')) {
                Session::put('sortItem', 'sort');
                Session::put('sort', 'asc');
            }
        }

        $query      = DB::table('product');
        if(Session::has('sortItem')) {
            $query ->orderby('product.'.Session::get('sortItem'), Session::get('sort'));
        } else {
            $query ->orderby('product.modelcode', 'desc');
        }
        $products   = $query->paginate(100);

        $warehouses = DB::table('warehouse')
                        ->get();

        $last       = DB::table('soldweekly')
                        ->orderby('weeksell', 'desc')
                        ->first();

        $params['main_week_sell']   = $last->weeksell;

        foreach($products as $product) {
            $productId  = $product->productid;
            $lagerstand = DB::table('lagerstand')
                ->where('productid', '=', $productId)
                ->selectRaw(DB::raw("COALESCE(sum(quantity),0) as total_qty"))
                ->first();
                
            $last = DB::table('soldweekly')
                ->where('productid', '=', $productId)
                ->orderby('weeksell', 'desc')
                ->first();

            $last_week  = 0;
            if (!empty($last)) {
                $last_week  = $last->weeksell;
            }
            $product->total_qty = $lagerstand->total_qty;
            $product->last_week = $last_week;
            
            for($i=1; $i<=26; $i++) { 
                $soldweekly = DB::table('soldweekly')
                            ->where('productid', '=', $productId)
                            ->where('weeksell' , '=', $last_week)
                            ->selectRaw(DB::raw("COALESCE(sum(quantity),0) as total_qty"))
                            ->get();
                
                $weekly_totol_qty_key = "soldweekly_total_qty_".$i;
                $product->$weekly_totol_qty_key = $soldweekly[0]->total_qty;
                
                $total_qty = 0;
                foreach ($warehouses as $key => $w) {
                    $soldweekly= DB::table('soldweekly')
                        ->where('productid'     , '=', $productId)
                        ->where('idwarehouse'   , '=', $w->idwarehouse)
                        ->where('weeksell'      , '=', $last_week)
                        ->selectRaw(DB::raw("COALESCE(sum(quantity),0) as total_qty"))
                        ->get();

                    $warehouse_weekly_totol_qty_key             = "warehouse_soldweekly_total_qty_".$i."_".$w->idwarehouse;
                    $product->$warehouse_weekly_totol_qty_key   = $soldweekly[0]->total_qty;
                }
                
                $last_week                                  = $last_week-$i;
             }
        }

        //print_r($products);
        $params['products']     = $products;
        $params['warehouses']   = $warehouses;
        return View::make('soldweeklyView', $params);
    }
}
