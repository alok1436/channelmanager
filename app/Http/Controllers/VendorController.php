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
use ZipArchive;
use Session;
use Redirect;
use Image;
use File;

class VendorController extends Controller {
    public function vendorView() {
        if(!Session::has('userID')) {
            return redirect()->route('login');
        }

        $vendors  = DB::table('vendorrequest')
                    ->leftjoin('channel',       'channel.idchannel', '=', 'vendorrequest.seller')
                    ->leftjoin('companyinfo',   'companyinfo.idcompany', '=', 'channel.idcompany')
                    ->leftjoin('vendorblacklist',   'vendorrequest.eanvendor', '=', 'vendorblacklist.eanvendor')
                    ->leftjoin('vendordepot',   'vendorrequest.vendordepot', '=', 'vendordepot.id')
                    ->where('vendorrequest.delete',      '=', 0)
                    ->select('channel.warehouse as warehouse', 'vendordepot.location as location', 'companyinfo.shortname as shortname', 'vendorrequest.*', 'vendorblacklist.comment as blacklistcomment', 'vendorblacklist.idblacklist')
                    ->get();
                    
        $platformsShort  = DB::table('platform')
                    ->leftjoin('channel', 'channel.platformid', '=', 'platform.platformid')
                    ->where('channel.vendor', '=', 1)
                    ->select('channel.idchannel as channelId', 'channel.platformid', 'channel.shortname as channelname', 'platform.shortname as platformname')
                    ->get();
        
        $vendordepot = DB::table('vendordepot')
                    ->get();
        foreach($vendors as $row) {
            $warehouseQnt = DB::table('lagerstand')
                            ->leftjoin('product', 'lagerstand.productid',   '=' , 'product.productid')
                            ->where('product.productid',                    '=' , $row->productid)
                            ->where('lagerstand.idwarehouse',               '=' , $row->warehouse)
                            ->first();
                            
            if(empty($warehouseQnt)) {
                $row->warehouseQnt = 0;    
            } else {
                $row->warehouseQnt = $warehouseQnt->quantity;    
            }
        }

        
        $params['platformsShort']   = $platformsShort;
        $params['vendors']          = $vendors;
        $params['vendordepot']      = $vendordepot;

        return View::make('vendorView', $params);
    }

    public function importVendorFile(Request $request) {
        if($request->hasFile('uploadfilename')) {
            $files      = $request->file('uploadfilename');
            $checks     = $request->check;
            $locations  = $request->location;
            $dateTime   = date('Y-m-d H:i:s');
            $fileArrKey = array_keys($files);
            $nonExistLocation = [];
            for($i = 0; $i < count($fileArrKey); $i++) {
                $path               = $files[$fileArrKey[$i]]->getRealPath();
                $data               = Excel::load($path)->get();
                $check_idexplode    = explode("-", $checks[$i]);
                $platformId         = $check_idexplode[0];
                $channelId          = $check_idexplode[1];
                $location           = $locations[$i];
                $channel   = DB::table('channel')
                                    ->where('channel.idchannel', '=', $channelId)
                                    ->leftjoin('coding', 'channel.codingId', '=', 'coding.codingId')
                                    ->first();
                if($location == "") {
                    array_push($nonExistLocation, $channel->shortname);
                } else {
                    $nonProducts = [];
                    foreach($data as $row) {
                        $asin       = $row['asin'];
                        $sku        = $row['model_number'];
                        $title      = $row['title'];
                        $quantity   = $row['quantity_requested'];
                        $cost       = $row['unit_cost'];
                        $ean        = $row['external_id'];
                        
                        $product = DB::table('product')
                                    ->where('ean', '=', $ean)
                                    ->first();

                        if(empty($product)) {
                            array_push($nonProducts, $sku);
                            $productId = DB::table('product')
                                ->insertGetId([
                                    'asin'          => $asin,
                                    'ean'           => $ean,
                                    'sku'           => $sku
                                ]);
                        } else {
                            $productId = $product->productid;
                        }

                        DB::table('vendorrequest')
                                ->insert([
                                    'seller'            => $channel->idchannel,
                                    'productid'         => $productId,
                                    'asinvendor'        => $asin,
                                    'eanvendor'         => $ean,
                                    'skuvendor'         => $sku,
                                    'cost'              => $cost,
                                    'quantitySubmitted' => $quantity,
                                    'vendordepot'       => $location,
                                    'title'             => $title
                                ]);
                    }

                    if(count($nonProducts) > 0) {
                        Session::put('noneProducts', $nonProducts);
                    }
                }
            }

            if(count($nonExistLocation) > 0) {
                Session::put('nonExistLocation', $nonExistLocation);
            }
        }
        
        return redirect()->route('vendorView');
    }

    public function vendorUpdate() {
        $idvendor       = $_GET["id"];
        $fieldName      = $_GET["field"];
        $fieldValue     = $_GET["value"]; 

        if($fieldName == 'send' && $fieldValue == 1) {
            $vendor = DB::table('vendorrequest')
                    ->where('idvendor', '=', $idvendor)
                    ->first();

            DB::table('vendorrequest')
                ->where('idvendor', '=', $idvendor)
                ->update([
                    $fieldName    => $fieldValue,
                    'accepted_quantity' => $vendor->quantitySubmitted
                ]);
        } else if($fieldName == 'send' && $fieldValue == 0) {
            $vendor = DB::table('vendorrequest')
                    ->where('idvendor', '=', $idvendor)
                    ->first();

            DB::table('vendorrequest')
                ->where('idvendor', '=', $idvendor)
                ->update([
                    $fieldName    => $fieldValue,
                    'accepted_quantity' => 0
                ]);
        } else {
            DB::table('vendorrequest')
                ->where('idvendor', '=', $idvendor)
                ->update([
                    $fieldName    => $fieldValue
                ]);
        }

        $vendor = DB::table('vendorrequest')
                    ->where('idvendor', '=', $idvendor)
                    ->first();

        return json_encode($vendor);
    }

    public function sendvendororder() {
        $vendorChannels  = DB::table('channel')
                    ->leftjoin('platform', 'channel.platformid', '=', 'platform.platformid')
                    ->where('channel.vendor', '=', 1)
                    ->select('channel.*', 'platform.shortname as platformName')
                    ->get();

        foreach($vendorChannels as $channel) {
            $rows   = DB::table('vendorrequest')
                    ->leftjoin('product', 'product.ean', '=', 'vendorrequest.eanvendor')
                    ->leftjoin('vendorblacklist',   'vendorrequest.eanvendor', '=', 'vendorblacklist.eanvendor')
                    ->leftjoin('vendordepot',   'vendordepot.id', '=', 'vendorrequest.vendordepot')
                    ->where('vendorrequest.seller',      '=', $channel->idchannel)
                    ->where('vendorrequest.delete',      '=', 0)
                    ->select('vendorrequest.*', 'vendordepot.location', 'product.productid as productMainID', 'vendorblacklist.idblacklist')
                    ->orderby('vendorrequest.vendordepot', 'desc')
                    ->get();

            $company = DB::table('companyinfo')
                    ->where('idcompany', '=', $channel->idcompany)
                    ->first();

            $timeArr = explode(" ", date('Y-m-d H:i:s'));
            $data = "PlanName\tVondor_".$channel->shortname."_".$timeArr[0]."_".$timeArr[1].PHP_EOL;
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
            $vendordepot = 0;
            $datetime = str_replace(" ", "_", date('Y-m-d H:i:s'));
            foreach($rows as $row) {
                if($row->accepted_quantity > 0 && $row->send == 1) {
                    if($vendordepot != $row->vendordepot) {
                        $vendordepot = $row->vendordepot;
                        $k=0;
                    }
                    DB::table('vendorrequest')
                        ->where('idvendor', '=', $row->idvendor)
                        ->update([
                            'delete'                => 1
                        ]);

                    $data .= $row->skuvendor."\t".$row->quantitySubmitted.PHP_EOL;
                    
                    $today  = date('Y-m-d');
                    $date   = new \DateTime($today);
                    $week   = $date->format("W");

                    $referenceorder = 'VENDOR_'.$channel->shortname.'_'.$row->location.'_'.$datetime;
                    if($k == 0) {
                        DB::table('orderitem')
                            ->insert([
                                'idorderplatform'           => $channel->platformid,
                                'idcompany'                 => $channel->idcompany,
                                'referencechannelname'      => $channel->shortname,
                                'platformname'              => $channel->platformName,
                                'referencechannel'          => $channel->idchannel,
                                'weeksell'                  => $week,
                                'notes'                     => $row->skuvendor,
                                'datee'                     => $today,
                                'quantity'                  => $row->quantitySubmitted,
                                'productid'                 => $row->productMainID,
                                'idchannel'                 => $channel->idchannel,
                                'idpayment'                 => 'VENDOR',
                                'sum'                       => $row->cost,
                                'idwarehouse'               => $channel->warehouse,
                                'referenceorder'            => 'VENDOR_'.$referenceorder,
                                'customer'                  => 'Amazon - '.$row->location,
                                'order_item_id'             => 'VENDOR_'.$referenceorder,
                                'inv_vat'                   => $channel->vat
                            ]);
                    } else {
                        DB::table('orderitem')
                            ->insert([
                                'idorderplatform'           => $channel->platformid,
                                'idcompany'                 => $channel->idcompany,
                                'referencechannelname'      => $channel->shortname,
                                'platformname'              => $channel->platformName,
                                'referencechannel'          => $channel->idchannel,
                                'weeksell'                  => $week,
                                'notes'                     => $row->skuvendor,
                                'datee'                     => $today,
                                'quantity'                  => $row->quantitySubmitted,
                                'productid'                 => $row->productMainID,
                                'idchannel'                 => $channel->idchannel,
                                'multiorder'                => 'VENDOR_'.$referenceorder,
                                'idpayment'                 => 'VENDOR',
                                'idwarehouse'               => $channel->warehouse,
                                'referenceorder'            => 'VENDOR_'.$referenceorder,
                                'customer'                  => 'Amazon - '.$row->location,
                                'order_item_id'             => 'VENDOR_'.$referenceorder,
                                'sum'                       => $row->cost,
                                'inv_vat'                   => $channel->vat
                            ]);
                    }
                    $k++;
                } else {
                    DB::table('vendorrequest')
                        ->where('idvendor', '=', $row->idvendor)
                        ->update([
                            'delete'                => 1
                        ]);
                }
            }
    
            $fileName = time() .$channel->shortname.'_vendor.txt';
            File::put(public_path()."/export/".$fileName, $data);
        }
        $zip        = new ZipArchive;
        $fileName   = time()."_VENDOR.zip";

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
        return redirect()->route('vendorView');
    }

    public function vendorBlacklist() {
        $vendorBlacklist = DB::table('vendorblacklist')
                            ->leftjoin('channel',       'channel.idchannel', '=', 'vendorblacklist.channelid')
                            ->leftjoin('product',       'vendorblacklist.eanvendor', '=', 'product.ean')
                            ->select('vendorblacklist.*', 'channel.shortname', 'product.sku')
                            ->get();

        $params['vendorBlacklist']   = $vendorBlacklist;
        return View::make('vendorBlacklistView', $params);
    }

    public function vendorblacklistaddview() {
        $channels  = DB::table('channel')
                    ->where('channel.vendor', '=', 1)
                    ->get();

        $blacklistProducts = DB::table('vendorblacklist')
                    ->select('eanvendor')
                    ->get();
        
        $eanvendorArr = [];
        foreach($blacklistProducts as $item) {
            array_push($eanvendorArr, $item->eanvendor);
        }

        $products   = DB::table('product')
                    ->whereNotIn('ean', $eanvendorArr)
                    ->select('ean', 'sku')
                    ->get();
                    
        $params['channels']   = $channels;
        $params['products']   = $products;
        return View::make('vendorblacklistaddview', $params);
    }

    public function vendorblacklistadd(Request $request) {
        $channelId  = $request->channel;
        $ean        = $request->ean;
        $comment    = $request->comment;

        DB::table('vendorblacklist')
            ->insert([
                'channelid'    => $channelId,
                'eanvendor'    => $ean,
                'comment'      => $comment
            ]);

        return redirect()->route('vendorBlackList');
    }

    public function deletevendorblacklist($idblacklist) {
        DB::table('vendorblacklist')
            ->where('idblacklist', '=', $idblacklist)
            ->delete();

        return redirect()->route('vendorBlackList');
    }
}
