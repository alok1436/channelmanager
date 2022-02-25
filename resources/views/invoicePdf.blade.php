<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <style>
            @page {
                margin: 30px 0 0 0;
            }

            body {
                margin: 0 auto;
                padding: 15px;
                font-family: Arial, Helvetica, sans-serif;
            }
            td {
                font-size: 12px;
                vertical-align: top;
            }
        </style>
    </head>

    <body>
        <div style="width: 100%;">
            <table style="width: 100%;">
                <tr>
                    <td>
                    @if($orders->linklogo != "" && $orders->linklogo != null)
                        <img src="assets/{{$orders->linklogo}}" style="width: 100px;" alt="">
                    @endif
                    </td>
                    <td style="text-align: right;">
                        <span style="color: green; font-size: 30px;"><b>{{$orders->companyName}}</b></span><br>
                        <span style="color: black; font-size: 15px;"><b>{{$orders->street1}}</b></span><br>
                        <span style="color: black; font-size: 15px;"><b>{{$orders->plzcomapny}} {{$orders->citycomapny}} ({{$orders->provincecompany}})</b></span><br>
                        <span style="color: black; font-size: 15px;"><b>Tel: {{$orders->companyPhone}}</b></span><br>
                        <span style="color: black; font-size: 15px;"><b>Fax: {{$orders->companyFax}}</b></span><br>
                        <span style="color: black; font-size: 15px;"><b>{{$orders->companyEmail}}</b></span>
                    </td>
                </tr>
            </table>
        </div>

        <div style="width: 100%; height: 10px; background: green;"></div>

        <div style="width: 100%; margin-top: 50px;">
            <table style="width: 100%;">
                <tr>
                    <td style="width:30%">
                        <span style="color: black; font-size: 15px;"><b>{{$orders->inv_customer}}</b></span><br>
                        <span style="color: black; font-size: 15px;"><b>{{$orders->inv_customerextra}}</b></span><br>
                        <span style="color: black; font-size: 15px;"><b>{{$orders->inv_address1}}</b></span><br>
                        <span style="color: black; font-size: 15px;"><b>{{$orders->inv_address2}}</b></span><br>
                        <span style="color: black; font-size: 15px;"><b>{{$orders->plz1}} {{$orders->city1}}({{$orders->region1}})</b></span><br>
                        <span style="color: black; font-size: 15px;"><b>{{$orders->country1}}</b></span><br>
                    </td>
                    <td style="width:30%">&nbsp;</td>
                    <td style="text-align: right;width:30%">
                        <table  style="width: 100%; color: black; font-size: 15px;">
                            <tr><td style="color: black; font-size: 15px;text-align:right"><b><b>Date:</b></td><td>{{$orders->datee}}</td></tr>
                            @if($orders->num_invoice)
                                <tr><td style="color: black; font-size: 15px;text-align:right"><b>Invoice Number:</b></td><td>{{$orders->num_invoice}}</td></tr>
                            @endif
                            @if($orders->cod_univ)
                                <tr><td style="color: black; font-size: 15px;text-align:right"><b>Code univ:</b></td><td>{{$orders->cod_univ}}</td></tr>
                            @endif
                            @if($orders->pec)
                                <tr><td style="color: black; font-size: 15px;text-align:right"><b>Pec:</b></td><td>{{$orders->pec}}</td></tr>
                            @endif
                        </table>
                    </td>
                </tr>
            </table>
        </div>
         <?php $total = $order->inv_price*$order->quantity;  ?>
         <?php 
            $vatnew = 0;
            $vat = $order->inv_vat !='' ? $order->inv_vat : $order->channel->vat;
            if($vat > 0){
               $vat = $vat/100;
               $vatnew = ($total*$vat)/(1+$vat);
            }
         ?>
        <div style="width: 100%; margin-top: 50px;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <th style="background: green; color: white; border: 1px solid grey; border-collapse: collapse; height: 20px;">Quantity</th>
                    <th style="background: green; color: white; border: 1px solid grey; border-collapse: collapse; height: 20px;">SKU</th>
                    <th style="background: green; color: white; border: 1px solid grey; border-collapse: collapse; height: 20px;">Product</th>
                    <th style="width:10%; background: green; color: white; border: 1px solid grey; border-collapse: collapse;">Price</th>
                    <th style="width:10%; background: green; color: white; border: 1px solid grey; border-collapse: collapse;">Total</th>
                    <th style="width:10%; background: green; color: white; border: 1px solid grey; border-collapse: collapse;">Vat(%)</th>
                </tr>
                <tr>
                    <td style="text-align: right; border: 1px solid grey; border-collapse: collapse; height: 20px;">{{$orders->quantity}}</td>
                    <td style="border: 1px solid grey; border-collapse: collapse; height: 20px;">{{$orders->sku}}</td>
                    <td style="border: 1px solid grey; border-collapse: collapse; height: 20px;">{{$orders->modelcode}}</td>
                    <td style="text-align: right; border: 1px solid grey; border-collapse: collapse; height: 20px;">{{$orders->inv_price}}</td>
                    <td style="text-align: right; border: 1px solid grey; border-collapse: collapse; height: 20px;">{{ number_format($total, 2)}}</td>
                    <td style="text-align: right; border: 1px solid grey; border-collapse: collapse; height: 20px;">{{$order->inv_vat !='' ? $order->inv_vat : $order->channel->vat }}</td>
                </tr>
                @if(isset($orders->multiOrders))
                @foreach($orders->multiOrders as $item)
                 <?php 
                    $total += $item->inv_price*$item->quantity;
                    $itemVat = $item->inv_vat !='' ? $item->inv_vat : $order->channel->vat;
                    
                    if($itemVat > 0){
                       $vat = $itemVat/100;
                       $vatnew += ($item->inv_price*$item->quantity*$vat)/(1+$vat);
                    }
                 ?>
                <tr>
                    <td style="text-align: right; border: 1px solid grey; border-collapse: collapse; height: 20px;">{{$item->quantity}}</td>
                    <td style="border: 1px solid grey; border-collapse: collapse; height: 20px;">{{$item->sku}}</td>
                    <td style="border: 1px solid grey; border-collapse: collapse; height: 20px;">{{$item->modelcode}}</td>
                    <td style="text-align: right; border: 1px solid grey; border-collapse: collapse; height: 20px;">{{$item->inv_price}}</td>
                    <td style="text-align: right; border: 1px solid grey; border-collapse: collapse; height: 20px;">{{ number_format($item->inv_price*$item->quantity,2) }}</td>
                    <td style="text-align: right; border: 1px solid grey; border-collapse: collapse; height: 20px;">{{$item->inv_vat !='' ? $item->inv_vat : $item->channel->vat }}</td>
                </tr>
                @endforeach
                @endif
                <tr>
                    <td style="text-align: right; border: 1px solid grey; border-collapse: collapse; height: 20px;"></td>
                    <td style="text-align: right; border: 1px solid grey; border-collapse: collapse; height: 20px;"></td>
                    <td style="border: 1px solid grey; border-collapse: collapse;"></td>
                    <td style="text-align: right; border: 1px solid grey; border-collapse: collapse; height: 20px;"></td>
                    <td style="text-align: right; border: 1px solid grey; border-collapse: collapse; height: 20px;"></td>
                    <td style="text-align: right; border: 1px solid grey; border-collapse: collapse; height: 20px;"></td>
                </tr><tr>
                    <td style="text-align: right; border: 1px solid grey; border-collapse: collapse; height: 20px;"></td>
                    <td style="text-align: right; border: 1px solid grey; border-collapse: collapse; height: 20px;"></td>
                    <td style="border: 1px solid grey; border-collapse: collapse;"></td>
                    <td style="text-align: right; border: 1px solid grey; border-collapse: collapse;"></td>
                    <td style="text-align: right; border: 1px solid grey; border-collapse: collapse; height: 20px;"></td>
                    <td style="text-align: right; border: 1px solid grey; border-collapse: collapse; height: 20px;"></td>
                </tr>center
                <tr>
                    <td style="text-align: right; border: 1px solid grey; border-collapse: collapse; height: 20px;"></td>
                    <td style="text-align: right; border: 1px solid grey; border-collapse: collapse; height: 20px;"></td>
                    <td style="border: 1px solid grey; border-collapse: collapse;"></td>
                    <td style="text-align: right; border: 1px solid grey; border-collapse: collapse;"></td>
                    <td style="text-align: right; border: 1px solid grey; border-collapse: collapse; height: 20px;"></td>
                    <td style="text-align: right; border: 1px solid grey; border-collapse: collapse; height: 20px;"></td>
                </tr>
                <tr>
                    <td style="text-align: right; border: 1px solid grey; border-collapse: collapse; height: 20px;"></td>
                    <td style="text-align: left; border: 1px solid grey; border-collapse: collapse; height: 20px;"><strong>Shipping</strong></td>
                    <td style="border: 1px solid grey; border-collapse: collapse;"></td>
                    <td style="text-align: right; border: 1px solid grey; border-collapse: collapse;"></td>
                    <td style="text-align: right; border: 1px solid grey; border-collapse: collapse; height: 20px;">{{ number_format($order->orderInvoice->shipping, 2) }}</td>
                    <td style="text-align: right; border: 1px solid grey; border-collapse: collapse; height: 20px;">{{ number_format($order->orderInvoice->vat, 2) }}</td>
                </tr>
                <tr>
                    <td style="text-align: right; border: 1px solid grey; border-collapse: collapse; height: 20px;"></td>
                    <td style="text-align: left; border: 1px solid grey; border-collapse: collapse; height: 20px;"><strong>Difference</strong></td>
                    <td style="border: 1px solid grey; border-collapse: collapse;"></td>
                    <td style="text-align: right; border: 1px solid grey; border-collapse: collapse;"></td>
                    <?php $dff = $order->sum - ($total+$order->orderInvoice->shipping);  ?>
                    <td style="text-align: right; border: 1px solid grey; border-collapse: collapse; height: 20px;">{{ $dff > 0 ? number_format($dff, 2) : 0 }}</td>
                    <td style="text-align: right; height: 20px;"></td>
                </tr>
                <?php 
                    if($order->orderInvoice->vat > 0){
                       $vat = $order->orderInvoice->vat/100;
                       $vatnew += ($order->orderInvoice->shipping*$vat)/(1+$vat);
                    }
                ?>
                <tr>
                    <td style="text-align: right; height: 20px;"></td>
                    <td style="text-align: right; height: 20px;"></td>
                    <td style="text-align: right; height: 20px;"></td>
                    <td style="border: 1px solid grey; border-collapse: collapse; text-align: center; height: 20px;">Vat</td>
                    <td style="text-align: right; border: 1px solid grey; border-collapse: collapse; height: 20px;">{{ number_format($vatnew,2) }}</td>
                    <td style="text-align: right; height: 20px;"></td>
                </tr>
                <tr>
                    <td style="text-align: right; height: 20px;"></td>
                    <td style="text-align: right; height: 20px;"></td>
                    <td style="text-align: right; height: 20px;"></td>
                    <td style="border: 1px solid grey; border-collapse: collapse; text-align: center; height: 20px;">Total</td>
                    <td style="text-align: right; border: 1px solid grey; border-collapse: collapse; height: 20px;">{{ number_format($orders->sum, 2)}}</td>
                    <td style="text-align: right; height: 20px;"></td>
                </tr>
            </table>
        </div>
        
        <p>Payment: {{$orders->idpayment}}</p>
        <p>Platform:{{$order->channel->shortname ?? '' }}</p>
        <p>Reference order: {{$orders->referenceorder}}</p>
        <div style="width: 100%; position: absolute; bottom: 10px;">
            <p style="text-align: center;">{!! $orders->companyNote !!}</p>
            <table style="width: 100%;">
                <tr>
                    <td style="width: 30%;">
                        {!! $orders->bankInformation !!}
                    </td>
                    <td style="width: 30%;">
                        {!! $orders->noteInvoice !!}
                    </td>
                    <td  style="width: 30%;text-align: left;padding-left:30px">
                        <span style="color: black; font-size: 13px;">{{$orders->companyName}}</span><br>
                        <span style="color: black; font-size: 13px;">{{$orders->street1}}</span><br>
                        <span style="color: black; font-size: 13px;">{{$orders->plzcomapny}} {{$orders->citycomapny}} ({{$orders->provincecompany}})</span><br>
                        <span style="color: black; font-size: 13px;">Tel: {{$orders->companyPhone}}</span><br>
                        <span style="color: black; font-size: 13px;">Fax: {{$orders->companyFax}}</span><br>
                        <span style="color: black; font-size: 13px;">{{$orders->companyEmail}}</span>
                    </td>
                </tr>
            </table>
        </div>
    </body>
</html>