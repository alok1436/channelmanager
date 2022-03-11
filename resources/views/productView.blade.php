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
                        <h3 class="text-themecolor">Product list</h3>
                    </div>
                    <div class="col-md-7 align-self-center">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                            <li class="breadcrumb-item">pages</li>
                            <li class="breadcrumb-item active">Product list</li>
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
                                    <div style="width: 100%; height: 45px;">
                                        @if(!Session::has("productViewType"))
                                        <a href="productView?viewType=expert" class="btn btn-info" style="float: right;">Expert section</a>
                                        @else
                                        <a href="productView?viewType=normal" class="btn btn-info">Normal section</a>
                                        @endif
                                        <a href="productAddView" class="btn btn-info mr-3" style="float: left;">Add new product</a>
                                        <a href="productXlsExport" class="btn btn-info mr-3" style="float: left;">Export all products</a>
                                        <form action="productXlsImport" method="POST" id="frmExcelImport" class="t-center" enctype="multipart/form-data" style="float: left;">
                                            {{ csrf_field() }}
                                            <span class="btn btn-info btn-file">
                                                Import all products<input type="file" name="importproductfile" id="id_newMainPhoto" onchange="importProducts()" accept=".xls,.xlsx">
                                            </span>
                                        </form>
                                        <input type="text" name="searchKeyword" id="id_searchKeyword" style="margin-left: 50px;">
                                        <input type="button" value="Search" class="btn btn-danger" onclick="searchProduct()">
                                    </div>
                                    
                                    <div style="width: 100%; height: 45px;">
                                        <select class="form-control" style="float: left;" id="channelforchecknewprouducts">
                                            <option value="">Select a channel</option>
                                            @foreach($channels as $channel)
                                            <option value="{{$channel->idchannel}}">{{$channel->shortname}}</option>
                                            @endforeach
                                        </select>
                                        <a onclick="getNewAmazonProducts()" class="btn btn-info ml-3" style="float: left;">New Amazon products</a>
                                        <a href="getNewWoocommerceProducts" class="btn btn-info ml-3" style="float: left;">New Woocommerce products</a>
                                    </div>
                                    <div class="col-xs-12 table-responsive" style="padding: 0;">
                                    <style>
                                        th {
                                            position: sticky;
                                            top: 0;
                                            background-color: white;
                                            border: 1px solid #ddd;
                                        }
                                    </style>
                                    @if(Session::has("productViewType"))
                                        <table id="myTable" class="table table-striped table-bordered" style="width: 100%;">
                                            <thead style="position: sticky; top: 0; background-color: white;">
                                                <tr>
                                                    <th>Item ID</th>
                                                    <th data-field="sort">Sort level</th>
                                                    <th>Image</th>
                                                    <th></th>
                                                    <th>Code model</th>
                                                    <th>Short name</th>
                                                    <th>Long name</th>
                                                    <th>Price</th>
                                                    <th>Last update</th>
                                                    <th>Category</th>
                                                    <th>Manufacturer</th>
                                                    <th>Code</th>
                                                    <th>Content</th>
                                                    <th>Time order(in Days)</th>
                                                    <th>Selling time range(in Weeks)</th>
                                                    <th>Note</th>
                                                    <th>ASIN</th>
                                                    <th>EAN</th>
                                                    <th>SKU</th>
                                                    <th>Item lenght</th>  
                                                    <th>Item width</th>
                                                    <th>Item height</th>
                                                    <th>Item weight</th>
                                                    <th>Box lenght</th>
                                                    <th>Box width</th>
                                                    <th>Box height</th>
                                                    <th>Box weight</th>
                                                    <th>Box SQM*1000</th>
                                                    <th>Parcel S</th>
                                                    <th>Parcel L</th>
                                                    <th>Parcel XL</th>
                                                    <th>Virtual kit</th>
                                                    <th>PCS 1</th>
                                                    <th>Product ID 1</th>
                                                    <th>PCS 2</th>
                                                    <th>Product ID 2</th>
                                                    <th>PCS 3</th>
                                                    <th>Product ID 3</th>
                                                    <th>PCS 4</th>
                                                    <th>Product ID 4</th>
                                                    <th>PCS 5</th>
                                                    <th>Product ID 5</th>
                                                    <th>PCS 6</th>
                                                    <th>Product ID 6</th>
                                                    <th>PCS 7</th>
                                                    <th>Product ID 7</th>
                                                    <th>PCS 8</th>
                                                    <th>Product ID 8</th>
                                                    <th>PCS 9</th>
                                                    <th>Product ID 9</th>
                                                    <th>Buffer</th>
                                                    <th>Target</th>
                                                    <th>Min sell</th>
                                                    <th>Action</th>
                                                    <th></th>                                              
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($products as $row)
                                                <tr>
                                                    <td>{{$row->productid}}</td>
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->sort}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="sort" class="form-control" value="{{$row->sort}}" data-id="{{$row->productid}}" data-field="sort">
                                                        </div>
                                                    </td>
              
                                                    <td class="td-field">
                                                        @if($row->image!=NULL)
                                                        <img src="storage/images/{{$row->image}}" height="50" width="50">
                                                        @endif
                                                    </td>
              
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->active}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="active" class="form-control" value="{{$row->active}}" data-id="{{$row->productid}}" data-field="active">
                                                        </div>
                                                    </td>
              
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->modelcode}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="modelcode" class="form-control" value="{{$row->modelcode}}" data-id="{{$row->productid}}" data-field="modelcode">
                                                        </div>
                                                    </td>
              
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->nameshort}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="nameshort" class="form-control" value="{{$row->nameshort}}" data-id="{{$row->productid}}" data-field="nameshort">
                                                        </div>
                                                    </td>
              
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->namelong}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="namelong" class="form-control" value="{{$row->namelong}}" data-id="{{$row->productid}}" data-field="namelong">
                                                        </div>
                                                    </td>
                                                    @if($row->virtualkit=="No")
                                                    <td class="td-field" style="text-align: right;">
                                                        <span  class="field-value">{{$row->price}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="price" class="form-control" style="text-align: right;" value="{{$row->price}}" data-id="{{$row->productid}}" data-field="price">
                                                        </div>
                                                    </td>
                                                    <td class="td-field" style="text-align: center;">
                                                        <span  class="field-value">@if($row->dateprice != '0000-00-00') {{$row->dateprice}} @endif</span>
                                                        <div class="field-edit">
                                                            <input type="date" id="datepicker{{$row->productid}}" style="text-align: center;" name="dateprice" class="form-control" value="{{$row->dateprice}}" data-id="{{$row->productid}}" data-field="dateprice" style="line-height: 1;">
                                                        </div>
                                                    </td>
                                                    @else 
                                                    <td></td>
                                                    <td></td>
                                                    @endif
                                                    <td class="td-field" style="display:none;">
                                                        <span class="field-value">{{$row->category}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="category" class="form-control" value="{{$row->category}}" data-id="{{$row->productid}}" data-field="category">
                                                        </div>
                                                    </td>
              
                                                    <td class="td-field" id="cat">
                                                        <select name="subcat" class="form-control subcat" data-id="{{$row->productid}}" data-field="subcat">
                                                            <option value="">Select</option>
                                                            @foreach ($subcategorys as $key => $sc)
                                                                <option value="{{$sc->Namesubcat}}"
                                                                    @if ($sc->Namesubcat==$row->subcat)
                                                                        selected="selected"
                                                                    @endif
                                                                    >
                                                                    {{$sc->Namecat}} - {{$sc->Namesubcat}}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td class="td-field">
                                                        @if($row->virtualkit=="No")
                                                        <span class="field-value">{{$row->manufacturername}}</span>
                                                        <div class="field-edit">
                                                            <select name="manufacturerid" class="form-control" data-id="{{$row->productid}}" data-field="manufacturerid">
                                                                <option value="">Select</option>
                                                                @foreach ($manufacturers as $key => $m)
                                                                    <option value="{{$m->manufacturerid}}"
                                                                        @if ($m->manufacturerid==$row->manufacturerid)
                                                                            selected="selected"
                                                                        @endif
                                                                        >
                                                                        {{$m->shortname}}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        @endif
                                                    </td>
                                                    @if($row->virtualkit=="No")
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->codemanu}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="codemanu" class="form-control" value="{{$row->codemanu}}" data-id="{{$row->productid}}" data-field="codemanu">
                                                        </div>
                                                    </td>
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->content}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="content" class="form-control" value="{{$row->content}}" data-id="{{$row->productid}}" data-field="content">
                                                        </div>
                                                    </td>
                                                    @else
                                                    <td>
                                                        <span  class="field-value">{{$row->codemanu}}</span>
                                                    </td>
                                                    <td>
                                                        <span  class="field-value">{{$row->content}}</span>
                                                    </td>
                                                    @endif
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->ordertime}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="ordertime" class="form-control" value="{{$row->ordertime}}" data-id="{{$row->productid}}" data-field="ordertime">
                                                        </div>
                                                    </td>
                                                    
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->orderrangetime}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="orderrangetime" class="form-control" value="{{$row->orderrangetime}}" data-id="{{$row->productid}}" data-field="orderrangetime">
                                                        </div>
                                                    </td>
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->description}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="description" class="form-control" value="{{$row->description}}" data-id="{{$row->productid}}" data-field="description">
                                                        </div>
                                                    </td>
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->asin}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="asin" class="form-control" value="{{$row->asin}}" data-id="{{$row->productid}}" data-field="asin">
                                                        </div>
                                                    </td>
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->ean}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="ean" class="form-control" value="{{$row->ean}}" data-id="{{$row->productid}}" data-field="ean">
                                                        </div>
                                                    </td>
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->sku}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="sku" class="form-control" value="{{$row->sku}}" data-id="{{$row->productid}}" data-field="sku">
                                                        </div>
                                                    </td>
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->Lengthcm}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="Lengthcm" class="form-control" value="{{$row->Lengthcm}}" data-id="{{$row->productid}}" data-field="Lengthcm">
                                                        </div>
                                                    </td>
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->Widthcm}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="Widthcm" class="form-control" value="{{$row->Widthcm}}" data-id="{{$row->productid}}" data-field="Widthcm">
                                                        </div>
                                                    </td>
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->Heightcm}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="Heightcm" class="form-control" value="{{$row->Heightcm}}" data-id="{{$row->productid}}" data-field="Heightcm">
                                                        </div>
                                                    </td>
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->Weightkg}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="Weightkg" class="form-control" value="{{$row->Weightkg}}" data-id="{{$row->productid}}" data-field="Weightkg">
                                                        </div>
                                                    </td>
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->Lengthcmbox}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="Lengthcmbox" class="form-control" value="{{$row->Lengthcmbox}}" data-id="{{$row->productid}}" data-field="Lengthcmbox">
                                                        </div>
                                                    </td>
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->Widthcmbox}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="Widthcmbox" class="form-control" value="{{$row->Widthcmbox}}" data-id="{{$row->productid}}" data-field="Widthcmbox">
                                                        </div>
                                                    </td>
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->Heightcmbox}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="Heightcmbox" class="form-control" value="{{$row->Heightcmbox}}" data-id="{{$row->productid}}" data-field="Heightcmbox">
                                                        </div>
                                                    </td>
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->Weightkgbox}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="Weightkgbox" class="form-control" value="{{$row->Weightkgbox}}" data-id="{{$row->productid}}" data-field="Weightkgbox">
                                                        </div>
                                                    </td>
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->mq1000box}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="mq1000box" class="form-control" value="{{$row->mq1000box}}" data-id="{{$row->productid}}" data-field="mq1000box">
                                                        </div>
                                                    </td>
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->itemsinpaket1}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="itemsinpaket1" class="form-control" value="{{$row->itemsinpaket1}}" data-id="{{$row->productid}}" data-field="itemsinpaket1">
                                                        </div>
                                                    </td>
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->itemsinpaket2}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="itemsinpaket2" class="form-control" value="{{$row->itemsinpaket2}}" data-id="{{$row->productid}}" data-field="itemsinpaket2">
                                                        </div>
                                                    </td>
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->itemsinpaket3}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="itemsinpaket3" class="form-control" value="{{$row->itemsinpaket3}}" data-id="{{$row->productid}}" data-field="itemsinpaket3">
                                                        </div>
                                                    </td>
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->virtualkit}}</span>
                                                        <div class="field-edit">
                                                            <select name="virtualkit" class="form-control" id="" data-id="{{$row->productid}}" data-field="virtualkit">
                                                                <option value="Yes" @if($row->virtualkit=="Yes") selected @endif>Yes</option>
                                                                <option value="No" @if($row->virtualkit=="No") selected @endif>No</option>
                                                            </select>
                                                        </div>
                                                    </td>
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->pcs1}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="pcs1" class="form-control" value="{{$row->pcs1}}" data-id="{{$row->productid}}" data-field="pcs1">
                                                        </div>
                                                    </td>
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->productid1}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="productid1" class="form-control" value="{{$row->productid1}}" data-id="{{$row->productid}}" data-field="productid1">
                                                        </div>
                                                    </td>
                                                    
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->pcs2}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="pcs2" class="form-control" value="{{$row->pcs2}}" data-id="{{$row->productid}}" data-field="pcs2">
                                                        </div>
                                                    </td>
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->productid2}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="productid2" class="form-control" value="{{$row->productid2}}" data-id="{{$row->productid}}" data-field="productid2">
                                                        </div>
                                                    </td>
                                                    
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->pcs3}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="pcs3" class="form-control" value="{{$row->pcs3}}" data-id="{{$row->productid}}" data-field="pcs3">
                                                        </div>
                                                    </td>
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->productid3}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="productid3" class="form-control" value="{{$row->productid3}}" data-id="{{$row->productid}}" data-field="productid3">
                                                        </div>
                                                    </td>
                                                    
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->pcs4}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="pcs4" class="form-control" value="{{$row->pcs4}}" data-id="{{$row->productid}}" data-field="pcs4">
                                                        </div>
                                                    </td>
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->productid4}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="productid4" class="form-control" value="{{$row->productid4}}" data-id="{{$row->productid}}" data-field="productid4">
                                                        </div>
                                                    </td>
                                                    
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->pcs5}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="pcs5" class="form-control" value="{{$row->pcs5}}" data-id="{{$row->productid}}" data-field="pcs5">
                                                        </div>
                                                    </td>
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->productid5}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="productid5" class="form-control" value="{{$row->productid5}}" data-id="{{$row->productid}}" data-field="productid5">
                                                        </div>
                                                    </td>
                                                    
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->pcs6}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="pcs6" class="form-control" value="{{$row->pcs6}}" data-id="{{$row->productid}}" data-field="pcs6">
                                                        </div>
                                                    </td>
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->productid6}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="productid6" class="form-control" value="{{$row->productid6}}" data-id="{{$row->productid}}" data-field="productid6">
                                                        </div>
                                                    </td>
                                                    
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->pcs7}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="pcs7" class="form-control" value="{{$row->pcs7}}" data-id="{{$row->productid}}" data-field="pcs7">
                                                        </div>
                                                    </td>
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->productid7}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="productid7" class="form-control" value="{{$row->productid7}}" data-id="{{$row->productid}}" data-field="productid7">
                                                        </div>
                                                    </td>
                                                    
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->pcs8}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="pcs8" class="form-control" value="{{$row->pcs8}}" data-id="{{$row->productid}}" data-field="pcs8">
                                                        </div>
                                                    </td>
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->productid8}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="productid8" class="form-control" value="{{$row->productid8}}" data-id="{{$row->productid}}" data-field="productid8">
                                                        </div>
                                                    </td>
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->pcs9}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="pcs9" class="form-control" value="{{$row->pcs9}}" data-id="{{$row->productid}}" data-field="pcs9">
                                                        </div>
                                                    </td>
                                                    
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->productid9}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="productid9" class="form-control" value="{{$row->productid9}}" data-id="{{$row->productid}}" data-field="productid9">
                                                        </div>
                                                    </td>
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->buffer}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="buffer" class="form-control" value="{{$row->buffer}}" data-id="{{$row->productid}}" data-field="buffer">
                                                        </div>
                                                    </td>
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->target}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="target" class="form-control" value="{{$row->target}}" data-id="{{$row->productid}}" data-field="target">
                                                        </div>
                                                    </td>
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->min_sell}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="min_sell" class="form-control" value="{{$row->min_sell}}" data-id="{{$row->productid}}" data-field="min_sell">
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <a href="productDelete?del={{$row->productid}}" class="btn btn-danger">Delete</a>
                                                        <a href="productEditView?productId={{$row->productid}}" class="btn btn-success">Edit</a>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                        {{$products->links()}}
                                    @else
                                        <style>
                                            #id_order_normal_tbl {
                                                table-layout:fixed;
                                            }

                                            #id_order_normal_tbl th {
                                                color: white;
                                                background: darkblue;
                                                word-wrap: break-word;
                                            }

                                            #id_order_normal_tbl td {
                                                word-wrap: break-word !important;
                                                padding: 3px;
                                                font-size: 12px;
                                                word-break: break-all;
                                            }

                                            #id_order_normal_tbl span {
                                                display: inline;
                                            }

                                            #id_order_normal_tbl select {
                                                width:100% !important;
                                            }

                                            #id_order_normal_tbl input {
                                                width:100% !important;
                                            }

                                            #id_order_normal_tbl a {
                                                width:100% !important;
                                            }

                                            .dropdown-toggle::after {
                                                display : none;
                                            }
                                        </style>    

                                        <table class="table table-bordered" id="id_order_normal_tbl" style="width: 100%;">
                                            <thead>
                                                <tr>
                                                    <th rowspan="4">Photo</th>
                                                    <th rowspan="4"></th>
                                                    <th>Code model</th>
                                                    <th>Asin</th>
                                                    <th rowspan="4">Price</th>
                                                    <th rowspan="4">Update</th>
                                                    <th>Manufacturer</th>
                                                    <th rowspan="2">Time order (in days)</th>
                                                    <th>Category</th>
                                                    <th>Item Length</th>
                                                    <th>Box Length</th>
                                                    <th>Parcel S</th>
                                                    <th>Buffer</th>
                                                    <th rowspan="4">Virtual Kit</th>
                                                    <th rowspan="4">Action</th>                                                    
                                                </tr>
                                                <tr>
                                                    <th>Short name</th>
                                                    <th>EAN</th>
                                                    <th>Code</th>
                                                    <th>Note</th>
                                                    <th>Width</th>
                                                    <th>Width</th>
                                                    <th>L</th>
                                                    <th>Target</th>
                                                </tr>
                                                <tr>
                                                    <th>Long name</th>
                                                    <th>SKU</th>
                                                    <th>Content</th>
                                                    <th>Selling time range(in weeks)</th>
                                                    <th></th>
                                                    <th>Height</th>
                                                    <th>Height</th>
                                                    <th>XL</th>
                                                    <th>MinSell</th>
                                                </tr>
                                                <tr>
                                                    <th></th>
                                                    <th>SKU_CD</th>
                                                    <th></th>
                                                    <th></th>
                                                    <th></th>
                                                    <th>Weight</th>
                                                    <th>Weight</th>
                                                    <th>Box SQM</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody id="ssss"> 
                                                @foreach($products as $row)
                                                <tr>
                                                    <td rowspan="4">
                                                        @if($row->image!=NULL)
                                                        <img src="storage/images/{{$row->image}}" height="50" width="50">
                                                        @endif
                                                    </td>
                                                    <td rowspan="4" class="td-field">
                                                        <div class="dropdown">
                                                            <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenu2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="border: none; box-shadow: none;">
                                                            @if($row->active == "Yes")
                                                                <div style="width: 30px; height: 30px; background: green; border-radius: 50%; margin: auto;" id="id_show_active_{{$row->productid}}"></div>
                                                            @else 
                                                                <div style="width: 30px; height: 30px; background: red; border-radius: 50%; margin: auto;" id="id_show_active_{{$row->productid}}"></div>
                                                            @endif
                                                            </button>
                                                            <div class="dropdown-menu" aria-labelledby="dropdownMenu2">
                                                                <button class="dropdown-item" data-id="{{$row->productid}}" data-field="active" data-value="Yes" type="button" onclick="activateProduct(this)"><div style="width: 30px; height: 30px; background: green; border-radius: 50%; margin: auto;"></div></button>
                                                                <button class="dropdown-item" data-id="{{$row->productid}}" data-field="active" data-value="No" type="button" onclick="activateProduct(this)"><div style="width: 30px; height: 30px; background: red; border-radius: 50%; margin: auto;"></div></button>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->modelcode}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="modelcode" class="form-control" value="{{$row->modelcode}}" data-id="{{$row->productid}}" data-field="modelcode">
                                                        </div>
                                                    </td>
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->asin}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="asin" class="form-control" value="{{$row->asin}}" data-id="{{$row->productid}}" data-field="asin">
                                                        </div>
                                                    </td>
                                                    @if($row->virtualkit=="No")
                                                    <td rowspan="4" class="td-field" style="text-align: right;">
                                                        <span  class="field-value">{{$row->price}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="price" class="form-control" style="text-align: right;" value="{{$row->price}}" data-id="{{$row->productid}}" data-field="price">
                                                        </div>
                                                    </td>
                                                    <td rowspan="4" class="td-field" style="text-align: center;">
                                                        <span  class="field-value">@if($row->dateprice != '0000-00-00') {{$row->dateprice}} @endif</span>
                                                        <div class="field-edit">
                                                            <input type="date" id="datepicker{{$row->productid}}" style="text-align: center;" name="dateprice" class="form-control" value="{{$row->dateprice}}" data-id="{{$row->productid}}" data-field="dateprice" style="line-height: 1;">
                                                        </div>
                                                    </td>
                                                    @else 
                                                    <td rowspan="4"></td>
                                                    <td rowspan="4"></td>
                                                    @endif
                                                    <td class="td-field">
                                                        @if($row->virtualkit=="No")
                                                        <span class="field-value">{{$row->manufacturername}}</span>
                                                        <div class="field-edit">
                                                            <select name="manufacturerid" class="form-control" data-id="{{$row->productid}}" data-field="manufacturerid">
                                                                <option value="">Select</option>
                                                                @foreach ($manufacturers as $key => $m)
                                                                    <option value="{{$m->manufacturerid}}"
                                                                        @if ($m->manufacturerid==$row->manufacturerid)
                                                                            selected="selected"
                                                                        @endif
                                                                        >
                                                                        {{$m->shortname}}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        @endif
                                                    </td>
                                                    <td class="td-field" rowspan="2">
                                                        <span  class="field-value">{{$row->ordertime}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="ordertime" class="form-control" value="{{$row->ordertime}}" data-id="{{$row->productid}}" data-field="ordertime">
                                                        </div>
                                                    </td>
                                                    <td class="td-field" id="cat">
                                                        <select name="subcat" class="form-control subcat" data-id="{{$row->productid}}" data-field="subcat">
                                                            <option value="">Select</option>
                                                            @foreach ($subcategorys as $key => $sc)
                                                                <option value="{{$sc->Namesubcat}}"
                                                                    @if ($sc->Namesubcat==$row->subcat)
                                                                        selected="selected"
                                                                    @endif
                                                                    >
                                                                    {{$sc->Namecat}} - {{$sc->Namesubcat}}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->Lengthcm}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="Lengthcm" class="form-control" value="{{$row->Lengthcm}}" data-id="{{$row->productid}}" data-field="Lengthcm">
                                                        </div>
                                                    </td>
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->Lengthcmbox}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="Lengthcmbox" class="form-control" value="{{$row->Lengthcmbox}}" data-id="{{$row->productid}}" data-field="Lengthcmbox">
                                                        </div>
                                                    </td>
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->itemsinpaket1}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="itemsinpaket1" class="form-control" value="{{$row->itemsinpaket1}}" data-id="{{$row->productid}}" data-field="itemsinpaket1">
                                                        </div>
                                                    </td>
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->buffer}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="buffer" class="form-control" value="{{$row->buffer}}" data-id="{{$row->productid}}" data-field="buffer">
                                                        </div>
                                                    </td>
                                                    <td class="td-field" rowspan="4">
                                                        <span  class="field-value">{{$row->virtualkit}}</span>
                                                        <div class="field-edit">
                                                            <select name="virtualkit" class="form-control" id="" data-id="{{$row->productid}}" data-field="virtualkit">
                                                                <option value="Yes" @if($row->virtualkit=="Yes") selected @endif>Yes</option>
                                                                <option value="No" @if($row->virtualkit=="No") selected @endif>No</option>
                                                            </select>
                                                        </div>

                                                        @if($row->virtualkit == "Yes")
                                                            <button class="btn btn-success" data-toggle="modal" data-target="#modalforeditkit_{{$row->productid}}">Create Kit</button>

                                                            <div class="modal fade" id="modalforeditkit_{{$row->productid}}" role="dialog" tabindex="-1">
                                                                <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                                                                    <div class="modal-content">
                                                                        <div class="modal-body">                                                                                
                                                                            <div class="row">
                                                                                <div class="col-6">
                                                                                    <div class="form-group field-kit" style="display: block;">
                                                                                        <label for="">PCS 1</label>
                                                                                        <input type="text" name="pcs1" class="form-control" value="{{$row->pcs1}}" data-id="{{$row->productid}}" data-field="pcs1">
                                                                                    </div>

                                                                                    <div class="form-group field-kit" style="display: block;">
                                                                                        <label for="">PCS 2</label>
                                                                                        <input type="text" name="pcs2" class="form-control" value="{{$row->pcs2}}" data-id="{{$row->productid}}" data-field="pcs2">
                                                                                    </div>

                                                                                    <div class="form-group field-kit" style="display: block;">
                                                                                        <label for="">PCS 3</label>
                                                                                        <input type="text" name="pcs3" class="form-control" value="{{$row->pcs3}}" data-id="{{$row->productid}}" data-field="pcs3">
                                                                                    </div>

                                                                                    <div class="form-group field-kit" style="display: block;">
                                                                                        <label for="">PCS 4</label>
                                                                                        <input type="text" name="pcs4" class="form-control" value="{{$row->pcs4}}" data-id="{{$row->productid}}" data-field="pcs4">
                                                                                    </div>

                                                                                    <div class="form-group field-kit" style="display: block;">
                                                                                        <label for="">PCS 5</label>
                                                                                        <input type="text" name="pcs5" class="form-control" value="{{$row->pcs5}}" data-id="{{$row->productid}}" data-field="pcs5">
                                                                                    </div>

                                                                                    <div class="form-group field-kit" style="display: block;">
                                                                                        <label for="">PCS 6</label>
                                                                                        <input type="text" name="pcs6" class="form-control" value="{{$row->pcs6}}" data-id="{{$row->productid}}" data-field="pcs6">
                                                                                    </div>

                                                                                    <div class="form-group field-kit" style="display: block;">
                                                                                        <label for="">PCS 7</label>
                                                                                        <input type="text" name="pcs7" class="form-control" value="{{$row->pcs7}}" data-id="{{$row->productid}}" data-field="pcs7">
                                                                                    </div>

                                                                                    <div class="form-group field-kit" style="display: block;">
                                                                                        <label for="">PCS 8</label>
                                                                                        <input type="text" name="pcs8" class="form-control" value="{{$row->pcs8}}" data-id="{{$row->productid}}" data-field="pcs8">
                                                                                    </div>

                                                                                    <div class="form-group field-kit" style="display: block;">
                                                                                        <label for="">PCS 9</label>
                                                                                        <input type="text" name="pcs9" class="form-control" value="{{$row->pcs9}}" data-id="{{$row->productid}}" data-field="pcs9">
                                                                                    </div>
                                                                                </div>

                                                                                <div class="col-6">
                                                                                    <div class="form-group field-kit" style="display: block;">
                                                                                        <label for="">Product ID 1</label>
                                                                                        <input type="text" name="productid1" class="form-control" value="{{$row->productid1}}" data-id="{{$row->productid}}" data-field="productid1">
                                                                                    </div>

                                                                                    <div class="form-group field-kit" style="display: block;">
                                                                                        <label for="">Product ID 2</label>
                                                                                        <input type="text" name="productid2" class="form-control" value="{{$row->productid2}}" data-id="{{$row->productid}}" data-field="productid2">
                                                                                    </div>

                                                                                    <div class="form-group field-kit" style="display: block;">
                                                                                        <label for="">Product ID 3</label>
                                                                                        <input type="text" name="productid3" class="form-control" value="{{$row->productid3}}" data-id="{{$row->productid}}" data-field="productid3">
                                                                                    </div>

                                                                                    <div class="form-group field-kit" style="display: block;">
                                                                                        <label for="">Product ID 4</label>
                                                                                        <input type="text" name="productid4" class="form-control" value="{{$row->productid4}}" data-id="{{$row->productid}}" data-field="productid4">
                                                                                    </div>

                                                                                    <div class="form-group field-kit" style="display: block;">
                                                                                        <label for="">Product ID 5</label>
                                                                                        <input type="text" name="productid5" class="form-control" value="{{$row->productid5}}" data-id="{{$row->productid}}" data-field="productid5">
                                                                                    </div>

                                                                                    <div class="form-group field-kit" style="display: block;">
                                                                                        <label for="">Product ID 6</label>
                                                                                        <input type="text" name="productid6" class="form-control" value="{{$row->productid6}}" data-id="{{$row->productid}}" data-field="productid6">
                                                                                    </div>

                                                                                    <div class="form-group field-kit" style="display: block;">
                                                                                        <label for="">Product ID 7</label>
                                                                                        <input type="text" name="productid7" class="form-control" value="{{$row->productid7}}" data-id="{{$row->productid}}" data-field="productid7">
                                                                                    </div>

                                                                                    <div class="form-group field-kit" style="display: block;">
                                                                                        <label for="">Product ID 8</label>
                                                                                        <input type="text" name="productid8" class="form-control" value="{{$row->productid8}}" data-id="{{$row->productid}}" data-field="productid8">
                                                                                    </div>

                                                                                    <div class="form-group field-kit" style="display: block;">
                                                                                        <label for="">Product ID 9</label>
                                                                                        <input type="text" name="productid9" class="form-control" value="{{$row->productid9}}" data-id="{{$row->productid}}" data-field="productid9">
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="modal-footer">
                                                                            <button type="button" class="btn btn-default closeprintdoc" data-dismiss="modal">Close</button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td rowspan="4">
                                                        <a href="productDelete?del={{$row->productid}}" class="btn btn-danger">Delete</a>
                                                        <a href="productEditView?productId={{$row->productid}}" class="btn btn-success">Edit</a>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->nameshort}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="nameshort" class="form-control" value="{{$row->nameshort}}" data-id="{{$row->productid}}" data-field="nameshort">
                                                        </div>
                                                    </td>
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->ean}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="ean" class="form-control" value="{{$row->ean}}" data-id="{{$row->productid}}" data-field="ean">
                                                        </div>
                                                    </td>
                                                    @if($row->virtualkit=="No")
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->codemanu}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="codemanu" class="form-control" value="{{$row->codemanu}}" data-id="{{$row->productid}}" data-field="codemanu">
                                                        </div>
                                                    </td>
                                                    @else
                                                    <td>
                                                        <span  class="field-value">{{$row->codemanu}}</span>
                                                    </td>
                                                    @endif
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->description}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="description" class="form-control" value="{{$row->description}}" data-id="{{$row->productid}}" data-field="description">
                                                        </div>
                                                    </td>
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->Widthcm}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="Widthcm" class="form-control" value="{{$row->Widthcm}}" data-id="{{$row->productid}}" data-field="Widthcm">
                                                        </div>
                                                    </td>
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->Widthcmbox}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="Widthcmbox" class="form-control" value="{{$row->Widthcmbox}}" data-id="{{$row->productid}}" data-field="Widthcmbox">
                                                        </div>
                                                    </td>
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->itemsinpaket2}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="itemsinpaket2" class="form-control" value="{{$row->itemsinpaket2}}" data-id="{{$row->productid}}" data-field="itemsinpaket2">
                                                        </div>
                                                    </td>
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->target}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="target" class="form-control" value="{{$row->target}}" data-id="{{$row->productid}}" data-field="target">
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->namelong}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="namelong" class="form-control" value="{{$row->namelong}}" data-id="{{$row->productid}}" data-field="namelong">
                                                        </div>
                                                    </td>
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->sku}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="sku" class="form-control" value="{{$row->sku}}" data-id="{{$row->productid}}" data-field="sku">
                                                        </div>
                                                    </td>
                                                    @if($row->virtualkit=="No")
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->content}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="content" class="form-control" value="{{$row->content}}" data-id="{{$row->productid}}" data-field="content">
                                                        </div>
                                                    </td>
                                                    @else
                                                    <td>
                                                        <span  class="field-value">{{$row->content}}</span>
                                                    </td>
                                                    @endif
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->orderrangetime}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="orderrangetime" class="form-control" value="{{$row->orderrangetime}}" data-id="{{$row->productid}}" data-field="orderrangetime">
                                                        </div>
                                                    </td>
                                                    <td></td>
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->Heightcm}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="Heightcm" class="form-control" value="{{$row->Heightcm}}" data-id="{{$row->productid}}" data-field="Heightcm">
                                                        </div>
                                                    </td>
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->Heightcmbox}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="Heightcmbox" class="form-control" value="{{$row->Heightcmbox}}" data-id="{{$row->productid}}" data-field="Heightcmbox">
                                                        </div>
                                                    </td>
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->itemsinpaket3}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="itemsinpaket3" class="form-control" value="{{$row->itemsinpaket3}}" data-id="{{$row->productid}}" data-field="itemsinpaket3">
                                                        </div>
                                                    </td>
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->min_sell}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="min_sell" class="form-control" value="{{$row->min_sell}}" data-id="{{$row->productid}}" data-field="min_sell">
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td></td>
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->sku_cd}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="sku_cd" class="form-control" value="{{$row->sku_cd}}" data-id="{{$row->productid}}" data-field="sku_cd">
                                                        </div>
                                                    </td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->Weightkg}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="Weightkg" class="form-control" value="{{$row->Weightkg}}" data-id="{{$row->productid}}" data-field="Weightkg">
                                                        </div>
                                                    </td>
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->Weightkgbox}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="Weightkgbox" class="form-control" value="{{$row->Weightkgbox}}" data-id="{{$row->productid}}" data-field="Weightkgbox">
                                                        </div>
                                                    </td>
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->mq1000box}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="mq1000box" class="form-control" value="{{$row->mq1000box}}" data-id="{{$row->productid}}" data-field="mq1000box">
                                                        </div>
                                                    </td>
                                                    <td></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="15" style="height: 15px;"></td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table> 
                                        {{$products->links()}}
                                    @endif
                                    </div>
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
                    var field   = $(this).data('field');
                    var data    ={id:$(this).data('id'),field:$(this).data('field'),action:$(this).data('action'),value:$(this).val()};
                    $.ajax({
                        url    : 'productUpdate',
                        data   : data,
                        method : 'get',
                        success: function(result) {
                            if(field == "active") {
                                location.reload();
                            }
                        }
                    });
                });

                $('.field-kit .form-control').change(function(event) {
                    $('.field-edit').hide();
                    var label=$(this).parents(".field-edit").siblings('.field-value');
                    if($(this)[0].nodeName=='SELECT'){
                        label.html($(this).find(":selected").text());
                    } else {
                        label.html($(this).val());
                    }
                    label.show();
                    var data={id:$(this).data('id'),field:$(this).data('field'),action:$(this).data('action'),value:$(this).val()};
                    $.ajax({
                        url    : 'productUpdate',
                        data   : data,
                        method : 'get',
                        success: function(result) {
                            
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

            function activateProduct(element) {
                var data    ={id:$(element).data('id'),field: $(element).data('field'), action:$(element).data('action'), value:$(element).data('value')};
                $.ajax({
                    url    : 'productUpdate',
                    data   : data,
                    method : 'get',
                    success: function(result) {
                        if($(element).data('value') == "Yes") {
                            $("#id_show_active_"+$(element).data('id')).css("background", "green");
                        } else {
                            $("#id_show_active_"+$(element).data('id')).css("background", "red");
                        }
                    }
                });
            }

            function searchProduct() {
                var keyword = document.getElementById("id_searchKeyword").value;
                window.location.href = 'productView?keyword='+keyword;
            }

            function getNewAmazonProducts() {
                var channelforchecknewprouducts = document.getElementById("channelforchecknewprouducts").value;
                window.open('api/product.php?channelforchecknewprouducts='+channelforchecknewprouducts, '_blank');
            }

            function importProducts() {
                $(".preloader").fadeOut();
                var form = document.getElementById("frmExcelImport");
                form.submit();
            }

            $("th").click(function(){
                console.log($(this).attr('class'));
                console.log($(this).attr('aria-label'));
                var label = $(this).attr('aria-label');
                var sort = "";
                var sortItem = "";
                if($(this).attr('aria-label') == "Item ID: activate to sort column descending") {
                    sortItem = "productid";
                } else if($(this).attr('aria-label') == "Sort level: activate to sort column ascending") {
                    sortItem = "sort";
                } else if($(this).attr('aria-label') == "Code model: activate to sort column ascending") {
                    sortItem = "modelcode";
                } else if($(this).attr('aria-label') == "Category: activate to sort column ascending") {
                    sortItem = "category";
                } else if($(this).attr('aria-label') == "Manufacturer: activate to sort column ascending") {
                    sortItem = "manufacturerid";
                } else if($(this).attr('aria-label') == "Short name: activate to sort column ascending") {
                    sortItem = "nameshort";
                }
                @if(isset($_GET['sort']))
                    @if($_GET['sort']=="desc")
                    sort= "asc";
                    @else
                    sort= "desc";
                    @endif
                @else
                    sort="desc";
                @endif

                var url = "productView?sort="+sort+"&sortItem="+sortItem;
                window.location.href = url;
                
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
                