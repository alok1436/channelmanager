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
use GuzzleHttp\Client;
use DateTime;
use App\Models\Price;
use Codexshaper\WooCommerce\Facades\Product;

class PriceController extends Controller {
    
    public function deletePrice(Request $request, $id){
        if(Price::find($id)){
            Price::find($id)->delete();
            return redirect()->back()->with('msg', 'Price deleted successfully');
        }else{
            return redirect()->back()->with('error', 'Failed');
        }
    }
    
    public function index() {
        if(!Session::has('userID')) {
            return redirect()->route('login');
        }

        $query         =Price::with('product')
                        ->leftjoin('product'        , 'prices.product_id'   , '=', 'product.productid')
                        ->leftjoin('warehouse'      , 'prices.warehouse_id' , '=', 'warehouse.idwarehouse')
                        ->leftjoin('platform'       , 'prices.platform_id'  , '=', 'platform.platformid')
                        ->leftjoin('channel'        , 'prices.channel_id'   , '=', 'channel.idchannel')
                        ->leftjoin('tbl_fba', function($join) {
                            $join->on('prices.sku'          , '=', 'tbl_fba.sku');
                            $join->on('prices.channel_id'   , '=', 'tbl_fba.channel');
                        })
                        ->leftjoin('modelproducts'  , 'prices.product_id'   , '=', 'modelproducts.idproduct')
                        ->select('prices.*', 'modelproducts.*', 'tbl_fba.idfba', 'product.min_sell', 'product.virtualkit', 'product.price as productPrice', 'product.nameshort', 'product.modelcode AS productModelcode', 'product.shipp_model_id', 'product.fees_model_id', 'product.vat_model_id', 'channel.shortname AS channelShortname', 'channel.quantity_strategy', 'channel.vatType', 'channel.vat as channelVat', 'channel.country AS channelCountry', 'warehouse.location AS warehouseLocation', 'platform.shortname AS platformShortname')
                        ->orderby('prices.product_id', 'asc');

        if(isset($_GET['keyword']) && $_GET['keyword'] != "") {
            $keyword = $_GET['keyword'];
            $query->where("product.modelcode",          "like", '%'.$keyword.'%')
                    ->orwhere("prices.sku",             "like", '%'.$keyword.'%')
                    ->orwhere("prices.ean",             "like", '%'.$keyword.'%')
                    ->orwhere("prices.asin",            "like", '%'.$keyword.'%')
                    ->orwhere("product.nameshort",      "like", '%'.$keyword.'%')
                    ->orwhere("product.namelong",       "like", '%'.$keyword.'%');
        }
        
        $prices     = $query->paginate(1);
 
        $none_products  = DB::table("tbl_none_product")
                        ->leftjoin("product", 'product.modelcode'   , '=', 'tbl_none_product.related_modelcode')
                        ->where('tbl_none_product.related_modelcode', "!=", "")
                        ->select("tbl_none_product.*")
                        ->get();

        $modelshipname  = DB::table("modelshipname")
                        ->get();

        $modelfeesname  = DB::table("modelfeesname")
                        ->get();

        $modelvatname   = DB::table("modelvatname")
                        ->get();

        $currency       = DB::table("currency")
                        ->get();

        $warehouses     = DB::table("warehouse")
                        ->get();

        $channels       = DB::table("channel")
                        ->get();

        $platformsShort = DB::table('platform')
                        ->leftjoin('channel', 'channel.platformid', '=', 'platform.platformid')
                        ->select('channel.idchannel as channelId', 'channel.platformid', 'channel.shortname as channelname', 'platform.shortname as platformname')
                        ->get();
        
        foreach($prices as $row) {
            $row->indicated_quantity = 0;
            $row->warehouse_quantity = 0;
            $row->can_sell_online = 0;
            $fbaFlag = 0;
            if($row->idfba != null && $row->idfba != "") {
                $fbaFlag = 1;
            }

            $row->fbaFlag   = $fbaFlag;

            $warehouse      = $row->warehouse_id;

            if($row->relation2 != null && $row->relation2 != "") {
                $warehouse = $row->relation2;
            }
            $productId = $row->product_id;
            $warehouseQnt = DB::table('lagerstand')
                            ->where('productid', '=', $productId)
                            ->where('idwarehouse', '=', $warehouse)
                            ->first();

            if(empty($warehouseQnt)) {
                $row->warehouseQnt = 0;
            } else {
                $row->warehouseQnt = $warehouseQnt->quantity;
            }

            $modelshipping = DB::table('modelshipping')
                                ->leftjoin('warehouse'  , 'modelshipping.warehouseId'   , '=', 'warehouse.idwarehouse')
                                ->leftjoin('country'    , 'modelshipping.countryId'     , '=', 'country.countryid')
                                ->where('modelshipping.idmodelshipname' , '=', $row->shipp_model_id)
                                ->where('country.shortname'             , '=', $row->country)
                                ->where('warehouse.idwarehouse'         , '=', $row->warehouse_id)
                                ->where('modelshipping.fba'             , '=', $fbaFlag)
                                ->first();

            if(!empty($modelshipping)) {
                $row->valueship = $modelshipping->valueship;
            }

            $modelvat = DB::table('modelvat')
                            ->leftjoin('country', 'modelvat.countryid', '=', 'country.countryid')
                            ->where('modelvat.idmodelvatname', '=', $row->vat_model_id)
                            ->where('country.shortname', '=', $row->country)
                            ->first();

            if(!empty($modelvat)) {
                $row->valuevat = $modelvat->valuevat;
            }

            if($row->vatType == 1) {
                $row->valuevat = $row->channelVat;
            }

            $modelfees = DB::table('modelfees')
                            ->where('idmodelfeesname', '=', $row->fees_model_id)
                            ->where('channelid', '=', $row->channel_id)
                            ->first();

            if(!empty($modelfees)) {
                $row->valuefees = $modelfees->valuefees;
            }

            if(isset($row->valuefees) && isset($row->valueship) && isset($row->valuevat)) {
                $valuefees          = $row->valuefees;
                $valueship          = $row->valueship;
                $valuevat           = $row->valuevat;
                $onlinePrice        = $row->online_price;
                $ship               = $row->shipping;
                $gain               = (($row->price+$ship)/(1+$valuevat/100))-($row->price+$ship)*$valuefees/100-$row->productPrice-$valueship;
                if($row->productPrice == 0 || $row->productPrice == null) {
                    $gain_percentage    = 0;
                } else {
                    $gain_percentage    = $gain/$row->productPrice*100;
                }
    
                if($row->gain != $gain) {
                    DB::table('prices')
                        ->where('price_id', '=', $row->price_id)
                        ->update([
                            'gain'            => $gain,
                            'gain_percentage' => $gain_percentage
                        ]);
                }
            }
            
            if(!empty($row->product) && $row->product->min_sell != null) {
                $row->indicated_quantity = $row->product->min_sell;
            }
           
            if($row->quantity_strategy == 1) {
                
                $row->can_sell_online = $row->indicated_quantity;
                if(!empty($warehouseQnt)) {
                    if($warehouseQnt->quantity >= $row->indicated_quantity){
                        $row->can_sell_online = $row->indicated_quantity;
                    }else{
                        $row->can_sell_online = $warehouseQnt->quantity;
                    }
                }
                
                
            }else if($row->quantity_strategy == 3) {
                if(!empty($warehouseQnt)) {
                    $row->can_sell_online = $warehouseQnt->quantity;
                }
            }
            
                
            // if($row->quantity_strategy == 1) {
            //     $row->quantity_strategy = 0;
                
            //     $buffer = DB::table('product')
            //                 ->where('productid', '=', $productId)
            //                 ->first();

            //     if(!empty($buffer) && $buffer->min_sell != null) {
            //         $row->quantity_strategy = $buffer->min_sell;
            //     }
                
            //     if(!empty($warehouseQnt)) {
            //         if(!empty($min_sell) && $buffer->min_sell == null) {
            //             $row->quantity_strategy = $warehouseQnt->quantity;
            //         } else {
            //             if($row->quantity_strategy > $warehouseQnt->quantity) {
            //                 $row->quantity_strategy = $warehouseQnt->quantity;
            //             }
            //         }
            //     }
            // } else if($row->quantity_strategy == 2) {

            // } else if($row->quantity_strategy == 3) {
            //     if(!empty($warehouseQnt)) {
            //         $row->quantity_strategy = $warehouseQnt->quantity;
            //     }
            // } 

            ///////////////////////////////////////////
            if($row->virtualkit == "Yes") {
                $product = DB::table("product")
                            ->where("productid", "=", $productId)
                            ->first();
                            
                for($i=1; $i<10; $i++) {
                    $item = "pcs".$i;
                    $itemProductId = "productid".$i;
                    $productmodelcode = $product->$itemProductId;
                    if($product->$item != null && $product->$item > 0 && $product->$item != "" && $productmodelcode != "" && $productmodelcode != null) {
                        $itemProduct = DB::table('product')
                                        ->where('product.modelcode',  '=', $productmodelcode)
                                        ->first();
                        
                        if(!empty($itemProduct)) {
                            $row->productPrice = floatval($row->productPrice) + floatval($itemProduct->price)*floatval($product->$item);
                        }
                    }
                }
            }
        }

        $params['channels']         = $channels;
        $params['prices']           = $prices;
        $params['modelshipname']    = $modelshipname;
        $params['warehouses']       = $warehouses;
        $params['modelfeesname']    = $modelfeesname;
        $params['modelvatname']     = $modelvatname;
        $params['currency']         = $currency;
        $params['platformsShort']   = $platformsShort;
        $params['none_products']    = $none_products;
        
        return View::make('priceView', $params);
    }

    public function get_shipping_model_data() {
        $prod_id            = $_GET['prod_id'];
        $shipp_model_id     = $_GET['shipp_model_id'];

        $rs = DB::table('modelshipping')
                ->leftjoin('warehouse'          , 'modelshipping.warehouseId'           , '=', 'warehouse.idwarehouse')
                ->leftjoin('country'            , 'modelshipping.countryId'             , '=', 'country.countryid')
                ->select('modelshipping.*'      , 'country.shortname AS countryName'    , 'warehouse.idwarehouse AS warehouseId')
                ->where('modelshipping.idmodelshipname'                                 , '=', $shipp_model_id)
                ->get();

        if(count($rs)>0){
            $modelData = array();
            foreach($rs as $rw) {
                $modelData[] = $rw->warehouseId.'@@'.$rw->countryName.'@@'.$rw->valueship.'@@'.$rw->fba;
            }

            DB::table('product')
                ->where('productid', '=', $prod_id)
                ->update([
                   'shipp_model_id' => $shipp_model_id
                ]);

            $response["msg"]        = "Success.";
            $response["status"]     = 1;
            $response["prod_id"]    = $prod_id;
            $response["data"]       = $modelData;
            
        }else{
            $response["msg"]        = "Data not found in this model.";
            $response["status"]     = 0;
            $response["data"]       = "";
        }

        echo json_encode($response);
    }

    public function get_fees_model_data() {
        $prod_id            = $_GET['prod_id'];
        $fees_model_id      = $_GET['fees_model_id'];

        $rs = DB::table('modelfees')
                ->where('idmodelfeesname', '=', $fees_model_id)
                ->get();

        if(count($rs)>0){
            $modelData = array();
            foreach($rs as $rw) {
                $modelData[] = $rw->channelid.'@@'.$rw->valuefees;
            }

            DB::table('product')
                ->where('productid', '=', $prod_id)
                ->update([
                   'fees_model_id' => $fees_model_id
                ]);

            $prices = DB::table('prices')
                        ->where('product_id', '=', $prod_id)
                        ->get();

            $response["msg"]        = "Success.";
            $response["status"]     = 1;
            $response["prod_id"]    = $prod_id;
            $response["data"]       = $modelData;
            
        }else{
            $response["msg"]        = "Data not found in this model.";
            $response["status"]     = 0;
            $response["data"]       = "";
        }

        echo json_encode($response);
    }

    public function get_vat_model_data() {
        $prod_id            = $_GET['prod_id'];
        $vat_model_id       = $_GET['vat_model_id'];

        $rs = DB::table('modelvat')
                ->leftjoin('country', 'modelvat.countryid', '=', 'country.countryid')
                ->select('modelvat.*', 'country.shortname')
                ->where('idmodelvatname', '=', $vat_model_id)
                ->get();

        if(count($rs)>0){
            $modelData = array();
            foreach($rs as $rw) {
                $modelData[] = $rw->shortname.'@@'.$rw->valuevat;
            }

            DB::table('product')
                ->where('productid', '=', $prod_id)
                ->update([
                   'vat_model_id' => $vat_model_id
                ]);

            $response["msg"]        = "Success.";
            $response["status"]     = 1;
            $response["prod_id"]    = $prod_id;
            $response["data"]       = $modelData;
            
        }else{
            $response["msg"]        = "Data not found in this model.";
            $response["status"]     = 0;
            $response["data"]       = "";
        }

        echo json_encode($response);
    }
    
    public function get_auto_price() {
        $prod_id            = $_GET['prod_id'];
        $percent            = $_GET['percent'];
        $shipp_model_id     = $_GET['shipping_model'];
        $fees_model_id      = $_GET['fees_model'];
        $vat_model_id       = $_GET['vat_model'];

        $modelProduct = DB::table('modelproducts')
                        ->where('idproduct', '=', $prod_id)
                        ->first();

        if(!empty($modelProduct)) {
            DB::table('modelproducts')
                ->where('idproduct', '=', $prod_id)
                ->update([
                    'pricevar' => 'auto',
                    'percent'  => $percent
                ]);
        } else {
            DB::table('modelproducts')
                ->insert([
                    'idproduct' => $prod_id,
                    'pricevar' => 'auto',
                    'percent'  => $percent
                ]);
        }
        
        $prices = DB::table("prices")
                ->where('prices.product_id', '=', $prod_id)
                ->leftjoin('product'        , 'prices.product_id'   , '=', 'product.productid')
                ->leftjoin('channel'        , 'prices.channel_id'   , '=', 'channel.idchannel')
                ->leftjoin('tbl_fba', function($join) {
                    $join->on('prices.sku'          , '=', 'tbl_fba.sku');
                    $join->on('prices.channel_id'   , '=', 'tbl_fba.channel');
                })
                ->select('prices.*', 'tbl_fba.idfba', 'product.price as productPrice', 'product.virtualkit', 'channel.country as channelCountry', 'channel.vatType', 'channel.vat as channelVat')
                ->get();

        foreach($prices as $row) {
            if($row->relation != 0) {
                $fee = DB::table('modelfees') 
                        ->where('channelid', '=', $row->channel_id)
                        ->first();

                if(!empty($fee)) {
                    $valuefees      = $fee->valuefees;
                } else {
                    $valuefees      = 0;
                }
                
                $fbaFlag = 0;
                if($row->idfba != null && $row->idfba != "") {
                    $fbaFlag = 1;
                }
                
                $ship = DB::table('modelshipping') 
                        ->leftjoin('country', 'country.countryid', '=', 'modelshipping.countryId')
                        ->where('country.shortname'             , '=', $row->country)
                        ->where('modelshipping.warehouseId'     , '=', $row->warehouse_id)
                        ->where('modelshipping.fba'             , '=', $fbaFlag)
                        ->first();

                if(!empty($ship)) {
                    $valueship      = $ship->valueship;
                } else {
                    $valueship      = 0;
                }
                
                $vat = DB::table('modelvat') 
                        ->leftjoin('country', 'country.countryid', '=', 'modelvat.countryid')
                        ->where('country.shortname', '=', $row->country)
                        ->first();

                if(!empty($vat)) {
                    $valuevat      = $vat->valuevat;
                } else {
                    $valuevat      = 0;
                }

                if($row->vatType == 1) {
                    $valuevat = $row->channelVat;
                }

                if($row->virtualkit == "Yes") {
                    $product = DB::table("product")
                                ->where("productid", "=", $prod_id)
                                ->first();
                                
                    for($i=1; $i<10; $i++) {
                        $item = "pcs".$i;
                        $itemProductId = "productid".$i;
                        $productmodelcode = $product->$itemProductId;
                        if($product->$item != null && $product->$item > 0 && $product->$item != "" && $productmodelcode != "" && $productmodelcode != null) {
                            $itemProduct = DB::table('product')
                                            ->where('product.modelcode',  '=', $productmodelcode)
                                            ->first();
                            
                            if(!empty($itemProduct)) {
                                $row->productPrice = floatval($row->productPrice) + floatval($itemProduct->price)*floatval($product->$item);
                            }
                        }
                    }
                }

                $onlinePrice    = $row->online_price;
                $ship           = $row->shipping;
                $price          = ((1+$percent/100)*$row->productPrice*(1+$valuevat/100)+$ship*$valuefees/100*(1+$valuevat/100)+$valueship*(1+$valuevat/100)-$ship)/(1-$valuefees/100*(1+$valuevat/100));
                $gain           = (($price+$ship)/(1+$valuevat/100))-($price+$ship)*$valuefees/100-$row->productPrice-$valueship;
                //echo $row->cost."--------".$percent."--------".$valuevat."--------".$valuefees."--------".$valueship."--------".$ship;
                if($row->productPrice != null && $row->productPrice != 0) {
                    $gain_percentage = 100*$gain/$row->productPrice;
                } else {
                    $gain_percentage = 100;
                }

                if($row->channelCountry == "UK") {
                    $gbp = DB::table("currency")
                            ->where("shortname", "=", "GBP")
                            ->first();

                    $rate = $gbp->rate;

                    $price = round($price - (floatval($rate) - 1)*$price, 2);
                }

                DB::table('prices')
                    ->where('price_id', '=', $row->price_id)
                    ->update([
                        'price'           => $price,
                        'gain'            => $gain,
                        'gain_percentage' => $gain_percentage
                    ]);
    
                $row->price             = $price;
                $row->gain              = $gain;
                $row->gain_percentage   = $gain_percentage;
            }
        }

        return json_encode($prices);
    }

    public function priceUpdate() {
        $price_id       = $_GET["id"];
        $fieldName      = $_GET["field"];
        $fieldValue     = $_GET["value"];

        if($fieldName == "price") {
            $product_id     = $_GET["product"];
            $pricemodel = DB::table('modelproducts')
                        ->where('idproduct', '=', $product_id)
                        ->first();
            if($pricemodel->pricevar == "manual") {
                DB::table('prices')
                    ->where('price_id', '=', $price_id)
                    ->update([
                        $fieldName => $fieldValue
                    ]);
    
                $price = DB::table('prices')
                    ->where('price_id', '=', $price_id)
                    ->first();
                
                $price->changeSuccess = 1;
                echo json_encode($price);
            } else {
                $price = DB::table('prices')
                    ->where('price_id', '=', $price_id)
                    ->first();

                $price->changeSuccess = 0;
                echo json_encode($price);
            }
        } else {
            DB::table('prices')
                ->where('price_id', '=', $price_id)
                ->update([
                    $fieldName => $fieldValue
                ]);

            echo 'success';
        }
    }

    public function get_manual_price() {
        $prod_id    = $_GET['prod_id'];

        $modelProduct = DB::table('modelproducts')
                        ->where('idproduct', '=', $prod_id)
                        ->first();

        if(!empty($modelProduct)) {
            DB::table('modelproducts')
                ->where('idproduct', '=', $prod_id)
                ->update([
                    'pricevar' => 'manual'
                ]);
        } else {
            DB::table('modelproducts')
                ->insert([
                    'idproduct' => $prod_id,
                    'pricevar' => 'manual'
                ]);
        }
        
        return 'success';
    }

    public function importPriceFile(Request $request) {
        if($request->hasFile('uploadfilename')) {
            $files      = $request->file('uploadfilename');
            $checks     = $request->check;
            $dateTime   = date('Y-m-d H:i:s');
            $fileArrKey = array_keys($files);
            $nonExistingProducts = [];
            for($i = 0; $i < count($fileArrKey); $i++) {
                $path            = $files[$fileArrKey[$i]]->getRealPath();
                $check_idexplode = explode("-", $checks[$i]);
                $platformId      = $check_idexplode[0];
                $channelId       = $check_idexplode[1];

                $channel   = DB::table('channel')
                            ->where('channel.idchannel', '=', $channelId)
                            ->leftjoin('coding', 'channel.codingId', '=', 'coding.codingId')
                            ->first();
                $coding    = $channel->coding;

                if($coding == "Amazon-01") {
                    $file       = file_get_contents($path);
                    $file       = explode("\n", $file);  
                    $totalrows  = count($file);
                    
                    for($loopfile=1; $loopfile < $totalrows; $loopfile++) {
                        $row        = $file[$loopfile];
                        $row        = explode("\t",$row);
                        $product    = DB::table('product')
                                        ->where('sku', '=', $row[0])
                                        ->first();
    
                        if(count($row) >3) {
                            if(!empty($product)) {
                                $existingPrice = DB::table('prices')
                                                ->where('product_id'      , '=', $product->productid)
                                                ->where('channel_id'     , '=', $channelId)
                                                ->first();

                                if(!empty($existingPrice)) {
                                    DB::table('prices')
                                        ->where('product_id'    , '=', $product->productid)
                                        ->where('channel_id'    , '=', $channelId)
                                        ->update([
                                            'product_id'        => $product->productid,
                                            'channel_id'        => $channelId,
                                            'warehouse_id'      => $channel->warehouse,
                                            'platform_id'       => $platformId,
                                            'online_price'      => $row[2],
                                            'online_quentity'   => $row[3],
                                            'price'             => $row[2],
                                            'sku'               => $row[0],
                                            'ean'               => $product->ean,
                                            'asin'              => $row[1],
                                            'cost'              => $product->price,
                                            'updated_date'      => $dateTime,
                                            'last_update_date'  => $dateTime
                                        ]);
                                } else {
                                    DB::table('prices')
                                        ->insert([
                                            'product_id'        => $product->productid,
                                            'channel_id'        => $channelId,
                                            'warehouse_id'      => $channel->warehouse,
                                            'platform_id'       => $platformId,
                                            'online_price'      => $row[2],
                                            'online_quentity'   => $row[3],
                                            'price'             => $row[2],
                                            'sku'               => $row[0],
                                            'ean'               => $product->ean,
                                            'asin'              => $row[1],
                                            'cost'              => $product->price,
                                            'updated_date'      => $dateTime,
                                            'created_date'      => $dateTime,
                                            'last_update_date'  => $dateTime
                                        ]);

                                    $modelProduct = DB::table('modelproducts')
                                                    ->where('idproduct', '=', $product->productid)
                                                    ->first();
                                    if(empty($modelProduct)) {
                                        DB::table('modelproducts')
                                            ->insert([
                                                'idproduct' => $product->productid
                                            ]);
                                    }
                                }
                            } else {
                                array_push($nonExistingProducts, $row[0]);
                            }
                        }
                    }
                } else if($coding == "Cdiscount-01") {
                    config(['excel.import.startRow' => 4]);
                    $data = Excel::load($path, function ($reader) {
                        $reader->limitRows(20);
                        $reader->ignoreEmpty();
                    })->get()->toArray();
                    
                    $data = array_filter($data);
                    foreach($data[0] as $row) {
                        $ean        = $row['ean'];
                        $product    = DB::table('product')
                                    ->where('ean', '=', $ean)
                                    ->first();
                        if(!empty($product)) {
                            $existingPrice = DB::table('prices')
                                            ->where('product_id'      , '=', $product->productid)
                                            ->where('channel_id'     , '=', $channelId)
                                            ->first();

                            if(!empty($existingPrice)) {
                                DB::table('prices')
                                    ->where('product_id'    , '=', $product->productid)
                                    ->where('channel_id'    , '=', $channelId)
                                    ->update([
                                        'product_id'        => $product->productid,
                                        'channel_id'        => $channelId,
                                        'warehouse_id'      => $channel->warehouse,
                                        'platform_id'       => $platformId,
                                        'online_price'      => $row['prix_ttc'],
                                        'online_quentity'   => $row['stock'],
                                        'price'             => $row['prix_ttc'],
                                        'sku'               => $product->sku,
                                        'ean'               => $product->ean,
                                        'asin'              => $product->asin,
                                        'cost'              => $product->price,
                                        'updated_date'      => $dateTime,
                                        'last_update_date'  => $dateTime
                                    ]);
                            } else {
                                DB::table('prices')
                                    ->insert([
                                        'product_id'        => $product->productid,
                                        'channel_id'        => $channelId,
                                        'warehouse_id'      => $channel->warehouse,
                                        'platform_id'       => $platformId,
                                        'online_price'      => $row['prix_ttc'],
                                        'online_quentity'   => $row['stock'],
                                        'price'             => $row['prix_ttc'],
                                        'sku'               => $product->sku,
                                        'ean'               => $product->ean,
                                        'asin'              => $product->asin,
                                        'cost'              => $product->price,
                                        'updated_date'      => $dateTime,
                                        'created_date'      => $dateTime,
                                        'last_update_date'  => $dateTime
                                    ]);

                                $modelProduct = DB::table('modelproducts')
                                            ->where('idproduct', '=', $product->productid)
                                            ->first();
                                if(empty($modelProduct)) {
                                    DB::table('modelproducts')
                                        ->insert([
                                            'idproduct' => $product->productid
                                        ]);
                                }
                            }
                        }
                    }
                } else if($coding == "Woocommerce-01") {
                    $file       = file_get_contents($path);
					$file       = explode("\n", $file);
                    $totalrows  = count($file);

                    for($loopfile=1; $loopfile < $totalrows; $loopfile++) {
                        $row    =str_getcsv($file[$loopfile], ",");
                        $sku    = "";
                        $ean    = "";
                        if(isset($row[1])) {
                            $product    = DB::table('product')
                                        ->where('sku', '=', $row[10])
                                        ->first();
                                        
                            $ean = $row[1];
                            if(count($row) > 8 && !empty($product)) {
                                $sku = $product->sku;
                                $existingPrice = DB::table('prices')
                                                ->where('product_id'      , '=', $product->productid)
                                                ->where('channel_id'     , '=', $channelId)
                                                ->first();
    
                                if(!empty($existingPrice)) {
                                    DB::table('prices')
                                        ->where('product_id'    , '=', $product->productid)
                                        ->where('channel_id'    , '=', $channelId)
                                        ->update([
                                            'product_id'        => $product->productid,
                                            'channel_id'        => $channelId,
                                            'warehouse_id'      => $channel->warehouse,
                                            'platform_id'       => $platformId,
                                            'online_price'      => str_replace(',', '.', $row[5]),
                                            'online_quentity'   => $row[7],
                                            'price'             => str_replace(',', '.', $row[5]),
                                            'sku'               => $sku,
                                            'ean'               => $ean,
                                            'asin'              => $product->asin,
                                            'cost'              => $product->price,
                                            'updated_date'      => $dateTime,
                                            'last_update_date'  => $dateTime
                                        ]);
                                } else {
                                    DB::table('prices')
                                        ->insert([
                                            'product_id'        => $product->productid,
                                            'channel_id'        => $channelId,
                                            'warehouse_id'      => $channel->warehouse,
                                            'platform_id'       => $platformId,
                                            'online_price'      => str_replace(',', '.', $row[5]),
                                            'online_quentity'   => $row[7],
                                            'price'             => str_replace(',', '.', $row[5]),
                                            'sku'               => $sku,
                                            'ean'               => $ean,
                                            'asin'              => $product->asin,
                                            'cost'              => $product->price,
                                            'updated_date'      => $dateTime,
                                            'created_date'      => $dateTime,
                                            'last_update_date'  => $dateTime
                                        ]);

                                    $modelProduct = DB::table('modelproducts')
                                                ->where('idproduct', '=', $product->productid)
                                                ->first();
                                    if(empty($modelProduct)) {
                                        DB::table('modelproducts')
                                            ->insert([
                                                'idproduct' => $product->productid
                                            ]);
                                    }
                                }
                            } else {
                                array_push($nonExistingProducts, $row[0]);
                            }
                        }
                    }
                } else if($coding == "EbayUK-01") {
                    $file       = file_get_contents($path);
					$file       = explode("\n", $file);
                    $totalrows  = count($file);

                    for($loopfile=1; $loopfile < $totalrows; $loopfile++) {
                        $row    = str_getcsv($file[$loopfile], ",");
                        $sku    = "";
                        $ean    = "";
                        if(isset($row[10])) {
                            $ean = $row[1];
                            $product    = DB::table('product')
                                        ->where('ean', '=', $ean)
                                        ->first();
                            
                            if(count($row) > 8 && !empty($product)) {
                                $sku = $product->sku;
                                $existingPrice = DB::table('prices')
                                                ->where('product_id'      , '=', $product->productid)
                                                ->where('channel_id'     , '=', $channelId)
                                                ->first();
    
                                if(!empty($existingPrice)) {
                                    DB::table('prices')
                                        ->where('product_id'    , '=', $product->productid)
                                        ->where('channel_id'    , '=', $channelId)
                                        ->update([
                                            'product_id'        => $product->productid,
                                            'channel_id'        => $channelId,
                                            'warehouse_id'      => $channel->warehouse,
                                            'platform_id'       => $platformId,
                                            'online_price'      => str_replace(',', '.', $row[5]),
                                            'online_quentity'   => $row[7],
                                            'price'             => str_replace(',', '.', $row[5]),
                                            'sku'               => $sku,
                                            'ean'               => $ean,
                                            'asin'              => $product->asin,
                                            'cost'              => $product->price,
                                            'updated_date'      => $dateTime,
                                            'last_update_date'  => $dateTime
                                        ]);
                                } else {
                                    DB::table('prices')
                                        ->insert([
                                            'product_id'        => $product->productid,
                                            'channel_id'        => $channelId,
                                            'warehouse_id'      => $channel->warehouse,
                                            'platform_id'       => $platformId,
                                            'online_price'      => str_replace(',', '.', $row[5]),
                                            'online_quentity'   => $row[7],
                                            'price'             => str_replace(',', '.', $row[5]),
                                            'sku'               => $sku,
                                            'ean'               => $ean,
                                            'asin'              => $product->asin,
                                            'cost'              => $product->price,
                                            'updated_date'      => $dateTime,
                                            'created_date'      => $dateTime,
                                            'last_update_date'  => $dateTime
                                        ]);

                                    $modelProduct = DB::table('modelproducts')
                                        ->where('idproduct', '=', $product->productid)
                                        ->first();
                                    if(empty($modelProduct)) {
                                        DB::table('modelproducts')
                                            ->insert([
                                                'idproduct' => $product->productid
                                            ]);
                                    }
                                }
                            } else {
                                array_push($nonExistingProducts, $row[0]);
                            }
                        }
                    }
                } else if($coding == "EbayIT-01") {
                    $file       = file_get_contents($path);
					$file       = explode("\n", $file);
                    $totalrows  = count($file);

                    for($loopfile=1; $loopfile < $totalrows; $loopfile++) {
                        $row    =str_getcsv($file[$loopfile], ";");
                        $sku    = "";
                        $ean    = "";

                        if(isset($row[10])) {
                            $product    = DB::table('product')
                                        ->where('sku', '=', $row[10])
                                        ->first();
                                        
                            $ean = $row[1];
                            if(count($row) > 8 && !empty($product)) {
                                $sku = $product->sku;
                                $existingPrice = DB::table('prices')
                                                ->where('product_id'      , '=', $product->productid)
                                                ->where('channel_id'     , '=', $channelId)
                                                ->first();
    
                                if(!empty($existingPrice)) {
                                    DB::table('prices')
                                        ->where('product_id'    , '=', $product->productid)
                                        ->where('channel_id'    , '=', $channelId)
                                        ->update([
                                            'product_id'        => $product->productid,
                                            'channel_id'        => $channelId,
                                            'warehouse_id'      => $channel->warehouse,
                                            'platform_id'       => $platformId,
                                            'online_price'      => str_replace(',', '.', $row[5]),
                                            'online_quentity'   => $row[7],
                                            'price'             => str_replace(',', '.', $row[5]),
                                            'sku'               => $sku,
                                            'ean'               => $ean,
                                            'asin'              => $product->asin,
                                            'cost'              => $product->price,
                                            'updated_date'      => $dateTime,
                                            'last_update_date'  => $dateTime
                                        ]);
                                } else {
                                    DB::table('prices')
                                        ->insert([
                                            'product_id'        => $product->productid,
                                            'channel_id'        => $channelId,
                                            'warehouse_id'      => $channel->warehouse,
                                            'platform_id'       => $platformId,
                                            'online_price'      => str_replace(',', '.', $row[5]),
                                            'online_quentity'   => $row[7],
                                            'price'             => str_replace(',', '.', $row[5]),
                                            'sku'               => $sku,
                                            'ean'               => $ean,
                                            'asin'              => $product->asin,
                                            'cost'              => $product->price,
                                            'updated_date'      => $dateTime,
                                            'created_date'      => $dateTime,
                                            'last_update_date'  => $dateTime
                                        ]);

                                    $modelProduct = DB::table('modelproducts')
                                        ->where('idproduct', '=', $product->productid)
                                        ->first();
                                    if(empty($modelProduct)) {
                                        DB::table('modelproducts')
                                            ->insert([
                                                'idproduct' => $product->productid
                                            ]);
                                    }
                                }
                            } else {
                                array_push($nonExistingProducts, $row[0]);
                            }
                        }
                    }
                } else if($coding == "EbayDE-01") {
                    $file       = file_get_contents($path);
					$file       = explode("\n", $file);
                    $totalrows  = count($file);

                    for($loopfile=1; $loopfile < $totalrows; $loopfile++) {
                        $row    =str_getcsv($file[$loopfile], ";");
                        $sku    = "";
                        $ean    = "";
                        
                        if(isset($row[10])) {
                            $product    = DB::table('product')
                                        ->where('sku', '=', $row[10])
                                        ->first();
                                        
                            $ean = $row[1];
                            if(count($row) > 8 && !empty($product)) {
                                $sku = $product->sku;
                                $existingPrice = DB::table('prices')
                                                ->where('product_id'      , '=', $product->productid)
                                                ->where('channel_id'     , '=', $channelId)
                                                ->first();
    
                                if(!empty($existingPrice)) {
                                    DB::table('prices')
                                        ->where('product_id'    , '=', $product->productid)
                                        ->where('channel_id'    , '=', $channelId)
                                        ->update([
                                            'product_id'        => $product->productid,
                                            'channel_id'        => $channelId,
                                            'warehouse_id'      => $channel->warehouse,
                                            'platform_id'       => $platformId,
                                            'online_price'      => str_replace(',', '.', $row[5]),
                                            'online_quentity'   => $row[7],
                                            'price'             => str_replace(',', '.', $row[5]),
                                            'sku'               => $sku,
                                            'ean'               => $ean,
                                            'asin'              => $product->asin,
                                            'cost'              => $product->price,
                                            'updated_date'      => $dateTime,
                                            'last_update_date'  => $dateTime
                                        ]);
                                } else {
                                    DB::table('prices')
                                        ->insert([
                                            'product_id'        => $product->productid,
                                            'channel_id'        => $channelId,
                                            'warehouse_id'      => $channel->warehouse,
                                            'platform_id'       => $platformId,
                                            'online_price'      => str_replace(',', '.', $row[5]),
                                            'online_quentity'   => $row[7],
                                            'price'             => str_replace(',', '.', $row[5]),
                                            'sku'               => $sku,
                                            'ean'               => $ean,
                                            'asin'              => $product->asin,
                                            'cost'              => $product->price,
                                            'updated_date'      => $dateTime,
                                            'created_date'      => $dateTime,
                                            'last_update_date'  => $dateTime
                                        ]);

                                    $modelProduct = DB::table('modelproducts')
                                                ->where('idproduct', '=', $product->productid)
                                                ->first();
                                    if(empty($modelProduct)) {
                                        DB::table('modelproducts')
                                            ->insert([
                                                'idproduct' => $product->productid
                                            ]);
                                    }
                                }
                            } else {
                                array_push($nonExistingProducts, $row[0]);
                            }
                        }
                    }
                } else {
                    $file   = file_get_contents($path);
					$file   = explode("\n", $file);
                    $totalrows  = count($file);
                    for($loopfile=1; $loopfile < $totalrows; $loopfile++) {
                        $row    =str_getcsv($file[$loopfile], ";");
                        $sku = "";
                        $ean = "";
                        if(isset($row[10])) {
                            $product    = DB::table('product')
                                        ->where('sku', '=', $row[10])
                                        ->first();

                            $sku = $row[10];
                        } else if(isset($row[1])) {
                            $product    = DB::table('product')
                                        ->where('ean', '=', $row[1])
                                        ->first();

                            $ean = $row[1];
                        }
                        
                        if(count($row) > 8 && !empty($product)) {
                            $existingPrice = DB::table('prices')
                                            ->where('product_id'      , '=', $product->productid)
                                            ->where('channel_id'     , '=', $channelId)
                                            ->first();

                            if(!empty($existingPrice)) {
                                DB::table('prices')
                                    ->where('product_id'    , '=', $product->productid)
                                    ->where('channel_id'    , '=', $channelId)
                                    ->update([
                                        'product_id'        => $product->productid,
                                        'channel_id'        => $channelId,
                                        'warehouse_id'      => $channel->warehouse,
                                        'platform_id'       => $platformId,
                                        'online_price'      => str_replace(',', '.', $row[5]),
                                        'online_quentity'   => $row[7],
                                        'price'             => str_replace(',', '.', $row[5]),
                                        'sku'               => $sku,
                                        'ean'               => $ean,
                                        'asin'              => $product->asin,
                                        'cost'              => $product->price,
                                        'updated_date'      => $dateTime,
                                        'last_update_date'  => $dateTime
                                    ]);
                            } else {
                                $modelProduct = DB::table('modelproducts')
                                                ->where('idproduct', '=', $product->productid)
                                                ->first();
                                if(empty($modelProduct)) {
                                    DB::table('modelproducts')
                                        ->insert([
                                            'idproduct' => $product->productid
                                        ]);
                                }
                                DB::table('prices')
                                    ->insert([
                                        'product_id'        => $product->productid,
                                        'channel_id'        => $channelId,
                                        'warehouse_id'      => $channel->warehouse,
                                        'platform_id'       => $platformId,
                                        'online_price'      => str_replace(',', '.', $row[5]),
                                        'online_quentity'   => $row[7],
                                        'price'             => str_replace(',', '.', $row[5]),
                                        'sku'               => $sku,
                                        'ean'               => $ean,
                                        'asin'              => $product->asin,
                                        'cost'              => $product->price,
                                        'updated_date'      => $dateTime,
                                        'created_date'      => $dateTime,
                                        'last_update_date'  => $dateTime
                                    ]);
                            }
                        } else {
                            array_push($nonExistingProducts, $row[0]);
                        }
                    }
                }
            }
        }

        return redirect()->route('priceView');
    }

    public function uploadShippingCost(Request $request) {
        if($request->hasFile('uploadfilename')) {
            $files      = $request->file('uploadfilename');
            $checks     = $request->check;
            $dateTime   = date('Y-m-d H:i:s');
            $fileArrKey = array_keys($files);
            $nonExistingProducts = [];
            
            for($i = 0; $i < count($fileArrKey); $i++) {
                $path            = $files[$fileArrKey[$i]]->getRealPath();
                $check_idexplode = explode("-", $checks[$i]);
                $platformId      = $check_idexplode[0];
                $channelId       = $check_idexplode[1];

                $channel   = DB::table('channel')
                            ->where('channel.idchannel', '=', $channelId)
                            ->leftjoin('platform', 'platform.platformid', '=', 'channel.platformid')
                            ->leftjoin('coding', 'channel.codingId', '=', 'coding.codingId')
                            ->first();

                if($channel->platformtype == "Ebay") {
                    if($channel->coding == "EbayUK-01") {
                        $file       = file_get_contents($path);
                        $file       = explode("\n", $file);
                        $totalrows  = count($file);

                        for($loopfile=1; $loopfile < $totalrows; $loopfile++) {
                            $row    = str_getcsv($file[$loopfile], ",");
                            if(count($row) > 3) {
                                $sku            = $row[4];
                                $shippingCost   = str_replace(",", ".", $row[37]);
                                $product        = DB::table('product')
                                                    ->where('sku', '=', $sku)
                                                    ->first();

                                if(!empty($product)) {
                                    $productid = $product->productid;

                                    $existingPrice = DB::table('prices')
                                                    ->where('product_id'      , '=', $product->productid)
                                                    ->where('channel_id'     , '=', $channelId)
                                                    ->first();
                                                    
                                    if(!empty($existingPrice)) {
                                        $online_shipping = str_replace(',', '.', $shippingCost);
                                        
                                        DB::table('prices')
                                            ->where('product_id'    , '=', $product->productid)
                                            ->where('channel_id'    , '=', $channelId)
                                            ->update([
                                                'last_update_shipping'  => date('Y-m-d H:i:s'),
                                                'online_shipping'       => $online_shipping,
                                                'shipping'              => $online_shipping
                                            ]);
                                    }
                                } else {
                                    array_push($nonExistingProducts, $sku);
                                }
                            }
                        }
                    } else {
                        $file       = file_get_contents($path);
                        $file       = explode("\n", $file);
                        $totalrows  = count($file);
                        for($loopfile=1; $loopfile < $totalrows; $loopfile++) {
                            $row    = str_getcsv($file[$loopfile], ";");
                            if(count($row) > 3) {
                                $sku            = $row[4];
                                $shippingCost   = str_replace(",", ".", $row[37]);
                                $product    = DB::table('product')
                                                ->where('sku', '=', $sku)
                                                ->first();

                                if(!empty($product)) {
                                    $productid = $product->productid;

                                    $existingPrice = DB::table('prices')
                                                    ->where('product_id'      , '=', $product->productid)
                                                    ->where('channel_id'     , '=', $channelId)
                                                    ->first();
                                                    
                                    if(!empty($existingPrice)) {
                                        $online_shipping = str_replace(',', '.', $shippingCost);
                                        
                                        DB::table('prices')
                                            ->where('product_id'    , '=', $product->productid)
                                            ->where('channel_id'    , '=', $channelId)
                                            ->update([
                                                'last_update_shipping'  => date('Y-m-d H:i:s'),
                                                'online_shipping'       => $online_shipping,
                                                'shipping'              => $online_shipping
                                            ]);
                                    }
                                } else {
                                    array_push($nonExistingProducts, $sku);
                                }
                            }
                        }
                    }
                } else if($channel->platformtype == "Amazon") {
                    $file       = file_get_contents($path);
                    $file       = explode("\n", $file);  
                    $totalrows  = count($file);
                    
                    for($loopfile=1; $loopfile < $totalrows; $loopfile++) {
                        $row    = $file[$loopfile];
                        $row    = explode("\t",$row);
                        if(count($row) > 16) {

                            if($channelId == 3) {
                                $modelName = $row[15];
                            } else {
                                $modelName = $row[16];
                            }                        

                            $product   = DB::table('product')
                                        ->where('sku', '=', $row[0])
                                        ->first();

                            if(!empty($product)) {
                                $existingPrice = DB::table('prices')
                                                ->where('product_id'      , '=', $product->productid)
                                                ->where('channel_id'     , '=', $channelId)
                                                ->first();
                                
                                $shippingcost = DB::table('modelamazonshippingcost')
                                                ->leftjoin('modelamazonshippingcostname', 'modelamazonshippingcostname.amazonshippingcostmodelnameid', '=', 'modelamazonshippingcost.modelnameId')
                                                ->where('modelamazonshippingcostname.modelname', '=', $modelName)
                                                ->where('modelamazonshippingcost.channelId', '=', $channelId)
                                                ->first();  

                                if(!empty($existingPrice)) {
                                    if(!empty($shippingcost)) {
                                        DB::table('prices')
                                            ->where('product_id'    , '=', $product->productid)
                                            ->where('channel_id'    , '=', $channelId)
                                            ->update([
                                                'last_update_shipping'  => date('Y-m-d H:i:s'),
                                                'online_shipping'       => $shippingcost->shippingcost,
                                                'shipping'              => $shippingcost->shippingcost
                                            ]);
                                    } else {

                                    }
                                }
                            } else {
                                array_push($nonExistingProducts, $row[0]);      
                            }
                        }
                    }
                }       
            }
        }

        return redirect()->route('priceView');
    }

    public function createUploadFiles() {
        $channels  = DB::table('channel')
                    ->leftjoin('platform', 'channel.platformid', '=', 'platform.platformid')
                    ->select('channel.*', 'platform.shortname as platformName')
                    ->get();

        foreach($channels as $channel) {
            $rows   = DB::table("prices")
                        ->leftjoin('product'    , 'prices.product_id'   , '=', 'product.productid')
                        ->leftjoin('warehouse'  , 'prices.warehouse_id' , '=', 'warehouse.idwarehouse')
                        ->leftjoin('platform'   , 'prices.platform_id'  , '=', 'platform.platformid')
                        ->leftjoin('channel'    , 'prices.channel_id'   , '=', 'channel.idchannel')
                        ->leftjoin('modelproducts'  , 'prices.product_id'   , '=', 'modelproducts.idproduct')
                        ->select('prices.*', 'modelproducts.*', 'product.min_sell', 'product.price as productPrice', 'product.nameshort', 'product.modelcode AS productModelcode', 'product.shipp_model_id', 'product.fees_model_id', 'product.vat_model_id', 'channel.shortname AS channelShortname', 'channel.country AS channelCountry', 'warehouse.location AS warehouseLocation', 'platform.shortname AS platformShortname')
                        ->where('prices.channel_id', '=', $channel->idchannel)
                        ->orderby('prices.product_id', 'asc')
                        ->paginate(100);

            $company = DB::table('companyinfo')
                        ->where('idcompany', '=', $channel->idcompany)
                        ->first();

            $timeArr = explode(" ", date('Y-m-d H:i:s'));
            $data = "PlanName\tFBA_".$channel->shortname."_".$timeArr[0]."_".$timeArr[1].PHP_EOL;
            $data .= "ShipToCountry\t".PHP_EOL;
            $data .= "AddressName\t".$company->shortname.PHP_EOL;
            $data .= "AddressFieldOne\t".$company->street1.PHP_EOL;
            $data .= "AddressFieldTwo\t".$company->street2.PHP_EOL;
            $data .= "AddressCity\t".$company->city.PHP_EOL;

            $countryCode = DB::table('country')
                        ->where('longname', '=', $company->country)
                        ->first();

            if(!empty($countryCode)) {
                $data .= "AddressCountryCode\t".$countryCode->shortname.PHP_EOL;
            }
            $data .= "AddressStateOrRegion\t".$company->province.PHP_EOL;
            $data .= "AddressPostalCode\t".$company->plz.PHP_EOL;
            $data .= "AddressDistrict\t".PHP_EOL.PHP_EOL;
            $data .= "MerchantSKU\tQuantity".PHP_EOL;
            $k = 0;
            foreach($rows as $row) {
                $data  .= $row->sku."\t".$row->price."\t".$row->shipping."\t".$row->shipping.PHP_EOL;
                $today  = date('Y-m-d');
                $date   = new \DateTime($today);
            }

            $fileName = time() .$channel->shortname.'.txt';
            File::put(public_path()."/export/".$fileName, $data);
        }

        $zip        = new ZipArchive;
        $fileName   = time()."Prices.zip";

        if ($zip->open($fileName, ZipArchive::CREATE) === TRUE) {
            $files = File::files(public_path()."/export/");
            foreach ($files as $key => $value) {
                $file = basename($value);
                $zip->addFile($value, $file);
            }
            
            $zip->close();
        }
        $files = File::files(public_path()."/export/");
        foreach ($files as $key => $value) {
            $file = basename($value);
            File::delete(public_path()."/export/".$file);
        }

        Session::flash('download.in.the.next.request', $fileName);
        return Redirect::to('priceView');
    }

    public function priceAutocheck() {
        echo app_path().'/AWS/amazon-mws-master/vendor/autoload.php';
        require_once app_path().'/AWS/amazon-mws-master/vendor/autoload.php';
        $products = DB::table('product')
                    ->get();

        $channels = DB::table('channel')
                    ->get();

        foreach($products as $product) {
            foreach($channels as $channel) {
                $asin = $product->asin;
                $ean  = $product->ean;
                if($channel->sync == "Automatic Synch with: Amazon") {
                    $aws_acc_key_id     = $channel->aws_acc_key_id;
                    $aws_secret_key_id  = $channel->aws_secret_key_id;
                    $merchant_id        = $channel->merchant_id;
                    $market_place_id    = $channel->market_place_id;
                    $mws_auth_token     = $channel->mws_auth_token;

                    $client = new Client();
                    
                    $date = date('Y-m-d H:i:s');
                    $dt = new DateTime($date);
                    $dt->setTimezone(new \DateTimeZone('UTC'));
                    echo $dt->format('Y-m-d\TH:i:s.u\Z');

                    // $res = $client->request('POST', 'https://mws.amazonservices.com/Products/'.$date, [
                    //     'form_params' => [
                    //         'AWSAccessKeyId'    => $aws_acc_key_id,
                    //         'Action'            => 'GetMyPriceForASIN',
                    //         'MWSAuthToken'      => $mws_auth_token,
                    //         'MarketplaceId'     => $market_place_id,
                    //         'SellerId'          => $merchant_id,
                    //         'SignatureMethod'   => 'HmacSHA256',
                    //         'SignatureVersion'  => '2',
                    //         'Timestamp'         => $datetime->format(DateTime::ATOM),
                    //         'Version'           => '2011-10-01',
                    //         'Signature'         => 'x%2FEXAMPLEFSPqX7tAN83%2FROsuHWc04SEaepLkEXAMPLEo%3D',
                    //         'ASINList.ASIN.1'   => $asin
                    //     ]
                    // ]);
                    
                    $ch = curl_init();

                    curl_setopt($ch, CURLOPT_URL,"https://mws.amazonservices.com/Products/2021-04-09");
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                    curl_setopt($ch, CURLOPT_POSTFIELDS,
                                "AWSAccessKeyId=AKIAJV7MBCTERNQSHBIA&Action=GetMyPriceForASIN&MWSAuthToken=amzn.mws.86b8f7b5-4e0f-5488-484d-e89c8e491acf&MarketplaceId=A1PA6795UKMFR9&SellerId=A1MYXZTPY3MSOT&SignatureMethod=HmacSHA256&SignatureVersion=2&Timestamp=".$dt->format('Y-m-d\TH:i:s.u\Z')."&Version=2021-04-09&Signature=x%2FEXAMPLEFSPqX7tAN83%2FROsuHWc04SEaepLkEXAMPLEo%3D&ASINList.ASIN.1=234123123");

                    // In real life you should use something like:
                    // curl_setopt($ch, CURLOPT_POSTFIELDS, 
                    //          http_build_query(array('postvar1' => 'value1')));

                    // Receive server response ...
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                    $server_output = curl_exec($ch);

                    curl_close ($ch);
                    
                    print_r($server_output);
                }
            }
        }
    }

    public function noneexistingproducts() {
        $newproducts = DB::table("tbl_none_product")
                ->leftjoin('channel'    , 'tbl_none_product.channelId'   , '=', 'channel.idchannel')
                ->where('tbl_none_product.related_modelcode', '=', "")
                ->orwhere('tbl_none_product.related_modelcode', '=', null)
                ->select('tbl_none_product.*', 'channel.shortname AS channelShortname', 'channel.country AS channelCountry')
                ->get();

        $productsModelCode = DB::table("product")
                                ->orderby("modelcode", "asc")
                                ->select("modelcode", "productid", "sku")
                                ->get();
        $params['newproducts']  = $newproducts;
        $params['modelcodes']   = $productsModelCode;
        return View::make('newProducts', $params);
    }

    public function newProductUpdate() {
        $productid      = $_GET["id"];
        $fieldName      = $_GET["field"];
        $fieldValue     = $_GET["value"]; 

        DB::table('tbl_none_product')
            ->where('id', '=', $productid)
            ->update([
                $fieldName    => $fieldValue
            ]);

        return 'success';
    }

    public function getWoocommercePriceandQuantity() {
        $channels = DB::table("channel")
                ->leftjoin("platform", "channel.platformid", "=", "platform.platformid")
                ->where("platform.platformtype", "=", "Woocommerce")
                ->select("channel.*")
                ->get();
                
        foreach($channels as $channel) {
            if($channel->woo_store_url != null && $channel->woo_store_url != "") {
                $page = 1;        
                config([
                    'woocommerce.store_url' => $channel->woo_store_url,
                    'woocommerce.consumer_key' => $channel->woo_consumer_key,
                    'woocommerce.consumer_secret' => $channel->woo_consumer_secret
                ]);
                Session::put("WOOCOMMERCE_STORE_URL",       $channel->woo_store_url);
                Session::put("WOOCOMMERCE_CONSUMER_KEY",    $channel->woo_consumer_key);
                Session::put("WOOCOMMERCE_CONSUMER_SECRET", $channel->woo_consumer_secret);
                while(1) {
                    $options = [
                        'per_page' => 50, // Or your desire number
                        'page' => $page
                    ];

                    $products = Product::all($options, $channel);

                    if(count($products) == 0) {
                        break;
                    }

                    $online_shipping = $channel->flat_shipping_costs;
                    foreach($products as $product) {
                        $sku        = $product->sku;
                        $price      = $product->price;
                        $quantity   = $product->stock_quantity;
                        $itemId     = $product->id;
                        // if(!isset($product->sku) || $product->sku == "") {
                        //     print_r($product);
                        // }

                        if(isset($product->sku) && $product->sku != "") {
                            echo $product->sku."<br>";
                            $modelcode = explode(" ", $sku)[0];
                            if(isset(explode(" ", $sku)[1])) {
                                $nameshort = explode(" ", $sku)[1];
                                $namelong = explode(" ", $sku)[1];
                            } else {
                                $nameshort = explode(" ", $sku)[0];
                                $namelong = explode(" ", $sku)[0];
                            }

                            $productExist = DB::table("product")
                                            ->where("modelcode", '=', $modelcode)
                                            ->first();

                            if(!empty($productExist)) {
                                $priceExist = DB::table("prices")
                                                ->where("channel_id",   "=", $channel->idchannel)
                                                ->where("sku",          "=", $sku)
                                                ->first();

                                if(!empty($priceExist)) {
                                    DB::table("prices")
                                        ->where("channel_id",   "=", $channel->idchannel)
                                        ->where("sku",          "=", $sku)
                                        ->update([
                                            "itemId"                => $itemId,
                                            "online_price"          => $price,
                                            "online_quentity"       => $quantity,
                                            "online_shipping"       => $online_shipping,
                                            "shipping"              => $online_shipping,
                                            "country"               => 'IT',
                                            "product_id"            => $productExist->productid,
                                            "last_update_shipping"  => date('Y-m-d H:i:s'),
                                            "last_update_qty_date"  => date('Y-m-d H:i:s'),
                                            "last_update_date"      => date('Y-m-d H:i:s'),
                                            "updated_date"          => date('Y-m-d H:i:s')
                                        ]);
                                } else {
                                    DB::table("prices")
                                        ->insert([
                                            "itemId"                => $itemId,
                                            "channel_id"            => $channel->idchannel,
                                            "warehouse_id"          => $channel->warehouse,
                                            "platform_id"           => $channel->platformid,
                                            "product_id"            => $productExist->productid,
                                            "online_price"          => $price,
                                            "price"                 => $price,
                                            "sku"                   => $sku,
                                            "country"               => 'IT',
                                            "cost"                  => $productExist->price,
                                            "online_quentity"       => $quantity,
                                            "online_shipping"       => $online_shipping,
                                            "shipping"              => $online_shipping,
                                            "last_update_shipping"  => date('Y-m-d H:i:s'),
                                            "last_update_qty_date"  => date('Y-m-d H:i:s'),
                                            "last_update_date"      => date('Y-m-d H:i:s'),
                                            "updated_date"          => date('Y-m-d H:i:s')
                                        ]);
                                }
                            } else {
                                $priceExist = DB::table("prices")
                                    ->where("channel_id",   "=", $channel->idchannel)
                                    ->where("sku",          "=", $sku)
                                    ->first();

                                if(!empty($priceExist)) {
                                    DB::table("prices")
                                        ->where("channel_id",   "=", $channel->idchannel)
                                        ->where("sku",          "=", $sku)
                                        ->update([
                                            "itemId"                => $itemId,
                                            "online_price"          => $price,
                                            "online_quentity"       => $quantity,
                                            "online_shipping"       => $online_shipping,
                                            "shipping"              => $online_shipping,
                                            "country"               => 'IT',
                                            "last_update_shipping"  => date('Y-m-d H:i:s'),
                                            "last_update_qty_date"  => date('Y-m-d H:i:s'),
                                            "last_update_date"      => date('Y-m-d H:i:s'),
                                            "updated_date"          => date('Y-m-d H:i:s')
                                        ]);
                                } else {
                                    $none_product = DB::table("tbl_none_product")
                                                        ->where("channelId",    "=", $channel->idchannel)
                                                        ->where("sku",          "=", $sku)
                                                        ->first();
                                    
                                    if(!empty($none_product)) {
                                        if($none_product->related_modelcode != null || $none_product->related_modelcode != "") {
                                            DB::table("prices")
                                                ->insert([
                                                    "itemId"                => $itemId,
                                                    "channel_id"            => $channel->idchannel,
                                                    "warehouse_id"          => $channel->warehouse,
                                                    "platform_id"           => $channel->platformid,
                                                    "online_price"          => $price,
                                                    "sku"                   => $sku,
                                                    "online_shipping"       => $online_shipping,
                                                    "shipping"              => $online_shipping,
                                                    "product_id"            => $none_product->related_modelcode,
                                                    "price"                 => $price,
                                                    "cost"                  => $productExist->price,
                                                    "online_quentity"       => $quantity,
                                                    "country"               => 'IT',
                                                    "last_update_shipping"  => date('Y-m-d H:i:s'),
                                                    "last_update_qty_date"  => date('Y-m-d H:i:s'),
                                                    "last_update_date"      => date('Y-m-d H:i:s'),
                                                    "updated_date"          => date('Y-m-d H:i:s')
                                                ]);
                                        } else {
                                            DB::table("tbl_none_product")
                                                ->where("channel_id",   "=", $channel->idchannel)
                                                ->where("sku",          "=", $sku)
                                                ->update([
                                                    "itemId"                => $itemId,
                                                    "channel_id"            => $channel->idchannel,
                                                    "online_price"          => $price,
                                                    "online_quentity"       => $quantity,
                                                    "status"                => 0
                                                ]);
                                        }
                                    } else {
                                        DB::table("tbl_none_product")
                                            ->insert([
                                                "itemId"                => $itemId,
                                                "channel_id"            => $channel->idchannel,
                                                "online_price"          => $price,
                                                "online_quentity"       => $quantity,
                                                "sku"                   => $sku,
                                                "status"                => 0
                                            ]);
                                    }
                                }
                            }       
                        }
                    }
                    
                    $page++;
                }
            }
        }

        return Redirect::route('priceView')->with(['msg' => 'product added']);
    }
}
