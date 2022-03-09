<?php
namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Channel;
use App\Models\OrderItem;
use App\Models\Document;
use App\Models\OrderToPay;
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
use DNS1D; 
use App\Exports\PrintOrderExport;
use MCS\MWSClient;

use Codexshaper\WooCommerce\Facades\Order; 

class OrderController extends Controller
{
    public function amazonOrders(Request $request) {
        $client = new MWSClient([
                'Marketplace_Id'    => 'A13V1IB3VIYZZH',
                'Seller_Id'         => 'A1MYXZTPY3MSOT',
                'Access_Key_ID'     => 'AKIAJV7MBCTERNQSHBIA',
                'Secret_Access_Key' => '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',
                'MWSAuthToken'      => 'amzn.mws.86b8f7b5-4e0f-5488-484d-e89c8e491acf' // Optional. Only use this key if you are a third party user/developer
            ]);
        $date     = date("Y-m-d", strtotime("-1 week"));
        $fromDate = new \DateTime($date);
        $orders = $client->ListOrders($fromDate, $allMarketplaces = false, $states = [
        'Shipped', 'PartiallyShipped']);
       echo '<pre>'; print_r($orders); echo '</pre>'; exit();
    }
    public function orderInvoiceCreate(Request $request, $id) {
        $order = OrderItem::findOrFail($id);

        if(!$order->orderInvoice()->exists()){
            $order->orderInvoice()->create(['shipping'=>0,'vat'=> $order->channel->vat ]);
        }
        
        $countries  = DB::table("country")->get();
        return View::make('ajax.invoice_page', compact('order','countries'));
    }
    
    public function orderView(Request $request) {        
        if(!Session::has('userID')) {
            return redirect()->route('login');
        }

        $keyword = "";
        $datee   = "";
        
        if(isset($_GET['datee'])) {
            $datee = $_GET['datee'];
        }
        if(isset($_GET['datcarriernameee'])) {
            $datcarriernameee = $_GET['datcarriernameee'];
        }
        if($request->search == 'idpayment') {
            $collection = OrderToPay::with('product');   
        }else{
            $collection = OrderItem::with('product');
        }
        if($datee == "") {
            
        } else if($datee == 31) {
            $fromDate = date('Y/m/d', strtotime($_GET['fromDate']));
            if($_GET['toDate'] != "") {
                $toDate = date('Y/m/d', strtotime($_GET['toDate']));
            } else {
                $toDate = date('Y/m/d');
            }

            $collection->whereBetween('datee', [$fromDate, $toDate]);
        } else {
            $fromDateArr = explode(" ", Carbon::today()->subdays($datee));
            if($datee == 1) {
                $collection->whereBetween('datee', [date('Y/m/d', strtotime($fromDateArr[0])), date('Y/m/d', strtotime($fromDateArr[0]))]);
            } else {
                $collection->where('datee', '>=',date('Y/m/d', strtotime($fromDateArr[0])));
            }
        }

        $collection->where(function($collection) {
            $collection->where('carriername', '!=', 'FBA')->orWhereNull('carriername');
        }); 
        
        if($request->keyword !=''){
          	$keyword = request()->keyword;
          	$collection->whereHas("products", function($collection) use($keyword){
                $collection->where('namelong','like', "%$keyword%");
                $collection->orWhere('sku','like', "%$keyword%");
                $collection->orWhere('ean','like', "%$keyword%");
            });
            $collection->orWhere('referenceorder','like', "%$keyword%");
            $collection->orWhere('customer','like', "%$keyword%");
            $collection->orWhere('customerextra','like', "%$keyword%");
            $collection->orWhere('address1','like', "%$keyword%");
            $collection->orWhere('address2','like', "%$keyword%");
            $collection->orWhere('plz','like', "%$keyword%");
            $collection->orWhere('city','like', "%$keyword%");
            $collection->orWhere('region','like', "%$keyword%");
            $collection->orWhere('country','like', "%$keyword%");
            $collection->orWhere('email','like', "%$keyword%");
            $collection->orWhere('fax','like', "%$keyword%");
            $collection->orWhere('telefon','like', "%$keyword%");
            $collection->orWhere('ship_service_level','like', "%$keyword%");
            $collection->orWhere('carriername','like', "%$keyword%");
            $collection->orWhere('tracking','like', "%$keyword%");
            $collection->orWhere('groupshipping','like', "%$keyword%");
        }
        
        if($request->filled('warehouse') && $request->get('warehouse') > 0) {
            $collection->where('idwarehouse', '=', $request->warehouse);
        }
        
        if($request->filled('warehouse') && $request->get('warehouse') > 0) {
            $collection->where('idwarehouse', '=', $request->warehouse);
        }
        
        if($request->filled('show_deleted_orders')) {
            $collection->where('idpayment', '=', 'Deleted');
        }else{
            $collection->where('idpayment', '!=', 'Deleted');
        }
        
        if(isset($_GET['search']) && $_GET['search'] == "idpayment") {
           // $collection->where('idpayment', '=', "");
        }
        
        if(isset($_GET['search']) && $_GET['search'] == "carriername") {
            $collection->where('carriername', '=', "");
            //$collection->orwhere('orderitem.carriername', '=', "");
        }

        if(isset($_GET['search']) && $_GET['search'] == "printedshippingok") {
            $collection->where('printedshippingok', '=', 0);
        }

        if(isset($_GET['search']) && $_GET['search'] == "registeredtosolddayok") {
            $collection->where('registeredtosolddayok', '=', 0);
        }

        if(isset($_GET['integrate'])) {
            $collection->whereNull('address1')->whereBetween('referencechannel', [1, 4]);
        }
        //$collection->where('idorder','25');
        $collection->where('multiorder','0');
        $orders = $collection->orderBy('idorder','desc')->groupBy('referenceorder')->paginate(100);
       // dd($orders);
        $modalWares = DB::table('orderitem')
                    ->leftjoin('warehouse', 'warehouse.idwarehouse', '=', 'orderitem.idwarehouse')
                    //->where('orderitem.printedshippingok', '=', 0)
                    ->groupBy('orderitem.idwarehouse')
                    ->get();

        $companies  = DB::table('companyinfo')->get();
        $products   = DB::table('product')->get();
        $warehouses = DB::table("warehouse")->get();
        $channels   = DB::table("channel")->get();
        $payments   = DB::table("payment")->get();
        $carriers   = DB::table("shippingmodel")->get();
        $currencies = DB::table("currency")->get();
        $countries  = DB::table("country")->get();

        $params['modalWares']   = $modalWares;
        $params['orders']       = $orders;
        $params['companies']    = $companies;
        $params['products']     = $products;
        $params['warehouses']   = $warehouses;
        $params['channels']     = $channels;
        $params['payments']     = $payments;
        $params['carriers']     = $carriers;
        $params['currencies']   = $currencies;
        $params['countries']    = $countries;

        if(isset($_GET['viewType']) && $_GET['viewType'] == "expert") {
            Session::put("orderViewType", $_GET['viewType']);
        } 

        if(isset($_GET['viewType']) && $_GET['viewType'] == "normal") {
            Session::forget("orderViewType");
        }

        return View::make('orderView', $params);
    }


    public function reportDocuments(Request $request){
        $documents = Document::orderBy('id','desc')->paginate(50);
        return View::make('order_report_documents', compact('documents'));
    }
    
    public function reportDocumentsDownload(Request $request){
        $id = $request->id;
        if($id > 0){
            try{
                $document = Document::find($id);
                if($document){
                    $file = public_path($document->file);
                    return response()->download($file);
                }
            }catch(\Throwable  $e){
                return redirect()->back()->with('error','Sorry, This file doesn\'t exist!');
            }
        }else{
            return redirect()->back()->with('error','Invalid request');
        }
    }

    public function getInvoiceForm(Request $request) {
        $idorder        = $request->id;
        $row = OrderItem::find($idorder);
        $countries  = DB::table("country")->get();
        return response()->json(['success'=>true, 'html'=> view('ajax.order_invoice_form', compact('row','countries'))->render()]);
    }

    public function orderUpdate(Request $request) {
        
        if($request->action == 'sku_update'){
           
            $idorder        = $request->id;
            $fieldValue     = $request->value; 
            $product = Product::where('sku', $fieldValue)->first();
            
            if(!$product){
               return response()->json(['success'=>false, 'message'=>'Product not found.']); 
            }
            
            $order = OrderItem::find($idorder);
            $order->productid = $product->productid;
            $order->save();
            
            return response()->json(['success'=>true, 'data'=> view('ajax.order', compact('order'))->render()]);
           
        }else if($request->action == 'orderinvoice'){
            
            $order = OrderItem::find($request->id);
            $order->orderInvoice()->update([$request->field => $request->value]);
            $order->save();
            return response()->json(['success'=>true]);
           
        }else if($request->action == 'orderpriceupdate'){
          
             DB::table('orderitem')
                ->where('idorder', '=',$request->id)
                ->update([
                    $request->field    => $request->value
                ]);
                
            return response()->json(['success'=>true]);
           
        }else if($request->action == 'update_channel'){
          
             DB::table('channel')
                ->where('idchannel', '=',$request->id)
                ->update([
                    $request->field    => $request->value
                ]);
                
            return response()->json(['success'=>true]);
           
        }else{
            $idorder        = $_GET["id"];
            $fieldName      = $_GET["field"];
            $fieldValue     = $_GET["value"]; 
    
            $order = DB::table('orderitem')
                    ->where('idorder', '=', $idorder)
                    ->first();
    
            DB::table('orderitem')
                ->where('referenceorder', '=', $order->referenceorder)
                ->update([
                    $fieldName    => $fieldValue
                ]);
            return 'success';
        }
    }

    public function orderDelete() {
        $type   = $_GET["type"];
        $id     = $_GET['del'];

        if($type=="delete_order"){
            DB::table('orderitem')
                ->where('idorder', '=', $id)
                ->update([
                    'sum'                       => '0.0',
                    'printedshippingok'         => '1',
                    'registeredtosolddayok'	    => '1',
                    'courierinformedok'         => '1',
                    'trackinguploadedok'        => '1',
                    'registeredtolagerstandok'  => 'Deleted',
                    'carriername'               => 'Deleted',
                    'idpayment'                 => 'Deleted',
                    'is_deleted'                => 1
                ]);
        } else if($type=="set_as_done"){
            DB::table('orderitem')
                ->where('idorder', '=', $id)
                ->update([
                    'printedshippingok'         => '1',
                    'registeredtosolddayok'	    => '1',
                    'courierinformedok'         => '1',
                    'trackinguploadedok'        => '1',
                    'registeredtolagerstandok'  => '1',
                    'tracking'                  => '---',
                    'carriername'               => '---'
                ]);
        } else if($type=="set_as_not_done"){
            DB::table('orderitem')
                ->where('idorder', '=', $id)
                ->update([
                    'printedshippingok'         => '0',
                    'registeredtosolddayok'	    => '0',
                    'courierinformedok'         => '0',
                    'trackinguploadedok'        => '0',
                    'registeredtolagerstandok'  => '0'
                ]);
        }
        return redirect()->route('orderView');
    }

    public function orderAddView() {

        if(!Session::has('userID')) {

            return redirect()->route('login');

        }

        $qcurrency  = DB::table('currency')->get();

        $qcountry   = DB::table('country')->get();

        $companies  = DB::table('companyinfo')->get();

        $channels   = DB::table("channel")->get();

        $products   = DB::table('product')->get();

        $payments   = DB::table("payment")->get();

        $warehouses = DB::table("warehouse")->get();

        $platformsShort  = DB::table('platform')

                    ->leftjoin('channel', 'channel.platformid', '=', 'platform.platformid')

                    ->select('channel.idchannel as channelId', 'channel.platformid', 'channel.shortname as channelname', 'platform.shortname as platformname')

                    ->get();



        $platformsLong  = DB::table('platform')

                    ->leftjoin('channel', 'channel.platformid', '=', 'platform.platformid')

                    ->select('channel.*', 'platform.longname as platformname')

                    ->get();



        $params['companies']        = $companies;

        $params['qcurrency']        = $qcurrency;

        $params['qcountry']         = $qcountry;

        $params['channels']         = $channels;

        $params['products']         = $products;

        $params['platformsShort']   = $platformsShort;

        $params['platformsLong']    = $platformsLong;

        $params['payments']         = $payments;

        $params['warehouses']       = $warehouses;

        return View::make('orderAddView', $params);

    }

    
    public function orderAdd(Request $request) {

        if ($request->isMethod('post') && $request->ajax()) {
             $validator = Validator::make($request->all(),[
                'currency'            => 'required',
                'idpayment'             => 'required',
                'idwarehouse'                 => 'required',
                'referenceorder'=>'required'
            ]);
            
            
            if($validator->fails()){
                return response()->json(['error'=>$validator->errors()],401);
            }
            //validation end
            
            $date = date('Y-m-d', strtotime($request->date));
            $channel = Channel::find($request->referencechannel);
            $order = new OrderItem();
            
            foreach($request->all() as $key=>$value){
                if( in_array( $key,$order->getFillable() ) ){
                    $order->$key = $value;
                }
            }
            
            $order->inv_customer = $order->customer;
            $order->inv_customerextra = $order->customerextra;
            $order->inv_address1 = $order->address1;
            $order->inv_address2 = $order->address2;
            $order->plz1 = $order->plz;
            $order->city1 = $order->city;
            $order->region1 = $order->region;
            $order->country1 = $order->country;
            $order->telefon1 = $order->telefon;
            $order->fax1 = $order->fax;
            $order->email1 = $order->email;
            
            $order->datee = $date;
            $order->referencechannelname = $channel ? $channel->shortname : '';
            $order->save();
            
            $countqty = $request->totaladdrow;
           
            if($countqty > 0){
                
                for($i=0; $i< $countqty; $i++){
                     
                    $quantity   =  ($request->quantity2)[$i];
                    $product_id =  ($request->productidval2)[$i];
                        
                    $product = Product::find($product_id);
                    $modelcode  = $product->modelcode;
                    $nameshort  = $product->nameshort;
                    $notes      = $modelcode.' '.$nameshort;   
                    
                    if($i == 0){

                        $order->quantity = $quantity;
                        $order->productid = $product_id;
                        $order->multiorder = '0';
                        $order->notes = $notes;
                        $order->save();
                        
                        
                    }else{
                        
                        $order2 = new OrderItem();
                        foreach($request->all() as $key=>$value){
                            if( in_array( $key,$order2->getFillable() ) ){
                                $order2->$key = $value;
                            }
                        }
                        
                        $order2->inv_customer = $order2->customer;
                        $order2->inv_customerextra = $order2->customerextra;
                        $order2->inv_address1 = $order2->address1;
                        $order2->inv_address2 = $order2->address2;
                        $order2->plz1 = $order2->plz;
                        $order2->city1 = $order2->city;
                        $order2->region1 = $order2->region;
                        $order2->country1 = $order2->country;
                        $order2->telefon1 = $order2->telefon;
                        $order2->fax1 = $order2->fax;
                        $order2->email1 = $order2->email;                        
                        
                        $order2->datee = $date;
                        $order2->referencechannelname = $channel ? $channel->shortname : '';
                        $order2->multiorder = $request->referenceorder;
                        $order2->quantity = $quantity;
                        $order2->productid = $product_id;
                        $order2->notes = $notes;
                        $order2->save();
                        
                    }
                    
                }
            }
         
            return response()->json([
                    'success'       =>  true,
                    'data'          => [],
                    'message'       => 'Order has been created.',
                    'redirect_url'  => url('orderView')
            ]);

        }
    }
    

    public function orderAdd1(Request $request) {

        $date               = Carbon::parse(date('Y-m-d'));

        $week               = $date->weekNumberInMonth;
        $idcompany          = $request->idcompany;
        $weeksell           = $week;
        $date               = $request->date;
        $quantity           = $request->quantity;
        $productid          = $request->productid;
        $sum                = $request->sum;
        $currency           = $request->currency;
        $idpayment          = $request->idpayment;
        $idchannel          = 0;
        $idwarehouse        = $request->idwarehouse;
        $referenceorder     = $request->referenceorder;
        $customer           = $request->customer;
        $customerextra      = $request->customerextra;
        $address1           = $request->address1;
        $address2           = $request->address2;
        $plz                = $request->plz;
        $city               = $request->city;
        $region             = $request->region;
        $country            = $request->country;
        $telefon            = $request->telefon;
        $fax                = $request->fax;
        $email              = $request->email;
        $invoicenr          = $request->invoicenr;
        $inv_customer       = $request->inv_customer;
        $inv_customerextra  = $request->inv_customerextra;
        $inv_vat            = $request->inv_vat;
        $inv_address1       = $request->inv_address1;
        $inv_address2       = $request->inv_address2;
        $plz1               = $request->plz1;
        $city1              = $request->city1;
        $region1            = $request->region1;
        $country1           = $request->country1;
        $telefon1           = $request->telefon1;
        $fax1               = $request->fax1;
        $email1             = $request->email1;
        $notes              = $request->notes;
        $order_item_id      = $request->order_item_id;
        $ship_service_level = $request->ship_service_level;
        $multiorder         = '0';


        if(isset($request->registeredtolagerstandok)){

            $registeredtolagerstandok = $request->registeredtolagerstandok;

        }else{

            $registeredtolagerstandok = "0";#default value

        }



        $registeredtolagerstandok = "0";#default value



        if(isset($request->registeredtosolddayok)){

            $registeredtosolddayok = $request->registeredtosolddayok;

        }else{

            $registeredtosolddayok = "0";#default value

        }



        $registeredtosolddayok = "0";#default value



        if(isset($request->courierinformedok)){

            $courierinformedok = $request->courierinformedok;

        }else{

            $courierinformedok = "0";#default value

        }



        $courierinformedok = "0";#default value



        if(isset($request->trackinguploadedok)){

            $trackinguploadedok = $request->trackinguploadedok;

        }else{

            $trackinguploadedok = "0";#default value

        }



        $trackinguploadedok = "0";#default value



        if(isset($request->referencechannel)){

            $referencechannel = $request->referencechannel;

            $duplicate = DB::table('orderitem')

                        ->where('referencechannel', '=', $referencechannel)

                        ->get();



            if (count($duplicate) > 0) {

                $message = "Duplicate Refference Channel";

            }else{



            }

        }

        

        $dbid = DB::table('orderitem')

                ->insertGetId([

                    'idcompany'                 => $idcompany,

                    'referencechannel'          => $referencechannel,

                    'weeksell'                  => $weeksell,

                    'datee'                     => $date,

                    'multiorder'                => $multiorder,

                    'quantity'                  => $quantity,

                    'productid'                 => $productid,

                    'sum'                       => $sum,

                    'currency'                  => $currency,

                    'idpayment'                 => $idpayment,

                    'idchannel'                 => $idchannel,

                    'idwarehouse'               => $idwarehouse,

                    'referenceorder'            => $referenceorder,

                    'customer'                  => $customer,

                    'customerextra'             => $customerextra,

                    'address1'                  => $address1,

                    'address2'                  => $address2,

                    'plz'                       => $plz,

                    'city'                      => $city,

                    'region'                    => $region,

                    'country'                   => $country,

                    'telefon'                   => $telefon,

                    'fax'                       => $fax,

                    'email'                     => $email,

                    'invoicenr'                 => $invoicenr,

                    'inv_customer'              => $inv_customer,

                    'inv_customerextra'         => $inv_customerextra,

                    'inv_vat'                   => $inv_vat,

                    'inv_address1'              => $inv_address1,

                    'inv_address2'              => $inv_address2,

                    'plz1'                      => $plz1,

                    'city1'                     => $city1,

                    'region1'                   => $region1,

                    'country1'                  => $country1,

                    'telefon1'                  => $telefon1,

                    'fax1'                      => $fax1,

                    'email1'                    => $email1,

                    'registeredtolagerstandok'  => $registeredtolagerstandok,

                    'registeredtosolddayok'     => $registeredtosolddayok,

                    'courierinformedok'         => $courierinformedok,

                    'trackinguploadedok'        => $trackinguploadedok,

                    'notes'                     => $notes,

                    'order_item_id'             => $order_item_id,

                    'ship_service_level'        => $ship_service_level

                ]);

        

        $countqty = $request->totaladdrow;

        if($countqty == 1) {

            for($i=0; $i< $countqty; $i++){

                $quantity   =  ($request->quantity2)[$i];

                $product_id =  ($request->productidval2)[$i];

                $arr = DB::table('product')

                        ->where('productid', '=', $product_id)

                        ->get();

                

                $modelcode  = $arr[0]->modelcode;

                $nameshort  = $arr[0]->nameshort;

                $notes      = $modelcode.' '.$nameshort;



                DB::table('orderitem')

                    ->where('idorder', '=', $dbid)

                    ->update([

                        'quantity'         => $quantity,

                        'productid'        => $product_id,

                        'notes'            => $notes

                    ]);

            }      

        } else {

            if($request->totaladdrow > 0){

                for($i=0; $i<$request->totaladdrow; $i++){

                    $quantity   = ($request->quantity2)[$i];

                    $product_id = ($request->productidval2)[$i];

                    

                    $arr = DB::table('product')

                        ->where('productid', '=', $product_id)

                        ->get();

                

                    $modelcode  = $arr[0]->modelcode;

                    $nameshort  = $arr[0]->nameshort;

                    $notes      = $modelcode.' '.$nameshort;

                    

                    if($i==0) {

                        DB::table('orderitem')

                            ->where('idorder', '=', $dbid)

                            ->update([

                                'quantity'         => $quantity,

                                'productid'        => $product_id,

                                'notes'            => $notes,

                                'multiorder'       => '1'

                            ]);

                    }else if($i>=1) {

                        DB::table('orderitem')

                            ->insertGetId([

                                'idcompany'                 => $idcompany,

                                'referencechannel'          => $referencechannel,

                                'weeksell'                  => $weeksell,

                                'datee'                     => $date,

                                'multiorder'                => $multiorder,

                                'quantity'                  => $quantity,

                                'productid'                 => $product_id,

                                'idchannel'                 => $idchannel,

                                'idwarehouse'               => $idwarehouse,

                                'referenceorder'            => $referenceorder,

                                'invoicenr'                 => $invoicenr,

                                'notes'                     => $notes,

                                'order_item_id'             => $order_item_id

                            ]);

                    }

                

                }

            }

        }



        return redirect()->route('orderView');

    }



    public function addAmazonAddress(Request $request) {

        if($request->hasFile('fileToUpload')) {

            $file   = $request->file('fileToUpload');

            $name   = time() . '-' . $file->getClientOriginalName();

            $path   = storage_path('documents');

            $dir    = storage_path('documents').'/'.$name;

            $file->move($path, $name);



            $file       = file_get_contents($dir);

            $file       = explode("\n", $file); // this is your array of words

            

            

            $totalrows  = count($file);

            for($i=1; $i<$totalrows; $i++) {

                $row    = explode("\t", $file[$i]);

                if(isset($row[5])) {                    

                    $orderId            = $row[0];

                    $orderItemId        = $row[1];

                    $customer           = $row[16];

                    $address1           = $row[17];

                    $address2           = $row[18];

                    $country            = $row[23];

                    $city               = $row[20];

                    $region             = $row[21];

                    $telefon            = $row[9];

                    $plz                = $row[22];

                    $inv_customer       = $row[8];

                    $inv_customerextra  = $row[19];

                    $inv_address1       = $row[17];

                    $inv_address2       = $row[18];

                    $plz1               = $row[22];

                    $city1              = $row[20];

                    $region1            = $row[21];

                    $country1           = $row[23];

                    

                    $existingOrder = DB::table('orderitem')

                                        ->where('idorderplatform', '=', $orderId)

                                        ->where('multiorder', '=', '0')

                                        ->first();

                                        

                    if(!empty($existingOrder)) {

                        DB::table('orderitem')

                            ->where('idorder', '=', $existingOrder->idorder)

                            ->update([

                                'customer'          => $customer,

                                'address1'          => $address1,

                                'address2'          => $address2,

                                'country'           => $country,

                                'city'              => $city,

                                'region'            => $region,

                                'telefon'           => $telefon,

                                'plz'               => $plz,

                                'inv_customerextra' => $inv_customerextra,

                                'inv_customer'      => $inv_customer,

                                'inv_address1'      => $inv_address1,

                                'inv_address2'      => $inv_address2,

                                'plz1'              => $plz1,

                                'city1'             => $city1,

                                'region1'           => $region1,

                                'country1'          => $country1

                            ]);

                    }

                }

            }

        }



       return redirect()->route('orderView');

    }



    public function importOrderFile(Request $request) {

        $date       = Carbon::parse(date('Y-m-d'));

        $week       = $date->format("W");

        $currency   = "";

        $noneProducts = [];

        if($request->hasFile('uploadfilename')) {

            $files  = $request->file('uploadfilename');

            $checks = $request->check;



            $fileArrKey = array_keys($files);

            for($i = 0; $i < count($fileArrKey); $i++) {

                $path            = $files[$fileArrKey[$i]]->getRealPath();

                $name            = time() . '-' . $files[$fileArrKey[$i]]->getClientOriginalName();

                $path            = storage_path('documents');

                $dir             = storage_path('documents').'/'.$name;

                $files[$fileArrKey[$i]]->move($path, $name);

                $check_idexplode = explode("-", $checks[$i]);

                $platformId      = $check_idexplode[0];

                $channelId       = $check_idexplode[1];

                $query           = DB::table('channel')

                                    ->where('channel.idchannel', '=', $channelId)

                                    ->leftjoin('coding', 'channel.codingId', '=', 'coding.codingId')

                                    ->get();



                $channel = $query[0];



                $referencechannel   = $channel->idchannel; //$filepath[0];

                $idcompany          = $channel->idcompany;

                $idwarehouse        = $channel->warehouse;

                $inv_vat            = $channel->vat;

                $channelname        = $channel->shortname;

                $coding             = $channel->coding;

                $p                  = ['platformId' => $platformId];

                $fieldsdatafetch    = DB::table('platformuploadsettings')

                                        ->where('platformId', '=', $platformId)

                                        ->get();

                        

                foreach($fieldsdatafetch as $data) {

                    $fieldsnamearray[]  = $data->fieldname;

                    $fieldsvaluearray[] = $data->arraynumber;

                }



                $platformdatafetch      = DB::table('platform')

                                            ->where('platformId', '=', $platformId)

                                            ->get();



                $platformtype           = $platformdatafetch[0]->platformtype;

                $fileseparator          = $platformdatafetch[0]->fileseparator;

                $filecolumns            = $platformdatafetch[0]->filecolumns;

                $filedatastartrow       = $platformdatafetch[0]->filedatastartrow;

                $filecontenttype        = $platformdatafetch[0]->filecontenttype;

                $filetype               = $platformdatafetch[0]->filetype;

                $platformname           = $platformdatafetch[0]->shortname;

                $idorderplatform        = $platformdatafetch[0]->platformid;



                $fewmorefieldsarray     = [];

                $fewmorefields          = "";

                $staticfieldsonearray   = ['referencechannel'           => $referencechannel, 

                                        'idcompany'                  => $idcompany, 

                                        'idwarehouse'                => $idwarehouse, 

                                        'inv_vat'                    => $inv_vat, 

                                        'weeksell'                   => $week, 

                                        'printedshippingok'          => 0, 

                                        'registeredtolagerstandok'   => 0, 

                                        'registeredtosolddayok'      => 0, 

                                        'courierinformedok'          => 0, 

                                        'trackinguploadedok'         => 0, 

                                        'platformname'               => $platformname, 

                                        'referencechannelname'       => $channelname, 

                                        'idorderplatform'            => $platformId, 

                                        'idchannel'                  => $referencechannel];

                if($files[$fileArrKey[$i]]->getClientOriginalExtension() == "csv") {

                    $data = $this->csvToArray(storage_path('documents')."/".$name, ",");



                    if($checks[$i] == '15-31') {

                        $rowscount          = count($data);

                        for($j=3; $j<$rowscount; $j++) {

                            $columnscount       = count($data[$j]);

                            $pallorderitem      = [];

                            $fieldsoforderitem  = "";

                            $fieldoforderitem   = "";

                        }

                    }

                }

                $filetype = $files[$fileArrKey[$i]]->getClientOriginalExtension();

                if($files[$fileArrKey[$i]]->getClientOriginalExtension() == "txt"){

					$file       = file_get_contents($dir);

					$file       = explode("\n", $file); // this is your array of words

					$totalrows  = count($file);

				} else if($files[$fileArrKey[$i]]->getClientOriginalExtension() == "csv") {

					$file       = file_get_contents($dir);

					$file       = explode("\n", $file);

                    $totalrows  = count($file);

                } else {

                    $xlsx=SimpleXLSX::parse($dir);

					$file=$xlsx->rows();

					$totalrows=count($xlsx->rows());

                    list($totalcolumns,$totalrowstobeused)=$xlsx->dimension();

                }              

                                                                  

                // Logic for xlsx and Cdiscount file

				if($coding == 'Cdiscount-01'){

					for($loopfile=0; $loopfile<$totalrows; $loopfile++) {

						if($loopfile >= 6) {

							if($filetype=="txt") {

								$row    = $file[$loopfile];

								$row    = explode("\t",$row);

							}else if($filetype=="xlsx"){

								$row    = $file[$loopfile];

							} else if($filetype=="csv") {

								$row    = str_getcsv($file[$loopfile], $fileseparator);

                            }

                            

                            if(isset($row[1])) {

                                if($row[10] != "INTERETBCA" && utf8_encode($row[11]) != "Annulée" && $row[11] != "Annulée" && $row[11] != "Refusé") {

                                    if($filetype=="txt") {

                                        $orderNo = $row[1];	

                                    }

                                    

                                    $columnscount       = count($row);

                                    $pallorderitem      = [];

                                    $productId          = "";

                                    $region             = '';

                                    $currency           = '';

                                    $datee              = date("Y-m-d H:i:s", strtotime($row[3]));

                                    $checkean           = $row[9];

                                    $quantity           = $row[12];

                                    if(isset($row[9]) && $row[9] != ""){

                                        $p         = ['ean' => $checkean];

                                        $qryres    = DB::table('product')

                                                        ->where('ean', '=', $checkean)

                                                        ->first();

                                        

                                        if(!empty($qryres)) {

                                            $productid  = $qryres->productid;

                                            $sku        = $qryres->sku;

                                        } else {

                                            $productid  = $this->product_check_insert_two('ean', $checkean, $channel->warehouse, $quantity, $checkean);

                                            $sku        = $checkean;

                                            array_push($noneProducts, $checkean);

                                        }

                                    }else{

                                        $productid      = $this->product_check_insert_two('ean', $checkean, $channel->warehouse, $quantity, $checkean);

                                        $sku            = $checkean;

                                        array_push($noneProducts, $checkean);

                                    }

                                    

                                    $item_price         = $row[13];

                                    $item_price1        = $row[14];

                                    $sumtotal           = round((float)$item_price + (float)$item_price1, 2);

                                    $customer           = $row[18]." ".$row[19];

                                    $address1           = $row[26];

                                    $plz                = $row[21];

                                    $city               = $row[22];

                                    $referenceorder     = $row[1];

                                    $country            = $row[23];

                                    $fax1               = $row[24];

                                    $telefon1           = $row[25];

                                    $customer1          = $row[18]." ".$row[19];

                                    $plz1               = $row[27];

                                    $city1              = $row[28];

                                    $country1           = $row[29];

                                    $note1              = $productid.' '.$row[9];

                                    $notes              = $row[9];

                                    

                                    $countrycode = DB::table('country')

                                            ->where('longname', '=', $country)

                                            ->get();

        

                                    if(count($countrycode) > 0) {	

                                        $country    = $countrycode[0]->shortname;

                                        $country1   = $countrycode[0]->shortname;

                                    }

        

                                    if($currency =="") {

                                        $query = DB::table('country')

                                                    ->where('shortname', '=', $country)

                                                    ->get();

                                        if(count($query) > 0) {

                                            $currency = $query[0]->currency;

                                        }

                                    }

                                    if($currency =="")

                                        $currency   ="EUR";

        

                                    $moveme             = 0;

                                    $datee1             = explode(" ", $row[3]);

                                    $date2              = explode("/", $datee1[0]);

                                    

                                    if(count($date2)>2) {

                                        $datee2 = $date2[2].'-'.$date2[1].'-'.$date2[0];

                                    } else {

                                        $date2  = explode("T", $datee1[0]);

                                        $datee2 = $date2[0];

                                    }

        

                                    $pallorderitem["datee"] = $datee2;

        

                                    $query = DB::table('orderitem')

                                                ->where('referenceorder', '=', $referenceorder)

                                                ->get();

        

                                    $orderId            = "";

                                    if(count($query) > 0) {

                                        $orderId                        = $query[0]->idorder;

                                        $pallorderitem['multiorder']    =  $referenceorder;

                                    }

                                    

                                    $order_item_id      = $row[1];

                                    $checkIsDuplicate   = $this->isDuplicate($referenceorder, $order_item_id, $productid);



                                    $pallorderitem['telefon']        =  $telefon1;

                                    $pallorderitem['idpayment']      =  'cDiscount';

                                    $pallorderitem['fax']            =  $fax1;

                                    $pallorderitem['inv_customer']   =  $customer;

                                    $pallorderitem['inv_address1']   =  $address1;

                                    $pallorderitem['plz1']           =  $plz1;

                                    $pallorderitem['plz']            =  $plz;

                                    $pallorderitem['city1']          =  $city1;

                                    $pallorderitem['country1']       =  $country1;

                                    $pallorderitem['fax1']           =  $fax1;

                                    $pallorderitem['address1']       =  $address1;

                                    $pallorderitem['sum']            =  $sumtotal;

                                    $pallorderitem['quantity']       =  $quantity;

                                    $pallorderitem['currency']       =  $currency;

                                    $pallorderitem['customer']       =  $customer;

                                    $pallorderitem['country']        =  $country;

                                    $pallorderitem['city']           =  $city;

                                    $pallorderitem['region']         =  $region;

                                    $pallorderitem['referenceorder'] =  $referenceorder;

                                    $pallorderitem['productid']      =  $productid;

                                    $pallorderitem['telefon1']       =  $telefon1;

                                    $pallorderitem['order_item_id']  =  $order_item_id;

                                    $pallorderitem['notes']          =  $notes;



                                    $pallorderitem  = array_merge($pallorderitem, $staticfieldsonearray);

                                    if($row[5] == "C Logistique") {

                                        $pallorderitem['registeredtolagerstandok']  = 1;

                                        $pallorderitem['registeredtosolddayok']     = 1;

                                        $pallorderitem['courierinformedok']         = 1;

                                        $pallorderitem['trackinguploadedok']        = 1;

                                        $pallorderitem['printedshippingok']         = 1;

                                        $pallorderitem['tracking']                  = '999999999';

                                        $pallorderitem['carriername']               = 'C-Logistique';

                                    }



                                    if($checkIsDuplicate == 0) {

                                        if($filetype == "txt") {

                                            //check amazon AM-PersonalcityCom

                                            if($filetype=="txt" && $checks[$i] == "7-80") {

                                                if($ship_promotion_id != "") {

                                                    $insert_id = DB::table('orderitem')

                                                        ->insertGetId($pallorderitem);

                                                    DB::table('orderitem')

                                                        ->where('idorder', '=', $insert_id)

                                                        ->update([

                                                            'sum'    => $sumtotal

                                                        ]);

                                                    }

                                            } else {

                                                $insert_id = DB::table('orderitem')

                                                        ->insertGetId($pallorderitem);

                                                DB::table('orderitem')

                                                    ->where('idorder', '=', $insert_id)

                                                    ->update([

                                                        'sum'    => $sumtotal

                                                    ]);

                                            }

                                            

                                        } else {

                                            if($row[5] == "C Logistique") {

                                                $pallorderitem['registeredtolagerstandok']  = 1;

                                                $pallorderitem['registeredtosolddayok']     = 1;

                                                $pallorderitem['courierinformedok']         = 1;

                                                $pallorderitem['trackinguploadedok']        = 1;

                                                $pallorderitem['printedshippingok']         = 1;

                                                $pallorderitem['tracking']                  = '999999999';

                                                $pallorderitem['carriername']               = 'C-Logistique';

                                            }

                                            $insert_id = DB::table('orderitem')->insertGetId($pallorderitem);

                                        }

                                    }

                                }

                            }

						}

					} // end of reading file

				} else if($coding == 'EbayIT-01') {

                    $file = fopen($dir, "r");

			        $newArr = array();



			        while (!feof($file)) {

			            $data = fgetcsv($file, null, ';');

			            array_push($newArr, $data);

                    }

                    

                    for($i=3; $i<count($newArr); $i++){

                		$quantity       = $newArr[$i][24];

                        $idpayment      = $newArr[$i][40];

                        $getdatemonth   = explode("-", $newArr[$i][41]);

                        if(isset($newArr[$i][41]) && $newArr[$i][41] != "") {

                            $dateday        = $getdatemonth[0];

                            $month          = $getdatemonth[1];

                            $dateyear       = $getdatemonth[2];

                        }



                		if($month == 'gen'){

							$datemonth = "01";

						}else if($month == 'feb'){

							$datemonth = "02";

						}else if($month == 'mar'){

							$datemonth = "03";

						}else if($month == 'apr'){

							$datemonth = "04";

						}else if($month == 'mag'){

							$datemonth = "05";

						}else if($month == 'giu'){

							$datemonth = "06";

						}else if($month == 'lug'){

							$datemonth = "07";

						}else if($month == 'aug'){

							$datemonth = "08";

						}else if($month == 'set'){

							$datemonth = "09";

						}else if(strtolower($month) == 'ott' || strtolower($month) == 'okt' || strtolower($month) == 'oct'){

							$datemonth = "10";

						}else if($month == 'nov'){

							$datemonth = "11";

						}else if($month == 'dic'){

							$datemonth = "12";

						}



						$year   = date("Y");

						$year1  = substr($year, 0, 2);

						$datee  = $year1.$dateyear.'-'.$datemonth.'-'.$dateday;



						$date = $this->checkmydate($datee);

						if($date==1){

							$newdatee = $datee;

						}else{

							$newdatee = '';

						}



                		$productid  = substr($newArr[$i][22], 0, 5);

                		$getsum     = explode(" ", $newArr[$i][38]);

                		$sum        = str_replace(",",".",$getsum[0]);

                            

                        if($currency =="" && isset($getsum[1])) {

                            $query = DB::table('country')

                                        ->where('shortname', '=', $getsum[1])

                                        ->get();

                            if(count($query) > 0) {

                                $currency = $query[0]->currency;

                            }

                        }



                        if($currency =="")

                            $currency   ="EUR";



                		$referenceorder = $newArr[$i][1];

                		$sale           = $newArr[$i][2];

                		$invoicenr      = $newArr[$i][2];

                		$inv_customer   = $newArr[$i][3];

                		$inv_address1   = $newArr[$i][6];

                		$inv_address2   = $newArr[$i][7];

                		$city1          = $newArr[$i][8];

                		$region1        = $newArr[$i][9];

                		$customer       = $newArr[$i][12];

                		$telefon        = $newArr[$i][13];

                		$telefon1       = $newArr[$i][13];

                		$address1       = $newArr[$i][14];

                		$address2       = $newArr[$i][15];

                		$city           = $newArr[$i][16];

                		$region         = $newArr[$i][17];

                		$plz            = $newArr[$i][18];

                		$plz1           = $newArr[$i][10];

                		$country        = $newArr[$i][19];

                		$country1       = $newArr[$i][11];

                		$notes          = $newArr[$i][22];

                		$order_item_id  = $newArr[$i][0];

                		$multiorder     = 0;

                         

                        $countrycode = DB::table('country')

                                        ->where('longname', '=', $country)

                                        ->get();



                        if(count($countrycode) > 0) {	

                            $country    = $countrycode[0]->shortname;

                            $country1   = $countrycode[0]->shortname;

                        }



                        $query = DB::table('orderitem')

                            ->where('referenceorder', '=', $referenceorder)

                            ->get();



                        $orderId = "";

                        if(count($query) > 0) {

                            $orderId        = $query[0]->idorder;

                            $multiorder     = $referenceorder;

                            $idpayment      = $query[0]->idpayment;

                        }



					 	$sku                = $notes;

                        $modelcode          = substr($sku, 0, 5);

                        $productExist = DB::table('product')

                                ->where('modelcode', '=', $modelcode)

                                ->first();



                        if(empty($productExist)) {

                            array_push($noneProducts, $modelcode);

                        }

						$productId          = $this->product_check_insert($modelcode, $sku, $channel->warehouse, $quantity);

                        $checkIsDuplicate   = $this->isDuplicate($referenceorder,$order_item_id, $productId);



					 	if($referenceorder!="" && $sale!="" && $checkIsDuplicate ==0) {

                            if($referenceorder!="") {

                                DB::table('orderitem')

                                    ->insert([

                                        'idorderplatform'       => $platformId,

                                        'referencechannelname'  => $channelname,

                                        'platformname'          => $platformname,

                                        'referencechannel'      => $referencechannel,

                                        'weeksell'              => $week,

                                        'idchannel'             => $referencechannel,

                                        'idwarehouse'           => $idwarehouse,

                                        'inv_vat'               => $inv_vat,

                                        'quantity'              => $quantity,

                                        'idpayment'             => $idpayment,

                                        'productid'             => $productId,

                                        'referenceorder'        => $referenceorder,

                                        'invoicenr'             => $invoicenr,

                                        'customer'              => $customer,

                                        'telefon'               => $telefon,

                                        'address1'              => $address1,

                                        'address2'              => $address2,

                                        'city'                  => $city,

                                        'region'                => $region,

                                        'plz'                   => $plz,

                                        'country'               => $country,

                                        'country1'              => $country1,

                                        'sum'                   => $sum,

                                        'currency'              => $currency,

                                        'datee'                 => $newdatee,

                                        'multiorder'            => $multiorder,

                                        'idcompany'             => $idcompany,

                                        'inv_customer'          => $inv_customer,

                                        'inv_address1'          => $inv_address1,

                                        'inv_address2'          => $inv_address2,

                                        'city1'                 => $city1,

                                        'region1'               => $region1,

                                        'notes'                 => $notes,

                                        'plz1'                  => $plz1,

                                        'order_item_id'         => $order_item_id,

                                        'telefon1'              => $telefon1

                                    ]);

                            }

					 	}

                	}

				} else if($coding == 'EbayDE-01') {

                    $file = fopen($dir, "r");

			        $newArr = array();



			        while (!feof($file)) {

			            $data = fgetcsv($file, null, ';');

			            array_push($newArr, $data);

                    }

                    

			        while (!feof($file)) {

			            $data = fgetcsv($file, null, ';');

			            array_push($newArr, $data);

                    }

                    

                    for($i=3; $i<count($newArr); $i++){

                		$quantity       = $newArr[$i][24];                        

                        $idpayment      = $newArr[$i][40];

                        $getdatemonth   = explode("-", $newArr[$i][41]);

                        if(isset($newArr[$i][41]) && $newArr[$i][41] != "") {

                            $dateday        = $getdatemonth[0];

                            $month          = $getdatemonth[1];

                            $dateyear       = $getdatemonth[2];

                        }

                                                

                		if(strtolower($month) == 'gen'){

							$datemonth = "01";

						}else if(strtolower($month) == 'feb'){

							$datemonth = "02";

						}else if(strtolower($month) == 'mar' || strtolower($month) == 'mrz') {

							$datemonth = "03";

						}else if(strtolower($month) == 'apr'){

							$datemonth = "04";

						}else if(strtolower($month) == 'mag'){

							$datemonth = "05";

						}else if(strtolower($month) == 'giu'){

							$datemonth = "06";

						}else if(strtolower($month) == 'lug'){

							$datemonth = "07";

						}else if(strtolower($month) == 'aug'){

							$datemonth = "08";

						}else if(strtolower($month) == 'set' || strtolower($month) == 'sep'){

							$datemonth = "09";

						}else if(strtolower($month) == 'ott' || strtolower($month) == 'okt' || strtolower($month) == 'oct'){

							$datemonth = "10";

						}else if(strtolower($month) == 'nov'){

							$datemonth = "11";

						}else if(strtolower($month) == 'dic' || strtolower($month) == 'dez'){

							$datemonth = "12";

						}



						$year   = date("Y");

						$year1  = substr($year, 0, 2);

						$datee  = $year1.$dateyear.'-'.$datemonth.'-'.$dateday;



						$date = $this->checkmydate($datee);

						if($date==1){

							$newdatee = $datee;

						}else{

							$newdatee = '';

						}



                		$getsum     = explode(" ", $newArr[$i][38]);

                		$sum        = str_replace(",",".",$getsum[0]);

                            

                        if($currency =="" && isset($getsum[1])) {

                            $query  = DB::table('country')

                                        ->where('shortname', '=', $getsum[1])

                                        ->get();

                            if(count($query) > 0) {

                                $currency = $query[0]->currency;

                            }

                        }



                        if($currency =="")

                            $currency   ="EUR";



                		$referenceorder = $newArr[$i][1];

                		$sale           = $newArr[$i][2];

                		$invoicenr      = $newArr[$i][2];

                		$inv_customer   = $newArr[$i][3];

                		$inv_address1   = $newArr[$i][6];

                		$inv_address2   = $newArr[$i][7];

                		$city1          = $newArr[$i][8];

                		$region1        = $newArr[$i][9];

                		$customer       = $newArr[$i][12];

                		$telefon        = $newArr[$i][13];

                		$telefon1       = $newArr[$i][13];

                		$address1       = $newArr[$i][14];

                		$address2       = $newArr[$i][15];

                		$city           = $newArr[$i][16];

                		$region         = $newArr[$i][17];

                		$plz            = $newArr[$i][18];

                		$plz1           = $newArr[$i][10];

                		$country        = $newArr[$i][19];

                		$country1       = $newArr[$i][11];

                		$notes          = $newArr[$i][22];

                		$order_item_id  = $newArr[$i][0];

                		$multiorder     = 0;



                        $countrycode = DB::table('country')

                                        ->where('longname', '=', $country)

                                        ->get();



                        if(count($countrycode) > 0) {	

                            $country    = $countrycode[0]->shortname;

                            $country1   = $countrycode[0]->shortname;

                        }



                        $query = DB::table('orderitem')

                            ->where('referenceorder', '=', $referenceorder)

                            ->get();

                        $orderId = "";



                        if(count($query) > 0) {

                            $orderId        = $query[0]->idorder;

                            $idpayment      = $query[0]->idpayment;

                            $multiorder     = $referenceorder;

                        }



					 	$sku                = $notes;

                        $modelcode          = substr($sku, 0, 5);

                        

                        $productExist = DB::table('product')

                                ->where('modelcode', '=', $modelcode)

                                ->first();



                        if(empty($productExist)) {

                            array_push($noneProducts, $modelcode);

                        }



						$productId          = $this->product_check_insert($modelcode, $sku, $channel->warehouse, $quantity);



                        $checkIsDuplicate   = $this->isDuplicate($referenceorder, $order_item_id, $productId);

                        if(strlen($country) > 3) {

                            $countryCode = DB::table('country_conversion')

                                            ->where('longname', '=', $country)

                                            ->first();

                            if(!empty($countryCode)) {

                                $country = $countryCode->shortname;

                            }

                        }



                        if(strlen($country1) > 3) {

                            $countryCode = DB::table('country_conversion')

                                            ->where('longname', '=', $country1)

                                            ->first();

                            if(!empty($countryCode)) {

                                $country1 = $countryCode->shortname;

                            }

                        }



					 	if($referenceorder!="" && $sale!="" && $checkIsDuplicate == 0) {

                            if($referenceorder!="") {

                                DB::table('orderitem')

                                    ->insert([

                                        'idorderplatform'       => $platformId,

                                        'referencechannelname'  => $channelname,

                                        'platformname'          => $platformname,

                                        'referencechannel'      => $referencechannel,

                                        'weeksell'              => $week,

                                        'idchannel'             => $referencechannel,

                                        'idwarehouse'           => $idwarehouse,

                                        'inv_vat'               => $inv_vat,

                                        'quantity'              => $quantity,

                                        'idpayment'             => $idpayment,

                                        'productid'             => $productId,

                                        'referenceorder'        => $referenceorder,

                                        'invoicenr'             => $invoicenr,

                                        'customer'              => $customer,

                                        'telefon'               => $telefon,

                                        'address1'              => $address1,

                                        'address2'              => $address2,

                                        'city'                  => $city,

                                        'region'                => $region,

                                        'plz'                   => $plz,

                                        'country'               => $country,

                                        'country1'              => $country1,

                                        'sum'                   => $sum,

                                        'currency'              => $currency,

                                        'datee'                 => $newdatee,

                                        'multiorder'            => $multiorder,

                                        'idcompany'             => $idcompany,

                                        'inv_customer'          => $inv_customer,

                                        'inv_address1'          => $inv_address1,

                                        'inv_address2'          => $inv_address2,

                                        'city1'                 => $city1,

                                        'region1'               => $region1,

                                        'notes'                 => $notes,

                                        'plz1'                  => $plz1,

                                        'order_item_id'         => $order_item_id,

                                        'telefon1'              => $telefon1

                                    ]);

                            }

					 	}

                	}

				} else if($files[$fileArrKey[$i]]->getClientOriginalExtension() == "csv" && $checks[$i] == "ddddd"){

                    for($loopfile=3; $loopfile<$totalrows; $loopfile++) {

                        if($filetype=="txt") {

                            $row=$file[$loopfile];

                            $row=explode("\t",$row);

                        } else if($filetype=="xlsx") {

                            $row=$file[$loopfile];

                        } else if($filetype=="csv") {

                            $row = explode(';', $file[$loopfile]);

                        }



                        $ship_promotion_id = $row[28];

                        

                        if($filetype=="txt") {

                            $orderNo = $row[1];	

                        }



                        $sumtotal = 'NULL';

                        if($loopfile==1){

                            $item_price     = $row[11];

                            $shipping_price = $row[13];

                            $sumtotal       = ($item_price + $shipping_price);	

                        }

                    

                        $columnscount       = count($row);

                        $pallorderitem      = [];

                        $fieldsoforderitem  = "";

                        $fieldoforderitem   = "";

                        

                        for($loopvalue=0; $loopvalue<count($fieldsvaluearray); $loopvalue++) {

                            if(isset($fieldsvaluearray[$loopvalue]) && $fieldsvaluearray[$loopvalue] != "") {

                                $fieldvalue     = $fieldsvaluearray[$loopvalue];

                                if(isset($fieldsvaluearray[$loopvalue])) {

                                    $fieldname      = $fieldsnamearray[$loopvalue];

                                    if(array_key_exists($loopvalue, $row) && $row[$loopvalue] != "") {

                                        ${$fieldname}   = $row[$fieldvalue];

                                    }

                                }

                            }

                        } // creating fields and their values variables and assigning data





                        $productId="";



                        if($platformtype=="Amazon") { 

                            $sku        = $row[7];

                            $modelcode  = substr($sku, 0, 5);

                            if($checks[$i] =="7-80" && $modelcode == "sku"){

                                $productId= "";

                            }else{

                                $productExist = DB::table('product')

                                        ->where('modelcode', '=', $modelcode)

                                        ->first();



                                if(empty($productExist)) {

                                    array_push($noneProducts, $modelcode);

                                }

                                $productId  = $this->product_check_insert($modelcode, $sku, $channel->warehouse, $quantity);	

                            }								

                            $fewmorefieldsarray = ['idpayment' => 'Amazon'];

                            $fewmorefields      = 'idpayment';

                            $dateexplode        = explode('T', $date);

                            $date               = $dateexplode[0];

                            $sumtotal           = '';

                            $sumtotal           = ($row[11] + $row[13]);

                        } else if($platformtype=="Ebay") { 

                            $notes      = $row[7];

                            $sku        = $notes;

                            $modelcode  = substr($sku, 0, 5);

                            $productExist = DB::table('product')

                                        ->where('modelcode', '=', $modelcode)

                                        ->first();



                            if(empty($productExist)) {

                                array_push($noneProducts, $modelcode);

                            }

                            

                            $productId  = $this->product_check_insert($modelcode,$sku,$channel->warehouse,$quantity);

                            $notes      = trim(substr($notes, 5));

                        } else if($platformtype=="Cdiscount") {

                            $sumtotal           = '';

                            $datee              = date("Y-m-d H:i:s",strtotime($row[3]));

                            $productid          = substr($row[9], 0, 5);

                            $item_price         = explode(" ", $row[13]);

                            $item_price1        = explode(" ", $row[14]);

                            $sumtotal           = ($item_price[0] + $item_price1[0]);

                            $currency           = $item_price[1]; 

                            $idpayment          = 'Cdiscount';

                            $customer           = $row[17].' '.$row[18].' '.$row[19];

                            $address1           = $row[20];

                            $city               = $row[21];

                            $region             = $row[22];

                            $referenceorder     = $row[1];

                            $country            = $row[23];

                            $fax                = $row[24];

                            $telefon            = $row[25];

                            $plz                = $row[27];

                            $sku                = $row[9];

                            $productExist = DB::table('product')

                                    ->where('modelcode', '=', $sku)

                                    ->first();



                            if(empty($productExist)) {

                                array_push($noneProducts, $sku);

                            }

                            $productId          = $this->product_check_insert_two('ean', $sku, $channel->warehouse, $quantity,$sku);

                            $fewmorefieldsarray = ['idpayment' => 'Cdiscount'];

                            $fewmorefields      = "idpayment";

                            $notes              = $row[8];

                        }



                        $countrycode = DB::table('country')

                                        ->where('longname', '=', $country)

                                        ->get();



                        $productIdarray=['productid' => $productId];



                        if(count($countrycode) > 0) {	

                            $country    = $countrycode;

                            $country1   = $countrycode;

                        }

                    

                        $sum    = str_replace("EUR", "", $sum);

                        $sum    = str_replace(",", ".", $sum);

                        $sum    = str_replace(" €", "", $sum);

                        $sum    = str_replace("£", "", $sum);

                        $sum    = trim($sum);



                        if($currency =="") {

                            $query = DB::table('country')

                                        ->where('shortname', '=', $country)

                                        ->get();

                            if(count($query) > 0) {

                                $currency = $query[0];

                            }

                        }

                        if($currency =="")

                            $currency   ="EUR";



                        $moveme=0;



                        foreach($fieldsnamearray as $data) {

                            if($data == "date"){

                                $poforderitem   = ['datee' => ${$data}];

                            } else {

                                $poforderitem   = [$data => ${$data}];

                            }

                            $pallorderitem  = array_merge($pallorderitem,$poforderitem);

                            $moveme++;

                        }

                        

                        $fieldsoforderitem  = trim($fieldsoforderitem);

                        $fieldsoforderitem  = rtrim($fieldsoforderitem, ",");





                        $query = DB::table('orderitem')

                                    ->where('referenceorder', '=', $referenceorder)

                                    ->get();

                        $orderId    = "";

                        $multiorder = [];

                        if(count($query) > 0) {

                            $orderId = $query[0]->idorder;

                            $multiorder = ['multiorder' => $referenceorder, 'idpayment' => $query[0]->idpayment];

                        }



                        $pallorderitem      = array_merge($pallorderitem, $staticfieldsonearray, $productIdarray, $fewmorefieldsarray, $multiorder);

                        $checkIsDuplicate = $this->isDuplicate($referenceorder, $order_item_id, $productId);



                        if($checkIsDuplicate==0) {

                            if($filetype=="txt") {

                                //check amazon AM-PersonalcityCom

                                if($filetype=="txt" && $check_id == "7-80") {

                                    if($ship_promotion_id != "") {

                                        $insert_id = DB::table('orderitem')

                                                        ->insertGetId($pallorderitem);

                                        DB::table('orderitem')

                                            ->where('idorder', '=', $insert_id)

                                            ->update([

                                                'sum'    => $sumtotal

                                            ]);

                                        }  

                                } else {

                                    $insert_id = DB::table('orderitem')

                                                ->insertGetId($pallorderitem);

                                    DB::table('orderitem')

                                        ->where('idorder', '=', $insert_id)

                                        ->update([

                                            'sum'    => $sumtotal

                                        ]);

                                }

                                

                            }else{

                                $insert_id = DB::table('orderitem')->insertGetId($pallorderitem);

                            }

                        }

                    }

                } else if($coding == 'Amazon-01'){

                    $open = fopen(storage_path('documents')."/".$name, 'r');

                    $loopfile = 0;

                    while (!feof($open)) {

                        $getTextLine    = fgets($open);

                        $row            = explode("\t", $getTextLine);

                        if($loopfile > 0) {

                            if(isset($row[1])) {

                                if(isset($row[28])) {

                                    $ship_promotion_id  = $row[28];

                                } else {

                                    $ship_promotion_id  = "";

                                }

                                

                                $orderNo                = $row[1];

                                $item_price             = $row[11];

                                $shipping_price         = $row[13];

                                if($item_price != "" && $shipping_price != "") {

                                    $sumtotal           = round(($item_price + $shipping_price), 2);

                                } else if($item_price != "") {

                                    $sumtotal           = round($item_price, 2);

                                } else if($shipping_price != "") {

                                    $sumtotal           = round($shipping_price, 2);

                                } else {

                                    $sumtotal = '';         

                                }

                                $columnscount       = count($row);

                                $pallorderitem      = [];

                                $fieldsoforderitem  = "";

                                $fieldoforderitem   = "";

                                for($loopvalue=0; $loopvalue<count($fieldsvaluearray); $loopvalue++) {

                                    if(isset($fieldsvaluearray[$loopvalue])) {

                                        $fieldvalue     = $fieldsvaluearray[$loopvalue];

                                        $fieldname      = $fieldsnamearray[$loopvalue];

                                        ${$fieldname}   = $row[$fieldvalue];

                                    }

                                }



                                $productId="";



                                $sku        = $row[7];

                                $modelcode  = substr($sku, 0, 5);

                                if($checks[$i] =="7-80" && $modelcode == "sku"){

                                    $productId = "";

                                }else{

                                    $productExist = DB::table('product')

                                            ->where('modelcode', '=', $modelcode)

                                            ->first();



                                    if(empty($productExist)) {

                                        array_push($noneProducts, $modelcode);

                                    }

                                    $productId  = $this->product_check_insert($modelcode, $sku, $channel->warehouse, $quantity);	

                                }								

                                $fewmorefieldsarray     = ['idpayment' => 'Amazon'];

                                $delivery_instruction   = ['delivery_Instructions' => $row[32]];

                                $fewmorefields          = 'idpayment';

                                $dateexplode            = explode('T', $date);

                                $date                   = $dateexplode[0];



                                $countrycode = DB::table('country')

                                                ->where('longname', '=', $country)

                                                ->get();



                                $productIdarray=['productid' => $productId];



                                if(count($countrycode) > 0) {	

                                    $country    = $countrycode;

                                    $country1   = $countrycode;

                                }

                            

                                $sum    = str_replace("EUR", "", $sum);

                                $sum    = str_replace(",", ".", $sum);

                                $sum    = str_replace(" €", "", $sum);

                                $sum    = str_replace("£", "", $sum);

                                $sum    = trim($sum);



                                if($currency =="") {

                                    $query = DB::table('country')

                                                ->where('shortname', '=', $country)

                                                ->get();

                                    if(count($query) > 0) {

                                        $currency = $query[0];

                                    }

                                }

                                

                                if($currency =="")

                                    $currency   ="EUR";



                                $moveme=0;



                                $query = DB::table('orderitem')

                                            ->where('referenceorder', '=', $referenceorder)

                                            ->get();

                                $orderId = "";

                                $multiorder = [];

                                if(count($query) > 0) {

                                    $orderId        = $query[0]->idorder;

                                    $multiorder     = ['multiorder' => $referenceorder, 'idpayment' => $query[0]->idpayment];

                                }



                                foreach($fieldsnamearray as $data) {

                                    if($data == "date"){

                                        $poforderitem   = ['datee' => ${$data}];

                                    } else {

                                        $poforderitem   = [$data => ${$data}];

                                    }



                                    if($data == "referenceorder"){

                                        $referenceorder   = ${$data};

                                    }



                                    $pallorderitem  = array_merge($pallorderitem, $poforderitem);

                                    $moveme++;

                                }



                                $pallorderitem      = array_merge($pallorderitem, $staticfieldsonearray, $productIdarray, $fewmorefieldsarray, $multiorder, $delivery_instruction);

                                $checkIsDuplicate   = $this->isDuplicate($referenceorder, $order_item_id, $productId);

                                $tempcustomer = $pallorderitem['customer'];

                                $pallorderitem['customer'] = $pallorderitem['inv_customer'];

                                $pallorderitem['inv_customer'] = $tempcustomer;

                                if($checkIsDuplicate == 0) {

                                    if($filetype=="txt") {

                                        //check amazon AM-PersonalcityCom

                                        if($filetype=="txt" && $checks[$i] == "7-80") {

                                            if($ship_promotion_id != "") {

                                                if (filter_var($orderNo, FILTER_VALIDATE_INT) === false) {

                                                    

                                                } else {

                                                    $insert_id = DB::table('orderitem')

                                                                    ->insertGetId($pallorderitem);

                                                    DB::table('orderitem')

                                                        ->where('idorder', '=', $insert_id)

                                                        ->update([

                                                            'sum'    => $sumtotal

                                                        ]);

                                                }

                                            }

                                        } else {

                                            

                                            $insert_id = DB::table('orderitem')

                                                                ->insertGetId($pallorderitem);

                                            DB::table('orderitem')

                                                ->where('idorder', '=', $insert_id)

                                                ->update([

                                                    'sum'    => $sumtotal

                                                ]);

                                        }

                                        

                                    }else{

                                        $insert_id = DB::table('orderitem')

                                                        ->insertGetId($pallorderitem);

                                    }

                                }

                            }

                        }

                        $loopfile++;

                    }

                    fclose($open);

                } else if($coding == 'Amazon-02'){

                    $open = fopen(storage_path('documents')."/".$name, 'r');

                    $loopfile = 0;

                    while (!feof($open)) {

                        $getTextLine    = fgets($open);

                        $row            = explode("\t", $getTextLine);

                        if($loopfile > 0) {

                            if(isset($row[1])) {

                                if(isset($row[28])) {

                                    $ship_promotion_id  = $row[28];

                                } else {

                                    $ship_promotion_id  = "";

                                }

                                

                                $orderNo                = $row[1];

                                $item_price             = $row[11];

                                $shipping_price         = $row[13];

                                if($item_price != "" && $shipping_price != "") {

                                    $sumtotal           = round(($item_price + $shipping_price), 2);

                                } else if($item_price != "") {

                                    $sumtotal           = round($item_price, 2);

                                } else if($shipping_price != "") {

                                    $sumtotal           = round($shipping_price, 2);

                                } else {

                                    $sumtotal = '';         

                                }

                                $columnscount       = count($row);

                                $pallorderitem      = [];

                                $fieldsoforderitem  = "";

                                $fieldoforderitem   = "";

                                for($loopvalue=0; $loopvalue<count($fieldsvaluearray); $loopvalue++) {

                                    if(isset($fieldsvaluearray[$loopvalue])) {

                                        $fieldvalue     = $fieldsvaluearray[$loopvalue];

                                        $fieldname      = $fieldsnamearray[$loopvalue];

                                        ${$fieldname}   = $row[$fieldvalue];

                                    }

                                }



                                $productId="";



                                if($platformtype=="Amazon") { 

                                    $sku        = $row[7];

                                    $modelcode  = substr($sku, 0, 5);

                                    if($checks[$i] =="7-80" && $modelcode == "sku"){

                                        $productId = "";

                                    }else{

                                        $productExist = DB::table('product')

                                                ->where('modelcode', '=', $modelcode)

                                                ->first();



                                        if(empty($productExist)) {

                                            array_push($noneProducts, $modelcode);

                                        }

                                        $productId  = $this->product_check_insert($modelcode, $sku, $channel->warehouse, $quantity);	

                                    }								

                                    $fewmorefieldsarray     = ['idpayment' => 'Amazon'];

                                    $delivery_instruction   = ['delivery_Instructions' => $row[32]];

                                    $fewmorefields          = 'idpayment';

                                    $dateexplode            = explode('T', $date);

                                    $date                   = $dateexplode[0];

                                    // $sumtotal           = '';

                                    // $sumtotal           = ($row[11] + $row[13]);

                                } else if($platformtype=="Ebay") { 

                                    $notes      = $row[7];

                                    $sku        = $notes;

                                    $modelcode  = substr($sku, 0, 5);

                                    $productExist = DB::table('product')

                                            ->where('modelcode', '=', $modelcode)

                                            ->first();



                                    if(empty($productExist)) {

                                        array_push($noneProducts, $modelcode);

                                    }

                                    $productId  = $this->product_check_insert($modelcode,$sku,$channel->warehouse,$quantity);

                                    $notes      = trim(substr($notes, 5));

                                } else if($platformtype=="Cdiscount") {

                                    $sumtotal           = '';

                                    $datee              = date("Y-m-d H:i:s",strtotime($row[3]));

                                    $productid          = substr($row[9], 0, 5);

                                    $item_price         = explode(" ", $row[13]);

                                    $item_price1        = explode(" ", $row[14]);

                                    $sumtotal           = ($item_price[0] + $item_price1[0]);

                                    $currency           = $item_price[1]; 

                                    $idpayment          = 'Cdiscount';

                                    $customer           = $row[17].' '.$row[18].' '.$row[19];

                                    $address1           = $row[20];

                                    $city               = $row[21];

                                    $region             = $row[22];

                                    $referenceorder     = $row[1];

                                    $country            = $row[23];

                                    $fax                = $row[24];

                                    $telefon            = $row[25];

                                    $plz                = $row[27];

                                    $sku                = $row[9];

                                    $productExist = DB::table('product')

                                            ->where('modelcode', '=', $sku)

                                            ->first();



                                    if(empty($productExist)) {

                                        array_push($noneProducts, $sku);

                                    }

                                    $productId          = $this->product_check_insert_two('ean', $sku, $channel->warehouse, $quantity,$sku);

                                    $fewmorefieldsarray = ['idpayment' => 'Cdiscount'];

                                    $fewmorefields      = "idpayment";

                                    $notes              = $row[8];

                                }



                                $countrycode = DB::table('country')

                                                ->where('longname', '=', $country)

                                                ->get();



                                $productIdarray=['productid' => $productId];



                                if(count($countrycode) > 0) {	

                                    $country    = $countrycode;

                                    $country1   = $countrycode;

                                }

                            

                                $sum    = str_replace("EUR", "", $sum);

                                $sum    = str_replace(",", ".", $sum);

                                $sum    = str_replace(" €", "", $sum);

                                $sum    = str_replace("£", "", $sum);

                                $sum    = trim($sum);



                                if($currency =="") {

                                    $query = DB::table('country')

                                                ->where('shortname', '=', $country)

                                                ->get();

                                    if(count($query) > 0) {

                                        $currency = $query[0];

                                    }

                                }

                                

                                if($currency =="")

                                    $currency   ="EUR";



                                $moveme=0;



                                $query = DB::table('orderitem')

                                            ->where('referenceorder', '=', $referenceorder)

                                            ->get();

                                $orderId = "";

                                $multiorder = [];

                                if(count($query) > 0) {

                                    $orderId        = $query[0]->idorder;

                                    $multiorder     = ['multiorder' => $referenceorder, 'idpayment' => $query[0]->idpayment];

                                }



                                foreach($fieldsnamearray as $data) {

                                    if($data == "date"){

                                        $poforderitem   = ['datee' => ${$data}];

                                    } else {

                                        $poforderitem   = [$data => ${$data}];

                                    }



                                    if($data == "referenceorder"){

                                        $referenceorder   = ${$data};

                                    }



                                    $pallorderitem  = array_merge($pallorderitem, $poforderitem);

                                    $moveme++;

                                }



                                $pallorderitem      = array_merge($pallorderitem, $staticfieldsonearray, $productIdarray, $fewmorefieldsarray, $multiorder, $delivery_instruction);

                                $checkIsDuplicate   = $this->isDuplicate($referenceorder, $order_item_id, $productId);

                                

                                if($checkIsDuplicate == 0) {

                                    if($filetype=="txt") {

                                        //check amazon AM-PersonalcityCom

                                        if($filetype=="txt" && $checks[$i] == "7-80") {

                                            if($ship_promotion_id != "") {

                                                if (filter_var($orderNo, FILTER_VALIDATE_INT) === false) {

                                                    

                                                } else {

                                                    $insert_id = DB::table('orderitem')

                                                                    ->insertGetId($pallorderitem);

                                                    DB::table('orderitem')

                                                        ->where('idorder', '=', $insert_id)

                                                        ->update([

                                                            'sum'    => $sumtotal

                                                        ]);

                                                }

                                            }

                                        } else {

                                            $insert_id = DB::table('orderitem')

                                                                ->insertGetId($pallorderitem);

                                            DB::table('orderitem')

                                                ->where('idorder', '=', $insert_id)

                                                ->update([

                                                    'sum'    => $sumtotal

                                                ]);

                                        }

                                        

                                    }else{

                                        $insert_id = DB::table('orderitem')

                                                        ->insertGetId($pallorderitem);

                                    }

                                }

                            }

                        }

                        $loopfile++;

                    }

                    fclose($open);

                } else if($filetype=="txt" && $checks[$i] == "10-81") {

					for($loopfile=0; $loopfile<$totalrows; $loopfile++) {

						if($loopfile > 0) {

							if($filetype=="txt") {

								$row=$file[$loopfile];

								$row=explode("\t",$row);

                            }

                            

							$columnscount       = count($row);

							$pallorderitem      = [];

							$fieldsoforderitem  = "";

							$fieldoforderitem   = "";

							for($loopvalue=0; $loopvalue<$columnscount; $loopvalue++) {

								if(isset($fieldsvaluearray[$loopvalue])) {

									$fieldvalue=$fieldsvaluearray[$loopvalue];

									$fieldname=$fieldsnamearray[$loopvalue];

									${$fieldname}=$row[$fieldvalue];

								}

						  	} // creating fields and their values variables and assigning data

                            if(isset($row[7])) {

                                $h = $row[7];

                            }

						  	// amazon format

						  	if(isset($h) && $h!="" && isset($row[11])) {

						  		$productId="";

							  	if($platformtype =="Amazon") { 

									$sku                = $notes;

                                    $modelcode          = substr($sku, 0, 5);

                                    $productExist = DB::table('product')

                                            ->where('modelcode', '=', $modelcode)

                                            ->first();



                                    if(empty($productExist)) {

                                        array_push($noneProducts, $modelcode);

                                    }

									$productId          = $this->product_check_insert($modelcode, $sku, $channel->warehouse, $quantity);

									$fewmorefieldsarray = ['idpayment' => 'Amazon'];

									$dateexplode        = explode("T", $date);

									$date               = $dateexplode[0];

									$sumtotal           = '';

									$sumtotal           = ($row[11] + $row[13]);

									$customer           = $row[16];

									$invoicenr          = $row[5];

								}



								$countrycode = DB::table('country')

                                        ->where('longname', '=', $country)

                                        ->get();



                                $productIdarray=['productid' => $productId];



                                if(count($countrycode) > 0) {	

                                    $country    = $countrycode;

                                    $country1   = $countrycode;

                                }

                            

                                $sum    = str_replace("EUR", "", $sum);

                                $sum    = str_replace(",", ".", $sum);

                                $sum    = str_replace(" €", "", $sum);

                                $sum    = str_replace("£", "", $sum);

                                $sum    = trim($sum);



                                if($currency =="") {

                                    $query = DB::table('country')

                                                ->where('shortname', '=', $country)

                                                ->get();

                                    if(count($query) > 0) {

                                        $currency = $query[0];

                                    }

                                }

                                if($currency =="")

                                    $currency   ="EUR";

                                $moveme=0;

                                

                                foreach($fieldsnamearray as $data) {

                                    if($data == "date"){

                                        $poforderitem   = ['datee' => ${$data}];

                                    } else {

                                        $poforderitem   = [$data => ${$data}];

                                    }

                                    $pallorderitem  = array_merge($pallorderitem, $poforderitem);

                                    $moveme++;

                                }



                                $query = DB::table('orderitem')

                                    ->where('referenceorder', '=', $referenceorder)

                                    ->get();

                                $orderId    = "";

                                $multiorder = [];

                                if(count($query) > 0) {

                                    $orderId = $query[0]->idorder;

                                    $multiorder = ['multiorder' => $referenceorder];

                                }



                                $pallorderitem      = array_merge($pallorderitem, $staticfieldsonearray, $productIdarray, $fewmorefieldsarray, $multiorder);



                                $checkIsDuplicate = $this->isDuplicate($referenceorder, $order_item_id, $productId);

                                if($referenceorder!="" && $checkIsDuplicate==0) {

                                    $insert_id = DB::table('orderitem')

                                                    ->insertGetId($pallorderitem);

                                    DB::table('orderitem')

                                        ->where('idorder', '=', $insert_id)

                                        ->update([

                                            'sum'       => $sumtotal,

                                            'customer'  => $customer,

                                            'invoicenr' => $invoicenr

                                        ]);

                                }

                            }

                        }

						  	

					} // end of reading file

				} else if($coding == 'EbayUK-01') {

					for($loopfile=3; $loopfile<$totalrows; $loopfile++) {

						if($loopfile > 0) {

							if($filetype=="txt") {

								$row=$file[$loopfile];

								$row=explode("\t",$row);

                            } else if($filetype=="xlsx"){

                                $row=$file[$loopfile];

                            } else if($filetype=="csv") {

                                $row=str_getcsv($file[$loopfile], ";");

                                if(count($row) == 1) {

                                    $row=str_getcsv($file[$loopfile], ",");

                                }

                            }

                            

							$columnscount       = count($row);

							$pallorderitem      = [];

							$fieldsoforderitem  = "";

							$fieldoforderitem   = "";

							for($loopvalue=0; $loopvalue<count($fieldsvaluearray); $loopvalue++) {

                                if(isset($fieldsvaluearray[$loopvalue]) && $fieldsvaluearray[$loopvalue] != "") {

                                    $fieldvalue     = $fieldsvaluearray[$loopvalue];

                                    if(isset($fieldsvaluearray[$loopvalue])) {

                                        $fieldname      = $fieldsnamearray[$loopvalue];

                                        if(array_key_exists($loopvalue, $row) && $row[$loopvalue] != "") {

                                            ${$fieldname}   = $row[$loopvalue];

                                        }

                                    }

                                }

                            } // creating fields and their values variables and assigning data

                            if(isset($row[7])) {

                                $h = $row[7];

                            }

						  	// amazon format

						  	if(isset($row[11])) {

						  		$productId="";

                                $quantity   = $row[24];

                                $idpayment  = $row[40];

                                

                                $getdatemonth   = explode("-", $row[41]);

                                if(isset($row[41]) && $row[41] != "") {

                                    $dateday        = $getdatemonth[0];

                                    $month          = $getdatemonth[1];

                                    $dateyear       = $getdatemonth[2];

                                }



                                if(strtolower($month) == 'gen'){

                                    $datemonth = "01";

                                }else if(strtolower($month) == 'feb'){

                                    $datemonth = "02";

                                }else if(strtolower($month) == 'mar' || strtolower($month) == 'mrz') {

                                    $datemonth = "03";

                                }else if(strtolower($month) == 'apr'){

                                    $datemonth = "04";

                                }else if(strtolower($month) == 'mag'){

                                    $datemonth = "05";

                                }else if(strtolower($month) == 'giu'){

                                    $datemonth = "06";

                                }else if(strtolower($month) == 'lug'){

                                    $datemonth = "07";

                                }else if(strtolower($month) == 'aug'){

                                    $datemonth = "08";

                                }else if(strtolower($month) == 'set' || strtolower($month) == 'sep'){

                                    $datemonth = "09";

                                }else if(strtolower($month) == 'ott' || strtolower($month) == 'okt' || strtolower($month) == 'oct'){

                                    $datemonth = "10";

                                }else if(strtolower($month) == 'nov'){

                                    $datemonth = "11";

                                }else if(strtolower($month) == 'dic' || strtolower($month) == 'dez'){

                                    $datemonth = "12";

                                }



                                $year   = date("Y");

                                $year1  = substr($year, 0, 2);

                                $datee  = $year1.$dateyear.'-'.$datemonth.'-'.$dateday;



                                $date = $this->checkmydate($datee);

                                if($date==1){

                                    $newdatee = $datee;

                                }else{

                                    $newdatee = '';

                                }

                                

                                $productid  = substr($row[22], 0, 5);

                                $getsum     = explode(" ", $row[38]);

                                $sum        = str_replace(",",".",$getsum[0]);

                                

                                if($currency =="" && isset($getsum[1])) {

                                    $query  = DB::table('country')

                                                ->where('shortname', '=', $getsum[1])

                                                ->get();

                                    if(count($query) > 0) {

                                        $currency = $query[0]->currency;

                                    }

                                }

        

                                if($currency =="")

                                    $currency   ="EUR";



                                $referenceorder = $row[1];

                                $sale           = $row[2];

                                $invoicenr      = $row[2];

                                $inv_customer   = $row[3];

                                $inv_address1   = $row[6];

                                $inv_address2   = $row[7];

                                $city1          = $row[8];

                                $region1        = $row[9];

                                $customer       = $row[12];

                                $telefon        = $row[13];

                                $telefon1       = $row[13];

                                $address1       = $row[14];

                                $address2       = $row[15];

                                $city           = $row[16];

                                $region         = $row[17];

                                $plz            = $row[18];

                                $plz1           = $row[10];

                                $country        = $row[19];

                                $country1       = $row[11];

                                $notes          = $row[22];

                                $order_item_id  = $row[0];

                                $multiorder     = 0;



                                $countrycode = DB::table('country')

                                        ->where('longname', '=', $country)

                                        ->get();



                                if(count($countrycode) > 0) {	

                                    $country    = $countrycode[0]->shortname;

                                    $country1   = $countrycode[0]->shortname;

                                }



                                $query = DB::table('orderitem')

                                    ->where('referenceorder', '=', $referenceorder)

                                    ->get();

                                $orderId = "";



                                if(count($query) > 0) {

                                    $orderId        = $query[0]->idorder;

                                    $multiorder     = $referenceorder;

                                    $idpayment      = $query[0]->idpayment;

                                }



                                $sku                = $notes;

                                $modelcode          = substr($sku, 0, 5);



                                $productExist = DB::table('product')

                                        ->where('modelcode', '=', $modelcode)

                                        ->first();



                                if(empty($productExist)) {

                                    array_push($noneProducts, $modelcode);

                                }



                                $productId          = $this->product_check_insert($modelcode, $sku, $channel->warehouse, $quantity);

                                $checkIsDuplicate   = $this->isDuplicate($referenceorder, $order_item_id, $productId);

                                if(strlen($country) > 3) {

                                    $countryCode = DB::table('country_conversion')

                                                    ->where('longname', '=', $country)

                                                    ->first();

                                    if(!empty($countryCode)) {

                                        $country = $countryCode->shortname;

                                    }

                                }



                                if(strlen($country1) > 3) {

                                    $countryCode = DB::table('country_conversion')

                                                    ->where('longname', '=', $country1)

                                                    ->first();

                                    if(!empty($countryCode)) {

                                        $country1 = $countryCode->shortname;

                                    }

                                }

                                if($referenceorder!="" && $sale!="" && $checkIsDuplicate ==0) {

                                    if($referenceorder!="") {

                                        DB::table('orderitem')

                                            ->insert([

                                                'idorderplatform'       => $platformId,

                                                'referencechannelname'  => $channelname,

                                                'platformname'          => $platformname,

                                                'referencechannel'      => $referencechannel,

                                                'weeksell'              => $week,

                                                'idchannel'             => $referencechannel,

                                                'idwarehouse'           => $idwarehouse,

                                                'inv_vat'               => $inv_vat,

                                                'quantity'              => $quantity,

                                                'idpayment'             => $idpayment,

                                                'productid'             => $productId,

                                                'referenceorder'        => $referenceorder,

                                                'invoicenr'             => $invoicenr,

                                                'customer'              => $customer,

                                                'telefon'               => $telefon,

                                                'address1'              => $address1,

                                                'address2'              => $address2,

                                                'city'                  => $city,

                                                'region'                => $region,

                                                'plz'                   => $plz,

                                                'country'               => $country,

                                                'country1'              => $country1,

                                                'sum'                   => $sum,

                                                'currency'              => $currency,

                                                'datee'                 => $newdatee,

                                                'multiorder'            => $multiorder,

                                                'idcompany'             => $idcompany,

                                                'inv_customer'          => $inv_customer,

                                                'inv_address1'          => $inv_address1,

                                                'inv_address2'          => $inv_address2,

                                                'city1'                 => $city1,

                                                'region1'               => $region1,

                                                'notes'                 => $notes,

                                                'plz1'                  => $plz1,

                                                'order_item_id'         => $order_item_id,

                                                'telefon1'              => $telefon1

                                            ]);

                                    }

                                }

                            }

                        }

					} // end of reading file

				} else if($coding == 'EbayES-01') {

					for($loopfile=3; $loopfile<$totalrows; $loopfile++) {

						if($loopfile > 0) {

							$row=str_getcsv($file[$loopfile], ";");

                            

						  	// amazon format

						  	if(isset($row[24]) && $row[24] != "") {

                                $quantity       = $row[24];

                                $idpayment      = $row[40];

                                $getdatemonth   = explode("-", $row[41]);

                                if(isset($row[41]) && $row[41] != "") {

                                    $dateday        = $getdatemonth[0];

                                    $month          = $getdatemonth[1];

                                    $dateyear       = $getdatemonth[2];

                                }

                                

                                if($month == 'gen'){

                                    $datemonth = "01";

                                }else if($month == 'feb'){

                                    $datemonth = "02";

                                }else if(strtolower($month) == 'mar' || strtolower($month) == 'mrz') {

                                    $datemonth = "03";

                                }else if($month == 'apr'){

                                    $datemonth = "04";

                                }else if($month == 'mag'){

                                    $datemonth = "05";

                                }else if($month == 'giu'){

                                    $datemonth = "06";

                                }else if($month == 'lug'){

                                    $datemonth = "07";

                                }else if($month == 'aug'){

                                    $datemonth = "08";

                                }else if(strtolower($month) == 'set' || strtolower($month) == 'sep'){

                                    $datemonth = "09";

                                }else if(strtolower($month) == 'ott' || strtolower($month) == 'okt' || strtolower($month) == 'oct'){

                                    $datemonth = "10";

                                }else if($month == 'nov'){

                                    $datemonth = "11";

                                }else if(strtolower($month) == 'dic' || strtolower($month) == 'dez'){

                                    $datemonth = "12";

                                }

        

                                $year   = date("Y");

                                $year1  = substr($year, 0, 2);

                                $datee  = $year1.$dateyear.'-'.$datemonth.'-'.$dateday;

        

                                $date = $this->checkmydate($datee);

                                if($date==1){

                                    $newdatee = $datee;

                                }else{

                                    $newdatee = '';

                                }

        

                                $productid  = substr($row[22], 0, 5);

                                $getsum     = explode(" ", $row[38]);

                                $sum        = str_replace(",",".",$getsum[0]);

                                    

                                if($currency =="" && isset($getsum[1])) {

                                    $query = DB::table('country')

                                                ->where('shortname', '=', $getsum[1])

                                                ->get();

                                    if(count($query) > 0) {

                                        $currency = $query[0]->currency;

                                    }

                                }

        

                                if($currency =="")

                                    $currency   ="EUR";

        

                                $referenceorder = $row[1];

                                $sale           = $row[2];

                                $invoicenr      = $row[2];

                                $inv_customer   = $row[3];

                                $inv_address1   = $row[6];

                                $inv_address2   = $row[7];

                                $city1          = $row[8];

                                $region1        = $row[9];

                                $customer       = $row[12];

                                $telefon        = $row[13];

                                $telefon1       = $row[13];

                                $address1       = $row[14];

                                $address2       = $row[15];

                                $city           = $row[16];

                                $region         = $row[17];

                                $plz            = $row[18];

                                $plz1           = $row[10];

                                $country        = $row[19];

                                $country1       = $row[11];

                                $notes          = $row[22];

                                $order_item_id  = $row[0];

                                $multiorder     = 0;

                                $email          = $row[4];

                                $countrycode = DB::table('country')

                                                ->where('longname', '=', $country)

                                                ->get();

        

                                if(count($countrycode) > 0) {	

                                    $country    = $countrycode[0]->shortname;

                                    $country1   = $countrycode[0]->shortname;

                                }

        

                                $query = DB::table('orderitem')

                                    ->where('referenceorder', '=', $referenceorder)

                                    ->get();

                                $orderId = "";

        

                                if(count($query) > 0) {

                                    $orderId        = $query[0]->idorder;

                                    $multiorder     = $referenceorder;

                                    $idpayment      = $query[0]->idpayment;

                                }

        

                                $sku                = $notes;

                                $modelcode          = substr($sku, 0, 5);

                                

                                $productExist = DB::table('product')

                                        ->where('modelcode', '=', $modelcode)

                                        ->first();

        

                                if(empty($productExist)) {

                                    array_push($noneProducts, $modelcode);

                                }



                                $productId          = $this->product_check_insert($modelcode, $sku, $channel->warehouse, $quantity);

                                $checkIsDuplicate   = $this->isDuplicate($referenceorder, $order_item_id, $productId);

                                if(strlen($country) > 3) {

                                    $countryCode = DB::table('country_conversion')

                                                    ->where('longname', '=', $country)

                                                    ->first();

                                    if(!empty($countryCode)) {

                                        $country = $countryCode->shortname;

                                    }

                                }

        

                                if(strlen($country1) > 3) {

                                    $countryCode = DB::table('country_conversion')

                                                    ->where('longname', '=', $country1)

                                                    ->first();

                                    if(!empty($countryCode)) {

                                        $country1 = $countryCode->shortname;

                                    }

                                }



                                if($referenceorder!="" && $checkIsDuplicate==0) {

                                    DB::table('orderitem')

                                        ->insert([

                                            'idorderplatform'       => $platformId,

                                            'referencechannelname'  => $channelname,

                                            'platformname'          => $platformname,

                                            'referencechannel'      => $referencechannel,

                                            'weeksell'              => $week,

                                            'idchannel'             => $referencechannel,

                                            'idwarehouse'           => $idwarehouse,

                                            'inv_vat'               => $inv_vat,

                                            'quantity'              => $quantity,

                                            'idpayment'             => $idpayment,

                                            'productid'             => $productId,

                                            'referenceorder'        => $referenceorder,

                                            'invoicenr'             => $invoicenr,

                                            'customer'              => $customer,

                                            'telefon'               => $telefon,

                                            'address1'              => $address1,

                                            'address2'              => $address2,

                                            'city'                  => $city,

                                            'region'                => $region,

                                            'plz'                   => $plz,

                                            'country'               => $country,

                                            'country1'              => $country1,

                                            'sum'                   => $sum,

                                            'currency'              => $currency,

                                            'datee'                 => $newdatee,

                                            'multiorder'            => $multiorder,

                                            'idcompany'             => $idcompany,

                                            'inv_customer'          => $inv_customer,

                                            'inv_address1'          => $inv_address1,

                                            'inv_address2'          => $inv_address2,

                                            'city1'                 => $city1,

                                            'region1'               => $region1,

                                            'notes'                 => $notes,

                                            'plz1'                  => $plz1,

                                            'email'                 => $email,

                                            'order_item_id'         => $order_item_id,

                                            'telefon1'              => $telefon1

                                        ]);

                                }

                            }

                        }

					} // end of reading file

				} else if($coding == 'EbayFR-01') {

					for($loopfile=0; $loopfile<$totalrows; $loopfile++) {

						if($loopfile > 0) {

							if($filetype=="txt") {

								$row=$file[$loopfile];

								$row=explode("\t",$row);

                            } else if($filetype=="xlsx"){

                                $row=$file[$loopfile];

                            } else if($filetype=="csv") {

                                $row = explode(';', $file[$loopfile]);

                            }

                            

							$columnscount       = count($row);

							$pallorderitem      = [];

							$fieldsoforderitem  = "";

							$fieldoforderitem   = "";

							for($loopvalue=0; $loopvalue<count($fieldsvaluearray); $loopvalue++) {

                                if(isset($fieldsvaluearray[$loopvalue]) && $fieldsvaluearray[$loopvalue] != "") {

                                    $fieldvalue     = $fieldsvaluearray[$loopvalue];

                                    if(isset($fieldsvaluearray[$loopvalue])) {

                                        $fieldname      = $fieldsnamearray[$loopvalue];

                                        if(array_key_exists($loopvalue, $row) && $row[$loopvalue] != "") {

                                            ${$fieldname}   = $row[$loopvalue];

                                        }

                                    }

                                }

                            } // creating fields and their values variables and assigning data

                            if(isset($row[7])) {

                                $h = $row[7];

                            }

						  	// amazon format

						  	if(isset($h) && $h!="" && isset($row[11])) {

						  		$productId="";

							  	if($platformtype =="Amazon") { 

									$sku                = $notes;

                                    $modelcode          = substr($sku, 0, 5);

                                    $productExist = DB::table('product')

                                            ->where('modelcode', '=', $modelcode)

                                            ->first();



                                    if(empty($productExist)) {

                                        array_push($noneProducts, $modelcode);

                                    }

									$productId          = $this->product_check_insert($modelcode, $sku, $channel->warehouse, $quantity);

									$fewmorefieldsarray = ['idpayment' => 'Amazon'];

									$dateexplode        = explode("T", $date);

									$date               = $dateexplode[0];

									$sumtotal           = '';

									$sumtotal           = ($row[11] + $row[13]);

									$customer           = $row[16];

									$invoicenr          = $row[5];

								} else if($platformtype=="Ebay"){ 

                                    // $quantity = $row[24];

                                    // $idpayment = $row[35];

                                    // $datee = $row[36];

                                    // $productid = $row[22];

                                    // $sum = $row[33];

                                    // $currency = "";

                                    // $referenceorder = $row[1];

                                    // //$customer = $row[3];

                                    // $email = $row[4];

                                    // $telefon = $row[13];

                                    // $address1 = $row[14];

                                    // $address2 = $row[15];

                                    // $city = $row[16];

                                    // $region = $row[17];

                                    // $plz = $row[18];

                                    // $country = $row[19];

                                    $notes      = $row[33];

                                    $sku        = $notes;

                                    $modelcode  = substr($sku, 0, 5);

                                    $productExist = DB::table('product')

                                            ->where('modelcode', '=', $modelcode)

                                            ->first();



                                    if(empty($productExist)) {

                                        array_push($noneProducts, $modelcode);

                                    }

                                    $productId  = $this->product_check_insert($modelcode,$sku,$channel->warehouse,$quantity);

                                    $notes      = trim(substr($notes,5));

                                }



								$countrycode = DB::table('country')

                                        ->where('longname', '=', $country)

                                        ->get();



                                $productIdarray=['productid' => $productId];



                                if(count($countrycode) > 0) {	

                                    $country    = $countrycode;

                                    $country1   = $countrycode;

                                }

                            

                                $sum    = str_replace("EUR", "", $sum);

                                $sum    = str_replace(",", ".", $sum);

                                $sum    = str_replace(" €", "", $sum);

                                $sum    = str_replace("£", "", $sum);

                                $sum    = trim($sum);



                                if($currency =="") {

                                    $query = DB::table('country')

                                                ->where('shortname', '=', $country)

                                                ->get();

                                    if(count($query) > 0) {

                                        $currency = $query[0];

                                    }

                                }

                                if($currency =="")

                                    $currency   ="EUR";

                                $moveme=0;

                                

                                foreach($fieldsnamearray as $data) {

                                    if($data == "date"){

                                        $poforderitem   = ['datee' => ${$data}];

                                    } else {

                                        if(isset(${$data})) {

                                            $poforderitem   = [$data => ${$data}];

                                        } else {

                                            $poforderitem   = [];

                                        }

                                    }

                                    $pallorderitem  = array_merge($pallorderitem, $poforderitem);

                                    $moveme++;

                                }



                                

                                $query = DB::table('orderitem')

                                    ->where('referenceorder', '=', $referenceorder)

                                    ->get();

                                $orderId = "";

                                $multiorder = [];

                                if(count($query) > 0) {

                                    $orderId = $query[0]->idorder;

                                    $multiorder = ['multiorder' => $referenceorder, 'idpayment' => $query[0]->idpayment];

                                }



                                $pallorderitem      = array_merge($pallorderitem, $staticfieldsonearray, $productIdarray, $fewmorefieldsarray, $multiorder);

                                if(strlen($pallorderitem['country']) > 3) {

                                    $countryCode = DB::table('country_conversion')

                                                    ->where('longname', '=', $country)

                                                    ->first();

                                    if(!empty($countryCode)) {

                                        $pallorderitem['country'] = $countryCode->shortname;

                                    }

                                }

                                $checkIsDuplicate = $this->isDuplicate($referenceorder, $order_item_id, $productId);

                                if($referenceorder!="" && $checkIsDuplicate==0) {

                                    $insert_id = DB::table('orderitem')

                                                    ->insertGetId($pallorderitem);

                                    // DB::table('orderitem')

                                    //     ->where('idorder', '=', $insert_id)

                                    //     ->update([

                                    //         'sum'       => $sumtotal,

                                    //         'customer'  => $customer,

                                    //         'invoicenr' => $invoicenr

                                    //     ]);

                                }

                            }

                        }

						  	

					} // end of reading file

				} else {

					for($loopfile=3; $loopfile<$totalrows; $loopfile++) {

						if($loopfile > 0) {

							if($filetype=="txt") {

								$row=$file[$loopfile];

								$row=explode("\t",$row);

                            } else if($filetype=="xlsx"){

                                $row=$file[$loopfile];

                            } else if($filetype=="csv") {

                                $row=str_getcsv($file[$loopfile],$fileseparator);

                            }



							$columnscount       = count($row);

							$pallorderitem      = [];

							$fieldsoforderitem  = "";

							$fieldoforderitem   = "";

							for($loopvalue=0; $loopvalue<count($fieldsvaluearray); $loopvalue++) {

                                if(isset($fieldsvaluearray[$loopvalue]) && $fieldsvaluearray[$loopvalue] != "") {

                                    $fieldvalue     = $fieldsvaluearray[$loopvalue];

                                    if(isset($fieldsvaluearray[$loopvalue])) {

                                        $fieldname      = $fieldsnamearray[$loopvalue];

                                        if(array_key_exists($loopvalue, $row) && $row[$loopvalue] != "") {

                                            ${$fieldname}   = $row[$loopvalue];

                                        }

                                    }

                                }

                            } // creating fields and their values variables and assigning data

                            if(isset($row[7])) {

                                $h = $row[7];

                            }

						  	// amazon format

						  	if(isset($h) && $h!="" && isset($row[11])) {

						  		$productId="";

							  	if($platformtype =="Amazon") { 

									$sku                = $notes;

                                    $modelcode          = substr($sku, 0, 5);

                                    $productExist = DB::table('product')

                                            ->where('modelcode', '=', $modelcode)

                                            ->first();



                                    if(empty($productExist)) {

                                        array_push($noneProducts, $modelcode);

                                    }

									$productId          = $this->product_check_insert($modelcode, $sku, $channel->warehouse, $quantity);

									$productId          = substr($productId, 0, 5);

									$fewmorefieldsarray = ['idpayment' => 'Amazon'];

									$dateexplode        = explode("T", $date);

									$date               = $dateexplode[0];

									$sumtotal           = '';

									$sumtotal           = round(($row[11] + $row[13]), 2);

									$customer           = $row[16];

									$invoicenr          = $row[5];

								} else if($platformtype=="Ebay"){ 

                                    // $quantity = $row[24];

                                    // $idpayment = $row[35];

                                    // $datee = $row[36];

                                    // $productid = $row[22];

                                    // $sum = $row[33];

                                    // $currency = "";

                                    // $referenceorder = $row[1];

                                    // //$customer = $row[3];

                                    // $email = $row[4];

                                    // $telefon = $row[13];

                                    // $address1 = $row[14];

                                    // $address2 = $row[15];

                                    // $city = $row[16];

                                    // $region = $row[17];

                                    // $plz = $row[18];

                                    // $country = $row[19];

    

                                    $sku        = $notes;

                                    $modelcode  = substr($sku, 0, 5);

                                    $productExist = DB::table('product')

                                            ->where('modelcode', '=', $modelcode)

                                            ->first();



                                    if(empty($productExist)) {

                                        array_push($noneProducts, $modelcode);

                                    }

                                    $productId  = $this->product_check_insert($modelcode,$sku,$channel->warehouse,$quantity);

                                    $notes      = trim(substr($notes,5));

                                }



								$countrycode = DB::table('country')

                                        ->where('longname', '=', $country)

                                        ->get();



                                $productIdarray=['productId' => $productId];



                                if(count($countrycode) > 0) {	

                                    $country    = $countrycode[0]->shortname;

                                    $country1   = $countrycode[0]->shortname;

                                }

                            

                                $sum    = str_replace("EUR", "", $sum);

                                $sum    = str_replace(",", ".", $sum);

                                $sum    = str_replace(" €", "", $sum);

                                $sum    = str_replace("£", "", $sum);

                                $sum    = trim($sum);



                                if($currency =="") {

                                    $query = DB::table('country')

                                                ->where('shortname', '=', $country)

                                                ->get();

                                    if(count($query) > 0) {

                                        $currency = $query[0]->currency;

                                    }

                                }

                                if($currency =="")

                                    $currency   ="EUR";

                                $moveme=0;

                                

                                foreach($fieldsnamearray as $data) {

                                    if($data == "date"){

                                        $poforderitem   = ['datee' => ${$data}];

                                    } else {

                                        if(isset(${$data})) {

                                            $poforderitem   = [$data => ${$data}];

                                        } else {

                                            $poforderitem   = [];

                                        }

                                    }

                                    $pallorderitem  = array_merge($pallorderitem, $poforderitem);

                                    $moveme++;

                                }



                                $query = DB::table('orderitem')

                                    ->where('referenceorder', '=', $referenceorder)

                                    ->get();

                                $orderId = "";



                                $multiorder = [];

                                if(count($query) > 0) {

                                    $orderId = $query[0]->idorder;

                                    $multiorder = ['multiorder' => $referenceorder, 'idpayment' => $query[0]->idpayment];

                                }



                                $pallorderitem      = array_merge($pallorderitem, $staticfieldsonearray, $productIdarray, $fewmorefieldsarray, $multiorder);

                                if(strlen($pallorderitem['country']) > 3) {

                                    $countryCode = DB::table('country_conversion')

                                                    ->where('longname', '=', $country)

                                                    ->first();

                                    

                                    if(!empty($countryCode)) {

                                        $pallorderitem['country'] = $countryCode->shortname;

                                    }

                                }

                                $checkIsDuplicate = $this->isDuplicate($referenceorder, $order_item_id, $productId);

                                if($referenceorder!="" && $checkIsDuplicate==0) {

                                    $insert_id = DB::table('orderitem')

                                                    ->insertGetId($pallorderitem);

                                    DB::table('orderitem')

                                        ->where('idorder', '=', $insert_id)

                                        ->update([

                                            'sum'       => $sumtotal,

                                            'customer'  => $customer,

                                            'invoicenr' => $invoicenr

                                        ]);

                                }

                            }

                        }

						  	

					} // end of reading file

				}

            }

        }



        if(count($noneProducts) > 0) {

            Session::put('noneProducts', $noneProducts);

        }

        

        return redirect()->route('orderView');

    }



    public function createCSV() {

        $this->dhl_csv();

        $this->sda_csv();

        $this->dpd_csv();	



        $zip        = new ZipArchive;

        $destinationPath = public_path('documents');
        if (! File::exists( $destinationPath ) ) {
            File::makeDirectory( $destinationPath );
        }

        $fileName   = 'documents/'.time()."_csv.zip";

        if ($zip->open($fileName, ZipArchive::CREATE) === TRUE) {

            $files = File::files(public_path('courier_csv'));

            foreach ($files as $key => $value) {

                $file = basename($value);

                $zip->addFile($value, $file);

            }

            

            $zip->close();

        }

        $document = new Document();
        $document->file = $fileName;
        $document->type = 'courier';
        $document->save();
        return response()->download(public_path($fileName));

    }



    public function documents()

    {

        $cha = DB::table('channel') 

                ->where('sync', '=', 'Automatic Synch with: eBay')

                ->get();

        

        $count = 0;

        foreach ($cha as $key => $channel) {

            $rows2 = DB::table('orderitem') 

                ->where('registeredtosolddayok', '=', 0)

                ->where('tracking',             '!=', '')

                ->where('courierinformedok',    '=', 1)

                ->where('referencechannel',     '=', $channel->idchannel)

                ->get();



            foreach ($rows2 as $key => $rows) {

                $idorder1 = $rows->idorder;

                $row = array(

                    $rows->referenceorder,

                    '','',

                    date('m-d-Y'),'',  

                    $rows->carriername,

                    $rows->tracking,   

                );



                if($channel->sync == "Automatic Synch with: eBay"){

                    //include 'ebay_sdk/api/tracking_info2.php';

                    $count++;

                    DB::table('orderitem')

                        ->where('idorder', '=', $idorder1)

                        ->update([

                            'registeredtosolddayok'    => 1

                        ]);

                }

            }

        }



        

        $channelx = DB::table('channel') 

                ->where('sync', '=', 'Automatic Synch with: Amazon')

                ->get();



        foreach ($channelx as $key => $channels) {

            $file = fopen ('create_csv/Order'.$channels->shortname.'Confirm.csv','w');

            fputcsv($file, array('order-id', 'order-item-id', 'quantity', 'ship-date','carrier-code','carrier-name','tracking-number','ship-method'));



            $rowss2 = DB::table('orderitem') 

                    ->where('courierinformedok',     '=', 1)

                    ->where('registeredtosolddayok', '=', 0)

                    ->where('tracking',             '!=', '')

                    ->where('referencechannel',      '=', $channels->idchannel)

                    ->get();

            // loop over the rows, outputting them

            foreach ($rowss2 as $key => $rowss) {

                $idorder1 = $rowss->idorder;

                $rowf = array(

                    $rowss->idorderplatform,

                    '','',

                    date('m-d-Y'),'',

                    $rowss->carriername, 

                    $rowss->tracking,

                );

                fputcsv($file, $rowf);

                DB::table('orderitem')

                        ->where('idorder', '=', $idorder1)

                        ->update([

                            'registeredtosolddayok'    => 1

                        ]);

                $count++;

            }

        }



        $zip        = new ZipArchive;

        $fileName   = time()."_platform_csv.zip";



        if ($zip->open($fileName, ZipArchive::CREATE) === TRUE) {

            $files = File::files(public_path('create_csv'));

            foreach ($files as $key => $value) {

                $file = basename($value);

                $zip->addFile($value, $file);

            }

            

            $zip->close();

        }



        return response()->download(public_path($fileName));

    }



    public function printDocuments() {

        $idwarehouse = base64_decode($_GET["idwarehouse"]);

        if($idwarehouse != ""){

            $orders = OrderItem::query()

                    ->leftjoin('companyinfo', 'companyinfo.idcompany', '=', 'orderitem.idcompany')

                    ->leftjoin('product', 'orderitem.productid', '=', 'product.productid')

                    ->leftjoin('channel', 'orderitem.idchannel', '=', 'channel.idchannel')

                    ->where('orderitem.carriername',          '!=', '')

                    ->where('orderitem.multiorder',           '=', '0')

                    ->where('orderitem.printedshippingok',    '=', 0)

                    ->where('orderitem.idwarehouse',          '=', $idwarehouse)

                    ->orderby('orderitem.referenceorder', 'desc')

                    ->select('orderitem.*', 'companyinfo.shortname as shortnamecompany', 'companyinfo.street1 as street1company', 'product.ean', 'companyinfo.plz as plzcomapny', 'product.sku', 'channel.vat as channelVat',

                            'companyinfo.city as citycomapny', 'companyinfo.country as countrycomapny')

                    ->get();

            $data   = "";

            $j      = 0;

       //  dd($orders);

            foreach($orders as $arr) {

                $idorder = $arr->idorder;

                DB::table('orderitem')

                    ->where('idorder',          '=', $idorder)

                    ->update([

                        'printedshippingok'    => 1

                    ]);

                

                $countryShortName   = $arr->country;

                $countryCode        = DB::table('country_conversion')

                                        ->where('shortname', '=', $arr->country)

                                        ->first();



                if(!empty($countryCode)) {

                    $arr->country    = $countryCode->longname;

                } else {

                    $arr->country    = $arr->country;

                }



                $multiOrders = DB::table('orderitem')

                    ->leftjoin('product', 'orderitem.productid', '=', 'product.productid')

                    ->where('orderitem.multiorder', '=', $arr->referenceorder)

                    ->get();

               

                if(count($multiOrders) > 0) {

                    $arr->multiOrders = $multiOrders;

                }



                foreach($multiOrders as $order) {

                    DB::table('orderitem')

                        ->where('idorder',          '=', $order->idorder)

                        ->update([

                           'printedshippingok'    => 1

                        ]);



                    if($order->ean != $order->sku) {

                        $order->barcode = '<img src="data:image/png;base64,' . DNS1D::getBarcodePNG($order->ean, 'EAN13') . '" alt="barcode"   /><br>';

                    } else {

                        $order->barcode = "";

                    }

                }

                if($arr->ean != $arr->sku) {

                    $arr->barcode = '<img src="data:image/png;base64,' . DNS1D::getBarcodePNG($arr->ean, 'EAN13') . '" alt="barcode"   /><br>';

                } else {

                    $arr->barcode = "";

                }

                // echo DNS1D::getBarcodeSVG('4260171666221', 'PHARMA2T');

                // echo DNS1D::getBarcodeHTML('4260171666221', 'PHARMA2T');

                // echo '<img src="data:image/png,' . DNS1D::getBarcodePNG('4', 'C39+') . '" alt="barcode"   />';

                // echo DNS1D::getBarcodePNGPath('4260171666221', 'PHARMA2T');

                // echo '<img src="data:image/png;base64,' . DNS1D::getBarcodePNG('4', 'C39+') . '" alt="barcode"   />';

            }

            

        }
 
        $customPaper    = array(0,0,1000.00,650.00);

        $pdf            = PDF::loadView('pdf', compact('orders'))->setPaper($customPaper, 'portrait');

        //return $pdf->stream();
        
        $destinationPath = public_path('order_pdf');
        if (! File::exists( $destinationPath ) ) {
            File::makeDirectory( $destinationPath );
        }
        $pdffile = $destinationPath.'/'.'order_'.time().uniqid().'.pdf';
        $pdf->save($pdffile);

        $excelFile = $destinationPath.'/'.'order_'.time().'.xlsx';

        Excel::store(new PrintOrderExport($orders, $idwarehouse), 'orders_'.time().'.xls', 'order_pdf');
 
        $zip        = new ZipArchive;
        $fileName   = 'documents/'.time()."_order.zip";

        $destinationPath = public_path('documents');
        if (! File::exists( $destinationPath ) ) {
            File::makeDirectory( $destinationPath );
        }

        if ($zip->open($fileName, ZipArchive::CREATE) === TRUE) {
            $files = File::files(public_path()."/order_pdf/");
            foreach ($files as $key => $value) {
                $file = basename($value);
                if(pathinfo($file, PATHINFO_EXTENSION) !='png'){
                    $zip->addFile($value, $file);
                }
            }
            $zip->close();
        }

        $document = new Document();
        $document->file = $fileName;
        $document->type = 'print';
        $document->save();

        $files = File::files(public_path()."/order_pdf/");
        foreach ($files as $key => $value) {
            $file = basename($value);
            File::delete(public_path()."/order_pdf/".$file);
        }
       // Session::flash('download.in.the.next.request', $fileName);
       // return Redirect::to('orderView');
        return response()->download(public_path($fileName));
    }



    public function getWoocommerceOrders() {

        $options = [

            'per_page' => 100, // Or your desire number

            'page' => 1

        ];

        



        $channels = DB::table("channel")

                        ->leftjoin("platform", "channel.platformid", "=", "platform.platformid")

                        ->where("platform.platformtype", "=", "Woocommerce")

                        ->select("channel.*")

                        ->get();



        $noneProducts = [];

        

        foreach($channels as $channel) {

            if($channel->woo_store_url != null && $channel->woo_store_url != "") {
                
                config([
                    'woocommerce.store_url' => $channel->woo_store_url,
                    'woocommerce.consumer_key' => $channel->woo_consumer_key,
                    'woocommerce.consumer_secret' => $channel->woo_consumer_secret
                ]);
                Session::put("WOOCOMMERCE_STORE_URL",       $channel->woo_store_url);
                Session::put("WOOCOMMERCE_CONSUMER_KEY",    $channel->woo_consumer_key);
                Session::put("WOOCOMMERCE_CONSUMER_SECRET", $channel->woo_consumer_secret);

                $orders = Order::all($options, $channel);

                $shortname          = $channel->shortname;   

                $idcompany          = $channel->idcompany;    

                $vat                = $channel->vat; 

                $platformid         = $channel->platformid; 

                $idwarehouse        = $channel->warehouse;

                $idchannel          = $channel->idchannel; 

                $countryname        = $channel->country;

                $warehouse          = $channel->warehouse;

                

                foreach($orders as $order) {

                    $referenceorder         = $order->id; 

                    $platformname           = 'Woocommerce';

                    $sum                    = $order->total;

                    $currency               = $order->currency;

                    $id                     = $order->id;

                    $cdate                  = $order->date_created;

                    $dateweekcell           = date_create($cdate);

                    $dateweek               = date_format($dateweekcell,"W");

                    $newcdate               = date_create($cdate);

                    $newcdateform           = date_format($newcdate,"Y/m/d");

                    $tracking               = '';

                    $carref                 = '';

                    $print_shipping         = '';

                    $platform               = 'Woocommerce';

                    $PostalCode             = $order->shipping->postcode;

                    $city                   = $order->shipping->city;

                    $country                = $order->shipping->country;

                    $region                 = $order->shipping->state;

                    $customer               = $order->shipping->first_name.' '.$order->billing->last_name;

                    $address_1              = $order->shipping->address_1;

                    $address_2              = $order->shipping->address_2;

                    $plz1                   = $order->billing->postcode;

                    $city1                  = $order->billing->city;

                    $country1               = $order->billing->country;

                    $email1                 = $order->billing->email;

                    $phone1                 = $order->billing->phone;

                    $region1                = $order->billing->state;

                    $inv_customer           = $order->billing->first_name.' '.$order->billing->last_name;

                    $inv_address1           = $order->billing->address_1;

                    $inv_address2           = $order->billing->address_2;

                    $orderstaus             = $order->status;

                    $transactionId          = $order->transaction_id;

                    $customer_note          = $order->customer_note;

                    $registeredtosolddayok  = 0;            

                    $courierinformedok      = 0;           

                    $trackinguploadedok     = 0;

                    $registeredtolagerstandok   = 0;



                    if($order->payment_method == "bacs") {

                        $idpayment              = "";

                    } else {

                        $idpayment              = $order->payment_method;

                    }

                   

                    $items                  = $order->line_items;

                    

                    foreach($items as $item) {

                        $sku            = $item->sku;



                        if($sku == "10452 S27kit") {

                            print_r($order);

                        }



                        $orderItemId    = $item->id;

                        $quantity       = $item->quantity;

                        $modelcode      = explode(" ", $sku)[0];

                        echo $id."--------------".$orderItemId."<br>";

                        if($sum == 0 || $quantity == 0 || $orderstaus == "cancelled" || $orderstaus == "refunded") {

                            $print_shipping             = 1;

                            $registeredtosolddayok      = 1;

                            $registeredtolagerstandok   = 1;

                            $courierinformedok          = 1;

                            $trackinguploadedok         = 1;

                            $idpayment                  = "Deleted";

                            $tracking                   = '---';

                            $carref                     = '---';

                        }



                        if($orderstaus == "completed") {

                            $print_shipping             = 1;

                            $registeredtosolddayok      = 1;

                            $registeredtolagerstandok   = 1;

                            $courierinformedok          = 1;

                            $trackinguploadedok         = 1;

                            $tracking                   = 'Shipped';

                            $carref                     = 'Shipped';

                        }



                        $productExist = DB::table('product')

                                            ->where('sku', '=', $sku)

                                            ->first();



                        if(empty($productExist)) {

                            array_push($noneProducts, $modelcode);

                            $productId          = $this->product_check_insert_two('sku', $sku, '', $quantity,$sku);

                        } else {

                            $productId          = $productExist->productid;

                        }



                        $orderExist = DB::table(($idpayment !='' ? "orderitem" : "order_to_pay"))
                                        ->where('referenceorder', '=', $id)
                                        ->first();



                        if(!empty($orderExist)) {

                            $itemExist = DB::table(($idpayment !='' ? "orderitem" : "order_to_pay"))

                                            ->where('referenceorder',   '=', $id)

                                            ->where('order_item_id',    '=', $orderItemId)

                                            ->first();



                            if(!empty($itemExist)) {

                                DB::table(($idpayment !='' ? "orderitem" : "order_to_pay"))

                                    ->where('referenceorder',   '=', $id)

                                    ->where('order_item_id',    '=', $orderItemId)

                                    ->update([

                                        'sync'                  => 'Synch with Woocommerce',

                                        'registeredtosolddayok' => $registeredtosolddayok, 

                                        'courierinformedok'     => $courierinformedok,

                                        'trackinguploadedok'    => $trackinguploadedok,

                                        'productid'             => $productId,

                                        'carriername'           => $carref, 

                                        'tracking'              => $tracking,

                                        'customer'              => $customer,

                                        'address1'              => $address_1,

                                        'address2'              => $address_2,

                                        'delivery_Instructions' => $customer_note,

                                        'inv_customer'          => $inv_customer,

                                        'inv_address1'          => $inv_address1,

                                        'inv_address2'          => $inv_address2,

                                        'country1'              => $country1,

                                        'plz1'                  => $plz1,

                                        'city1'                 => $city1, 

                                        'telefon1'              => $phone1,

                                        'region1'               => $region1,

                                        'idcompany'             => $idcompany,

                                        'referencechannel'      => $idchannel,

                                        'idwarehouse'           => $warehouse,

                                        'printedshippingok'     => $print_shipping

                                    ]);

                            } else {
                                DB::table(($idpayment !='' ? "orderitem" : "order_to_pay"))
                                    ->insert([
                                        'idorderplatform'           => $id,
                                        'registeredtolagerstandok'  => $registeredtolagerstandok, 
                                        'multiorder'                => $id,
                                        'referenceorder'            => $id,
                                        'sync'                      => 'Synch with Woocommerce', 
                                        'idcompany'                 => $idcompany,
                                        'referencechannel'          => $idchannel,
                                        'productid'                 => $productId,
                                        'weeksell'                  => $dateweek, 
                                        'datee'                     => $newcdateform,
                                        'delivery_Instructions'     => $customer_note,
                                        'quantity'                  => $quantity,
                                        'sum'                       => $sum, 
                                        'carriername'               => $carref, 
                                        'tracking'                  => $tracking,
                                        'idpayment'                 => $idpayment,
                                        'idwarehouse'               => $warehouse,
                                        'platformname'              => $platform, 
                                        'referencechannelname'      => $shortname,
                                        'customer'                  => $customer,
                                        'address1'                  => $address_1,
                                        'address2'                  => $address_2,
                                        'country'                   => $country,
                                        'currency'                  => $currency,
                                        'plz'                       => $PostalCode,
                                        'city'                      => $city, 
                                        'region'                    => $region,
                                        'inv_customer'              => $inv_customer,
                                        'inv_address1'              => $inv_address1,
                                        'inv_address2'              => $inv_address2,
                                        'country1'                  => $country1,
                                        'plz1'                      => $plz1,
                                        'city1'                     => $city1, 
                                        'telefon1'                  => $phone1,
                                        'region1'                   => $region1,
                                        'order_item_id'             => $orderItemId,
                                        'transactionId'             => $transactionId,
                                        'registeredtosolddayok'     => $registeredtosolddayok,
                                        'courierinformedok'         => $courierinformedok, 
                                        'trackinguploadedok'        => $trackinguploadedok,
                                        'printedshippingok'         => $print_shipping
                                    ]);
                            }

                        } else {
                            DB::table(($idpayment !='' ? "orderitem" : "order_to_pay"))
                                ->insert([
                                    'idorderplatform'           => $id,
                                    'registeredtolagerstandok'  => $registeredtolagerstandok, 
                                    'referenceorder'            => $id,
                                    'sync'                      => 'Synch with Woocommerce',
                                    'idcompany'                 => $idcompany,
                                    'referencechannel'          => $idchannel,
                                    'productid'                 => $productId,
                                    'weeksell'                  => $dateweek, 
                                    'datee'                     => $newcdateform,
                                    'quantity'                  => $quantity,
                                    'inv_customer'              => $inv_customer,
                                    'inv_address1'              => $inv_address1,
                                    'inv_address2'              => $inv_address2,
                                    'country1'                  => $country1,
                                    'delivery_Instructions'     => $customer_note,
                                    'plz1'                      => $plz1,
                                    'city1'                     => $city1, 
                                    'telefon1'                  => $phone1,
                                    'region1'                   => $region1,
                                    'sum'                       => $sum, 
                                    'idpayment'                 => $idpayment,
                                    'idwarehouse'               => $warehouse,
                                    'platformname'              => $platform, 
                                    'referencechannelname'      => $shortname,
                                    'country'                   => $country,
                                    'currency'                  => $currency,
                                    'plz'                       => $PostalCode,
                                    'city'                      => $city,
                                    'carriername'               => $carref, 
                                    'inv_customer'              => $inv_customer,
                                    'inv_address1'              => $inv_address1,
                                    'inv_address2'              => $inv_address2,
                                    'country1'                  => $country1,
                                    'plz1'                      => $plz1,
                                    'city1'                     => $city1, 
                                    'telefon1'                  => $phone1,
                                    'region1'                   => $region1,
                                    'tracking'                  => $tracking, 
                                    'customer'                  => $customer,
                                    'address1'                  => $address_1,
                                    'address2'                  => $address_2,
                                    'region'                    => $region,
                                    'order_item_id'             => $orderItemId,
                                    'transactionId'             => $transactionId,
                                    'registeredtosolddayok'     => $registeredtosolddayok,
                                    'courierinformedok'         => $courierinformedok, 
                                    'trackinguploadedok'        => $trackinguploadedok, 
                                    'printedshippingok'         => $print_shipping

                                ]);
                        }             
                    }
                }
                if(count($noneProducts) > 0) {
                    Session::put('noneProducts', $noneProducts);
                }

            }

        }

        

        //return redirect()->route('orderView');

    }



    public function createOrderInvoice() {

        $orderId    = $_GET['del'];

        $type       = $_GET['type'];



        $orders     = DB::table('orderitem')

                        ->leftjoin('companyinfo', 'companyinfo.idcompany', '=', 'orderitem.idcompany')

                        ->leftjoin('product', 'orderitem.productid', '=', 'product.productid')

                        ->leftjoin('channel', 'orderitem.referencechannel', '=', 'channel.idchannel')

                        // ->where('orderitem.carriername',          '!=', '')

                        // ->where('orderitem.multiorder',           '=', '0')

                        // ->where('orderitem.printedshippingok',    '=', 0)

                        ->where('orderitem.idorder',          '=', $orderId)

                        //->orderby('orderitem.referenceorder', 'desc')

                        ->select('orderitem.*', 'companyinfo.shortname as shortnamecompany', 'companyinfo.street1 as street1company', 'product.ean', 'companyinfo.plz as plzcomapny', 

                                'companyinfo.shortname as companyName', 'companyinfo.street1', 'companyinfo.phone as companyPhone', 'companyinfo.email as companyEmail', 'product.sku', 'product.modelcode', 'channel.vat as channelVat', 

                                'companyinfo.city as citycomapny', 'companyinfo.linklogo', 'companyinfo.country as countrycomapny', 'companyinfo.fax as companyFax', 'companyinfo.province as provincecompany',

                                'companyinfo.note as companyNote', 'companyinfo.bankInformation', 'companyinfo.noteInvoice',)

                        ->first();



        $multiOrders = DB::table('orderitem')

                        ->leftjoin('product', 'orderitem.productid', '=', 'product.productid')

                        ->where('orderitem.multiorder', '=', $orders->referenceorder)

                        ->get();



        

        $vat    = DB::table('modelvat') 

                    ->leftjoin('country', 'country.countryid', '=', 'modelvat.countryid')

                    ->where('country.shortname', '=', $orders->country)

                    ->first();



        if(!empty($vat)) {

            $valuevat      = $vat->valuevat;

        } else {

            $valuevat      = 0;

        }



        if(count($multiOrders) > 0) {

            $orders->multiOrders = $multiOrders;

        }





        $customPaper    = array(0,0,650.00,1000.00);
        $order = OrderItem::findOrFail($orderId);
        $pdf            = PDF::loadView('invoicePdf', compact('orders','order'))->setPaper($customPaper, 'portrait');
        return  $pdf->stream();
        return $pdf->download('invoice.pdf');

    }



    public function editInvoiceData(Request $request) {

        $idorder            = $request->idorder;

        $inv_customer       = $request->inv_customer;

        $inv_customerextra  = $request->inv_customerextra;

        $inv_vat            = $request->inv_vat;

        $inv_address1       = $request->inv_address1;

        $inv_address2       = $request->inv_address2;

        $plz1               = $request->plz1;

        $city1              = $request->city1;

        $country1           = $request->country1;

        $telefon1           = $request->telefon1;

        $fax1               = $request->fax1;

        $email1             = $request->email1;



        DB::table('orderitem')

            ->where('idorder', '=', $idorder)

            ->update([

                'inv_customer'          => $inv_customer,

                'inv_customerextra'     => $inv_customerextra,

                'inv_vat'               => $inv_vat,

                'inv_address1'          => $inv_address1,

                'inv_address2'          => $inv_address2,

                'plz1'                  => $plz1,

                'city1'                 => $city1,

                'country1'              => $country1,

                'telefon1'              => $telefon1,

                'fax1'                  => $fax1,

                'email1'                => $email1

            ]);



        return redirect()->route('orderView');

    }

}

