<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $primaryKey = 'idorder';
    protected $table = 'orderitem';
    public $timestamps = false;

    protected $fillable = ['idorderplatform','idcompany','referencechannelname','platformname','referencechannel','weeksell','datee','quantity','productid','sum','currency','idpayment','idchannel','idwarehouse','referenceorder','customer','customerextra','address1','address2','plz','city','region','country','telefon','fax','email','carriername','tracking','groupshipping','printedshippingok','invoicenr','inv_customer','inv_customerextra','inv_vat','inv_address1','inv_address2','plz1','city1','region1','telefon1','fax1','email1','notes','order_item_id','ship_service_level','transactionId','delivery_Instructions','multiorder','registeredtolagerstandok','registeredtosolddayok','courierinformedok','trackinguploadedok'];

    public function products(){
        return $this->hasMany('App\Models\Product','productid','productid');
    }
    
    public function product(){
        return $this->hasOne('App\Models\Product', 'productid', 'productid');
    }
    
    public function company(){
        return $this->hasOne('App\Models\Company', 'idcompany', 'idcompany');
    }
    
    public function channel(){
        return $this->hasOne('App\Models\Channel', 'idchannel', 'referencechannel');
    }
    
    public function warehouse(){
        return $this->hasOne('App\Models\Warehouse', 'idwarehouse', 'idwarehouse');
    }
    
    public function orderInvoice(){
        return $this->hasOne('App\Models\OrderInvoice', 'idorder', 'idorder');
    }
}
