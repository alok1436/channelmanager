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
                        <h3 class="text-themecolor">Price</h3>
                    </div>
                    <div class="col-md-7 align-self-center">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                            <li class="breadcrumb-item active">Price</li>
                        </ol>
                    </div>
                </div>
                <div class="container-fluid">
                    @if(session()->has('msg'))
                    <div class='alert alert-success'>
                        <i class='fa fa-check-circle'></i> 
                        Success: {{session()->get('msg')}}
                        <button type='button' class='close' data-dismiss='alert'>&times;</button>
                    </div>
                    @endif
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-9" style="padding: 0;">
                                            <select id="channelforcheckprice" class="form-control">
                                                <option value="">Select a channel</option>
                                                @foreach($channels as $channel)
                                                <option value="{{$channel->idchannel}}" platform="{{ $channel->sync }}">{{$channel->shortname}}</option>
                                                @endforeach
                                            </select>
                                            <a onclick="getOnlinePrice()" class="btn btn-info">Check price</a>
                                            <a onclick="getOnlineQuantity()" class="btn btn-info">Check quantity</a>
                                            <a onclick="getWoocommercePriceandQuantity()" class="btn btn-info">Check woocommerce</a>
                                            <button data-toggle="modal" data-target="#myModalNew" class="btn btn-info">Download Prices/Quantity</button>  
                                             <a href="createUploadFiles" class="btn btn-info">Gen. prices/Quantity to Upload</a>
                                            <button data-toggle="modal" data-target="#uploadShippingCosts" class="btn btn-info">Upload shipping costs</button>
                                             <a href="noneexistingproducts" class="btn btn-info">New products to align</a>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="text" name="searchKeyword" id="id_searchKeyword" value="<?php if(isset($_GET['keyword'])) echo $_GET['keyword']; ?>" class="form-control w-50">
                                            <input type="button" value="Search" class="btn btn-danger" onclick="searchPrice()">
                                        </div>
                                    </div>

                                    <div class="modal fade" id="myModalNew" role="dialog">
                                        <div class="modal-dialog modal-lg">
                                            <!-- Modal content-->
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h4 class="modal-title">Download Prices/Quantity From Platform</h4>
                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                </div>
                                                <div class="modal-body">
                                                    <table class="table table-responsive w-100" border="1">
                                                        <thead class="w-100">
                                                            <tr>
                                                                <th>Platform</th>
                                                                <th>Channel</th>
                                                                <th>File</th>
                                                            </tr>
                                                        </thead>
                                                        <form class="form-horizontal" action="importPriceFile" method="post" name="frmCSVImport" id="frmCSVImport" enctype="multipart/form-data">
                                                        {{csrf_field()}}
                                                        <tbody class="w-100">
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
                                   
                                    <div class="modal fade" id="uploadShippingCosts" role="dialog">
                                        <div class="modal-dialog modal-lg">
                                            <!-- Modal content-->
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h4 class="modal-title">Upload shipping costs</h4>
                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                </div>
                                                <div class="modal-body">
                                                    <table class="table table-responsive w-100" border="1">
                                                        <thead class="w-100">
                                                            <tr>
                                                                <th>Platform</th>
                                                                <th>Channel</th>
                                                                <th>File</th>
                                                            </tr>
                                                        </thead>
                                                        <form class="form-horizontal" action="uploadShippingCost" method="post" name="uploadShippingCost" id="uploadShippingCost" enctype="multipart/form-data">
                                                        {{csrf_field()}}
                                                        <tbody class="w-100">
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
                                   

                                    <div class="row">
                                        
                                        <div class="col-md-12 table-responsive">
                                            <style>
                                                th {
                                                    position: sticky;
                                                    top: 0;
                                                    background-color: white;
                                                    border: 1px solid #ddd;
                                                }
                                            </style>
                                            <table id="priceTable" class="table table-striped table-bordered" style="width: 100%;">
                                                <thead style="position: sticky; top: 0; background-color: white;">
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>Code</th>
                                                        <th>Ebay Active</th>
                                                        <th>Model</th>
                                                        <th>Channel</th>
                                                        <th>Warehouse</th>
                                                        <th>Country</th>
                                                        <th>Platform</th>
                                                        <th>Online Price</th>
                                                        <th>Online Shipping</th>
                                                        <th>Last Update Price</th>
                                                        <th>Last Update Shipping</th>
                                                        <th>Price</th>
                                                        <th>Shipping</th>
                                                        <th>Relation</th>
                                                        <th>FBA</th>
                                                        <th>Status Price</th>
                                                        <th>Status Shipping</th>
                                                        <th>Online Quantity</th>
                                                        <th>Last Update Date</th>
                                                        <th>Buffer sell</th>
                                                        <th>Real quantity</th>
                                                        <th>Quantity can sell online</th>
                                                        <th>Status Quantity</th>
                                                        <th>Relation</th>
                                                        <th>SKU</th>
                                                        <th>EAN</th>
                                                        <th>ASIN</th>
                                                        <th>Cost</th>
                                                        <th>Currency</th>
                                                        <th>Shipping costs</th>
                                                        <th>Fees</th>
                                                        <th>VAT</th>
                                                        <th>Gain</th>
                                                        <th>Gain Percent</th>
                                                        <th>Price if 100%</th>
                                                        <th>Shipping if 100%</th>
                                                        <th>Price if Manual</th>
                                                        <th>Shipping if Manual</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php 
                                                        $tempProdArray  = array();
                                                        $cnt            = 0;
                                                    ?>
                                                    @foreach($prices as $prices_data)
                                                        @if (!in_array($prices_data->product_id, $tempProdArray))
                                                            <?php array_push($tempProdArray, $prices_data->product_id); ?>
                                                    <tr>
                                                        <td></td>
                                                        <td>{{$prices_data->productModelcode}}</td>
                                                        <td></td>
                                                        <td>{{$prices_data->productModelcode}}</td>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                        <td>
                                                            <select name="shipp_top_dropdown" id="shipp_top_dropdown" class="form-control price_model" data-id="{{$prices_data->product_id}}">
                                                                <option value=""></option>
                                                                <option value="manual" @if($prices_data->pricevar == "manual") selected="" @endif>Manual</option>
                                                                <option value="auto" @if($prices_data->pricevar == "auto") selected="" @endif>Automatic</option>
                                                            </select>
                                                            <input type="text" class="form-control price_auto" id="id_input_{{$prices_data->product_id}}" data-id="{{$prices_data->product_id}}" value="{{$prices_data->percent}}" 
                                                                style="display:  @if($prices_data->pricevar == 'auto') block @else none @endif;">
                                                        </td>
                                                        <td><button onclick="sendpriceandquantitytoplatform({{$prices_data->product_id}})" class="btn btn-info d-block w-100">Send to platform</button></td>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                        <td>
                                                            @if($prices_data->idfba == null || $prices_data->idfba == "")
                                                            <select name="shipping_model" id="shipping_model_{{$prices_data->product_id}}" class="form-control shipping_model" data-id="{{$prices_data->product_id}}" data-fba="0">
                                                            @else
                                                            <select name="shipping_model" id="shipping_model_{{$prices_data->product_id}}" class="form-control shipping_model" data-id="{{$prices_data->product_id}}" data-fba="1">
                                                            @endif
                                                                <option value="">Select Shipping Model</option>
                                                                @foreach ($modelshipname as $shipnamekey => $shippModelData)
                                                                <option value="{{$shippModelData->idmodelshipname}}" @if($shippModelData->idmodelshipname == $prices_data->shipp_model_id) selected="" @endif>{{$shippModelData->name}}</option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <select name="fees_model" id="fees_model_{{$prices_data->product_id}}" class="form-control fees_model" data-id="{{$prices_data->product_id}}">
                                                                <option value="">Select Fees Model</option>
                                                                @foreach ($modelfeesname as $feesnamekey => $feesModelData)
                                                                <option value="{{$feesModelData->idmodelfeesname}}" @if($feesModelData->idmodelfeesname == $prices_data->fees_model_id) selected="" @endif>{{$feesModelData->name}}</option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <select name="vat_model" id="vat_model_{{$prices_data->product_id}}" class="form-control vat_model" data-id="{{$prices_data->product_id}}">
                                                                <option value="">Select Vat Model</option>
                                                                @foreach ($modelvatname as $vatnamekey => $vatModelData)
                                                                <option value="{{$vatModelData->idmodelvatname}}" @if($vatModelData->idmodelvatname == $prices_data->vat_model_id) selected="" @endif>{{$vatModelData->name}}</option>
                                                                @endforeach
                                                                ?>
                                                            </select>
                                                        </td>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                    </tr>
                                                        @endif
                                                    <tr>
                                                        <td>{{$prices_data->price_id}}</td>
                                                        <td>{{$prices_data->productModelcode}}</td>
                                                        <td>
                                                            @if($prices_data->ebayActive == 1) 
                                                                <div style="width: 20px; height: 20px; border-radius: 50%; background: #1bb684; margin: auto;" class="{{ $prices_data->ebayActive == 1 ? 'ebay_active_cls' : 'ebay_inactive_cls' }}" onclick="return deletePrice({{$prices_data->price_id}},{{$prices_data->ebayActive}})"></div>
                                                            @else
                                                                <div style="width: 20px; height: 20px; border-radius: 50%; background: red; margin: auto;cursor:pointer" class="{{ $prices_data->ebayActive == 1 ? 'ebay_active_cls' : 'ebay_inactive_cls' }}" onclick="return deletePrice({{$prices_data->price_id}},{{$prices_data->ebayActive}})"></div>
                                                            @endif
                                                        </td>
                                                        <td>{{$prices_data->nameshort}}</td>
                                                        <td>{{$prices_data->channelShortname}}</td>
                                                        <td>{{$prices_data->warehouseLocation}}</td>
                                                        <td>{{$prices_data->country}}</td>
                                                        <td>{{$prices_data->platformShortname}}</td>
                                                        <td>{{$prices_data->online_price}}</td>
                                                        <td>{{$prices_data->online_shipping}}</td>
                                                        <td>{{$prices_data->last_update_date}}</td>
                                                        <td>{{$prices_data->last_update_shipping}}</td>
                                                        <td class="td-field">
                                                            <span class="field-value" id="spanprice{{$prices_data->product_id.$prices_data->channel_id.$prices_data->country}}">{{$prices_data->price}}</span>
                                                            <div class="field-edit">
                                                                <input type="text" class="form-control" id="inputprice{{$prices_data->product_id.$prices_data->channel_id.$prices_data->country}}" value="{{$prices_data->price}}" data-product="{{$prices_data->product_id}}"  data-id="{{$prices_data->price_id}}" data-field="price">
                                                            </div>
                                                        </td>
                                                        <td>{{$prices_data->shipping}}</td>
                                                        <td class="td-field">
                                                            <span class="field-value" id="spanrelation{{$prices_data->product_id.$prices_data->channel_id}}">
                                                                @if($prices_data->relation == 1)
                                                                    Yes
                                                                @else 
                                                                    No
                                                                @endif
                                                            </span>
                                                            <div class="field-edit">
                                                                <select name="relation" class="form-control" id="inputrelation{{$prices_data->product_id.$prices_data->channel_id}}" data-product="{{$prices_data->product_id}}"  data-id="{{$prices_data->price_id}}" data-field="relation">
                                                                    <option value="1" @if($prices_data->relation == 1) selected="" @endif>Yes</option>
                                                                    <option value="0" @if($prices_data->relation == 0) selected="" @endif>No</option>
                                                                </select>
                                                            </div>
                                                        </td>
                                                        @if($prices_data->idfba == null || $prices_data->idfba == "")
                                                        <td>No</td>
                                                        @else
                                                        <td>Yes</td>
                                                        @endif
                                                        @if($prices_data->online_price == $prices_data->price)
                                                        <td id="tdstatusprice_{{$prices_data->product_id.$prices_data->channel_id.$prices_data->country}}" style="background-color: lightgreen;">Yes</td>
                                                        @else 
                                                        <td id="tdstatusprice_{{$prices_data->product_id.$prices_data->channel_id.$prices_data->country}}" style="background-color: #ffcccb;">No</td>
                                                        @endif
                                                        <td>{{$prices_data->shipping_status}}</td>
                                                        @if($prices_data->idfba == null || $prices_data->idfba == "")
                                                        <td>{{$prices_data->online_quentity > 0 ? $prices_data->online_quentity : 0}}</td>
                                                        @else
                                                        <td></td>
                                                        @endif
                                                        @if($prices_data->idfba == null || $prices_data->idfba == "")
                                                        <td>{{$prices_data->last_update_qty_date}}</td>
                                                        @else
                                                        <td></td>
                                                        @endif
                                                        @if($prices_data->idfba == null || $prices_data->idfba == "")
                                                        <td>{{$prices_data->indicated_quantity}}</td>
                                                        @else
                                                        <td></td>
                                                        @endif

                                                        <td>{{$prices_data->warehouseQnt}}</td>
                                                        <td>{{$prices_data->can_sell_online}}</td>
                                                        @if($prices_data->idfba == null || $prices_data->idfba == "")
                                                            @if($prices_data->warehouseQnt >= $prices_data->quantity_strategy)
                                                                @if($prices_data->online_quentity == $prices_data->quantity_strategy)
                                                                <td style="background-color: lightgreen;">Yes</td>
                                                                @else 
                                                                <td style="background-color: #ffcccb;">No</td>
                                                                @endif
                                                            @else 
                                                                @if($prices_data->online_quentity == $prices_data->warehouseQnt)
                                                                <td style="background-color: lightgreen;">Yes</td>
                                                                @else 
                                                                <td style="background-color: #ffcccb;">No</td>
                                                                @endif
                                                            @endif
                                                        @else
                                                        <td></td>
                                                        @endif
                                                        <td class="td-field">
                                                            <span class="field-value" id="spanrelation2{{$prices_data->product_id.$prices_data->channel_id}}">
                                                                @foreach ($warehouses as $warehouse)
                                                                    @if($warehouse->idwarehouse == $prices_data->relation2)
                                                                        {{$warehouse->location}}
                                                                    @endif
                                                                @endforeach
                                                            </span>
                                                            <div class="field-edit">
                                                                <select name="relation2" id="id_relation2{{$prices_data->product_id.$prices_data->channel_id}}" class="form-control" data-product="{{$prices_data->product_id}}"  data-id="{{$prices_data->price_id}}" data-field="relation2">
                                                                    <option value="">No relation</option>
                                                                    @foreach ($warehouses as $warehouse)
                                                                        @if($warehouse->idwarehouse != $prices_data->warehouse_id)
                                                                    <option value="{{$warehouse->idwarehouse}}">{{$warehouse->location}}</option>
                                                                        @endif
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </td>
                                                        
                                                        <td class="td-field">
                                                            <span class="field-value">{{$prices_data->sku}}</span>
                                                            <div class="field-edit">
                                                                <input type="text" name="sku" class="form-control" value="{{$prices_data->sku}}" data-id="{{$prices_data->price_id}}" data-field="sku">
                                                            </div>
                                                        </td>

                                                        <td class="td-field">
                                                            <span class="field-value">{{$prices_data->ean}}</span>
                                                            <div class="field-edit">
                                                                <input type="text" name="ean" class="form-control" value="{{$prices_data->ean}}" data-id="{{$prices_data->price_id}}" data-field="ean">
                                                            </div>
                                                        </td>

                                                        <td class="td-field">
                                                            <span class="field-value">{{$prices_data->asin}}</span>
                                                            <div class="field-edit">
                                                                <input type="text" name="asin" class="form-control" value="{{$prices_data->asin}}" data-id="{{$prices_data->price_id}}" data-field="asin">
                                                            </div>
                                                        </td>

                                                        <td>{{$prices_data->productPrice}}</td>
                                                        <td>
                                                            @if($prices_data->channelCountry == "UK")
                                                                GBP
                                                            @else 
                                                                EUR
                                                            @endif
                                                        </td>
                                                        <td class="shipping_{{$prices_data->product_id.$prices_data->warehouse_id.$prices_data->country.$prices_data->fbaFlag}}">
                                                            @if(isset($prices_data->valueship))
                                                                {{$prices_data->valueship}}
                                                            @endif
                                                        </td>
                                                        <td class="fees_{{$prices_data->product_id.$prices_data->channel_id}}">
                                                            @if(isset($prices_data->valuefees))
                                                                {{$prices_data->valuefees}}
                                                            @endif
                                                        </td>
                                                        <td class="vat_{{$prices_data->product_id.$prices_data->country}}">
                                                            @if(isset($prices_data->valuevat))
                                                                {{$prices_data->valuevat}}
                                                            @endif
                                                        </td>
                                                        <td>{{$prices_data->gain}}</td>
                                                        @if($prices_data->gain_percentage != null && $prices_data->gain_percentage < 50)
                                                        <td style="background-color: red; color: black;">{{$prices_data->gain_percentage}}</td>
                                                        @elseif($prices_data->gain_percentage != null && $prices_data->gain_percentage>= 50 && $prices_data->gain_percentage <= 80)
                                                        <td style="background-color: orange; color: black;">{{$prices_data->gain_percentage}}</td>
                                                        @elseif($prices_data->gain_percentage != null && $prices_data->gain_percentage >= 81 && $prices_data->gain_percentage <= 99)
                                                        <td style="background-color: yellow; color: black;">{{$prices_data->gain_percentage}}</td>
                                                        @elseif($prices_data->gain_percentage != null && $prices_data->gain_percentage >= 100 && $prices_data->gain_percentage <= 120)
                                                        <td style="background-color: lightgreen; color: black;">{{$prices_data->gain_percentage}}</td>
                                                        @elseif($prices_data->gain_percentage != null && $prices_data->gain_percentage >= 120)
                                                        <td style="background-color: green; color: black;">{{$prices_data->gain_percentage}}</td>
                                                        @else 
                                                        <td></td>
                                                        @endif
                                                        <td>{{$prices_data->price_if_100_per}}</td>
                                                        <td>{{$prices_data->shipping_if_100_per}}</td>
                                                        <td>{{$prices_data->price_if_manual}}</td>
                                                        <td>{{$prices_data->shipping_if_manual}}</td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    
                                    {{$prices->links()}}
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

        <script>
            function deletePrice(priceId, isactive){
                if(isactive == 0){
                    if(confirm('are you sure, you want to delete?')){
                        location.href= "{{ url('price/delete') }}/"+priceId;   
                    }
                }
            }
            function searchPrice() {
                var keyword = document.getElementById("id_searchKeyword").value;
                window.location.href = 'priceView?keyword='+keyword;
            }

            $(document).ready(function() {
                $("#priceTable").DataTable({
                    fixedHeader: true,
                    scrollY: "900px",
                    scrollX: true,
                    scrollCollapse: true,
                    aaSorting: [],
                    pageLength: 500,
                     
                });

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
                    var data={id:$(this).data('id'),product:$(this).data('product'),field:$(this).data('field'),action:$(this).data('action'),value:$(this).val()};
                    $.ajax({
                        url    : 'priceUpdate',
                        data   : data,
                        method : 'get',
                        success: function(result) {
                            var response = JSON.parse(result);
                            if(response.changeSuccess == 1) {
                                if(response.online_price == response.price) {
                                    document.getElementById("tdstatusprice_"+response.product_id+response.channel_id+response.country).innerHTML = "Yes";
                                    document.getElementById("tdstatusprice_"+response.product_id+response.channel_id+response.country).style.backgroundColor = "lightgreen";
                                } else {
                                    document.getElementById("tdstatusprice_"+response.product_id+response.channel_id+response.country).innerHTML = "No";
                                    document.getElementById("tdstatusprice_"+response.product_id+response.channel_id+response.country).style.backgroundColor = "#ffcccb";
                                }
                            } else {
                                document.getElementById("spanprice"+response.product_id+response.channel_id+response.country).innerHTML  = response.price;
                                document.getElementById("inputprice"+response.product_id+response.channel_id+response.country).value     = response.price;
                                alert("You can not set price for this product manually!");
                            }
                        }
                    });
                });
                
                // FOR CATEGORY
                $(document).on("change",".subcat",function(event) {
                    var data={id:$(this).data('id'),field:$(this).data('field'),value:$(this).val()};
                    $.ajax({
                        url: "productUpdate",
                        data:data,
                        method:"get",
                        success: function(result) {
                            
                        }
                    });
                });
            });

            function getOnlinePrice() {
                var channelforcheckprice = document.getElementById("channelforcheckprice").value;
                var option = $('#channelforcheckprice option:selected', this).attr('platform');
                var element = $("#channelforcheckprice").find('option:selected'); 
                var platform = element.attr("platform"); 
                
                if(channelforcheckprice == 5) {
                    window.open('api/cdiscountprice.php', '_blank');
                }else if(channelforcheckprice == 17) {
                    window.open('otto/get/price?channelId='+channelforcheckprice, '_blank');
                }else if(platform == 'Automatic Synch with: eBay') {
                    window.open('ebay/downloadReport?channelId='+channelforcheckprice, '_blank');
                }else if(platform == 'Automatic Synch with: Woocommerce') {
                    window.open('woo/downloadReport?channelId='+channelforcheckprice, '_blank');
                }else{
                    window.open('api/price.php?channelforcheckprice='+channelforcheckprice, '_blank');
                }
            }

            function getWoocommercePriceandQuantity() {
                window.open('getWoocommercePriceandQuantity', '_blank');
            }

            function getOnlineQuantity() {
                var channelforcheckprice = document.getElementById("channelforcheckprice").value;
                var option = $('#channelforcheckprice option:selected', this).attr('platform');
                var element = $("#channelforcheckprice").find('option:selected'); 
                var platform = element.attr("platform"); 
                
                if(channelforcheckprice == 5) {
                    window.open('api/cdiscountprice.php', '_blank');
                }else if(channelforcheckprice == 17) {
                    window.open('otto/get/quantity?channelId='+channelforcheckprice, '_blank');
                }else if(platform == 'Automatic Synch with: Woocommerce') {
                    window.open('woo/downloadReport?channelId='+channelforcheckprice, '_blank');
                }else {
                    window.open('api/quantity.php?channelforcheckprice='+channelforcheckprice, '_blank');
                }
            }

            function sendpriceandquantitytoplatform(productId) {
                window.open('api/sendpriceandquantitytoplatform.php?productId='+productId, '_blank');
            }

            function importProducts() {
                $(".preloader").fadeOut();
                var form = document.getElementById("frmExcelImport");
                form.submit();
            }

            function checkCheckBox(element) {
                var childNodes = element.parentElement.parentElement.childNodes;
                childNodes[1].children[0].checked = true;
            }

            $(document).on("change", ".price_auto", function(event) {
                var prod_id = $(this).attr("data-id");
                var percent = $(this).val();
                var shipping_model  = document.getElementById("shipping_model_"+prod_id).value;
                var fees_model      = document.getElementById("fees_model_"+prod_id).value;
                var vat_model       = document.getElementById("vat_model_"+prod_id).value;

                if(shipping_model == "") {
                    alert("Please choose shipping model.");
                } else if(fees_model == "") {
                    alert("Please choose fees model.");
                } else if(vat_model == "") {
                    alert("Please choose vat model.");
                } else if(percent!=''){
                    var data = {
                            prod_id         : prod_id,
                            percent         : percent,
                            shipping_model  : shipping_model,
                            fees_model      : fees_model,
                            vat_model       : vat_model
                        };
                        
                    $.ajax({
                        type: 'get',
                        url:  'get_auto_price',
                        data:  data,
                        success: function(resp){
                            var response = JSON.parse(resp);
                            console.log(response);
                            for(var i=0; i<response.length; i++) {
                                if(response[i].price) {
                                    var price = response[i].price;
                                    if(document.getElementById("spanprice"+response[i].product_id+response[i].channel_id+response[i].country)) {
                                        document.getElementById("spanprice"+response[i].product_id+response[i].channel_id+response[i].country).innerHTML = price.toFixed(2);
                                        document.getElementById("inputprice"+response[i].product_id+response[i].channel_id+response[i].country).value = price.toFixed(2);

                                        if(response[i].online_price == response[i].price) {
                                            document.getElementById("tdstatusprice_"+response[i].product_id+response[i].channel_id+response[i].country).innerHTML = "Yes";
                                            document.getElementById("tdstatusprice_"+response[i].product_id+response[i].channel_id+response[i].country).style.backgroundColor = "lightgreen";
                                        } else {
                                            document.getElementById("tdstatusprice_"+response[i].product_id+response[i].channel_id+response[i].country).innerHTML = "No";
                                            document.getElementById("tdstatusprice_"+response[i].product_id+response[i].channel_id+response[i].country).style.backgroundColor = "#ffcccb";
                                        }
                                    }
                                } 
                            }
                        },
                        error: function(resp){
                            console.log(resp.responseText);
                        }
                    });
                }
            });

            $(document).on("change", ".price_model", function(event) {
                var prod_id     = $(this).attr("data-id");
                var price_model = $(this).val();

                if(price_model == "auto") {
                    document.getElementById("id_input_"+prod_id).style.display = "block";
                } else {
                    document.getElementById("id_input_"+prod_id).style.display = "none";
                    var data = {
                            prod_id : prod_id
                        };

                    $.ajax({
                        type: 'get',
                        url:  'get_manual_price',
                        data:  data,
                        success: function(resp){
                            
                        },
                        error: function(resp){
                            console.log(resp.responseText);
                        }
                    });
                }
            });

            $(document).on("change", ".shipping_model", function(event) {
                var shipp_model_id  = $(this).val();
                var prod_id         = $(this).attr("data-id");
                var fba             = $(this).attr("data-fba");

                if(shipp_model_id!=''){
                    var data = {
                            prod_id         : prod_id,
                            shipp_model_id  : shipp_model_id,
                            fba             : fba
                        };
                    console.log(data);
                    $.ajax({
                        type: 'get',
                        url:  'get_shipping_model_data',
                        data:  data,
                        success: function(resp){
                            var response = JSON.parse(resp);
                            if(response.status==1){
                                var prodId = response.prod_id;
                                for (var i=0; i<response.data.length; i++) {
                                    var str = response.data[i].split('@@');
                                    var warehouse       = str[0];
                                    var country         = str[1];
                                    var countryVal      = str[2];
                                    var fba             = str[3];
                                    $('.shipping_'+prodId+warehouse+country+fba).html(countryVal);
                                }
                            }else{
                                alert(resp.msg);
                            }
                        },
                        error: function(resp){
                            console.log(resp.responseText);
                        }
                    });
                }
            });
            
            $(document).on("change", ".fees_model", function(event) {
                var fees_model_id   = $(this).val();
                var prod_id         = $(this).attr("data-id");
                if(fees_model_id!=''){
                    var data ={
                        prod_id         : prod_id,
                        fees_model_id   : fees_model_id
                    };

                    $.ajax({
                        url         : 'get_fees_model_data',
                        data        : data,
                        type        : 'get',
                        dataType    : 'json',
                        success     : function(resp){
                            var response = resp;
                            if(response.status==1){
                                var prodId = response.prod_id;
                                for (var i=0; i<response.data.length; i++) {
                                    var str = response.data[i].split('@@');
                                    var channelId = str[0];
                                    var channelVal =str[1];
                                    var elements = document.getElementsByClassName('fees_'+prodId+channelId);
                                    for(var kk=0; kk<elements.length; kk++) {
                                        elements[kk].innerHTML = channelVal;
                                    }
                                    $('.fees_'+prodId+channelId).html(channelVal);
                                }
                            }else{
                                alert(resp.msg);
                            }
                        }
                    });
                }
            });
            
            $(document).on("change", ".vat_model", function(event) {
                var vat_model_id = $(this).val();
                var prod_id      = $(this).attr("data-id");
                if(vat_model_id!=''){
                    var data  = {
                        prod_id         : prod_id,
                        vat_model_id    : vat_model_id
                    };
                    $.ajax({
                        url         : 'get_vat_model_data',
                        data        : data,
                        type        : 'get',
                        dataType    : 'json',
                        success: function(resp){
                            if(resp.status==1){
                                var prodId = resp.prod_id;
                                for (var i=0; i<resp.data.length; i++) {
                                    var str = resp.data[i].split('@@');
                                    var country = str[0];
                                    var countryVal =str[1];
                                    $('.vat_'+prodId+country).html(countryVal);
                                }
                            }else{
                                alert(resp.msg);
                            }    
                        }
                    });
                }
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
    </body>
</html>
                