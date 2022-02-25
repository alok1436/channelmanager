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

class KitController extends Controller {
    public function index() {
        if(!Session::has('userID')) {
            return redirect()->route('login');
        }
        
        $warehouses = DB::table('warehouse')
                        ->get();
        
        if(isset($_GET['warehouse']) || Session::has('kitwarehouse')) {
            if(isset($_GET['warehouse'])) {
                Session::put('kitwarehouse', $_GET['warehouse']);
                $warehouseId    = $_GET['warehouse'];
            } else {
                $warehouseId    = Session::get('kitwarehouse');
            }
            
            $products       = DB::table('product')
                                ->leftjoin('manufacturer', 'product.manufacturerid', '=', 'manufacturer.manufacturerid')
                                ->where('product.virtualkit', '=', 'Yes')
                                ->paginate(100);

            $manufacturers  = DB::table('manufacturer')
                                ->get();

            foreach($products as $product) {
                $qty        = DB::table('lagerstand')
                                ->where('productid',    '=', $product->productid)
                                ->where('idwarehouse',  '=', $warehouseId)
                                ->first();
                if(empty($qty)) {
                    $product->warehouseqty      = 0;
                    $product->warehousebuffer   = 0;
                } else {
                    $product->warehousebuffer   = $qty->buffer;
                    $product->warehouseqty      = $qty->quantity;
                }
                
                for($i=1; $i<10; $i++) {
                    $item = "pcs".$i;
                    $itemProductId = "productid".$i;
                    $productid = $product->$itemProductId;
                    if($product->$item != null && $product->$item > 0 && $product->$item != "" && $productid != "" && $productid != null) {
                        $itemProduct = DB::table('lagerstand')
                                        ->leftjoin('product', 'product.productid', '=', 'lagerstand.productid')
                                        ->where('lagerstand.idwarehouse',  '=', $warehouseId)
                                        ->where('product.modelcode',  '=', $productid)
                                        ->select('lagerstand.*')
                                        ->first();
                        
                        if(empty($itemProduct)) {
                            $product->$item = 0;
                        } else {
                            $product->$item = intval($itemProduct->quantity/$product->$item);
                        }
                        
                        if(isset($product->maxKit)) {
                            if($product->maxKit > $product->$item) {
                                $product->maxKit = $product->$item;
                            }
                        } else {
                            $product->maxKit = $product->$item;
                        }
                    }
                }

                if(!isset($product->maxKit)) {
                    $product->maxKit = 0;
                }
            }

            $params['products']         = $products;
            $params['manufacturers']    = $manufacturers;

            $warehouse  = DB::table('warehouse')
                                ->where('idwarehouse', '=', $warehouseId)
                                ->first();

            $params['warehouselocation'] = $warehouse->location;
        }
        
        $params['warehouses']       = $warehouses;    

        return View::make('kitView', $params);
    }

    public function bufferUpdate() {
        $warehouseId    = Session::get('kitwarehouse');
        $buffer         = $_GET['buffer'];
        $productid      = $_GET['productid'];

        $data = DB::table('lagerstand')
            ->where('productid',    '=', $productid)
            ->where('idwarehouse',  '=', $warehouseId)
            ->first();

        if(empty($data)) {
            DB::table('lagerstand')
            ->insert([
                'idwarehouse' => $warehouseId,
                'productid'   => $productid,
                'quantity'    => 0,
                'dateupdate'  => date('Y-m-d H:i:s'),
                'buffer' => $buffer
            ]);
        } else {
            DB::table('lagerstand')
            ->where('productid',    '=', $productid)
            ->where('idwarehouse',  '=', $warehouseId)
            ->update([
                'buffer' => $buffer
            ]);
        }
            

        echo json_encode($data);
    }

    public function sendNewKit() {
        $modelcode      = $_GET['modelcode'];
        $newCount       = $_GET['newCount'];
        $warehouseId    = Session::get('kitwarehouse');

        $product = DB::table("product")
                    ->where("modelcode", "=", $modelcode)
                    ->first();

        $kitProductinLagerstand = DB::table('lagerstand')
                    ->leftjoin('product', 'product.productid', '=', 'lagerstand.productid')
                    ->where('lagerstand.idwarehouse',  '=', $warehouseId)
                    ->where('product.modelcode',  '=', $modelcode)
                    ->first();

        if(empty($kitProductinLagerstand)) {
            DB::table('lagerstand')
                ->insert([
                    'idwarehouse' => $warehouseId,
                    'productid'   => $product->productid,
                    'quantity'    => $newCount,
                    'dateupdate'  => date('Y-m-d H:i:s'),
                    'buffer'      => 0
                ]);
        } else {
            DB::table('lagerstand')
            ->leftjoin('product', 'product.productid', '=', 'lagerstand.productid')
            ->where('lagerstand.idwarehouse',  '=', $warehouseId)
            ->where('product.modelcode',  '=', $modelcode)
            ->increment('lagerstand.quantity', $newCount);
        }

        for($i=1; $i<10; $i++) {
            $item = "pcs".$i;
            $itemProductId  = "productid".$i;
            $productid      = $product->$itemProductId;
            if($product->$item != null && $product->$item > 0 && $product->$item != "" && $productid != "" && $productid != null) {
                $itemProduct = DB::table('lagerstand')
                                ->leftjoin('product', 'product.productid', '=', 'lagerstand.productid')
                                ->where('lagerstand.idwarehouse',  '=', $warehouseId)
                                ->where('product.modelcode',  '=', $productid)
                                ->select('lagerstand.*')
                                ->first();
                
                if(empty($itemProduct)) {

                } else {
                    $decrementNum = $newCount*$product->$item;
                    DB::table('lagerstand')
                        ->leftjoin('product', 'product.productid', '=', 'lagerstand.productid')
                        ->where('lagerstand.idwarehouse',  '=', $warehouseId)
                        ->where('product.modelcode',  '=', $productid)
                        ->decrement('lagerstand.quantity', $decrementNum);
                }
            }
        }

        echo "success";
    }
}