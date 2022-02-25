<?php 
namespace App\Exports;
use DB;
use App\Invoice;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use App\Models\Product;
use App\Models\LagerStand;

class InventoryExport implements FromView
{
    public $request;
    
    public function __construct($request){
        $this->request = $request;
    }
    public function view(): View
    {
        
         $collection   = Product::leftjoin('manufacturer', 'product.manufacturerid', '=', 'manufacturer.manufacturerid');
                    $collection->where('product.virtualkit', "=", "No");
                    
                            
                    if($this->request->filled('orderByColumn') && $this->request->filled('orderByValue')){
                        $collection->orderBy($this->request->get('orderByColumn'), $this->request->get('orderByValue'));   
                    }
                    
           $products =         $collection->get();
        $collection = DB::table('warehouse');
        if($this->request->filled('idwarehouse')){
            $collection->where(['idwarehouse'=>$this->request->idwarehouse]);   
        }

        
        $warehouses = $collection->get();
        return view('exports.inventory', [
            'products' => $products, 'warehouses'=>$warehouses
        ]);
    }
}
?>