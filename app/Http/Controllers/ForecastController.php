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
use Rap2hpoutre\FastExcel\FastExcel;
use App\SimpleXLSX;

class ForecastController extends Controller {
    public function trendView() {
        $trends = DB::table("tbl_trend")
                    ->groupby('trendName')
                    ->get();

        
        $trendsData = array();
        foreach($trends as $trend) {
            $row = array();
            $eachTrends = DB::table("tbl_trend")
                    ->where('trendName', '=', $trend->trendName)
                    ->get();
            $row['trendName'] = $trend->trendName;

            foreach($eachTrends as $eachTrend) {
                $row[$eachTrend->week]  = $eachTrend->trendVal;
            }
            
            array_push($trendsData, $row);
        }

        $params['trends'] = $trendsData;
        return View::make('trendView', $params);
    }

    public function trendAddView() {
        return View::make('trendAddView');
    }

    public function trendAdd(Request $request) {
        $trendName = $request->trendName;
        $newTrendData = ['name' => $trendName];
        
        for($i=1; $i<54; $i++) {
            $key        = "week_".$i;
            $trendVal   = $request[$key];
            DB::table('tbl_trend')
                ->insert([
                    'trendName' => $trendName,
                    'trendVal'  => $trendVal,
                    'week'      => $i
                ]);
        }

        return redirect()->route('trendView');
    }

    public function trendUpdate() {
        $trendName  = $_GET["trendName"];
        $week       = $_GET["week"];
        $trendVal   = $_GET["value"]; 

        DB::table('tbl_trend')
            ->where('trendName' , '=', $trendName)
            ->where('week'      , '=', $week)
            ->update([
                'trendVal'    => $trendVal
            ]);

        return 'success';
    }

    public function forcastOutput() {
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

        $now = Carbon::now();
        $currentWeek = $now->weekOfYear;

        $query      = DB::table('product');
        if(Session::has('sortItem')) {
            $query ->orderby('product.'.Session::get('sortItem'), Session::get('sort'));
        } else {
            $query ->orderby('product.modelcode', 'desc');
        }
        $products   = $query->paginate(100);

        $warehouses = DB::table('warehouse')
                        ->get();

        $lastYear           = date("Y", strtotime("-1 year"));
        $currentYear        = date("Y"); 
        $date               = Carbon::parse($lastYear.'-12-28 16:00:00');
        $weekOfYear         = $date->weekOfYear;
        $date               = Carbon::parse($currentYear.'-12-28 16:00:00');
        $weekOfCurrentYear  = $date->weekOfYear;

        $weekArr            = [];
        $tempweekArr        = [];
        for($i=1; $i<=$weekOfYear; $i++) {
            array_push($tempweekArr, "last_".$i);
        }
        
        $currentWeekIndex = 0;
        for($i=1; $i<=$weekOfCurrentYear; $i++) {
            if($i == $currentWeek) {
                $currentWeekIndex = count($tempweekArr);
            }
            array_push($tempweekArr, "now_".$i);
        }

        for($i=($currentWeekIndex-26); $i<=($currentWeekIndex+25); $i++) {
            if(isset($tempweekArr[$i])) {
                array_push($weekArr, $tempweekArr[$i]);
            }
        }

        if(count($weekArr) < $weekOfCurrentYear) {
            $i = 1;
            while(1) {
                array_push($weekArr, "next_".$i);
                $i++;

                if(count($weekArr) == $weekOfCurrentYear) {
                    break;
                }
            }
        }

        foreach($products as $product) {
            $productId      = $product->productid;
            $lagerstand     = DB::table('lagerstand')
                                ->where('productid', '=', $productId)
                                ->selectRaw(DB::raw("COALESCE(sum(quantity),0) as total_qty"))
                                ->first();
                
            $last   = DB::table('soldweekly')
                        ->where('productid', '=', $productId)
                        ->orderby('weeksell', 'desc')
                        ->first();

            $product->total_qty = $lagerstand->total_qty;
            
            $index              = 0;
            $total              = 0;
            $aver_1             = 0;
            $aver_5             = 0;
            $aver_13            = 0;
            $aver_26            = 0;
            $unconfirmedAmount  = 0;

            for($i=0; $i<count($weekArr); $i++) {
                $weekNumArr = explode("_", $weekArr[$i]);

                if($weekNumArr[0] == "last") {
                    $soldweekly = DB::table('soldweekly')
                                    ->where('productid', '=', $productId)
                                    ->where('weeksell' , '=', $weekNumArr[1])
                                    ->where('year'     , '=', $lastYear)
                                    ->selectRaw(DB::raw("COALESCE(sum(quantity),0) as total_qty"))
                                    ->get();

                    $weekly_totol_qty_key           = "soldweekly_total_qty_".$i;
                    $weekly_warehouse_totol_qty_key = "soldweekly_warehouse_total_qty_".$i;
                    if(count($soldweekly) > 0) {
                        $product->$weekly_totol_qty_key = $soldweekly[0]->total_qty;
                        $total += intval($soldweekly[0]->total_qty);
                    } else {
                        $product->$weekly_totol_qty_key = 0;
                    }
                    $product->$weekly_warehouse_totol_qty_key = $lagerstand->total_qty;
                } elseif($weekNumArr[0] == "now") {
                    if($weekNumArr[1] < $currentWeek) {
                        $soldweekly = DB::table('soldweekly')
                                        ->where('productid', '=', $productId)
                                        ->where('weeksell' , '=', $weekNumArr[1])
                                        ->where('year'     , '=', $currentYear)
                                        ->selectRaw(DB::raw("COALESCE(sum(quantity),0) as total_qty"))
                                        ->get();

                        $unconfirmedAmount  = DB::table('newcontainer') 
                                                ->where('productid'  ,      '=', $productId)
                                                ->where('deliveryweek'  ,   '=', $weekNumArr[1])
                                                ->get();

                        $weekly_totol_qty_key           = "soldweekly_total_qty_".$i;
                        $weekly_warehouse_totol_qty_key = "soldweekly_warehouse_total_qty_".$i;
                        if(count($soldweekly) > 0) {
                            $product->$weekly_totol_qty_key = $soldweekly[0]->total_qty;
                            $total += intval($soldweekly[0]->total_qty);
                        } else {
                            $product->$weekly_totol_qty_key = 0;
                        }
                        $product->$weekly_warehouse_totol_qty_key = $lagerstand->total_qty;                        
                    } else {
                        $sum    = 0;
                        $count  = 1;
                        $index  = $i-1;

                        $unconfirmedAmount  = DB::table('newcontainer') 
                                                ->where('productid'  ,      '=', $productId)
                                                ->where('deliveryweek'  ,   '=', $weekNumArr[1])
                                                ->get();
                        
                        $unconfirmedAmountTotal = 0;
                        if(count($unconfirmedAmount) > 0) {
                            foreach($unconfirmedAmount as $amountItem) {
                                $unconfirmedAmountTotal += intval($amountItem->quantity);
                            }
                        }

                        while(1) {
                            $weekly_totol_qty_key = "soldweekly_total_qty_".$index;
                            if($count == 1) {
                                $aver_1 = round($product->$weekly_totol_qty_key, 3);
                            }
                            $sum += round($product->$weekly_totol_qty_key, 3);
                            if($count == 5) {
                                $aver_5 = round($sum/5, 3);
                            }

                            if($count == 13) {
                                $aver_13 = round($sum/13, 3);
                            }

                            if($count == 26) {
                                $aver_26 = round($sum/26, 3);
                                break;
                            }

                            $count++;
                            $index--;
                        }
                            
                        $primary_key                            = "soldweekly_total_qty_new_".$i;
                        $before_primary_key                     = "soldweekly_total_qty_new_".($i-1);
                        $weekly_warehouse_totol_qty_key         = "soldweekly_warehouse_total_qty_new_".$i;
                        $before_weekly_warehouse_totol_qty_key  = "soldweekly_warehouse_total_qty_new_".($i-1);

                        if(isset($product->$before_weekly_warehouse_totol_qty_key)) {
                            $before_weekly_warehouse_totol_qty_key = "soldweekly_warehouse_total_qty_new_".($i-1);
                        } else {
                            $before_weekly_warehouse_totol_qty_key = "soldweekly_warehouse_total_qty_".($i-1);
                        }

                        if(!isset($product->$primary_key)) {
                            $primary_key = "soldweekly_total_qty_".$i;
                        }

                        if(!isset($product->$before_primary_key)) {
                            $before_primary_key = "soldweekly_total_qty_".($i-1);
                        }

                        if(isset($product->$weekly_warehouse_totol_qty_key)) {
                            $weekly_warehouse_totol_qty_key = "soldweekly_warehouse_total_qty_new_".$i;
                        } else {
                            $weekly_warehouse_totol_qty_key = "soldweekly_warehouse_total_qty_".$i;
                        }
                        
                        $trendVal = DB::table('tbl_trend')
                                        ->where('week'      , '=', $weekNumArr[1])
                                        ->where('trendName' , '=', 'Normal')
                                        ->first();
                                        
                        if(!empty($trendVal)) {
                            if($trendVal->trendVal == null || $trendVal->trendVal == "") {
                                $trendVal = 0;
                            } else {
                                $trendVal = $trendVal->trendVal;
                            }
                            
                        } else {
                            $trendVal = 0;
                        }
                        
                        if($weekNumArr[1] == $currentWeek) {
                            $currentWeekIndex = $i;
                            $product->$primary_key = round((2*$aver_1+2*$aver_5+5*$aver_13+3*$aver_26)/12, 3);
                        } else {
                            $product->$primary_key = round($product->$before_primary_key * (100+$trendVal)/100, 3);
                        }
                        
                        $product->$weekly_warehouse_totol_qty_key = $product->$before_weekly_warehouse_totol_qty_key - $product->$before_primary_key + intval($unconfirmedAmountTotal);
                    }
                } else {
                    $sum    = 0;
                    $count  = 1;
                    $index  = $i-1;

                    $unconfirmedAmountTotal = 0;
                    $unconfirmedAmount  = DB::table('newcontainer') 
                                                ->where('productid'  ,      '=', $productId)
                                                ->where('deliveryweek'  ,   '=', $weekNumArr[1])
                                                ->get();

                    if(count($unconfirmedAmount) > 0) {
                        foreach($unconfirmedAmount as $amountItem) {
                            $unconfirmedAmountTotal += intval($amountItem->quantity);
                        }
                    }
                    
                    // if($productId == 1026) {
                    //     echo "<br>".$unconfirmedAmountTotal;
                    // }
                    while(1) {
                        $weekly_totol_qty_key = "soldweekly_total_qty_".$index;
                        if($count == 1) {
                            $aver_1 = round($product->$weekly_totol_qty_key, 3);
                        }
                        $sum += round($product->$weekly_totol_qty_key, 3);
                        if($count == 5) {
                            $aver_5 = round($sum/5, 3);
                        }

                        if($count == 13) {
                            $aver_13 = round($sum/13, 3);
                        }

                        if($count == 26) {
                            $aver_26 = round($sum/26, 3);
                            break;
                        }

                        $count++;
                        $index--;
                    }
                        
                    $primary_key        = "soldweekly_total_qty_new_".$i;
                    $before_primary_key = "soldweekly_total_qty_new_".($i-1);
                    $weekly_warehouse_totol_qty_key = "soldweekly_warehouse_total_qty_new_".$i;
                    $before_weekly_warehouse_totol_qty_key = "soldweekly_warehouse_total_qty_new_".($i-1);
                    if(isset($product->$before_weekly_warehouse_totol_qty_key)) {
                        $before_weekly_warehouse_totol_qty_key = "soldweekly_warehouse_total_qty_new_".($i-1);
                    } else {
                        $before_weekly_warehouse_totol_qty_key = "soldweekly_warehouse_total_qty_".($i-1);
                    }

                    if(!isset($product->$primary_key)) {
                        $primary_key = "soldweekly_total_qty_".$i;
                    }

                    if(!isset($product->$before_primary_key)) {
                        $before_primary_key = "soldweekly_total_qty_".($i-1);
                    }

                    if(isset($product->$weekly_warehouse_totol_qty_key)) {
                        $weekly_warehouse_totol_qty_key = "soldweekly_warehouse_total_qty_new_".$i;
                    } else {
                        $weekly_warehouse_totol_qty_key = "soldweekly_warehouse_total_qty_".$i;
                    }
                    
                    $trendVal = DB::table('tbl_trend')
                                    ->where('week'      , '=', $weekNumArr[1])
                                    ->where('trendName' , '=', 'Normal')
                                    ->first();

                    if(!empty($trendVal)) {
                        if($trendVal->trendVal == null || $trendVal->trendVal == "") {
                            $trendVal = 0;
                        } else {
                            $trendVal = $trendVal->trendVal;
                        }
                        
                    } else {
                        $trendVal = 0;
                    }
                    
                    if($weekNumArr[1] == $currentWeek) {
                        $product->$primary_key = round((2*$aver_1+2*$aver_5+5*$aver_13+3*$aver_26)/12, 3);
                    } else {
                        $product->$primary_key = round($product->$before_primary_key * (100+$trendVal)/100, 3);
                    }
                    
                    $product->$weekly_warehouse_totol_qty_key = $product->$before_weekly_warehouse_totol_qty_key - $product->$before_primary_key + $unconfirmedAmountTotal;
                }
            }
        }
        
        $trends = DB::table('tbl_trend')->groupby('trendName')->get();
        $params['weekArr']          = $weekArr;
        $params['trends']           = $trends;
        $params['currentWeek']      = $currentWeek;
        $params['currentWeekIndex'] = $currentWeekIndex;
        $params['products']         = $products;
        $params['warehouses']       = $warehouses;
        
        return View::make('forcastOutput', $params);
    }   

    public function calculate() {
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

        $now = Carbon::now();
        $currentWeek = $now->weekOfYear;

        $query      = DB::table('product');
        if(Session::has('sortItem')) {
            $query ->orderby('product.'.Session::get('sortItem'), Session::get('sort'));
        } else {
            $query ->orderby('product.modelcode', 'desc');
        }
        $products   = $query->paginate(100);

        $warehouses = DB::table('warehouse')
                        ->get();

        $lastYear           = date("Y", strtotime("-1 year"));
        $currentYear        = date("Y"); 
        $date               = Carbon::parse($lastYear.'-12-28 16:00:00');
        $weekOfYear         = $date->weekOfYear;
        $date               = Carbon::parse($currentYear.'-12-28 16:00:00');
        $weekOfCurrentYear  = $date->weekOfYear;

        $weekArr            = [];
        $tempweekArr        = [];
        for($i=1; $i<=$weekOfYear; $i++) {
            array_push($tempweekArr, "last_".$i);
        }
        
        $currentWeekIndex = 0;
        for($i=1; $i<=$weekOfCurrentYear; $i++) {
            if($i == $currentWeek) {
                $currentWeekIndex = count($tempweekArr);
            }
            array_push($tempweekArr, "now_".$i);
        }

        for($i=($currentWeekIndex-26); $i<=($currentWeekIndex+25); $i++) {
            if(isset($tempweekArr[$i])) {
                array_push($weekArr, $tempweekArr[$i]);
            }
        }

        if(count($weekArr) < $weekOfCurrentYear) {
            $i = 1;
            while(1) {
                array_push($weekArr, "next_".$i);
                $i++;

                if(count($weekArr) == $weekOfCurrentYear) {
                    break;
                }
            }
        }

        foreach($products as $product) {
            $productId      = $product->productid;
            $lagerstand     = DB::table('lagerstand')
                                ->where('productid', '=', $productId)
                                ->selectRaw(DB::raw("COALESCE(sum(quantity),0) as total_qty"))
                                ->first();
                
            $last   = DB::table('soldweekly')
                        ->where('productid', '=', $productId)
                        ->orderby('weeksell', 'desc')
                        ->first();

            $product->total_qty = $lagerstand->total_qty;
            
            $index      = 0;
            $total      = 0;
            $aver_1     = 0;
            $aver_5     = 0;
            $aver_13    = 0;
            $aver_26    = 0;

            for($i=0; $i<count($weekArr); $i++) {
                $weekNumArr = explode("_", $weekArr[$i]);               
                
                if($weekNumArr[0] == "last") {
                    $soldweekly = DB::table('soldweekly')
                                    ->where('productid', '=', $productId)
                                    ->where('weeksell' , '=', $weekNumArr[1])
                                    ->where('year'     , '=', $lastYear)
                                    ->selectRaw(DB::raw("COALESCE(sum(quantity),0) as total_qty"))
                                    ->get();
    
                    $weekly_totol_qty_key           = "soldweekly_total_qty_".$i;
                    $weekly_warehouse_totol_qty_key = "soldweekly_warehouse_total_qty_".$i;
                    if(count($soldweekly) > 0) {
                        $product->$weekly_totol_qty_key = $soldweekly[0]->total_qty;
                        $total += intval($soldweekly[0]->total_qty);
                    } else {
                        $product->$weekly_totol_qty_key = 0;
                    }
                    $product->$weekly_warehouse_totol_qty_key = $lagerstand->total_qty;
                } elseif($weekNumArr[0] == "now") {
                    if($weekNumArr[1] < $currentWeek) {
                        $soldweekly = DB::table('soldweekly')
                                        ->where('productid', '=', $productId)
                                        ->where('weeksell' , '=', $weekNumArr[1])
                                        ->where('year'     , '=', $currentYear)
                                        ->selectRaw(DB::raw("COALESCE(sum(quantity),0) as total_qty"))
                                        ->get();
    
                        $weekly_totol_qty_key           = "soldweekly_total_qty_".$i;
                        $weekly_warehouse_totol_qty_key = "soldweekly_warehouse_total_qty_".$i;
                        if(count($soldweekly) > 0) {
                            $product->$weekly_totol_qty_key = $soldweekly[0]->total_qty;
                            $total += intval($soldweekly[0]->total_qty);
                        } else {
                            $product->$weekly_totol_qty_key = 0;
                        }
                        $product->$weekly_warehouse_totol_qty_key = $lagerstand->total_qty;                        
                    } else {
                        $sum    = 0;
                        $count  = 1;
                        $index  = $i-1;
                        while(1) {
                            $weekly_totol_qty_key = "soldweekly_total_qty_".$index;
                            if($count == 1) {
                                $aver_1 = round($product->$weekly_totol_qty_key, 3);
                                $aver_1_key = "aver_1_".$i; 
                                $product->$aver_1_key = $aver_1;
                            }
                            $sum += round($product->$weekly_totol_qty_key, 3);
                            if($count == 5) {
                                $aver_5_key = "aver_5_".$i; 
                                $aver_5 = round($sum/5, 3);
                                $product->$aver_5_key = $aver_5;
                            }
        
                            if($count == 13) {
                                $aver_13_key = "aver_13_".$i; 
                                $aver_13 = round($sum/13, 3);
                                $product->$aver_13_key = $aver_13;
                            }
        
                            if($count == 26) {
                                $aver_26_key = "aver_26_".$i; 
                                $aver_26 = round($sum/26, 3);
                                $product->$aver_26_key = $aver_26;
                                break;
                            }
    
                            $count++;
                            $index--;
                        }
                            
                        $primary_key        = "soldweekly_total_qty_new_".$i;
                        $before_primary_key = "soldweekly_total_qty_new_".($i-1);
                        $weekly_warehouse_totol_qty_key = "soldweekly_warehouse_total_qty_new_".$i;
                        $before_weekly_warehouse_totol_qty_key = "soldweekly_warehouse_total_qty_new_".($i-1);
                        if(isset($product->$before_weekly_warehouse_totol_qty_key)) {
                            $before_weekly_warehouse_totol_qty_key = "soldweekly_warehouse_total_qty_new_".($i-1);
                        } else {
                            $before_weekly_warehouse_totol_qty_key = "soldweekly_warehouse_total_qty_".($i-1);
                        }
    
                        if(!isset($product->$primary_key)) {
                            $primary_key = "soldweekly_total_qty_".$i;
                        }
    
                        if(!isset($product->$before_primary_key)) {
                            $before_primary_key = "soldweekly_total_qty_".($i-1);
                        }
    
                        if(isset($product->$weekly_warehouse_totol_qty_key)) {
                            $weekly_warehouse_totol_qty_key = "soldweekly_warehouse_total_qty_new_".$i;
                        } else {
                            $weekly_warehouse_totol_qty_key = "soldweekly_warehouse_total_qty_".$i;
                        }
                        
                        $trendVal = DB::table('tbl_trend')
                                        ->where('week'      , '=', $weekNumArr[1])
                                        ->where('trendName' , '=', 'Normal')
                                        ->first();
                                        
                        if(!empty($trendVal)) {
                            if($trendVal->trendVal == null || $trendVal->trendVal == "") {
                                $trendVal = 0;
                            } else {
                                $trendVal = $trendVal->trendVal;
                            }
                            
                        } else {
                            $trendVal = 0;
                        }
                        
                        if($weekNumArr[1] == $currentWeek) {
                            $currentWeekIndex = $i;
                            $product->$primary_key = round((2*$aver_1+2*$aver_5+5*$aver_13+3*$aver_26)/12, 3);
                        } else {
                            $product->$primary_key = round($product->$before_primary_key * (100+$trendVal)/100, 3);
                        }
                        
                        $product->$weekly_warehouse_totol_qty_key = $product->$before_weekly_warehouse_totol_qty_key - $product->$before_primary_key;

                        $targetWeekIndex = $currentWeekIndex;

                        if(Session::has("targetWeekIndex")) {
                            $targetWeekIndex = Session::get("targetWeekIndex");
                        }

                        if(isset($_GET['week'])) {
                            if($_GET['week'] == $weekNumArr[1]) {
                                $targetWeekIndex = $i;
                                Session::put("targetWeekIndex", $targetWeekIndex);
                            }
                        }
                    }
                } else {
                    $sum    = 0;
                    $count  = 1;
                    $index  = $i-1;
                    while(1) {
                        $weekly_totol_qty_key = "soldweekly_total_qty_".$index;
                        if($count == 1) {
                            $aver_1 = round($product->$weekly_totol_qty_key, 3);
                            $aver_1_key = "aver_1_".$i; 
                            $product->$aver_1_key = $aver_1;
                        }
                        $sum += round($product->$weekly_totol_qty_key, 3);
                        if($count == 5) {
                            $aver_5_key = "aver_5_".$i; 
                            $aver_5 = round($sum/5, 3);
                            $product->$aver_5_key = $aver_5;
                        }
    
                        if($count == 13) {
                            $aver_13_key = "aver_13_".$i; 
                            $aver_13 = round($sum/13, 3);
                            $product->$aver_13_key = $aver_13;
                        }
    
                        if($count == 26) {
                            $aver_26_key = "aver_26_".$i; 
                            $aver_26 = round($sum/26, 3);
                            $product->$aver_26_key = $aver_26;
                            break;
                        }
    
                        $count++;
                        $index--;
                    }
                        
                    $primary_key        = "soldweekly_total_qty_new_".$i;
                    $before_primary_key = "soldweekly_total_qty_new_".($i-1);
                    $weekly_warehouse_totol_qty_key = "soldweekly_warehouse_total_qty_new_".$i;
                    $before_weekly_warehouse_totol_qty_key = "soldweekly_warehouse_total_qty_new_".($i-1);
                    if(isset($product->$before_weekly_warehouse_totol_qty_key)) {
                        $before_weekly_warehouse_totol_qty_key = "soldweekly_warehouse_total_qty_new_".($i-1);
                    } else {
                        $before_weekly_warehouse_totol_qty_key = "soldweekly_warehouse_total_qty_".($i-1);
                    }
    
                    if(!isset($product->$primary_key)) {
                        $primary_key = "soldweekly_total_qty_".$i;
                    }
    
                    if(!isset($product->$before_primary_key)) {
                        $before_primary_key = "soldweekly_total_qty_".($i-1);
                    }
    
                    if(isset($product->$weekly_warehouse_totol_qty_key)) {
                        $weekly_warehouse_totol_qty_key = "soldweekly_warehouse_total_qty_new_".$i;
                    } else {
                        $weekly_warehouse_totol_qty_key = "soldweekly_warehouse_total_qty_".$i;
                    }
                    
                    $trendVal = DB::table('tbl_trend')
                                    ->where('week'      , '=', $weekNumArr[1])
                                    ->where('trendName' , '=', 'Normal')
                                    ->first();
    
                    if(!empty($trendVal)) {
                        if($trendVal->trendVal == null || $trendVal->trendVal == "") {
                            $trendVal = 0;
                        } else {
                            $trendVal = $trendVal->trendVal;
                        }
                        
                    } else {
                        $trendVal = 0;
                    }
                    
                    if($weekNumArr[1] == $currentWeek) {
                        $product->$primary_key = round((2*$aver_1+2*$aver_5+5*$aver_13+3*$aver_26)/12, 3);
                    } else {
                        $product->$primary_key = round($product->$before_primary_key * (100+$trendVal)/100, 3);
                    }
                    
                    $product->$weekly_warehouse_totol_qty_key = $product->$before_weekly_warehouse_totol_qty_key - $product->$before_primary_key;

                    $targetWeekIndex = $currentWeekIndex;

                    if(Session::has("targetWeekIndex")) {
                        $targetWeekIndex = Session::get("targetWeekIndex");
                    }
                    
                    if(isset($_GET['week'])) {
                        if($_GET['week'] == $weekNumArr[1]) {
                            $targetWeekIndex = $i;
                            Session::put("targetWeekIndex", $targetWeekIndex);
                        }
                    }
                }
            }
        }     
        
        $trends = DB::table('tbl_trend')->groupby('trendName')->get();

        $params['weekArr']              = $weekArr;
        $params['trends']               = $trends;
        $params['currentWeek']          = $currentWeek;
        $params['targetWeekIndex']      = $targetWeekIndex;
        $params['currentWeekIndex']     = $currentWeekIndex;
        $params['products']             = $products;
        $params['warehouses']           = $warehouses;
        $params['weekOfCurrentYear']    = $weekOfCurrentYear;
        
        return View::make('calculate', $params);
    }

    public function caltrend() {
        $productId      = $_GET['productId'];
        $trendName      = $_GET['trendName'];

        $now            = Carbon::now();
        $currentWeek    = $now->weekOfYear;

        $products       = DB::table('product')
                            ->where('productid', '=', $productId)
                            ->get();

        $warehouses     = DB::table('warehouse')
                            ->get();

        $lastYear           = date("Y", strtotime("-1 year"));
        $currentYear        = date("Y"); 
        $date               = Carbon::parse($lastYear.'-12-28 16:00:00');
        $weekOfYear         = $date->weekOfYear;
        $date               = Carbon::parse($currentYear.'-12-28 16:00:00');
        $weekOfCurrentYear  = $date->weekOfYear;

        $weekArr            = [];
        $tempweekArr        = [];
        for($i=1; $i<=$weekOfYear; $i++) {
            array_push($tempweekArr, "last_".$i);
        }
        
        $currentWeekIndex = 0;
        for($i=1; $i<=$weekOfCurrentYear; $i++) {
            if($i == $currentWeek) {
                $currentWeekIndex = count($tempweekArr);
            }
            array_push($tempweekArr, "now_".$i);
        }

        for($i=($currentWeekIndex-26); $i<=($currentWeekIndex+25); $i++) {
            if(isset($tempweekArr[$i])) {
                array_push($weekArr, $tempweekArr[$i]);
            }
        }

        if(count($weekArr) < $weekOfCurrentYear) {
            $i = 1;
            while(1) {
                array_push($weekArr, "next_".$i);
                $i++;

                if(count($weekArr) == $weekOfCurrentYear) {
                    break;
                }
            }
        }

        foreach($products as $product) {
            $productId      = $product->productid;
            $lagerstand     = DB::table('lagerstand')
                                ->where('productid', '=', $productId)
                                ->selectRaw(DB::raw("COALESCE(sum(quantity),0) as total_qty"))
                                ->first();
                
            $last = DB::table('soldweekly')
                ->where('productid', '=', $productId)
                ->orderby('weeksell', 'desc')
                ->first();

            $product->total_qty = $lagerstand->total_qty;
            
            $index      = 0;
            $total      = 0;
            $aver_1     = 0;
            $aver_5     = 0;
            $aver_13    = 0;
            $aver_26    = 0;

            for($i=0; $i<count($weekArr); $i++) {
                $weekNumArr = explode("_", $weekArr[$i]);

                if($weekNumArr[0] == "last") {
                    $soldweekly = DB::table('soldweekly')
                                    ->where('productid', '=', $productId)
                                    ->where('weeksell' , '=', $weekNumArr[1])
                                    ->where('year'     , '=', $lastYear)
                                    ->selectRaw(DB::raw("COALESCE(sum(quantity),0) as total_qty"))
                                    ->get();

                    $weekly_totol_qty_key           = "soldweekly_total_qty_".$i;
                    $weekly_warehouse_totol_qty_key = "soldweekly_warehouse_total_qty_".$i;
                    if(count($soldweekly) > 0) {
                        $product->$weekly_totol_qty_key = $soldweekly[0]->total_qty;
                        $total += intval($soldweekly[0]->total_qty);
                    } else {
                        $product->$weekly_totol_qty_key = 0;
                    }
                    $product->$weekly_warehouse_totol_qty_key = $lagerstand->total_qty;
                } elseif($weekNumArr[0] == "now") {
                    if($weekNumArr[1] < $currentWeek) {
                        $soldweekly = DB::table('soldweekly')
                                        ->where('productid', '=', $productId)
                                        ->where('weeksell' , '=', $weekNumArr[1])
                                        ->where('year'     , '=', $currentYear)
                                        ->selectRaw(DB::raw("COALESCE(sum(quantity),0) as total_qty"))
                                        ->get();

                        $weekly_totol_qty_key           = "soldweekly_total_qty_".$i;
                        $weekly_warehouse_totol_qty_key = "soldweekly_warehouse_total_qty_".$i;
                        if(count($soldweekly) > 0) {
                            $product->$weekly_totol_qty_key = $soldweekly[0]->total_qty;
                            $total += intval($soldweekly[0]->total_qty);
                        } else {
                            $product->$weekly_totol_qty_key = 0;
                        }
                        $product->$weekly_warehouse_totol_qty_key = $lagerstand->total_qty;                        
                    } else {
                        $sum    = 0;
                        $count  = 1;
                        $index  = $i-1;
                        while(1) {
                            $weekly_totol_qty_key = "soldweekly_total_qty_".$index;
                            if($count == 1) {
                                $aver_1 = round($product->$weekly_totol_qty_key, 3);
                                $aver_1_key = "aver_1_".$i; 
                                $product->$aver_1_key = $aver_1;
                            }
                            $sum += round($product->$weekly_totol_qty_key, 3);
                            if($count == 5) {
                                $aver_5_key = "aver_5_".$i; 
                                $aver_5 = round($sum/5, 3);
                                $product->$aver_5_key = $aver_5;
                            }
        
                            if($count == 13) {
                                $aver_13_key = "aver_13_".$i; 
                                $aver_13 = round($sum/13, 3);
                                $product->$aver_13_key = $aver_13;
                            }
        
                            if($count == 26) {
                                $aver_26_key = "aver_26_".$i; 
                                $aver_26 = round($sum/26, 3);
                                $product->$aver_26_key = $aver_26;
                                break;
                            }

                            $count++;
                            $index--;
                        }
                            
                        $primary_key        = "soldweekly_total_qty_new_".$i;
                        $before_primary_key = "soldweekly_total_qty_new_".($i-1);
                        $weekly_warehouse_totol_qty_key = "soldweekly_warehouse_total_qty_new_".$i;
                        $before_weekly_warehouse_totol_qty_key = "soldweekly_warehouse_total_qty_new_".($i-1);
                        if(isset($product->$before_weekly_warehouse_totol_qty_key)) {
                            $before_weekly_warehouse_totol_qty_key = "soldweekly_warehouse_total_qty_new_".($i-1);
                        } else {
                            $before_weekly_warehouse_totol_qty_key = "soldweekly_warehouse_total_qty_".($i-1);
                        }

                        if(!isset($product->$primary_key)) {
                            $primary_key = "soldweekly_total_qty_".$i;
                        }

                        if(!isset($product->$before_primary_key)) {
                            $before_primary_key = "soldweekly_total_qty_".($i-1);
                        }

                        if(isset($product->$weekly_warehouse_totol_qty_key)) {
                            $weekly_warehouse_totol_qty_key = "soldweekly_warehouse_total_qty_new_".$i;
                        } else {
                            $weekly_warehouse_totol_qty_key = "soldweekly_warehouse_total_qty_".$i;
                        }
                        
                        $trendVal = DB::table('tbl_trend')
                                        ->where('week'      , '=', $weekNumArr[1])
                                        ->where('trendName' , '=', $trendName)
                                        ->first();
                                        
                        if(!empty($trendVal)) {
                            if($trendVal->trendVal == null || $trendVal->trendVal == "") {
                                $trendVal = 0;
                            } else {
                                $trendVal = $trendVal->trendVal;
                            }
                            
                        } else {
                            $trendVal = 0;
                        }
                        
                        if($weekNumArr[1] == $currentWeek) {
                            $currentWeekIndex = $i;
                            $product->$primary_key = round((2*$aver_1+2*$aver_5+5*$aver_13+3*$aver_26)/12, 3);
                        } else {
                            $product->$primary_key = round($product->$before_primary_key * (100+$trendVal)/100, 3);
                        }
                        
                        $product->$weekly_warehouse_totol_qty_key = $product->$before_weekly_warehouse_totol_qty_key - $product->$before_primary_key;
                    }
                } else {
                    $sum    = 0;
                    $count  = 1;
                    $index  = $i-1;
                    while(1) {
                        $weekly_totol_qty_key = "soldweekly_total_qty_".$index;
                        if($count == 1) {
                            $aver_1 = round($product->$weekly_totol_qty_key, 3);
                            $aver_1_key = "aver_1_".$i; 
                            $product->$aver_1_key = $aver_1;
                        }
                        $sum += round($product->$weekly_totol_qty_key, 3);
                        if($count == 5) {
                            $aver_5_key = "aver_5_".$i; 
                            $aver_5 = round($sum/5, 3);
                            $product->$aver_5_key = $aver_5;
                        }
    
                        if($count == 13) {
                            $aver_13_key = "aver_13_".$i; 
                            $aver_13 = round($sum/13, 3);
                            $product->$aver_13_key = $aver_13;
                        }
    
                        if($count == 26) {
                            $aver_26_key = "aver_26_".$i; 
                            $aver_26 = round($sum/26, 3);
                            $product->$aver_26_key = $aver_26;
                            break;
                        }

                        $count++;
                        $index--;
                    }
                        
                    $primary_key        = "soldweekly_total_qty_new_".$i;
                    $before_primary_key = "soldweekly_total_qty_new_".($i-1);
                    $weekly_warehouse_totol_qty_key = "soldweekly_warehouse_total_qty_new_".$i;
                    $before_weekly_warehouse_totol_qty_key = "soldweekly_warehouse_total_qty_new_".($i-1);
                    if(isset($product->$before_weekly_warehouse_totol_qty_key)) {
                        $before_weekly_warehouse_totol_qty_key = "soldweekly_warehouse_total_qty_new_".($i-1);
                    } else {
                        $before_weekly_warehouse_totol_qty_key = "soldweekly_warehouse_total_qty_".($i-1);
                    }

                    if(!isset($product->$primary_key)) {
                        $primary_key = "soldweekly_total_qty_".$i;
                    }

                    if(!isset($product->$before_primary_key)) {
                        $before_primary_key = "soldweekly_total_qty_".($i-1);
                    }

                    if(isset($product->$weekly_warehouse_totol_qty_key)) {
                        $weekly_warehouse_totol_qty_key = "soldweekly_warehouse_total_qty_new_".$i;
                    } else {
                        $weekly_warehouse_totol_qty_key = "soldweekly_warehouse_total_qty_".$i;
                    }
                    
                    $trendVal = DB::table('tbl_trend')
                                    ->where('week'      , '=', $weekNumArr[1])
                                    ->where('trendName' , '=', $trendName)
                                    ->first();

                    if(!empty($trendVal)) {
                        if($trendVal->trendVal == null || $trendVal->trendVal == "") {
                            $trendVal = 0;
                        } else {
                            $trendVal = $trendVal->trendVal;
                        }
                        
                    } else {
                        $trendVal = 0;
                    }                    
                    
                    if($weekNumArr[1] == $currentWeek) {
                        $product->$primary_key = round((2*$aver_1+2*$aver_5+5*$aver_13+3*$aver_26)/12, 3);
                    } else {
                        $product->$primary_key = round($product->$before_primary_key * (100+$trendVal)/100, 3);
                    }
                    
                    $product->$weekly_warehouse_totol_qty_key = $product->$before_weekly_warehouse_totol_qty_key - $product->$before_primary_key;
                }
            }
        }
        
        $params['currentWeekIndex']     = $currentWeekIndex;
        $params['products']             = $products;
        $params['weekArr']              = $weekArr;

        echo json_encode($params);
    }

    public function sendCalculateManufacturerOrder(Request $request) {
        $qtys           = $request->quantity;
        $deliveryweek   = $request->deliveryweek;
        $notes          = $request->notes;
        
        foreach ($qtys as $key => $qty) {
            if ($qty != 0) {
                if(isset($deliveryweek)) {
                    $deliveryweekVal = $deliveryweek;
                } else {
                    $deliveryweekVal = "";
                    $notesVal        = "";
                }

                DB::table('newcontainer')
                    ->insert([
                        'productid'     => $key,
                        'quantity'      => $qty,
                        'deliveryweek'  => $deliveryweekVal,
                        'notes'         => ""
                    ]);
            }
        }
        
        return Redirect::route('calculate')->with(['msg' => 'Item transfered for confirmation.']);
    }
}
