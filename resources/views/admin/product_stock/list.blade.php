@extends('admin.layout')

@section('content')
    <div class="row page-titles mx-0">
        <div class="col p-md-0">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)">Stock List</a></li>
            </ol>
        </div>
    </div>
    <!-- row -->

    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Stock List</h4>

                        <div class="action-section">
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#ProductStockModal" id="AddProductStockBtn"><i class="fa fa-plus" aria-hidden="true"></i></button>
                        </div>

                        <div class="table-responsive">
                            <table id="ProductStock" class="table zero-configuration customNewtable" style="width:100%">
                                <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Product</th>
                                    <th>Stock</th>
                                    <th>Purchase From</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tfoot>
                                <tr>
                                    <th>No.</th>
                                    <th>Product</th>
                                    <th>Stock</th>
                                    <th>Purchase From</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                                </tfoot>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="ProductStockModal">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form class="form-valide" action="" id="ProductStockForm" method="post">
                    <div class="modal-header">
                        <h5 class="modal-title" id="formtitle">Add Stock</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div id="attr-cover-spin" class="cover-spin"></div>
                        {{ csrf_field() }}

                        <div class="form-group ">
                            <label class="col-form-label" for="Product">Product <span class="text-danger">*</span>
                            </label>
                            <select class="" id="product" name="product" ></select>
                            <div id="product-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                        </div>

                        <div class="form-group ">
                            <label class="col-form-label" for="Stock">Stock (Kg) <span class="text-danger">*</span>
                            </label>
                            <input type="number" class="form-control input-flat" id="stock" name="stock" >
                            <div id="stock-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                        </div>

                        <div class="form-group ">
                            <label class="col-form-label" for="purchase_from">Purchase From <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control input-flat" id="purchase_from" name="purchase_from" >
                            <div id="purchase_from-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                        </div>

                        <div class="form-group row">
                            <label class="col-lg-12 col-form-label text-left" for="">Date</label>
                            <div class="col-lg-12">
                                <input class="form-control custom_date_picker" type="text" id="stock_date" name="stock_date" placeholder="dd-mm-yyyy" data-date-format="dd-mm-yyyy">
                                <label id="stock_date-error" class="error invalid-feedback animated fadeInDown" for="invoice_date"></label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-primary" id="save_newProductStockBtn">Save & New <i class="fa fa-circle-o-notch fa-spin loadericonfa" style="display:none;"></i></button>
                        <button type="button" class="btn btn-primary" id="save_closeProductStockBtn">Save & Close <i class="fa fa-circle-o-notch fa-spin loadericonfa" style="display:none;"></i></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="DeleteProductStockModal">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Remove Stock</h5>
                </div>
                <div class="modal-body">
                    Are you sure you wish to remove this Stock?
                </div>
                <div class="modal-footer">
                    <button class="btn btn-default" data-dismiss="modal" type="button">Cancel</button>
                    <button class="btn btn-danger" id="RemoveProductStockSubmit" type="submit">Remove <i class="fa fa-circle-o-notch fa-spin removeloadericonfa" style="display:none;"></i></button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
<!-- product Stock JS start -->
<script type="text/javascript">
$('body').on('click', '#AddProductStockBtn', function (e) {
    $.get("{{ url('admin/get_customers_products') }}", function (res) {
        $("#product").empty();

        $('#product').select2({
            width: '100%',
            placeholder: "Select Product",
            allowClear: false
        });
        $("#product").append("<option value='' disabled selected>Select Product</option>");
        var products = res['products'];
        for(var i = 0; i < products.length; i++) {
            $("#product").append("<option value='"+ products[i].id +"'>"+ products[i].title_english +"</option>");
        }
    })
});

$('#ProductStockModal').on('shown.bs.modal', function (e) {
    $("#stock").focus();
});

$('#ProductStockModal').on('hidden.bs.modal', function () {
    $(this).find('form').trigger('reset');
    $(this).find("#save_newProductStockBtn").removeAttr('data-action');
    $(this).find("#save_closeProductStockBtn").removeAttr('data-action');
    $(this).find("#save_newProductStockBtn").removeAttr('data-id');
    $(this).find("#save_closeProductStockBtn").removeAttr('data-id');
    $('#product-error').html("");
    $('#stock-error').html("");
    $('#purchase_from-error').html("");
    $("#product").empty();
});

$('body').on('click', '#save_newProductStockBtn', function () {
    save_product_stock($(this),'save_new');
});

$('body').on('click', '#save_closeProductStockBtn', function () {
    save_product_stock($(this),'save_close');
});

function save_product_stock(btn,btn_type){
    $(btn).prop('disabled',true);
    $(btn).find('.loadericonfa').show();

    var formData = new FormData($("#ProductStockForm")[0]);

    $.ajax({
        type: 'POST',
        url: "{{ url('admin/addProductStock') }}",
        data: formData,
        processData: false,
        contentType: false,
        success: function (res) {
            if(res.status == 'failed'){
                $(btn).prop('disabled',false);
                $(btn).find('.loadericonfa').hide();

                if (res.errors.product) {
                    $('#product-error').show().text(res.errors.product);
                } else {
                    $('#product-error').hide();
                }

                if (res.errors.stock) {
                    $('#stock-error').show().text(res.errors.stock);
                } else {
                    $('#stock-error').hide();
                }

                if (res.errors.purchase_from) {
                    $('#purchase_from-error').show().text(res.errors.purchase_from);
                } else {
                    $('#purchase_from-error').hide();
                }
            }

            if(res.status == 200){
                if(btn_type == 'save_close'){
                    $("#ProductStockModal").modal('hide');
                    $(btn).prop('disabled',false);
                    $(btn).find('.loadericonfa').hide();
                    if(res.action == 'add'){
                        Product_Stock_Table(true);
                        toastr.success("Stock Added",'Success',{timeOut: 5000});
                    }
                    if(res.action == 'update'){
                        Product_Stock_Table();
                        toastr.success("Stock Updated",'Success',{timeOut: 5000});
                    }
                }

                if(btn_type == 'save_new'){
                    $(btn).prop('disabled',false);
                    $(btn).find('.loadericonfa').hide();
                    $("#ProductStockModal").find('form').trigger('reset');
                    $("#ProductStockModal").find("#save_newProductStockBtn").removeAttr('data-action');
                    $("#ProductStockModal").find("#save_closeProductStockBtn").removeAttr('data-action');
                    $("#ProductStockModal").find("#save_newProductStockBtn").removeAttr('data-id');
                    $("#ProductStockModal").find("#save_closeProductStockBtn").removeAttr('data-id');
                    $('#product-error').html("");
                    $('#stock-error').html("");
                    $('#purchase_from-error').html("");
                    $("#stock").focus();
                    if(res.action == 'add'){
                        Product_Stock_Table(true);
                        toastr.success("Stock Added",'Success',{timeOut: 5000});
                    }
                    if(res.action == 'update'){
                        Product_Stock_Table();
                        toastr.success("Stock Updated",'Success',{timeOut: 5000});
                    }
                }
            }
        },
        error: function (data) {
            $("#ProductStockModal").modal('hide');
            $(btn).prop('disabled',false);
            $(btn).find('.loadericonfa').hide();
            Product_Stock_Table();
            toastr.error("Please try again",'Error',{timeOut: 5000});
        }
    });
}

$(document).ready(function() {
    Product_Stock_Table(true);
});

function Product_Stock_Table(is_clearState=false) {
    if(is_clearState){
        $('#ProductStock').DataTable().state.clear();
    }

    $('#ProductStock').DataTable({
        "destroy": true,
        "processing": true,
        "serverSide": true,
        "pageLength": 100,
        'stateSave': function(){
            if(is_clearState){
                return false;
            }
            else{
                return true;
            }
        },
        "ajax":{
            "url": "{{ url('admin/allProductStocklist') }}",
            "dataType": "json",
            "type": "POST",
            "data":{ _token: '{{ csrf_token() }}'},
            // "dataSrc": ""
        },
        'columnDefs': [
            { "width": "50px", "targets": 0 },
            { "width": "145px", "targets": 1 },
            { "width": "165px", "targets": 2 },
            { "width": "230px", "targets": 3 },
            { "width": "75px", "targets": 4 },
            { "width": "75px", "targets": 5 },
        ],
        "columns": [
            {data: 'id', name: 'id', class: "text-center", orderable: false,
                render: function (data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
            {data: 'product', name: 'product', class: "text-left", orderable: false},
            {data: 'stock', name: 'stock'},
            {data: 'purchase_from', name: 'purchase_from', class: "text-left"},
            {data: 'created_at', name: 'created_at', class: "text-left"},
            {data: 'action', name: 'action', orderable: false, searchable: false, class: "text-center"},
        ]
    });
}

$('body').on('click', '#deleteProductStockBtn', function (e) {
    // e.preventDefault();
    var product_stock_id = $(this).attr('data-id');
    $("#DeleteProductStockModal").find('#RemoveProductStockSubmit').attr('data-id',product_stock_id);
});

$('#DeleteProductStockModal').on('hidden.bs.modal', function () {
    $(this).find("#RemoveProductStockSubmit").removeAttr('data-id');
});

$('body').on('click', '#RemoveProductStockSubmit', function (e) {
    $('#RemoveProductStockSubmit').prop('disabled',true);
    $(this).find('.removeloadericonfa').show();
    e.preventDefault();
    var product_stock_id = $(this).attr('data-id');

    $.ajax({
        type: 'GET',
        url: "{{ url('admin/product_stock') }}" +'/' + product_stock_id +'/delete',
        success: function (res) {
            if(res.status == 200){
                $("#DeleteProductStockModal").modal('hide');
                $('#RemoveProductStockSubmit').prop('disabled',false);
                $("#RemoveProductStockSubmit").find('.removeloadericonfa').hide();
                Product_Stock_Table();
                toastr.success("Stock Deleted",'Success',{timeOut: 5000});
            }

            if(res.status == 400){
                $("#DeleteProductStockModal").modal('hide');
                $('#RemoveProductStockSubmit').prop('disabled',false);
                $("#RemoveProductStockSubmit").find('.removeloadericonfa').hide();
                Product_Stock_Table();
                toastr.error("Please try again",'Error',{timeOut: 5000});
            }
        },
        error: function (data) {
            $("#DeleteProductStockModal").modal('hide');
            $('#RemoveProductStockSubmit').prop('disabled',false);
            $("#RemoveProductStockSubmit").find('.removeloadericonfa').hide();
            Product_Stock_Table();
            toastr.error("Please try again",'Error',{timeOut: 5000});
        }
    });
});
</script>
<!-- product Stock JS end -->
@endsection
