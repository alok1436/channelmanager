<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Price extends Model
{
    public $primaryKey = "price_id";
    public $timestamps = false;

    public $fillable = [
                        'shipping',
                        'itemId',
                        'country',
                        'product_id',
                        'online_shipping',
                        'last_update_shipping',
                        'last_update_qty_date',
                        'last_update_date',
                        'online_quentity',
                        'channel_id',
                        'warehouse_id',
                        'platform_id',
                        'country',
                        'sku',
                        'online_price',
                        'ean',
                        'asin',
                        'price',
                        'online_price',
                        'online_shipping',
                        'created_date',
                        'updated_date',
                        'ebayActive',
                    ];
    
    public function product(){
        return $this->hasOne('App\Models\Product', 'productid', 'product_id');
    }
}
