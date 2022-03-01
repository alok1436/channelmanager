@extends('layouts.default')
@section('content')

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-9 col-lg-9 col-md-9" style="margin:auto">
                        <div style="width: 100%;">
                            <table class="" style="width: 100%;">
                                <tr>
                                    <td>
                                    @if($order->company && $order->company->linklogo)
                                        <img src="{{ url('assets/'.$order->company->linklogo) }}" style="width: 220px;" alt="">
                                    @endif
                                    </td>
                                    <td style="text-align: right;">
                                        <span style="color: green; font-size: 30px;"><b>{{$order->company->longname ?? '' }}</b></span><br>
                                        <span style="color: black; font-size: 15px;"><b>{{$order->company->street1 ?? '' }}</b></span><br>
                                        <span style="color: black; font-size: 15px;"><b>{{$order->company->plz ?? '' }} {{$order->company->city ?? '' }} ({{$order->company->province ?? '' }})</b></span><br>
                                        <span style="color: black; font-size: 15px;"><b>Tel: {{$order->company->phone ?? '' }}</b></span><br>
                                        <span style="color: black; font-size: 15px;"><b>Fax: {{$order->company->fax ?? '' }}</b></span><br>
                                        <span style="color: black; font-size: 15px;"><b>{{$order->company->email ?? '' }}</b></span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                
                        <div style="width: 100%; height: 10px; background: green;"></div>
                
                        <div style="margin-top: 50px;" class="row">
                            <div class="col-md-6">
                            <table class="table table-bordered" style="width: 100%;">
                                <tr>
                                    <td class="td-field">
                                         <span  class="field-value">{{$order->inv_customer}}</span>
                                        <div class="field-edit">
                                            <input type="text" name="inv_customer" class="form-control" placeholder="Customer" value="{{$order->inv_customer}}" data-id="{{$order->idorder}}" data-field="inv_customer">
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="td-field">
                                         <span  class="field-value">{{$order->inv_customerextra}}</span>
                                        <div class="field-edit">
                                            <input type="text" name="inv_customerextra" class="form-control" placeholder="Customer extra" value="{{$order->inv_customerextra}}" data-id="{{$order->idorder}}" data-field="inv_customerextra">
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="td-field">
                                         <span  class="field-value">{{$order->inv_address1}}</span>
                                        <div class="field-edit">
                                            <input type="text" name="inv_address1" class="form-control" placeholder="Address1" value="{{$order->inv_address1}}" data-id="{{$order->idorder}}" data-field="inv_address1">
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="td-field">
                                         <span  class="field-value">{{$order->inv_address2}}</span>
                                        <div class="field-edit">
                                            <input type="text" name="inv_address2" class="form-control" placeholder="Address2" value="{{$order->inv_address2}}" data-id="{{$order->idorder}}" data-field="inv_address2">
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <table class="table">
                                            <tr>
                                                <td class="td-field" style="width:30%">
                                                     <span  class="field-value">{{$order->plz1}}</span>
                                                    <div class="field-edit">
                                                        <input type="text" name="plz1" class="form-control" placeholder="plz" value="{{$order->plz1}}" data-id="{{$order->idorder}}" data-field="plz1">
                                                    </div>
                                                </td>
                                                <td class="td-field" style="width:30%">
                                                     <span  class="field-value">{{$order->city1}}</span>
                                                    <div class="field-edit">
                                                        <input type="text" name="city1" class="form-control" placeholder="city" value="{{$order->city1}}" data-id="{{$order->idorder}}" data-field="city1">
                                                    </div>
                                                </td>
                                                <td class="td-field" style="width:30%">
                                                     <span  class="field-value">{{$order->region1}}</span>
                                                    <div class="field-edit">
                                                        <input type="text" name="region1" class="form-control" placeholder="Address2" value="{{$order->region1}}" data-id="{{$order->idorder}}" data-field="region1">
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="td-field">
                                    <label class="lb-h">Country:</label>
                                    <span  class="field-value">{{$order->country1}}</span>
                                    <div class="field-edit">
                                        <select name="country" class="form-control" data-id="{{$order->idorder}}" data-field="country1">
                                        @foreach ($countries as $key => $country)
                                        <option value="{{$country->shortname}}"
                                        @if ($country->shortname==$order->country1)
                                        selected=""
                                        @endif
                                        >
                                        {{$country->longname}}
                                        </option>
                                        @endforeach
                                        </select>
                                    </div>
                                </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-2"></div>
                        <div class="col-md-4">
                            <table style="width: 100%;">
                                <tr>
                                    <td class="td-field">
                                         <div  class="field-value"><div class="row"><div class="col-md-6 text-right">Date:</div><div class="col-md-6 text-left">{{$order->datee}}</div></div></div>
                                        <div class="field-edit">
                                            <input type="text" name="datee" class="form-control" placeholder="Region" value="{{$order->datee}}" data-id="{{$order->idorder}}" data-field="datee">
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="td-field">
                                        <div  class="field-value"><div class="row"><div class="col-md-6 text-right">Num Invoice:</div><div class="col-md-6 text-left">{{$order->num_invoice}}</div></div></div>
                                        <div class="field-edit">
                                            <input type="text" name="num_invoice" class="form-control" placeholder="num invoice" value="{{$order->num_invoice}}" data-id="{{$order->idorder}}" data-field="num_invoice">
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="td-field">
                                        <div  class="field-value"><div class="row"><div class="col-md-6 text-right">Cod univ:</div><div class="col-md-6 text-left">{{$order->cod_univ}}</div></div></div>
                                        <div class="field-edit">
                                            <input type="text" name="datee" class="form-control" placeholder="cod univ" value="{{$order->cod_univ}}" data-id="{{$order->idorder}}" data-field="cod_univ">
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="td-field">
                                         <div  class="field-value"><div class="row"><div class="col-md-6 text-right">Pec:</div><div class="col-md-6 text-left">{{$order->pec}}</div></div></div>
                                        <div class="field-edit">
                                            <input type="text" name="pec" class="form-control" placeholder="pec" value="{{$order->pec}}" data-id="{{$order->idorder}}" data-field="pec">
                                        </div>
                                    </td>
                                    <!--<td style="text-align: right;">-->
                                    <!--    <span style="color: black; font-size: 15px;"><b>Date : </b></span>-->
                                    <!--    <span style="color: black; font-size: 15px;"><b>{{$order->datee}}</b></span><br>-->
                                    <!--    <span style="color: black; font-size: 15px;"><b>{{$order->num_invoice}}</b></span><br>-->
                                    <!--    <span style="color: black; font-size: 15px;"><b>{{$order->cod_univ}}</b></span><br>-->
                                    <!--    <span style="color: black; font-size: 15px;"><b>{{$order->pec}}</b></span>-->
                                    <!--</td>-->
                                </tr>
                            </table>
                        </div>
                        </div>
                        <div style="margin-top: 50px;" class="row">
                            <div class="col-md-12">
                        <!--<div style="width: 100%; margin-top: 50px;">-->
                        <?php
                        
                            $total = $order->inv_price*$order->quantity; 
                            $vatnew = 0;
                            $vat = $order->inv_vat !='' ? $order->inv_vat : $order->channel->vat;
                            //$vat = ($total*($order->inv_vat !='' ? $order->inv_vat : $order->channel->vat ))/100; 
                            
                            if($vat > 0){
                               $vat = $vat/100;
                               $vatnew = ($total*$vat)/(1+$vat);
                            }
                            
                            
                            
                            if($order->channel->vat){
                               // $vat += ($order->orderInvoice->shipping*$order->orderInvoice->vat)/100;
                            }
                        ?>
                            <table class="table table-bordered">
                                <tr>
                                    <th style="width:10%; background: green; color: white; border: 1px solid grey; border-collapse: collapse;">Quantity</th>
                                    <th style="width:20%;  background: green; color: white; border: 1px solid grey; border-collapse: collapse;">SKU</th>
                                    <th style="width:10%; background: green; color: white; border: 1px solid grey; border-collapse: collapse;">Product</th>
                                    <th style="width:10%; background: green; color: white; border: 1px solid grey; border-collapse: collapse;">Price</th>
                                    <th style="width:10%; background: green; color: white; border: 1px solid grey; border-collapse: collapse;">Total</th>
                                    <th style="width:10%; background: green; color: white; border: 1px solid grey; border-collapse: collapse;">Vat(%)</th>
                                </tr>
                                <tr id="row_{{ $order->idorder }}">
                                    <td class="td-field quantity text-center">
                                         <span  class="field-value">{{$order->quantity}}</span>
                                        <div class="field-edit">
                                            <input type="text" name="quantity" class="form-control quantityInp" data-action="orderpriceupdate" placeholder="" value="{{$order->quantity}}" data-id="{{$order->idorder}}" data-field="quantity">
                                        </div>
                                    </td>
                                    <td class="td-field1 get_product_list">
                                         <span  class="field-value">{{$order->product->sku ?? ''}}</span>
                                        <div class="field-edit">
                                            <input type="text" class="form-control" placeholder="" value="{{$order->product->sku ?? ''}}" data-id="{{$order->idorder}}" data-field="sku">
                                        </div>
                                    </td>
                                    <td class="text-center">{{$order->product->modelcode ?? ''}}</td>
                                    <td class="td-field price text-right">
                                         <span  class="field-value">{{$order->inv_price ? number_format($order->inv_price, 2) : ''}}</span>
                                        <div class="field-edit">
                                            <input type="text" class="form-control priceInp" placeholder="" data-action="orderpriceupdate" value="{{$order->inv_price}}" data-id="{{$order->idorder}}" data-field="inv_price">
                                        </div>
                                    </td>
                                    <td class="td-field total text-right">
                                         <span  class="field-value">{{ number_format($total,2) }}</span>
                                        <div class="field-edit">
                                            <input type="text" class="form-control totalInp" placeholder="" value="{{ $order->inv_price*$order->quantity }}" data-id="{{$order->idorder}}" data-field="inv_total">
                                        </div>
                                    </td>
                                    <td class="td-field text-center">
                                         <span  class="field-value">{{$order->inv_vat !='' ? number_format($order->inv_vat, 2) : number_format($order->channel->vat,2) }}</span>
                                        <div class="field-edit">
                                            <input type="text" class="form-control vatInp" placeholder="" data-action="orderpriceupdate" value="{{$order->inv_vat !='' ? $order->inv_vat : $order->channel->vat }}" data-id="{{ $order->idorder }}" data-field="inv_vat">
                                        </div>
                                    </td>
                                </tr>
                                <?php $total = $order->inv_price*$order->quantity; ?>
                                <?php $multi_orders = \App\Models\OrderItem::where('multiorder',$order->referenceorder)->get(); ?>
                                @if($multi_orders)
                                @foreach($multi_orders as $item)
                                <?php 
                                    $total += $item->inv_price*$item->quantity; 
                                    
                                    $itemVat = $item->inv_vat !='' ? $item->inv_vat : $order->channel->vat;
                                   
                                    
                                    if($itemVat > 0){
                                       $vat = $itemVat/100;
                                       $vatnew += ($item->inv_price*$item->quantity*$vat)/(1+$vat);
                                    }
                                    
                                    //$vat += ($item->inv_price*$item->quantity*($item->inv_vat !='' ? $item->inv_vat : $order->channel->vat ))/100; 
                                    
                                ?>
                                <tr id="row_{{ $item->idorder }}">
                                    <td class="td-field quantity text-center">
                                         <span  class="field-value">{{$item->quantity}}</span>
                                        <div class="field-edit">
                                            <input type="text" class="form-control quantityInp" placeholder="" data-action="orderpriceupdate" value="{{$item->quantity}}" data-id="{{$item->idorder}}" data-field="quantity">
                                        </div>
                                    </td>
                                    <td class="td-field1 get_product_list">
                                         <span  class="field-value">{{$item->product->sku ?? ''}}</span>
                                        <div class="field-edit">
                                            <input type="text" class="form-control sku" placeholder="" value="{{$item->product->sku ?? ''}}" data-id="{{$item->idorder}}" data-field="">
                                        </div>
                                    </td>
                                    <td class="text-center">{{$item->product->modelcode ?? ''}}</td>
                                    <td class="td-field price text-right">
                                         <span  class="field-value">{{$item->inv_price ? number_format($item->inv_price, 2) : 0}}</span>
                                        <div class="field-edit">
                                            <input type="text" class="form-control priceInp" placeholder="" data-action="orderpriceupdate" value="{{$item->inv_price}}" data-id="{{$item->idorder}}" data-field="inv_price">
                                        </div>
                                    </td>
                                    <td class="td-field total text-right">
                                         <span  class="field-value">{{ number_format($item->inv_price*$item->quantity, 2) }}</span>
                                        <div class="field-edit">
                                            <input type="text" class="form-control totalInp" placeholder="" value="{{$item->inv_price*$item->quantity}}" data-id="{{$item->idorder}}" data-field="inv_total">
                                        </div>
                                    </td>
                                    <td class="td-field text-center">
                                         <span  class="field-value">{{$item->inv_vat !='' ?  number_format($item->inv_vat, 2) : number_format($item->channel->vat ,2)}}</span>
                                        <div class="field-edit">
                                            <input type="text" class="form-control vatInp" placeholder="" data-action="orderpriceupdate" value="{{$item->inv_vat !='' ? $item->inv_vat : $item->channel->vat }}" data-id="{{ $item->idorder }}" data-field="inv_vat">
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                                @endif
                                <tr>
                                    <td style="text-align: right;"></td>
                                    <td style="text-align:;"><strong>Shipping</strong></td>
                                    <td style="text-align: right;"></td>
                                    <td style=""></td>
                                    <td class="td-field text-right">
                                         <span  class="field-value">{{ number_format( $order->orderInvoice->shipping, 2) }}</span>
                                        <div class="field-edit">
                                            <input type="text" class="form-control shippingInp" placeholder="" data-action="orderinvoice" value="{{ $order->orderInvoice->shipping }}" data-id="{{$order->idorder}}" data-field="shipping">
                                        </div>
                                    </td>
                                    <td class="td-field text-center">
                                         <span  class="field-value">{{ number_format($order->orderInvoice->vat, 2) }}</span>
                                        <div class="field-edit">
                                            <input type="text" class="form-control shippingVat" placeholder=""  data-action="orderinvoice"  value="{{ $order->orderInvoice->vat }}" data-id="{{$order->idorder}}" data-field="vat">
                                        </div>
                                    </td>
                                </tr>
                                <?php 
                                    if($order->orderInvoice->vat > 0){
                                       $vat = $order->orderInvoice->vat/100;
                                       $vatnew += ($order->orderInvoice->shipping*$vat)/(1+$vat);
                                    }
                                ?>
                                <tr>
                                    <td style="text-align: right;"></td>
                                    <td><strong>Difference</strong></td>
                                    <td style="text-align: right;"></td>
                                    <td style=""></td>
                                    <?php $dff = $order->sum - ($total+$order->orderInvoice->shipping);  ?>
                                    <td style="" class="diff text-right">{{ $dff > 0 ? number_format($dff,2) : 0 }}</td>
                                </tr>
                                <tr>
                                    <td style="text-align: right;"></td>
                                    <td style="text-align: right;"></td>
                                    <td style="text-align: right;"></td>
                                    <td style=""><strong>VAT</strong></td>
                                    <?php ?>
                                    <td style="" class="vat text-right">{{ number_format($vatnew,2) }}</td>
                                </tr>
                                <tr>
                                    <td style="text-align: right;"></td>
                                    <td style="text-align: right;"></td>
                                    <td style="text-align: right;"></td>
                                    <td style=""><strong>Total</strong></td>
                                    <td style="" class="total text-right">{{ number_format($order->sum,2)}}<input type="hidden" id="grandtotal" value="{{$order->sum}}" /></td>
                                </tr>
                            </table>
                        </div>
                        </div>
                        <div style="margin-top: 50px;" class="row">
                            <div class="col-md-12">
                        <p>Payment: {{$order->idpayment}}</p>
                        <p>Platform: {{$order->channel->shortname ?? '' }}</p>
                        <p>Reference order: {{$order->referenceorder}}</p>
                        <div style="">
                            <p style="text-align: center;">{!! $order->company->note !!}</p>
                            <table style="width: 100%;">
                                <tr>
                                    <td style="width: 30%;">
                                        {!! $order->company->bankInformation !!}
                                    </td>
                                    <td style="width: 30%;">
                                        {!! $order->company->noteInvoice !!}
                                    </td>
                                    <td style="text-align: right;">
                                        <span style="color: green; font-size: 30px;"><b>{{$order->company->longname ?? '' }}</b></span><br>
                                        <span style="color: black; font-size: 15px;"><b>{{$order->company->street1 ?? '' }}</b></span><br>
                                        <span style="color: black; font-size: 15px;"><b>{{$order->company->plz ?? '' }} {{$order->company->city ?? '' }} ({{$order->company->province ?? '' }})</b></span><br>
                                        <span style="color: black; font-size: 15px;"><b>Tel: {{$order->company->phone ?? '' }}</b></span><br>
                                        <span style="color: black; font-size: 15px;"><b>Fax: {{$order->company->fax ?? '' }}</b></span><br>
                                        <span style="color: black; font-size: 15px;"><b>{{$order->company->email ?? '' }}</b></span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="text-center">
                            <a href="{{ url('createOrderInvoice?del='.$order->idorder.'&type=create_invoice') }}" class="btn btn-warning">Download invoice</a>
                        </div>
                    </div>
                </div>
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
<style>
    .modal-lg {
        max-width: 1200px !important;
    }
/*    input.form-control.quantityInp {*/
/*    width: 60%;*/
/*}*/
/*td.td-field.quantity.text-center .field-edit {*/
/*    width: 60%;*/
/*}*/
</style>
<script>
$(document).ready(function() {
    
    $(document).on('keyup', '.quantityInp, .totalInp, .priceInp, .shippingInp, .vatInp, .shippingVat', function () { 
        var row = $(this).closest('tr');
        
        var quantity = row.find('.quantityInp').val();
        var price = row.find('.priceInp').val();
        var total = parseInt(quantity)*parseFloat((price?price:0));
        row.find('.total').find('.field-value').text(total.toFixed(2));
        row.find('.total').find('.totalInp').val(total.toFixed(2));
        var shippingVat = $(".shippingVat").val();
        var total= 0;
        var vattotal = 0;
        $('.totalInp').each(function(){
            var val = $(this).val() ? parseFloat($(this).val()) : 0
            
            var vat_val = $(this).closest('td').next().find('input').val();
            
            if(vat_val > 0){
                var vat = parseFloat(vat_val)/100;
                console.log(vat);
                vattotal += val*vat/(1+vat);
                 console.log(vattotal);
            }
            
            total+= val;
        });
        
        var grandtotal = parseFloat($("#grandtotal").val());
        var shipping = parseFloat($(".shippingInp").val());
        
        if(shipping > 0){
            var vat = parseFloat(shippingVat)/100;
            vattotal += shipping*vat/(1+vat);
        }
        
        var mwst = grandtotal - (total + shipping);
        var diff = grandtotal - (total + shipping);
        $('.diff').text(diff.toFixed(2));
        $('.vat').text(vattotal.toFixed(2));
        $('.mwst').text(mwst.toFixed(2));
        
    });
    
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
                url    : "{{ url('orderUpdate') }}",
                data   : {
                    id:ordrid ,
                    value:sku ,
                    action: 'sku_update',
                    type: 'invoice'
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

            url    : "{{ url('orderUpdate') }}",

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
});
</script>
@endsection