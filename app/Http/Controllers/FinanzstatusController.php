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
use Yajra\Datatables\Datatables;
use App\Models\Product;

class FinanzstatusController extends Controller
{
    //

    public function ajaxFinanzstatus(){
        $warehouses = DB::table('warehouse')
                        ->get();
        $products   = Product::leftjoin('manufacturer', 'product.manufacturerid', '=', 'manufacturer.manufacturerid')
                ->get();
        $rawColumns = ['price'];
        
        $manufacturers = DB::table('manufacturer')
                        ->get();
// dd($products);
//         foreach($products as $key=>$product){
//             $arrr = [];
//             foreach($warehouses as $house){
//                 $arrr[$house->idwarehouse] = $product->getTotalQuantity($house);
//             }
//             $products[$key]->warehouse_quantity = $arrr;
//         }
        foreach($products as $product) {
            $total_qty = 0;
            foreach($warehouses as $warehouse) {                
                $qty    = DB::table('lagerstand')
                        ->where('productid',    '=', $product->productid)
                        ->where('idwarehouse',  '=', $warehouse->idwarehouse)
                        ->selectRaw(DB::raw("COALESCE(sum(quantity),0) as total_qty"))
                        ->first();
                    
                $total_qty += intval($qty->total_qty);
                $product->{$warehouse->idwarehouse} = intval($qty->total_qty);
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
                            $product->price1 = intval($product->price) + intval($itemProduct->price)*intval($product->$item);
                        }
                    }
                }
            }
        }
 
        $datatable = Datatables::of($products);
        foreach($warehouses as $house){
            $rawColumns[] = $house->shortname;
            $datatable->addColumn( $house->shortname, function($row) use ($house){
            //    $record = $row->warehouse_quantity[$house->idwarehouse]; 
                $record = $row->{$house->idwarehouse} ;
                return '<span  class="field-value">'.$record.'</span>
                <div class="field-edit">
                    <input type="text" name="warehouse" class="form-control" value="'.$record.'" data-old="'.$record.'" data-action="update_warehouse" data-id="'.$row->productid.'" data-warehouse="'.$house->idwarehouse.'" data-field="'.$house->idwarehouse.'">
                </div>';
                
            });
        }
        $rawColumns[] = 'dateprice';
        $rawColumns[] = 'itemsinpaket1';
        $rawColumns[] = 'itemsinpaket2';
        $rawColumns[] = 'itemsinpaket3';
        $rawColumns[] = 'manufacturer_area';
        $rawColumns[] = 'codemanu';
        $rawColumns[] = 'content';
        $rawColumns[] = 'ordertime';
        $rawColumns[] = 'orderrangetime';

        $datatable->addColumn('dateprice', function($row) {
            return '<span  class="field-value">'.$row->dateprice.'</span>
                    <div class="field-edit">
                    <input type="date" name="dateprice" class="form-control" value="'.$row->dateprice.'" data-id="'.$row->productid.'" data-field="dateprice" style="line-height: 1;"></div>';
        });
        
        $datatable->addColumn('itemsinpaket1', function($row) {
            return '<span  class="field-value">'.$row->itemsinpaket1.'</span>
                    <div class="field-edit">
                    <input type="text" name="itemsinpaket1" class="form-control" value="'.$row->itemsinpaket1.'" data-id="'.$row->productid.'" data-field="itemsinpaket1"></div>';
        });

        $datatable->addColumn('itemsinpaket2', function($row) {
            return '<span  class="field-value">'.$row->itemsinpaket2.'</span>
                    <div class="field-edit">
                    <input type="text" name="itemsinpaket2" class="form-control" value="'.$row->itemsinpaket2.'" data-id="'.$row->productid.'" data-field="itemsinpaket2"></div>';
        });

        $datatable->addColumn('itemsinpaket3', function($row) {
            return '<span  class="field-value">'.$row->itemsinpaket3.'</span>
                    <div class="field-edit">
                    <input type="text" name="itemsinpaket3" class="form-control" value="'.$row->itemsinpaket3.'" data-id="'.$row->productid.'" data-field="itemsinpaket3"></div>';
        });

        $datatable->addColumn('manufacturer_area', function($row) use ($manufacturers) {
            $html = '<span class="field-value">'.$row->shortname.'</span>
                    <div class="field-edit">';
                        $html .='<select name="manufacturerid" class="form-control"  data-id="'.$row->productid.'" data-field="manufacturerid">';
                            foreach ($manufacturers as $key => $m){
                                $selected = $m->manufacturerid==$row->manufacturerid ? 'selected':'';
                                $html .= '<option value="'.$m->manufacturerid.'" '.$selected.'>';
                                $html .= $m->shortname;
                               $html .= '</option>';
                            }
                        $html .='</select>
                    </div>';
            return $html;
        });

        $datatable->addColumn('codemanu', function($row) {
            return ' <span  class="field-value">'.$row->codemanu.'</span>
                    <div class="field-edit">
                    <input type="text" name="codemanu" class="form-control" value="'.$row->codemanu.'" data-id="'.$row->productid.'" data-field="codemanu">
                </div>';
        });

        $datatable->addColumn('content', function($row) {
            return ' <span  class="field-value">'.$row->content.'</span>
                    <div class="field-edit">
                    <input type="text" name="content" class="form-control" value="'.$row->content.'" data-id="'.$row->productid.'" data-field="content">
                </div>';
        });

        $datatable->addColumn('ordertime', function($row) {
            return ' <span  class="field-value">'.$row->ordertime.'</span>
                    <div class="field-edit">
                    <input type="text" name="ordertime" class="form-control" value="'.$row->ordertime.'" data-id="'.$row->productid.'" data-field="ordertime">
                </div>';
        });

        $datatable->addColumn('orderrangetime', function($row) {
            return ' <span  class="field-value">'.$row->orderrangetime.'</span>
                    <div class="field-edit">
                    <input type="text" name="orderrangetime" class="form-control" value="'.$row->orderrangetime.'" data-id="'.$row->productid.'" data-field="orderrangetime">
                </div>';
        });


        $datatable->addColumn('total_qty', function($row) {
            return $row->total_qty;
        });
        $datatable->addColumn('total', function($row) {
            $total  = (float)($row->price)*(float)($row->total_qty);                             
            return number_format($total, 2);
        });

        $datatable->editColumn('price', function ($row) {  
            return '<span  class="field-value">'.$row->price.'</span>
            <div class="field-edit">
            <input type="text" name="price" class="form-control" value="'.$row->price.'" data-id="'.$row->productid.'" data-field="price">';
        });
        
        $datatable->addColumn('manufacturer', function($row) {
                return $row->manufacturer ? $row->manufacturer->shortname : '';
        });
        
        $datatable->addColumn('sort', function($row) {
                return $row->sort;
        });               
        $datatable->rawColumns($rawColumns);
        return $datatable->make(true);
    }

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
