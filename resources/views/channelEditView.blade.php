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
        <link href="assets/css/style.css" rel="stylesheet">
        <script src="assets/js/jquery-3.2.1.min.js"></script>
        <link href="assets/css/colors/default.css" id="theme" rel="stylesheet">
        <link rel="stylesheet" type="text/css" href="assets/js/jquery.form-validator.min.js">
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
                        <h3 class="text-themecolor">channel</h3>
                    </div>
                    <div class="col-md-7 align-self-center">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                            <li class="breadcrumb-item">pages</li>
                            <li class="breadcrumb-item active">Channel</li>
                        </ol>
                    </div>
                </div>
    
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div id="response" class="<?php if(!empty($type)) { echo $type . " display-block"; } ?>"><?php if(!empty($message)) { echo $message; } ?></div>
                                </div>
                            </div>  
                            <div class="card">
                                <div class="card-body">
                                    <form method="post" id="bbb" action="channelEdit" enctype="multipart/form-data">
                                        {{ csrf_field() }}
                                        <input type="hidden" name="channelIdhidden" id="channelIdhidden" value="{{$channel->idchannel}}">
                                        <input type="hidden" name="channelType" id="channelType" value="{{$channelType}}">
                                        <div class="row">
                                            <!-- Ebay -->  
                                            <div class="col-md-5 col-sm-12" style="@if($channel->sync != 'Automatic Synch with: eBay') display:none; @endif">
                                                <div class="form-group">
                                                    <label>Dev Id</label>
                                                    <input type="text" name="devid" id="devid" class="form-control" value="{{$channel->devid}}" />
                                                </div>
                                                <div class="form-group">
                                                    <label>App Id</label>
                                                    <input type="text" name="appid" id="appid" class="form-control" value="{{$channel->appid}}" />
                                                </div>
                                                <div class="form-group">
                                                    <label>Cert Id</label>
                                                    <input type="text" name="certid" id="certid" class="form-control" value="{{$channel->certid}}" />
                                                </div>
                                                <div class="form-group">
                                                    <label>User token</label>
                                                    <input type="text" name="usertoken" id="usertoken" class="form-control" value="{{$channel->usertoken}}" />
                                                </div>
                                                <div class="form-group">
                                                    <input type="submit" name="submit" id="submit" class="btn btn-info pull-right" value="Save channel">
                                                </div>
                                            </div>
                                            <!-- End Ebay -->  
                                        
                                            <!-- Amazon -->
                                            <div class="col-md-5 col-sm-12" style="@if($channel->sync != 'Automatic Synch with: Amazon') display:none; @endif ">
                                                <div class="form-group">
                                                    <label>AWS ACESS KEY ID</label>
                                                    <input type="text" name="aakid" id="aakid" class="form-control" value="{{$channel->aws_acc_key_id}}" />
                                                </div>
                                                <div class="form-group">
                                                    <label>AWS SECRET ACCESS KEY</label>
                                                    <input type="text" name="asak" id="asak" class="form-control" value="{{$channel->aws_secret_key_id}}" />
                                                </div>
                                                <div class="form-group">
                                                    <label>MERCHANT ID</label>
                                                    <input type="text" name="merchantid" id="merchantid" class="form-control" value="{{$channel->merchant_id}}" />
                                                </div>
                                                <div class="form-group">
                                                    <label>MARKET PLACE ID</label>
                                                    <input type="text" name="marketplaceid" id="marketplaceid" class="form-control" value="{{$channel->market_place_id}}" />
                                                </div>
                                                <div class="form-group">
                                                    <label>Auth Token</label>
                                                    <input type="text" name="authtoken" id="authtoken" class="form-control" value="{{$channel->mws_auth_token}}" />
                                                </div>
                                                <div class="form-group">
                                                    <input type="submit" name="submit" id="submit" class="btn btn-info pull-right" value="Save channel">
                                                </div>
                                            </div>
                                            <!-- End Amazon -->  

                                            <!-- Amazon -->
                                            <div class="col-md-5 col-sm-12" style="@if($channelType != 'woocommerce') display:none; @endif ">
                                                <div class="form-group">
                                                    <label>WOOCOMMERCE STORE URL</label>
                                                    <input type="text"class='form-control' id="woo_store_url" name="woo_store_url" value="{{$channel->woo_store_url}}">
                                                </div>
                                                <div class="form-group">
                                                    <label>WOOCOMMERCE CONSUMER KEY</label>
                                                    <input type="text"class='form-control' id="woo_consumer_key" name="woo_consumer_key" value="{{$channel->woo_consumer_key}}">
                                                </div>
                                                <div class="form-group">
                                                    <label>WOOCOMMERCE CONSUMER SECRET</label>
                                                    <input type="text"class='form-control' id="woo_consumer_secret" name="woo_consumer_secret" value="{{$channel->woo_consumer_secret}}">
                                                </div>
                                                <div class="form-group">
                                                    <label>Flat Shipping costs</label>
                                                    <input type="text" id="flat_shipping_cost" name="flat_shipping_cost" class="form-control" value="{{$channel->flat_shipping_costs}}" />
                                                </div>
                                                <div class="form-group">
                                                    <input type="submit" name="submit" id="submit" class="btn btn-info pull-right" value="Save channel">
                                                </div>
                                            </div>
                                            <div class="col-md-5 col-sm-12" style="@if($channelType != 'otto') display:none; @endif ">
                                                <div class="form-group">
                                                    <label>Flat Shipping costs</label>
                                                    <input type="text" id="flat_shipping_cost" name="flat_shipping_cost" class="form-control" value="{{$channel->flat_shipping_costs}}" />
                                                </div>
                                                <div class="form-group">
                                                    
                                                    <input type="submit" name="submit" id="submit" class="btn btn-info pull-right" value="Save channel">

                                                    <a href="{{ url('channelView') }}" class="mr-3 btn btn-info pull-right" value="Save channel">Back</a>
                                                </div>
                                            </div>
                                            <!-- End Amazon --> 
                                        </div>
                                    </form>
                                </div>
                            </div>                         
                        </div>   	
                    </div>
                </div>
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
        <script src="assets/js/custom.min.js"></script>
        <script src="assets/plugins/raphael/raphael-min.js"></script>
        <script src="assets/plugins/morrisjs/morris.min.js"></script>
        <script src="assets/plugins/styleswitcher/jQuery.style.switcher.js"></script>
    </body>
</html>