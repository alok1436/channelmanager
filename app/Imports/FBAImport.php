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

                if(empty($existingFBA) && $row[5] > 0) {
                    $this->nonExistingProducts[] = $row[2];
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