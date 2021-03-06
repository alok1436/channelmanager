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
                        <h3 class="text-themecolor">View Warehouse</h3>
                    </div>
                    <div class="col-md-7 align-self-center">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                            <li class="breadcrumb-item">pages</li>
                            <li class="breadcrumb-item active">View Warehouse</li>
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
                            <a href="warehouseAddView" class="btn btn-info" style="margin-bottom: 20px;">Add Warehouse</a>
                            <div class="card">
                                <div class="card-body">
                                    <div class="col-xs-12">
                                        <table id="myTable" class="table table-striped table-bordered table-responsive">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Company</th>
                                                    <th>Short Name</th>
                                                    <th>Location</th>
                                                    <th>Street 1</th>
                                                    <th>Street 2</th>
                                                    <th>Plz</th>
                                                    <th>City</th>
                                                    <th>Provice</th>
                                                    <th>Country</th>
                                                    <th>Phone</th>
                                                    <th>Fax</th>
                                                    <th>Email</th>
                                                    <th>Note</th>
                                                    <th>Action</th>                                                
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($warehouse as $row)
                                                <tr>
                                                    <td>{{$row->idwarehouse}}</td>
                                                    <td class="td-field">
                                                        <span class="field-value">{{$row->cm_shortname}}</span>
                                                        <div class="field-edit">
                                                            <select name="idcompany" class="form-control" data-id="{{$row->idwarehouse}}" data-field="idcompany">
                                                                @foreach($companies as $comp)
                                                                    <option value="{{$comp->idcompany}}"
                                                                        @if ($comp->idcompany==$row->idcompany)
                                                                            selected=""
                                                                        @endif
                                                                        >
                                                                        {{$comp->shortname}}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </td>
                                                
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->shortname}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="shortname" class="form-control" value="{{$row->shortname}}" data-id="{{$row->idwarehouse}}" data-field="shortname">
                                                        </div>
                                                    </td>
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->location}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="location" class="form-control" value="{{$row->location}}" data-id="{{$row->idwarehouse}}" data-field="location">
                                                        </div>
                                                    </td>
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->street1}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="street1" class="form-control" value="{{$row->street1}}" data-id="{{$row->idwarehouse}}" data-field="street1">
                                                        </div>
                                                    </td>
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->street2}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="street2" class="form-control" value="{{$row->street2}}" data-id="{{$row->idwarehouse}}" data-field="street2">
                                                        </div>
                                                    </td>
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->plz}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="plz" class="form-control" value="{{$row->plz}}" data-id="{{$row->idwarehouse}}" data-field="plz">
                                                        </div>
                                                    </td>
                                
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->city}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="city" class="form-control" value="{{$row->city}}" data-id="{{$row->idwarehouse}}" data-field="city">
                                                        </div>
                                                    </td>
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->province}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="province" class="form-control" value="{{$row->province}}" data-id="{{$row->idwarehouse}}" data-field="province">
                                                        </div>
                                                    </td>
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->country}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="country" class="form-control" value="{{$row->country}}" data-id="{{$row->idwarehouse}}" data-field="country">
                                                        </div>
                                                    </td>
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->phone}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="phone" class="form-control" value="{{$row->phone}}" data-id="{{$row->idwarehouse}}" data-field="phone">
                                                        </div>
                                                    </td>

                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->fax}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="fax" class="form-control" value="{{$row->fax}}" data-id="{{$row->idwarehouse}}" data-field="fax">
                                                        </div>
                                                    </td>
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->email}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="email" class="form-control" value="{{$row->email}}" data-id="{{$row->idwarehouse}}" data-field="email">
                                                        </div>
                                                    </td>
                                                    <td class="td-field">
                                                        <span  class="field-value">{{$row->notes}}</span>
                                                        <div class="field-edit">
                                                            <input type="text" name="notes" class="form-control" value="{{$row->notes}}" data-id="{{$row->idwarehouse}}" data-field="notes">
                                                        </div>
                                                    </td>

                                                    <td>
                                                        <a href="warehouseDelete?del={{$row->idwarehouse}}" class="btn btn-danger">Delete</a>
                                                    </td>
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
                <footer class="footer">
                    ?? 2021 Semplifat powered by Confidence Europe GmbH
                </footer>
            </div>
        </div>
        <style>
            .field-edit{
                width: 100%;
                display: block;
                display: none;
            }
        </style>
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
                    var data={id:$(this).data('id'),field:$(this).data('field'),action:$(this).data('action'),value:$(this).val()};

                    $.ajax({
                        url    : 'warehouseUpdate',
                        data   : data,
                        method : 'GET',
                        success: function(result) {

                        }
                    });
                });
                
                // FOR CATEGORY
                $(document).on("change",".subcat",function(event) {
                    var data={id:$(this).data('id'),field:$(this).data('field'),value:$(this).val()};
                    $.ajax({
                        url: "manufacturerUpdate",
                        data:data,
                        method:"POST",
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
                $('#table').DataTable();
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
