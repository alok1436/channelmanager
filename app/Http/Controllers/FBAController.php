<?php

namespace App\Http\Controllers;
use App\Models\Product;
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
use DNS1D;
use DNS2D;
use CodeItNow\BarcodeBundle\Utils\BarcodeGenerator;

class FBAController extends Controller {
    public function index() {
        $query = DB::table('tbl_fba');

        if(isset($_GET['sort'])) {
            if($_GET['sortItem'] != "") {
                $query->orderby($_GET['sortItem'], $_GET['sort']);
            }
        }
        $query->leftjoin('channel', 'channel.idchannel', '=', 'tbl_fba.channel');
        $query->join('product', 'product.sku', '=', 'tbl_fba.productid');
        $query->select('tbl_fba.*','channel.*');
        if(isset($_GET['keyword'])) {
            $keyword = $_GET['keyword'];
            $query->where('tbl_fba.productid', 'like', '%'.$keyword.'%');
            $params['keyword']   = $keyword;
        }
        $rows = $query->get();

        foreach($rows as $row) {
            $warehouseQnt = DB::table('lagerstand')
                            ->leftjoin('product', 'lagerstand.productid', '=', 'product.productid')
                            ->where('product.sku', '=', $row->productid)
                            ->where('lagerstand.idwarehouse', '=', $row->warehouse)
                            ->get();

            
                           
            $notArrivedQnt = DB::table('fba_shipped')
                            ->where('arrived', '=', 0)
                            ->where('SKU', '=', $row->sku)
                            ->groupby('SKU')
                            ->selectRaw(DB::raw("COALESCE(sum(quantityNotArrived),0) as ontheway"))
                            ->get();

            
            if(count($notArrivedQnt) > 0) {
                $row->ontheway = $notArrivedQnt[0]->ontheway;         
            } else {
                $row->ontheway = 0;
            }

            $row->warehouseQnt = 0;
            if(count($warehouseQnt) == 0) {
                $row->warehouseQnt = 0;    
            } else {
                foreach($warehouseQnt as $item) {
                    $row->warehouseQnt += $item->quantity;    
                }
            }
            
            // if($row->productid == "10240 SYD26W") {
            //     print_r($row->warehouseQnt); 
            // }

            $tmpQnt = intval($row->ideallevel) - intval($row->actuallevel) - intval($row->ontheway);
            if($row->blacklist == 1) {
                $qnt = 0;
            } else {
                if($tmpQnt < 0) {
                    $qnt = 0;
                } else {
                    $qnt = $tmpQnt;
                }
            }

            if($row->active == 1) {
                if($qnt > ($row->warehouseQnt)) {
                    $qnt = ($row->warehouseQnt);
                }
                if($qnt > 0) {
                    if($row->quantitytosend < $qnt) {
                        DB::table('tbl_fba')
                            ->where('idfba', '=', $row->idfba)
                            ->update([
                                'quantitytosend'        => $qnt
                            ]);
                    }
                    $row->quantitytosend = $qnt;
                } else {
                    if($row->quantitytosend == 0) {
                        DB::table('tbl_fba')
                            ->where('idfba', '=', $row->idfba)
                            ->update([
                                'quantitytosend'        => 0
                            ]);
                    }
                    $row->quantitytosend = 0;
                }

                $row->qnt = $qnt;
            } else {
                $row->qnt = 0;
                $row->quantitytosend = 0;
            }
        }
        $params['rows'] = $rows;

        $platformsShort  = DB::table('platform')
                    ->leftjoin('channel', 'channel.platformid', '=', 'platform.platformid')
                    ->where('platform.platformtype', '=', 'Amazon')
                    ->where('channel.fba', '=', 1)
                    ->select('channel.idchannel as channelId', 'channel.platformid', 'channel.shortname as channelname', 'platform.shortname as platformname')
                    ->get();

        $params['platformsShort']   = $platformsShort;
        return View::make('FBAView', $params);
    }

    public function importFBAFile(Request $request) {
        if($request->hasFile('uploadfilename')) {
            $files      = $request->file('uploadfilename');
            $checks     = $request->check;
            $dateTime   = date('Y-m-d H:i:s');
            $fileArrKey = array_keys($files);
            $nonExistingProducts = [];
            for($i = 0; $i < count($fileArrKey); $i++) {
                $path            = $files[$fileArrKey[$i]]->getRealPath();
                $name            = time() . '-' . $files[$fileArrKey[$i]]->getClientOriginalName();
                $path            = storage_path('documents');
                $dir             = storage_path('documents').'/'.$name;
                $files[$fileArrKey[$i]]->move($path, $name);
                $check_idexplode = explode("-", $checks[$i]);
                $platformId      = $check_idexplode[0];
                $channelId       = $check_idexplode[1];

                DB::table('tbl_fba')
                    ->where('channel', '=', $channelId)
                    ->update([
                        'actuallevel'     => 0,
                        'active'        => 0
                    ]);

                $query   = DB::table('channel')
                            ->where('channel.idchannel', '=', $channelId)
                            ->leftjoin('coding', 'channel.codingId', '=', 'coding.codingId')
                            ->get();

                $channel = $query[0];

                $file       = file_get_contents($dir);
                $file       = explode("\n", $file);  
                $totalrows  = count($file);
                
                for($loopfile=1; $loopfile<$totalrows; $loopfile++) {
                    $row    = $file[$loopfile];
                    $row    = explode("\t",$row);

                    if($row[4] == "SELLABLE") {
                        $existingFBA = DB::table('tbl_fba')
                                ->where('asin'      , '=', $row[2])
                                ->where('channel'   , '=', $channelId)
                                ->first();

                        if(empty($existingFBA) && $row[5] > 0) {
                            array_push($nonExistingProducts, $row[2]);
                        } else {
                            DB::table('tbl_fba')
                                ->where('asin'      , '=', $row[2])
                                ->where('channel'   , '=', $channelId)
                                ->update([
                                    'actuallevel'     => $row[5],
                                    'active'          => 1
                                ]);
                        }
                    }
                }
            }
        }

        if(count($nonExistingProducts) > 0) {
            Session::put('noneProducts', $nonExistingProducts);
        }
        
        return redirect()->route('FBAView');
    }

    public function fbaUpdate() {
        $idfba          = $_GET["id"];
        $fieldName      = $_GET["field"];
        $fieldValue     = $_GET["value"]; 

        if($fieldName == "blacklist" && $fieldValue == 1) {
            DB::table('tbl_fba')
                ->where('idfba', '=', $idfba)
                ->update([
                    $fieldName    => $fieldValue,
                    'quantitytosend' => 0
                ]);
        } else {
            DB::table('tbl_fba')
                ->where('idfba', '=', $idfba)
                ->update([
                    $fieldName    => $fieldValue
                ]);
        }
        
        
        $updatedRow = DB::table('tbl_fba')
                        ->where('idfba', '=', $idfba)
                        ->leftjoin('channel', 'channel.idchannel', '=', 'tbl_fba.channel')
                        ->first();
                
        $warehouseQnt = DB::table('lagerstand')
                        ->leftjoin('product', 'lagerstand.productid', '=', 'product.productid')
                        ->where('product.sku', '=', $updatedRow->productid)
                        ->where('lagerstand.idwarehouse', '=', $updatedRow->warehouse)
                        ->first();

        $notArrivedQnt = DB::table('fba_shipped')
                        ->where('arrived', '=', 0)
                        ->where('SKU', '=', $updatedRow->sku)
                        ->groupby('SKU')
                        ->selectRaw(DB::raw("COALESCE(sum(quantityNotArrived),0) as ontheway"))
                        ->get();

        if(count($notArrivedQnt) > 0) {
            $updatedRow->ontheway = $notArrivedQnt[0]->ontheway;         
        } else {
            $updatedRow->ontheway = 0;
        }

        if(empty($warehouseQnt)) {
            $updatedRow->warehouseQnt = 0;    
        } else {
            $updatedRow->warehouseQnt = $warehouseQnt->quantity;    
        }

        $tmpQnt = intval($updatedRow->ideallevel) - intval($updatedRow->actuallevel) - intval($updatedRow->ontheway);

        if($updatedRow->blacklist == 1) {
            $qnt = 0;
            $updatedRow->quantitytosend = 0;
        } else {
            if($tmpQnt < 0) {
                $qnt = 0;
            } else {
                $qnt = $tmpQnt;
            }
        }

        if($qnt > ($updatedRow->warehouseQnt)) {
            $qnt = ($updatedRow->warehouseQnt);
        }

        $updatedRow->qnt = $qnt;
        return json_encode($updatedRow);
    }

    public function fbadelete() {
        $idfba = $_GET['del'];
        DB::table('tbl_fba')
            ->where('idfba', '=', $idfba)
            ->delete();

        return redirect()->route('FBAView');      
    }

    public function fbaShippedUpdate() {
        $id          = $_GET["id"];
        $fieldName      = $_GET["field"];
        $fieldValue     = $_GET["value"]; 

        $order = DB::table('fba_shipped')
                ->where('id', '=', $id)
                ->first();

        $remained_quantity = intval($order->quantityNotArrived)-intval($fieldValue);
        DB::table('fba_shipped')
            ->where('id', '=', $id)
            ->update([
                $fieldName              => $fieldValue,
                'quantityNotArrived'    => $remained_quantity
            ]);
            
        return $remained_quantity;
    }

    public function createExcel() {
        $fbaChannels  = DB::table('channel')
                    ->leftjoin('platform', 'channel.platformid', '=', 'platform.platformid')
                    ->where('channel.fba', '=', 1)
                    ->select('channel.*', 'platform.shortname as platformName')
                    ->get();
        
        foreach($fbaChannels as $channel) {
            $query   = DB::table('tbl_fba')
                    ->join('product', 'product.sku', '=', 'tbl_fba.sku')
                    ->select('tbl_fba.*', 'product.productid as productMainID')
                    ->where('quantitytosend', '>', 0)
                    ->where('tbl_fba.channel', '=', $channel->idchannel);

            $keyword = "";
            $query ->where(function($query) use ($keyword){
                $query->where('tbl_fba.blacklist', '=', 0);
                $query->orwhere('tbl_fba.blacklist', '=', null);
            });
            
            $rows = $query->get();
            
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
            
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $worksheet = $spreadsheet->getActiveSheet();
            $num = 1;
            $uni_id = uniqid();
            foreach($rows as $row) {
                $product = Product::where(['sku'=>$row->sku])->first();
                if($product) {
                    $barcode = new BarcodeGenerator();
                    $barcode->setText($product->ean);
                    $barcode->setType(BarcodeGenerator::Isbn);
                    $barcode->setScale(2);
                    $barcode->setThickness(35);
                    $barcode->setFontSize(9);
                    $code = $barcode->generate();
                    $pngdata = 'data:image/png;base64,'.$code;
                    //echo '<img src="data:image/png;base64,'.$code.'" />';exit();
                    list($type, $pngdata) = explode(';', $pngdata);
                    list(, $pngdata)      = explode(',', $pngdata);
                    $pngdata = base64_decode($pngdata);
                    $file = public_path('export/'.time().uniqid().'.png');
                    File::put($file, $pngdata);
                    for ($n=0; $n < $row->quantitytosend ; $n++) {  
                        $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                        $drawing->setName($product->ean);
                        $drawing->setPath($file); // put your path and image here   
                        $drawing->setCoordinates('A'.$num);
                        $drawing->setOffsetX(5); 
                        $drawing->setOffsetY(12); 
                        $drawing->setWidth(174);
                        $drawing->setWorksheet($spreadsheet->getActiveSheet());
                        $worksheet->getRowDimension($num)->setRowHeight(90);
                        $worksheet->getStyle('A'.$num)->getAlignment()->setIndent(1);
                        
                        $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                        $drawing->setPath(public_path('assets/images/DynaSun_Logo_quadrat.jpg')); // put your path and image here   
                        $drawing->setCoordinates('B'.$num);
                        $drawing->setOffsetX(1); 
                        $drawing->setOffsetY(27); 
                        $drawing->setWidth(72);
                        $drawing->setWorksheet($spreadsheet->getActiveSheet());

                        $num++;
                            $worksheet->getCell('A'.($num))->setValue($product->sku);
                        $num++;
                            $worksheet->getCell('A'.($num))->setValue('');
                        $num++;
                    }

                    DB::table('fba_shipped')
                        ->insert([
                            'SKU'        => $row->sku,
                            'date'       => date('Y-m-d H:i:s'),
                            'quantityNotArrived' => $row->quantitytosend
                        ]);

                    DB::table('tbl_fba')
                        ->where('idfba', '=', $row->idfba)
                        ->update([
                            'quantitytosend' => 0
                        ]);

                    $data .= $row->sku."\t".$row->quantitytosend.PHP_EOL;
                    
                    $today = date('Y-m-d');
                    $date = new \DateTime($today);
                    $week = $date->format("W");
                   
                    if($k == 0) {
                    $insertedId =    DB::table('orderitem')
                            ->insertGetId([
                                'idorderplatform'           => $channel->platformid,
                                'idcompany'                 => $channel->idcompany,
                                'referencechannelname'      => $channel->shortname,
                                'platformname'              => $channel->platformName,
                                'referencechannel'          => $channel->idchannel,
                                'weeksell'                  => $week,
                                //'productid'                 => $row->productid,
                                'notes'                     => $row->sku,
                                'datee'                     => $today,
                                'multiorder'                => 0,
                                'quantity'                  => $row->quantitytosend,
                                'productid'                 => $row->productMainID,
                                'idchannel'                 => $channel->idchannel,
                                'idpayment'                 => 'FBA',
                                'idwarehouse'               => $channel->warehouse,
                                'referenceorder'            => 'FBA_'.$channel->shortname.$uni_id,
                                'customer'                  => 'Amazon',
                                'order_item_id'             => 'FBA_'.$channel->shortname.$uni_id,
                                'inv_vat'                   => $channel->vat
                            ]);
                    }else{
                    DB::table('orderitem')
                            ->insert([
                                'idorderplatform'           => $channel->platformid,
                                'idcompany'                 => $channel->idcompany,
                                'referencechannelname'      => $channel->shortname,
                                'platformname'              => $channel->platformName,
                                'referencechannel'          => $channel->idchannel,
                                'weeksell'                  => $week,
                                //'productid'                 => $row->productid,
                                'notes'                     => $row->sku,
                                'datee'                     => $today,
                                'quantity'                  => $row->quantitytosend,
                                'productid'                 => $row->productMainID,
                                'idchannel'                 => $channel->idchannel,
                                'multiorder'                => 'FBA_'.$channel->shortname.$uni_id,
                                'idpayment'                 => 'FBA',
                                'idwarehouse'               => $channel->warehouse,
                                'referenceorder'            => 'FBA_'.$channel->shortname.$uni_id,
                                'customer'                  => 'Amazon',
                                'order_item_id'             => 'FBA_'.$channel->shortname.$uni_id,
                                'inv_vat'                   => $channel->vat,
                                //'orderitem_id'              => $insertedId,
                            ]);
                    }
                    $k++;
                }else{
                    echo "product not found.";
                }
            }
            $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(26.7);
            $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(11.3);
            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xls');
            $writer->save(public_path('export/'.time() .$channel->shortname.'.xls'));
            
            $fileName = time() .$channel->shortname.'_fba.txt';
            File::put(public_path()."/export/".$fileName, $data);
        }

        $zip        = new ZipArchive;
        $fileName   = time()."_FBA.zip";

        if ($zip->open($fileName, ZipArchive::CREATE) === TRUE) {
            $files = File::files(public_path()."/export/");
            foreach ($files as $key => $value) {
                $file = basename($value);
                if(pathinfo($file, PATHINFO_EXTENSION) !='png'){
                    $zip->addFile($value, $file);
                }
            }
            
            $zip->close();
        }
        $files = File::files(public_path()."/export/");
        foreach ($files as $key => $value) {
            $file = basename($value);
            File::delete(public_path()."/export/".$file);
        }

        Session::flash('download.in.the.next.request', $fileName);
        return Redirect::to('FBAView');
    }

    public function FBAontheway() {
        $data = DB::table('fba_shipped')
                            ->where('arrived', '=', 0)
                            ->get();

        $params['data'] = $data;
        return View::make('FBAshippedView', $params);
    }

    public function setArrived() {
        $id = $_GET['id'];
        DB::table('fba_shipped')
            ->where('id', '=', $id)
            ->update([
                'arrived'        => 1
            ]);

        return 'success';
    }

    public function removeShippedFBA() {
        $id = $_GET['id'];
        DB::table('fba_shipped')
            ->where('id', '=', $id)
            ->delete();

        return 'success';
    }

    public function integrityFBAFile(Request $request) {
        if($request->hasFile('uploadfilename')) {
            $files      = $request->file('uploadfilename');
            $checks     = $request->check;
            $dateTime   = date('Y-m-d H:i:s');
            $fileArrKey = array_keys($files);
            $nonExistingFBAProductsArr = [];
            for($i = 0; $i < count($fileArrKey); $i++) {
                $path            = $files[$fileArrKey[$i]]->getRealPath();
                $check_idexplode = explode("-", $checks[$i]);
                $platformId      = $check_idexplode[0];
                $channelId       = $check_idexplode[1];

                $query   = DB::table('channel')
                            ->where('channel.idchannel', '=', $channelId)
                            ->leftjoin('coding', 'channel.codingId', '=', 'coding.codingId')
                            ->get();

                $channel = $query[0];

                DB::table('tbl_fba')
                    ->where('channel', '=', $channelId)
                    ->update([
                        'active'        => 0
                    ]);

                $file       = file_get_contents($path);
                $file       = explode("\n", $file);  
                $totalrows  = count($file);
                for($loopfile=1; $loopfile<$totalrows; $loopfile++) {
                    $row    = $file[$loopfile];
                    $row    = explode("\t",$row);
                    if(count($row) > 10) {
                        if($row[15] == "AMAZON_EU") {
                            $existingFBA = DB::table('tbl_fba')
                                        ->where('asin'      , '=', $row[1])
                                        ->where('channel'   , '=', $channelId)
                                        ->first();
                                        
                            if(empty($existingFBA)) {
                                DB::table('tbl_fba')
                                    ->insert([
                                        'productid'     => $row[0],
                                        'channel'       => $channelId,
                                        'asin'          => $row[1],
                                        'sku'           => $row[0],
                                        'dateupdate'    => $dateTime,
                                        'active'        => 1
                                    ]);
                            } else {
                                DB::table('tbl_fba')
                                    ->where('asin'   , '=', $row[1])
                                    ->where('channel', '=', $channelId)
                                    ->update([
                                        'active'        => 1
                                    ]);
                            }
                        }
                    }
                }
                //before it was fba products but not it is not
                $nonExistingFBAProducts = DB::table('tbl_fba')
                    ->where('channel', '=', $channelId)
                    ->where('active',  '=', 0)
                    ->get();

                foreach($nonExistingFBAProducts as $item) {
                    array_push($nonExistingFBAProductsArr, $item->asin."-".$item->sku);
                }
            }
        }

        if(count($nonExistingFBAProducts) > 0) {
            Session::put('nonExistingFBAProducts', $nonExistingFBAProductsArr);
        }
        return redirect()->route('FBAView');
    }
}