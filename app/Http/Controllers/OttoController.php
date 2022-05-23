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
use Codexshaper\WooCommerce\Facades\Product;

class OttoController extends Controller {
    
    public function getOrders(){

    	echo "Hello";

    }
 
}
