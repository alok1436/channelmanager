<!DOCTYPE html>
<html lang="en">
    <head>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
    <meta http-equiv='Content-Type' content='text/html; charset=windows-1256'/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/images/favicon') }}">
    <title>Admin - Panel </title>
    <link href="{{ asset('assets/plugins/bootstrap-switch/bootstrap-switch.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/plugins/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/plugins/morrisjs/morris.css') }}" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/jquery.dataTables.min.css') }}">
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">
    <script src="{{ asset('assets/js/jquery-3.2.1.min.js') }}"></script>
    <link href="{{ asset('assets/css/colors/default.css') }}" id="theme" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/js/jquery.form-validator.min.js') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('backend/css/toastr.min.css') }}">
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
        
        .popup-overlay {
            visibility:hidden;
        }

        .popup-content {
            visibility:hidden;
        }
        .popup-overlay.active{
            visibility:visible;
        }

        .popup-content.active {
            visibility:visible;
        }

        .switch {
            position: relative;
            display: inline-block;
            width: 98px;
            height: 34px;
            border: 1px solid #d9d9d9;
            border-radius: 2px;
        }

        .switch input {display:none;}

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #26dad2;
            -webkit-transition: .4s;
            transition: .4s;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 100%;
            width: 50%;
            left: 0px;
            bottom: 0;
            background-color: white;
            -webkit-transition: .4s;
            transition: .4s;
        }

        input:checked + .slider {
            background-color: #2ab934;
        }

        input:focus + .slider {
            box-shadow: 0 0 1px #2196F3;
        }

        input:checked + .slider:before {
            -webkit-transform: translateX(48px);
            -ms-transform: translateX(48px);
            transform: translateX(48px);
        }

        /*------ ADDED CSS ---------*/
        .on {
            display: none;
        }

        .on, .off {
            color: white;
            padding: 6px 12px;
            font-size: 14px;
            line-height: 20px;
            box-sizing: border-box;
            cursor: pointer;
            display: table-cell;
            vertical-align: middle;
            color: #ffffff;
        }

        input:checked+ .slider .on {
            display: block;
            background: #1976d2;
        }

        input:checked + .slider .off {
            display: none;
        }

        .slider.round {
            border-radius: 34px;
        }

        .slider.round:before {
            border-radius: 50%;
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
            label.lb-h {
                display: none;
            }
            .btn-group label{
            color:#1976d2 !important;
            }
            .btn-group label:hover, .btn-group label:active{
            color:#fff !important;
            }
            label.btn.btn-outline-primary.active {
            color: #fff !important;
            }
            label.warehouse.last {
            margin-right: 11px;
            }
            #product-table_filter input {
                border: 1px solid #ccc;
                padding: 6px 12px;
                border-radius: 4px;
                background-image: none;
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
                            
                            <img src="{{ asset('assets/images/logo-light-icon') }}" alt="homepage" class="light-logo" />
                        </b>
                        <span>
                            <img src="{{ asset('assets/images/'.Session::get('logo2')) }}" alt="homepage" class="dark-logo dark-logo1" />
                            <img src="{{ asset('assets/images/logo-light-text') }}" class="light-logo" alt="homepage" />
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
                        <li><a href="{{ url('productView') }}"><i class="mdi mdi-basket-fill"></i><span class="hide-menu">Products</span></a></li>   
                        <li><a href="{{ url('offlineProductsView') }}"><i class="mdi mdi-basket-fill"></i><span class="hide-menu">Check Offline products</span></a></li>    
                        <li><a href="{{ url('blacklistView') }}"><i class="mdi mdi-basket-fill"></i><span class="hide-menu">Black list</span></a></li>      
                        <li><a href="{{ url('createKit') }}"><i class="mdi mdi-stocking"></i>Create Kit</a></li>
                        <li><a href="{{ url('orderView') }}"><i class="mdi mdi-stocking"></i>Orders</a></li>
                        <li><a href="{{ url('FBAView') }}"><i class="mdi mdi-group"></i>FBA</a></li>
                        <li><a class="has-arrow waves-effect waves-dark" href="#" aria-expanded="false"><i class="mdi mdi-group"></i><span class="hide-menu">Vendor </span></a>
                            <ul aria-expanded="false" class="collapse">
                                <li><a href="{{ url('vendorView') }}">Vendor </a></li>
                                <li><a href="{{ url('vendorBlackList') }}">BlackList</a></li>
                            </ul>
                        </li>
                        <li><a href="{{ url('priceView') }}"><i class="mdi mdi-credit-card-multiple"></i><span class="hide-menu">Price </span></a></li>
                        <li><a href="{{ url('modelView') }}"><i class="mdi mdi-format-list-bulleted"></i>Models</a></li>
                        <li>
                            <a class="has-arrow waves-effect waves-dark" href="#" aria-expanded="false"><i class="mdi mdi-airplay"></i><span class="hide-menu">Forecast</span></a>
                            <ul aria-expanded="false" class="collapse">
                                <li><a href="{{ url('trendView') }}">Trend</a></li>
                                <li><a href="{{ url('calculate') }}">Calculate</a></li>
                                <li><a href="{{ url('forcastOutput') }}">Show Forecast Output </a></li>
                            </ul>
                        </li>
                        <li> 
                            <a class="has-arrow waves-effect waves-dark" href="#" aria-expanded="false"><i class="mdi mdi-credit-card-multiple"></i><span class="hide-menu">Manufacturer orders</span></a>
                            <ul aria-expanded="false" class="collapse">
                                <li><a href="{{ url('manufacturerorderView') }}">New order items</a></li>
                                <li><a href="{{ url('containerconfirmView') }}">Confirm arrival</a></li>
                                <li><a href="{{ url('warehouseTransferFirstView') }}">Internal transfer</a></li>
                                <li><a href="{{ url('warehouseconfirmView') }}">Confirm internal</a></li>
                            </ul>
                        </li>
                        <li> 
                            <a class="has-arrow waves-effect waves-dark" href="#" aria-expanded="false"><i class="mdi mdi-arrange-send-backward"></i><span class="hide-menu">Settings </span></a>
                            <ul aria-expanded="false" class="collapse">
                                <li><a class="" href="{{ url('manufacturer') }}">Manufacturer</a></li>
                                <li><a class="" href="{{ url('warehouseView') }}">Warehouse</a></li>
                                <li><a class="" href="{{ url('channelView') }}">Channel</a></li>
                                <li><a class="" href="{{ url('channelCountry') }}">Channel country</a></li>
                                <li><a class="" href="{{ url('paymentView') }}">Payment</a></li>
                                <li><a class="" href="{{ url('userView') }}">Users</a></li>
                                <li><a href="{{ url('platformView') }}">Platform </a></li>
                                <li><a href="{{ url('countryView') }}">Country </a></li>
                                <li><a href="{{ url('currencyView') }}">Currency</a></li>
                                <li><a href="{{ url('companyView') }}">Company Info </a></li>
                                <li><a href="{{ url('courierView') }}">Courier</a></li>
                                <li><a href="{{ url('vendordepotView') }}">Vendordepot</a></li>
                                <li><a href="{{ url('generalSettingView') }}">General</a></li>
                                <li> 
                                    <a class="has-arrow waves-effect waves-dark" href="#" aria-expanded="false"><span class="hide-menu">Category</span></a>
                                    <ul aria-expanded="false" class="collapse">
                                        <li><a href="{{ url('categoryView') }}">Main</a></li>
                                        <li><a href="{{ url('subcategoryView') }}">Subcategory</a></li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                        <li><a href="{{ url('finanzstatusView') }}"><i class="mdi mdi-arrange-send-backward"></i><span class="hide-menu">Finance</span></a></li>
                        <li><a href="{{ url('inventoryView') }}"><i class="mdi mdi-arrange-send-backward"></i><span class="hide-menu">InventoryView </span></a></li>
                        <li><a href="{{ url('soldweeklyView') }}"><i class="mdi mdi-arrange-send-backward"></i><span class="hide-menu">Soldweekly </span></a></li>                        
                    </ul>
                </nav>
                <!-- End Sidebar navigation -->
            </div>
            <!-- End Sidebar scroll-->
        </aside>

        <div class="page-wrapper">
            <div class="row page-titles">
                <div class="col-md-5 align-self-center">
                    <h3 class="text-themecolor">Order</h3>
                </div>
                <div class="col-md-7 align-self-center">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                        <li class="breadcrumb-item">pages</li>
                        <li class="breadcrumb-item active">Order</li>
                    </ol>
                </div>
            </div>
            
            <div class="container-fluid">
                @if(session()->has('error'))
                <div class="alert alert-warning" role="alert">
                  {{ session()->get('error') }}
                </div>
                @endif
                <!--Begin::Content-->
                @yield('content')
                <!--End::Content-->
             <!-- ============================================================== -->
                    <!-- End PAge Content -->
                    <!-- ============================================================== -->
                <!-- ============================================================== -->
                </div>
                <!-- ============================================================== -->
                <!-- End Container fluid  -->
                <!-- ============================================================== -->
                <!-- ============================================================== -->
                <!-- footer -->
                <!-- ============================================================== -->
                <footer class="footer">
                    © 2021 Semplifat powered by Confidence Europe GmbH
                </footer>
                <!-- ============================================================== -->
                <!-- End footer -->
                <!-- ============================================================== -->
            </div>
        </div>
        <script src="{{ asset('assets/plugins/jquery/jquery.min.js') }}"></script>
        <script src="{{ asset('assets/plugins/bootstrap/js/popper.min.js') }}"></script>
        <script src="{{ asset('assets/plugins/bootstrap/js/bootstrap.min.js') }}"></script>
        <script src="{{ asset('assets/js/jquery.slimscroll.js') }}"></script>
        <script src="{{ asset('assets/js/waves.js') }}"></script>
        <script src="{{ asset('assets/js/sidebarmenu.js') }}"></script>
        <script src="{{ asset('assets/plugins/sticky-kit-master/dist/sticky-kit.min.js') }}"></script>
        <script src="{{ asset('assets/plugins/sparkline/jquery.sparkline.min.js') }}"></script>
        <script src="{{ asset('assets/plugins/sparkline/jquery.sparkline.min.js') }}"></script>
        <script src="{{ asset('assets/plugins/bootstrap-switch/bootstrap-switch.min.js') }}"></script>
        <script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
        <script src="{{ asset('assets/js/jquery.validate.min.js') }}"></script>
        <script src="{{ asset('assets/js/additional-methods.js') }}"></script>
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

            function checkCheckBox(element) {
                var childNodes = element.parentElement.parentElement.childNodes;
                console.log(childNodes[1].children[0]);
                childNodes[1].children[0].checked = true;
            }
            
            $(document).ready(function() {
                radioswitch.init()
            });

            $(document).on('click','#remove_trai_qualification',function(){
                var del_id = $(this).data("id"); 
                var total_row = $("#totaladdrow").val() - 1;
                m = total_row;
                $(".qty_proid_del_id"+del_id).remove();
                $("#totaladdrow").val(total_row);
                return false;
            });

            var m = 0;
            var n = 0;
            $(document).on('click','#add_mul_qty',function(){
                
                var quantity = $( "#quantity" ).val();
                var productidName = $( "#productid option:selected" ).text();
                var productidval = $( "#productid option:selected" ).val();
                if(quantity != "" && productidName!= "" && productidval!= ""){
                    var image = "{{ asset('assets/images/minus-solid.svg') }}";
                    var total_row = parseInt($("#totaladdrow").val()) + parseInt(1);
                    $(".add_multiple_qty_section").append('<div class="form-row mx-0 px-2 qty_proid_del_id'+m+'"><hr class="w-100"><div class="form-group col-sm-5"><label>Quantity</label><input type="text" name="quantity2[]" class="form-control" value='+quantity+' readonly></div><div class="form-group col-sm-5"><label> Product ID </label><input type="text" name="productid2[]" class="form-control" value='+productidName+' readonly><input type="hidden" name="productidval2[]" value='+productidval+'></div><div class="addnewqty" id="dev-new-data-here'+total_row+'" ></div><div class="col-sm-2 text-right"><label class="w-100 d-none d-sm-block d-xl-block">&nbsp;</label><button class="btn btn-default d-inline-block" id="remove_trai_qualification" data-id="'+m+'"><img src="'+image+'" style="width: 12px;"></button></div></div>'); 
                    $("#quantity").val("");
                    $("#productid").val("");
                    $("#totaladdrow").val(total_row);
                            
                    m++; n++;
                }else{
                    alert("Enter Quantity AND Select Product");
                }
                
                return false;
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
        
        <script src="{{ asset('assets/js/custom.min.js') }}"></script>
        <script src="{{ asset('assets/plugins/raphael/raphael-min.js') }}"></script>
        <script src="{{ asset('assets/plugins/morrisjs/morris.min.js') }}"></script>
        <script src="{{ asset('assets/plugins/styleswitcher/jQuery.style.switcher.js') }}"></script>
        <script src="{{asset('vendor/jquery-validation/dist/jquery.validate.js')}}"></script>
        <script src="{{asset('vendor/jquery-validation/dist/additional-methods.js')}}"></script>
        <script src="{{ asset('backend/js/toastr.min.js') }}"></script>
        <script type="text/javascript" src="{{ asset('backend/js/admin.js')}}"></script> 
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

        </script>
    </body>

</html>