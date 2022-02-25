<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use Session;
use Redirect;

class SupplyController extends Controller
{
    //
    public function manufacturerorderView() {
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

        $products       = DB::table('product')
                            ->where('active',       '=', 'yes')
                            ->where('virtualkit',   '=', 'no')
                            ->orderby(Session::get('sortItem'), Session::get('sort'))
                            ->paginate(100);
        
        $warehouses     = DB::table('warehouse')
                            ->get();

        foreach($products as $product) {
            $total_qty = 0;
            foreach($warehouses as $warehouse) {                
                $qty    = DB::table('lagerstand')
                        ->where('productid',    '=', $product->productid)
                        ->where('idwarehouse',  '=', $warehouse->idwarehouse)
                        ->selectRaw(DB::raw("COALESCE(sum(quantity),0) as total_qty"))
                        ->get();
                    
                $total_qty += $qty[0]->total_qty; 
                $product->{$warehouse->idwarehouse} = $qty[0]->total_qty;
            }
            
            $product->total_qty = $total_qty;
        }
        $params['products']     = $products;
        $params['warehouses']   = $warehouses;    
        return View::make('manufacturerorderView', $params);
    }

    public function sendManufacturerOrder(Request $request) {
        $qtys           = $request->quantity;
        $deliveryweek   = $request->deliveryweek;
        $notes          = $request->notes;
        
        foreach ($qtys as $key => $qty) {
            if ($qty != 0) {
                if(isset($deliveryweek)) {
                    $deliveryweekVal = $deliveryweek[$key];
                    $notesVal        = $notes[$key];
                } else {
                    $deliveryweekVal = "";
                    $notesVal        = "";
                }
                DB::table('newcontainer')
                    ->insert([
                        'productid'     => $key,
                        'quantity'      => $qty,
                        'deliveryweek'  => $deliveryweekVal,
                        'notes'         => $notesVal
                    ]);
            }
        }
        
        if(isset($deliveryweek)) {
            return Redirect::route('manufacturerorderView')->with(['msg' => 'Item transfered for confirmation.']);
        } else {
            return Redirect::route('calculate')->with(['msg' => 'Item transfered for confirmation.']);
        }
    }

    public function containerconfirmView() {
        if(!Session::has('userID')) {
            return redirect()->route('login');
        }
        $newContainers  = DB::table('newcontainer') 
                            ->leftjoin('product', 'product.productid', '=', 'newcontainer.productId')
                            ->get();

        $warehouses     = DB::table('warehouse')
                            ->get();

        $params['newContainers']    = $newContainers;
        $params['warehouses']       = $warehouses;
        return View::make('containerconfirmView', $params);
    }

    public function containerconfirmDelete() {
        $idnewcontainer = $_GET['del_id'];
        DB::table('newcontainer') 
            ->where('idnewcontainer', '=', $idnewcontainer)
            ->delete();

        return Redirect::route('containerconfirmView');
    }

    public function confirmNewContainer(Request $request) {
        if(!Session::has('userID')) {
            return redirect()->route('login');
        }
        $qtys   = $request['quantity'];
        $to     = $request['to']; 
        foreach ($qtys as $key => $qty) {
            $container  = DB::table('newcontainer') 
                            ->where('idnewcontainer', '=', $key)
                            ->get();
            $productid  = $container[0]->productid;
            $quantity   = (int)$qty;
            if (!empty($quantity)) {
                $checklager = DB::table('lagerstand') 
                                ->where('productid',    '=', $productid)
                                ->where('idwarehouse',  '=', $to)
                                ->get();
                
                if(count($checklager) > 0){
                    $row            = $checklager[0];
                    $idlagerstand   = $row->idlagerstand;
                    $quantity       = intval($row->quantity) + intval($quantity);
                    
                    DB::table('lagerstand')
                        ->where('idlagerstand', '=', $idlagerstand)
                        ->update([
                            'quantity'    => $quantity
                        ]);
                } else {
                    if($productid != "" && $productid != null && $productid != 0) {
                        DB::table('lagerstand')
                            ->insert([
                                'productid'   => $productid,
                                'idwarehouse' => $to,
                                'quantity'    => $quantity
                            ]);
                    }
                }

                DB::table('newcontainer')
                    ->where('idnewcontainer', '=', $key)
                    ->delete();
            }
        }
        return Redirect::route('containerconfirmView');
    }

    public function warehouseTransferFirstView() {
        if(!Session::has('userID')) {
            return redirect()->route('login');
        }
        $warehouses     = DB::table('warehouse')
                            ->get();

        $params['warehouses']       = $warehouses;
        return View::make('warehouseTransferView', $params);
    }

    public function warehouseTransferSecondView(Request $request) {
        if(!Session::has('userID')) {
            return redirect()->route('login');
        }
        $from   = $request['from'];
        $to     = $request['to'];
        $lagerstands    = DB::table('lagerstand') 
                            ->where('idwarehouse', '=', $from)
                            ->leftjoin('product', 'product.productid', '=', 'lagerstand.productId')
                            ->get();
        
        $warehouses     = DB::table('warehouse')
                            ->get();

        foreach($lagerstands as $item) {
            $productid = $item->productid;
            foreach($warehouses as $warehouse) {
                $idwarehouse = $warehouse->idwarehouse;
                $quantity = DB::table('lagerstand') 
                        ->where('idwarehouse', '=', $idwarehouse)
                        ->where('productid', '=', $productid)
                        ->first();
                if(!empty($quantity)) {
                    $item->$idwarehouse = $quantity->quantity;
                } else {
                    $item->$idwarehouse = 0;
                }
            }
        }

        $params['lagerstands']      = $lagerstands;
        $params['from']             = $from;
        $params['to']               = $to;
        $params['warehouses']       = $warehouses;
        return View::make('warehouseTransferView', $params);
    }

    public function confirmWarehouseTransfer(Request $request) {
        if(!Session::has('userID')) {
            return redirect()->route('login');
        }
        $qtys   = $request['idlagerstand'];
        $notes  = $request['notes'];
        $from   = $request['from'];
        $to     = $request['to'];
        foreach ($qtys as $key => $qty) {
            $checklager = DB::table('lagerstand') 
                            ->where('idlagerstand',  '=', $key)
                            ->get();
            
            if(count($checklager) > 0) {
                $row = $checklager[0];
                $qty = (int)$qty;
                if (!empty($qty)) {
                    $quantity = intval($row->quantity)-intval($qty);
                    DB::table('transferitems')
                        ->insert([
                            'productid'      => $row->productid,
                            'notes'          => $notes[$key],
                            'quantity'       => $qty,
                            'from'           => $from,
                            'to'             => $to,
                            'outsideconfirm' => 0,
                            'insideconfirm'  => 0
                        ]);

                    DB::table('lagerstand')
                        ->where('idlagerstand', '=', $key)
                        ->update([
                            'quantity'  => $quantity
                        ]);
                }
            }                
        }
        return Redirect::route('warehouseTransferFirstView');
    }
    
    public function warehouseconfirmView() {
        if(!Session::has('userID')) {
            return redirect()->route('login');
        }
        $transferitems  = DB::table('transferitems') 
                            ->leftjoin('product', 'product.productid', '=', 'transferitems.productId')
                            ->where('transferitems.outsideconfirm'  , '!=', 1)
                            ->where('transferitems.insideconfirm'   , '!=', 1)
                            ->get();

        foreach($transferitems as $item) {
            $from = DB::table('warehouse')
                    ->where('idwarehouse', '=', $item->from)
                    ->first();
            if(!empty($from)) {
                $item->fromWarehouse = $from->shortname;
            }

            $to = DB::table('warehouse')
                    ->where('idwarehouse', '=', $item->to)
                    ->first();
            if(!empty($to)) {
                $item->toWarehouse = $to->shortname;
            }
        }

        $params['transferitems'] = $transferitems;  
        return View::make('warehouseconfirmView', $params);                  
    }

    public function transferWarehouse(Request $request) {
        $qtys   = $request['idtransfer'];
        foreach ($qtys as $key => $qty) {
            $row = DB::table('transferitems') 
                            ->where('idtransfer',  '=', $key)
                            ->get();

            $idtransfer = $key;
            $productid  = $row[0]->productid;
            $to         = $row[0]->to;
            $quantity   = (int)$qty;

            if (!empty($quantity)) {
                $checklager = DB::table('lagerstand') 
                            ->where('productid',  '=', $productid)
                            ->where('idwarehouse',  '=', $to)
                            ->get();
                

                if(count($checklager) > 0){
                    $row            = $checklager[0];
                    $idlagerstand   = $row->idlagerstand;
                    $quantity       = intval($row->quantity) + intval($quantity);
                    DB::table('lagerstand')
                        ->where('idlagerstand', '=', $idlagerstand)
                        ->update([
                            'quantity'  => $quantity
                        ]);
                }else{
                    if($productid != "" && $productid != null && $productid != 0) {
                        DB::table('lagerstand')
                            ->insert([
                                'productid'      => $productid,
                                'idwarehouse'    => $to,
                                'quantity'       => $quantity
                            ]);
                    }
                }
                DB::table('transferitems')
                    ->where('idtransfer', '=', $idtransfer)
                    ->update([
                        'insideconfirm'  =>  1
                    ]);
            }
        }
        return Redirect::route('warehouseconfirmView');
    }

    public function warehouseconfirmDel() {
        $idtransfer = $_GET['del_id'];
        DB::table('transferitems')
                    ->where('idtransfer', '=', $idtransfer)
                    ->delete();
        return Redirect::route('warehouseconfirmView');
    }
}
