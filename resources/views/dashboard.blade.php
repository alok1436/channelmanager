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
        <link rel="stylesheet" type="text/css" href="https://www.jqueryscript.net/demo/DataTables-Jquery-Table-Plugin/media/css/jquery.dataTables.css">
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
        <!-- Preloader - style you can find in spinners.css -->
        <div class="preloader">
            <svg class="circular" viewBox="25 25 50 50">
                <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10" /> 
            </svg>
        </div>
        <!-- Main wrapper - style you can find in pages.scss -->
        <div id="main-wrapper">
            <!-- Topbar header - style you can find in pages.scss -->
            <header class="topbar">
                <nav class="navbar top-navbar navbar-expand-md navbar-light">
                    <!-- Logo -->
                    <div class="navbar-header">
                        <a class="navbar-brand" href="index.php">
                            <!-- Logo icon -->
                            <b>
                                <!-- Dark Logo icon -->
                                
                                <!-- Light Logo icon -->
                                <img src="assets/images/logo-light-icon.png" alt="homepage" class="light-logo" />
                            </b>
                            <!--End Logo icon -->
                            <!-- Logo text -->
                            <span>
                                <!-- dark Logo text -->
                                <img src="assets/images/{{Session::get('logo2')}}" alt="homepage" class="dark-logo dark-logo1" />
                                <!-- Light Logo text -->    
                                <img src="assets/images/logo-light-text.png" class="light-logo" alt="homepage" />
                            </span> 
                        </a>
                    </div>
                    <!-- End Logo -->
                    <div class="navbar-collapse">
                        <!-- toggle and nav items -->
                        <ul class="navbar-nav mr-auto mt-md-0">
                            <!-- This is  -->
                            <li class="nav-item"> <a class="nav-link nav-toggler hidden-md-up text-muted waves-effect waves-dark" href="javascript:void(0)"><i class="mdi mdi-menu"></i></a> </li>
                            <!-- ============================================================== -->
                        </ul>
                        <!-- User profile and search -->
                        <ul class="navbar-nav my-lg-0">
                            <!-- Language -->
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle text-muted waves-effect waves-dark" href="logout"> <i class="fa fa-power-off"></i> Logout</a>
                            </li>
                            <!-- Profile -->
                        </ul>
                    </div>
                </nav>
            </header>
            <!-- End Topbar header -->
            <!-- Left Sidebar - style you can find in sidebar.scss  -->
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
            <!-- End Left Sidebar - style you can find in sidebar.scss  -->
            <!-- Page wrapper  -->
            <div class="page-wrapper">
                <!-- Bread crumb and right sidebar toggle -->
                <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h3 class="text-themecolor">Dashboard</h3>
                    </div>
                    <div class="col-md-7 align-self-center">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                            <li class="breadcrumb-item active">Dashboard</li>
                        </ol>
                    </div>
                </div>
                <!-- End Bread crumb and right sidebar toggle -->
                <!-- Container fluid  -->
                <div class="container-fluid">
                    <!-- Start Page Content -->
                    <!-- Row -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body">
                                    <div>
                                        <h4 class="card-title"><b>Finanzstatus</b></h4>
                                    </div>
                                    <div class="ml-auto">
                                        <div class="col-xs-12 table-responsive" style="height: 500px; overflow: auto;">
                                            <table class="table table-striped table-bordered" style="width: 100%;">
                                                <thead>
                                                    <tr>
                                                        <th>Total warehouse</th>
                                                        @foreach ($warehouses as $key => $w)
                                                        <th>{{$w->shortname}}</th>
                                                        @endforeach
                                                        <th>Subcategory</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <th style="text-align: right;">
                                                            <?php $total = 0; ?>
                                                            @foreach ($warehouses as $key => $w)
                                                                <?php
                                                                    if(isset($subcategories[0]->{"total_".$w->idwarehouse})) {
                                                                        $total += $subcategories[0]->{"total_".$w->idwarehouse};
                                                                    }
                                                                ?>
                                                            @endforeach
                                                            {{$total}}
                                                        </th>
                                                        @foreach ($warehouses as $key => $w)
                                                            <th style="text-align: right;">
                                                                <?php
                                                                    if(isset($subcategories[0]->{"total_".$w->idwarehouse})) {
                                                                        echo $subcategories[0]->{"total_".$w->idwarehouse};
                                                                    }
                                                                ?>
                                                            </th>
                                                        @endforeach
                                                        <td></td>
                                                    </tr>
                                                    @foreach($subcategories as $row)
                                                    <tr>
                                                        <td style="text-align: right;">
                                                            <?php $total = 0; ?>
                                                            @foreach ($warehouses as $key => $w)
                                                                <?php
                                                                    if(isset($row->{$w->idwarehouse})) {
                                                                        $total += $row->{$w->idwarehouse};
                                                                    }
                                                                ?>
                                                            @endforeach
                                                            {{$total}}
                                                        </td>
                                                        @foreach ($warehouses as $key => $w)
                                                            <td style="text-align: right;">
                                                                <?php
                                                                    if(isset($row->{$w->idwarehouse})) {
                                                                        echo $row->{$w->idwarehouse};
                                                                    }
                                                                ?>
                                                            </td>
                                                        @endforeach
                                                        <td style="text-align: right;">{{$row->Namesubcat}}</td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="form-group" style="float: right; margin-bottom: 0;">
                                            <a href="finanzstatusView" class="btn btn-info">Finanzstatus</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body">
                                    <div>
                                        <h4 class="card-title"><b>Products</b></h4>
                                    </div>
                                    <div class="ml-auto">
                                        <div class="col-xs-12 table-responsive" style="height: 500px; overflow: auto;">
                                            <table class="table table-striped table-bordered" style="width: 100%;">
                                                <thead>
                                                    <tr>
                                                        <th>SKU</th>
                                                        <th>EAN</th>
                                                        <th>Short name</th>
                                                        <th>Long name</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($zeroProducts as $row)
                                                    <tr>
                                                        <td>{{$row->sku}}</td>
                                                        <td>{{$row->ean}}</td>
                                                        <td>{{$row->nameshort}}</td>
                                                        <td>{{$row->namelong}}</td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="form-group" style="float: right; margin-bottom: 0;">
                                            <a href="manufacturerorderView" class="btn btn-info">New order items</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body">
                                    <div>
                                        <h4 class="card-title"><b>Confirm arrival of orders</b></h4>
                                    </div>
                                    <div class="ml-auto">
                                        <div class="col-xs-12 table-responsive" style="height: 500px; overflow: auto;">
                                            <table class="table table-striped table-bordered" style="width: 100%;">
                                                <thead>
                                                    <tr>
                                                        <th>Modelcode</th>
                                                        <th>Nameshort</th>
                                                        <th>Active (yes/no)</th>
                                                        <th>Kit (yes/no)</th>
                                                        <th>Delivery week</th>
                                                        <th>Notes</th>
                                                        <th>To tranfer</th>                                        
                                                        <th>Arrived</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($newContainers as $row)
                                                    <tr>
                                                        <td>{{$row->modelcode}}</td>
                                                        <td>{{$row->nameshort}}</td>
                                                        <td>{{$row->active}}</td>
                                                        <td>{{$row->virtualkit}}</td>
                                                        <td>{{$row->deliveryweek}}</td>
                                                        <td>{{$row->notes}}</td>
                                                        <td>{{$row->quantity}}</td>
                                                        <td>
                                                            <input type="text" class="form-control" name="quantity[{{$row->idnewcontainer}}]" value="{{$row->quantity}}">
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="form-group" style="float: right; margin-bottom: 0;">
                                            <a href="containerconfirmView" class="btn btn-info">Confirm arrival</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <div>
                                        <h4 class="card-title"><b>Open activities</b></h4>
                                    </div>
                                    <div class="ml-auto">
                                        <div class="col-xs-12 table-responsive">
                                            <table class="table table-striped table-bordered" style="width: 100%;">
                                                <thead>
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>What</th>
                                                        <th></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($warnings as $item)
                                                    <tr>
                                                        <td>{{$item->dateTime}}</td>
                                                        <td>{{$item->issues}}</td>
                                                        <td><a href="fixWarning/{{$item->id}}" class="btn btn-info">Done</a></td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-8 col-md-7">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex flex-wrap">
                                        <div>
                                            <h4 class="card-title">Yearly Earning</h4>
                                        </div>
                                        <div class="ml-auto">
                                            <ul class="list-inline">
                                                <li>
                                                    <h6 class="text-muted text-success"><i class="fa fa-circle font-10 m-r-10 "></i>iMac</h6> 
                                                </li>
                                                <li>
                                                    <h6 class="text-muted  text-info"><i class="fa fa-circle font-10 m-r-10"></i>iPhone</h6> 
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div id="morris-area-chart2" style="height: 405px;"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-5">
                            <!-- Column -->
                            <div class="card card-default">
                                <div class="card-header">
                                    <div class="card-actions">
                                        <a class="" data-action="collapse"><i class="ti-minus"></i></a>
                                        <a class="btn-minimize" data-action="expand"><i class="mdi mdi-arrow-expand"></i></a>
                                        <a class="btn-close" data-action="close"><i class="ti-close"></i></a>
                                    </div>
                                    <h4 class="card-title m-b-0">Order Stats</h4>
                                </div>
                                <div class="card-body collapse show">
                                    <div id="morris-donut-chart" class="ecomm-donute" style="height: 317px;"></div>
                                    <ul class="list-inline m-t-20 text-center">
                                        <li >
                                            <h6 class="text-muted"><i class="fa fa-circle text-info"></i> Order</h65>
                                            <h4 class="m-b-0">8500</h4>
                                        </li>
                                        <li>
                                            <h6 class="text-muted"><i class="fa fa-circle text-danger"></i> Pending</h6>
                                            <h4 class="m-b-0">3630</h4>
                                        </li>
                                        <li>
                                            <h6 class="text-muted"> <i class="fa fa-circle text-success"></i> Delivered</h6>
                                            <h4 class="m-b-0">4870</h4>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <!-- Column -->
                        </div>
                    </div>
                    <!-- Row -->
                    <!-- End PAge Content -->      
                </div>
                <!-- End Container fluid  -->
                <!-- footer -->
                <footer class="footer">
                    © 2021 Semplifat powered by Confidence Europe GmbH
                </footer>
                <!-- End footer -->
            </div>
            <!-- End Page wrapper  -->
        </div>
        <!-- ============================================================== -->
        <!-- End Wrapper -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- All Jquery -->
        <!-- ============================================================== -->
        <script src="assets/plugins/jquery/jquery.min.js"></script>
        <!-- Bootstrap tether Core JavaScript -->
        <script src="assets/plugins/bootstrap/js/popper.min.js"></script>

        <script src="assets/plugins/bootstrap/js/bootstrap.min.js"></script>


        <!-- slimscrollbar scrollbar JavaScript -->
        <script src="assets/js/jquery.slimscroll.js"></script>
        <!--Wave Effects -->
        <script src="assets/js/waves.js"></script>
        <!--Menu sidebar -->
        <script src="assets/js/sidebarmenu.js"></script>
        <!--stickey kit -->
        <script src="assets/plugins/sticky-kit-master/dist/sticky-kit.min.js"></script>
        <script src="assets/plugins/sparkline/jquery.sparkline.min.js"></script>
        
        <!-- ============================================================== -->
        <!-- This page plugins -->
        <!-- ============================================================== -->
        
        <!-- sparkline chart -->
        <script src="assets/plugins/sparkline/jquery.sparkline.min.js"></script>
        <script src="assets/js/dashboard4.js"></script>

        <!-- bt-switch -->
        <script src="assets/plugins/bootstrap-switch/bootstrap-switch.min.js"></script>

        <!-- DataTable -->
        <script src="assets/plugins/datatables/jquery.dataTables.min.js"></script>

        <!----- FOR JQUERY VALIDATE ------------------->
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
        <!-- Style switcher -->
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
        <!-- `Data Table -->
        <!--Custom JavaScript -->
        <script src="assets/js/custom.min.js"></script>
        <!--morris JavaScript -->
        <script src="assets/plugins/raphael/raphael-min.js"></script>
        <script src="assets/plugins/morrisjs/morris.min.js"></script>
        <script src="assets/plugins/styleswitcher/jQuery.style.switcher.js"></script>
    </body>

</html>