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

class FinanzstatusController extends Controller
{
    //
    public function index(){
        if(!Session::has('userID')) {
            return redirect()->route('login');
        }
        $products   = DB::table('product')
                        ->leftjoin('manufacturer', 'product.manufacturerid', '=', 'manufacturer.manufacturerid')
                        ->paginate(100);

        $warehouses = DB::table('warehouse')
                        ->get();

        $manufacturers = DB::table('manufacturer')
                        ->get();
                        
        foreach($products as $product) {
            $total_qty = 0;
            foreach($warehouses as $warehouse) {                
                $qty    = DB::table('lagerstand')
                        ->where('productid',    '=', $product->productid)
                        ->where('idwarehouse',  '=', $warehouse->idwarehouse)
                        ->selectRaw(DB::raw("COALESCE(sum(quantity),0) as total_qty"))
                        ->get();
                    
                $total_qty += intval($qty[0]->total_qty); 
                $product->{$warehouse->idwarehouse} = intval($qty[0]->total_qty);
            }
            
            $product->total_qty = $total_qty;

            if($product->virtualkit == "Yes") {
                $product->price = 0;
                for($i=1; $i<10; $i++) {
                    $item = "pcs".$i;
                    $itemProductId = "productid".$i;
                    $productid = $product->$itemProductId;
                    if($product->$item != null && $product->$item > 0 && $product->$item != "" && $productid != "" && $productid != null) {
                        $itemProduct = DB::table('product')
                                        ->where('product.modelcode',  '=', $productid)
                                        ->first();
                        
                        if(!empty($itemProduct)) {
                            $product->price = intval($product->price) + intval($itemProduct->price)*intval($product->$item);
                        }
                    }
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

        return View::make('finanzstatusView', $params);
    }

    public function finanzstatusUpdate() {
        if(isset($time_update)) {
            DB::table('updates')
                ->where('id', '=', 1)
                ->update(['date' => date('Y-m-d H:i:s')]);
        } else {

        }
        
        return redirect()->route('finanzstatusView');
    }
}
