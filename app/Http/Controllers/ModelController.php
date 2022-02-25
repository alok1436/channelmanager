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

class ModelController extends Controller
{
    //
    public function modelView() {
        if(!Session::has('userID')) {
            return redirect()->route('login');
        }
        $channels = DB::table('channel')
                    ->get();

        $shippingcostChannels = DB::table('channel')
                                ->leftjoin('platform', 'platform.platformid', '=', 'channel.platformid')
                                ->where('platform.platformtype', '=', 'Amazon')
                                ->select('channel.*')
                                ->get();
                                
        $getModelName       = DB::table('modelshipname')
                                ->get();

        $modelfeesname      = DB::table('modelfeesname')
                                ->get(); 
        
        $modelvatname       = DB::table('modelvatname')
                                ->get(); 

        $countrys           = DB::table('country')
                                ->get();

        $warehouses         = DB::table('warehouse')
                                ->get();

        $modelamazonshippingcostname    = DB::table('modelamazonshippingcostname')
                                            ->get(); 
                                            
        foreach($shippingcostChannels as $channel) {
            foreach ($modelamazonshippingcostname as $key => $model) {
                $modelamazonshippingcost = DB::table('modelamazonshippingcost')
                                ->where('channelId',    '=', $channel->idchannel)
                                ->where('modelnameId',  '=', $model->amazonshippingcostmodelnameid)
                                ->first();

                $amazonshippingcost             = "amazonshippingcostmodelnameid".$model->amazonshippingcostmodelnameid;
                $modelamazonshippingcostId      = "modelamazonshippingcostId".$model->amazonshippingcostmodelnameid;
                if(!empty($modelamazonshippingcost)) {
                    $channel->$amazonshippingcost           = $modelamazonshippingcost->shippingcost;
                    $channel->$modelamazonshippingcostId    = $modelamazonshippingcost->modelId;
                } else {
                    $channel->$modelamazonshippingcostId    = null;
                    $channel->$amazonshippingcost           = null;
                }
            }
        }


        $modelshipping = DB::table('modelshipping')
                            ->leftjoin('warehouse'      , 'modelshipping.warehouseId'       , '=', 'warehouse.idwarehouse')
                            ->leftjoin('country'        , 'modelshipping.countryId'         , '=', 'country.countryid')
                            ->leftjoin('modelshipname'  , 'modelshipping.idmodelshipname'   , '=', 'modelshipname.idmodelshipname')
                            ->select('modelshipping.*', 'warehouse.shortname AS warehousename', 'country.shortname as countryname', 'modelshipname.name')
                            ->get();
        
        $modelshippingArr = [];
        foreach($warehouses as $warehouse) {
            foreach($countrys as $country) {
                $row = [];
                $row['warehousename']   = $warehouse->shortname;
                $row['countryname']     = $country->shortname;
                $row['warehouseId']     = $warehouse->idwarehouse;
                $row['countryId']       = $country->countryid;
                foreach ($getModelName as $key => $model) {
                    $modelshipping = DB::table('modelshipping')
                                        ->leftjoin('warehouse'      , 'modelshipping.warehouseId'       , '=', 'warehouse.idwarehouse')
                                        ->leftjoin('country'        , 'modelshipping.countryId'         , '=', 'country.countryid')
                                        ->leftjoin('modelshipname'  , 'modelshipping.idmodelshipname'   , '=', 'modelshipname.idmodelshipname')
                                        ->where('modelshipping.countryId'         , '=', $country->countryid)
                                        ->where('modelshipping.warehouseId'       , '=', $warehouse->idwarehouse)
                                        ->where('modelshipping.idmodelshipname'   , '=', $model->idmodelshipname)
                                        ->where('modelshipping.fba'               , '=', 0)
                                        ->select('modelshipping.*', 'warehouse.shortname AS warehousename', 'country.shortname as countryname', 'modelshipname.name')
                                        ->first();

                    if(!empty($modelshipping)) {
                        $row[$warehouse->idwarehouse.'-'.$country->countryid."-".$model->idmodelshipname]     = $modelshipping->valueship;
                        $row['fba']     = $modelshipping->fba;
                    }
                }

                array_push($modelshippingArr, $row);
                foreach ($getModelName as $key => $model) {
                    $modelshipping = DB::table('modelshipping')
                                        ->leftjoin('warehouse'      , 'modelshipping.warehouseId'       , '=', 'warehouse.idwarehouse')
                                        ->leftjoin('country'        , 'modelshipping.countryId'         , '=', 'country.countryid')
                                        ->leftjoin('modelshipname'  , 'modelshipping.idmodelshipname'   , '=', 'modelshipname.idmodelshipname')
                                        ->where('modelshipping.countryId'         , '=', $country->countryid)
                                        ->where('modelshipping.warehouseId'       , '=', $warehouse->idwarehouse)
                                        ->where('modelshipping.idmodelshipname'   , '=', $model->idmodelshipname)
                                        ->where('modelshipping.fba'               , '=', 1)
                                        ->select('modelshipping.*', 'warehouse.shortname AS warehousename', 'country.shortname as countryname', 'modelshipname.name')
                                        ->first();

                    if(!empty($modelshipping)) {
                        $row[$warehouse->idwarehouse.'-'.$country->countryid."-".$model->idmodelshipname]     = $modelshipping->valueship;
                        $row['fba']     = $modelshipping->fba;
                    }
                }

                array_push($modelshippingArr, $row);
            }
        }
        
        foreach($countrys as $country) {
            foreach($warehouses as $warehouse) {
                foreach ($getModelName as $key => $model) {
                    $modelshipping = DB::table('modelshipping')
                                    ->where('countryId'         , '=', $country->countryid)
                                    ->where('warehouseId'       , '=', $warehouse->idwarehouse)
                                    ->where('idmodelshipname'   , '=', $model->idmodelshipname)
                                    ->get();
    
                    $idmodelshipname    = "modelship".$model->idmodelshipname;
                    $idmodelshipId      = "idmodelship".$model->idmodelshipname;
                    if(count($modelshipping) > 0) {
                        $country->$idmodelshipname    = $modelshipping[0]->valueship;
                        $country->$idmodelshipId      = $modelshipping[0]->idmodelship;
                    } else {
                        $country->$idmodelshipname    = null;
                        $country->$idmodelshipId      = null;
                    }
                }
            }
            

            foreach ($modelvatname as $key => $model) {
                $modelvat = DB::table('modelvat')
                                ->where('countryid', '=', $country->countryid)
                                ->where('idmodelvatname', '=', $model->idmodelvatname)
                                ->get();
                $idmodelvatname    = "modelvat".$model->idmodelvatname;
                $idmodelvatId      = "idmodelvat".$model->idmodelvatname;
                if(count($modelvat) > 0) {
                    $country->$idmodelvatname    = $modelvat[0]->valuevat;
                    $country->$idmodelvatId      = $modelvat[0]->idmodelvat;
                } else {
                    $country->$idmodelvatname    = null;
                    $country->$idmodelvatId      = null;
                }
            }
        }

        foreach($channels as $item) {
            // foreach ($getModelName as $key => $model) {
            //     $modelshipping = DB::table('modelshipping')
            //                     ->where('channelid', '=', $item->idchannel)
            //                     ->where('idmodelshipname', '=', $model->idmodelshipname)
            //                     ->get();

            //     $idmodelshipname    = "modelship".$model->idmodelshipname;
            //     $idmodelshipId      = "idmodelship".$model->idmodelshipname;
            //     if(count($modelshipping) > 0) {
            //         $item->$idmodelshipname    = $modelshipping[0]->valueship;
            //         $item->$idmodelshipId      = $modelshipping[0]->idmodelship;
            //     } else {
            //         $item->$idmodelshipname    = null;
            //         $item->$idmodelshipId      = null;
            //     }
            // }

            foreach ($modelfeesname as $key => $model) {
                $modelfees = DB::table('modelfees')
                                ->where('channelid', '=', $item->idchannel)
                                ->where('idmodelfeesname', '=', $model->idmodelfeesname)
                                ->get();

                $idmodelfeesname    = "modelfees".$model->idmodelfeesname;
                $idmodelfeesId      = "idmodelfees".$model->idmodelfeesname;
                if(count($modelfees) > 0) {
                    $item->$idmodelfeesname    = $modelfees[0]->valuefees;
                    $item->$idmodelfeesId      = $modelfees[0]->idmodelfees;
                } else {
                    DB::table('modelfees')
                        ->insert([
                            'channelid'         => $item->idchannel,
                            'idmodelfeesname'   => $model->idmodelfeesname,
                        ]);
                        
                    $item->$idmodelfeesname    = null;
                    $item->$idmodelfeesId      = null;
                }
                
            }

            // foreach ($modelvatname as $key => $model) {
            //     $modelvat = DB::table('modelvat')
            //                     ->where('channelid', '=', $item->idchannel)
            //                     ->where('idmodelvatname', '=', $model->idmodelvatname)
            //                     ->get();
            //     $idmodelvatname    = "modelvat".$model->idmodelvatname;
            //     $idmodelvatId      = "idmodelvat".$model->idmodelvatname;
            //     if(count($modelvat) > 0) {
            //         $item->$idmodelvatname    = $modelvat[0]->valuevat;
            //         $item->$idmodelvatId      = $modelvat[0]->idmodelvat;
            //     } else {
            //         $item->$idmodelvatname    = null;
            //         $item->$idmodelvatId      = null;
            //     }
            // }
        }

        $params['channels']                     = $channels;
        $params['warehouses']                   = $warehouses;
        $params['shippingcostChannels']         = $shippingcostChannels;
        $params['getModelName']                 = $getModelName;
        $params['modelfeesname']                = $modelfeesname;
        $params['modelvatname']                 = $modelvatname;
        $params['modelamazonshippingcostname']  = $modelamazonshippingcostname;
        $params['countrys']                     = $countrys;
        $params['modelshipping']                = $modelshippingArr;
        return View::make('modelView', $params);
    }

    public function modelshippingUpdate() {
        $warehouse      = $_GET['warehouse'];
        $country        = $_GET['country'];
        $modelId        = $_GET['modelId'];
        $fieldName      = $_GET["field"];
        $fieldValue     = $_GET["value"];
        $fba            = $_GET["fba"];
        DB::table('modelshipping')
            ->where('modelshipping.countryId'         , '=', $country)
            ->where('modelshipping.warehouseId'       , '=', $warehouse)
            ->where('modelshipping.idmodelshipname'   , '=', $modelId)
            ->where('modelshipping.fba'               , '=', $fba)
            ->update([
                $fieldName    => $fieldValue
            ]);

        return 'success';
    }

    public function modelamazonshippingcostUpdate() {
        $idmodelship = $_GET['id'];
        $fieldName   = $_GET["field"];
        $fieldValue  = $_GET["value"];
        DB::table('modelamazonshippingcost')
            ->where('modelId', '=', $idmodelship)
            ->update([
                $fieldName    => $fieldValue
            ]);

        return 'success';
    }

    public function modelfeesUpdate() {
        $idmodelfees = $_GET['id'];
        $fieldName   = $_GET["field"];
        $fieldValue  = $_GET["value"];
        
        DB::table('modelfees')
            ->where('idmodelfees', '=', $idmodelfees)
            ->update([
                $fieldName    => $fieldValue
            ]);

        return 'success';
    }

    public function modelvatUpdate() {
        $idmodelvat  = $_GET['id'];
        $fieldName   = $_GET["field"];
        $fieldValue  = $_GET["value"];
        DB::table('modelvat')
            ->where('idmodelvat', '=', $idmodelvat)
            ->update([
                $fieldName    => $fieldValue
            ]);

        return 'success';
    }

    public function addNewShippingModel(Request $request) {
        if($request['shipping_model_name']!=''){
            if(isset($request['warehouse_country_ids']) && count($request['warehouse_country_ids'])>0){
                //INSERT NEW MODEL NAME
                $last_shipping_model_name_id = DB::table('modelshipname')
                    ->insertGetId([
                        'name'    => $request['shipping_model_name']
                    ]);
                
                if($last_shipping_model_name_id){
                    foreach ($request['warehouse_country_ids'] as $warehouse_countryID){
                        
                        $warehouse_countryIDArr = explode("-", $warehouse_countryID) ;
                        $warehouseId    = $warehouse_countryIDArr[0];
                        $countryId      = $warehouse_countryIDArr[1];
                        if(trim($countryId)!='' && trim($warehouseId)!=''){
                            $warehouse_countryVal = $request['warehouse_country_val'][$warehouse_countryID];
                            if($warehouse_countryID == ''){
                                $warehouse_countryID = null;
                            }
                            DB::table('modelshipping')
                                ->insertGetId([
                                    'idmodelshipname'   => $last_shipping_model_name_id,
                                    'countryId'         => $countryId,
                                    'warehouseId'       => $warehouseId,
                                    'valueship'         => $warehouse_countryVal
                                ]);
                        }
                    }

                    foreach ($request['warehouse_country_fba_ids'] as $warehouse_countryID){
                        $warehouse_countryIDArr = explode("-", $warehouse_countryID) ;
                        $warehouseId    = $warehouse_countryIDArr[0];
                        $countryId      = $warehouse_countryIDArr[1];
                        if(trim($countryId)!='' && trim($warehouseId)!=''){
                            $warehouse_countryVal = $request['warehouse_country_fba_val'][$warehouse_countryID];
                            if($warehouse_countryID == ''){
                                $warehouse_countryID = null;
                            }
                            DB::table('modelshipping')
                                ->insertGetId([
                                    'idmodelshipname'   => $last_shipping_model_name_id,
                                    'countryId'         => $countryId,
                                    'warehouseId'       => $warehouseId,
                                    'valueship'         => $warehouse_countryVal,
                                    'fba'               => 1
                                ]);
                        }
                    }
                }
            }
        }
        return redirect()->route('modelView');
    }

    public function addNewFeesModel(Request $request) {
        if($request['fees_model_name']!=''){
            if(isset($request['channel_ids']) && count($request['channel_ids'])>0){
                //INSERT NEW MODEL NAME
                $last_fees_model_name_id = DB::table('modelfeesname')
                    ->insertGetId([
                        'name'    => $request['fees_model_name']
                    ]);
                
                if($last_fees_model_name_id){
                    foreach ($request['channel_ids'] as $channelID){
                        if(trim($channelID)!=''){
                            $channelVal = $request['channel_val'][$channelID];
                            if($channelVal == ''){
                                $channelVal = null;
                            }
                            DB::table('modelfees')
                                ->insertGetId([
                                    'idmodelfeesname'   => $last_fees_model_name_id,
                                    'channelid'         => $channelID,
                                    'valuefees'         => $channelVal
                                ]);
                        }
                    }
                }
            }
        }
        return redirect()->route('modelView');
    }

    public function addNewVatModel(Request $request) {
        if($request['vat_model_name']!=''){
            if(isset($request['country_ids']) && count($request['country_ids'])>0){
                //INSERT NEW MODEL NAME
                $last_vat_model_name_id = DB::table('modelvatname')
                    ->insertGetId([
                        'name'    => $request['vat_model_name']
                    ]);
                
                if($last_vat_model_name_id){
                    foreach ($request['country_ids'] as $countryID){
                        if(trim($countryID)!=''){
                            $countryVal = $request['country_val'][$countryID];
                            if($countryVal == ''){
                                $countryVal = null;
                            }
                            DB::table('modelvat')
                                ->insertGetId([
                                    'idmodelvatname'   => $last_vat_model_name_id,
                                    'countryid'        => $countryID,
                                    'valuevat'         => $countryVal
                                ]);
                        }
                    }
                }
            }
        }
        return redirect()->route('modelView');
    }

    public function addNewAmazonShippingCostModel(Request $request) {
        if($request['shippingcost_model_name']!=''){
            if(isset($request['channel_ids']) && count($request['channel_ids'])>0){
                //INSERT NEW MODEL NAME
                $last_amazonshippingcostmodelnameid = DB::table('modelamazonshippingcostname')
                    ->insertGetId([
                        'modelname'    => $request['shippingcost_model_name']
                    ]);
                
                if($last_amazonshippingcostmodelnameid){
                    foreach ($request['channel_ids'] as $channelID){
                        if(trim($channelID)!=''){
                            $channelVal = $request['channel_val'][$channelID];
                            if($channelVal == ''){
                                $channelVal = null;
                            }
                            DB::table('modelamazonshippingcost')
                                ->insertGetId([
                                    'modelnameId'   => $last_amazonshippingcostmodelnameid,
                                    'channelId'     => $channelID,
                                    'shippingcost'  => $channelVal
                                ]);
                        }
                    }
                }
            }
        }
        return redirect()->route('modelView');
    }
}
