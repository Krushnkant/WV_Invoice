@extends('admin.layout')

@section('content')
    <div class="row page-titles mx-0">
        <div class="col p-md-0">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)">Product List</a></li>
            </ol>
        </div>
    </div>
    <!-- row -->

    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Product List</h4>

                        <div class="action-section">
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#ProductModal" id="AddProductBtn"><i class="fa fa-plus" aria-hidden="true"></i></button>
                            {{-- <button class="btn btn-danger" onclick="deleteMultipleAttributes()"><i class="fa fa-trash" aria-hidden="true"></i></button>--}}
                        </div>

                        <div class="table-responsive">
                            <table id="Product" class="table zero-configuration customNewtable" style="width:100%">
                                <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Image</th>
                                    <th>Title</th>
                                    <th>Description</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tfoot>
                                <tr>
                                    <th>No.</th>
                                    <th>Image</th>
                                    <th>Title</th>
                                    <th>Description</th>
                                    <th>Price</th>
                                    <th>Stock</th>
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

    <div class="modal fade" id="ProductModal">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form class="form-valide" action="" id="ProductForm" method="post" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="formtitle">Add Product</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div id="attr-cover-spin" class="cover-spin"></div>
                        {{ csrf_field() }}

                        <div class="form-group ">
                            <label class="col-form-label" for="title">Title (English) <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control input-flat" id="title_english" name="title_english" placeholder="">
                            <div id="title_english-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                        </div>

                        <div class="form-group ">
                            <label class="col-form-label" for="title">Title (Hindi) <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control input-flat" id="title_hindi" name="title_hindi" placeholder="">
                            <div id="title_hindi-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                        </div>

                        <div class="form-group ">
                            <label class="col-form-label" for="title">Title (Gujarati) <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control input-flat" id="title_gujarati" name="title_gujarati" placeholder="">
                            <div id="title_gujarati-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                        </div>

                        <div class="form-group ">
                            <label class="col-form-label" for="description">Description
                            </label>
                            <input type="text" class="form-control input-flat" id="description" name="description" placeholder="">
                            <div id="description-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                        </div>

                        <div class="form-group ">
                            <label class="col-form-label" for="image">Image
                            </label>
                            <input type="file" class="form-control-file" id="image" onchange="" name="image">
                            <div id="image-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                            <img src="{{ url('public/images/placeholder_image.png') }}" class="" id="image_show" height="50px" width="50px" style="margin-top: 5px">
                        </div>

                        <div class="form-group ">
                            <label class="col-form-label" for="price">Price <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control input-flat" id="price" name="price" placeholder="">
                            <div id="price-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                        </div>

                        <div class="form-group" id="is_update_for_all_div" style="display: none">
                            <div class="form-check">
                                <label class="form-check-label">
                                    <input type="checkbox" id="is_update_for_all" class="form-check-input" value="0" name="is_update_for_all">Do you want to update price for all customers?
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="product_id" id="product_id">
{{--                        <button type="button" class="btn btn-outline-primary" data-dismiss="modal">Close</button>--}}
                        <button type="button" class="btn btn-outline-primary" id="save_newProductBtn">Save & New <i class="fa fa-circle-o-notch fa-spin loadericonfa" style="display:none;"></i></button>
                        <button type="button" class="btn btn-primary" id="save_closeProductBtn">Save & Close <i class="fa fa-circle-o-notch fa-spin loadericonfa" style="display:none;"></i></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="DeleteProductModal">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Remove Product</h5>
                </div>
                <div class="modal-body">
                    Are you sure you wish to remove this Product?
                </div>
                <div class="modal-footer">
                    <button class="btn btn-default" data-dismiss="modal" type="button">Cancel</button>
                    <button class="btn btn-danger" id="RemoveProductSubmit" type="submit">Remove <i class="fa fa-circle-o-notch fa-spin removeloadericonfa" style="display:none;"></i></button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
<!-- product JS start -->
<script type="text/javascript">
    $(document).ready(function() {
        Product_Table(true);
    });

    function save_product(btn,btn_type){
        $(btn).prop('disabled',true);
        $(btn).find('.loadericonfa').show();

        var action  = $(btn).attr('data-action');

        var formData = new FormData($("#ProductForm")[0]);

        formData.append('action',action);

        $.ajax({
            type: 'POST',
            url: "{{ url('admin/addorupdateProduct') }}",
            data: formData,
            processData: false,
            contentType: false,
            success: function (res) {
                if(res.status == 'failed'){
                    $(btn).prop('disabled',false);
                    $(btn).find('.loadericonfa').hide();
                    if (res.errors.image) {
                        $('#image-error').show().text(res.errors.image);
                    } else {
                        $('#image-error').hide();
                    }

                    if (res.errors.title_english) {
                        $('#title_english-error').show().text(res.errors.title_english);
                    } else {
                        $('#title_english-error').hide();
                    }

                    if (res.errors.title_hindi) {
                        $('#title_hindi-error').show().text(res.errors.title_hindi);
                    } else {
                        $('#title_hindi-error').hide();
                    }

                    if (res.errors.title_gujarati) {
                        $('#title_gujarati-error').show().text(res.errors.title_gujarati);
                    } else {
                        $('#title_gujarati-error').hide();
                    }

                    if (res.errors.description) {
                        $('#description-error').show().text(res.errors.description);
                    } else {
                        $('#description-error').hide();
                    }

                    if (res.errors.price) {
                        $('#price-error').show().text(res.errors.price);
                    } else {
                        $('#price-error').hide();
                    }
                }

                if(res.status == 200){
                    if(btn_type == 'save_close'){
                        $("#ProductModal").modal('hide');
                        $(btn).prop('disabled',false);
                        $(btn).find('.loadericonfa').hide();
                        if(res.action == 'add'){
                            Product_Table(true);
                            toastr.success("Product Added",'Success',{timeOut: 5000});
                        }
                        if(res.action == 'update'){
                            Product_Table();
                            toastr.success("Product Updated",'Success',{timeOut: 5000});
                        }
                    }

                    if(btn_type == 'save_new'){
                        $(btn).prop('disabled',false);
                        $(btn).find('.loadericonfa').hide();
                        $("#ProductModal").find('form').trigger('reset');
                        $("#ProductModal").find("#save_newProductBtn").removeAttr('data-action');
                        $("#ProductModal").find("#save_closeProductBtn").removeAttr('data-action');
                        $("#ProductModal").find("#save_newProductBtn").removeAttr('data-id');
                        $("#ProductModal").find("#save_closeProductBtn").removeAttr('data-id');
                        $('#product_id').val("");
                        $('#image-error').html("");
                        $('#title_english-error').html("");
                        $('#title_hindi-error').html("");
                        $('#title_gujarati-error').html("");
                        $('#description-error').html("");
                        $('#price-error').html("");
                        $('#stock-error').html("");
                        var default_image = "{{ url('public/images/placeholder_image.png') }}";
                        $('#image_show').attr('src', default_image);
                        $("#title_english").focus();
                        if(res.action == 'add'){
                            Product_Table(true);
                            toastr.success("Product Added",'Success',{timeOut: 5000});
                        }
                        if(res.action == 'update'){
                            Product_Table();
                            toastr.success("Product Updated",'Success',{timeOut: 5000});
                        }
                    }
                }

                if(res.status == 400){
                    $("#ProductModal").modal('hide');
                    $(btn).prop('disabled',false);
                    $(btn).find('.loadericonfa').hide();
                    Product_Table();
                    toastr.error("Please try again",'Error',{timeOut: 5000});
                }
            },
            error: function (data) {
                $("#ProductModal").modal('hide');
                $(btn).prop('disabled',false);
                $(btn).find('.loadericonfa').hide();
                Product_Table();
                toastr.error("Please try again",'Error',{timeOut: 5000});
            }
        });
    }

    $('body').on('click', '#save_newProductBtn', function () {
        save_product($(this),'save_new');
    });

    $('body').on('click', '#save_closeProductBtn', function () {
        save_product($(this),'save_close');
    });

    $('#ProductModal').on('shown.bs.modal', function (e) {
        $("#title_english").focus();
    });

    $('#image').change(function(){
        $('#image-error').hide();
        var file = this.files[0];
        var fileType = file["type"];
        var validImageTypes = ["image/jpeg", "image/png", "image/jpg"];
        if ($.inArray(fileType, validImageTypes) < 0) {
            $('#image-error').show().text("Please provide a Valid Extension Image(e.g: .jpg .png)");
            var default_image = "{{ url('public/images/placeholder_image.png') }}";
            $('#image_show').attr('src', default_image);
        }
        else {
            let reader = new FileReader();
            reader.onload = (e) => {
                $('#image_show').attr('src', e.target.result);
            }
            reader.readAsDataURL(this.files[0]);
        }
    });

    $('#ProductModal').on('hidden.bs.modal', function () {
        $(this).find('form').trigger('reset');
        $(this).find("#save_newProductBtn").removeAttr('data-action');
        $(this).find("#save_closeProductBtn").removeAttr('data-action');
        $(this).find("#save_newProductBtn").removeAttr('data-id');
        $(this).find("#save_closeProductBtn").removeAttr('data-id');
        $('#product_id').val("");
        $('#image-error').html("");
        $('#title_english-error').html("");
        $('#title_hindi-error').html("");
        $('#title_gujarati-error').html("");
        $('#description-error').html("");
        $('#price-error').html("");
        $('#stock-error').html("");
        var default_image = "{{ url('public/images/placeholder_image.png') }}";
        $('#image_show').attr('src', default_image);
        $("#is_update_for_all").val(0);
        $("#is_update_for_all").attr('checked', false);
        $("#is_update_for_all_div").hide();
    });

    $('#DeleteProductModal').on('hidden.bs.modal', function () {
        $(this).find("#RemoveProductSubmit").removeAttr('data-id');
    });

    function Product_Table(is_clearState=false) {
        if(is_clearState){
            $('#Product').DataTable().state.clear();
        }

        $('#Product').DataTable({
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
                "url": "{{ url('admin/allProductslist') }}",
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
                { "width": "120px", "targets": 5 },
                { "width": "115px", "targets": 6 },
                { "width": "115px", "targets": 7 },
            ],
            "columns": [
                {data: 'id', name: 'id', class: "text-center", orderable: false,
                    render: function (data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {data: 'image', name: 'image', class: "text-center", orderable: false},
                {data: 'title', name: 'title', class: "text-left multirow"},
                {data: 'description', name: 'description', class: "text-left"},
                {data: 'price', name: 'price'},
                {data: 'stock', name: 'stock'},
                {data: 'created_at', name: 'created_at', searchable: false, class: "text-left"},
                {data: 'action', name: 'action', orderable: false, searchable: false, class: "text-center"},
            ]
        });
    }

    $('body').on('click', '#AddProductBtn', function (e) {
        $("#ProductModal").find('.modal-title').html("Add Product");
    });

    $('body').on('click', '#editProductBtn', function () {
        var product_id = $(this).attr('data-id');
        $.get("{{ url('admin/products') }}" +'/' + product_id +'/edit', function (data) {
            $('#ProductModal').find('.modal-title').html("Edit Product");
            $('#ProductModal').find('#save_closeProductBtn').attr("data-action","update");
            $('#ProductModal').find('#save_newProductBtn').attr("data-action","update");
            $('#ProductModal').find('#save_closeProductBtn').attr("data-id",product_id);
            $('#ProductModal').find('#save_newProductBtn').attr("data-id",product_id);
            $('#product_id').val(data.id);
            if(data.image==null){
                var default_image = "{{ url('public/images/placeholder_image.png') }}";
                $('#image_show').attr('src', default_image);
            }
            else{
                var image = "{{ url('public/images/product') }}" +"/" + data.image;
                $('#image_show').attr('src', image);
            }
            $('#title_english').val(data.title_english);
            $('#title_hindi').val(data.title_hindi);
            $('#title_gujarati').val(data.title_gujarati);
            $('#description').val(data.description);
            $('#price').val(data.price);
            $("#is_update_for_all_div").show();
        })
    });

    $('body').on('click', '#deleteProductBtn', function (e) {
        // e.preventDefault();
        var delete_product_id = $(this).attr('data-id');
        $("#DeleteProductModal").find('#RemoveProductSubmit').attr('data-id',delete_product_id);
    });

    $('body').on('click', '#RemoveProductSubmit', function (e) {
        $('#RemoveProductSubmit').prop('disabled',true);
        $(this).find('.removeloadericonfa').show();
        e.preventDefault();
        var remove_product_id = $(this).attr('data-id');

        $.ajax({
            type: 'GET',
            url: "{{ url('admin/products') }}" +'/' + remove_product_id +'/delete',
            success: function (res) {
                if(res.status == 200){
                    $("#DeleteProductModal").modal('hide');
                    $('#RemoveProductSubmit').prop('disabled',false);
                    $("#RemoveProductSubmit").find('.removeloadericonfa').hide();
                    Product_Table();
                    toastr.success("Product Deleted",'Success',{timeOut: 5000});
                }

                if(res.status == 400){
                    $("#DeleteProductModal").modal('hide');
                    $('#RemoveProductSubmit').prop('disabled',false);
                    $("#RemoveProductSubmit").find('.removeloadericonfa').hide();
                    Product_Table();
                    toastr.error("Please try again",'Error',{timeOut: 5000});
                }
            },
            error: function (data) {
                $("#DeleteProductModal").modal('hide');
                $('#RemoveProductSubmit').prop('disabled',false);
                $("#RemoveProductSubmit").find('.removeloadericonfa').hide();
                Product_Table();
                toastr.error("Please try again",'Error',{timeOut: 5000});
            }
        });
    });

    $(document).on('change', '#is_update_for_all', function(e) {
        if ($(this).is(':checked')) {
            $(this).val(1);
            $(this).attr('checked', true);
        }
        else {
            $(this).val(0);
            $(this).attr('checked', false);
        }
    });
</script>
<!-- product JS end -->
@endsection

