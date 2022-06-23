<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use DB;
class FBAImport implements ToCollection
{
    public $data;
    public $check_idexplode;
    public $nonExistingProducts = [];

    public function __construct($request, $check_idexplode){
        $this->data = $request;
        $this->check_idexplode = $check_idexplode;
    }

    public function nonExistingProducts(){
        return $this->nonExistingProducts;
    }
    public function collection(Collection $rows)
    {
        $request = $this->data;
        $checks     = $request->check;
        $check_idexplode = $this->check_idexplode;
        $platformId      = $check_idexplode[0];
        $channelId       = $check_idexplode[1]; 

        DB::table('tbl_fba')
                    ->where('channel', '=', $channelId)
                    ->update([
                        'actuallevel'     => 0,
                        'active'        => 0
                    ]);

        $channel   = DB::table('channel')
                            ->where('channel.idchannel', '=', $channelId)
                            ->leftjoin('coding', 'channel.codingId', '=', 'coding.codingId')
                            ->first();

        foreach ($rows as $key => $row) {


           // if(strtolower($row[4]) == "sellable") {
                $existingFBA = DB::table('tbl_fba')
                        ->where('asin'      , '=', $row[2])
                        ->where('channel'   , '=', $channelId)
                        ->first();
                // if($row[2] == 'B07FW3TCFH'){
                //     dd($existingFBA);
                // }
                        
                if(empty($existingFBA) && $row[8] == 'Yes') {
                    $this->nonExistingProducts[] = $row[2];

                    $product = DB::table('product')
                        ->where('asin', '=', $row[2])
                        ->first();

                    if($product){
                        DB::table('tbl_fba')
                        ->where('asinasin'      , '=', $row[2])
                        ->where('channel'   , '=', $channelId)
                        ->insert([
                            'asin'            => $row[2],
                            'channel'         => $channelId,
                            'actuallevel'     => $row[10],
                            'active'          => $row[8] == 'Yes' ? 1 : 0,
                            'sku'             => $row[0],
                            'productid'       => $row[0],
                            'dateupdate'      => date('Y-m-d h:i:s'),
                        ]);
                    }else{
                        $this->nonExistingProducts[] = 'ASIN '.$row[2].' DON\'T FOUND, PLEASE CHECK';
                    }

                } else {
                    DB::table('tbl_fba')
                        ->where('asin'      , '=', $row[2])
                        ->where('channel'   , '=', $channelId)
                        ->update([
                            'actuallevel'     => $row[10],
                            'active'          => $row[8] == 'Yes' ? 1 : 0
                        ]);
                }
           // }
        }
        return $this->nonExistingProducts;
    }
}