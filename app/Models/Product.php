<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'product';
    public $timestamps = false;
    public $primaryKey = 'productid';
    public function manufacturer(){
        return $this->hasOne('App\Models\Manufacture', 'manufacturerid', 'manufacturerid');
    }

    public function lagerStand(){
        return $this->hasMany('App\Models\LagerStand', 'productid', 'productid');
    }
    
    public function getTotalQuantity($warehouse){

        $kitProducts = Product::where('product.virtualkit', "=", "Yes")->get();
        $quantitySum = 0;  
        foreach($kitProducts as $kitProduct) {
            for($i=1; $i<10; $i++) {
                $item = "pcs".$i;
                $itemProductId = "productid".$i;
                if($kitProduct->$itemProductId == $this->modelcode) {
                    if($warehouse){
                        $kitquantity = $kitProduct->lagerStand()->where('idwarehouse', $warehouse->idwarehouse)->sum('quantity');
                    }else{
                        $kitquantity = $kitProduct->lagerStand()->sum('quantity');
                    }
                    $quantitySum  +=  ($kitquantity * $kitProduct->$item); 
                }
            }
        }    
        
        if($warehouse){
            $mainProductTotalQuantity = $this->lagerStand()->where('idwarehouse', $warehouse->idwarehouse)->sum('quantity');
        }else{
            $mainProductTotalQuantity = $this->lagerStand()->sum('quantity');
        }
        
        $totalQauntity = $quantitySum + $mainProductTotalQuantity;
        return $totalQauntity;
    }
}
