<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;


class OrderInvoice extends Model
{
    protected $fillable = ['shipping','vat'];
    public function order(){
        return $this->hasOne('App\Models\OrderItem', 'idorder', 'idorder');
    }
}
