<!DOCTYPE html>
<html lang="en">
    <head>
        <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
        <meta http-equiv='Content-Type' content='text/html; charset=windows-1256'/>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="">
        <link rel="icon" type="image/png" sizes="16x16" href="assets/images/favicon.png">
        <title>Admin - Panel </title>
        <link href="assets/plugins/bootstrap-switch/bootstrap-switch.min.css" rel="stylesheet">
        <link href="assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">
        <link href="assets/plugins/morrisjs/morris.css" rel="stylesheet">
        <link rel="stylesheet" type="text/css" href="assets/css/jquery.dataTables.min.css">
        <link href="assets/css/style.css" rel="stylesheet">
        <script src="assets/js/jquery-3.2.1.min.js"></script>
        <link href="assets/css/colors/default.css" id="theme" rel="stylesheet">
        <link rel="stylesheet" type="text/css" href="assets/js/jquery.form-validator.min.js">

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
            
        </style>
    </head>

    <body class="fix-header fix-sidebar card-no-border">
        <div class="preloader">
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
                        <h3 class="text-themecolor">Edit product</h3>
                    </div>
                    <div class="col-md-7 align-self-center">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                            <li class="breadcrumb-item">pages</li>
                            <li class="breadcrumb-item active">Product</li>
                        </ol>
                    </div>
                </div>
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <form method="post" enctype="multipart/form-data" action="productEdit">
                                        {{ csrf_field() }}
                                        <div class="row">
                                            <div class="col-md-5 col-sm-12">
                                                <div class="form-group">
                                                    <label>Sort level</label>
                                                    <input type="hidden" name="productIdhidden" value="{{$editproduct->productid}}">
                                                    <input type="text" name="sort" class="form-control" value="{{$editproduct->sort}}"/>
                                                </div>
                                                <div class="form-group">
                                                    <label>Item active:</label>
                                                    <div class="bt-switch mb-10" style="width:140px;">
                                                        <input type="checkbox" name="active" checked data-on-color="info" value="1" data-off-color="success" @if($editproduct->active=='Yes') checked @endif>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label>Code model</label>
                                                    <input type="number" name="modelcode" class="form-control" value="{{$editproduct->modelcode}}"/>
                                                </div>
                                                <div class="form-group">
                                                    <label>Short name</label>
                                                    <input type="text" name="nameshort" class="form-control" value="{{$editproduct->nameshort}}"/>
                                                </div>
                                                <div class="form-group">
                                                    <label>Long name</label>
                                                    <input type="text" name="namelong" class="form-control" value="{{$editproduct->namelong}}"/>
                                                </div>
                                                
                                                <div class="form-group">
                                                    <label>Buffer</label>
                                                    <input type="number" name="buffer" class="form-control" value="{{$editproduct->buffer}}" placeholder="Insert the quantity to have as buffer"/>
                                                </div>
                                                <div class="form-group">
                                                    <label>Target</label>
                                                    <input type="number" name="target" class="form-control" value="{{$editproduct->target}}" placeholder="Insert the wish quantity to sell every month"/>
                                                </div>
                                                <div class="form-group">
                                                    <label>Category</label>
                                                    <select class="form-control" name="subcat" class="form-control">
                                                        <option value="">Select category</option>
                                                        @foreach($subcategorys as $key => $row)
                                                        <option value="{{$row->Namesubcat}}" @if($editproduct->subcat ==$row->Namesubcat) selected="selected" @endif>{{$row->Namesubcat}}</option>
                                                        @endforeach
                                                    </select>  
                                                </div><br>
                                                <div class="form-group">
                                                    <label>Time for order (in days)</label>
                                                    <input type="number" name="ordertime" class="form-control" placeholder="Insert time need to get the order (in days)" value="{{$editproduct->ordertime}}" />
                                                </div>
                                                <div class="form-group">
                                                    <label>Selling time range (in weeks)</label>
                                                    <input type="number" name="orderrangetime" class="form-control" placeholder="Insert time range to sell the products (in weeks)" value="{{$editproduct->orderrangetime}}" />
                                                </div>
                                                <div class="form-group">
                                                    <label>Notes</label>
                                                    <textarea name="description" class="form-control" rows="6" placeholder="Insert a comment for the product">{{$editproduct->description}}</textarea>
                                                </div>
                                            </div>
                                            <!-- col-5 -->
                                            <div class="col-md-7 col-sm-12">
                                                <fieldset>
                                                    <table class="table table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th>ASIN</th>
                                                                <th>EAN</th>
                                                                <th>SKU</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td><input type="text" name="asin" class="form-control" placeholder="Enter ASIN" value="{{$editproduct->asin}}"></td>
                                                                <td><input type="text" name="ean" class="form-control" placeholder="Enter EAN" value="{{$editproduct->ean}}"></td>
                                                                <td><input type="text" name="sku" class="form-control" placeholder="Enter SKU" value="{{$editproduct->sku}}"></td>
                                                            </tr>
                                                            <tr>
                                                                <td><input type="text" name="asin2" class="form-control" placeholder="Enter ASIN" value="{{$editproduct->asin2}}"></td>
                                                                <td><input type="text" name="ean2" class="form-control" placeholder="Enter EAN" value="{{$editproduct->ean2}}"></td>
                                                                <td><input type="text" name="sku2" class="form-control" placeholder="Enter SKU" value="{{$editproduct->sku2}}"></td>
                                                            </tr>
                                                                <td><input type="text" name="asin3" class="form-control" placeholder="Enter ASIN" value="{{$editproduct->asin3}}"></td>
                                                                <td><input type="text" name="ean3" class="form-control" placeholder="Enter EAN" value="{{$editproduct->ean3}}"></td>
                                                                <td><input type="text" name="sku3" class="form-control" placeholder="Enter SKU" value="{{$editproduct->sku3}}"></td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </fieldset><br>
        
                                                <fieldset>
                                                    <h3>Product size:</h3>
                                                    <table class="table table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th></th>
                                                                <th>Length</th>
                                                                <th>Width</th>
                                                                <th>Height</th>
                                                                <th>Weight</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td>Product</td>
                                                                <td><input type="text" name="Lengthcm" class="form-control" placeholder="In cm" value="{{$editproduct->Lengthcm}}"></td>
                                                                <td><input type="text" name="Widthcm" class="form-control" placeholder="In cm" value="{{$editproduct->Widthcm}}"></td>
                                                                <td><input type="text" name="Heightcm" class="form-control" placeholder="In cm" value="{{$editproduct->Heightcm}}"></td>
                                                                <td><input type="text" name="Weightkg" class="form-control" placeholder="In kg" value="{{$editproduct->Weightkg}}"></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Box</td>
                                                                <td><input type="text" name="Lengthcmbox" class="form-control" placeholder="In cm" value="{{$editproduct->Lengthcmbox}}"></td>
                                                                <td><input type="text" name="Widthcmbox" class="form-control" placeholder="In cm" value="{{$editproduct->Widthcmbox}}"></td>
                                                                <td><input type="text" name="Heightcmbox" class="form-control" placeholder="In cm" value="{{$editproduct->Heightcmbox}}"></td>
                                                                <td><input type="text" name="Weightkgbox" class="form-control" placeholder="In kg" value="{{$editproduct->Weightkgbox}}"></td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </fieldset><br>
                                                
                                                <p>Number of items that can be packed together</p>
                                                <div class="col-12 table-bordered">	
                                                    <div class="form-group">
                                                        <br>
                                                        <div class="row">
                                                            <div class="col-2"><label>Parcel S :</label></div>
                                                            <div class="col-6">
                                                                <input type="number" name="itemsinpaket1" class="form-control" placeholder="Number of items in a parcel size S" value="{{$editproduct->itemsinpaket1}}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="row">
                                                            <div class="col-2"><label>Parcel L :</label></div>
                                                            <div class="col-6">
                                                                <input type="number" name="itemsinpaket2" class="form-control" placeholder="Number of items in a parcel size L" value="{{$editproduct->itemsinpaket2}}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="row">
                                                            <div class="col-2"><label>Parcel XL :</label></div>
                                                            <div class="col-6">
                                                                <input type="number" name="itemsinpaket3" class="form-control" placeholder="Number of items in a parcel size XL" value="{{$editproduct->itemsinpaket3}}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div><br>
        
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <h3>Photo product:</h3>
                                                            <div class="avatar-wrapper">
                                                                <img class="profile-pic" src="assets/images/upload_img.png" />
                                                                <div class="upload-button">
                                                                    <!-- <i class="fa fa-arrow-circle-up w-100" aria-hidden="true"></i> -->
                                                                </div>
                                                                <input name="image" type="file" id="id_avata" class="form-control file-upload"><br/>
                                                                
                                                            </div>
                                                        </div>
                                                        <div class="form-group bt-switch mb-10" style="width:140px;">
                                                            <label>Virtual kit:</label>
                                                            <input onchange="works()" type="checkbox" name="virtualkit" data-on-color="info" @if($editproduct->virtualkit=='Yes') checked @endif value="1" data-off-color="success" id="myCheck">
                                                        </div>
                                                        <table class="table table-bordered" id="showtable" style="display: none;">
                                                            <tbody>
                                                                <tr>
                                                                    <td><input type="text" name="pcs1" class="form-control" placeholder="Enter PCS 1" value="{{$editproduct->pcs1}}"></td>
                                                                    <td>
                                                                        <select class="form-control" name="productid1">
                                                                            <option value="">Select ProductID 1</option>
                                                                            @foreach($products as $key => $row)
                                                                            <option value="{{$row->modelcode}}" @if($editproduct->productid1==$row->modelcode) selected="selected" @endif>{{$row->nameshort}}</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td><input type="text" name="pcs2" class="form-control" placeholder="Enter PCS 2" value="{{$editproduct->pcs2}}"></td>
                                                                    <td>
                                                                        <select class="form-control" name="productid2">
                                                                            <option value="">Select ProductID 2</option>
                                                                            @foreach($products as $key => $row)
                                                                            <option value="{{$row->modelcode}}" @if($editproduct->productid2==$row->modelcode) selected="selected" @endif>{{$row->nameshort}}</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td><input type="text" name="pcs3" class="form-control" placeholder="Enter PCS 3" value="{{$editproduct->pcs3}}"></td>
                                                                    <td>
                                                                        <select class="form-control" name="productid3">
                                                                            <option value="">Select ProductID 3</option>
                                                                            @foreach($products as $key => $row)
                                                                            <option value="{{$row->modelcode}}" @if($editproduct->productid3==$row->modelcode) selected="selected" @endif>{{$row->nameshort}}</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td><input type="text" name="pcs4" class="form-control" placeholder="Enter PCS 4" value="{{$editproduct->pcs4}}"></td>
                                                                    <td>
                                                                        <select class="form-control" name="productid4">
                                                                            <option value="">Select ProductID 4</option>
                                                                            @foreach($products as $key => $row)
                                                                            <option value="{{$row->modelcode}}" @if($editproduct->productid4==$row->modelcode) selected="selected" @endif>{{$row->nameshort}}</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td><input type="text" name="pcs5" class="form-control" placeholder="Enter PCS 5" value="{{$editproduct->pcs5}}"></td>
                                                                    <td>
                                                                        <select class="form-control" name="productid5">
                                                                            <option value="">Select ProductID 5</option>
                                                                            @foreach($products as $key => $row)
                                                                            <option value="{{$row->modelcode}}" @if($editproduct->productid5==$row->modelcode) selected="selected" @endif>{{$row->nameshort}}</option>
                                                                            @endforeach
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td><input type="text" name="pcs6" class="form-control" placeholder="Enter PCS 6" value="{{$editproduct->pcs6}}"></td>
                                                                    <td>
                                                                        <select class="form-control" name="productid6">
                                                                            <option value="">Select ProductID 6</option>
                                                                            @foreach($products as $key => $row)
                                                                            <option value="{{$row->modelcode}}" @if($editproduct->productid6==$row->modelcode) selected="selected" @endif>{{$row->nameshort}}</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td><input type="text" name="pcs7" class="form-control" placeholder="Enter PCS 7" value="{{$editproduct->pcs7}}"></td>
                                                                    <td>
                                                                        <select class="form-control" name="productid7">
                                                                            <option value="">Select ProductID 7</option>
                                                                            @foreach($products as $key => $row)
                                                                            <option value="{{$row->modelcode}}" @if($editproduct->productid7==$row->modelcode) selected="selected" @endif>{{$row->nameshort}}</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td><input type="text" name="pcs8" class="form-control" placeholder="Enter PCS 8" value="{{$editproduct->pcs8}}"></td>
                                                                    <td>
                                                                        <select class="form-control" name="productid8">
                                                                            <option value="">Select ProductID 8</option>
                                                                            @foreach($products as $key => $row)
                                                                            <option value="{{$row->modelcode}}" @if($editproduct->productid8==$row->modelcode) selected="selected" @endif>{{$row->nameshort}}</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td><input type="text" name="pcs9" class="form-control" placeholder="Enter PCS 9" value="{{$editproduct->pcs9}}"></td>
                                                                    <td>
                                                                        <select class="form-control" name="productid9">
                                                                            <option value="">Select ProductID 9</option>
                                                                            @foreach($products as $key => $row)
                                                                            <option value="{{$row->modelcode}}" @if($editproduct->productid9==$row->modelcode) selected="selected" @endif>{{$row->nameshort}}</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </td>
                                                                </tr>
                                                            </body>
                                                        </table>
        
                                                        <div id="showproductdetail">
                                                            <div class="row">
                                                                <div class="col-6">
                                                                    <div class="form-group">
                                                                        <label>Price</label>
                                                                        <input type="text" name="price" class="form-control" value="{{$editproduct->price}}"/>
                                                                    </div>
                                                                </div>
                                                                <div class="col-6">
                                                                    <div class="form-group">
                                                                        <label>Last update price</label>
                                                                        <input type="date" name="dateprice" class="form-control" value="{{$editproduct->dateprice}}"/>
                                                                    </div>
                                                                </div>
                                                            </div>
        
                                                            <h3>Product Detail: </h3><br>
                                                            <div class="form-group">
                                                                <label>Manufacturer</label>
                                                                <select class="form-control" name="manufacturerid">
                                                                    <option value="">Select manufacturer</option>
                                                                    @foreach($manufacturers as $key => $row)
                                                                    <option value="{{$row->manufacturerid}}" @if($editproduct->manufacturerid == $row->manufacturerid) selected="selected" @endif>{{$row->shortname}}</option>
                                                                    @endforeach
                                                                </select>    
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Code</label>
                                                                <input type="text" name="codemanu" class="form-control" value="{{$editproduct->codemanu}}"/>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Content</label>
                                                                <input type="text" name="content" class="form-control" value="{{$editproduct->content}}"/>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- col-6 -->
                                                </div>
                                                <!-- row -->
                                            </div>
                                            <!-- col-7 -->
                                        </div>
                                        <input type="submit" name="submit" class="btn btn-info pull-right" value="Save Product">
                                    </form>
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
        <script src="assets/plugins/datatables/jquery.dataTables.min.js"></script>
        <script src="assets/js/jquery.validate.min.js"></script>
        <script src="assets/js/additional-methods.js"></script>
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
                radioswitch.init();
                var readURL = function(input) {
                    if (input.files && input.files[0]) {
                        var reader = new FileReader();
                        reader.onload = function (e) {
                            $('.profile-pic').attr('src', e.target.result);
                        }
                        reader.readAsDataURL(input.files[0]);
                    }
                }
            
                $(".file-upload").on('change', function(){
                    readURL(this);
                });
                
                $(".upload-button").on('click', function() {
                    $(".file-upload").click();
                });
            });

            function works() {
                var checkBox = document.getElementById("myCheck");
                var showTable =document.getElementById("showtable");
                var showproductdetail =document.getElementById("showproductdetail");
                if (checkBox.checked == true){
                    showTable.style.display = "block";
                    showproductdetail.style.display = "none";
                } else {
                    showTable.style.display = "none";
                    showproductdetail.style.display = "block";
                }
            }
        </script>
        <script>
            $("#fileimg").on("change",function(e){
                var ext = this.value.match(/\.([^\.]+)$/)[1];
                switch(ext)
                {
                    case 'JPG':
                    case 'jpg':
                    case 'JPEG':
                    case 'jpeg':
                        break;
                    default:
                        alert('this file type is not allowed');
                        this.value='';
                }
            });
            $(document).ready(function() {
                $('#myTable').DataTable();
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

            $(".open").on("click", function(){
                $(".popup, .popup-content").addClass("active");
            });

            $(".close, .popup").on("click", function(){
                $(".popup, .popup-content").removeClass("active");
            });
        </script>
        
        <script src="assets/js/custom.min.js"></script>
        <script src="assets/plugins/raphael/raphael-min.js"></script>
        <script src="assets/plugins/morrisjs/morris.min.js"></script>
        <script src="assets/plugins/styleswitcher/jQuery.style.switcher.js"></script>

        <script type="text/javascript">
            $(document).ready(function(){
                $("select.plt").change(function(){
                    var option = $('option:selected', this).attr('pro');
                    var showTableebay =document.getElementById("showTableEbay");
                    var showTableamazon =document.getElementById("showTableamazon");
                    var display = $('option:selected', this).attr('site');
                    $('#defaultId').val(display);
                    if(option == 'Ebay'){
                        showTableebay.style.display="block";
                    } else {
                        showTableebay.style.display="none";
                    }
                    if(option == 'Amazon'){
                        showTableamazon.style.display="block";
                    } else {
                        showTableamazon.style.display="none";
                    }
                });
            });

            // function addChannel() {
            //     $('.preloader').show();
            //     var option              = $('option:selected', 'select.plt').attr('pro');
            //     var devid               = $('#devid').val();
            //     var appid               = $('#appid').val();
            //     var certid              = $('#certid').val();
            //     var usertoken           = $('#usertoken').val();
            //     var idcompany           = $('#idcompany').val();
            //     var platformid          = $('#platformid').val();
            //     var shortname           = $('#shortname').val();
            //     var longname            = $('#longname').val();
            //     var country             = $('#country').val();
            //     var warehouse           = $('#warehouse').val();
            //     var vat                 = $('#vat').val();
            //     var aws_acc_key_id      = $('#aws_acc_key_id').val();
            //     var aws_secret_key_id   = $('#aws_secret_key_id').val();
            //     var merchant_id         = $('#merchant_id').val();
            //     var market_place_id     = $('#market_place_id').val();
            //     var mws_auth_token      = $('#mws_auth_token').val();

            //     var data = {
            //         devid               : devid,
            //         appid               : appid,
            //         certid              : certid,
            //         usertoken           : usertoken,
            //         idcompany           : idcompany,
            //         platformid          : platformid,
            //         shortname           : shortname,
            //         longname            : longname,
            //         country             : country,
            //         warehouse           : warehouse,
            //         vat                 : vat,
            //         aws_acc_key_id      : aws_acc_key_id,
            //         aws_secret_key_id   : aws_secret_key_id,
            //         merchant_id         : merchant_id,
            //         market_place_id     : market_place_id,
            //         mws_auth_token      : mws_auth_token
            //     }
            //     $.ajax({
            //         type: "POST",
            //         url: "channelAdd",
            //         data: data,
            //         success: function(data) {
            //             $('.preloader').hide();
            //             if(data == false) {
            //                 alert('Invalid Keys');
            //             } else {
            //                 alert("Data Added");
            //                 window.location.href="channelView";
            //             }
            //         },
            //         error: function() {
            //             alert('error handing here');
            //             $('.preloader').hide();
            //         }
            //     });
            // }
        </script>
    </body>

</html>