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
        //$collection->where('product.productId', "=", 86);      
                            
        if($this->request->filled('orderByColumn') && $this->request->filled('orderByValue')){
            $collection->orderBy($this->request->get('orderByColumn'), $this->request->get('orderByValue'));   
        }

         $kitProducts = DB::table('product')
                        ->leftjoin('manufacturer', 'product.manufacturerid', '=', 'manufacturer.manufacturerid')
                        ->where('product.virtualkit', "=", "Yes")
                        ->get();
                    
        $products =         $collection->get();


        $collection = DB::table('warehouse');
        if($this->request->filled('idwarehouse')){
            $collection->where(['idwarehouse'=>$this->request->idwarehouse]);   
        }

        $warehouses = $collection->get();

        foreach($products as $product) {
            $total_qty = 0;
            foreach($warehouses as $warehouse) {            
                $qty    = DB::table('lagerstand')
                        ->where('productid',    '=', $product->productid)
                        ->where('idwarehouse',  '=', $warehouse->idwarehouse)
                        ->selectRaw(DB::raw("COALESCE(sum(quantity),0) as total_qty"))
                        ->first();
                $total_qty += intval($qty->total_qty); 
                $product->{$warehouse->idwarehouse} = intval($qty->total_qty);

                $record = $product->lagerStand()->where('idwarehouse', $warehouse->idwarehouse)->first();

                $product->{"hall"} = $record ? $record->hall : '';
                $product->{"area"} = $record ? $record->area : '';
                $product->{"rack"} = $record ? $record->rack : '';
            }
            $product->total_qty = $total_qty;
        }

        foreach($kitProducts as $kitProduct) {
            $total_qty = 0;
            foreach($warehouses as $warehouse) {                
                $qty    = DB::table('lagerstand')
                        ->where('productid',    '=', $kitProduct->productid)
                        ->where('idwarehouse',  '=', $warehouse->idwarehouse)
                        ->selectRaw(DB::raw("COALESCE(sum(quantity),0) as total_qty"))
                        ->first();
                $total_qty += intval($qty->total_qty); 
                $kitProduct->{$warehouse->idwarehouse} = intval($qty->total_qty);
            }
            
            $kitProduct->total_qty = $total_qty;
        }

        foreach($products as $product) {
        if($product->virtualkit != "Yes") {
            foreach($kitProducts as $kitProduct) {
                for($i=1; $i<10; $i++) {
                        $item = "pcs".$i;
                        $itemProductId = "productid".$i;
                        if($kitProduct->$itemProductId == $product->modelcode) {
                            foreach($warehouses as $warehouse) {
                                $product->{$warehouse->idwarehouse} = intval($product->{$warehouse->idwarehouse}) + intval($kitProduct->{$warehouse->idwarehouse})*intval($kitProduct->$item);
                               
                                $product->total_qty = $product->total_qty+intval(intval($kitProduct->{$warehouse->idwarehouse})*intval($kitProduct->$item));
                            }
                        }
                    }
                }
            }
        }

     //   dd($products->toArray());

        return view('exports.inventory', [
            'products' => $products, 'warehouses'=>$warehouses
        ]);
    }
}
?>