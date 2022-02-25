<?php

namespace App\Http\Controllers;
use App\Models\Product as ProductModal;
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
use Yajra\Datatables\Datatables;
use Codexshaper\WooCommerce\Facades\Coupon;
use Codexshaper\WooCommerce\Facades\Product;

class ProductController extends Controller {
    
    public function productlist(){
    
        $products = ProductModal::get();
        
        $datatable = Datatables::of($products);
        $datatable->addColumn('sort', function($row) {
                return '<input type="radio" value="'.$row->sku.'" name="select_product" class="select_product" style="position: relative;left: 0;opacity:1">';
        });
        $datatable->rawColumns(['sort']);
        return $datatable->make(true);
    }
    
    public function productView() {
        if(!Session::has('userID')) {
            return redirect()->route('login');
        }
    
        

        $query       = DB::table('product')
                        ->leftjoin('manufacturer', 'manufacturer.manufacturerid', '=', 'product.manufacturerid');

        if(isset($_GET['keyword']) && $_GET['keyword'] != "") {
            $keyword = $_GET['keyword'];
            $query->where("modelcode", "=", $keyword)
                    ->orwhere("sku", "=", $keyword)
                    ->orwhere("ean", "=", $keyword)
                    ->orwhere("nameshort", "=", $keyword)
                    ->orwhere("namelong", "=", $keyword)
                    ->orwhere("asin", "=", $keyword);
        }
        if(isset($_GET['sort'])) {
            if($_GET['sortItem'] != "") {
                $query->orderby($_GET['sortItem'], $_GET['sort']);
            }
        }
        $products = $query->select('product.*', 'manufacturer.shortname as manufacturername')
            ->paginate(100);
        $manufacturers  = DB::table('manufacturer')
                            ->get();
        $subcategorys   = DB::table('subcategory')
                            ->get();

        $channels       = DB::table("channel")
                            ->where('sync', '=', 'Automatic Synch with: Amazon')
                            ->get();

        $params['products']         = $products;
        $params['channels']         = $channels;
        $params['manufacturers']    = $manufacturers;
        $params['subcategorys']     = $subcategorys;
        
        if(isset($_GET['viewType']) && $_GET['viewType'] == "expert") {
            Session::put("productViewType", $_GET['viewType']);
        } 
        
        if(isset($_GET['viewType']) && $_GET['viewType'] == "normal") {
            Session::forget("productViewType");
        }
        return View::make('productView', $params);
    }

    public function productDelete() {
        $id = $_GET['del'];

        DB::table('product')
            ->where('productid', '=', $id)
            ->delete();
        
        return Redirect::route('productView')->with(['msg' => 'product deleted']);
    }

    public function productUpdate() {
        $productid      = $_GET["id"];
        $fieldName      = $_GET["field"];
        $fieldValue     = $_GET["value"]; 

        DB::table('product')
            ->where('productid', '=', $productid)
            ->update([
                $fieldName    => $fieldValue
            ]);

        return 'success';
    }

    public function productAdd(Request $request) {
        $sort           = $request['sort'];
        $modelcode      = $request['modelcode'];
        $nameshort      = $request['nameshort'];
        $namelong       = $request['namelong'];
        $buffer         = $request['buffer'];
        $target         = $request['target'];
        $category       = '';
        $subcat         = $request['subcat'];
        $ordertime      = $request['ordertime'];
        $orderrangetime = $request['orderrangetime'];
        $asin           = $request['asin'];
        $ean            = $request['ean'];
        $sku            = $request['sku'];
        $asin2          = $request['asin2'];
        $ean2           = $request['ean2'];
        $sku2           = $request['sku2'];
        $asin3          = $request['asin3'];
        $ean3           = $request['ean3'];
        $sku3           = $request['sku3'];
        $Lengthcm       = $request['Lengthcm'];
        $Widthcm        = $request['Widthcm'];
        $Heightcm       = $request['Heightcm'];
        $Weightkg       = $request['Weightkg'];
        $Lengthcmbox    = $request['Lengthcmbox'];
        $Widthcmbox     = $request['Widthcmbox'];
        $Heightcmbox    = $request['Heightcmbox'];
        $Weightkgbox    = $request['Weightkgbox'];
        $mq1000box      = NULL;
        $itemsinpaket1  = $request['itemsinpaket1'];
        $itemsinpaket2  = $request['itemsinpaket2'];
        $itemsinpaket3  = $request['itemsinpaket3'];
        $pcs1           = $request['pcs1'];
        $pcs2           = $request['pcs2'];
        $pcs3           = $request['pcs3'];
        $pcs4           = $request['pcs4'];
        $pcs5           = $request['pcs5'];
        $pcs6           = $request['pcs6'];
        $pcs7           = $request['pcs7'];
        $pcs8           = $request['pcs8'];
        $pcs9           = $request['pcs9'];
        $productid1     = $request['productid1'];
        $productid2     = $request['productid2'];
        $productid3     = $request['productid3'];
        $productid4     = $request['productid4'];
        $productid5     = $request['productid5'];
        $productid6     = $request['productid6'];
        $productid7     = $request['productid7'];
        $productid8     = $request['productid8'];
        $productid9     = $request['productid9'];
        $description    = $request['description'];
        $virtualkit1    = 0;

        if ($request->file('image')) {
            $file = $request->file('image');            
            $filename = time().'.'.$file->getClientOriginalExtension();
            $this->resizeImage($file, $filename);
        } else {
            $filename = "";
        }

        if(isset($request['active'])){
            $active = $request['active'];
        }

        if($active==1)
            $active="Yes";
        else
            $active = "No";
        
        if(isset($request['virtualkit'])){
            $virtualkit = $request['virtualkit'];
        } 
        
        if(isset($virtualkit) && $virtualkit==1){
            $virtualkit     = "Yes";
            $price          = NULL;
            $dateprice      = NULL;
            $manufacturerid = 0;
            $codemanu       = '';
            $content        = '';
        } else {
            $virtualkit     = "No";
            $price          = str_replace(",",".", $request['price']);
            if($request['dateprice'] != '0000-00-00') {
                $dateprice = date('Y-m-d', strtotime($request['dateprice']));
            } else {
                $dateprice = null;
            }
            $manufacturerid = $request['manufacturerid'];
            $codemanu       = $request['codemanu'];
            $content        = $request['content'];
        }

        DB::table('product')
            ->insert([
                'sort'              => $sort,
                'active'            => $active,
                'modelcode'         => $modelcode,
                'nameshort'         => $nameshort,
                'namelong'          => $namelong,
                'price'             => (float)$price,
                'dateprice'         => $dateprice,
                'buffer'            => $buffer,
                'target'            => $target,
                'category'          => $category,
                'subcat'            => $subcat,
                'manufacturerid'    => $manufacturerid,
                'codemanu'          => $codemanu,
                'content'           => $content,
                'ordertime'         => $ordertime,
                'orderrangetime'    => $orderrangetime,
                'asin'              => $asin,
                'ean'               => $ean,
                'sku'               => $sku,
                'asin2'             => $asin2,
                'ean2'              => $ean2,
                'sku2'              => $sku2,
                'asin3'             => $asin3,
                'ean3'              => $ean3,
                'sku3'              => $sku3,
                'Lengthcm'          => $Lengthcm,
                'Widthcm'           => $Widthcm,
                'Heightcm'          => $Heightcm,
                'Weightkg'          => $Weightkg,
                'Lengthcmbox'       => $Lengthcmbox,
                'Widthcmbox'        => $Widthcmbox,
                'Heightcmbox'       => $Heightcmbox,
                'Weightkgbox'       => $Weightkgbox,
                'mq1000box'         => $mq1000box,
                'itemsinpaket1'     => $itemsinpaket1,
                'itemsinpaket2'     => $itemsinpaket2,
                'itemsinpaket3'     => $itemsinpaket3,
                'virtualkit'        => $virtualkit,
                'pcs1'              => $pcs1,
                'pcs2'              => $pcs2,
                'pcs3'              => $pcs3,
                'pcs4'              => $pcs4,
                'pcs5'              => $pcs5,
                'pcs6'              => $pcs6,
                'pcs7'              => $pcs7,
                'pcs8'              => $pcs8,
                'pcs9'              => $pcs9,
                'productid1'        => $productid1,
                'productid2'        => $productid2,
                'productid3'        => $productid3,
                'productid4'        => $productid4,
                'productid5'        => $productid5,
                'productid6'        => $productid6,
                'productid7'        => $productid7,
                'productid8'        => $productid8,
                'productid9'        => $productid9,
                'description'       => $description,
                'image'             => $filename
            ]);

        return Redirect::route('productView')->with(['msg' => 'product added']);
    }

    public function productAddView() {
        if(!Session::has('userID')) {
            return redirect()->route('login');
        }
        $products       = DB::table('product')
                            ->orderby('modelcode', 'asc')
                            ->get();
        $manufacturers  = DB::table('manufacturer')
                            ->get();
        $subcategorys   = DB::table('subcategory')
                            ->get();

        $params['manufacturers']    = $manufacturers;
        $params['subcategorys']     = $subcategorys;
        $params['products']         = $products;
        return View::make('productAddView', $params);
    }
    
    public function productEditView() {
        if(!Session::has('userID')) {
            return redirect()->route('login');
        }
        $productId      = $_GET["productId"];
        $editproduct    = DB::table('product')
                            ->where('productId', '=', $productId)
                            ->get();
        $products       = DB::table('product')
                            ->get();
        $manufacturers  = DB::table('manufacturer')
                            ->get();
        $subcategorys   = DB::table('subcategory')
                            ->get();

        $params['editproduct']      = $editproduct[0];
        $params['manufacturers']    = $manufacturers;
        $params['subcategorys']     = $subcategorys;
        $params['products']         = $products;

        return View::make('productEditView', $params);
    }

    public function productEdit(Request $request) {
        $sort           = $request['sort'];
        $modelcode      = $request['modelcode'];
        $nameshort      = $request['nameshort'];
        $namelong       = $request['namelong'];
        $buffer         = $request['buffer'];
        $target         = $request['target'];
        $category       = '';
        $subcat         = $request['subcat'];
        $ordertime      = $request['ordertime'];
        $orderrangetime = $request['orderrangetime'];
        $asin           = $request['asin'];
        $ean            = $request['ean'];
        $sku            = $request['sku'];
        $asin2          = $request['asin2'];
        $ean2           = $request['ean2'];
        $sku2           = $request['sku2'];
        $asin3          = $request['asin3'];
        $ean3           = $request['ean3'];
        $sku3           = $request['sku3'];
        $Lengthcm       = $request['Lengthcm'];
        $Widthcm        = $request['Widthcm'];
        $Heightcm       = $request['Heightcm'];
        $Weightkg       = $request['Weightkg'];
        $Lengthcmbox    = $request['Lengthcmbox'];
        $Widthcmbox     = $request['Widthcmbox'];
        $Heightcmbox    = $request['Heightcmbox'];
        $Weightkgbox    = $request['Weightkgbox'];
        $mq1000box      = NULL;
        $itemsinpaket1  = $request['itemsinpaket1'];
        $itemsinpaket2  = $request['itemsinpaket2'];
        $itemsinpaket3  = $request['itemsinpaket3'];
        $pcs1           = $request['pcs1'];
        $pcs2           = $request['pcs2'];
        $pcs3           = $request['pcs3'];
        $pcs4           = $request['pcs4'];
        $pcs5           = $request['pcs5'];
        $pcs6           = $request['pcs6'];
        $pcs7           = $request['pcs7'];
        $pcs8           = $request['pcs8'];
        $pcs9           = $request['pcs9'];
        $productid1     = $request['productid1'];
        $productid2     = $request['productid2'];
        $productid3     = $request['productid3'];
        $productid4     = $request['productid4'];
        $productid5     = $request['productid5'];
        $productid6     = $request['productid6'];
        $productid7     = $request['productid7'];
        $productid8     = $request['productid8'];
        $productid9     = $request['productid9'];
        $description    = $request['description'];
        $virtualkit1    = 0;

        if ($request->file('image')) {
            $file = $request->file('image');
            $filename = time().'.'.$file->getClientOriginalExtension();
            $this->resizeImage($file, $filename);
        } else {
            $filename = "";
        }
        if(isset($request['active'])){
            $active = $request['active'];
        }

        if($active==1)
            $active="Yes";
        else
            $active = "No";
        
        if(isset($request['virtualkit'])){
            $virtualkit = $request['virtualkit'];
        } 
        
        if(isset($request['virtualkit']) && $virtualkit==1){
            $virtualkit     = "Yes";
            $price          = NULL;
            $dateprice      = NULL;
            $manufacturerid = 0;
            $codemanu       = '';
            $content        = '';
        } else {
            $virtualkit     = "No";
            $price          = str_replace(",",".", $request['price']);
            if($request['dateprice'] != '0000-00-00') {
                $dateprice = date('Y-m-d', strtotime($request['dateprice']));
            } else {
                $dateprice = null;
            }
            $manufacturerid = $request['manufacturerid'];
            $codemanu       = $request['codemanu'];
            $content        = $request['content'];
        }

        $productIdhidden    = $request["productIdhidden"];
        DB::table('product')
            ->where('productid', '=', $productIdhidden)
            ->update([
                'sort'              => $sort,
                'active'            => $active,
                'modelcode'         => $modelcode,
                'nameshort'         => $nameshort,
                'namelong'          => $namelong,
                'price'             => (float)$price,
                'dateprice'         => $dateprice,
                'buffer'            => $buffer,
                'target'            => $target,
                'category'          => $category,
                'subcat'            => $subcat,
                'manufacturerid'    => $manufacturerid,
                'codemanu'          => $codemanu,
                'content'           => $content,
                'ordertime'         => $ordertime,
                'orderrangetime'    => $orderrangetime,
                'asin'              => $asin,
                'ean'               => $ean,
                'sku'               => $sku,
                'asin2'             => $asin2,
                'ean2'              => $ean2,
                'sku2'              => $sku2,
                'asin3'             => $asin3,
                'ean3'              => $ean3,
                'sku3'              => $sku3,
                'Lengthcm'          => $Lengthcm,
                'Widthcm'           => $Widthcm,
                'Heightcm'          => $Heightcm,
                'Weightkg'          => $Weightkg,
                'Lengthcmbox'       => $Lengthcmbox,
                'Widthcmbox'        => $Widthcmbox,
                'Heightcmbox'       => $Heightcmbox,
                'Weightkgbox'       => $Weightkgbox,
                'mq1000box'         => $mq1000box,
                'itemsinpaket1'     => $itemsinpaket1,
                'itemsinpaket2'     => $itemsinpaket2,
                'itemsinpaket3'     => $itemsinpaket3,
                'image'             => $filename,
                'virtualkit'        => $virtualkit,
                'pcs1'              => $pcs1,
                'pcs2'              => $pcs2,
                'pcs3'              => $pcs3,
                'pcs4'              => $pcs4,
                'pcs5'              => $pcs5,
                'pcs6'              => $pcs6,
                'pcs7'              => $pcs7,
                'pcs8'              => $pcs8,
                'pcs9'              => $pcs9,
                'productid1'        => $productid1,
                'productid2'        => $productid2,
                'productid3'        => $productid3,
                'productid4'        => $productid4,
                'productid5'        => $productid5,
                'productid6'        => $productid6,
                'productid7'        => $productid7,
                'productid8'        => $productid8,
                'productid9'        => $productid9,
                'description'       => $description
            ]);
        return Redirect::route('productView')->with(['msg' => 'Product edited!']);
    }
    
    public function productXlsImport(Request $request) {
        $this->validate($request, [
            'importproductfile'  => 'required|mimes:xls,xlsx'
        ]);
    
        $path = $request->file('importproductfile')->getRealPath();
        $data = Excel::load($path)->get();
        if($data->count() > 0) {
            foreach($data->toArray() as $key => $row) {
                $existedproduct = DB::table('product')
                    ->where('modelcode', '=', $row['modelcode'])
                    ->get();

                if($row['dateprice'] != '0000-00-00') {
                    $dateprice = date('Y-m-d', strtotime($row['dateprice']));
                } else {
                    $dateprice = null;
                }

                $manufacturerid = "";

                if($row['manufacturer'] != "") {
                    $manufacturer = DB::table('manufacturer')
                        ->where('shortname', '=', $row['manufacturer'])
                        ->first();

                    if(empty($manufacturer)) {
                        $manufacturerid = DB::table('manufacturer')
                            ->insertGetId([
                                'shortname' => $row['manufacturer']
                                //'longname' => $row['manufacturer']
                            ]);
                    } else {
                        $manufacturerid = $manufacturer->manufacturerid;
                    }
                } 

                if(count($existedproduct) == 0) {
                    DB::table('product')
                    ->insert([
                        'sort'              => $row['sort'],
                        'active'            => $row['active'],
                        'modelcode'         => $row['modelcode'],
                        'nameshort'         => $row['nameshort'],
                        'namelong'          => $row['namelong'],
                        'price'             => (float)str_replace(',', '.', $row['price']),
                        'dateprice'         => $dateprice,
                        'buffer'            => $row['buffer'],
                        'target'            => $row['target'],
                        'category'          => $row['category'],
                        'subcat'            => $row['subcat'],
                        'manufacturerid'    => $manufacturerid,
                        'codemanu'          => $row['codemanu'],
                        'content'           => $row['content'],
                        'ordertime'         => $row['ordertime'],
                        'orderrangetime'    => $row['orderrangetime'],
                        'asin'              => $row['asin'],
                        'ean'               => $row['ean'],
                        'sku'               => $row['sku'],
                        'asin2'             => $row['asin2'],
                        'ean2'              => $row['ean2'],
                        'sku2'              => $row['sku2'],
                        'asin3'             => $row['asin3'],
                        'ean3'              => $row['ean3'],
                        'sku3'              => $row['sku3'],
                        'Lengthcm'          => $row['lengthcm'],
                        'Widthcm'           => $row['widthcm'],
                        'Heightcm'          => $row['heightcm'],
                        'Weightkg'          => $row['weightkg'],
                        'Lengthcmbox'       => $row['lengthcmbox'],
                        'Widthcmbox'        => $row['widthcmbox'],
                        'Heightcmbox'       => $row['heightcmbox'],
                        'Weightkgbox'       => $row['weightkgbox'],
                        'mq1000box'         => $row['mq1000box'],
                        'itemsinpaket1'     => $row['itemsinpaket1'],
                        'itemsinpaket2'     => $row['itemsinpaket2'],
                        'itemsinpaket3'     => $row['itemsinpaket3'],
                        'image'             => $row['image'],
                        'virtualkit'        => $row['virtualkit'],
                        'pcs1'              => $row['pcs1'],
                        'pcs2'              => $row['pcs2'],
                        'pcs3'              => $row['pcs3'],
                        'pcs4'              => $row['pcs4'],
                        'pcs5'              => $row['pcs5'],
                        'pcs6'              => $row['pcs6'],
                        'pcs7'              => $row['pcs7'],
                        'pcs8'              => $row['pcs8'],
                        'pcs9'              => $row['pcs9'],
                        'productid1'        => $row['productid1'],
                        'productid2'        => $row['productid2'],
                        'productid3'        => $row['productid3'],
                        'productid4'        => $row['productid4'],
                        'productid5'        => $row['productid5'],
                        'productid6'        => $row['productid6'],
                        'productid7'        => $row['productid7'],
                        'productid8'        => $row['productid8'],
                        'productid9'        => $row['productid9'],
                        'description'       => $row['description']
                    ]);
                } else {
                    DB::table('product')
                    ->where('modelcode', '=', (string)$row['modelcode'])
                    ->update([
                        'sort'              => $row['sort'],
                        'active'            => $row['active'],
                        'modelcode'         => $row['modelcode'],
                        'nameshort'         => $row['nameshort'],
                        'namelong'          => $row['namelong'],
                        'price'             => (float)str_replace(',', '.', $row['price']),
                        'dateprice'         => $dateprice,
                        'buffer'            => $row['buffer'],
                        'target'            => $row['target'],
                        'category'          => $row['category'],
                        'subcat'            => $row['subcat'],
                        'manufacturerid'    => $row['manufacturer'],
                        'codemanu'          => $row['codemanu'],
                        'content'           => $row['content'],
                        'ordertime'         => $row['ordertime'],
                        'orderrangetime'    => $row['orderrangetime'],
                        'asin'              => $row['asin'],
                        'ean'               => $row['ean'],
                        'sku'               => $row['sku'],
                        'asin2'             => $row['asin2'],
                        'ean2'              => $row['ean2'],
                        'sku2'              => $row['sku2'],
                        'asin3'             => $row['asin3'],
                        'ean3'              => $row['ean3'],
                        'sku3'              => $row['sku3'],
                        'Lengthcm'          => $row['lengthcm'],
                        'Widthcm'           => $row['widthcm'],
                        'Heightcm'          => $row['heightcm'],
                        'Weightkg'          => $row['weightkg'],
                        'Lengthcmbox'       => $row['lengthcmbox'],
                        'Widthcmbox'        => $row['widthcmbox'],
                        'Heightcmbox'       => $row['heightcmbox'],
                        'Weightkgbox'       => $row['weightkgbox'],
                        'mq1000box'         => $row['mq1000box'],
                        'itemsinpaket1'     => $row['itemsinpaket1'],
                        'itemsinpaket2'     => $row['itemsinpaket2'],
                        'itemsinpaket3'     => $row['itemsinpaket3'],
                        'image'             => $row['image'],
                        'virtualkit'        => $row['virtualkit'],
                        'pcs1'              => $row['pcs1'],
                        'pcs2'              => $row['pcs2'],
                        'pcs3'              => $row['pcs3'],
                        'pcs4'              => $row['pcs4'],
                        'pcs5'              => $row['pcs5'],
                        'pcs6'              => $row['pcs6'],
                        'pcs7'              => $row['pcs7'],
                        'pcs8'              => $row['pcs8'],
                        'pcs9'              => $row['pcs9'],
                        'productid1'        => $row['productid1'],
                        'productid2'        => $row['productid2'],
                        'productid3'        => $row['productid3'],
                        'productid4'        => $row['productid4'],
                        'productid5'        => $row['productid5'],
                        'productid6'        => $row['productid6'],
                        'productid7'        => $row['productid7'],
                        'productid8'        => $row['productid8'],
                        'productid9'        => $row['productid9'],
                        'description'       => $row['description']
                    ]);
                }
            }
        }
        return Redirect::route('productView')->with(['msg' => 'Product imported!']);
    }

    public function productXlsExport() {
        $products       = DB::table('product')
                            ->leftjoin('manufacturer', 'product.manufacturerid', '=', 'manufacturer.manufacturerid')
                            ->select('product.*', 'manufacturer.shortname')
                            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'productid');
        $sheet->setCellValue('B1', 'sort');
        $sheet->setCellValue('C1', 'active');
        $sheet->setCellValue('D1', 'modelcode');
        $sheet->setCellValue('E1', 'nameshort');
        $sheet->setCellValue('F1', 'namelong');
        $sheet->setCellValue('G1', 'category');
        $sheet->setCellValue('H1', 'subcat');
        $sheet->setCellValue('I1', 'virtualkit');
        $sheet->setCellValue('J1', 'asin');
        $sheet->setCellValue('K1', 'ean');
        $sheet->setCellValue('L1', 'sku');
        $sheet->setCellValue('M1', 'asin2');
        $sheet->setCellValue('N1', 'ean2');
        $sheet->setCellValue('O1', 'sku2');
        $sheet->setCellValue('P1', 'asin3');
        $sheet->setCellValue('Q1', 'ean3');
        $sheet->setCellValue('R1', 'sku3');
        $sheet->setCellValue('S1', 'price');
        $sheet->setCellValue('T1', 'dateprice');
        $sheet->setCellValue('U1', 'itemsinpaket1');
        $sheet->setCellValue('V1', 'itemsinpaket2');
        $sheet->setCellValue('W1', 'itemsinpaket3');
        $sheet->setCellValue('X1', 'manufacturer');
        $sheet->setCellValue('Y1', 'codemanu');
        $sheet->setCellValue('Z1', 'content');
        $sheet->setCellValue('AA1', 'ordertime');
        $sheet->setCellValue('AB1', 'orderrangetime');
        $sheet->setCellValue('AC1', 'buffer');
        $sheet->setCellValue('AD1', 'target');
        $sheet->setCellValue('AE1', 'Lengthcm');
        $sheet->setCellValue('AF1', 'Widthcm');
        $sheet->setCellValue('AG1', 'Heightcm');
        $sheet->setCellValue('AH1', 'Weightkg');
        $sheet->setCellValue('AI1', 'Lengthcmbox');
        $sheet->setCellValue('AJ1', 'Widthcmbox');
        $sheet->setCellValue('AK1', 'Heightcmbox');
        $sheet->setCellValue('AL1', 'Weightkgbox');
        $sheet->setCellValue('AM1', 'mq1000box');
        $sheet->setCellValue('AN1', 'pcs1');
        $sheet->setCellValue('AO1', 'productid1');
        $sheet->setCellValue('AP1', 'pcs2');
        $sheet->setCellValue('AQ1', 'productid2');
        $sheet->setCellValue('AR1', 'pcs3');
        $sheet->setCellValue('AS1', 'productid3');
        $sheet->setCellValue('AT1', 'pcs4');
        $sheet->setCellValue('AU1', 'productid4');
        $sheet->setCellValue('AV1', 'pcs5');
        $sheet->setCellValue('AW1', 'productid5');
        $sheet->setCellValue('AX1', 'pcs6');
        $sheet->setCellValue('AY1', 'productid6');
        $sheet->setCellValue('AZ1', 'pcs7');
        $sheet->setCellValue('BA1', 'productid7');
        $sheet->setCellValue('BB1', 'pcs8');
        $sheet->setCellValue('BC1', 'productid8');
        $sheet->setCellValue('BD1', 'pcs9');
        $sheet->setCellValue('BE1', 'productid9');
        $sheet->setCellValue('BF1', 'image');
        $sheet->setCellValue('BG1', 'description');
        $rows = 2;

        foreach($products as $product){
            $sheet->setCellValue('A'. $rows, $product->productid);
            $sheet->setCellValue('B'. $rows, $product->sort);
            $sheet->setCellValue('C'. $rows, $product->active);
            $sheet->setCellValue('D'. $rows, $product->modelcode);
            $sheet->setCellValue('E'. $rows, $product->nameshort);
            $sheet->setCellValue('F'. $rows, $product->namelong);
            $sheet->setCellValue('G'. $rows, $product->category);
            $sheet->setCellValue('H'. $rows, $product->subcat);
            $sheet->setCellValue('I'. $rows, $product->virtualkit);
            $sheet->setCellValue('J'. $rows, $product->asin);
            $sheet->setCellValue('K'. $rows, $product->ean);
            $sheet->setCellValue('L'. $rows, $product->sku);
            $sheet->setCellValue('M'. $rows, $product->asin2);
            $sheet->setCellValue('N'. $rows, $product->ean2);
            $sheet->setCellValue('O'. $rows, $product->sku2);
            $sheet->setCellValue('P'. $rows, $product->asin3);
            $sheet->setCellValue('Q'. $rows, $product->ean3);
            $sheet->setCellValue('R'. $rows, $product->sku3);
            $sheet->setCellValue('S'. $rows, $product->price);
            $sheet->setCellValue('T'. $rows, $product->dateprice);
            $sheet->setCellValue('U'. $rows, $product->itemsinpaket1);
            $sheet->setCellValue('V'. $rows, $product->itemsinpaket2);
            $sheet->setCellValue('W'. $rows, $product->itemsinpaket3);
            $sheet->setCellValue('X'. $rows, $product->shortname);
            $sheet->setCellValue('Y'. $rows, $product->codemanu);
            $sheet->setCellValue('Z'. $rows, $product->content);
            $sheet->setCellValue('AA'. $rows, $product->ordertime);
            $sheet->setCellValue('AB'. $rows, $product->orderrangetime);
            $sheet->setCellValue('AC'. $rows, $product->buffer);
            $sheet->setCellValue('AD'. $rows, $product->target);
            $sheet->setCellValue('AE'. $rows, $product->Lengthcm);
            $sheet->setCellValue('AF'. $rows, $product->Widthcm);
            $sheet->setCellValue('AG'. $rows, $product->Heightcm);
            $sheet->setCellValue('AH'. $rows, $product->Weightkg);
            $sheet->setCellValue('AI'. $rows, $product->Lengthcmbox);
            $sheet->setCellValue('AJ'. $rows, $product->Widthcmbox);
            $sheet->setCellValue('AK'. $rows, $product->Heightcmbox);
            $sheet->setCellValue('AL'. $rows, $product->Weightkgbox);
            $sheet->setCellValue('AM'. $rows, $product->mq1000box);
            $sheet->setCellValue('AN'. $rows, $product->pcs1);
            $sheet->setCellValue('AO'. $rows, $product->productid1);
            $sheet->setCellValue('AP'. $rows, $product->pcs2);
            $sheet->setCellValue('AQ'. $rows, $product->productid2);
            $sheet->setCellValue('AR'. $rows, $product->pcs3);
            $sheet->setCellValue('AS'. $rows, $product->productid3);
            $sheet->setCellValue('AT'. $rows, $product->pcs4);
            $sheet->setCellValue('AU'. $rows, $product->productid4);
            $sheet->setCellValue('AV'. $rows, $product->pcs5);
            $sheet->setCellValue('AW'. $rows, $product->productid5);
            $sheet->setCellValue('AX'. $rows, $product->pcs6);
            $sheet->setCellValue('AY'. $rows, $product->productid6);
            $sheet->setCellValue('AZ'. $rows, $product->pcs7);
            $sheet->setCellValue('BA'. $rows, $product->productid7);
            $sheet->setCellValue('BB'. $rows, $product->pcs8);
            $sheet->setCellValue('BC'. $rows, $product->productid8);
            $sheet->setCellValue('BD'. $rows, $product->pcs9);
            $sheet->setCellValue('BE'. $rows, $product->productid9);
            $sheet->setCellValue('BF'. $rows, $product->image);
            $sheet->setCellValue('BG'. $rows, $product->description);
            $rows++;
        }

        $fileName = "emp.xls";
        $writer = new Xls($spreadsheet);
        $writer->save("export/".$fileName);
        $headers = [
            'Content-Type' => 'application/vnd.ms-excel',
         ];
        //header("Content-Type: application/vnd.ms-excel");
        
        return response()->download(public_path()."/export/".$fileName, $fileName, $headers);
    }

    public function resizeImage($file, $fileNameToStore) {
        // Resize image
        $resize = Image::make($file)->resize(60, null, function ($constraint) {
            $constraint->aspectRatio();
        })->encode('jpg');

        // Create hash value
        $hash = md5($resize->__toString());

        // Prepare qualified image name
        $image = $hash."jpg";

        // Put image to storage
        $save = Storage::put("public/images/{$fileNameToStore}", $resize->__toString());

        print_r($save);
        if($save) {
            return true;
        }
        return false;
    }

    public function newproductDelete() {
        $id = $_GET['del'];

        DB::table('tbl_none_product')
            ->where('id', '=', $id)
            ->delete();

        return redirect()->route('noneexistingproducts');
    }

    public function getNewWoocommerceProducts() {
        $channels = DB::table("channel")
                        ->leftjoin("platform", "channel.platformid", "=", "platform.platformid")
                        ->where("platform.platformtype", "=", "Woocommerce")
                        ->select("channel.*")
                        ->get();

        foreach($channels as $channel) {
            if($channel->woo_store_url != null && $channel->woo_store_url != "") {
                $page = 1;                
                
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
                    foreach($products as $product) {
                        $sku    = $product->sku;
                        $price  = $product->price;
                        $quantity = $product->stock_quantity;
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

                            if(empty($productExist)) {
                                DB::table('product')
                                    ->insert([
                                        'modelcode'         => $modelcode,
                                        'nameshort'         => $nameshort,
                                        'namelong'          => $namelong,
                                        'price'             => $price,
                                        'buffer'            => $quantity,
                                        'sku'               => $sku
                                    ]);

                                DB::table('tbl_open_activities')
                                    ->insert([
                                        'dateTime'          => date('Y-m-d H:i:s'),
                                        'issues'            => "Warning: The product ".$sku." is added in Product table. Please fill up the rest of the information."
                                    ]);
                            }       
                        }
                    }
                    
                    $page++;
                }
            }
        }

        return Redirect::route('productView')->with(['msg' => 'product added']);
    }

    public function offlineProductsView() {
        $channels   = DB::table("channel")
                        ->get();


        $products   = DB::table('product')
                        ->paginate(100);

        foreach($products as $product) {
            foreach($channels as $channel) {
                $channelId = $channel->idchannel;
                $priceExist = DB::table("prices")
                                ->where("sku", "=", $product->sku)
                                ->where("channel_id", "=", $channel->idchannel)
                                ->first();

                if(empty($priceExist)) {
                    $product->$channelId = "No";
                } else {
                    if($priceExist->online_quentity > 0) {
                        $product->$channelId = "Yes";
                    } else {
                        $product->$channelId = "Pause";
                    }
                }

                $blackListExist = DB::table("blacklistvendor")
                                    ->where("productid", "=", $product->productid)
                                    ->where("channelId", "=", $channelId)
                                    ->first();

                if(!empty($blackListExist)) {
                    $product->$channelId = "BL";
                }
            }
        }

        $params['products'] = $products;
        $params['channels'] = $channels;
        return View::make("offlineProductsView", $params);
    }

    public function addBlackList(Request $request) {
        $productId = $request->productId;
        $channelId = $request->channelId;
        $note      = $request->blackListNote;

        DB::table("blacklistvendor")
            ->insert([
                'productId' => $productId,
                'channelId' => $channelId,
                'date'      => date('Y-m-d'),
                'notes'     => $note
            ]);

        return Redirect::route('offlineProductsView');
    }

    public function removeFromBlackList() {
        $productId = $_GET['productId'];
        $channelId = $_GET['channelId'];

        DB::table("blacklistvendor")
            ->where("productId", "=", $productId)
            ->where("channelId", "=", $channelId)
            ->delete();

        return Redirect::back();
    }

    public function blacklistView() {
        $products   = DB::table("blacklistvendor")
                        ->leftjoin("product", "product.productid", "=", "blacklistvendor.productId")
                        ->leftjoin("channel", "blacklistvendor.channelId", "=", "channel.idchannel")
                        ->select("blacklistvendor.*", "product.sku", "channel.shortname")
                        ->paginate(100);

        $params['products'] = $products;
        return View::make("blacklistView", $params);
    }
}
