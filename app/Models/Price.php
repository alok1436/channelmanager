<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Price extends Model
{
    public $primaryKey = "price_id";
    public $timestamps = false;
    
    public function product(){
        return $this->hasOne('App\Models\Product', 'productid', 'product_id');
    }
}
