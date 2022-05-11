<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function csvToArray($filename = '', $delimiter = ',') {
        if (!file_exists($filename) || !is_readable($filename))
            return false;

        $header = null;
        $data = array();
        if (($handle = fopen($filename, 'r')) !== false)
        {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {
                array_push($data, $row);
            }
            fclose($handle);
        }

        return $data;
    }

    public function product_check_insert($name, $sku, $idwarehouse, $quantity) {
        if (!empty($name)) {
            $row = DB::table('product')
                ->where('modelcode', '=', $name)
                ->get();

            if(count($row) > 0) {
                $id = $row[0]->productid;
                $lager = DB::table('lagerstand')
                    ->where('productid', '=', $id)
                    ->where('idwarehouse', '=', $idwarehouse)
                    ->get();

                if(count($lager) == 0) {
                    if($id != "" && $id != 0 && $id != null) {
                        DB::table('lagerstand')
                            ->insert([
                                'productid'     => $id,
                                'idwarehouse'   => $idwarehouse,
                                'quantity'      => 0
                            ]);
                    }
                }

                return $id;
            } else {
                $id = DB::table('product')
                    ->insertGetId([
                        'modelcode'     => $name,
                        'nameshort'     => $name,
                        'namelong'      => $name,
                        'sku'           => $sku,
                        "active"        =>"Yes",
                        "virtualkit"    =>"No"
                    ]);
                
                if($id != "" && $id != 0 && $id != null) {
                    DB::table('lagerstand')
                        ->insert([
                            'productid'     => $id,
                            'idwarehouse'   => $idwarehouse,
                            'quantity'      => 0
                        ]);
                }
                    
                return $id;
            }
        } else {
            return $name;
        }
    }

    public function isDuplicate($referenceorder, $order_item_id, $productId){
        $query = DB::table('orderitem')
                ->where('referenceorder', '=', $referenceorder)
                ->where('order_item_id' , '=', $order_item_id)
                ->where('productid'     , '=', $productId)
                ->get();

        $checkIsDuplicate = count($query);
        return $checkIsDuplicate;
    }

    public function product_check_insert_two($fieldname, $fieldvalue, $idwarehouse, $quantity, $sku) {
        if(!empty($fieldvalue)) {
            $row = DB::table('product')
                ->where($fieldname, '=', $fieldvalue)
                ->get();
			if(count($row) > 0) {
				$id         = $row[0]->productid;
                $modelcode  = $row[0]->modelcode;
                
                
                $lager = DB::table('lagerstand')
                        ->where('productid', '=', $id)
                        ->where('idwarehouse', '=', $idwarehouse)
                        ->get();

                if(count($lager) == 0) {
                    if($id != "" && $id != 0 && $id != null) {
                        DB::table('lagerstand')
                            ->insert([
                                'productid'     => $id,
                                'idwarehouse'   => $idwarehouse,
                                'quantity'      => 0
                            ]);
                    }
                }
                return $id;
             } else {
                 $id = DB::table('product')
                    ->insertGetId([
                        "modelcode"  => $sku,
                        "ean"        => $sku,
                        "sku"        => $sku,
                        "nameshort"  => $sku,
                        "namelong"   => $sku,
                        "active"     => "Yes",
                        "virtualkit" => "No",
                    ]);

                if($id != "" && $id != 0 && $id != null) {
                    DB::table('lagerstand')
                        ->insert([
                            'productid'     => $id,
                            'idwarehouse'   => $idwarehouse,
                            'quantity'      => 0
                        ]);
                }

                return $id;
            }
        } else {
            return $sku;
        }
    }

    public function putt_data($rows){
        $weeksell    = $rows['weeksell'];
        $date        = $rows['date'];
        $productid   = '12302';
        $idwarehouse = $rows['idwarehouse'];
        $quantity    = $rows['quantity'];
        $country     = $rows['country'];
        $checksoldw  = "SELECT * FROM `soldweekly` WHERE weeksell='$weeksell' AND productid='$productid' AND idwarehouse ='$idwarehouse'";
        $return_soldw = query($checksoldw);
    }

    public function dhl_csv(){
        $file = fopen ('courier_csv/DHL.csv','w');
        // send the column headers
        fputcsv($file, array('Kundennummer', 'EbayName', 'Versandart', 'Versandkosten','Zahlart','Firma','Anrede','Name','Strasse','Land','PLZ','Ort','Telefon','fax',
                                'email','auktionsgruppe','Lieferanschrift','LFirma','Lname','Lstrasse','Lplz','Lort','LLand','Gruppensumme','Zahlartenaufschlag','Versandgruppe',
                                'Gewicht','ReNr','EbayVersand','Zinfo','ISOLLand','ISOLand','EKNummer','Anschrift2','Lanschrift2','Logistikerversandart','Artikelnummer','Artikelname',
                                'EAN','MengeEAN','Bundesland','LBundesland','Memo','MarkierungsID','MengeProduktID','LTelefon','Produkt','Teilnahme')
                );
        
        $select = DB::table('orderitem')
                ->where('carriername', '=', 'DHL')
                ->where('courierinformedok', '=', 0)
                ->where('multiorder', '=', '0')
                ->where('idpayment', '!=', '')
                ->where('idpayment', '!=', 'Not Paid')
                ->get();
        
        if (count($select) > 0){
            // loop over the rows, outputting them
            foreach($select as $rows) {
                $quentiti   = $rows->quantity;
                $idorder    = $rows->idorder;
                $multiorder = $rows->multiorder;
                $waerhouse  = $rows->idwarehouse;

                if(strlen($rows->plz) == 3) {
                    $rows->plz = "00".$rows->plz;
                } else if(strlen($rows->plz) == 4) {
                    $rows->plz = "0".$rows->plz;
                } else if(strlen($rows->plz) == 2) {
                    $rows->plz = "000".$rows->plz;
                }

                $customerName = $rows->customer;
                $inv_address1 = $rows->inv_address1;
                $inv_address2 = $rows->inv_address2;
                $plz = $rows->plz;
                $city = $rows->city;
                $telefon = $rows->telefon;
                $country = $rows->country;
                $productid = $rows->productid;
                $gewicht  = "3";         
                $row = array(
                    '',
                    '',
                    '',
                    '',
                    '',
                    $rows->customer,
                    '',
                    $rows->inv_address1,
                    $rows->inv_address2,
                    '',
                    $rows->plz,
                    $rows->city,
                    $rows->telefon,
                    '','','','','','','',
                    '','','','','','','3',
                    '','','',
                    $rows->country,
                    '','','','','',
                    $rows->productid,'',
                    '','','','','','','','','',''
                );
                
                if($rows->address1 != "" && $rows->address1 != null) {
                    if($rows->multiorder == "0") {
                        fputcsv($file, $row);
                    }
                    
                    DB::table('orderitem')
                        ->where('idorder', '=', $idorder)
                        ->update([
                            "courierinformedok"     => 0,
                            "printedshippingok"     => 0,
                            "registeredtosolddayok" => 0
                        ]);
    
                    $rowssss = DB::table('product')
                            ->where('productid', '=', $rows->productid)
                            ->first();
    
                    $rowlager = DB::table('lagerstand')
                            ->where('productid', '=', $rows->productid)
                            ->where('idwarehouse', '=', $waerhouse)
                            ->first();
                    /* ADD LAGERSTAND RECORD */ 
                   
                    if (empty($rowlager)){
                        $reslager = DB::table('lagerstand')
                                ->insert([
                                    "productid"     => $rows->productid,
                                    "idwarehouse"   => $waerhouse,
                                    "quantity"      => (-1)*$quentiti
                                ]);
                    } else {
                        $quan = $rowlager->quantity - $quentiti;
                        DB::table('lagerstand')
                            ->where('productid',    '=', $rows->productid)
                            ->where('idwarehouse',  '=', $waerhouse)
                            ->update(['quantity'=>$quan]);
                    }
                    /* ADD LAGERSTAND RECORD */
    
                    $existedCheck = DB::table('soldweekly')
                        ->where('productid',    '=', $rows->productid)
                        ->where('idwarehouse',  '=', $waerhouse)
                        ->where('weeksell',     '=', $rows->weeksell)
                        ->first();
                    
                    if(empty($existedCheck)) {
                        $added = DB::table('soldweekly')
                            ->insert([
                                'idwarehouse'   => $waerhouse,
                                'productid'     => $rows->productid,
                                'quantity'      => $quentiti,
                                'weeksell'      => $rows->weeksell,
                                'country'       => $rows->country,
                                'year'          => date('Y')
                            ]);
    
                    } else {
                        DB::table('soldweekly')
                            ->where('productid',    '=', $rows->productid)
                            ->where('idwarehouse',  '=', $waerhouse)
                            ->increment('quantity', $quentiti);
                    }
    
                    //get multiorders
                    $multiOrders = DB::table('orderitem')
                                    ->where('multiorder', '=', $rows->referenceorder)
                                    ->get();
                    if(false) {
                        foreach($multiOrders as $order) {
                            $quentiti   = $order->quantity;
                            $idorder    = $order->idorder;
                            $multiorder = $order->multiorder;
                            $waerhouse  = $order->idwarehouse;
                            
                            
                            $customerName  .= $order->customer."\n";
                            $inv_address1  .= $order->inv_address1."\n";
                            $inv_address2  .= $order->inv_address2."\n";
                            $plz  .= $order->plz."\n";
                            $city  .= $order->city."\n";
                            $telefon  .= $order->telefon."\n";
                            $country  .= $order->country."\n";
                            $productid  .= $order->productid."\n";
                            $gewicht  .= "3\n";
                            
        
                            DB::table('orderitem')
                                ->where('idorder', '=', $idorder)
                                ->update([
                                    "courierinformedok"     => 1,
                                    "printedshippingok"     => 0,
                                    "registeredtosolddayok" => 0
                                ]);
        
                            $rowssss = DB::table('product')
                                    ->where('productid', '=', $order->productid)
                                    ->first();
        
                            $rowlager = DB::table('lagerstand')
                                    ->where('productid', '=', $order->productid)
                                    ->where('idwarehouse', '=', $waerhouse)
                                    ->first();
                            /* ADD LAGERSTAND RECORD */ 
        
                            if (empty($rowlager)){
                                if($order->productid != "" && $order->productid != 0 && $order->productid != null) {
                                    $quentiti = -1*$quentiti;
                                    $reslager = DB::table('lagerstand')
                                            ->insert([
                                                "productid"     => $order->productid,
                                                "idwarehouse"   => $waerhouse,
                                                "quantity"      => $quentiti
                                            ]);
                                }
                            } else {
                                $quan = $rowlager->quantity - $quentiti;
                                DB::table('lagerstand')
                                    ->where('productid',    '=', $order->productid)
                                    ->where('idwarehouse',  '=', $waerhouse)
                                    ->update(['quantity'=>$quan]);
                            }


                            /* ADD LAGERSTAND RECORD */
        
                            $existedCheck = DB::table('soldweekly')
                                ->where('productid',    '=', $order->productid)
                                ->where('idwarehouse',  '=', $waerhouse)
                                ->where('weeksell',     '=', $order->weeksell)
                                ->first();
                            
                            if(empty($existedCheck)) {
                                $added = DB::table('soldweekly')
                                    ->insert([
                                        'idwarehouse'   => $waerhouse,
                                        'productid'     => $order->productid,
                                        'quantity'      => $quentiti,
                                        'weeksell'      => $order->weeksell,
                                        'country'       => $order->country
                                    ]);
        
                            } else {
                                DB::table('soldweekly')
                                    ->where('productid',    '=', $order->productid)
                                    ->where('idwarehouse',  '=', $waerhouse)
                                    ->increment('quantity', $quentiti);
                            }
                        }
                    }
                }
           }
        }
    }
    public function sda_csv(){
        $file = fopen ('courier_csv/SDA.csv','w');
    
        // send the column headers
        fputcsv($file, array('VABRMN;VABRSD;VABIND;VABLOD;VABNZD;VABCAD;VABNCL;VABPKB;VABCCM;VABCBO;VABCTR;VABTSP;VABCAS;VABTIC;VABVCA;VABCTM;VABRD2;VABPRD'));
        //query the database
        $select2 = DB::table('orderitem')
                    ->where('carriername', '=', 'SDA')
                    ->where('courierinformedok', '=', 0)
                    ->where('idpayment', '!=', '')
                    ->where('idpayment', '!=', 'Not Paid')
                    ->get();
        
        $sdaCourier   = DB::table('shippingmodel')
                        ->where('shortname', '=', 'SDA')
                        ->first();
        if (count($select2) > 0) {
            $i=1;   
            // loop over the rows, outputting them
            foreach($select2 as $rows) {
                if($rows->address1 != "" && $rows->address1 != null) {
                    //$quentiti = $rows['quantity'];
                    $idorder2   = $rows->idorder;
                    $quentiti   = $rows->quantity;
                    $idorder    = $rows->idorder;
                    $multiorder = $rows->multiorder;
                    $waerhouse  = $rows->idwarehouse;

                    $province = $rows->region;
                    if(strlen($rows->region) > 2) {
                        $region = DB::table('tbl_province')
                                    ->where('long_code', '=', $rows->region)
                                    ->first();
                        
                        if(!empty($region)) {
                            $province = $region->short_code;
                        }
                    }

                    if(strlen($rows->plz) == 3) {
                        $rows->plz = "00".$rows->plz;
                    } else if(strlen($rows->plz) == 4) {
                        $rows->plz = "0".$rows->plz;
                    } else if(strlen($rows->plz) == 2) {
                        $rows->plz = "000".$rows->plz;
                    }

                    $row = array(
                        $i,
                        str_replace('"','',$rows->customer),
                        $rows->inv_address1,
                        $rows->city,
                        '',
                        $rows->plz,
                        $rows->groupshipping,
                        $sdaCourier->vabpkb, $sdaCourier->vabccm, $sdaCourier->vabcbo,
                        $sdaCourier->vabctr, $sdaCourier->vabtsp,
                        $rows->referenceorder,'','','','',
                        $province,
                    );

                    //fputcsv($file2, $row2,';','"','"');
                    if($rows->multiorder == '0') {
                        fputs($file, implode(';', $row)."\n");
                    }

                    DB::table('orderitem')
                        ->where('idorder', '=', $idorder)
                        ->update([
                            "courierinformedok"     => 1,
                            "printedshippingok"     => 0,
                            "registeredtosolddayok" => 0
                        ]);
                        
                    $rowssss = DB::table('product')
                        ->where('productid', '=', $rows->productid)
                        ->first();

                    $rowlager = DB::table('lagerstand')
                            ->where('productid', '=', $rows->productid)
                            ->where('idwarehouse', '=', $waerhouse)
                            ->first();
                    /* ADD LAGERSTAND RECORD */ 

                    if (empty($rowlager)){
                        if($rows->productid != "" && $rows->productid != 0 && $rows->productid != null) {
                            $quentiti = -1*$quentiti;
                            $reslager = DB::table('lagerstand')
                                    ->insert([
                                        "productid"     => $rows->productid,
                                        "idwarehouse"   => $waerhouse,
                                        "quantity"      => $quentiti
                                    ]);
                        }
                    } else {
                        DB::table('lagerstand')
                            ->where('productid',    '=', $rows->productid)
                            ->where('idwarehouse',  '=', $waerhouse)
                            ->decrement('quantity', $quentiti);
                    }
                    /* ADD LAGERSTAND RECORD */
                    
                    $existedCheck = DB::table('soldweekly')
                        ->where('productid',    '=', $rows->productid)
                        ->where('idwarehouse',  '=', $waerhouse)
                        ->where('weeksell',     '=', $rows->weeksell)
                        ->first();
                    if(empty($existedCheck)) {
                        DB::table('soldweekly')
                            ->insert([
                                'idwarehouse'   => $waerhouse,
                                'productid'     => $rows->productid,
                                'weeksell'      => $rows->weeksell,
                                'quantity'      => $quentiti,
                                'country'       => $rows->country,
                                'year'          => date('Y')
                            ]);
                    } else {
                        DB::table('soldweekly')
                            ->where('productid',    '=', $rows->productid)
                            ->where('idwarehouse',  '=', $waerhouse)
                            ->increment('quantity', $quentiti);
                    }
                    $i++;
                    //get multiorders
                    // $multiOrders = DB::table('orderitem')
                    //     ->where('multiorder', '=', $rows->referenceorder)
                    //     ->get();
                    // foreach($multiOrders as $order) {
                    //     $idorder2   = $order->idorder;
                    //     $quentiti   = $order->quantity;
                    //     $idorder    = $order->idorder;
                    //     $multiorder = $order->multiorder;
                    //     $waerhouse  = $order->idwarehouse;
                    //     $row = array(
                    //         $i,
                    //         str_replace('"','',$order->customer),
                    //         $order->inv_address1,
                    //         $order->city,'',
                    //         $order->plz, $rows->groupshipping,
                    //         $sdaCourier->vabpkb, $sdaCourier->vabccm, $sdaCourier->vabcbo,
                    //         $sdaCourier->vabctr, $sdaCourier->vabtsp,
                    //         '','','','','',
                    //         $order->country,
                    //     );

                    //     if($order->multiorder == "0") {
                    //         fputcsv($file2, $row2,';','"','"');
                    //     }
                    //     //fputs($file, implode(';', $row)."\n");
                    //     DB::table('orderitem')
                    //         ->where('idorder', '=', $idorder)
                    //         ->update([
                    //             "courierinformedok"     => 1,
                    //             "printedshippingok"     => 0,
                    //             "registeredtosolddayok" => 0
                    //         ]);
                            
                    //     $rowssss = DB::table('product')
                    //         ->where('productid', '=', $order->productid)
                    //         ->first();

                    //     $rowlager = DB::table('lagerstand')
                    //             ->where('productid', '=', $order->productid)
                    //             ->where('idwarehouse', '=', $waerhouse)
                    //             ->first();
                    //     /* ADD LAGERSTAND RECORD */ 

                    //     if (empty($rowlager)){
                    //         if($order->productid != "" && $order->productid != 0 && $order->productid != null) {
                    //             $reslager = DB::table('lagerstand')
                    //                     ->insert([
                    //                         "productid"     => $order->productid,
                    //                         "idwarehouse"   => $waerhouse,
                    //                         "quantity"      => 0
                    //                     ]);
                    //         }
                    //     }
                    //     /* ADD LAGERSTAND RECORD */
                        
                    //     DB::table('lagerstand')
                    //         ->where('productid',    '=', $order->productid)
                    //         ->where('idwarehouse',  '=', $waerhouse)
                    //         ->decrement('quantity', $quentiti);
                        
                    //     $existedCheck = DB::table('soldweekly')
                    //         ->where('productid',    '=', $order->productid)
                    //         ->where('idwarehouse',  '=', $waerhouse)
                    //         ->where('weeksell',     '=', $order->weeksell)
                    //         ->first();
                    //     if(empty($existedCheck)) {
                    //         DB::table('soldweekly')
                    //             ->insert([
                    //                 'idwarehouse'   => $waerhouse,
                    //                 'productid'     => $order->productid,
                    //                 'weeksell'      => $order->weeksell,
                    //                 'quantity'      => $quentiti,
                    //                 'country'       => $order->country
                    //             ]);
                    //     } else {
                    //         DB::table('soldweekly')
                    //             ->where('productid',    '=', $order->productid)
                    //             ->where('idwarehouse',  '=', $waerhouse)
                    //             ->increment('quantity', $quentiti);
                    //     }
                    //     $i++;
                    // }
                }
            }
        }
    }

    public function sda_csv_old(){
        //query the database
        $select2 = DB::table('orderitem')->where('carriername', '=', 'SDA')->where('courierinformedok', '=', 0)->where('idpayment', '!=', '')
                    ->where('multiorder', '=', '0')
                    ->where('idpayment', '!=', 'Not Paid')
                    ->get();
     
        $sdaCourier   = DB::table('shippingmodel')->where('shortname', '=', 'SDA')->first();
        $headers = array('VABRMN','VABRSD','VABIND','VABLOD','VABNZD','VABCAD','VABNCL','VABPKB','VABCCM','VABCBO','VABCTR','VABTSP','VABCAS','VABTIC','VABVCA','VABCTM','VABRD2','VABPRD');
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();
        if (count($select2) > 0) {
            $i=1;
            foreach(range('A','R') as $index=>$v){
                $worksheet->getCell($v.($i))->setValue($headers[$index]);
            }
            $i++;
            // loop over the rows, outputting them
            $num = 1;
            foreach($select2 as $rows) {
                if($rows->address1 != "" && $rows->address1 != null) {
                    //$quentiti = $rows['quantity'];
                    $idorder2   = $rows->idorder;
                    $quentiti   = $rows->quantity;
                    $idorder    = $rows->idorder;
                    $multiorder = $rows->multiorder;
                    $waerhouse  = $rows->idwarehouse;

                    $province = $rows->region;
                    if(strlen($rows->region) > 2) {
                        $region = DB::table('tbl_province')->where('long_code', '=', $rows->region)->first();
                        if(!empty($region)) {
                            $province = $region->short_code;
                        }
                    }

                    if(strlen($rows->plz) == 3) {
                        $rows->plz = "00".$rows->plz;
                    } else if(strlen($rows->plz) == 4) {
                        $rows->plz = "0".$rows->plz;
                    } else if(strlen($rows->plz) == 2) {
                        $rows->plz = "000".$rows->plz;
                    }

                    $customerName   = str_replace('"','',$rows->customer);
                    $inv_address1   = $rows->inv_address1;
                    $city           = $rows->city;
                    $plz            = $rows->plz;
                    $groupshipping  = $rows->groupshipping;
                    $vabpkb         = $sdaCourier->vabpkb;
                    $vabccm         = $sdaCourier->vabccm;
                    $vabcbo         = $sdaCourier->vabcbo;
                    $vabctr         = $sdaCourier->vabctr;
                    $vabtsp         = $sdaCourier->vabtsp;
                    $referenceorder = $rows->referenceorder;
                    $provinceCell       = $province;

                    DB::table('orderitem')->where('idorder', '=', $idorder)->update([
                            "courierinformedok"     => 1,
                            "printedshippingok"     => 0,
                            "registeredtosolddayok" => 0
                        ]); 
                    $rowssss = DB::table('product')->where('productid', '=', $rows->productid)->first();
                    $rowlager = DB::table('lagerstand')->where('productid', '=', $rows->productid)->where('idwarehouse', '=', $waerhouse)->first();
                    /* ADD LAGERSTAND RECORD */
                    if (empty($rowlager)){
                        $reslager = DB::table('lagerstand')->insert([
                                    "productid"     => $rows->productid,
                                    "idwarehouse"   => $waerhouse,
                                    "quantity"      => (-1)*$quentiti
                                ]);
                    } else {
                        $quan = $rowlager->quantity - $quentiti;
                        DB::table('lagerstand')
                            ->where('productid',    '=', $rows->productid)
                            ->where('idwarehouse',  '=', $waerhouse)
                            ->update(['quantity'=>$quan]);
                    }
                    
                    /* ADD LAGERSTAND RECORD */
                    $existedCheck = DB::table('soldweekly')
                        ->where('productid',    '=', $rows->productid)
                        ->where('idwarehouse',  '=', $waerhouse)
                        ->where('weeksell',     '=', $rows->weeksell)
                        ->first();
                    if(empty($existedCheck)) {
                        DB::table('soldweekly')->insert([
                                'idwarehouse'   => $waerhouse,
                                'productid'     => $rows->productid,
                                'weeksell'      => $rows->weeksell,
                                'quantity'      => $quentiti,
                                'country'       => $rows->country,
                                'year'          => date('Y')
                            ]);
                    } else {
                        DB::table('soldweekly')->where('productid',    '=', $rows->productid)->where('idwarehouse',  '=', $waerhouse)
                            ->increment('quantity', $quentiti);
                    }
                  //  $i++;
                    //get multiorders
                    $multiOrders = DB::table('orderitem')
                        ->where('multiorder', '=', $rows->referenceorder)
                        ->get();
                    if(false){
                        foreach($multiOrders as $order) {

                            $idorder2   = $order->idorder;
                            $quentiti   = $order->quantity;
                            $idorder    = $order->idorder;
                            $multiorder = $order->multiorder;
                            $waerhouse  = $order->idwarehouse;

                            $customerName   .= "\r\n".str_replace('"','',$order->customer);
                            $inv_address1   .= "\r\n".$order->inv_address1;
                            $city           .= "\r\n".$order->city;
                            $plz            .= "\r\n".$order->plz;
                            $groupshipping  .= "\r\n".$order->groupshipping;
                            $vabpkb         .= "\r\n".$sdaCourier->vabpkb;
                            $vabccm         .= "\r\n".$sdaCourier->vabccm;
                            $vabcbo         .= "\r\n".$sdaCourier->vabcbo;
                            $vabctr         .= "\r\n".$sdaCourier->vabctr;
                            $vabtsp         .= "\r\n".$sdaCourier->vabtsp;
                            $referenceorder .= "\r\n".$order->referenceorder;
                            $provinceCell       .= "\r\n".$order->country;

                            DB::table('orderitem')->where('idorder', '=', $idorder)->update([
                                    "courierinformedok"     => 1,
                                    "printedshippingok"     => 0,
                                    "registeredtosolddayok" => 0
                                ]);
                                
                            $rowssss = DB::table('product')
                                ->where('productid', '=', $order->productid)
                                ->first();

                            $rowlager = DB::table('lagerstand')->where('productid', '=', $order->productid)->where('idwarehouse', '=', $waerhouse)
                                    ->first();
                            /* ADD LAGERSTAND RECORD */
                            if (empty($rowlager)){
                                if($order->productid != "" && $order->productid != 0 && $order->productid != null) {
                                    $reslager = DB::table('lagerstand')
                                            ->insert([
                                                "productid"     => $order->productid,
                                                "idwarehouse"   => $waerhouse,
                                                "quantity"      => 0
                                            ]);
                                }
                            }

                            /* ADD LAGERSTAND RECORD */
                            DB::table('lagerstand')->where('productid',    '=', $order->productid)->where('idwarehouse',  '=', $waerhouse)->decrement('quantity', $quentiti);
                            
                            $existedCheck = DB::table('soldweekly')
                                ->where('productid',    '=', $order->productid)
                                ->where('idwarehouse',  '=', $waerhouse)
                                ->where('weeksell',     '=', $order->weeksell)
                                ->first();

                            if(empty($existedCheck)) {
                                DB::table('soldweekly')->insert([
                                        'idwarehouse'   => $waerhouse,
                                        'productid'     => $order->productid,
                                        'weeksell'      => $order->weeksell,
                                        'quantity'      => $quentiti,
                                        'country'       => $order->country
                                    ]);
                            } else {
                                DB::table('soldweekly')->where('productid',    '=', $order->productid)->where('idwarehouse',  '=', $waerhouse)->increment('quantity', $quentiti);
                            }
                            
                        } // multiorderclose
                    }
                    $worksheet->getCell('A'.($i))->setValue($num);

                    $worksheet->getCell('B'.($i))->setValue($customerName);
                    $worksheet->getStyle('B'.($i))->getAlignment()->setWrapText(true);

                    $worksheet->getCell('C'.($i))->setValue($inv_address1);
                    $worksheet->getStyle('C'.($i))->getAlignment()->setWrapText(true);

                    $worksheet->getCell('D'.($i))->setValue($city);
                    $worksheet->getStyle('D'.($i))->getAlignment()->setWrapText(true);

                    $worksheet->getCell('E'.($i))->setValue('');

                    $worksheet->getCell('F'.($i))->setValue($plz);
                    $worksheet->getStyle('F'.($i))->getAlignment()->setWrapText(true);

                    $worksheet->getCell('G'.($i))->setValue($groupshipping);
                    $worksheet->getStyle('G'.($i))->getAlignment()->setWrapText(true);

                    $worksheet->getCell('H'.($i))->setValue($vabpkb);
                    $worksheet->getStyle('H'.($i))->getAlignment()->setWrapText(true);

                    $worksheet->getCell('I'.($i))->setValue($vabccm);
                    $worksheet->getStyle('I'.($i))->getAlignment()->setWrapText(true);

                    $worksheet->getCell('J'.($i))->setValue($vabcbo);
                    $worksheet->getStyle('J'.($i))->getAlignment()->setWrapText(true);

                    $worksheet->getCell('K'.($i))->setValue($vabctr);
                    $worksheet->getStyle('K'.($i))->getAlignment()->setWrapText(true);

                    $worksheet->getCell('L'.($i))->setValue($vabtsp);
                    $worksheet->getStyle('L'.($i))->getAlignment()->setWrapText(true);

                    $worksheet->getCell('M'.($i))->setValue($referenceorder);
                    $worksheet->getStyle('M'.($i))->getAlignment()->setWrapText(true);

                    $worksheet->getCell('N'.($i))->setValue('');
                    $worksheet->getCell('O'.($i))->setValue('');
                    $worksheet->getCell('P'.($i))->setValue('');
                    $worksheet->getCell('Q'.($i))->setValue('');

                    $worksheet->getCell('R'.($i))->setValue($provinceCell);
                    $worksheet->getStyle('R'.($i))->getAlignment()->setWrapText(true);
                    $i++;
                    $num++;
                }//address check
            }
            foreach(range('A','R') as $index=>$v){
                $spreadsheet->getActiveSheet()->getColumnDimension($v)->setWidth(26.7);
                $style = array(
                        'alignment' => array(
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                        )
                    );
                $spreadsheet->getActiveSheet()->getStyle($v)->applyFromArray($style);
            }
            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Csv');
            $writer->save(public_path('courier_csv/'.'SDA.csv'));
        }
    }
    
    public function dpd_csv(){
        $file = fopen ('courier_csv/DPD.csv','w');
        // send the column headers
        fputcsv($file, array('Kundennummer', 'EbayName', 'Versandart', 'Versandkosten','Zahlart','Firma','Anrede','Name','Strasse','Land','PLZ','Ort','Telefon','fax','email','auktionsgruppe',
                                'Lieferanschrift','LFirma','Lname','Lstrasse','Lplz','Lort','LLand','Gruppensumme','Zahlartenaufschlag','Versandgruppe','Gewicht','ReNr','EbayVersand','Zinfo','ISOLLand','ISOLand',
                                'EKNummer','Anschrift2','Lanschrift2','Logistikerversandart','Artikelnummer','Artikelname','EAN','MengeEAN','Bundesland','LBundesland','Memo','MarkierungsID','MengeProduktID','LTelefon')
                );
        
        $select = DB::table('orderitem')
                ->where('carriername', '=', 'DPD')
                ->where('courierinformedok', '=', 0)
                ->where('idpayment', '!=', '')
                ->where('multiorder', '=', '0')
                ->where('idpayment', '!=', 'Not Paid')
                ->get();
        
        if (count($select) > 0){
            // loop over the rows, outputting them
            foreach($select as $rows) {
                if($rows->address1 != "" && $rows->address1 != null) {
                    $quentiti   = $rows->quantity;
                    $idorder    = $rows->idorder;
                    $multiorder = $rows->multiorder;
                    $waerhouse  = $rows->idwarehouse;

                    if(strlen($rows->plz) == 3) {
                        $rows->plz = "00".$rows->plz;
                    } else if(strlen($rows->plz) == 4) {
                        $rows->plz = "0".$rows->plz;
                    } else if(strlen($rows->plz) == 2) {
                        $rows->plz = "000".$rows->plz;
                    }

                    $row = array(
                        '','','','','',
                        $rows->customer,'',
                        $rows->address1,
                        $rows->address2,'',
                        $rows->plz,
                        $rows->city,
                        $rows->telefon,
                        '','','','','','','',
                        '','','','','','','',
                        '','','',
                        $rows->country,
                        '','','','','',
                        $rows->productid,'',
                        '','','','','','','','',
                    );

                    if($rows->multiorder == '0') {
                        fputcsv($file, $row);
                    }

                    DB::table('orderitem')
                        ->where('idorder', '=', $idorder)
                        ->update([
                            "courierinformedok"     =>1,
                            "printedshippingok"     => 0,
                            "registeredtosolddayok" =>0
                        ]);

                    $rowssss = DB::table('product')
                            ->where('productid', '=', $rows->productid)
                            ->first();

                    $rowlager = DB::table('lagerstand')
                            ->where('productid', '=', $rows->productid)
                            ->where('idwarehouse', '=', $waerhouse)
                            ->first();
                    /* ADD LAGERSTAND RECORD */ 

                    if(empty($rowlager)){
                        $reslager = DB::table('lagerstand')
                                ->insert([
                                    "productid"     => $rows->productid,
                                    "idwarehouse"   => $waerhouse,
                                    "quantity"      => (-1)*$quentiti
                                ]);
                    }else{
                        $quan = $rowlager->quantity - $quentiti;
                        DB::table('lagerstand')
                            ->where('productid',    '=', $rows->productid)
                            ->where('idwarehouse',  '=', $waerhouse)
                            ->update(['quantity'=>$quan]);
                    }

                    $existedCheck = DB::table('soldweekly')
                        ->where('productid',    '=', $rows->productid)
                        ->where('idwarehouse',  '=', $waerhouse)
                        ->where('weeksell',     '=', $rows->weeksell)
                        ->first();
                    if(empty($existedCheck)) {
                        DB::table('soldweekly')
                            ->insert([
                                'idwarehouse'   => $waerhouse,
                                'productid'     => $rows->productid,
                                'weeksell'      => $rows->weeksell,
                                'quantity'      => $quentiti,
                                'country'       => $rows->country,
                                'year'          => date('Y')
                            ]);
                    } else {
                        DB::table('soldweekly')
                            ->where('productid',    '=', $rows->productid)
                            ->where('idwarehouse',  '=', $waerhouse)
                            ->increment('quantity', $quentiti);
                    }

                    //get multiorders
                    // $multiOrders = DB::table('orderitem')
                    //     ->where('multiorder', '=', $rows->referenceorder)
                    //     ->get();
                    
                    // foreach($multiOrders as $order) {
                    //     $quentiti   = $order->quantity;
                    //     $idorder    = $order->idorder;
                    //     $multiorder = $order->multiorder;
                    //     $waerhouse  = $order->idwarehouse;

                    //     $row = array(
                    //         '','','','','',
                    //         $order->customer,'',
                    //         $order->address1,
                    //         $order->address2,'',
                    //         $order->plz,
                    //         $order->city,
                    //         $order->telefon,
                    //         '','','','','','','',
                    //         '','','','','','','',
                    //         '','','',
                    //         $order->country,
                    //         '','','','','',
                    //         $order->productid,'',
                    //         '','','','','','','','',
                    //     );
                        
                    //     if($order->multiorder == "0") {
                    //         fputcsv($file, $row);
                    //     }

                    //     DB::table('orderitem')
                    //         ->where('idorder', '=', $idorder)
                    //         ->update([
                    //             "courierinformedok"     =>1,
                    //             "printedshippingok"     => 0,
                    //             "registeredtosolddayok" =>0
                    //         ]);

                    //     $rowssss = DB::table('product')
                    //             ->where('productid', '=', $order->productid)
                    //             ->first();

                    //     $rowlager = DB::table('lagerstand')
                    //             ->where('productid', '=', $order->productid)
                    //             ->where('idwarehouse', '=', $waerhouse)
                    //             ->first();
                    //     /* ADD LAGERSTAND RECORD */ 

                    //     if (empty($rowlager)){
                    //         if($order->productid != "" && $order->productid != 0 && $order->productid != null) {
                    //             $reslager = DB::table('lagerstand')
                    //                     ->insert([
                    //                         "productid"     => $order->productid,
                    //                         "idwarehouse"   => $waerhouse,
                    //                         "quantity"      => 0
                    //                     ]);
                    //         }
                    //     }
                    //     /* ADD LAGERSTAND RECORD */
                        
                    //     DB::table('lagerstand')
                    //         ->where('productid',    '=', $order->productid)
                    //         ->where('idwarehouse',  '=', $waerhouse)
                    //         ->decrement('quantity', $quentiti);

                    //     $existedCheck = DB::table('soldweekly')
                    //         ->where('productid',    '=', $order->productid)
                    //         ->where('idwarehouse',  '=', $waerhouse)
                    //         ->where('weeksell',     '=', $order->weeksell)
                    //         ->first();
                    //     if(empty($existedCheck)) {
                    //         DB::table('soldweekly')
                    //             ->insert([
                    //                 'idwarehouse'   => $waerhouse,
                    //                 'productid'     => $order->productid,
                    //                 'weeksell'      => $order->weeksell,
                    //                 'quantity'      => $quentiti,
                    //                 'country'       => $order->country
                    //             ]);
                    //     } else {
                    //         DB::table('soldweekly')
                    //             ->where('productid',    '=', $order->productid)
                    //             ->where('idwarehouse',  '=', $waerhouse)
                    //             ->increment('quantity', $quentiti);
                    //     }
                    // }
                }
            }
        }

        $fileNames = array('create_csv/DHL.csv','create_csv/SDA.csv','create_csv/DPD.csv');
        $datafetch = DB::table('shippingmodel')
                    ->where('type', '=', 0)
                    ->get();

        if(count($datafetch) > 0) {
            foreach($datafetch as $data) { 
                $couriername    = $data->shortname;
                $filename       = $couriername.".csv";
                $fileNames[]    = "create_csv/".$filename;
                $file           = fopen('courier_csv/'.$filename,'w');
                
                // send the column headers
                fputcsv($file, array('Kundennummer', 'EbayName', 'Versandart', 'Versandkosten','Zahlart','Firma','Anrede','Name','Strasse','Land','PLZ','Ort','Telefon','fax','email','auktionsgruppe','Lieferanschrift','LFirma','Lname','Lstrasse','Lplz','Lort','LLand','Gruppensumme','Zahlartenaufschlag','Versandgruppe','Gewicht','ReNr','EbayVersand','Zinfo','ISOLLand','ISOLand','EKNummer','Anschrift2','Lanschrift2','Logistikerversandart','Artikelnummer','Artikelname','EAN','MengeEAN','Bundesland','LBundesland','Memo','MarkierungsID','MengeProduktID','LTelefon'));
                //query the database

                $select3 = DB::table('orderitem')
                        ->where('carriername', '=', $couriername)
                        ->where('courierinformedok', '=', 0)
                        ->where('idpayment', '!=', '')
                        ->where('idpayment', '!=', 'Not Paid')
                        ->get();

                if (count($select3) > 0){
                    // loop over the rows, outputting them
                    foreach($select3 as $rows3) {
                        if($rows3->address1 != "" && $rows3->address1 != null) {
                            $idorder3   = $rows3->idorder;
                            $multiorder = $rows3->multiorder;
                            $quentiti   = $rows3->quantity;
                            $waerhouse  = $rows3->idwarehouse;

                            if(strlen($rows3->plz) == 3) {
                                $rows3->plz = "00".$rows3->plz;
                            } else if(strlen($rows3->plz) == 4) {
                                $rows3->plz = "0".$rows3->plz;
                            } else if(strlen($rows3->plz) == 2) {
                                $rows3->plz = "000".$rows3->plz;
                            }

                            $row3 = array(
                                '','','','','',
                                $rows3->customer,'',
                                $rows3->address1,
                                $rows3->address2,'',
                                $rows3->plz,
                                $rows3->city,
                                $rows3->telefon,
                                '','','','','','','',
                                '','','','','','','',
                                '','','',
                                $rows3->country,
                                '','','','','',
                                $rows3->productid,'',
                                '','','','','','','','',
                            );
                            
                            //if($rows3->multiorder == "0") {
                                fputcsv($file, $row3);
                            //}
                            //putt_data($rows3);

                            DB::table('orderitem')
                                ->where('idorder', '=', $idorder3)
                                ->update([
                                    "courierinformedok"     => 1,
                                    "printedshippingok"     => 0,
                                    "registeredtosolddayok" => 0
                                ]);
                            
                            // wtd-> 19-10-2020 $selects =$mysqliiii->query("select * from product where modelcode ='".$rows3['productid']."'");
                            $selects = DB::table('product')
                                    ->where('productid', '=', $rows3->productid)
                                    ->get();   
                            
                            if(count($selects) > 0) {
                                $rowsss = $selects[0];
                            }

                            /* ADD LAGERSTAND RECORD */ 
                            $lager  = DB::table('lagerstand')
                                    ->where('productid',   '=', $rows3->productid)
                                    ->where('idwarehouse', '=', $waerhouse)
                                    ->get();
                            
                            
                            if (empty($lager)){
                                if($rows3->productid != "" && $rows3->productid != 0 && $rows3->productid != null) {
                                    DB::table('lagerstand')
                                        ->insert([
                                            "productid"     => $rows3->productid,
                                            "idwarehouse"   => $waerhouse,
                                            "quantity"      => 0
                                        ]);
                                }
                            }
                            /* ADD LAGERSTAND RECORD */

                            DB::table('lagerstand')
                                ->where('productid',    '=', $rows3->productid)
                                ->where('idwarehouse',  '=', $waerhouse)
                                ->decrement('quantity', $quentiti);

                            $existedCheck = DB::table('soldweekly')
                                ->where('productid',    '=', $rows3->productid)
                                ->where('idwarehouse',  '=', $waerhouse)
                                ->where('weeksell',     '=', $rows3->weeksell)
                                ->first();
                            if(empty($existedCheck)) {
                                DB::table('soldweekly')
                                    ->insert([
                                        'idwarehouse'   => $waerhouse,
                                        'productid'     => $rows3->productid,
                                        'weeksell'      => $rows3->weeksell,
                                        'quantity'      => $quentiti,
                                        'country'       => $rows3->country,
                                        'year'          => date('Y')
                                    ]);
                            } else {
                                DB::table('soldweekly')
                                    ->where('productid',    '=', $rows3->productid)
                                    ->where('idwarehouse',  '=', $waerhouse)
                                    ->increment('quantity', $quentiti);
                            }

                            //get multiorders
                            // $multiOrders = DB::table('orderitem')
                            //     ->where('multiorder', '=', $rows3->referenceorder)
                            //     ->get();

                            // foreach($multiOrders as $order) {
                            //     $idorder3   = $order->idorder;
                            //     $multiorder = $order->multiorder;
                            //     $quentiti   = $order->quantity;
                            //     $waerhouse  = $order->idwarehouse;
                            //     $row3 = array(
                            //         '','','','','',
                            //         $order->customer,'',
                            //         $order->address1,
                            //         $order->address2,'',
                            //         $order->plz,
                            //         $order->city,
                            //         $order->telefon,
                            //         '','','','','','','',
                            //         '','','','','','','',
                            //         '','','',
                            //         $order->country,
                            //         '','','','','',
                            //         $order->productid,'',
                            //         '','','','','','','','',
                            //     );
                                
                            //     if($order->multiorder == "0") {
                            //         fputcsv($file, $row3);
                            //     }

                            //     DB::table('orderitem')
                            //         ->where('idorder', '=', $idorder3)
                            //         ->update([
                            //             "courierinformedok"     => 1,
                            //             "printedshippingok"     => 0,
                            //             "registeredtosolddayok" => 0
                            //         ]);

                                
                            //     $selects = DB::table('product')
                            //             ->where('productid', '=', $order->productid)
                            //             ->get();   

                            //     if(count($selects) > 0) {
                            //         $rowsss = $selects[0];
                            //     }

                            //     /* ADD LAGERSTAND RECORD */ 
                            //     $lager  = DB::table('lagerstand')
                            //             ->where('productid',   '=', $order->productid)
                            //             ->where('idwarehouse', '=', $waerhouse)
                            //             ->get();
                                
                                
                            //     if (empty($lager)){
                            //         if($order->productid != "" && $order->productid != 0 && $order->productid != null) {
                            //             DB::table('lagerstand')
                            //                 ->insert([
                            //                     "productid"     => $order->productid,
                            //                     "idwarehouse"   => $waerhouse,
                            //                     "quantity"      => 0
                            //                 ]);
                            //         }
                            //     }
                            //     /* ADD LAGERSTAND RECORD */

                            //     DB::table('lagerstand')
                            //         ->where('productid',    '=', $order->productid)
                            //         ->where('idwarehouse',  '=', $waerhouse)
                            //         ->decrement('quantity', $quentiti);

                            //     $existedCheck = DB::table('soldweekly')
                            //         ->where('productid',    '=', $order->productid)
                            //         ->where('idwarehouse',  '=', $waerhouse)
                            //         ->where('weeksell',     '=', $order->weeksell)
                            //         ->first();
                            //     if(empty($existedCheck)) {
                            //         DB::table('soldweekly')
                            //             ->insert([
                            //                 'idwarehouse'   => $waerhouse,
                            //                 'productid'     => $order->productid,
                            //                 'weeksell'      => $order->weeksell,
                            //                 'quantity'      => $quentiti,
                            //                 'country'       => $order->country
                            //             ]);
                            //     } else {
                            //         DB::table('soldweekly')
                            //             ->where('productid',    '=', $order->productid)
                            //             ->where('idwarehouse',  '=', $waerhouse)
                            //             ->increment('quantity', $quentiti);
                            //     }
                            // }
                        }
                    }
                }

                fclose($file);
            } // end of foreach
        }
    }
    
    public function gls_csv(){
        $file = fopen ('courier_csv/GLS.csv','w');
        // send the column headers
        fputcsv($file, array('Kundennummer', 'EbayName', 'Versandart', 'Versandkosten','Zahlart','Firma','Anrede','Name','Strasse','Land','PLZ','Ort','Telefon','fax','email','auktionsgruppe',
                                'Lieferanschrift','LFirma','Lname','Lstrasse','Lplz','Lort','LLand','Gruppensumme','Zahlartenaufschlag','Versandgruppe','Gewicht','ReNr','EbayVersand','Zinfo','ISOLLand','ISOLand',
                                'EKNummer','Anschrift2','Lanschrift2','Logistikerversandart','Artikelnummer','Artikelname','EAN','MengeEAN','Bundesland','LBundesland','Memo','MarkierungsID','MengeProduktID','LTelefon')
                );
        
        $select = DB::table('orderitem')
                ->where('carriername', '=', 'GLS')
                ->where('courierinformedok', '=', 0)
                ->where('idpayment', '!=', '')
                //->where('multiorder', '=', '0')
                ->where('idpayment', '!=', 'Not Paid')
                ->get();
        
        if (count($select) > 0){
            // loop over the rows, outputting them
            foreach($select as $rows) {
                //if($rows->address1 != "" && $rows->address1 != null) {
                    $quentiti   = $rows->quantity;
                    $idorder    = $rows->idorder;
                    $multiorder = $rows->multiorder;
                    $waerhouse  = $rows->idwarehouse;

                    if(strlen($rows->plz) == 3) {
                        $rows->plz = "00".$rows->plz;
                    } else if(strlen($rows->plz) == 4) {
                        $rows->plz = "0".$rows->plz;
                    } else if(strlen($rows->plz) == 2) {
                        $rows->plz = "000".$rows->plz;
                    }

                    $row = array(
                        '','','','','',
                        $rows->customer,'',
                        $rows->address1,
                        $rows->address2,'',
                        $rows->plz,
                        $rows->city,
                        $rows->telefon,
                        '','','','','','','',
                        '','',$rows->country,'','','','',
                        '','','',
                        $rows->country,
                        '','','','','',
                        $rows->productid,'',
                        '','','','','','','','',
                    );

                    if($rows->multiorder == '0') {
                        fputcsv($file, $row);
                    }

                    DB::table('orderitem')
                        ->where('idorder', '=', $idorder)
                        ->update([
                            "courierinformedok"     =>1,
                            "printedshippingok"     => 0,
                            "registeredtosolddayok" =>0
                        ]);

                    $rowssss = DB::table('product')
                            ->where('productid', '=', $rows->productid)
                            ->first();

                    $rowlager = DB::table('lagerstand')
                            ->where('productid', '=', $rows->productid)
                            ->where('idwarehouse', '=', $waerhouse)
                            ->first();
                    /* ADD LAGERSTAND RECORD */ 

                    if(empty($rowlager)){
                        $reslager = DB::table('lagerstand')
                                ->insert([
                                    "productid"     => $rows->productid,
                                    "idwarehouse"   => $waerhouse,
                                    "quantity"      => (-1)*$quentiti
                                ]);
                    }else{
                        $quan = $rowlager->quantity - $quentiti;
                        DB::table('lagerstand')
                            ->where('productid',    '=', $rows->productid)
                            ->where('idwarehouse',  '=', $waerhouse)
                            ->update(['quantity'=>$quan]);
                    }

                    $existedCheck = DB::table('soldweekly')
                        ->where('productid',    '=', $rows->productid)
                        ->where('idwarehouse',  '=', $waerhouse)
                        ->where('weeksell',     '=', $rows->weeksell)
                        ->first();
                    if(empty($existedCheck)) {
                        DB::table('soldweekly')
                            ->insert([
                                'idwarehouse'   => $waerhouse,
                                'productid'     => $rows->productid,
                                'weeksell'      => $rows->weeksell,
                                'quantity'      => $quentiti,
                                'country'       => $rows->country,
                                'year'          => date('Y')
                            ]);
                    } else {
                        DB::table('soldweekly')
                            ->where('productid',    '=', $rows->productid)
                            ->where('idwarehouse',  '=', $waerhouse)
                            ->increment('quantity', $quentiti);
                    }

                    //get multiorders
                    // $multiOrders = DB::table('orderitem')
                    //     ->where('multiorder', '=', $rows->referenceorder)
                    //     ->get();
                    
                    // foreach($multiOrders as $order) {
                    //     $quentiti   = $order->quantity;
                    //     $idorder    = $order->idorder;
                    //     $multiorder = $order->multiorder;
                    //     $waerhouse  = $order->idwarehouse;

                    //     $row = array(
                    //         '','','','','',
                    //         $order->customer,'',
                    //         $order->address1,
                    //         $order->address2,'',
                    //         $order->plz,
                    //         $order->city,
                    //         $order->telefon,
                    //         '','','','','','','',
                    //         '','','','','','','',
                    //         '','','',
                    //         $order->country,
                    //         '','','','','',
                    //         $order->productid,'',
                    //         '','','','','','','','',
                    //     );
                        
                    //     if($order->multiorder == "0") {
                    //         fputcsv($file, $row);
                    //     }

                    //     DB::table('orderitem')
                    //         ->where('idorder', '=', $idorder)
                    //         ->update([
                    //             "courierinformedok"     =>1,
                    //             "printedshippingok"     => 0,
                    //             "registeredtosolddayok" =>0
                    //         ]);

                    //     $rowssss = DB::table('product')
                    //             ->where('productid', '=', $order->productid)
                    //             ->first();

                    //     $rowlager = DB::table('lagerstand')
                    //             ->where('productid', '=', $order->productid)
                    //             ->where('idwarehouse', '=', $waerhouse)
                    //             ->first();
                    //     /* ADD LAGERSTAND RECORD */ 

                    //     if (empty($rowlager)){
                    //         if($order->productid != "" && $order->productid != 0 && $order->productid != null) {
                    //             $reslager = DB::table('lagerstand')
                    //                     ->insert([
                    //                         "productid"     => $order->productid,
                    //                         "idwarehouse"   => $waerhouse,
                    //                         "quantity"      => 0
                    //                     ]);
                    //         }
                    //     }
                    //     /* ADD LAGERSTAND RECORD */
                        
                    //     DB::table('lagerstand')
                    //         ->where('productid',    '=', $order->productid)
                    //         ->where('idwarehouse',  '=', $waerhouse)
                    //         ->decrement('quantity', $quentiti);

                    //     $existedCheck = DB::table('soldweekly')
                    //         ->where('productid',    '=', $order->productid)
                    //         ->where('idwarehouse',  '=', $waerhouse)
                    //         ->where('weeksell',     '=', $order->weeksell)
                    //         ->first();
                    //     if(empty($existedCheck)) {
                    //         DB::table('soldweekly')
                    //             ->insert([
                    //                 'idwarehouse'   => $waerhouse,
                    //                 'productid'     => $order->productid,
                    //                 'weeksell'      => $order->weeksell,
                    //                 'quantity'      => $quentiti,
                    //                 'country'       => $order->country
                    //             ]);
                    //     } else {
                    //         DB::table('soldweekly')
                    //             ->where('productid',    '=', $order->productid)
                    //             ->where('idwarehouse',  '=', $waerhouse)
                    //             ->increment('quantity', $quentiti);
                    //     }
                    // }
                //}
            }
        }

        $fileNames = array('create_csv/DHL.csv','create_csv/SDA.csv','create_csv/DPD.csv');
        $datafetch = DB::table('shippingmodel')
                    ->where('type', '=', 0)
                    ->get();

        if(count($datafetch) > 0) {
            foreach($datafetch as $data) { 
                $couriername    = $data->shortname;
                $filename       = $couriername.".csv";
                $fileNames[]    = "create_csv/".$filename;
                $file           = fopen('courier_csv/'.$filename,'w');
                
                // send the column headers
                fputcsv($file, array('Kundennummer', 'EbayName', 'Versandart', 'Versandkosten','Zahlart','Firma','Anrede','Name','Strasse','Land','PLZ','Ort','Telefon','fax','email','auktionsgruppe','Lieferanschrift','LFirma','Lname','Lstrasse','Lplz','Lort','LLand','Gruppensumme','Zahlartenaufschlag','Versandgruppe','Gewicht','ReNr','EbayVersand','Zinfo','ISOLLand','ISOLand','EKNummer','Anschrift2','Lanschrift2','Logistikerversandart','Artikelnummer','Artikelname','EAN','MengeEAN','Bundesland','LBundesland','Memo','MarkierungsID','MengeProduktID','LTelefon'));
                //query the database

                $select3 = DB::table('orderitem')
                        ->where('carriername', '=', $couriername)
                        ->where('courierinformedok', '=', 0)
                        ->where('idpayment', '!=', '')
                        ->where('idpayment', '!=', 'Not Paid')
                        ->get();

                if (count($select3) > 0){
                    // loop over the rows, outputting them
                    foreach($select3 as $rows3) {
                        if($rows3->address1 != "" && $rows3->address1 != null) {
                            $idorder3   = $rows3->idorder;
                            $multiorder = $rows3->multiorder;
                            $quentiti   = $rows3->quantity;
                            $waerhouse  = $rows3->idwarehouse;

                            if(strlen($rows3->plz) == 3) {
                                $rows3->plz = "00".$rows3->plz;
                            } else if(strlen($rows3->plz) == 4) {
                                $rows3->plz = "0".$rows3->plz;
                            } else if(strlen($rows3->plz) == 2) {
                                $rows3->plz = "000".$rows3->plz;
                            }

                            $row3 = array(
                                '','','','','',
                                $rows3->customer,'',
                                $rows3->address1,
                                $rows3->address2,'',
                                $rows3->plz,
                                $rows3->city,
                                $rows3->telefon,
                                '','','','','','','',
                                '','','','','','','',
                                '','','',
                                $rows3->country,
                                '','','','','',
                                $rows3->productid,'',
                                '','','','','','','','',
                            );
                            
                            //if($rows3->multiorder == "0") {
                                fputcsv($file, $row3);
                            //}
                            //putt_data($rows3);

                            DB::table('orderitem')
                                ->where('idorder', '=', $idorder3)
                                ->update([
                                    "courierinformedok"     => 1,
                                    "printedshippingok"     => 0,
                                    "registeredtosolddayok" => 0
                                ]);
                            
                            // wtd-> 19-10-2020 $selects =$mysqliiii->query("select * from product where modelcode ='".$rows3['productid']."'");
                            $selects = DB::table('product')
                                    ->where('productid', '=', $rows3->productid)
                                    ->get();   
                            
                            if(count($selects) > 0) {
                                $rowsss = $selects[0];
                            }

                            /* ADD LAGERSTAND RECORD */ 
                            $lager  = DB::table('lagerstand')
                                    ->where('productid',   '=', $rows3->productid)
                                    ->where('idwarehouse', '=', $waerhouse)
                                    ->get();
                            
                            
                            if (empty($lager)){
                                if($rows3->productid != "" && $rows3->productid != 0 && $rows3->productid != null) {
                                    DB::table('lagerstand')
                                        ->insert([
                                            "productid"     => $rows3->productid,
                                            "idwarehouse"   => $waerhouse,
                                            "quantity"      => 0
                                        ]);
                                }
                            }
                            /* ADD LAGERSTAND RECORD */

                            DB::table('lagerstand')
                                ->where('productid',    '=', $rows3->productid)
                                ->where('idwarehouse',  '=', $waerhouse)
                                ->decrement('quantity', $quentiti);

                            $existedCheck = DB::table('soldweekly')
                                ->where('productid',    '=', $rows3->productid)
                                ->where('idwarehouse',  '=', $waerhouse)
                                ->where('weeksell',     '=', $rows3->weeksell)
                                ->first();
                            if(empty($existedCheck)) {
                                DB::table('soldweekly')
                                    ->insert([
                                        'idwarehouse'   => $waerhouse,
                                        'productid'     => $rows3->productid,
                                        'weeksell'      => $rows3->weeksell,
                                        'quantity'      => $quentiti,
                                        'country'       => $rows3->country,
                                        'year'          => date('Y')
                                    ]);
                            } else {
                                DB::table('soldweekly')
                                    ->where('productid',    '=', $rows3->productid)
                                    ->where('idwarehouse',  '=', $waerhouse)
                                    ->increment('quantity', $quentiti);
                            }

                            //get multiorders
                            // $multiOrders = DB::table('orderitem')
                            //     ->where('multiorder', '=', $rows3->referenceorder)
                            //     ->get();

                            // foreach($multiOrders as $order) {
                            //     $idorder3   = $order->idorder;
                            //     $multiorder = $order->multiorder;
                            //     $quentiti   = $order->quantity;
                            //     $waerhouse  = $order->idwarehouse;
                            //     $row3 = array(
                            //         '','','','','',
                            //         $order->customer,'',
                            //         $order->address1,
                            //         $order->address2,'',
                            //         $order->plz,
                            //         $order->city,
                            //         $order->telefon,
                            //         '','','','','','','',
                            //         '','','','','','','',
                            //         '','','',
                            //         $order->country,
                            //         '','','','','',
                            //         $order->productid,'',
                            //         '','','','','','','','',
                            //     );
                                
                            //     if($order->multiorder == "0") {
                            //         fputcsv($file, $row3);
                            //     }

                            //     DB::table('orderitem')
                            //         ->where('idorder', '=', $idorder3)
                            //         ->update([
                            //             "courierinformedok"     => 1,
                            //             "printedshippingok"     => 0,
                            //             "registeredtosolddayok" => 0
                            //         ]);

                                
                            //     $selects = DB::table('product')
                            //             ->where('productid', '=', $order->productid)
                            //             ->get();   

                            //     if(count($selects) > 0) {
                            //         $rowsss = $selects[0];
                            //     }

                            //     /* ADD LAGERSTAND RECORD */ 
                            //     $lager  = DB::table('lagerstand')
                            //             ->where('productid',   '=', $order->productid)
                            //             ->where('idwarehouse', '=', $waerhouse)
                            //             ->get();
                                
                                
                            //     if (empty($lager)){
                            //         if($order->productid != "" && $order->productid != 0 && $order->productid != null) {
                            //             DB::table('lagerstand')
                            //                 ->insert([
                            //                     "productid"     => $order->productid,
                            //                     "idwarehouse"   => $waerhouse,
                            //                     "quantity"      => 0
                            //                 ]);
                            //         }
                            //     }
                            //     /* ADD LAGERSTAND RECORD */

                            //     DB::table('lagerstand')
                            //         ->where('productid',    '=', $order->productid)
                            //         ->where('idwarehouse',  '=', $waerhouse)
                            //         ->decrement('quantity', $quentiti);

                            //     $existedCheck = DB::table('soldweekly')
                            //         ->where('productid',    '=', $order->productid)
                            //         ->where('idwarehouse',  '=', $waerhouse)
                            //         ->where('weeksell',     '=', $order->weeksell)
                            //         ->first();
                            //     if(empty($existedCheck)) {
                            //         DB::table('soldweekly')
                            //             ->insert([
                            //                 'idwarehouse'   => $waerhouse,
                            //                 'productid'     => $order->productid,
                            //                 'weeksell'      => $order->weeksell,
                            //                 'quantity'      => $quentiti,
                            //                 'country'       => $order->country
                            //             ]);
                            //     } else {
                            //         DB::table('soldweekly')
                            //             ->where('productid',    '=', $order->productid)
                            //             ->where('idwarehouse',  '=', $waerhouse)
                            //             ->increment('quantity', $quentiti);
                            //     }
                            // }
                        }
                    }
                }

                fclose($file);
            } // end of foreach
        }
    }

    public function makeZipWithFiles(string $zipPathAndName, array $filesAndPaths) {
        $zip = new ZipArchive();
        $tempFile = tmpfile();
        $tempFileUri = stream_get_meta_data($tempFile)['uri'];
        if ($zip->open($tempFileUri, ZipArchive::CREATE) === TRUE) {
            // Add File in ZipArchive
            foreach($filesAndPaths as $file)
            {
                if (! $zip->addFile($file, basename($file))) {
                    echo 'Could not add file to ZIP: ' . $file;
                }
            }
            // Close ZipArchive
            $zip->close();
        } else {
            echo 'Could not open ZIP file.';
        }
        echo 'Path:' . $zipPathAndName;
        rename($tempFileUri, $zipPathAndName);
    }

    public function checkmydate($date) {
        $tempDate = explode('-', $date);
        return checkdate($tempDate[1], $tempDate[2], $tempDate[0]);
    }

    public function getValue($tbl, $dispFld, $IdFld, $id_val) {
        $return_val = "";
    
        $result = DB::table($tbl)
                    ->where($IdFld, '=', $id_val)
                    ->select($dispFld)
                    ->first();

        $return_val = $result->$dispFld;
        return $return_val;
    }
}
