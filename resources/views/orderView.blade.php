@extends('layouts.default')
@section('content')
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-sm-6 col-lg-6 col-md-6">
                                            <form action="">
                                                <input type="hidden" name="is_search" value="1" />
                                                <div class="btn-group btn-group-toggle" data-toggle="buttons" style="    margin-top: 20px;">
                                                    <label class="btn btn-outline-primary warehouse {{ request()->warehouse == 'all' || (!request()->warehouse) ? 'active' : '' }}">
                                                    <input type="radio" name="warehouse" id="option1" autocomplete="off" value="all" onchange="this.form.submit()"> All
                                                    </label>
                                                    @foreach($warehouses as $index =>$warehouse)
                                                    <label for="{{$warehouse->shortname}}" class="btn btn-outline-primary warehouse {{request()->warehouse == $warehouse->idwarehouse ? 'active' : ''}} {{ $index == count($warehouses) -1 ? 'last' : 'row-tiem' }}">
                                                    <input type="radio" name="warehouse" id="{{$warehouse->shortname}}" autocomplete="off" value="{{$warehouse->idwarehouse}}" {{ request()->warehouse == $warehouse->idwarehouse ? 'checked' : '' }} onchange="this.form.submit()">{{$warehouse->shortname}}
                                                    </label>
                                                    @endforeach
                                                    <?php $searchMethods = ['idpayment'=>'Orders to pay','carriername'=>'Orders to ship','printedshippingok'=>'Print orders','registeredtosolddayok'=>'Orders for platforms'] ?>
                                                    @foreach($searchMethods as $k=>$method)
                                                    <label for="option_{{$method}}" class="btn btn-outline-primary {{ request()->search == $k ? 'active' : '' }}">
                                                    <input type="radio" name="search" id="option_{{$method}}" autocomplete="off" value="{{$k}}" {{ request()->search == $k ? 'checked' : '' }} onchange="this.form.submit()">{{$method}}
                                                    </label>
                                                    @endforeach
                                                </div>
                                                @if(request()->is_search == 1)
                                                <a href="orderView" class="btn btn-warning">Reset</a>
                                                @endif
                                            </form>
                                        </div>
                                        <div class="col-sm-6 col-lg-6 col-md-6">
                                            <form class="form-inline">
                                                <div class="form-group">
                                                    <label for="">Date</label>
                                                    <input type="hidden" name="is_search" value="1" />
                                                    <select name="datee" id="getrec" onchange="setSearchOption()" class="form-control w-100">
                                                        <option value="" {{ request()->datee=='' ? 'selected' : ''  }}>Select Date Order</option>
                                                        <option value="0" {{ request()->datee=='0' ? 'selected' : ''  }}>Today</option>
                                                        <option value="1" {{ request()->datee=='1' ? 'selected' : ''  }}>Yesterday</option>
                                                        <option value="7" {{ request()->datee== '7' ? 'selected' : ''  }}>Last 7 days</option>
                                                        <option value="30" {{ request()->datee=='30' ? 'selected' : ''  }}>Last 30 days</option>
                                                        <option value="31" {{ request()->datee=='31' ? 'selected' : ''  }}>Personalize range</option>
                                                    </select>
                                                </div>
                                                <div class="form-group ml-2" id="fromDateDiv" style="display: {{ !request()->filled('fromDate') ? 'none' : 'block'  }}">
                                                    <label for="" class="w-100">From Date</label>
                                                    <input type="date" class="form-control" value="{{ request()->fromDate }}" name="fromDate" id="fromDate">
                                                </div>
                                                <div class="form-group ml-2"  id="toDateDiv" style="display:{{ !request()->filled('toDate') ? 'none' : 'block'  }}">
                                                    <label for= class="w-100">To Date</label>
                                                    <input type="date" class="form-control" value="{{ request()->toDate }}" name="toDate" id="toDate">
                                                </div>
                                                <div class="form-group ml-2">
                                                    <label class="w-100">Keyword</label>
                                                    <input type="text" class="form-control" name="keyword" id="keyword" value="{{ request()->keyword }}">
                                                </div>
                                                <div class="form-group ml-2">
                                                    <label for="" class="w-100">&nbsp;</label>
                                                    <!--onclick="searchOrder()" -->
                                                    <input type="submit" class="btn btn-primary ml-2" value="Search" name="search">
                                                    @if(request()->is_search == 1)
                                                    <a href="orderView" class="btn btn-warning">Reset</a>
                                                    @endif
                                                </div>
                                                <div class="form-group ml-2">
                                                    <label for="" class="w-100">&nbsp;</label>
                                                    @if(!Session::has("orderViewType"))
                                                    <a href="orderView?viewType=expert" class="btn btn-info">Expert section</a>
                                                    @else
                                                    <a href="orderView?viewType=normal" class="btn btn-info">Normal section</a>
                                                    @endif
                                                </div>
                                                <div class="form-group ml-2">
                                                    <label for="" class="w-100">&nbsp;</label>
                                                    <a href="orderAddView" class="btn btn-info">Add Order</a>
                                                </div>
                                              
                                            </form>
                                        </div>
                                      
                                    </div>
                                    
                                    <div class="row mb-4 mt-4">
                                        <div class="mr-3 ml-4">
                                            <input type="button" class="btn btn-info d-block w-100 send_courier" value="Send to Courier" onclick="createCSV()" name="courier">
                                        </div>
                                        <div class="mr-3">
                                            <input type="button" onclick="platforminfo()" class="btn btn-info d-block w-100 send_platform" value="Send to Platforms" name="platform">
                                        </div>
                                        <div class="mr-3">
                                            <button type="button" class="btn btn-info d-block w-100" data-toggle="modal" data-target="#documentModal">Print Documents</button>
                                        </div>
                                        <div class="mr-3">
                                            <button  class="btn btn-info d-block w-100">
                                            <a onclick="getAmazonOrders()"> Get Amazon Orders</a>
                                            </button>
                                        </div>
                                        <div class="mr-3">
                                            <button class="btn btn-info d-block w-100" onclick="getEbayOrders()">
                                            Get Ebay Orders
                                            </button>
                                        </div>
                                        <div class="mr-3">
                                            <button  class="btn btn-info d-block w-100">
                                            <a onclick="getWoocommerceOrders()">Get Woo Orders</a>
                                            </button>
                                        </div>
                                        <div class="mr-3">
                                            <button  class="btn btn-info d-block w-100">
                                            <a onclick="getCdiscountOrders()">Get cDiscount Orders</a>
                                            </button>
                                        </div>
                                        <div class="mr-3">
                                           
                                            <form action="addAmazonAddress" method="POST" enctype="multipart/form-data">
                                                {{csrf_field()}}
                                                <span class="btn btn-info btn-file p-0" style="width: 100%; padding: 7px 12px !important;">
                                                Integrate Amazon orders <input type="file" name="fileToUpload" class="custom-file-inputs fileToUpload d-block w-100">
                                                </span>
                                                <input type="submit" value="upload" name="submitcsvf" class="btnn" style="display: none;">
                                            </form>
                                        </div>
                                        <div class="mr-3">
                                            <input type="button" class="btn btn-info d-block w-100" value="Show Amazon Items to Integrate" id="integrate" name="integrate" >
                                        </div>
                                         <div class="mr-3">
                                            <a href="orderView?show_deleted_orders=1&is_search=1" class="btn btn-warning">Show deleted orders</a>
                                        </div>
                                        <div class="mr-3">
                                            <a href="{{ route('order.documents') }}" class="btn btn-success">Get files</a>
                                        </div>
                                    </div>
                                    

<div class="panel panel-default panel_ebay_order_list">
    <div class="panel-heading">
        <h6 class="panel-title">Orders</h6>
        <div>{{ $orders->links() }}</div>
    </div>
    <div class="table-responsive ebay_order_tablelist">
        <table class="table table-bordered table-check amz-ord-table-set">
            <thead>
                <tr class="head_ct">
                    <th align="center" width="10%">
                        <a sort_direction="asc" class="sorting_link sorting" sort_by="order_channel_id" href="javascript:;">
                            <span class="titl1">Date/platform/Id</span>
                        </a>
                    </th>
                    <th align="center" width="10%">
                        <a sort_direction="asc" class="sorting_link sorting" sort_by="order_channel_id" href="javascript:;">
                            <span class="titl1">Company/Channel</span>
                        </a>
                    </th>
                    <th align="center" style="width:15%">
                        <a sort_direction="asc" class="sorting_link sorting" sort_by="order_date" href="javascript:;">
                            <span class="titl2">Product Info</span>
                        </a>
                    </th>
                    <th align="right" width="10%">
                        <a sort_direction="asc" class="sorting_link sorting" sort_by="order_total" href="javascript:;">
                            <span class="titl3">Sum</span>
                        </a>
                    </th>
                    <th style="width:20%">
                        <a sort_direction="asc" class="sorting_link sorting" sort_by="order_status" href="javascript:;">
                            <span>Customer Info</span>
                        </a>
                    </th>
                    <th align="center" width="10%">
                        <a sort_direction="asc" class="sorting_link sorting" sort_by="manual_tracking_number" href="javascript:;">
                            <span>Warehouse</span>
                        </a>
                    </th>                    
                    <th align="center" width="10%">
                        <a sort_direction="asc" class="sorting_link sorting" sort_by="manual_tracking_number" href="javascript:;">
                            <span>Shipping info</span>
                        </a>
                    </th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody class="supplier_tbody">
                <?php 
                $sps = 0;
                foreach ($orders as $key => $row) { //dd($row->channel);
                ?>
                <tr class="ebay_order_4" style="">
                    <td>
                        <table class="table">
                            <tr><td class="td-field">{{$row->datee }}</td></tr>
                            <tr><td class="td-field">{{$row->platformname }}</td></tr>
                            <tr><td class="td-field">{{$row->referenceorder }}</td></tr>
                        </table>
                    </td>
                    <td>
                        <table class="table">
                        <tr>
                          <td class="td-field">
                            <span class="field-value">{{$row->company ? $row->company->shortname : ''}}</span>
                            <div class="field-edit">
                                <select name="idcompany" class="form-control" data-id="{{$row->idorder}}" data-field="idcompany">
                                <option value="">Select</option>
                                @foreach ($companies as $key => $comp)
                                <option value="{{$comp->idcompany}}"
                                @if ($comp->idcompany==$row->idcompany)
                                selected=""
                                @endif
                                >
                                {{$comp->shortname}}
                                </option>
                                @endforeach
                                </select>
                            </div>
                        </td>
                        </tr>
                        <tr>
                            <td class="td-field">
                                <span class="field-value">{{$row->channel ? $row->channel->shortname : ''}}</span>
                                <div class="field-edit">
                                    <select name="referencechannel" class="form-control" data-id="{{$row->idorder}}" data-field="referencechannel">
                                    <option value="">Select</option>
                                    @foreach ($channels as $key => $channel)
                                    <option value="{{$channel->idchannel}}"
                                    @if($channel->idchannel==$row->referencechannel)
                                    selected=""
                                    @endif
                                    >
                                    {{$channel->shortname}}
                                    </option>
                                    @endforeach
                                    </select>
                                </div>
                            </td>
                        </tr>
                    </table>
                    </td>
                  
                    <td>
                        
                        <table class="table">
                            <thead>
                                <tr>
                                    <th style="width:5%;">Quantity</th>
                                    <th style="width:20%;">SKU</th>
                                    <th style="width:15%;">Product</th>
                                    <th style="width:25%;">EAN</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr id="row_{{ $row->idorder }}">
                                    <td style="width:5%;" class="td-field">
                                        <span class="field-value">{{ $row->quantity ? $row->quantity : '0' }}</span>
                                        <div class="field-edit">
                                            <input type="text" class="form-control" placeholder="Quantity" value="{{$row->quantity }}" data-field="quantity" data-id="{{$row->idorder}}">
                                        </div>
                                    </td>
                                    <td style="width:20%; text-align:cengter;" class="td-field1 get_product_list">
                                    <span class="field-value">{{ $row->product ? $row->product->sku : 'N/A' }}</span>
                                        <div class="field-edit">
                                            <input type="text" class="form-control update_item" placeholder="sku" data-action="sku_update" value="{{$row->product ? $row->product->sku :''}}"  data-id="{{$row->idorder}}">
                                        </div>
                                    </td>
                                    <td style="width:15%; text-align:cengter;">{{ $row->product ? $row->product->namelong : 'N/A' }}</td>
                                    <td style="width:25%; text-align:cengter;">{{  $row->product ? $row->product->ean : 'N/A' }}</td>
                                </tr>
                                <?php $multi_orders = \App\Models\OrderItem::where('multiorder',$row->referenceorder)->get(); ?>
                                @foreach($multi_orders as $multi_order)
                                <?php //dd($multi_order); ?>
                                <tr id="row_{{ $multi_order->idorder }}">
                                    <td style="width:5%;" class="td-field">
                                        <span class="field-value">{{ $multi_order->quantity ? $multi_order->quantity : '0' }}</span>
                                        <div class="field-edit">
                                            <input type="text" class="form-control" placeholder="Quantity" value="{{$multi_order->quantity }}" data-field="quantity" data-id="{{$multi_order->idorder}}">
                                        </div>
                                    </td>
                                    <td style="width:20%; text-align:cengter;" class="td-field1 get_product_list">
                                    <span class="field-value">{{ $multi_order->product ? $multi_order->product->sku : 'N/A' }}</span>
                                        <div class="field-edit">
                                            <input type="text" class="form-control update_item" placeholder="sku" data-action="sku_update" value="{{$multi_order->product->sku}}" data-id="{{$multi_order->idorder}}">
                                        </div>
                                    </td>
                                    <td style="width:15%; text-align:cengter;">{{ $multi_order->product ? $multi_order->product->namelong : 'N/A' }}</td>
                                    <td style="width:25%; text-align:cengter;">{{ $multi_order->product ? $multi_order->product->ean : 'N/A' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </td>
                    <td>
                        <table class="table">
                          <tr>
                            <td class="td-field">
                               <span class="field-value">{{$row->sum}}EUR</span>
                                    <div class="field-edit">
                                        <input type="text" name="sum" class="form-control" placeholder="sum" value="{{$row->sum}}" data-id="{{$row->idorder}}" data-field="sum">
                                    </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="td-field">
                                <span class="field-value">{{$row->idpayment}}</span>
                                <div class="field-edit">
                                    <select name="idpayment" class="form-control" data-id="{{$row->idorder}}" data-field="idpayment">
                                    <option value="">Select</option>
                                    @foreach ($payments as $key => $payment)
                                    <option value="{{$payment->shortname}}"
                                    @if ($payment->shortname==$row->idpayment)
                                    selected=""
                                    @endif
                                    >
                                    {{$payment->shortname}}
                                    </option>
                                    @endforeach
                                    </select>
                                </div>
                            </td>
                        </tr>
                        </table>
                    </td>
                   
                    <td>
                        <table class="table">
                            <tr>
                                <td  class="td-field">
                                    <label class="lb-h">Customer:</label>
                                    <span class="field-value">{{$row->customer}}</span>
                                    <div class="field-edit">
                                        <input type="text" name="customer" class="form-control" placeholder="customer" value="{{$row->customer}}" data-id="{{$row->idorder}}" data-field="customer">
                                    </div>
                                </td>
                                <td class="td-field">
                                    <label class="lb-h">Customerextra:</label>
                                    <span  class="field-value">{{$row->customerextra}}</span>
                                    <div class="field-edit">
                                        <input type="text" name="customerextra" class="form-control" placeholder="customerextra" value="{{$row->customerextra}}" data-id="{{$row->idorder}}" data-field="customerextra">
                                    </div>
                                </td>
                               
                            </tr>
                            <tr>
                                 <td  class="td-field">
                                    <label class="lb-h">Address1:</label>
                                    <span  class="field-value">{{$row->address1}}</span>
                                    <div class="field-edit">
                                        <input type="text" name="address1" class="form-control" placeholder="address1" value="{{$row->address1}}" data-id="{{$row->idorder}}" data-field="address1">
                                    </div>
                                </td>   
                                <td class="td-field">
                                    <label class="lb-h">Address2:</label>
                                    <span  class="field-value">{{$row->address2}}</span>
                                    <div class="field-edit">
                                        <input type="text" name="address2" class="form-control" placeholder="address2" value="{{$row->address2}}" data-id="{{$row->idorder}}" data-field="address2">
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="td-field">
                                    <label class="lb-h">Plz:</label>
                                   <span  class="field-value">{{$row->plz}}</span>
                                    <div class="field-edit">
                                        <input type="number" name="plz" class="form-control" placeholder="plz"  value="{{$row->plz}}" data-id="{{$row->idorder}}" data-field="plz">
                                    </div>
                                </td>
                                <td  class="td-field">
                                    <label class="lb-h">City:</label>
                                    <span  class="field-value">{{$row->city}}</span>
                                    <div class="field-edit">
                                        <input type="text" name="city" class="form-control" placeholder="city" value="{{$row->city}}" data-id="{{$row->idorder}}" data-field="city">
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="td-field">
                                    <label class="lb-h">Region:</label>
                                    <span  class="field-value">{{$row->region}}</span>
                                    <div class="field-edit">
                                        <input type="text" name="region" class="form-control" placeholder="Region" value="{{$row->region}}" data-id="{{$row->idorder}}" data-field="region">
                                    </div>
                                </td>
                                <td class="td-field">
                                    <label class="lb-h">Country:</label>
                                    <span  class="field-value">{{$row->country}}</span>
                                    <div class="field-edit">
                                        <select name="country" class="form-control" data-id="{{$row->idorder}}" data-field="country">
                                        <option value="">Select</option>
                                        @foreach ($countries as $key => $country)
                                        <option value="{{$country->shortname}}"
                                        @if ($country->shortname==$row->currency)
                                        selected=""
                                        @endif
                                        >
                                        {{$country->shortname}}
                                        </option>
                                        @endforeach
                                        </select>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="td-field">
                                    <label class="lb-h">Phone:</label>
                                    <span  class="field-value">{{$row->telefon}}</span>
                                    <div class="field-edit">
                                        <input type="text" name="telefon" placeholder="Phone" class="form-control" value="{{$row->telefon}}" data-id="{{$row->idorder}}" data-field="telefon">
                                    </div>
                                </td>
                                <td class="td-field">
                                    <label class="lb-h">Fax:</label>
                                    <span  class="field-value">{{$row->fax}}</span>
                                    <div class="field-edit">
                                        <input type="text" name="fax" class="form-control"  placeholder="Fax"  value="{{$row->fax}}" data-id="{{$row->idorder}}" data-field="fax">
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="td-field" colspan="3">
                                    <label class="lb-h">Email:</label>
                                   <span  class="field-value">{{$row->email}}</span>
                                    <div class="field-edit">
                                        <input type="email" name="email" class="form-control" value="{{$row->email}}" data-id="{{$row->idorder}}" data-field="email">
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="td-field" colspan="3">
                                    <label class="lb-h">Delivery Instructions:</label>
                                    <span  class="field-value">{{$row->delivery_Instructions}}</span>
                                    <div class="field-edit">
                                        <input type="text" name="delivery_Instructions"  placeholder="Delivery instructions" class="form-control" value="{{$row->delivery_Instructions}}" data-id="{{$row->idorder}}" data-field="delivery_Instructions">
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td class="td-field">
                        <span  class="field-value">{{$row->warehouse->shortname}}</span>
                        <div class="field-edit">
                            <select name="idwarehouse" class="form-control w-100" data-id="{{$row->idorder}}" data-field="idwarehouse">
                            <option value="">Select</option>
                            @foreach ($warehouses as $key => $ware)
                            <option value="{{$ware->idwarehouse}}"
                            @if ($ware->idwarehouse==$row->idwarehouse)
                            selected=""
                            @endif
                            >
                            {{$ware->shortname}}
                            </option>
                            @endforeach
                            </select>
                        </div>
                    </td>
                    <td>
                      <table class="table">
                          <tr>
                            <td  class="td-field">
                                <span  class="field-value">{{$row->ship_service_level}}</span>
                                <div class="field-edit">
                                    <input type="email" name="ship_service_level" class="form-control" value="{{$row->ship_service_level}}" data-id="{{$row->idorder}}" data-field="ship_service_level">
                                </div>
                            </td>
                          </tr>
                          <tr>
                            <td  class="td-field">
                                <span class="field-value">{{$row->carriername}}</span>
                                <div class="field-edit">
                                    <select name="carriername" class="form-control w-100" data-id="{{$row->idorder}}" data-field="carriername">
                                    <option value="">Select</option>
                                    @foreach ($carriers as $key => $carrier)
                                    <option value="{{$carrier->shortname}}" @if ($carrier->shortname==$row->carriername) selected="" @endif >
                                    {{$carrier->shortname}}
                                    </option>
                                    @endforeach
                                    </select>
                                </div>
                            </td>
                          </tr>
                          <tr>
                            <td  class="td-field">
                                <span  class="field-value">{{$row->tracking}}</span>
                                <div class="field-edit">
                                    <input type="text" name="tracking" class="form-control" value="{{$row->tracking}}" data-id="{{$row->idorder}}" data-field="tracking">
                                </div>
                            </td>
                          </tr>
                          <tr>
                            <td  class="td-field">
                                <span  class="field-value">{{$row->groupshipping}}</span>
                                <div class="field-edit">
                                    <input type="text" name="groupshipping" class="form-control" value="{{$row->groupshipping}}" data-id="{{$row->idorder}}" data-field="groupshipping">
                                </div>
                            </td>
                          </tr>
                         </table>
                    </td>
                    <td>
                       <select name="delete" class="form-control delete" data-id="{{$row->idorder}}" style="width: 100%;">
                            <option value="">Select Option</option>
                            <option value="delete_order">Delete Order</option>
                            <option value="set_as_done">Set as Done</option>
                            <option value="set_as_not_done">Set as not Done</option>
                            <option value="send_to_platform">Send to platform</option>
                            <option value="create_invoice">Create invoice</option>
                        </select>
                        <a class="btn btn-danger getInvoiceForm1" target="_blank" href="{{ route('orderInvoiceCreate',['id'=>$row->idorder]) }}" data-id="{{$row->idorder}}" style="width: 100%; word-wrap: break-word; margin-top: 10px;">Edit invoice</a>
                    </td>
                </tr>
                <?php $sps++; }?>
            </tbody>
        </table>
    </div>
    <div>{{ $orders->links() }}</div>
</div>
<div class="modal fade" id="modalforeditinvoice" role="dialog" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Invoice form</h4>
            </div>
            
            <div id="formcontent"></div>
            
        </div>
    </div>
</div>
<style>
.panel-default > .panel-heading, .panel-default, .small-padding-table, .panel.padding-20, .product-box.product_pull_out {
    box-shadow: 0 0 3px rgba(0, 0, 0, 0.1);
}
.panel {
    border-radius: 2px;
}
.panel-default {
    border-color: #ddd;
}
.panel {
    margin-bottom: 20px;
    background-color: #fff;
    border: 1px solid transparent;
    border-radius: 4px;
    -webkit-box-shadow: 0 1px 1px rgba(0,0,0,.05);
    box-shadow: 0 1px 1px rgba(0,0,0,.05);
}
.panel-default>.panel-heading {
    background-color: #f3f3f3;
}
.panel-default>.panel-heading {
    color: #333;
    background-color: #f5f5f5;
    border-color: #ddd;
}
.panel-heading {
    border-top-left-radius: 2px;
    border-top-right-radius: 2px;
    padding: 0;
    position: relative;
}
.panel-heading {
    border-bottom: 1px solid transparent;
}
.panel-title {
    float: left;
    display: block;
    font-size: 14px;
    padding: 11px 12px 12px;
}
.h1, .h2, .h3, .h4, .h5, .h6, h1, h2, h3, h4, h5, h6 {
    font-weight: 600;
    line-height: 1.42857143;
    margin-top: 0;
}
.header-bar-stats .page-stats li:after, .page-header:after, .panel-heading:after, .panel-toolbar:after {
    content: "";
    display: table;
    clear: both;
}
.panel .table-responsive:last-child>.table:last-child, .panel .table:last-child {
    border-bottom-right-radius: 0;
    border-bottom-left-radius: 0;
}
.panel .table-responsive:first-child>.table:first-child, .panel .table:first-child {
    border-top-left-radius: 0;
    border-top-right-radius: 0;
}
.panel .table-bordered, .panel .table-responsive>.table-bordered {
    border: 0;
}
.table-responsive table {
    background: #fff;
    font-family: 'Open Sans', sans-serif !important;
}
.panel>.table, .panel>.table-responsive>.table {
    margin-bottom: 0;
}
.table-check {
    font-family: arial;
    font-size: 11px;
}
.table-responsive thead {
    z-index: 9999;
}
.table-responsive thead {
    background: #546672 none repeat scroll 0 0;
    left: 0;
    right: 0;
    top: 310px;
}
.supplier_tbody {
    background: #ffffff;
}
tbody {
    color: #494949;
}
.wrap_plus.table-image-box img.product-img {
    max-height: 160px;
    max-width: 150px;
    width: auto;
}
.table-controls {
    white-space: nowrap;
}
.table-controls {
    text-align: center;
}
.table-controls .btn-xs.btn-icon {
    margin: 3px 0 0 !important;
    padding: 3px !important;
    width: 25px !important;
}
.supplier_tbody td a {
    color: #333;
}
.btn {
    -moz-user-select: none;
    border-radius: 0;
    box-shadow: 0 1px 1px #bfbfbf;
    font-size: 12px;
    font-weight: 600;
    padding: 7px 16px 8px;
}
.table-responsive table thead tr a span, table th, table th span, table th label {
    color: #ffffff;
}
.table > thead > tr > th a, .table > thead > tr > th span {
    white-space: nowrap;
}
.radio label, .checkbox label {
    display: inline;
    font-weight: 400;
    cursor: pointer;
}

.table-bordered>tbody>tr>td, .table-bordered>tbody>tr>th, .table-bordered>tfoot>tr>td, .table-bordered>tfoot>tr>th, .table-bordered>thead>tr>td, .table-bordered>thead>tr>th {
    border: 1px solid #e4e7ea;
}
.table>tbody>tr>td, .table>tbody>tr>th, .table>tfoot>tr>td, .table>tfoot>tr>th, .table>thead>tr>td, .table>thead>tr>th {
    /*vertical-align: middle;*/
    padding: 10px 12px;
}
.buyer_name_cls {
    border-top: 1px solid #dedede;
    margin: 10px 0 6px;
    border-top: 1px solid #dedede;
}
.buyer_name_cls a.table-td-link {
    display: inline-block;
    margin: 0 0 5px;
}
.table-td-link {
    color: #750a00 !important;
    opacity: 0.7;
}
.checkbox input[type=checkbox], .checkbox-inline input[type=checkbox], .radio input[type=radio], .radio-inline input[type=radio] {
    margin-left: -20px;
    margin-right: 8px;
}
input[type=checkbox], input[type=radio] {
    margin: 3px 0 0;
}
.ebay_order_tablelist .checkbox {
    left: 0 !important;
    width: 13px !important;
}
.table > thead > tr > th .sorting {
    width: 100%;
    text-align: center;
    display: inline-block;
    padding: 0;
}
.supplier_tbody tr td {
    font-size: 13px;
}
.checkbox {
    left: 5px;
    position: relative;
}
.checkbox, .radio {
    padding-left: 0;
}
.table-responsive.ebay_order_tablelist table tr td {
    padding: 2px !important;
}
.ebay_order_tablelist table tr td:first-child {
    text-align: center;
    /*width: 20px;*/
}
.table-bordered>tbody>tr>td, .table-bordered>tbody>tr>th, .table-bordered>tfoot>tr>td, .table-bordered>tfoot>tr>th, .table-bordered>thead>tr>td, .table-bordered>thead>tr>th {
    border: 1px solid #e4e7ea;
}
[type=checkbox]+label:before, [type=checkbox]:not(.filled-in)+label:after{
    display: none;
}

#product-table thead th {
    color: #000 !important;
}
.modal-backdrop.fade {
    opacity: .5 !important;
}

.modal-header {
    display: block !important;
}
#product-table_filter input {
    border: 1px solid #ccc;
    padding: 6px 12px;
    border-radius: 4px;   
    background-image: none;
}



.panel>.table-responsive {
    margin-bottom: 0;
    border: 0;
    height: 70vh;
}
.head_ct{
    background: #546672 none repeat scroll 0 0;
    position: sticky;
    top: 0;
    z-index: 10;
}
.modal-lg {
    max-width: 1200px !important;
}
</style>
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal fade" id="documentModal" role="dialog" tabindex="-1">
                        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title">Select Warehouse</h4>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label for="">Select Warehouse</label>
                                        <select name="idwarehouse" id="idwarehouse" class="form-control">
                                            <option value="">---Select Option---</option>
                                            @foreach($modalWares as $res)
                                            <option value="<?php echo base64_encode($res->idwarehouse); ?>">{{$res->shortname}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default closeprintdoc" data-dismiss="modal">Close</button>
                                    <button type="button" class="btn btn-success" id="printDocument">Submit</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal fade" id="productListModal" role="dialog" tabindex="-1">
                        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title">Product list</h4>
                                    <input type="hidden" value="" id="ordrrId" />
                                </div>
                                <div class="modal-body">
                                   <table id="product-table" class="table table-striped table-bordered" style="width: 100%;">
                                        <thead>
                                            <tr>
                                                <th>Select</th>
                                                <th data-colmun="modelcode" class="sort_head">Model Number</th>
                                                <th data-colmun="nameshort" class="sort_head" style="width:10%">Article</th>
                                                <th data-colmun="subcat" class="sort_head">Category</th>
                                                <th data-colmun="active" class="sort_head">Sku</th>
                                                <th data-colmun="virtualkit" class="sort_head">Kit (yes/no)</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default closeprintdoc" data-dismiss="modal">Close</button>
                                    <button type="button" class="btn btn-success" id="printDocument">Submit</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>
                <footer class="footer">
                    © 2021 Semplifat powered by Confidence Europe GmbH
                </footer>
            </div>
        </div>
        </div>
        <script>
            $(document).ready(function() {
                
                var t = $('#product-table').DataTable({
                    processing: true,
                    serverSide: true,
                    pageLength: 10,
                    dom: 'lpftrip',
                    "order": [[ 1, "asc" ]],
                    "lengthMenu": [[10, 20, 30], [10, 20, 30]],
                    "language": { processing: '<i class="fa fa-spinner fa-spin fa-4x fa-fw"></i><span class="sr-only">Loading...</span> '},
                    ajax: "{{ route('ajax.productlist') }}",
                    columns: [
                        { data: 'sort', name: 'sort' },
                        { data: 'modelcode', name: 'modelcode' },
                        { data: 'nameshort', name: 'nameshort' },
                        { data: 'subcat',    name: 'subcat' },
                        { data: 'sku',    name: 'sku' },
                        { data: 'virtualkit', name: 'virtualkit' },
                    ]
                });
                
                $(document).on("click",".get_product_list",function(event) { 
                    var ordrid  = $(this).find('input').data('id');
                    $("#ordrrId").val(ordrid);
                    $("#productListModal").modal('show');
                });
                
                $(document).on("click",".select_product",function(event) { 
                    var sku = $(this).val();
                    var ordrid = $("#ordrrId").val();
                    $.ajax({
                        url    : 'orderUpdate',
                        data   : {
                            id:ordrid ,
                            value:sku ,
                            action: 'sku_update'
                        },
                        method : 'get',
                        success: function(result) {
                            if(result.success){
                                $("#row_"+ordrid).html(result.data);
                            }
                        }
            
                    });
                    
                    $("#productListModal").modal('hide');
                });
                
                 $(document).on("click",".getInvoiceForm",function(event) { 
                    var id = $(this).data('id');
                    var that = $(this);
                    $.ajax({
                        url    : 'getInvoiceForm',
                        data   : {
                            id:id,
                        },
                        beforeSend:function(){
                          that.text('Loading....')    
                        },
                        method : 'get',
                        success: function(result) {
                            if(result.success){
                                $("#formcontent").html(result.html);
                                $("#modalforeditinvoice").modal('show');
                            }
                        },
                        complete:function(){
                            that.text('Edit invoice')    
                        }
            
                    });
                    
                    
                }); 
               
                $(document).on("click",".td-field,.dtr-data",function(event) {
            
                    $(".field-value").show();
            
                    $('.field-edit').hide();
            
                    $(this).find(".field-value").hide();
            
                    $(this).find('.field-edit').show();
            
                });
            
            
                $(document).on('change', '.field-edit .form-control', function(event) {
                    var that  = $(this);
            
                    $('.field-edit').hide();
            
                    var label=$(this).parents(".field-edit").siblings('.field-value');
            
                    if($(this)[0].nodeName=='SELECT'){
            
                        label.html($(this).find(":selected").text());
            
                    } else {
            
                        label.html($(this).val());
            
                    }
            
                    label.show();
            
                    var data={id:$(this).data('id'),field:$(this).data('field'),action:$(this).data('action'),value:$(this).val()};
            
            
            
                    console.log(data);
            
                    $.ajax({
            
                        url    : 'orderUpdate',
            
                        data   : data,
            
                        method : 'get',
            
                        success: function(result) {
                            if(that.data('action') == 'sku_update'){
                                if(result.success){
                                    that.closest('tr').html(result.data);
                                }
                            }
                        }
            
                    });
            
                });
            
                
            
                // FOR CATEGORY
            
                $(document).on("change",".subcat",function(event) {
            
                    var data={id:$(this).data('id'),field:$(this).data('field'),value:$(this).val()};
            
                    $.ajax({
            
                        url: "orderUpdate",
            
                        data:data,
            
                        method:"get",
            
                        success: function(result) {
            
                            
            
                        }
            
                    });
            
                });
            
                
            
                $("#printDocument").on('click', function() {
                    $("#documentModal").modal('hide');
                    var idwarehouse      = $("#idwarehouse").val();
            
                    window.location.href = 'printDocuments?idwarehouse='+idwarehouse;
            
                    $("#idwarehouse").val("");
            
                    jQuery('.closeprintdoc').trigger("click");
            
                });
            
            
            
                $(".fileToUpload").on('change', function() {
            
                    jQuery('.btnn').trigger("click");
            
                });
            
                
            
                $(".delete").on('change', function() {
            
                    var deleid  = $(this).data("id");
            
                    var type    = $(this).val();
            
                    if(type=="delete_order"){
            
                        if (confirm('Are you sure to Delete this Order?')) {
            
                            window.location.href = 'orderDelete?del='+deleid+"&type="+type;
            
                            return true;
            
                        } 
            
                    }else if(type=="set_as_done"){
            
                        if (confirm('Are you sure to change status set as done?')) {
            
                            window.location.href = 'orderDelete?del='+deleid+"&type="+type;
            
                            return true;
            
                        }
            
                    }else if(type=="set_as_not_done"){
            
                        if (confirm('Are you sure to change status set as not done?')) {
            
                            window.location.href = 'orderDelete?del='+deleid+"&type="+type;
            
                            return true;
            
                        }
            
                    }else if(type=="create_invoice"){
            
                        if (confirm('Are you sure to create invoice?')) {
            
                            window.location.href = 'createOrderInvoice?del='+deleid+"&type="+type;
            
                            return true;
            
                        }
            
                    }else if(type=="send_to_platform"){
            
                        if (confirm('Are you sure to change status send to platform?')) {
            
                            // $(".preloader").fadeIn();
            
                            // var data = {
            
                            //     orderId     : deleid
            
                            // }
            
                            // $.ajax({
            
                            //     type: "GET",
            
                            //     url: "api/updateOrder.php",
            
                            //     data: data,
            
                            //     success : function(data) {
            
                            //         $(".preloader").fadeOut();
            
                            //         if(data=="Success") {
            
                            //             alert("Successfuly sent!");
            
                            //         } else {
            
                            //             alert(data);
            
                            //         }
            
                            //     }
            
                            // });
            
                            window.location.href = 'api/updateOrder.php?orderId='+deleid;
            
                            return true;
            
                        }
            
                    }
            
                });
            
            }); 
            
            
            
            function getAmazonOrders() {
            
                window.open('api/orders.php', '_blank');
            
            }
            
            
            
            function getEbayOrders() {
            
                window.open('api/ebayOrders.php', '_blank');
            
            }
            
            
            
            function getWoocommerceOrders() {
            
                window.open('getWoocommerceOrders', '_blank');
            
            }
            
            
            
            function getCdiscountOrders() {
            
                window.open('api/cdiscountorder.php', '_blank');
            
            }
            
            
            
            function createCSV() {
            
                window.location.href = 'createCSV';
            
            }
            
            
            
            function setSearchOption() {
            
                var datee               = document.getElementById("getrec").value;
            console.log('datee',datee);
                //var datcarriernameee    = document.getElementById("carriername").value;
            
                if(datee == 31) {
            
                    document.getElementById("fromDateDiv").style.display = "block";
            
                    document.getElementById("toDateDiv").style.display = "block";
            
                } else {
            
                    document.getElementById("fromDateDiv").style.display = "none";
            
                    document.getElementById("fromDate").value = "";
            
                    document.getElementById("toDateDiv").style.display = "none";
            
                    document.getElementById("toDate").value = "";
            
                }
            
            }
            
            
            
            function searchOrder() {
                var datee       = document.getElementById("getrec").value;
                var keyword       = document.getElementById("keyword").value;
                var url         = 'orderView2';
                var fromDate    = document.getElementById("fromDate").value;
                var toDate      = document.getElementById("toDate").value;
                //if(datee == 31) {
                    // if(fromDate != "") {
                        url = url+'?datee='+datee+'&keyword='+keyword+'&fromDate='+fromDate+"&toDate="+toDate;
                        window.location.href = url;
                    // } else {
                    //     alert("Please select date range!")
                    // }
                /*} else {
                    if(datee != "") {
                        url = url+'?datee='+datee;
                        window.location.href = url;
                    } else {
                        alert("Please select date!")
                    }
                }*/
            }
            
            function deleteme(deleid) {
            
                if (confirm('Are you sure to Delete this Order?')) {
            
                    window.location.href = 'orderView?del='+deleid;
            
                    return true;
            
                } 
            
            }
            
            
            
            function platforminfo(){
            
                if (confirm('We are sending?')) {
            
                    window.location.href = 'sendToPlatform';
            
                    return true;
            
                } 
            
            }
            
            
            
            $(function(){
            
                $('#dele').click(function(){
            
                    if (confirm('Some message')) {
            
                        location.href = this.href;
            
                    } 
            
                });
            
            });
            
            
            
            $(function(){
            
                $('#search_text').keyup(function(){
            
                    var txt = $(this).val();
            
                    if(txt != '') {
            
                        $('#result').html('');
            
                        $.ajax({
            
                            type: "POST",
            
                            url: "fetch.php",
            
                            data: '&search_text=' + txt,
            
                            success : function(data) {
            
                                if(data == false) {
            
                                    alert("Invalid Arguments");
            
                                } else {
            
                                    $('#ssss').html(data); 
            
                                }
            
                            }
            
                        });
            
                    } else {
            
                        
            
                    }
            
                });
            
            });
            
            
            
            $(function() {
            
                $("#waitingpay").click(function(e) {
            
                    e.preventDefault();
            
                    var txt = $(this).val();
            
                    $.ajax({
            
                        type: "POST",
            
                        url: "fetchpayment.php",
            
                        data: '&getdata=' + txt,
            
                        success : function(data) {
            
                            $('#ssss').html(data); 
            
                        }
            
                    });
            
                });
            
            });
            
            
            
            $(function() {
            
                $("#integrate").click(function(e) {
            
                    url = 'orderView?integrate=1';
            
                    window.location.href = url;
            
                });
            
            });
            
        </script>
        <script src="assets/plugins/jquery/jquery.min.js"></script>
        <script src="assets/plugins/bootstrap/js/popper.min.js"></script>
        <script src="assets/plugins/bootstrap/js/bootstrap.min.js"></script>
        <script src="assets/js/jquery.slimscroll.js"></script>
        <script src="assets/js/waves.js"></script>
        <script src="assets/js/sidebarmenu.js"></script>
        <script src="assets/plugins/sticky-kit-master/dist/sticky-kit.min.js"></script>
        <script src="assets/plugins/sparkline/jquery.sparkline.min.js"></script>
        <script src="assets/plugins/sparkline/jquery.sparkline.min.js"></script>
        <script src="assets/plugins/bootstrap-switch/bootstrap-switch.min.js"></script>
        <script src="assets/js/datatables.min.js"></script>
        <script type="text/javascript">
            $(".bt-switch input[type='checkbox'], .bt-switch input[type='radio']").bootstrapSwitch();
            
            var radioswitch = function() {
            
                var bt = function() {
            
                    $(".radio-switch").on("switch-change", function() {
            
                        $(".radio-switch").bootstrapSwitch("toggleRadioState")
            
                    }), $(".radio-switch").on("switch-change", function() {
            
                        $(".radio-switch").bootstrapSwitch("toggleRadioStateAllowUncheck")
            
                    }), $(".radio-switch").on("switch-change", function() {
            
                        $(".radio-switch").bootstrapSwitch("toggleRadioStateAllowUncheck", !1)
            
                    })
            
                };
            
                return {
            
                    init: function() {
            
                        bt()
            
                    }
            
                }
            
            }();
            
            $(document).ready(function() {
            
                radioswitch.init()
            
            });
            
        </script>
        <script>
            $("#fileimg").on("change",function(e){
            
                var ext = this.value.match(/\.([^\.]+)$/)[1];
            
                switch(ext) {
            
                    case 'jpg':
            
                    case 'jpeg':
            
                        //alert('allowed');
            
                        break;
            
                    default:
            
                        alert('this file type is not allowed');
            
                        this.value='';
            
                }
            
            });
            
            $(document).ready(function() {
            
                $('#myTable').DataTable();
            
            });
            
            $(document).ready(function() {
            
                $('#my-examples').DataTable();
            
            });
            
            
            
            function readURL(input) {
            
                if (input.files && input.files[0]) {
            
                    var reader = new FileReader();
            
                    
            
                    reader.onload = function (e) {
            
                        $('#fileimg-tag').attr('src', e.target.result);
            
                    }
            
                    reader.readAsDataURL(input.files[0]);
            
                }
            
            }
            
            $("#fileimg").change(function(){
            
                readURL(this);
            
            });
            
            
            
            // open window on click
            
            $(".open").on("click", function(){
            
                $(".popup, .popup-content").addClass("active");
            
            });
            
            
            
            $(".close, .popup").on("click", function(){
            
                $(".popup, .popup-content").removeClass("active");
            
            });
            
            
            
            // show button on check
            
            $(document).ready(function() {
            
                var $submit = $("#file").hide(),
            
                    $cbs = $('input[name="prog"]').click(function() {
            
                        $submit.toggle( $cbs.is(":checked") );
            
                    });
            
            });
            
        </script>
        <script src="assets/js/custom.min.js"></script>
        <script src="assets/plugins/raphael/raphael-min.js"></script>
        <script src="assets/plugins/morrisjs/morris.min.js"></script>
        <script src="assets/plugins/styleswitcher/jQuery.style.switcher.js"></script>
        <script>
            function funccalldeletepopup(popupname,deletetype,deleteId,deleterowdiv) {
            
                $('#deletetypeId').val(deleteId);
            
                $('#deletetype').val(deletetype);
            
                $('#deletetypediv').val(deleterowdiv);
            
                $('#deletetypetoshow').html(deletetype);
            
                $('#'+popupname).modal('show');
            
            }
            
            
            
            function funcdeletefrompopup(popupname,redirecturl="") { 
            
                deletetypedivtouse  = $('#deletetypediv').val();
            
                deletetypeIdtouse   = $('#deletetypeId').val();
            
                deletetypetouse     = $('#deletetype').val();
            
                $("#"+deletetypedivtouse).hide();
            
                $("#"+popupname).modal('hide');
            
            
            
                var data = {
            
                    manufacturerId     : deletetypeIdtouse
            
                }
            
            
            
                $.ajax({
            
                    type: 'get',
            
                    url:  'manufacturerDelete',
            
                    data:  data,
            
                    success: function(data){
            
                        location.reload();
            
                    }        
            
                });
            
            }
            
        </script>
@endsection