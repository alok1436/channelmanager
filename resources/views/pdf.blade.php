<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <style>
            @page {
                margin: 30px 0 0 0;
            }

            body {
                margin: 0 auto;
                font-family: Arial, Helvetica, sans-serif;
            }
            td {
                font-size: 12px;
                vertical-align: top;
            }
            .abc{
                width: 50%;float: left;min-height: 300px;
            }
            .abc2{
                width: 100%;float: none;min-height: 500px;
            }
        </style>
    </head>

    <body>
        
        <div class="">
            <!--<table style="width: 100%; float: left; padding: 0px;">-->
            <!--    <tbody>-->
                    <?php $j = 0;?>
                    @foreach($orders as $order)
                        <!--@if($j==0)-->
                        <!--    <tr style="">	-->
                        <!--@endif-->
                    
                    <div class="{{ $order->idpayment == 'FBA' ? 'abc2' : 'abc' }}">
						<table style="100%">
							<tbody>
								<tr style="">
									<td style="width: 160px;vertical-align: middle; text-align: center; padding: 0 25px 0 0;">
                                        <span style="width: 160px;font-weight: bold; vertical-align: middle; text-align: center; padding: 0 25px 0 0;">{{$order->shortnamecompany}}</span> <br/>
                                        <span style="font-size:7px; display: block;">{{$order->street1company}}</span> <br/>
                                        <span style="font-size:7px; display: block;">{{$order->plzcomapny}} {{$order->citycomapny}} {{$order->countrycomapny}}</span>
                                    </td>
                                    <td style="width: 312px; height: 50px;" colspan="2">{{$order->carriername}} {{$order->tracking}}</td>
								</tr>
                                
								<tr style="">
                                    <td style="">&nbsp;</td>
                                    <td style="font-size: 13px; width: 30%;">{{$order->quantity}} x {{$order->sku}}</td>
                                    <td style="padding: 5px; width: 70%;">{!!$order->barcode!!}<td>
                                </tr>
                               
                                @if(isset($order->multiOrders))
                                    @foreach($order->multiOrders as $k=>$item)
                                         @if(isset($item->sku) && $item->sku != "")
                                <tr style="">
                                    <td style="">&nbsp;</td>
                                    <td style="font-size: 13px; width: 30%;">{{$item->quantity}} x {{$item->sku}}</td>
                                    <td style="padding: 5px; width: 70%;">{!!$item->barcode!!}</td>
                                </tr>
                                         @endif
                                    @endforeach
                                @endif
								<tr style="">
									<td style="font-weight: bold; vertical-align: top; text-align: right; padding: 0 25px 0 0;font-size: 8px; height: 40px;font-style:italic;">Contenuto</td>
									<td style="height: 23px;" colspan="2">&nbsp;</td>
								</tr>
								<tr style="">
									<td style="font-weight: bold; vertical-align: top; text-align: right; padding: 0 25px 0 0;font-size: 8px; height: 80px;font-style:italic;">Destinatario</td>
									<td style="font-size: 13px;">
										<span style="font-weight: bold; font-size: 13px;"> 
										{{$order->customer}} {{$order->customerextra}}</span> <br/>
										{{$order->address1}} {{$order->address2}}<br/>
										{{$order->plz1}} {{$order->city1}} {{$order->region1}}<br/>
										{{$order->country}}<br/>
										{{$order->telefon}} {{$order->fax}}<br/>
                                        {{$order->delivery_Instructions}}<br/>
									</td>
								</tr>
								<tr style="">
									<th colspan="2" style="font-weight: bold; vertical-align: middle; text-align: center; padding: 0 100px 0 0;font-size: 8px; height: 40px;font-style:italic;">Data <span style="font-weight:normal;">{{$order->datee}}</span></th>
								</tr>
							</tbody>
						</table>
					</div>
					{{--  <div style="page-break-before:always">&nbsp;</div>   --}}
                        
                        <?php 
                            if($order->idpayment != 'FBA') $j++; 
                        ?>
                        @if($j%2==0)
                            <div style="clear:both; width:100%"></div>
                        @endif
                        @if($j%4==0 || $order->idpayment == 'FBA' )
                            <div style="page-break-before:always">&nbsp;</div>
                        @endif
                    @endforeach
            <!--    </tbody>-->
            <!--</table>-->
        </div>
    </body>
</html>