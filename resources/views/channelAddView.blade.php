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
                            <li><a class="has-arrow waves-effect waves-dark" href="#" aria-expanded="false"><i class="mdi mdi-group"></i><span class="hide-menu">FBA </span></a>
                                <ul aria-expanded="false" class="collapse">
                                    <li><a href="FBAView">FBA </a></li>
                                    <li><a href="FBAontheway">FBA ontheway</a></li>
                                    <li><a href="#">FBA Premium</a></li>
                                    <li><a href="#">Dropshipping </a></li>
                                </ul>
                            </li>
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
                        <h3 class="text-themecolor">Add new channel</h3>
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
                                    <form method="post" enctype="multipart/form-data" action="channelAdd">
                                        {{ csrf_field() }}
                                        <div class="row">
                                            <div class="col-md-6 col-sm-12">
                                                <div class="form-group">
                                                    <label>Company</label>
                                                    <select class="form-control" name="idcompany" id="idcompany" required="">
                                                        <option value="">Select company</option>
                                                        @foreach($companies as $key => $company)
                                                        <option value="{{$company->idcompany}}">{{$company->shortname}}</option>
                                                        @endforeach
                                                    </select>    
                                                </div>
                                                <div class="form-group">
                                                    <label>Platform</label>
                                                    <select id="platformid" name="platformid" class="form-control plt" required="">
                                                        <option  value="">Select platform</option>
                                                        @foreach ($platforms as $key => $p)
                                                        <option site="{{$p->siteid}}" pro="{{$p->platformtype}}" value="{{$p->platformid}}">{{$p->shortname}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div id="showTableEbay" style="display: none; float:right; width:85%">
                                                    <div class="form-group">
                                                        <label>Site ID</label>
                                                        <input type="text" id="defaultId" readonly="readonly"  class='form-control' name="siteid">
                                                    </div>                          
                                                    <div class="form-group">
                                                        <label>Dev ID</label>
                                                        <input type="text"class='form-control' id="devid" name="devid">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>App Id</label>
                                                        <input type="text"class='form-control' id="appid" name="appid">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Cert ID</label>
                                                        <input type="text"class='form-control' id="certid" name="certid">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>User Token</label>
                                                        <input type="text"class='form-control' id="usertoken" name="usertoken">
                                                    </div>
                                                </div>
                                                <div id="showTableamazon" style="display: none;">
                                                    <div class="form-group">
                                                        <label>AWS ACESS KEY ID</label>
                                                        <input type="text"class='form-control' id="aws_acc_key_id" name="aws_acc_key_id">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>AWS SECRET ACCESS KEY</label>
                                                        <input type="text"class='form-control' id="aws_secret_key_id" name="aws_secret_key_id">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>MERCHANT ID</label>
                                                        <input type="text"class='form-control' id="merchant_id" name="merchant_id">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>MARKET PLACE ID</label>
                                                        <input type="text"class='form-control' id="market_place_id" name="market_place_id">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Auth Token</label>
                                                        <input type="text"class='form-control' id="mws_auth_token" name="mws_auth_token">
                                                    </div>
                                                </div>      
    
                                                <div id="showTablewoocommerce" style="display: none;">
                                                    <div class="form-group">
                                                        <label>WOOCOMMERCE STORE URL</label>
                                                        <input type="text"class='form-control' id="woo_store_url" name="woo_store_url">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>WOOCOMMERCE CONSUMER KEY</label>
                                                        <input type="text"class='form-control' id="woo_consumer_key" name="woo_consumer_key">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>WOOCOMMERCE CONSUMER SECRET</label>
                                                        <input type="text"class='form-control' id="woo_consumer_secret" name="woo_consumer_secret">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Flat Shipping costs</label>
                                                        <input type="text" id="flat_shipping_cost" name="flat_shipping_cost" class="form-control"/>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label>Short name</label>
                                                    <input type="text" id="shortname" name="shortname" class="form-control"/>
                                                </div>

                                                <div class="form-group">
                                                    <label>Long name</label><br>
                                                    <input type="text" id="longname" name="longname" class="form-control" />
                                                </div>
                                                
                                                <div class="form-group">
                                                    <label>Country</label>
                                                    <select name="country" id="country" class="form-control">
                                                        <option value="">Select Country</option>
                                                        @foreach($datafetchcountry as $data)
                                                        <option value="{{$data->shortname}}">{{$data->shortname}}</option>
                                                        @endforeach
                                                    </select>   
                                                </div>
                                                
                                                <div class="form-group">
                                                    <label>Main warehouse</label>
                                                    <select name="warehouse" id="warehouse" class="form-control" required="">
                                                        <option value="">Select warehouse</option>
                                                        @foreach($warehouse as $key => $w)
                                                            <option value="{{$w->idwarehouse}}">{{$w->shortname}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                
                                                <div class="form-group">
                                                    <label>Vat</label>
                                                    <input type="number" id="vat" name="vat" class="form-control"/>
                                                </div>
                                            </div>
                                        </div>
                                        <input type="submit" id="submit" name="submit-channel" value="Add new channel" class="btn btn-info pull-right">
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
                radioswitch.init()
            });
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
                    var showTableebay           = document.getElementById("showTableEbay");
                    var showTableamazon         = document.getElementById("showTableamazon");
                    var showTablewoocommerce    = document.getElementById("showTablewoocommerce");
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

                    if(option == 'Woocommerce'){
                        showTablewoocommerce.style.display="block";
                    } else {
                        showTablewoocommerce.style.display="none";
                    }
                });
            });
        </script>
    </body>

</html>