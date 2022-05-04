@extends('admin.layout')

@section('content')
    <div class="row page-titles mx-0">
        <div class="col p-md-0">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)">Settings</a></li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12 col-lg-9 col-xl-6">
                <div class="card">
                    <div class="card-body">
                        <div class="col-lg-12">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered customNewtable" style="width:100%">
                                    <thead>
                                    <tr>
                                        <th><h4 class="text-white mt-0 mb-0">Invoice Setting</h4></th>
                                        <th colspan="2" class="text-right">
                                            <button id="editInvoiceBtn" class="btn btn-outline-white btn-sm" data-toggle="modal" data-target="#InvoiceModal">
                                                <i class="fa fa-pencil" aria-hidden="true"></i>
                                            </button>
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <th style="width: 50%">Company Name</th>
                                        <td><span id="company_name_val">{{ $Settings->company_name }}</span></td>
                                    </tr>
                                    <tr>
                                        <th style="width: 50%">Company Logo</th>
                                        <td>
                                            @if(isset($Settings->company_logo))
                                                <img src="{{ url('public/images/company/'.$Settings->company_logo) }}" width="50px" height="50px" alt="Company Logo" id="company_logo_val">
                                            @else
                                                <img src="{{ url('public/images/placeholder_image.png') }}" width="50px" height="50px" alt="Company Logo" id="company_logo_val">
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th style="width: 50%">Company Address</th>
                                        <td><span id="company_address_val">{{ $Settings->company_address }}</span></td>
                                    </tr>
                                    <tr>
                                        <th style="width: 50%">Company Mobile No.</th>
                                        <td><span id="company_mobile_no_val">{{ $Settings->company_mobile_no }}</span></td>
                                    </tr>
                                    <tr>
                                        <th style="width: 50%">Prefix for Invoice No</th>
                                        <td><span id="prefix_invoice_no_val">{{ $Settings->prefix_invoice_no }}</span></td>
                                    </tr>
                                    <tr>
                                        <th style="width: 50%">Invoice No</th>
                                        <td><span id="invoice_no_val">{{ $Settings->invoice_no }}</span></td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="InvoiceModal">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form class="form-valide" action="" id="InvoiceForm" method="post">
                    {{ csrf_field() }}
                    <div class="modal-header">
                        <h5 class="modal-title">Update Invoice Settings</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>×</span></button>
                    </div>
                    <div class="modal-body">
                        <div id="attr-cover-spin" class="cover-spin"></div>
                        <div class="form-group">
                            <label class="col-form-label" for="Company Name">Company Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control input-flat" id="company_name" name="company_name" placeholder="">
                            <div id="company_name-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                        </div>
                        <div class="form-group">
                            <label class="col-form-label" for="Logo">Company Logo <span class="text-danger">*</span>
                            </label>
                            <input type="file" class="form-control-file" id="company_logo" name="company_logo" placeholder="">
                            <div id="company_logo-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                            <img src="{{ url('public/images/placeholder_image.png') }}" class="" id="company_logo_image_show" height="50px" width="50px" style="margin-top: 5px">
                        </div>
                        <div class="form-group">
                            <label class="col-form-label" for="Company Address">Company Address <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control input-flat" id="company_address" name="company_address" placeholder=""></textarea>
                            <div id="company_address-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                        </div>
                        <div class="form-group">
                            <label class="col-form-label" for="Company Mobile No.">Company Mobile No. <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control input-flat" id="company_mobile_no" name="company_mobile_no" placeholder="">
                            <div id="company_mobile_no-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                        </div>
                        <div class="form-group">
                            <label class="col-form-label" for="Prefix for Invoice No">Prefix for Invoice No <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control input-flat" id="prefix_invoice_no" name="prefix_invoice_no" placeholder="">
                            <div id="prefix_invoice_no-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                        </div>
                        <div class="form-group">
                            <label class="col-form-label" for="Invoice No">Invoice No <span class="text-danger">*</span>
                            </label>
                            <input type="number" class="form-control input-flat" id="invoice_no" name="invoice_no" placeholder="">
                            <div id="invoice_no-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-primary" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="saveInvoiceBtn">Save <i class="fa fa-circle-o-notch fa-spin loadericonfa" style="display:none;"></i></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@section('js')
<!-- settings JS start -->
<script type="text/javascript">
    $('#InvoiceModal').on('shown.bs.modal', function (e) {
        $("#prefix_invoice_no").focus();
    });

    $('#InvoiceModal').on('hidden.bs.modal', function () {
        $(this).find('form').trigger('reset');
        $('#prefix_invoice_no-error').html("");
        $('#invoice_no-error').html("");
        $('#company_name-error').html("");
        $('#company_logo-error').html("");
        $('#company_address-error').html("");
        $('#company_mobile_no-error').html("");
        var default_image = "{{ url('public/images/placeholder_image.png') }}";
        $('#company_logo_image_show').attr('src', default_image);
    });

    $('body').on('click', '#editInvoiceBtn', function () {
        $.get("{{ url('admin/settings/edit') }}", function (data) {
            $('#prefix_invoice_no').val(data.prefix_invoice_no);
            $('#invoice_no').val(data.invoice_no);
            $('#company_name').val(data.company_name);
            $('#company_address').val(data.company_address);
            $('#company_mobile_no').val(data.company_mobile_no);
            if(data.company_logo==null){
                var default_image = "{{ url('public/images/placeholder_image.png') }}";
                $('#company_logo_image_show').attr('src', default_image);
            }
            else{
                var company_logo = "{{ url('public/images/company') }}" +"/" + data.company_logo;
                $('#company_logo_image_show').attr('src', company_logo);
            }
        })
    });

    $('body').on('click', '#saveInvoiceBtn', function () {
        $('#saveInvoiceBtn').prop('disabled',true);
        $('#saveInvoiceBtn').find('.loadericonfa').show();
        var formData = new FormData($("#InvoiceForm")[0]);

        $.ajax({
            type: 'POST',
            url: "{{ url('admin/updateInvoiceSetting') }}",
            data: formData,
            processData: false,
            contentType: false,
            success: function (res) {
                if(res.status == 'failed'){
                    $('#saveInvoiceBtn').prop('disabled',false);
                    $('#saveInvoiceBtn').find('.loadericonfa').hide();
                    if (res.errors.prefix_invoice_no) {
                        $('#prefix_invoice_no-error').show().text(res.errors.prefix_invoice_no);
                    } else {
                        $('#prefix_invoice_no-error').hide();
                    }

                    if (res.errors.invoice_no) {
                        $('#invoice_no-error').show().text(res.errors.invoice_no);
                    } else {
                        $('#invoice_no-error').hide();
                    }

                    if (res.errors.company_name) {
                        $('#company_name-error').show().text(res.errors.company_name);
                    } else {
                        $('#company_name-error').hide();
                    }

                    if (res.errors.company_logo) {
                        $('#company_logo-error').show().text(res.errors.company_logo);
                    } else {
                        $('#company_logo-error').hide();
                    }

                    if (res.errors.company_address) {
                        $('#company_address-error').show().text(res.errors.company_address);
                    } else {
                        $('#company_address-error').hide();
                    }

                    if (res.errors.company_mobile_no) {
                        $('#company_mobile_no-error').show().text(res.errors.company_mobile_no);
                    } else {
                        $('#company_mobile_no-error').hide();
                    }
                }

                if(res.status == 200){
                    $("#InvoiceModal").modal('hide');
                    $('#saveInvoiceBtn').prop('disabled',false);
                    $('#saveInvoiceBtn').find('.loadericonfa').hide();
                    $("#prefix_invoice_no_val").html(res.Settings.prefix_invoice_no);
                    $("#invoice_no_val").html(res.Settings.invoice_no);
                    $("#company_name_val").html(res.Settings.company_name);
                    var logo = "{{ url('public/images/company') }}" + "/" + res.Settings.company_logo;
                    if(res.Settings.company_logo!="" && res.Settings.company_logo!=null) {
                        $('#company_logo_val').attr('src', logo);
                    }
                    $("#company_address_val").html(res.Settings.company_address);
                    $("#company_mobile_no_val").html(res.Settings.company_mobile_no);
                    toastr.success("Invoice Settings Updated",'Success',{timeOut: 5000});
                }

                if(res.status == 400){
                    $("#InvoiceModal").modal('hide');
                    $('#saveInvoiceBtn').prop('disabled',false);
                    $('#saveInvoiceBtn').find('.loadericonfa').hide();
                    toastr.error("Please try again",'Error',{timeOut: 5000});
                }
            },
            error: function (data) {
                $("#InvoiceModal").modal('hide');
                $('#saveInvoiceBtn').prop('disabled',false);
                $('#saveInvoiceBtn').find('.loadericonfa').hide();
                toastr.error("Please try again",'Error',{timeOut: 5000});
            }
        });
    });

    $('#company_logo').change(function(){
        $('#company_logo-error').hide();
        var file = this.files[0];
        var fileType = file["type"];
        var validImageTypes = ["image/jpeg", "image/png", "image/jpg"];
        if ($.inArray(fileType, validImageTypes) < 0) {
            $('#company_logo-error').show().text("Please provide a Valid Extension Logo(e.g: .jpg .png)");
            var default_image = "{{ url('public/images/placeholder_image.png') }}";
            $('#company_logo_image_show').attr('src', default_image);
        }
        else {
            let reader = new FileReader();
            reader.onload = (e) => {
                $('#company_logo_image_show').attr('src', e.target.result);
            }
            reader.readAsDataURL(this.files[0]);
        }
    });
</script>
<!-- settings JS end -->
@endsection
