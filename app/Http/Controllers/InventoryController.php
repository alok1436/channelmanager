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
use App\Models\LagerStand;
use App\Exports\InventoryExport;
class InventoryController extends Controller
{
    
    protected $total = 0;
            
    public function exportQuantity(Request $request){
        return Excel::download(new InventoryExport($request), 'inventory_'.time().'.xlsx');
    }
    
    public function getKitProducts(){
        return Product::leftjoin('manufacturer', 'product.manufacturerid', '=', 'manufacturer.manufacturerid')
                    ->where('product.virtualkit', "=", "Yes")
                    ->get();
    }
         
    
    public function ajaxInventory(){
        
      //  dd($this->getKitProducts());
        
        $products   = Product::leftjoin('manufacturer', 'product.manufacturerid', '=', 'manufacturer.manufacturerid')
                    ->where('product.virtualkit', "=", "No")
                    ->get();
        $warehouses = DB::table('warehouse')
                        ->get();

        $manufacturers = DB::table('manufacturer')
                        ->get();
        
         $kitProducts = DB::table('product')
                        ->leftjoin('manufacturer', 'product.manufacturerid', '=', 'manufacturer.manufacturerid')
                        ->where('product.virtualkit', "=", "Yes")
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

                $record = $product->lagerStand()->where('idwarehouse', $warehouse->idwarehouse)->first();

                $product->{"hall"} = $record ? $record->hall : '';
                $product->{"area"} = $record ? $record->area : '';
                $product->{"rack"} = $record ? $record->rack : '';
            }
            
            $product->total_qty = $total_qty;
        }

        foreach($kitProducts as $kitProduct) {
            $total_qty = 0;
            foreach($warehouses as $warehouse) {                
                $qty    = DB::table('lagerstand')
                        ->where('productid',    '=', $kitProduct->productid)
                        ->where('idwarehouse',  '=', $warehouse->idwarehouse)
                        ->selectRaw(DB::raw("COALESCE(sum(quantity),0) as total_qty"))
                        ->get();
                    
                $total_qty += intval($qty[0]->total_qty); 
                $kitProduct->{$warehouse->idwarehouse} = intval($qty[0]->total_qty);
            }
            
            $kitProduct->total_qty = $total_qty;
        }

        foreach($products as $product) {
            $total_qty = 0;

            if($product->virtualkit != "Yes") {
                foreach($kitProducts as $kitProduct) {
                    for($i=1; $i<10; $i++) {
                        $item = "pcs".$i;
                        $itemProductId = "productid".$i;

                        
                        if($kitProduct->$itemProductId == $product->modelcode) {
                            foreach($warehouses as $warehouse) { 
                                $product->{$warehouse->idwarehouse} = intval($product->{$warehouse->idwarehouse}) + intval($kitProduct->{$warehouse->idwarehouse})*intval($kitProduct->$item);
                                $product->total_qty = $product->total_qty+intval(intval($kitProduct->{$warehouse->idwarehouse})*intval($kitProduct->$item));
                            }
                        }
                    }
                }
            }
        }

        // foreach($products as $key=>$product){
        //     $arrr = [];
        //     foreach($warehouses as $house){
        //         $arrr[$house->idwarehouse] = 0;
        //     }
            
        //     $products[$key]->warehouse_quantity = $arrr;
        // }
                        
        $datatable = Datatables::of($products);
        $rawColumns = ['price'];
        foreach($warehouses as $house){
            $rawColumns[] = $house->shortname;
            $rawColumns[] = 'hall_'.$house->shortname;
            $rawColumns[] = 'area_'.$house->shortname;
            $rawColumns[] = 'rack_'.$house->shortname;
            $datatable->addColumn( $house->shortname, function($row) use ($house){
                $record = $row->{$house->idwarehouse} ;
                return '<span  class="field-value">'.$record.'</span>
                <div class="field-edit">
                    <input type="text" name="warehouse" class="form-control" value="'.$record.'" data-old="'.$record.'" data-action="update_warehouse" data-id="'.$row->productid.'" data-warehouse="'.$house->idwarehouse.'" data-field="'.$house->idwarehouse.'">
                </div>';
                
            });

            $datatable->addColumn( 'hall_'.$house->shortname, function($row) use ($house){
                $record = $row->hall;
                return '<span  class="field-value">'.$record.'</span>
                <div class="field-edit">
                    <input type="text" name="warehouse" class="form-control" value="'.$record.'" data-action="update_hac" data-id="'.$row->productid.'" data-old="'.$house->idwarehouse.'" data-field="hall">
                </div>';
                
            });

            $datatable->addColumn( 'area_'.$house->shortname, function($row) use ($house){
                
                $record = $row->area;
                return '<span  class="field-value">'.$record.'</span>
                <div class="field-edit">
                    <input type="text" name="warehouse" class="form-control" value="'.$record.'" data-action="update_hac" data-id="'.$row->productid.'" data-old="'.$house->idwarehouse.'" data-field="area">
                </div>';
                
            });

            $datatable->addColumn( 'rack_'.$house->shortname, function($row) use ($house){
                $record = $row->rack;
                return '<span  class="field-value">'.$record.'</span>
                <div class="field-edit">
                    <input type="text" name="warehouse" class="form-control" value="'.$record.'" data-action="update_hac" data-id="'.$row->productid.'" data-old="'.$house->idwarehouse.'" data-field="rack">
                </div>';
                
            });
        }
        $datatable->addColumn('total_qty', function($row) {
            return $row->total_qty;
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
    
    public function inventoryView(){
        if(!Session::has('userID')) {
            return redirect()->route('login');
        }
        
        // $product   = Product::where('modelcode', "=", "10190")
        //                 ->first();
        
        // $kitProducts = Product::where('product.virtualkit', "=", "Yes")->get();
        // $quantitySum = 0;  
        // foreach($kitProducts as $kitProduct) {
        //     for($i=1; $i<10; $i++) {
        //         $item = "pcs".$i;
        //         $itemProductId = "productid".$i;
        //         if($kitProduct->$itemProductId == $product->modelcode) {
        //             $kitquantity = $kitProduct->lagerStand()->sum('quantity');
        //             $quantitySum  +=  ($kitquantity * $kitProduct->$item); 
        //         }
        //     }
        // }        
         
         
        // echo $product = $quantitySum + $product->lagerStand()->sum('quantity');
        
        // for($i=1; $i<10; $i++) {
        //     $item = "pcs".$i;
        //     $itemProductId = "productid".$i;
        //     $kitProducts = Product::query()
        //         ->where($itemProductId, "=", $product->modelcode)
        //         ->first();
                
        //     if($kitProducts) {
        //         $kitquantitywarehouse = $kitProducts->lagerStand()->first();
        //         $kitquantity = $kitquantitywarehouse ? $kitquantitywarehouse->quantity : 0;
        //         $quantitySum  =  ($kitquantity * $kitProducts->$item); 
        //         echo $item.'-'.$itemProductId.'-'.$kitProducts->modelcode;
        //         echo '<br>';
        //     }
        // }

// exit();
        $products   = DB::table('product')
                        ->leftjoin('manufacturer', 'product.manufacturerid', '=', 'manufacturer.manufacturerid')
                        ->where('product.virtualkit', "=", "No")
                        ->paginate(100);

        $kitProducts = DB::table('product')
                        ->leftjoin('manufacturer', 'product.manufacturerid', '=', 'manufacturer.manufacturerid')
                        ->where('product.virtualkit', "=", "Yes")
                        ->get();

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
        }

        foreach($kitProducts as $kitProduct) {
            $total_qty = 0;
            foreach($warehouses as $warehouse) {                
                $qty    = DB::table('lagerstand')
                        ->where('productid',    '=', $kitProduct->productid)
                        ->where('idwarehouse',  '=', $warehouse->idwarehouse)
                        ->selectRaw(DB::raw("COALESCE(sum(quantity),0) as total_qty"))
                        ->get();
                    
                $total_qty += intval($qty[0]->total_qty); 
                $kitProduct->{$warehouse->idwarehouse} = intval($qty[0]->total_qty);
            }
            
            $kitProduct->total_qty = $total_qty;
        }

        foreach($products as $product) {
            $total_qty = 0;

            if($product->virtualkit != "Yes") {
                foreach($kitProducts as $kitProduct) {
                    for($i=1; $i<10; $i++) {
                        $item = "pcs".$i;
                        $itemProductId = "productid".$i;

                        
                        if($kitProduct->$itemProductId == $product->modelcode) {
                            foreach($warehouses as $warehouse) { 
                                $product->{$warehouse->idwarehouse} = intval($product->{$warehouse->idwarehouse}) + intval($kitProduct->{$warehouse->idwarehouse})*intval($kitProduct->$item);
                                $product->total_qty = $product->total_qty+intval(intval($kitProduct->{$warehouse->idwarehouse})*intval($kitProduct->$item));
                            }
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

        return View::make('inventoryView', $params);
    }
    
    public function inventoryView2(){
        if(!Session::has('userID')) {
            return redirect()->route('login');
        }

        $products   = DB::table('product')
                        ->leftjoin('manufacturer', 'product.manufacturerid', '=', 'manufacturer.manufacturerid')
                        ->where('product.virtualkit', "=", "No")
                        ->paginate(100);

        $kitProducts = DB::table('product')
                        ->leftjoin('manufacturer', 'product.manufacturerid', '=', 'manufacturer.manufacturerid')
                        ->where('product.virtualkit', "=", "Yes")
                        ->get();

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
        }

        foreach($kitProducts as $kitProduct) {
            $total_qty = 0;
            foreach($warehouses as $warehouse) {                
                $qty    = DB::table('lagerstand')
                        ->where('productid',    '=', $kitProduct->productid)
                        ->where('idwarehouse',  '=', $warehouse->idwarehouse)
                        ->selectRaw(DB::raw("COALESCE(sum(quantity),0) as total_qty"))
                        ->get();
                    
                $total_qty += intval($qty[0]->total_qty); 
                $kitProduct->{$warehouse->idwarehouse} = intval($qty[0]->total_qty);
            }
            
            $kitProduct->total_qty = $total_qty;
        }

        foreach($products as $product) {
            $total_qty = 0;

            if($product->virtualkit != "Yes") {
                foreach($kitProducts as $kitProduct) {
                    for($i=1; $i<10; $i++) {
                        $item = "pcs".$i;
                        $itemProductId = "productid".$i;

                        
                        if($kitProduct->$itemProductId == $product->modelcode) {
                            foreach($warehouses as $warehouse) { 
                                $product->{$warehouse->idwarehouse} = intval($product->{$warehouse->idwarehouse}) + intval($kitProduct->{$warehouse->idwarehouse})*intval($kitProduct->$item);
                                $product->total_qty = $product->total_qty+intval(intval($kitProduct->{$warehouse->idwarehouse})*intval($kitProduct->$item));
                            }
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

        return View::make('inventoryView2', $params);
    }

}
