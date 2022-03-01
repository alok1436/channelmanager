<?php 
namespace App\Exports;
use DB;
use App\Invoice;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use App\Models\Product;
use App\Models\LagerStand;

class PrintOrderExport implements FromView
{
    public $orders;
    public $idwarehouse;
    
    public function __construct($orders, $idwarehouse){
        $this->orders = $orders;
        $this->idwarehouse = $idwarehouse;
    }
    public function view(): View
    {
        return view('exports.printOrders', [
            'orders' => $this->orders, 'idwarehouse' => $this->idwarehouse
        ]);
    }
}
?>