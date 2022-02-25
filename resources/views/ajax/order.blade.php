@if(request()->type == 'invoice')
<td class="td-field quantity">
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
<td>{{$order->product->modelcode ?? ''}}</td>
<td class="td-field price">
     <span  class="field-value">{{$order->inv_price ? $order->inv_price : 0}}</span>
    <div class="field-edit">
        <input type="text" class="form-control priceInp" placeholder="" data-action="orderpriceupdate" value="{{$order->inv_price}}" data-id="{{$order->idorder}}" data-field="inv_price">
    </div>
</td>
<td class="td-field total">
     <span  class="field-value">{{ $order->inv_price*$order->quantity }}</span>
    <div class="field-edit">
        <input type="text" class="form-control totalInp" placeholder="" value="{{ $order->inv_price*$order->quantity }}" data-id="{{$order->idorder}}" data-field="inv_total">
    </div>
</td>
<td class="td-field">
     <span  class="field-value">{{$order->channel->vat ?? '' }}</span>
    <div class="field-edit">
        <input type="text" class="form-control" placeholder="" value="{{$order->channel->vat ?? '' }}" data-action="update_channel"  data-id="{{$order->channel->idchannel ?? '' }}" data-field="vat">
    </div>
</td>
@else
<td style="width:5%;" class="td-field">
<span class="field-value">{{ $order->quantity ? $order->quantity : '0' }}</span>
<div class="field-edit">
    <input type="text" class="form-control" placeholder="Quantity" value="{{$order->quantity }}" data-field="quantity" data-id="{{$order->idorder}}">
</div>
</td>
<td style="width:20%; text-align:cengter;" class="td-field1 get_product_list">
<span class="field-value">{{ $order->product ? $order->product->sku : 'N/A' }}</span>
<div class="field-edit">
    <input type="text" class="form-control update_item" placeholder="sku" value="{{$order->product->sku}}" data-action="sku_update"  data-id="{{$order->idorder}}">
</div>
</td>
<td style="width:15%; text-align:cengter;">{{ $order->product ? $order->product->namelong : 'N/A' }}</td>
<td style="width:25%; text-align:cengter;">{{  $order->product ? $order->product->ean : 'N/A' }}</td>
@endif