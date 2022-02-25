@extends('layouts.default')
@section('content')
<div class="row">
    <div class="col-12">			  
        <div class="card">
            <div class="card-body">
                <div class="container">
                    <button type="button" class="btn btn-info btn-lg" data-toggle="modal" data-target="#myModalNew">Import File New</button>
                    <div class="modal fade" id="myModalNew" role="dialog">
                        <div class="modal-dialog modal-lg">
                            <!-- Modal content-->
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    <h4 class="modal-title">IMPORT CSV</h4>
                                </div>
                                <div class="modal-body">
                                    <table class="table table-responsive" border="1">
                                        <thead>
                                            <tr>
                                                <th>Platform</th>
                                                <th>Channel</th>
                                                <th>File</th>
                                            </tr>
                                        </thead>
                                        <form class="form-horizontal" action="importOrderFile" method="post" name="frmCSVImport" id="frmCSVImport" enctype="multipart/form-data">
                                        {{csrf_field()}}
                                        <tbody>
                                            @foreach ($platformsShort as $key => $row)
                                            <tr>
                                                <td>
                                                    <input type="checkbox" name="check[]" value="{{$row->platformid}}-{{$row->channelId}}" style="display: none;" id="">
                                                    {{$row->platformname}}
                                                </td>
                                                <td>{{$row->channelname}}</td>
                                                <td><input type="file" name="uploadfilename[]" class="form-control" onchange="checkCheckBox(this)" /></td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <button type="submit" id="submit" name="importsystem" class="btn-submit">Upload Files</button>
                                        </form>
                                    </table>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Modal -->
                    <div class="modal fade" id="myModal" role="dialog">
                        <div class="modal-dialog modal-lg">
                            <!-- Modal content-->
                            <div class="modal-content">
                                <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                <h4 class="modal-title">IMPORT CSV</h4>
                                </div>
                                <div class="modal-body">
                                <table class="table table-responsive" border="1">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>Platform ID</th>
                                            <th>Channel</th>
                                            <th>File</th>
                                            <th>B2C</th>
                                        </tr>
                                    </thead>
                                    <form class="form-horizontal" action="" method="post" name="frmCSVImport" id="frmCSVImport" enctype="multipart/form-data">
                                    <tbody>
                                        @foreach ($platformsLong as $key => $row)
                                        <tr>
                                            <td><input type="checkbox" name="check[]" value="{{$row->idchannel}}"  id=""></td>
                                            <td>{{$row->platformname}}</td>
                                            <td>{{$row->shortname}}</td>
                                            <td><input type="file" name="longname[]" class="form-control" />
                                            <td>{{$row->b2c}}</td>
                                        </tr>
                                        @endforeach
                                        <button type="submit" id="submit" name="import" class="btn-submit">Upload Files</button>
                                    </tbody>
                                    </form>
                                </table>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>  
        <!-- card -->
        <div class="card">
            <div class="card-body">
                <form method="post" action="{{ url('orderAdd') }}" id="form">
                    {{csrf_field()}}
                    <div class="row">
                        <div class="col-md-6 col-sm-12">
                            <div class="form-group">
                                <label>Company ID</label>
                                <select class="form-control" name="idcompany" required>
                                    <option value="">Select Company</option>
                                    @foreach ($companies as $key => $row)
                                    <option value="{{$row->idcompany}}">{{$row->shortname}}</option>
                                    @endforeach
                                </select>    
                            </div>
                            <div class="form-group">
                                <label>Reference Channel</label>
                                <select name="referencechannel" class="form-control">
                                <!--<option value="0" selected="selected">Manual</option>-->
                                    @foreach ($channels as $key => $row)
                                    <option value="{{$row->idchannel}}">{{$row->shortname}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Date</label>
                                <input type="date" name="date" class="form-control"/>
                            </div>
                            <div class="for_single row">
                                <input type="hidden" id="totaladdrow" name="totaladdrow" value="0">
                                <div class="form-group col-sm-5">
                                    <label>Quantity</label>
                                    <input type="number" name="quantity" id="quantity" class="form-control" />
                                </div>
                                <div class="form-group col-sm-5">
                                    <label>Product ID</label>
                                    <select class="form-control" name="productid" id="productid">
                                        <option value="">Select Product</option>
                                        @foreach ($products as $key => $row)
                                        <option value="{{$row->productid}}">{{$row->nameshort}}</option>
                                        @endforeach
                                    </select>    
                                </div>

                                <div class="form-group add_more_data col-sm-2 text-right">
                                    <label class="w-100 d-none d-sm-block d-xl-block">&nbsp; </label>
                                    <button class="btn btn-default" id="add_mul_qty"><img src="assets/images/plus-solid.svg" style="width: 12px;" alt=""></button>
                                </div>

                                <div class="form-group col-sm-12">
                                    <div class="add_multiple_qty_section"></div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Sum</label>
                                <input type="number" name="sum" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Currency</label>
                                <select class="form-control" name="currency" required="">
                                    <option value="">Select Currency</option>
                                    @foreach($qcurrency as $data) 
                                    <option value="{{$data->shortname}}">{{$data->shortname}}</option>
                                    @endforeach
                                </select>    
                            </div>
                            
                            <div class="form-group">
                                <label>Payment</label>
                                <select class="form-control" name="idpayment" required="">
                                    <option value="">Select Payment</option>
                                    @foreach($payments as $row) 
                                    <option value="{{$row->shortname}}">{{$row->shortname}}</option>
                                    @endforeach
                                </select>    
                            </div>
                            
                            <div class="form-group">
                                <label>Warehouse ID</label>
                                <select class="form-control" name="idwarehouse" required="">
                                    <option value="">Select Warehouse ID</option>
                                    @foreach ($warehouses as $key => $ware)
                                    <option value="{{$ware->idwarehouse}}">{{$ware->shortname}}</option>
                                    @endforeach
                                </select>    
                            </div>
                            <div class="form-group">
                                <label>Reference Order</label>
                                <input type="number" name="referenceorder" class="form-control" required="" />
                            </div>
                            <div class="form-group">
                                <label>Customer</label>
                                <input type="text" name="customer" class="form-control"/>
                            </div>
                            <div class="form-group">
                                <label>Customer Extra</label>
                                <input type="text" name="customerextra" class="form-control"/>
                            </div>
                            <div class="form-group">
                                <label>Address 1</label>
                                <input type="text" name="address1" class="form-control"/>
                            </div>
                            <div class="form-group">
                                <label>Address 2</label>
                                <input type="text" name="address2" class="form-control"/>
                            </div>
                            <div class="form-group">
                                <label>Plz</label>
                                <input type="number" name="plz" class="form-control"/>
                            </div>
                            <div class="form-group">
                                <label>City</label>
                                <input type="text" name="city" class="form-control"/>
                            </div>
                            <div class="form-group">
                                <label>Region</label>
                                <input type="text" name="region" class="form-control"/>
                            </div>
                            
                            <div class="form-group">
                                <label>Country</label>
                                <select class="form-control" name="country" required="">
                                    <option value="">Select Country</option>
                                    @foreach($qcountry as $data) 
                                    <option value="{{$data->shortname}}">{{$data->shortname}}</option>
                                    @endforeach
                                </select>    
                            </div>

                            <div class="form-group">
                                <label>Telefon</label>
                                <input type="text" name="telefon" class="form-control"/>
                            </div>
                            <div class="form-group">
                                <label>Fax</label>
                                <input type="text" name="fax" class="form-control"/>
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control"/>
                            </div>
                            <div class="form-group">
                                <label>Order item id</label>
                                <input type="text" name="order_item_id" class="form-control"/>
                            </div>
                            <div class="form-group">
                                <label>Ship service level</label>
                                <input type="text" name="ship_service_level" class="form-control"/>
                            </div>
                            
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <div class="form-group d-none">
                                <label>Invoicenr</label>
                                <input type="number" name="invoicenr" class="form-control"/>
                            </div>
                            <div class="form-group d-none">
                                <label>Customer</label>
                                <input type="text" name="inv_customer" class="form-control"/>
                            </div>
                            <div class="form-group d-none">
                                <label>Customer Extra</label>
                                <input type="text" name="inv_customerextra" class="form-control"/>
                            </div>
                            <div class="form-group d-none">
                                <label>VAT</label>
                                <input type="text" name="inv_vat" class="form-control"/>
                            </div>
                            <div class="form-group d-none">
                                <label>Address 1</label>
                                <input type="text" name="inv_address1" class="form-control"/>
                            </div>
                            <div class="form-group d-none">
                                <label>Address 2</label>
                                <input type="text" name="inv_address2" class="form-control"/>
                            </div>
                            <div class="form-group d-none">
                                <label>Plz 1</label>
                                <input type="number" name="plz1" class="form-control"/>
                            </div>
                            <div class="form-group d-none">
                                <label>City 1</label>
                                <input type="text" name="city1" class="form-control"/>
                            </div>
                            <div class="form-group d-none">
                                <label>Region 1</label>
                                <input type="text" name="region1" class="form-control"/>
                            </div>
                            <div class="form-group d-none">
                                <label>Country 1</label>
                                <input type="text" name="country1" class="form-control"/>
                            </div>
                            <div class="form-group d-none">
                                <label>Telefon 1</label>
                                <input type="text" name="telefon1" class="form-control"/>
                            </div>
                            <div class="form-group d-none">
                                <label>Fax 1</label>
                                <input type="text" name="fax1" class="form-control"/>
                            </div>
                            <div class="form-group d-none">
                                <label>Email 1</label>
                                <input type="email" name="email1" class="form-control"/>
                            </div>
                            <div class="form-group d-none">
                            <label>Registered Stand Ok:</label>
                                <div class="bt-switch mb-10" style="width:140px;">
                                    <input type="checkbox" name="registeredtolagerstandok" checked data-on-color="info" value="1" data-off-color="success">
                                </div>
                            </div>
                            <div class="form-group d-none">
                            <label>Registered Sold Ok:</label>
                                <div class="bt-switch mb-10" style="width:140px;">
                                    <input type="checkbox" name="registeredtosolddayok" checked data-on-color="info" value="1" data-off-color="success">
                                </div>
                            </div>
                            <div class="form-group d-none">
                                <label>Courier Ok:</label>
                                <div class="bt-switch mb-10" style="width:140px;">
                                    <input type="checkbox" name="courierinformedok" checked data-on-color="info" value="1" data-off-color="success">
                                </div>
                            </div>
                            <div class="form-group d-none">
                                <label>Tracking Ok:</label>
                                <div class="bt-switch mb-10" style="width:140px;">
                                    <input type="checkbox" name="trackinguploadedok" checked data-on-color="info" value="1" data-off-color="success">
                                </div>
                            </div>

                            <div class="form-group d-none">
                                <label>Note</label>
                                <textarea type="text" name="notes" class="form-control" placeholder="Say Something ..." rows="5"></textarea>
                            </div>
                        </div>
                    </div>
                    <button type="submit" name="submit-order" class="btn btn-info pull-right">Submit <i class="fa fa-spinner fa-spin" style="display:none;"></i></button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
                   