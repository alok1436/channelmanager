<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Maatwebsite\Excel\Facades\Excel;
use Session;
use Redirect;
use Image;
use App\Models\Warehouse;
use App\Models\LagerStand;
use App\Models\InventoryWarehouseHistory;

class SettingController extends Controller
{
    //
    public function index() {
        if(!Session::has('userID')) {
            return redirect()->route('login');
        }
    }

    public function manufacturer() {
        if(!Session::has('userID')) {
            return redirect()->route('login');
        }
        $manufacturer = DB::table('manufacturer')
                        ->get();

        $params['manufacturer'] = $manufacturer;
        return View::make('manufacturer', $params);
    }

    public function manufacturerDelete() {
        $manufacturerId   = $_GET["manufacturerId"];

        DB::table('manufacturer')
            ->where('manufacturerid', '=', $manufacturerId)
            ->delete();

        return 'success';
    }
    
    public function manufacturerUpdate() {
        $manufacturerId = $_GET["id"];
        $fieldName      = $_GET["field"];
        $fieldValue     = $_GET["value"]; 

        DB::table('manufacturer')
            ->where('manufacturerid', '=', $manufacturerId)
            ->update([
                $fieldName    => $fieldValue
            ]);

        return 'success';
    }

    public function manufacturerAddView() {
        if(!Session::has('userID')) {
            return redirect()->route('login');
        }
        return View::make('manufacturerAddView');
    }

    public function manufacturerAdd(Request $request) {
        $shortname  = $request['shortname'];
        $longname   = $request['longname'];
        $street1    = $request['street1'];
        $street2    = $request['street2'];
        $plz        = $request['plz'];
        $city       = $request['city'];
        $province   = $request['province'];
        $country    = $request['country'];
        $contact1   = $request['contact1'];
        $contact2   = $request['contact2'];
        $contact3   = $request['contact3'];
        $phone1     = $request['phone1'];
        $phone2     = $request['phone2'];
        $phone3     = $request['phone3'];
        $email1     = $request['email1'];
        $email2     = $request['email2'];
        $email3     = $request['email3'];
        $note1      = $request['note1'];
        $note2      = $request['note2'];
        $note3      = $request['note3'];

        DB::table('manufacturer')
            ->insert([
                'shortname' => $shortname,
                'longname'  => $longname,
                'street1'   => $street1,
                'street2'   => $street2,
                'plz'       => $plz,
                'city'      => $city,
                'province'  => $province,
                'country'   => $country,
                'contact1'  => $contact1,
                'contact2'  => $contact2,
                'contact3'  => $contact3,
                'phone'     => $phone1,
                'phone1'    => $phone1,
                'phone2'    => $phone2,
                'phone3'    => $phone3,
                'email1'    => $email1,
                'email2'    => $email2,
                'email3'    => $email3,
                'note1'     => $note1,
                'note2'     => $note2,
                'note3'     => $note3
            ]);

        return redirect()->route('manufacturer');
    }

    public function warehouseView() {
        $warehouse = DB::table('warehouse')
                        ->leftJoin('companyinfo','warehouse.idcompany','=','companyinfo.idcompany')
                        ->select('warehouse.*', 'companyinfo.shortname as cm_shortname')
                        ->get();
        $companies = DB::table('companyinfo')
                        ->get();    
                        
        $params['warehouse'] = $warehouse;
        $params['companies'] = $companies;

        return View::make('warehouseView', $params);
    }

    public function warehouseDelete() {
        $id = $_GET['del'];

        DB::table('warehouse')
            ->where('idwarehouse', '=', $id)
            ->delete();

        return Redirect::route('warehouseView')->with(['msg' => 'Warehouse deleted']);
    }

    public function warehouseAddView() {
        $companies = DB::table('companyinfo')
                        ->get();
        $params['companies'] = $companies;
        return View::make('warehouseAddView', $params);
    }

    public function warehouseAdd(Request $request) {
        $shortname  = $request['shortname'];
        $location   = $request['location'];
        $idcompany  = $request['idcompany'];
        $street1    = $request['street1'];
        $street2    = $request['street2'];
        $plz        = $request['plz'];
        $city       = $request['city'];
        $province   = $request['province'];
        $country    = $request['country'];
        $phone      = $request['phone'];
        $fax        = $request['fax'];
        $email      = $request['email'];
        $notes      = $request['notes'];

        DB::table('warehouse')
            ->insert([
                'shortname' => $shortname,
                'location'  => $location,
                'idcompany' => $idcompany,
                'street1'   => $street1,
                'street2'   => $street2,
                'plz'       => $plz,
                'city'      => $city,
                'province'  => $province,
                'country'   => $country,
                'phone'     => $phone,
                'fax'       => $fax,
                'email'     => $email,
                'notes'     => $notes
            ]);

        return redirect()->route('warehouseView');
    }

    public function warehouseUpdate() {
        $warehouseId    = $_GET["id"];
        $fieldName      = $_GET["field"];
        $fieldValue     = $_GET["value"]; 

        DB::table('warehouse')
            ->where('idwarehouse', '=', $warehouseId)
            ->update([
                $fieldName    => $fieldValue
            ]);

        return 'success';
    }

    public function channelView() {
        $channel            = DB::table('channel')
                                ->leftJoin('companyinfo','channel.idcompany'    ,'='    ,'companyinfo.idcompany')
                                ->leftJoin('platform'   ,'channel.platformid'   ,'='    ,'platform.platformid')
                                ->leftJoin('warehouse'  ,'channel.warehouse'    ,'='    ,'warehouse.idwarehouse')
                                ->leftJoin('coding'     ,'coding.codingId'      ,'='    ,'channel.codingId')
                                ->select('channel.*', 'coding.coding', 'companyinfo.shortname as cm_shortname', 'platform.shortname as pl_shortname', 'warehouse.shortname as w_shortname')
                                ->get();

        $companies          = DB::table('companyinfo')
                                ->get();    
        $platforms          = DB::table('platform')
                                ->get();   
        $warehouse          = DB::table('warehouse')
                                ->get();                
        $datafetchcountry   = DB::table('country')
                                ->get();
        $codings            = DB::table('coding')
                                ->get();
        $params['channel']          = $channel;
        $params['warehouse']        = $warehouse;
        $params['companies']        = $companies;
        $params['platforms']        = $platforms;
        $params['codings']          = $codings;
        $params['datafetchcountry'] = $datafetchcountry;

        return View::make('channelView', $params);
    }

    public function channelUpdate() {
        $channelId      = $_GET["id"];
        $fieldName      = $_GET["field"];
        $fieldValue     = $_GET["value"]; 

        DB::table('channel')
            ->where('idchannel', '=', $channelId)
            ->update([
                $fieldName    => $fieldValue
            ]);

        return 'success';
    }

    public function channeldelete() {
        $id = $_GET['del'];

        DB::table('channel')
            ->where('idchannel', '=', $id)
            ->delete();
        
        return Redirect::route('channelView')->with(['msg' => 'Channel deleted']);
    }

    public function channelAdd(Request $request) {
        $devID               = $request['devid'];
        $appID               = $request['appid'];
        $certID              = $request['certid'];
        $userTokent          = $request['usertoken'];
        $userToken           = str_replace(' ', '+', $userTokent);
        $idcompany           = $request['idcompany'];
        $platformid          = $request['platformid'];
        $shortname           = $request['shortname'];
        $longname            = $request['longname'];
        $country             = $request['country'];
        $warehouse           = $request['warehouse'];
        $vat                 = $request['vat'];
        $aws_acc_key_id      = $request['aws_acc_key_id'];
        $aws_secret_key_id   = $request['aws_secret_key_id'];
        $merchant_id         = $request['merchant_id'];
        $market_place_id     = $request['market_place_id'];
        $mws_auth_token      = $request['mws_auth_token'];
        $flat_shipping_cost  = $request['flat_shipping_cost'];
        $woo_store_url       = $request['woo_store_url'];
        $woo_consumer_key    = $request['woo_consumer_key'];
        $woo_consumer_secret = $request['woo_consumer_secret'];

        $platforms           = DB::table('platform')
                                ->where('platformid', '=', $platformid)
                                ->get(); 

        $platformtype = $platforms[0]->platformtype;
        $sync = "";
        if($platformtype == 'Amazon') {
            $sync = 'Automatic Synch with: Amazon';
        }

        if($platformtype == 'Ebay') {
            $sync = 'Automatic Synch with: eBay';
        }

        if($platformtype == 'Woocommerce') {
            $sync = 'Automatic Synch with: Woocommerce';
        }

        DB::table('channel')
            ->insert([
                'idcompany'           => $idcompany,
                'platformid'          => $platformid,
                'sync'                => $sync,
                'shortname'           => $shortname,
                'country'             => $country,
                'longname'            => $longname,
                'warehouse'           => $warehouse,
                'vat'                 => $vat,
                'devid'               => $devID,
                'appid'               => $appID,
                'certid'              => $certID,
                'usertoken'           => $userToken,
                'aws_acc_key_id'      => $aws_acc_key_id,
                'aws_secret_key_id'   => $aws_secret_key_id,
                'merchant_id'         => $merchant_id,
                'market_place_id'     => $market_place_id,
                'mws_auth_token'      => $mws_auth_token,
                'woo_store_url'         => $woo_store_url,
                'woo_consumer_key'      => $woo_consumer_key,
                'woo_consumer_secret'   => $woo_consumer_secret,
                'flat_shipping_costs' => $flat_shipping_cost
            ]);
        return Redirect::route('channelView')->with(['msg' => 'Channel added']);
    }

    public function channelAddView() {
        $companies          = DB::table('companyinfo')
                                ->get();

        $datafetchcountry   = DB::table('country')
                                ->get();
        $platforms          = DB::table('platform')
                                ->get(); 
        $warehouse          = DB::table('warehouse')
                                ->get(); 
        $params['companies']        = $companies;
        $params['datafetchcountry'] = $datafetchcountry;
        $params['platforms']        = $platforms;
        $params['warehouse']        = $warehouse;
        return View::make('channelAddView', $params);
    }

    public function channelEditView() {
        $channelType = $_GET['channel'];
        $idchannel   = $_GET['idchannel']; 
        $channel     = DB::table('channel')
                        ->where('idchannel', '=', $idchannel)
                        ->get();

        $params['channel']      = $channel[0];
        $params['channelType']  = $channelType;
        return View::make('channelEditView', $params);
    }

    public function channelEdit(Request $request) {
        $devID                  = $request['devid'];
        $appID                  = $request['appid'];
        $certID                 = $request['certid'];
        $userTokent             = $request['usertoken'];
        $userToken              = str_replace(' ', '+', $userTokent);
        $aakid                  = $request['aakid'];
        $asak                   = $request['asak'];
        $merchantid             = $request['merchantid'];
        $marketplaceid          = $request['marketplaceid'];
        $authtoken              = $request['authtoken'];
        $channelIdhidden        = $request["channelIdhidden"];
        $channelType            = $request["channelType"]; 
        $flat_shipping_cost     = $request['flat_shipping_cost'];
        $woo_store_url          = $request['woo_store_url'];
        $woo_consumer_key       = $request['woo_consumer_key'];
        $woo_consumer_secret    = $request['woo_consumer_secret'];

        if($channelType == 'amazon') {
            DB::table('channel')
                ->where('idchannel', '=', $channelIdhidden)
                ->update([
                    'aws_acc_key_id'     => $aakid,
                    'aws_secret_key_id'  => $asak,
                    'merchant_id'        => $merchantid,
                    'market_place_id'    => $marketplaceid,
                    'mws_auth_token'     => $authtoken
                ]);
        } elseif($channelType == 'woocommerce') {
            DB::table('channel')
                ->where('idchannel', '=', $channelIdhidden)
                ->update([
                    'woo_store_url'         => $woo_store_url,
                    'woo_consumer_key'      => $woo_consumer_key,
                    'woo_consumer_secret'   => $woo_consumer_secret,
                    'flat_shipping_costs'   => $flat_shipping_cost
                ]);
        } else {
            DB::table('channel')
                ->where('idchannel', '=', $channelIdhidden)
                ->update([
                    'devid'     => $devID,
                    'appid'     => $appID,
                    'certid'    => $certID,
                    'usertoken' => $userToken,
                    'flat_shipping_costs'   => $flat_shipping_cost
                ]);
        }
        
        return Redirect::route('channelView')->with(['msg' => 'Channel updated']);
    }

    public function paymentView() {
        $payments     = DB::table('payment')
                        ->get();

        $params['payments'] = $payments;
        return View::make('paymentView', $params);
    }

    public function paymentUpdate(Request $request) {
        if($request->action == 'update_warehouse'){
            
            $ls =  LagerStand::where(['productid'=>$request->id, 'idwarehouse'=>$request->field])->first();
            if($ls){
                $ls->quantity = $request->value;
                $ls->save();
            }else{
                $ls = new LagerStand();
                $ls->productid = $request->id;
                $ls->idwarehouse =$request->field; 
                $ls->quantity = $request->value;
                $ls->save();
            }
            
            $record = new InventoryWarehouseHistory(); 
            $record->product_id = $request->id;
            $record->warehouse_id = $request->field;
            $record->old_value = $request->old;
            $record->new_value = $request->value;
            $record->save();
            
        }else if($request->action == 'update_hac'){
            $fieldName      = $_GET["field"];
           $ls =  LagerStand::where(['productid'=>$request->id, 'idwarehouse'=>$request->old])->first();
            if($ls){
                $ls->$fieldName = $request->value;
                $ls->save();
            }else{
                $ls = new LagerStand();
                $ls->productid = $request->id;
                $ls->idwarehouse =$request->old; 
                $ls->$fieldName = $request->value;
                $ls->save();
            }

            return 'success';
        }else{
            $idpayment      = $_GET["id"];
            $fieldName      = $_GET["field"];
            $fieldValue     = $_GET["value"]; 
    
            DB::table('payment')
                ->where('idpayment', '=', $idpayment)
                ->update([
                    $fieldName    => $fieldValue
                ]);
    
            return 'success';
        }
    }

    public function paymentDelete() {
        $idpayment      = $_GET["del"];

        DB::table('payment')
            ->where('idpayment', '=', $idpayment)
            ->delete();

        return Redirect::route('paymentView')->with(['msg' => 'Payment deleted']);
    }
    
    public function paymentAddView() {
        return View::make('paymentAddView');
    }

    public function paymentAdd(Request $request) {
        $shortname  = $request['shortname'];
        $longname   = $request['longname'];

        $idpayment  = DB::table('payment')
            ->insertGetId([
                'shortname' => $shortname,
                'longname'  => $longname
            ]);
        return Redirect::route('paymentView')->with(['idpayment' => $idpayment]);
    }

    public function userView() {
        $users     = DB::table('user')
                        ->get();

        $params['users'] = $users;
        return View::make('userView', $params);
    }

    public function userEditView() {
        $userid = $_GET['id'];
        $user       = DB::table('user')
                        ->where('userid', '=', $userid)
                        ->get();

        $params['user'] = $user[0];
        return View::make('userEditView', $params);
    }

    public function userAddView() {
        return View::make('userAddView');
    }

    public function userAdd(Request $request) {
        $shortname  = $request['shortname'];
        $longname   = $request['longname'];
        $username   = $request['username'];
        $email      = $request['email'];
        $password   = $request['password'];

        DB::table('user')
            ->insert([
                'shortname' => $shortname,
                'longname'  => $longname,
                'username'  => $username,
                'email'     => $email,
                'password'  => $password
            ]);
        return Redirect::route('userView')->with(['msg' => 'User added']);
    }

    public function userDelete() {
        $userid = $_GET['userid'];
        DB::table('user')
            ->where('userid', '=', $userid)
            ->delete();
        
        return Redirect::route('userView')->with(['msg' => 'User deleted']);
    }

    public function platformView() {
        $platforms     = DB::table('platform')
                        ->get();

        $params['platforms'] = $platforms;
        return View::make('platformView', $params);
    }

    public function platformDelete() {
        $id = $_GET['del'];

        DB::table('platform')
            ->where('platformid', '=', $id)
            ->delete();
        
        return Redirect::route('platformView')->with(['msg' => 'Platform deleted']);
    }

    public function platformUpdate() {
        $idplatform     = $_GET["id"];
        $fieldName      = $_GET["field"];
        $fieldValue     = $_GET["value"]; 

        DB::table('platform')
            ->where('platformid', '=', $idplatform)
            ->update([
                $fieldName    => $fieldValue
            ]);

        return 'success';
    }

    public function countryView() {
        $countrys     = DB::table('country')
                        ->get();
        $datafetchcurrency = DB::table('currency')
                            ->get();
        $params['countrys']             = $countrys;
        $params['datafetchcurrency']    = $datafetchcurrency;
        return View::make('countryView', $params);
    }

    public function countryDelete() {
        $id = $_GET['del'];

        DB::table('country')
            ->where('countryid', '=', $id)
            ->delete();
        
        return Redirect::route('countryView')->with(['msg' => 'Country deleted']);
    }

    public function countryUpdate() {
        $idcountry     = $_GET["id"];
        $fieldName      = $_GET["field"];
        $fieldValue     = $_GET["value"]; 

        DB::table('country')
            ->where('countryid', '=', $idcountry)
            ->update([
                $fieldName    => $fieldValue
            ]);

        return 'success';
    }

    public function countryAdd(Request $request) {
        $shortname  = $request['shortname'];
        $longname   = $request['longname'];
        $currency   = $request['currency'];

        DB::table('country')
            ->insert([
                'shortname' => $shortname,
                'longname'  => $longname,
                'currency'  => $currency
            ]);
        return Redirect::route('countryView')->with(['msg' => 'Country added']);
    }

    public function countryAddView() {
        $datafetchcurrency = DB::table('currency')
                            ->get();
        $params['datafetchcurrency']    = $datafetchcurrency;
        return View::make('countryAddView', $params);
    }

    public function currencyView() {
        $currencys     = DB::table('currency')
                        ->get();
        $datafetchcurrency = DB::table('currency')
                            ->get();
        $params['currencys']             = $currencys;
        $params['datafetchcurrency']    = $datafetchcurrency;
        return View::make('currencyView', $params);
    }

    public function currencyDelete() {
        $id = $_GET['del'];

        DB::table('currency')
            ->where('currencyid', '=', $id)
            ->delete();
        
        return Redirect::route('currencyView')->with(['msg' => 'currency deleted']);
    }

    public function currencyUpdate() {
        $idcurrency     = $_GET["id"];
        $fieldName      = $_GET["field"];
        $fieldValue     = $_GET["value"]; 

        DB::table('currency')
            ->where('currencyid', '=', $idcurrency)
            ->update([
                $fieldName    => $fieldValue
            ]);

        return 'success';
    }

    public function currencyAdd(Request $request) {
        $shortname  = $request['shortname'];
        $longname   = $request['longname'];

        DB::table('currency')
            ->insert([
                'shortname' => $shortname,
                'longname'  => $longname
            ]);
        return Redirect::route('currencyView')->with(['msg' => 'currency added']);
    }

    public function currencyAddView() {
        $datafetchcurrency = DB::table('currency')
                            ->get();
        $params['datafetchcurrency']    = $datafetchcurrency;
        return View::make('currencyAddView', $params);
    }

    public function companyView() {
        $companys     = DB::table('companyinfo')
                        ->get();
        $datafetchcompany = DB::table('companyinfo')
                            ->get();
        $params['companys']            = $companys;
        $params['datafetchcompany']    = $datafetchcompany;
        return View::make('companyView', $params);
    }

    public function companyDelete() {
        $id = $_GET['del'];

        DB::table('companyinfo')
            ->where('idcompany', '=', $id)
            ->delete();
        
        return Redirect::route('companyView')->with(['msg' => 'company deleted']);
    }

    public function companyUpdate() {
        $idcompany     = $_GET["id"];
        $fieldName      = $_GET["field"];
        $fieldValue     = $_GET["value"]; 

        DB::table('companyinfo')
            ->where('idcompany', '=', $idcompany)
            ->update([
                $fieldName    => $fieldValue
            ]);

        return 'success';
    }

    public function companyAdd(Request $request) {
        $shortname        = $request['shortname'];
        $longname         = $request['longname'];
        $street1          = $request['street1'];
        $street2          = $request['street2'];
        $plz              = $request['plz'];
        $city             = $request['city'];
        $province         = $request['province'];
        $country          = $request['country'];
        $phone            = $request['phone'];
        $fax              = $request['fax'];
        $email            = $request['email'];
        $linklogo         = $request['linklogo'];
        $fussnoteinvoice  = $request['fussnoteinvoice'];
        DB::table('companyinfo')
            ->insert([
                'shortname'         => $shortname,
                'longname'          => $longname,
                'street1'           => $street1,
                'street2'           => $street2,
                'plz'               => $plz,
                'city'              => $city,
                'province'          => $province,
                'country'           => $country,
                'phone'             => $phone,
                'fax'               => $fax,
                'email'             => $email,
                'linklogo'          => $linklogo,
                'fussnoteinvoice'   => $fussnoteinvoice
            ]);
        return Redirect::route('companyView')->with(['msg' => 'company added']);
    }

    public function companyAddView() {
        $datafetchcompany = DB::table('companyinfo')
                            ->get();
        $params['datafetchcompany']    = $datafetchcompany;
        return View::make('companyAddView', $params);
    }

    public function courierView() {
        $couriers     = DB::table('shippingmodel')
                        ->get();

        $sdaCourier   = DB::table('shippingmodel')
                        ->where('shortname', '=', 'SDA')
                        ->first();
        $params['couriers']     = $couriers;
        $params['sdaCourier']   = $sdaCourier;

        return View::make('courierView', $params);
    }

    public function sdaCourierEdit(Request $request) {
        $vabpkb = $request->vabpkb;
        $vabccm = $request->vabccm;
        $vabcbo = $request->vabcbo;
        $vabctr = $request->vabctr;
        $vabtsp = $request->vabtsp;

        DB::table('shippingmodel')
            ->where('shortname', '=', 'SDA')
            ->update([
                'vabpkb' => $vabpkb,
                'vabccm' => $vabccm,
                'vabcbo' => $vabcbo,
                'vabctr' => $vabctr,
                'vabtsp' => $vabtsp
            ]);

        return Redirect::route('courierView')->with(['msg' => 'Edited successfully!']);
    }

    public function courierDelete() {
        $id = $_GET['del'];

        DB::table('shippingmodel')
            ->where('idcourier', '=', $id)
            ->delete();
        
        return Redirect::route('courierView')->with(['msg' => 'courier deleted']);
    }

    public function courierUpdate() {
        $idcourier     = $_GET["id"];
        $fieldName      = $_GET["field"];
        $fieldValue     = $_GET["value"]; 

        DB::table('shippingmodel')
            ->where('shippingmodelid', '=', $idcourier)
            ->update([
                $fieldName    => $fieldValue
            ]);

        return 'success';
    }

    public function courierAdd(Request $request) {
        $shortname        = $request['shortname'];
        $longname         = $request['longname'];
        $street1          = $request['street1'];
        $street2          = $request['street2'];
        $plz              = $request['plz'];
        $city             = $request['city'];
        $province         = $request['province'];
        $country          = $request['country'];
        $phone            = $request['phone'];
        $fax              = $request['fax'];
        $email            = $request['email'];
        $linklogo         = $request['linklogo'];
        $fussnoteinvoice  = $request['fussnoteinvoice'];
        DB::table('shippingmodel')
            ->insert([
                'shortname'         => $shortname,
                'longname'          => $longname,
                'street1'           => $street1,
                'street2'           => $street2,
                'plz'               => $plz,
                'city'              => $city,
                'province'          => $province,
                'country'           => $country,
                'phone'             => $phone,
                'fax'               => $fax,
                'email'             => $email,
                'linklogo'          => $linklogo,
                'fussnoteinvoice'   => $fussnoteinvoice
            ]);
        return Redirect::route('courierView')->with(['msg' => 'courier added']);
    }

    public function courierAddView() {
        $datafetchcourier = DB::table('shippingmodel')
                            ->get();
        $params['datafetchcourier']    = $datafetchcourier;
        return View::make('courierAddView', $params);
    }

    public function categoryView() {
        $categorys     = DB::table('maincategory')
                        ->get();

        $params['categorys']  = $categorys;

        return View::make('categoryView', $params);
    }

    public function categoryDelete() {
        $id = $_GET['del'];

        DB::table('maincategory')
            ->where('catmainid', '=', $id)
            ->delete();
        
        return Redirect::route('categoryView')->with(['msg' => 'category deleted']);
    }

    public function categoryUpdate() {
        $idcategory     = $_GET["id"];
        $fieldName      = $_GET["field"];
        $fieldValue     = $_GET["value"]; 

        DB::table('maincategory')
            ->where('catmainid', '=', $idcategory)
            ->update([
                $fieldName    => $fieldValue
            ]);

        return 'success';
    }

    public function categoryAdd(Request $request) {
        $shortname        = $request['Namecat'];
        $longname         = $request['Descriptioncat'];
        
        DB::table('maincategory')
            ->insert([
                'Namecat'         => $shortname,
                'Descriptioncat'          => $longname
            ]);
        return Redirect::route('categoryView')->with(['msg' => 'Category added']);
    }

    public function categoryAddView() {
        $datafetchcategory = DB::table('maincategory')
                            ->get();
        $params['datafetchcategory']    = $datafetchcategory;
        return View::make('categoryAddView', $params);
    }

    public function subcategoryView() {
        $subcategorys     = DB::table('subcategory')
                        ->get();

        $maincategorys     = DB::table('maincategory')
                        ->get();
        $params['subcategorys']  = $subcategorys;
        $params['maincategorys'] = $maincategorys;

        return View::make('subcategoryView', $params);
    }

    public function subcategoryDelete() {
        $id = $_GET['del'];

        DB::table('subcategory')
            ->where('catsubid', '=', $id)
            ->delete();
        
        return Redirect::route('subcategoryView')->with(['msg' => 'Subcategory deleted']);
    }

    public function subcategoryUpdate() {
        $idcategory     = $_GET["id"];
        $fieldName      = $_GET["field"];
        $fieldValue     = $_GET["value"]; 

        DB::table('subcategory')
            ->where('catsubid', '=', $idcategory)
            ->update([
                $fieldName    => $fieldValue
            ]);

        return 'success';
    }

    public function subcategoryAdd(Request $request) {
        $Namesubcat         = $request["shortname"];
        $Descriptionsubcat  = $request["longname"];
        $category           = $request["category"];
        $categoryexplode    = explode("!@-",$category);
        $catmainid          = $categoryexplode[0];
        $Namecat            = $categoryexplode[1];
        
        DB::table('subcategory')
            ->insert([
                'catmainid'         => $catmainid,
                'Namecat'           => $Namecat,
                'Namesubcat'        => $Namesubcat,
                'Descriptionsubcat' => $Descriptionsubcat
            ]);
        return Redirect::route('subcategoryView')->with(['msg' => 'Subcategory added']);
    }

    public function subcategoryAddView() {
        $maincategorys     = DB::table('maincategory')
                        ->get();
        $params['maincategorys']    = $maincategorys;
        return View::make('subcategoryAddView', $params);
    }

    public function vendordepotView() {
        $vendordepot    = DB::table('vendordepot')
                    ->leftjoin('country', 'vendordepot.country', '=', 'country.countryid')
                    ->select('vendordepot.*', 'country.longname')
                    ->get();

        $countrys = DB::table('country')
                    ->get();
        $params['vendordepot']  = $vendordepot;
        $params['countrys']     = $countrys;
        return View::make('vendordepotView', $params);
    }

    public function vendordepotUpdate() {
        $id             = $_GET["id"];
        $fieldName      = $_GET["field"];
        $fieldValue     = $_GET["value"]; 

        DB::table('vendordepot')
            ->where('id', '=', $id)
            ->update([
                $fieldName    => $fieldValue
            ]);

        return 'success';
    }

    public function vendordepotAddView() {
        $countrys = DB::table('country')
                    ->get();
        $params['countrys']     = $countrys;
        return View::make('vendordepotAddView', $params);
    }

    public function vendordepotAdd(Request $request) {
        $location       = $request->location;
        $description    = $request->description;
        $address        = $request->address;
        $city           = $request->city;
        $region         = $request->region;
        $plz            = $request->plz;
        $country        = $request->country;
        
        DB::table('vendordepot')
            ->insert([
                'location'      => $location,
                'description'   => $description,
                'address'       => $address,
                'city'          => $city,
                'region'        => $region,
                'plz'           => $plz,
                'country'       => $country
            ]);

        return redirect()->route('vendordepotView');
    }

    public function channelCountryView() {
        $channelCountry = DB::table('tbl_return')
            ->leftjoin('channel', 'tbl_return.channelId', '=', 'channel.idchannel')
            ->select('tbl_return.*', 'channel.shortname')
            ->get();

        $channels = DB::table('channel')
                    ->where('sync', '=', 'Automatic Synch with: eBay')
                    ->get();
        $params['channelCountry']   = $channelCountry;
        $params['channels']         = $channels;
        return View::make('channelCountryView', $params);
    }

    public function channelCountryUpdate() {
        $id             = $_GET["id"];
        $fieldName      = $_GET["field"];
        $fieldValue     = $_GET["value"]; 

        DB::table('tbl_return')
            ->where('id', '=', $id)
            ->update([
                $fieldName    => $fieldValue
            ]);

        return 'success';
    }

    public function channelCountryAddView() {
        $channels = DB::table('channel')
                    ->where('sync', '=', 'Automatic Synch with: eBay')
                    ->get();

        $params['channels']         = $channels;
        return View::make('channelCountryAddView', $params);
    }

    public function channelCountryAdd(Request $request) {
        DB::table('tbl_return')
            ->insert([
                'channelId' => $request->channelId,
                'returnId'  => $request->returnId,
                'country'   => $request->country
            ]);

        return redirect()->route('channelCountryView');
    }

    public function channelCountryDelete($id) {
        DB::table('tbl_return')
            ->where('id', '=', $id)
            ->delete();

        return redirect()->route('channelCountryView');
    }

    public function uploadCompanyLogo(Request $request) {
        $idcompany = $request->idcompany;
        if ($request->file('linklogo')) {
            $file = $request->file('linklogo');            
            $filename = time().'.'.$file->getClientOriginalExtension();
            $path = public_path('assets');
            $file->move($path, $filename);

            DB::table("companyinfo")
                ->where("idcompany", $idcompany)
                ->update([
                    "linklogo"  => $filename
                ]);

        } else {
            $filename = "";
        }

        return Redirect::back();
    }

    public function generalSettingView() {
        $setting    = DB::table('tbl_setting')
                        ->first();

        $params['setting']  = $setting;
        return View::make('generalSettingView', $params);
    }

    public function updateGeneralSetting(Request $request) {
        if($request->file('main_background')) {
            $file       = $request->file('main_background');            
            $mainbackimageName   = time().'.'.$file->getClientOriginalExtension();
            $img        = Image::make($file->getRealPath());
            $path       = public_path('assets');
            $img->save($path.'/images/'.$mainbackimageName);

            DB::table('tbl_setting')
                ->where("id", 1)
                ->update([
                    "main_background"  => $mainbackimageName
                ]);

            Session::put("main_back",   $mainbackimageName);
        }
        
        if($request->file('logo1')) {
            $file       = $request->file('logo1');            
            $logo1imageName   = time().'logo1.'.$file->getClientOriginalExtension();
            $img        = Image::make($file->getRealPath());
            $path       = public_path('assets');
            $img->save($path.'/images/'.$logo1imageName);

            DB::table('tbl_setting')
                ->where("id", 1)
                ->update([
                    "logo1"  => $logo1imageName
                ]);

            Session::put("logo1",       $logo1imageName);
        }

        if($request->file('logo2')) {
            $file           = $request->file('logo2');            
            $logo2imageName = time().'logo2.'.$file->getClientOriginalExtension();
            $img            = Image::make($file->getRealPath());
            $path           = public_path('assets');
            $img->save($path.'/images/'.$logo2imageName);

            DB::table('tbl_setting')
                ->where("id", 1)
                ->update([
                    "logo2"  => $logo2imageName
                ]);

            Session::put("logo2",       $logo2imageName);
        }

        return Redirect::back();
    }
}
