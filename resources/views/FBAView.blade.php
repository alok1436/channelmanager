<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="">
        <link rel="icon" type="image/png" sizes="16x16" href="assets/images/favicon.png">
        <title>Admin - Panel </title>
        @if(Session::has('download.in.the.next.request'))
            <meta http-equiv="refresh" content="3;url={{ Session::get('download.in.the.next.request') }}">
        @endif
        <link href="assets/plugins/bootstrap-switch/bootstrap-switch.min.css" rel="stylesheet">
        <link href="assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">
        <link href="assets/plugins/morrisjs/morris.css" rel="stylesheet">
        <link rel="stylesheet" type="text/css" href="assets/datatables.min.css"/>
        <link href="assets/css/style.css" rel="stylesheet">
        <script src="assets/js/jquery-3.2.1.min.js"></script>
        <script src="assets/js/jquery.ui.widget.js"></script>
        <link href="assets/css/colors/default.css" id="theme" rel="stylesheet">
        <link rel="stylesheet" type="text/css" href="assets/css/jquery.dataTables.min.css">
        <style type="text/css">
            .outer-container {
                background: #F0F0F0;
                border: #e0dfdf 1px solid;
                padding: 40px 20px;
                border-radius: 2px;
            }

            .btn-submit {
                background: #333;
                border: #1d1d1d 1px solid;
                border-radius: 2px;
                color: #f0f0f0;
                cursor: pointer;
                padding: 5px 20px;
                font-size:0.9em;
            }

            .tutorial-table {
                margin-top: 40px;
                font-size: 0.8em;
                border-collapse: collapse;
                width: 100%;
            }

            .tutorial-table th {
                background: #f0f0f0;
                border-bottom: 1px solid #dddddd;
                padding: 8px;
                text-align: left;
            }

            .tutorial-table td {
                background: #FFF;
                border-bottom: 1px solid #dddddd;
                padding: 8px;
                text-align: left;
            }

            .outer-scontainer table {
                border-collapse: collapse;
                width: 100%;
            }

            .outer-scontainer th {
                border: 1px solid #dddddd;
                padding: 8px;
                text-align: left;
            }

            .outer-scontainer td {
                border: 1px solid #dddddd;
                padding: 8px;
                text-align: left;
            }

            #response {
                padding: 10px;
                margin-top: 10px;
                border-radius: 2px;
                display:none;
            }

            .success {
                background: #c7efd9;
                border: #bbe2cd 1px solid;
            }

            .error {
                background: #fbcfcf;
                border: #f3c6c7 1px solid;
            }

            div#response.display-block {
                display: block;
            }

            select.form-control:not([size]):not([multiple]) {
                height: calc(2.25rem + 2px);
                width: 199px;
            }

            #price{
                text-align: right !important;
            }

            .field-edit{
                width: 100%;
                display: block;
                display: none;
            }
        </style>
    </head>

    <body class="fix-header fix-sidebar card-no-border">
        <div id="managedeletepopup" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modal-standard-title" aria-hidden="true" style="margin-top:40px;">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="" style="border-bottom:1px solid #e9ecef;padding:4px;padding-left:10px;padding-top:10px;">
                    <div style="float:left;"><h4 class="">Delete <span id="deletetypetoshow"></h4></div>
                    <div style="float:right;padding-top:10px;">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <input type="hidden" name="deletetypeId" id="deletetypeId" />
                        <input type="hidden" name="deletetype" id="deletetype" />
                        <input type="hidden" name="deletetypediv" id="deletetypediv" />
                    </div>
                    <div style="clear:both;"></div>
                    </div>
                    
                    <div class="modal-body text-center">
                        <p>Are you sure want to delete this ?</p>
                    </div>
                    
                    <div class="modal-footer">
                        <div style="float:left;"><button type="button" class="btn btn-default" data-dismiss="modal">Close</button></div>
                        <div style="float:right;"><button type="button" class="btn btn-primary" onClick="funcdeletefrompopup('managedeletepopup');">Delete</button></div>
                        <div style="clear:both;"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="preloader">
            please wait 
            <svg class="circular" viewBox="25 25 50 50">
                <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10" /> 
            </svg>
        </div>
        
        <div id="main-wrapper">
            <header class="topbar">
                <nav class="navbar top-navbar navbar-expand-md navbar-light">
                    <div class="navbar-header">
                        <a class="navbar-brand" href="index.php">
                            <b>
                                
                                <img src="assets/images/logo-light-icon.png" alt="homepage" class="light-logo" />
                            </b>
                            <span>
                                <img src="assets/images/{{Session::get('logo2')}}" alt="homepage" class="dark-logo dark-logo1" />
                                <img src="assets/images/logo-light-text.png" class="light-logo" alt="homepage" />
                            </span> 
                        </a>
                    </div>
                    <div class="navbar-collapse">
                        <ul class="navbar-nav mr-auto mt-md-0">
                            <li class="nav-item"> <a class="nav-link nav-toggler hidden-md-up text-muted waves-effect waves-dark" href="javascript:void(0)"><i class="mdi mdi-menu"></i></a> </li>
                        </ul>
                        <ul class="navbar-nav my-lg-0">
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle text-muted waves-effect waves-dark" href="logout"> <i class="fa fa-power-off"></i> Logout</a>
                            </li>
                        </ul>
                    </div>
                </nav>
            </header>

            <aside class="left-sidebar">
                <!-- Sidebar scroll-->
                <div class="scroll-sidebar">
                    <!-- Sidebar navigation-->
                    <nav class="sidebar-nav">
                        <ul id="sidebarnav">
                            <li class="nav-devider"></li>
                            <li class="nav-small-cap">PERSONAL</li>
                            <li> 
                                <a class="has-arrow waves-effect waves-dark" href="dashboard">
                                    <i class="mdi mdi-gauge"></i>
                                    <span class="hide-menu">Dashboard </span>
                                </a>
                            </li>
                            <li><a href="productView"><i class="mdi mdi-basket-fill"></i><span class="hide-menu">Products</span></a></li>   
                            <li><a href="offlineProductsView"><i class="mdi mdi-basket-fill"></i><span class="hide-menu">Check Offline products</span></a></li>    
                            <li><a href="blacklistView"><i class="mdi mdi-basket-fill"></i><span class="hide-menu">Black list</span></a></li>      
                            <li><a href="createKit"><i class="mdi mdi-stocking"></i>Create Kit</a></li>
                            <li><a href="orderView"><i class="mdi mdi-stocking"></i>Orders</a></li>
                            <li><a href="FBAView"><i class="mdi mdi-group"></i>FBA</a></li>
                            <li><a class="has-arrow waves-effect waves-dark" href="#" aria-expanded="false"><i class="mdi mdi-group"></i><span class="hide-menu">Vendor </span></a>
                                <ul aria-expanded="false" class="collapse">
                                    <li><a href="vendorView">Vendor </a></li>
                                    <li><a href="vendorBlackList">BlackList</a></li>
                                </ul>
                            </li>
                            <li><a href="priceView"><i class="mdi mdi-credit-card-multiple"></i><span class="hide-menu">Price </span></a></li>
                            <li><a href="modelView"><i class="mdi mdi-format-list-bulleted"></i>Models</a></li>
                            <li>
                                <a class="has-arrow waves-effect waves-dark" href="#" aria-expanded="false"><i class="mdi mdi-airplay"></i><span class="hide-menu">Forecast</span></a>
                                <ul aria-expanded="false" class="collapse">
                                    <li><a href="trendView">Trend</a></li>
                                    <li><a href="calculate">Calculate</a></li>
                                    <li><a href="forcastOutput">Show Forecast Output </a></li>
                                </ul>
                            </li>
                            <li> 
                                <a class="has-arrow waves-effect waves-dark" href="#" aria-expanded="false"><i class="mdi mdi-credit-card-multiple"></i><span class="hide-menu">Manufacturer orders</span></a>
                                <ul aria-expanded="false" class="collapse">
                                    <li><a href="manufacturerorderView">New order items</a></li>
                                    <li><a href="containerconfirmView">Confirm arrival</a></li>
                                    <li><a href="warehouseTransferFirstView">Internal transfer</a></li>
                                    <li><a href="warehouseconfirmView">Confirm internal</a></li>
                                </ul>
                            </li>
                            <li> 
                                <a class="has-arrow waves-effect waves-dark" href="#" aria-expanded="false"><i class="mdi mdi-arrange-send-backward"></i><span class="hide-menu">Settings </span></a>
                                <ul aria-expanded="false" class="collapse">
                                    <li><a class="" href="manufacturer">Manufacturer</a></li>
                                    <li><a class="" href="warehouseView">Warehouse</a></li>
                                    <li><a class="" href="channelView">Channel</a></li>
                                    <li><a class="" href="channelCountry">Channel country</a></li>
                                    <li><a class="" href="paymentView">Payment</a></li>
                                    <li><a class="" href="userView">Users</a></li>
                                    <li><a href="platformView">Platform </a></li>
                                    <li><a href="countryView">Country </a></li>
                                    <li><a href="currencyView">Currency</a></li>
                                    <li><a href="companyView">Company Info </a></li>
                                    <li><a href="courierView">Courier</a></li>
                                    <li><a href="vendordepotView">Vendordepot</a></li>
                                    <li><a href="generalSettingView">General</a></li>
                                    <li> 
                                        <a class="has-arrow waves-effect waves-dark" href="#" aria-expanded="false"><span class="hide-menu">Category</span></a>
                                        <ul aria-expanded="false" class="collapse">
                                            <li><a href="categoryView">Main</a></li>
                                            <li><a href="subcategoryView">Subcategory</a></li>
                                        </ul>
                                    </li>
                                </ul>
                            </li>
                            <li><a href="finanzstatusView"><i class="mdi mdi-arrange-send-backward"></i><span class="hide-menu">Finance</span></a></li>
                            <li><a href="inventoryView"><i class="mdi mdi-arrange-send-backward"></i><span class="hide-menu">InventoryView </span></a></li>
                            <li><a href="soldweeklyView"><i class="mdi mdi-arrange-send-backward"></i><span class="hide-menu">Soldweekly </span></a></li>                        
                        </ul>
                    </nav>
                    <!-- End Sidebar navigation -->
                </div>
                <!-- End Sidebar scroll-->
            </aside>

            <div class="page-wrapper">
                <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h3 class="text-themecolor">View FBA</h3>
                    </div>
                    <div class="col-md-7 align-self-center">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                            <li class="breadcrumb-item">pages</li>
                            <li class="breadcrumb-item active">View FBA</li>
                        </ol>
                    </div>
                </div>
                <div class="container-fluid">
                    @if(Session::has('noneProducts'))
                        <?php $noneProducts = Session::get('noneProducts'); ?>
                        @if(isset($noneProducts[0]) && !empty($noneProducts[0]))
                            <div class='alert alert-danger'>
                                <button type='button' class='close' data-dismiss='alert'>&times;</button>
                                @foreach($noneProducts as $rows11)
                                    @foreach($rows11 as $roww)
                                        @if($roww != "")
                                        <i class='fa fa-exclamation-circle'></i> 
                                        Warning: The product {{$roww}} not found in warehouse<br>
                                        @endif
                                    @endforeach
                                @endforeach
                                <?php Session::forget('noneProducts'); ?>
                            </div>
                        @endif
                    @endif
                    @if(Session::has('nonExistingFBAProducts'))
                        <?php $nonExistingFBAProducts = Session::get('nonExistingFBAProducts'); ?>
                        <div class='alert alert-danger'>
                            <button type='button' class='close' data-dismiss='alert'>&times;</button>
                            @foreach($nonExistingFBAProducts as $row)
                                @if($row != "")
                                <i class='fa fa-exclamation-circle'></i> 
                                Warning: The product {{$row}} is not FBA more<br>
                                @endif
                            @endforeach
                            <?php Session::forget('nonExistingFBAProducts'); ?>
                        </div>
                    @endif
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row mb-2">
                                        <div class="col-sm-4">
                                            Check  first if there are item to be delivered to Amazon FBA by clicking here :
                                        </div>
                                        <div class="col-sm-7">
                                            <a href="{{ url('FBAontheway') }}" target="_blank" class="btn btn-info">FBA ON THE WAY</a>
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-sm-4">
                                            Download the actual level of the items in Amazon Warehouses :
                                        </div>
                                        <div class="col-sm-7">
                                            <a href="{{ url('api/fbaQuantityNew.php') }}" target="_blank" class="btn btn-info">CHECK VIA API ACTUAL QUANTITY</a>

                                            <a href="" target="_blank" class="btn btn-info" data-toggle="modal" data-target="#myModalNew">CHECK VIA EXCEL QUANTITY</a>

                                            <a href="https://sellercentral.amazon.de/reportcentral/FBA_MYI_ALL_INVENTORY/1" target="_blank" class="btn btn-info">Link for download file</a>
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-sm-4">
                                            Maybe you can also check the FBA list in the database :
                                        </div>
                                        <div class="col-sm-7">
                                            <a href="{{ url('api/fbaProduct.php') }}" target="_blank" class="btn btn-info">CHECK VIA API Integrity FBA</a>
                                        </div>
                                    </div>
                                    <div class="modal fade" id="myModalNew" role="dialog">
                                            <div class="modal-dialog modal-lg">
                                                <!-- Modal content-->
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title">IMPORT FBA <a href="https://sellercentral.amazon.de/reportcentral/FBA_MYI_ALL_INVENTORY/1" target="_blank" class="btn btn-info">Link for download file</a></h4>
                                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
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
                                                            <form class="form-horizontal" action="importFBAFile" method="post" name="frmCSVImport" id="frmCSVImport" enctype="multipart/form-data">
                                                            {{csrf_field()}}
                                                            <tbody>
                                                                @foreach ($platformsShort as $key => $row)
                                                                <tr>
                                                                    <td>
                                                                        <input type="checkbox" name="check[]" value="{{$row->platformid}}-{{$row->channelId}}" style="display: none;">
                                                                        {{$row->platformname}}
                                                                    </td>
                                                                    <td>{{$row->channelname}}</td>
                                                                    <td><input type="file" name="uploadfilename[]" class="form-control" onchange="checkCheckBox(this)" /></td>
                                                                </tr>
                                                                @endforeach
                                                                <tr>
                                                                    <td colspan="3">
                                                                        <button type="submit" id="submit" name="importsystem" class="btn-submit">Upload Files</button>
                                                                    </td>
                                                                </tr>
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
                                    <?php /* ?>
                                    <div class="col-sm-3">
                                        <input type="button" class="btn btn-info btn-block send_courier" data-toggle="modal" data-target="#myModalNew" value="Download actual level" name="">
                                        <div class="modal fade" id="myModalNew" role="dialog">
                                            <div class="modal-dialog modal-lg">
                                                <!-- Modal content-->
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title">IMPORT FBA</h4>
                                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
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
                                                            <form class="form-horizontal" action="importFBAFile" method="post" name="frmCSVImport" id="frmCSVImport" enctype="multipart/form-data">
                                                            {{csrf_field()}}
                                                            <tbody>
                                                                @foreach ($platformsShort as $key => $row)
                                                                <tr>
                                                                    <td>
                                                                        <input type="checkbox" name="check[]" value="{{$row->platformid}}-{{$row->channelId}}" style="display: none;">
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
                                    </div>
                                    <div class="col-sm-3">
                                        <a href="createExcel" class="btn btn-info btn-block send_courier">Send quantites to FBA</a>
                                    </div>
                                    <div class="col-sm-3">
                                        <input type="button" class="btn btn-info btn-block send_courier" data-toggle="modal" data-target="#myModalNewintegrity" value="Check integrity of FBA" name="">
                                        <div class="modal fade" id="myModalNewintegrity" role="dialog">
                                            <div class="modal-dialog modal-lg">
                                                <!-- Modal content-->
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title">Integrity FBA</h4>
                                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
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
                                                            <form class="form-horizontal" action="integrityFBAFile" method="post" name="integrityCSVImport" id="frmintegrityCSVImport" enctype="multipart/form-data">
                                                            {{csrf_field()}}
                                                            <tbody>
                                                                @foreach ($platformsShort as $key => $row)
                                                                <tr>
                                                                    <td>
                                                                        <input type="checkbox" name="check[]" value="{{$row->platformid}}-{{$row->channelId}}" style="display: none;">
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
                                    </div>
                                    <?php */ ?>
                                    <div class="row mb-2">
                                    <div class="col-xs-12 table-responsive">
                                        <style>
                                            th {
                                                position: sticky;
                                                top: 0;
                                                background-color: white;
                                                border: 1px solid #ddd;
                                            }
                                        </style>
                                        <table id="myTable" class="table table-striped table-bordered" style="width: 100%;">
                                            <thead style="position: sticky; top: 0; background-color: white;">
                                                <th>ID FBA</th>
                                                <th>channel</th>
                                                <th>Active</th>
                                                <th>Quantity</th>
                                                <th>On the way</th>
                                                <th>ProductId</th>
                                                <th>Actual Level (with on the way products)</th>
                                                <th>IdealLevel</th>
                                                <th>Blacklist</th>
                                                <th>Asin</th>
                                                <th>Sku</th>
                                                <th>quantity upload</th>
                                                <th>quantity to send</th>
                                                <th>Comment Blacklist</th>
                                                <th></th>
                                            </thead>
                                            <tbody>
                                                @foreach($rows as $row)
                                                <tr>
                                                    <td>{{$row->idfba}}</td>
                                                    <td>{{$row->shortname}}</td>
                                                    <td style="text-align: center">
                                                        @if($row->active == 1)
                                                        <div style="width: 20px; height: 20px; border-radius: 50%; background: #1bb684; margin: auto;"></div>
                                                        @else 
                                                        <div style="width: 20px; height: 20px; border-radius: 50%; background: red; margin: auto;"></div>
                                                        @endif
                                                    </td>
                                                    <td>{{$row->warehouseQnt}}</td>
                                                    <td>{{$row->ontheway}}</td>
                                                    <td>{{$row->productid}}</td>
                                                    <td>{{intval($row->actuallevel)+intval($row->ontheway)}}</td>
                                                    <td class="td-field">
                                                        <span class="field-value">{{$row->ideallevel}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="ideallevel" class="form-control" value="{{$row->ideallevel}}" data-id="{{$row->idfba}}" data-field="ideallevel">
                                                        </div>
                                                    </td>
                                                    <td class="td-field">
                                                        <span class="field-value"> @if($row->blacklist == 1) Yes @else No @endif</span>
                                                        <div class="field-edit">
                                                            <select name="blacklist" class="form-control" data-id="{{$row->idfba}}" data-field="blacklist">
                                                                <option value="">Select blacklist</option>
                                                                <option value="1" @if($row->blacklist == 1) selected @endif>Yes</option>
                                                                <option value="0" @if($row->blacklist == 0) selected @endif>No</option>
                                                            </select>
                                                        </div>
                                                    </td>
                                                    <td>{{$row->asin}}</td>
                                                    <td>{{$row->sku}}</td>
                                                    @if($row->qnt > 0)
                                                    <td id="id_qnt_{{$row->idfba}}">{{$row->qnt}}</td>
                                                    @else 
                                                    <td id="id_qnt_{{$row->idfba}}">0</td>
                                                    @endif
                                                    <td class="td-field">
                                                        <span class="field-value" id="id_span_qntsend_{{$row->idfba}}">{{$row->quantitytosend}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="quantitytosend" id="id_input_qntsend_{{$row->idfba}}" class="form-control" value="{{$row->quantitytosend}}" data-id="{{$row->idfba}}" data-field="quantitytosend">
                                                        </div>
                                                    </td>
                                                    <td class="td-field">
                                                        <span class="field-value">{{$row->commentblack}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="commentblack" class="form-control" value="{{$row->commentblack}}" data-id="{{$row->idfba}}" data-field="commentblack">
                                                        </div>
                                                    </td>
                                                    <td><a href="fbadelete?del={{$row->idfba}}" class="btn btn-danger">Delete</a></td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-sm-4">
                                            After you check the quantity to ship, click here :
                                        </div>
                                        <div class="col-sm-7">
                                            <a href="createExcel" class="btn btn-info">Send quantites to FBA</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <footer class="footer">
                    ?? 2021 Semplifat powered by Confidence Europe GmbH
                </footer>
            </div>
        </div>

        <script>
            $(document).ready(function() {
                $(document).on("click",".td-field,.dtr-data",function(event) {
                    $(".field-value").show();
                    $('.field-edit').hide();
                    $(this).find(".field-value").hide();
                    $(this).find('.field-edit').show();
                });
                
                $('.field-edit .form-control').change(function(event) {
                    $('.field-edit').hide();
                    var label=$(this).parents(".field-edit").siblings('.field-value');
                    if($(this)[0].nodeName=='SELECT'){
                        label.html($(this).find(":selected").text());
                    } else {
                        label.html($(this).val());
                    }
                    label.show();
                    var field = $(this).data('field');
                    var data={id:$(this).data('id'),field:$(this).data('field'),action:$(this).data('action'),value:$(this).val()};
                    $.ajax({
                        url    : 'fbaUpdate',
                        data   : data,
                        method : 'get',
                        success: function(result) {
                            var response = JSON.parse(result);
                            console.log(response);
                            document.getElementById("id_qnt_"+response.idfba).innerHTML = response.qnt;
                            document.getElementById("id_span_qntsend_"+response.idfba).innerHTML = response.quantitytosend;
                            document.getElementById("id_input_qntsend_"+response.idfba).value = response.quantitytosend;
                        }
                    });
                });
                
                // FOR CATEGORY
                $(document).on("change",".subcat",function(event) {
                    var data={id:$(this).data('id'),field:$(this).data('field'),value:$(this).val()};
                    $.ajax({
                        url: "fbaUpdate",
                        data:data,
                        method:"get",
                        success: function(result) {
                            
                        }
                    });
                });
            });

            function searchFBA(element) {
                var keyword = element.value;
                var data={
                    keyword : keyword
                };

                var url = "FBAView?keyword="+keyword;
                window.location.href = url;
            }

            function checkCheckBox(element) {
                var childNodes = element.parentElement.parentElement.childNodes;
                console.log(childNodes[1].children[0]);
                childNodes[1].children[0].checked = true;
            }

            function importProducts() {
                $(".preloader").fadeOut();
                var form = document.getElementById("frmExcelImport");
                form.submit();
            }
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
    </body>
</html>
                