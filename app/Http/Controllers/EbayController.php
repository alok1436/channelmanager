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
use App\Models\Channel;
use App\Models\Product;
use App\Models\LagerStand;
use App\Models\OrderItem;

 

class EbayController extends Controller {
    

    public function __construct(){

    }


    public function connect(Request $request, $id)
    {
        $channel = Channel::find($id); //dd($channel);
        if($channel){
            session()->put('channel', $channel->idchannel);
            $url = "https://auth.ebay.com/oauth2/authorize?client_id=".$channel->appid."&response_type=code&redirect_uri=ottavio_linzalo-ottaviol-testap-gipawcx&scope=https://api.ebay.com/oauth/api_scope https://api.ebay.com/oauth/api_scope/sell.marketing.readonly https://api.ebay.com/oauth/api_scope/sell.marketing https://api.ebay.com/oauth/api_scope/sell.inventory.readonly https://api.ebay.com/oauth/api_scope/sell.inventory https://api.ebay.com/oauth/api_scope/sell.account.readonly https://api.ebay.com/oauth/api_scope/sell.account https://api.ebay.com/oauth/api_scope/sell.fulfillment.readonly https://api.ebay.com/oauth/api_scope/sell.fulfillment https://api.ebay.com/oauth/api_scope/sell.analytics.readonly https://api.ebay.com/oauth/api_scope/sell.finances https://api.ebay.com/oauth/api_scope/sell.payment.dispute https://api.ebay.com/oauth/api_scope/commerce.identity.readonly https://api.ebay.com/oauth/api_scope/commerce.notification.subscription https://api.ebay.com/oauth/api_scope/commerce.notification.subscription.readonly";
            return redirect($url);
        }
    }

    public function callback(Request $request)
    {   
        if($request->filled('code')){
            $this->getAccessToken($request->code);
            return redirect('channelView')->with('msg','Connected successfully');
        }else{
            return redirect('channelView')->with('msg','Something went wrong.');
        }
    }

    public function getAccessToken( $code)
    {   $idchannel = session()->get('channel');
        $channel = Channel::find($idchannel);
        $client = new \GuzzleHttp\Client();
        try {
            $res = $client->post('https://api.ebay.com/identity/v1/oauth2/token', [
                    'form_params' => [
                        'grant_type' => 'authorization_code',
                        'code' => $code,
                        'redirect_uri' => 'ottavio_linzalo-ottaviol-testap-gipawcx',
                    ],
                    'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Authorization' => 'Basic ' .base64_encode($channel->appid.':'.$channel->certid),
                  ]                    
                ]);
    
            $res = json_decode($res->getBody()->getContents()); 
            if($res){
                
                $channel->refresh_token = $res->refresh_token;
                $channel->accesstoken = $res->access_token;
                $channel->expire = $res->expires_in;
                $channel->data = json_encode($res);
                $channel->save();
            }
            return $res;
        }
        catch (\GuzzleHttp\Exception\ClientException $e) {
            // $response = $e->getResponse();
            // $result =  json_decode($response->getBody()->getContents());
            // return response()->json(['data' => $result]);

            return [];
        }
    }
}
