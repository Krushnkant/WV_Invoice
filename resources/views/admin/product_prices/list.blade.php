@extends('admin.layout')

@section('content')
    <div class="row page-titles mx-0">
        <div class="col p-md-0">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)">Product Price List</a></li>
            </ol>
        </div>
    </div>
    <!-- row -->

    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Product Price List</h4>

                        <div class="action-section">
                            <button type="button" class="btn btn-primary" id="printBtn">Download PDF</button>
{{--                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#ProductPriceModal" id="AddProductPriceBtn"><i class="fa fa-plus" aria-hidden="true"></i></button>--}}
                        </div>

                        <div class="table-responsive">
                            <table id="ProductPrice" class="table zero-configuration customNewtable" style="width:100%">
                                <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tfoot>
                                <tr>
                                    <th>No.</th>
                                    <th>Product</th>
                                    <th>Price</th>
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

    <div class="modal fade" id="ProductPriceModal">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form class="form-valide" action="" id="ProductPriceForm" method="post" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="formtitle">Add Customer Price</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div id="attr-cover-spin" class="cover-spin"></div>
                        {{ csrf_field() }}

                        <div class="form-group ">
                            <label class="col-form-label" for="Customer">Customer <span class="text-danger">*</span>
                            </label>
                            <select class="" id="customer" name="customer" ></select>
                            <div id="customer-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                        </div>

                        <div class="form-group ">
                            <label class="col-form-label" for="Product">Product <span class="text-danger">*</span>
                            </label>
                            <select class="" id="product" name="product" ></select>
                            <div id="product-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                        </div>

                        <div class="form-group ">
                            <label class="col-form-label" for="Price">Price <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control input-flat" id="price" name="price" >
                            <div id="price-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="product_price_id" id="product_price_id">
{{--                        <button type="button" class="btn btn-outline-primary" id="save_newProductPriceBtn">Save & New <i class="fa fa-circle-o-notch fa-spin loadericonfa" style="display:none;"></i></button>--}}
                        <button type="button" class="btn btn-primary" id="save_closeProductPriceBtn">Save & Close <i class="fa fa-circle-o-notch fa-spin loadericonfa" style="display:none;"></i></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="DeleteProductPriceModal">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Remove Customer Price</h5>
                </div>
                <div class="modal-body">
                    Are you sure you wish to remove this Customer Price?
                </div>
                <div class="modal-footer">
                    <button class="btn btn-default" data-dismiss="modal" type="button">Cancel</button>
                    <button class="btn btn-danger" id="RemoveProductPriceSubmit" type="submit">Remove <i class="fa fa-circle-o-notch fa-spin removeloadericonfa" style="display:none;"></i></button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
<!-- product price JS start -->
<script type="text/javascript">
$('body').on('click', '#AddProductPriceBtn', function (e) {
    $("#ProductPriceModal").find('.modal-title').html("Add Customer Price");
    $.get("{{ url('admin/get_customers_products') }}", function (res) {
        $("#customer").empty();
        $("#product").empty();

        $('#customer').select2({
            width: '100%',
            placeholder: "Select Customer",
            allowClear: false
        });
        $("#customer").append("<option value='' disabled selected>Select Customer</option>");
        var customers = res['customers'];
        for(var i = 0; i < customers.length; i++) {
            $("#customer").append("<option value='"+ customers[i].id +"'>"+ customers[i].full_name +"</option>");
        }

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

$('body').on('click', '#save_newProductPriceBtn', function () {
    save_product_price($(this),'save_new');
});

$('body').on('click', '#save_closeProductPriceBtn', function () {
    save_product_price($(this),'save_close');
});

function save_product_price(btn,btn_type){
    $(btn).prop('disabled',true);
    $(btn).find('.loadericonfa').show();

    var action  = $(btn).attr('data-action');

    var formData = new FormData($("#ProductPriceForm")[0]);

    formData.append('action',action);

    $.ajax({
        type: 'POST',
        url: "{{ url('admin/addorupdateProductPrice') }}",
        data: formData,
        processData: false,
        contentType: false,
        success: function (res) {
            if(res.status == 'failed'){
                $(btn).prop('disabled',false);
                $(btn).find('.loadericonfa').hide();

                if (res.errors.customer) {
                    $('#customer-error').show().text(res.errors.customer);
                } else {
                    $('#customer-error').hide();
                }

                if (res.errors.product) {
                    $('#product-error').show().text(res.errors.product);
                } else {
                    $('#product-error').hide();
                }

                if (res.errors.price) {
                    $('#price-error').show().text(res.errors.price);
                } else {
                    $('#price-error').hide();
                }
            }

            if(res.status == 200){
                if(btn_type == 'save_close'){
                    $("#ProductPriceModal").modal('hide');
                    $(btn).prop('disabled',false);
                    $(btn).find('.loadericonfa').hide();
                    if(res.action == 'add'){
                        Product_Price_Table(true);
                        toastr.success("Customer Price Added",'Success',{timeOut: 5000});
                    }
                    if(res.action == 'update'){
                        Product_Price_Table();
                        toastr.success("Customer Price Updated",'Success',{timeOut: 5000});
                    }
                }

                if(btn_type == 'save_new'){
                    $(btn).prop('disabled',false);
                    $(btn).find('.loadericonfa').hide();
                    $("#ProductPriceModal").find('form').trigger('reset');
                    $("#ProductPriceModal").find("#save_newProductPriceBtn").removeAttr('data-action');
                    $("#ProductPriceModal").find("#save_closeProductPriceBtn").removeAttr('data-action');
                    $("#ProductPriceModal").find("#save_newProductPriceBtn").removeAttr('data-id');
                    $("#ProductPriceModal").find("#save_closeProductPriceBtn").removeAttr('data-id');
                    $('#product_price_id').val("");
                    $('#customer-error').html("");
                    $('#product-error').html("");
                    $('#price-error').html("");
                    $("#customer").focus();
                    if(res.action == 'add'){
                        Product_Price_Table(true);
                        toastr.success("Customer Price Added",'Success',{timeOut: 5000});
                    }
                    if(res.action == 'update'){
                        Product_Price_Table();
                        toastr.success("Customer Price Updated",'Success',{timeOut: 5000});
                    }
                }
            }

            if(res.status == 400){
                $("#ProductPriceModal").modal('hide');
                $(btn).prop('disabled',false);
                $(btn).find('.loadericonfa').hide();
                Product_Price_Table();
                toastr.error("Please try again",'Error',{timeOut: 5000});
            }

            if(res.status == 401){
                $(btn).prop('disabled',false);
                $(btn).find('.loadericonfa').hide();
                toastr.error(res.error,'Error',{timeOut: 5000});
            }
        },
        error: function (data) {
            $("#ProductPriceModal").modal('hide');
            $(btn).prop('disabled',false);
            $(btn).find('.loadericonfa').hide();
            Product_Price_Table();
            toastr.error("Please try again",'Error',{timeOut: 5000});
        }
    });
}

$(document).ready(function() {
    Product_Price_Table(true);
});

function Product_Price_Table(is_clearState=false) {
    if(is_clearState){
        $('#ProductPrice').DataTable().state.clear();
    }

    $('#ProductPrice').DataTable({
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
            "url": "{{ url('admin/allProductPriceslist') }}",
            "dataType": "json",
            "type": "POST",
            "data":{ _token: '{{ csrf_token() }}', user_id: "{{ $user_id }}"},
            // "dataSrc": ""
        },
        'columnDefs': [
            { "width": "50px", "targets": 0 },
            { "width": "145px", "targets": 1 },
            { "width": "165px", "targets": 2 },
            { "width": "230px", "targets": 3 },
            { "width": "75px", "targets": 4 },
        ],
        "columns": [
            {data: 'id', name: 'id', class: "text-center", orderable: false,
                render: function (data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
            {data: 'product', name: 'product', class: "text-left", orderable: false},
            {data: 'price', name: 'price', orderable: false},
            {data: 'created_at', name: 'created_at', searchable: false, class: "text-left"},
            {data: 'action', name: 'action', orderable: false, searchable: false, class: "text-center"},
        ]
    });
}

$('#ProductPriceModal').on('shown.bs.modal', function (e) {
    $("#customer").focus();
});

$('#ProductPriceModal').on('hidden.bs.modal', function () {
    $("#customer").prop('disabled',false);
    $("#product").prop('disabled',false);
    $(this).find('form').trigger('reset');
    $(this).find("#save_newProductPriceBtn").removeAttr('data-action');
    $(this).find("#save_closeProductPriceBtn").removeAttr('data-action');
    $(this).find("#save_newProductPriceBtn").removeAttr('data-id');
    $(this).find("#save_closeProductPriceBtn").removeAttr('data-id');
    $('#product_price_id').val("");
    $('#customer-error').html("");
    $('#product-error').html("");
    $('#price-error').html("");
    $("#customer").empty();
    $("#product").empty();
});

$('body').on('click', '#editProductPriceBtn', function () {
    var product_price_id = $(this).attr('data-id');
    $.get("{{ url('admin/product_prices') }}" +'/' + product_price_id +'/edit', function (data) {
        $('#ProductPriceModal').find('.modal-title').html("Edit Customer Price");
        $('#ProductPriceModal').find('#save_closeProductPriceBtn').attr("data-action","update");
        $('#ProductPriceModal').find('#save_newProductPriceBtn').attr("data-action","update");
        $('#ProductPriceModal').find('#save_closeProductPriceBtn').attr("data-id",product_price_id);
        $('#ProductPriceModal').find('#save_newProductPriceBtn').attr("data-id",product_price_id);
        $('#product_price_id').val(data['ProductPrice'].id);

        $("#customer").empty();
        $("#product").empty();

        $('#customer').select2({
            width: '100%',
            placeholder: "Select Customer",
            allowClear: false
        });
        var customers = data['customers'];
        for(var i = 0; i < customers.length; i++) {
            $("#customer").append("<option value='"+ customers[i].id +"'>"+ customers[i].full_name +" [" + customers[i].id + "]</option>");
        }
        $('#customer').find("option[value="+data['ProductPrice'].user_id+"]").prop("selected", true);

        $('#product').select2({
            width: '100%',
            placeholder: "Select Product",
            allowClear: false
        });
        var products = data['products'];
        for(var i = 0; i < products.length; i++) {
            $("#product").append("<option value='"+ products[i].id +"'>"+ products[i].title_english +"</option>");
        }
        $('#product').find("option[value="+data['ProductPrice'].product_id+"]").prop("selected", true);
        $('#price').val(data['ProductPrice'].price);

        $("#customer").prop('disabled',true);
        $("#product").prop('disabled',true);
    })
});

$("#product").change(function() {
    var product_id = $(this).val();
    $.get("{{ url('admin/get_products_price') }}" + "/" + product_id, function (res) {
        $("#price").val(res);
    })
})

$('body').on('click', '#deleteProductPriceBtn', function (e) {
    // e.preventDefault();
    var product_price_id = $(this).attr('data-id');
    $("#DeleteProductPriceModal").find('#RemoveProductPriceSubmit').attr('data-id',product_price_id);
});

$('#DeleteProductPriceModal').on('hidden.bs.modal', function () {
    $(this).find("#RemoveProductPriceSubmit").removeAttr('data-id');
});

$('body').on('click', '#RemoveProductPriceSubmit', function (e) {
    $('#RemoveProductPriceSubmit').prop('disabled',true);
    $(this).find('.removeloadericonfa').show();
    e.preventDefault();
    var product_price_id = $(this).attr('data-id');

    $.ajax({
        type: 'GET',
        url: "{{ url('admin/product_prices') }}" +'/' + product_price_id +'/delete',
        success: function (res) {
            if(res.status == 200){
                $("#DeleteProductPriceModal").modal('hide');
                $('#RemoveProductPriceSubmit').prop('disabled',false);
                $("#RemoveProductPriceSubmit").find('.removeloadericonfa').hide();
                Product_Price_Table();
                toastr.success("Customer Price Deleted",'Success',{timeOut: 5000});
            }

            if(res.status == 400){
                $("#DeleteProductPriceModal").modal('hide');
                $('#RemoveProductPriceSubmit').prop('disabled',false);
                $("#RemoveProductPriceSubmit").find('.removeloadericonfa').hide();
                Product_Price_Table();
                toastr.error("Please try again",'Error',{timeOut: 5000});
            }
        },
        error: function (data) {
            $("#DeleteProductPriceModal").modal('hide');
            $('#RemoveProductPriceSubmit').prop('disabled',false);
            $("#RemoveProductPriceSubmit").find('.removeloadericonfa').hide();
            Product_Price_Table();
            toastr.error("Please try again",'Error',{timeOut: 5000});
        }
    });
});

$('body').on('click', '#printBtn', function (e) {
    // e.preventDefault();
    var user_id = "{{ $user_id }}";
    var url = "{{ url('admin/product_prices/pdf') }}" + "/" + user_id;
    window.open(url,'_blank');
});
</script>
<!-- product price JS end -->
@endsection
