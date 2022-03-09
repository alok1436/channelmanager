@extends('layouts.default')
@section('content')
 
<div class="panel panel-default panel_ebay_order_list">
    <div class="panel-heading">
        <h6 class="panel-title">Files</h6>
    </div>
    <div class="table-responsive ebay_order_tablelist">
        <table class="table table-bordered table-check amz-ord-table-set">
            <thead>
                <tr class="head_ct">
                    <th align="center">
                        <a sort_direction="asc" class="sorting_link sorting" sort_by="order_channel_id" href="javascript:;">
                            <span class="titl1">File</span>
                        </a>
                    </th>
                    <th align="center">
                        <a sort_direction="asc" class="sorting_link sorting" sort_by="order_channel_id" href="javascript:;">
                            <span class="titl1">Date</span>
                        </a>
                    </th>
                    <th align="center">
                        <a sort_direction="asc" class="sorting_link sorting" sort_by="order_channel_id" href="javascript:;">
                            <span class="titl1">Type</span>
                        </a>
                    </th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody class="supplier_tbody">
                @foreach($documents as $row)

                	<tr>
                		<td class="text-center"> <i class="fa fa-file"></i> {{ $row->file }} </td>
                		<td> {{ $row->created_at }} </td>
                		<td> {{ $row->type }} </td>
                		<td>
                			 <a href="{{ route('order.documents.download',['id'=>$row->id]) }}" class="btn btn-success">Download</a>
                		</td>
                	</tr>

                @endforeach
            </tbody>
        </table>
    </div>
    <div>{{ $documents->links() }}</div>
</div>
<div class="modal fade" id="modalforeditinvoice" role="dialog" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Invoice form</h4>
            </div>
            
            <div id="formcontent"></div>
            
        </div>
    </div>
</div>
<style>
.panel-default > .panel-heading, .panel-default, .small-padding-table, .panel.padding-20, .product-box.product_pull_out {
    box-shadow: 0 0 3px rgba(0, 0, 0, 0.1);
}
.panel {
    border-radius: 2px;
}
.panel-default {
    border-color: #ddd;
}
.panel {
    margin-bottom: 20px;
    background-color: #fff;
    border: 1px solid transparent;
    border-radius: 4px;
    -webkit-box-shadow: 0 1px 1px rgba(0,0,0,.05);
    box-shadow: 0 1px 1px rgba(0,0,0,.05);
}
.panel-default>.panel-heading {
    background-color: #f3f3f3;
}
.panel-default>.panel-heading {
    color: #333;
    background-color: #f5f5f5;
    border-color: #ddd;
}
.panel-heading {
    border-top-left-radius: 2px;
    border-top-right-radius: 2px;
    padding: 0;
    position: relative;
}
.panel-heading {
    border-bottom: 1px solid transparent;
}
.panel-title {
    float: left;
    display: block;
    font-size: 14px;
    padding: 11px 12px 12px;
}
.h1, .h2, .h3, .h4, .h5, .h6, h1, h2, h3, h4, h5, h6 {
    font-weight: 600;
    line-height: 1.42857143;
    margin-top: 0;
}
.header-bar-stats .page-stats li:after, .page-header:after, .panel-heading:after, .panel-toolbar:after {
    content: "";
    display: table;
    clear: both;
}
.panel .table-responsive:last-child>.table:last-child, .panel .table:last-child {
    border-bottom-right-radius: 0;
    border-bottom-left-radius: 0;
}
.panel .table-responsive:first-child>.table:first-child, .panel .table:first-child {
    border-top-left-radius: 0;
    border-top-right-radius: 0;
}
.panel .table-bordered, .panel .table-responsive>.table-bordered {
    border: 0;
}
.table-responsive table {
    background: #fff;
    font-family: 'Open Sans', sans-serif !important;
}
.panel>.table, .panel>.table-responsive>.table {
    margin-bottom: 0;
}
.table-check {
    font-family: arial;
    font-size: 11px;
}
.table-responsive thead {
    z-index: 9999;
}
.table-responsive thead {
    background: #546672 none repeat scroll 0 0;
    left: 0;
    right: 0;
    top: 310px;
}
.supplier_tbody {
    background: #ffffff;
}
tbody {
    color: #494949;
}
.wrap_plus.table-image-box img.product-img {
    max-height: 160px;
    max-width: 150px;
    width: auto;
}
.table-controls {
    white-space: nowrap;
}
.table-controls {
    text-align: center;
}
.table-controls .btn-xs.btn-icon {
    margin: 3px 0 0 !important;
    padding: 3px !important;
    width: 25px !important;
}
.supplier_tbody td a {
    color: #333;
}
.btn {
    -moz-user-select: none;
    border-radius: 0;
    box-shadow: 0 1px 1px #bfbfbf;
    font-size: 12px;
    font-weight: 600;
    padding: 7px 16px 8px;
}
.table-responsive table thead tr a span, table th, table th span, table th label {
    color: #ffffff;
}
.table > thead > tr > th a, .table > thead > tr > th span {
    white-space: nowrap;
}
.radio label, .checkbox label {
    display: inline;
    font-weight: 400;
    cursor: pointer;
}

.table-bordered>tbody>tr>td, .table-bordered>tbody>tr>th, .table-bordered>tfoot>tr>td, .table-bordered>tfoot>tr>th, .table-bordered>thead>tr>td, .table-bordered>thead>tr>th {
    border: 1px solid #e4e7ea;
}
.table>tbody>tr>td, .table>tbody>tr>th, .table>tfoot>tr>td, .table>tfoot>tr>th, .table>thead>tr>td, .table>thead>tr>th {
    /*vertical-align: middle;*/
    padding: 10px 12px;
}
.buyer_name_cls {
    border-top: 1px solid #dedede;
    margin: 10px 0 6px;
    border-top: 1px solid #dedede;
}
.buyer_name_cls a.table-td-link {
    display: inline-block;
    margin: 0 0 5px;
}
.table-td-link {
    color: #750a00 !important;
    opacity: 0.7;
}
.checkbox input[type=checkbox], .checkbox-inline input[type=checkbox], .radio input[type=radio], .radio-inline input[type=radio] {
    margin-left: -20px;
    margin-right: 8px;
}
input[type=checkbox], input[type=radio] {
    margin: 3px 0 0;
}
.ebay_order_tablelist .checkbox {
    left: 0 !important;
    width: 13px !important;
}
.table > thead > tr > th .sorting {
    width: 100%;
    text-align: center;
    display: inline-block;
    padding: 0;
}
.supplier_tbody tr td {
    font-size: 13px;
}
.checkbox {
    left: 5px;
    position: relative;
}
.checkbox, .radio {
    padding-left: 0;
}
.table-responsive.ebay_order_tablelist table tr td {
    padding: 2px !important;
}
.ebay_order_tablelist table tr td:first-child {
    text-align: center;
    /*width: 20px;*/
}
.table-bordered>tbody>tr>td, .table-bordered>tbody>tr>th, .table-bordered>tfoot>tr>td, .table-bordered>tfoot>tr>th, .table-bordered>thead>tr>td, .table-bordered>thead>tr>th {
    border: 1px solid #e4e7ea;
}
[type=checkbox]+label:before, [type=checkbox]:not(.filled-in)+label:after{
    display: none;
}

#product-table thead th {
    color: #000 !important;
}
.modal-backdrop.fade {
    opacity: .5 !important;
}

.modal-header {
    display: block !important;
}
#product-table_filter input {
    border: 1px solid #ccc;
    padding: 6px 12px;
    border-radius: 4px;   
    background-image: none;
}



.panel>.table-responsive {
    margin-bottom: 0;
    border: 0;
    height: 70vh;
}
.head_ct{
    background: #546672 none repeat scroll 0 0;
    position: sticky;
    top: 0;
    z-index: 10;
}
.modal-lg {
    max-width: 1200px !important;
}
</style>
                                    
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
        </div>
        
@endsection