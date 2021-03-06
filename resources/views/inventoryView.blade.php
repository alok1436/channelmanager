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
            .container-fluid {
                padding: 0px 6px !important;
            }
            .page-titles {
                margin: 0;
            }
            div.dataTables_wrapper div.dataTables_processing {
                position: absolute;
                top: 53px !important;
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
                        <h3 class="text-themecolor">Inventory</h3>
                    </div>
                    <div class="col-md-7 align-self-center">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                            <li class="breadcrumb-item">pages</li>
                            <li class="breadcrumb-item active">View Payment</li>
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
                    @if(session()->has('idpayment'))
                    <div class='alert alert-success'>
                        <i class='fa fa-check-circle'></i> 
                        Success: Payment added
                        <button type='button' class='close' data-dismiss='alert'>&times;</button>
                    </div>
                    @endif
                    <div class="row">
                        <div class="col-12" style="padding: 0px;">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row d-none">
                                        <div class="col-sm-3">
                                            <p>Last update:  {{$update->date}}</p>
                                        </div>
                                        <div class="col-sm-4">
                                            <a href="finanzstatusUpdate?time_update=1" class="btn btn-info">Update now</a>
                                        </div>
                                    </div>
                                    <div class="row d-none">
                                        <div class="col-sm-3">
                                            <p>Last update:  {{$update->date}}</p>
                                        </div>
                                        <div class="col-sm-4">
                                            <a href="finanzstatusUpdate?time_update=1" class="btn btn-info">Update now</a>
                                        </div>
                                    </div>
                                    <div class="row mb-4 mt-4">
                                            <div class="mr-3 ml-4">
                                                <button type="button" class="btn btn-info d-block w-100" data-toggle="modal" data-target="#documentModal">Print out</button>
                                            </div>
                                    </div>
                                    <div class="col-xs-12 table-responsive">
                                        <table id="inventory-table" class="table table-striped table-bordered" style="width: 100%;">
                                            <thead>
                                                <tr>
                                                    <th>Sort</th>
                                                    <th data-colmun="modelcode" class="sort_head">Model Number</th>
                                                    <th data-colmun="nameshort" class="sort_head">Article</th>
                                                    <th data-colmun="subcat" class="sort_head">Category</th>
                                                    <th data-colmun="active" class="sort_head">Active (yes/no)</th>
                                                    <th data-colmun="virtualkit" class="sort_head">Kit (yes/no)</th>
                                                    @foreach ($warehouses as $key => $w)
                                                    <th>idwarehouse: {{$w->location}}</th>
                                                    <th>Hall</th>
                                                    <th>Area</th>
                                                    <th>Rack</th>
                                                    @endforeach
                                                    <th>Total</th>
                                                    <!--<th>Price</th>-->
                                                    <!--<th>Last  Price UpDate</th>-->
                                                    <!--<th>Total</th>-->
                                                    <!--<th>Pcs in S</th>-->
                                                    <!--<th>Pcs in L</th>-->
                                                    <!--<th>Pcs in XL</th>-->
                                                    <!--<th>Manufacturer</th>-->
                                                    <!--<th>Code Manufacturer</th>-->
                                                    <!--<th>Content</th>-->
                                                    <!--<th>Delivery Time (Time production+time delivery)</th>-->
                                                    <!--<th>Selling Range (is related to frequency of delivery from Manufacturer)</th>                                           --->
                                                </tr>
                                                
                                            </thead>
                                            
                                        </table>
                                        
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal fade" id="documentModal" role="dialog" tabindex="-1">
                    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">Select Warehouse</h4>
                            </div>
                            <div class="modal-body">
                                <form class="form-inline" action="{{ url('exportQuantity') }}" method="get" id="exportform">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                                <div class="form-group">
                                    <label for="">Select Warehouse</label>
                                    <select name="idwarehouse" id="idwarehouse" class="form-control">
                                        <option value="">---Select Option---</option>
                                        @foreach($warehouses as $res)
                                        <option value="<?php echo $res->idwarehouse ?>">{{$res->shortname}}</option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" value="modelcode" name="orderByColumn" id="orderByColumn"/>
                                    <input type="hidden" value="asc" name="orderByValue" id="orderByValue"/>
                                </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default closeprintdoc" data-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-success" id="printDocument" onclick="">Submit</button>
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
            
            $(function() {
            var t = $('#inventory-table').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 100,
                scrollY:"600px",
                scrollX:true,
                scrollCollapse: true,
                dom: 'lpftrip',
                "order": [[ 1, "asc" ]],
                "lengthMenu": [[100, 200, 500], [100, 200, 500]],
                "language": { processing: '<i class="fa fa-spinner fa-spin fa-4x fa-fw"></i><span class="sr-only">Loading...</span> '},
                ajax: "{{ route('ajax.inventory') }}",
                columns: [
                    { data: 'sort', name: 'sort' },
                    { data: 'modelcode', name: 'modelcode' },
                    { data: 'nameshort', name: 'nameshort' },
                    { data: 'subcat',    name: 'subcat' },
                    { data: 'active',    name: 'active' },
                    { data: 'virtualkit', name: 'virtualkit' },
                    @foreach($warehouses as $res)
                    { data: '{{$res->shortname}}', name: '{{$res->shortname}}' },
                    { data: 'hall_{{$res->shortname}}', name: 'hall_{{$res->shortname}}' },
                    { data: 'area_{{$res->shortname}}', name: 'area_{{$res->shortname}}' },
                    { data: 'rack_{{$res->shortname}}', name: 'rack_{{$res->shortname}}' },
                    @endforeach
                    { data: 'total_qty',    name: 'total_qty' },
                    // { data: 'price',    name: 'price' },
                    // { data: 'dateprice',    name: 'dateprice' },
                    // { data: 'total',    name: 'total' },
                    // { data: 'itemsinpaket1',    name: 'itemsinpaket1' },
                    // { data: 'itemsinpaket2',    name: 'itemsinpaket2' },
                    // { data: 'itemsinpaket3',    name: 'itemsinpaket3' },
                    // { data: 'manufacturer',    name: 'manufacturer' },
                    // { data: 'codemanu',    name: 'codemanu' },
                    // { data: 'content',    name: 'content' },
                    // { data: 'ordertime',    name: 'ordertime' },
                    // { data: 'orderrangetime',    name: 'orderrangetime' },
                ],
                createdRow: function(row, data, index) {
                    $('th', row).eq(0).addClass('sort_head');
                    $('td', row).eq(5).addClass('td-field'); // 6 is index of column
                    $('td', row).eq(6).addClass('td-field');
                    $('td', row).eq(7).addClass('td-field'); 
                    $('td', row).eq(8).addClass('td-field'); 
                    $('td', row).eq(9).addClass('td-field'); 
                    $('td', row).eq(10).addClass('td-field'); 
                    $('td', row).eq(11).addClass('td-field'); 
                    $('td', row).eq(12).addClass('td-field');
                    $('td', row).eq(13).addClass('td-field');
                },
                
            });
            
            t.on( 'order.dt search.dt page.dt', function (data) {
                
                $(".sort_head").each(function(){ console.log('hello');
                    var that = $(this);
                    // console.log('sdfsdfsdfd', that.attr('aria-sort'));
                    // that.addClass('hellllo');
                    if(that.hasClass('sorting_asc')){
                        $("#orderByColumn").val(that.attr('data-colmun'));
                        $("#orderByValue").val('asc');
                    }
                    
                    if(that.hasClass('sorting_desc')){
                        $("#orderByColumn").val(that.attr('data-colmun'));
                        $("#orderByValue").val('desc');
                    }
                    
                });
                
                // t.column(0, {search:'applied', order:'applied', page: 'applied'}).nodes().each( function (cell, i) {
                //     cell.innerHTML = i+1;
                // } );
            } );
        });   
        
            
            $(document).ready(function() {
                
                $(document).on("click","#printDocument",function(event) {
                    $("#exportform").submit();
                });
                
                $(document).on("click",".sorting",function(event) {
                    $("#orderId").val($(this).data('colmun'));
                });
                
                $(document).on("click",".td-field,.dtr-data",function(event) {
                    $(".field-value").show();
                    $('.field-edit').hide();
                    $(this).find(".field-value").hide();
                    $(this).find('.field-edit').show();
                });
                
                $(document).on('change','.field-edit .form-control', function(event) {
                    var that = $(this);
                    $('.field-edit').hide();
                    var label=$(this).parents(".field-edit").siblings('.field-value');
                    if($(this)[0].nodeName=='SELECT'){
                        label.html($(this).find(":selected").text());
                    } else {
                        label.html($(this).val());
                    }
                    label.show();
                    var data={id:$(this).data('id'),field:$(this).data('field'),action:$(this).data('action'),old:$(this).data('old'),value:$(this).val()};
                    $.ajax({
                        url    : 'paymentUpdate',
                        data   : data,
                        method : 'get',
                        success: function(result) {
                            if(that.data('action') == 'update_warehouse'){
                                that.attr('data-old', that.val());
                            }
                        }
                    });
                });
                
                // FOR CATEGORY
                $(document).on("change",".subcat",function(event) {
                    var data={id:$(this).data('id'),field:$(this).data('field'),value:$(this).val()};
                    $.ajax({
                        url: "paymentUpdate",
                        data:data,
                        method:"get",
                        success: function(result) {
                            
                        }
                    });
                });
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
                