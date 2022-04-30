@extends('admin.layout')

@section('content')
    <div class="row page-titles mx-0">
        <div class="col p-md-0">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)">Invoice</a></li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            @if(isset($action) && $action=='create')
                                Add Invoice
                            @elseif(isset($action) && $action=='edit')
                                Edit Invoice
                            @else
                                Invoice List
                            @endif
                        </h4>

                        @if(isset($action) && $action=='list')
                        <div class="action-section">
                            <button type="button" class="btn btn-primary" id="AddInvoiceBtn"><i class="fa fa-plus" aria-hidden="true"></i></button>
                            {{-- <button class="btn btn-danger" onclick="deleteMultipleAttributes()"><i class="fa fa-trash" aria-hidden="true"></i></button>--}}
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <select class="form-control" id="user_id_filter">
                                    <option></option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 input-group">
                                <input type="text" class="form-control custom_date_picker" id="start_date" name="start_date" placeholder="Start Date" data-date-format="yyyy-mm-dd"> <span class="input-group-append"><span class="input-group-text"><i class="mdi mdi-calendar-check"></i></span></span>
                            </div>
                            <div class="col-md-3 input-group">
                                <input type="text" class="form-control custom_date_picker" id="end_date" name="end_date" placeholder="End Date" data-date-format="yyyy-mm-dd"> <span class="input-group-append"><span class="input-group-text"><i class="mdi mdi-calendar-check"></i></span></span>
                            </div>
                            <div class="col-md-3">
                                <button type="button" class="btn btn-outline-primary" id="export_excel_btn" >Export to Excel <i class="fa fa-circle-o-notch fa-spin loadericonfa" style="display:none;"></i></button>
                                <button type="button" class="btn btn-outline-primary" id="export_pdf_btn" >Export to PDF <i class="fa fa-circle-o-notch fa-spin loadericonfa" style="display:none;"></i></button>
                            </div>
                        </div>
                        @endif

                        @if(isset($action) && $action=='list')
                            <div class="table-responsive">
                                <table id="Invoice" class="table zero-configuration customNewtable" style="width:100%">
                                    <thead>
                                    <tr>
                                        <th></th>
                                        <th>No.</th>
                                        <th>Invoice No</th>
                                        <th>Customer</th>
                                        <th>Amount</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                        <th>Quantity</th>
                                        <th>Amount</th>
                                        <th>amount transfer</th>
                                        <th>payment type</th>
                                        <th>outstanding amount</th>
                                    </tr>
                                    </thead>
                                    <tfoot>
                                    <tr>
                                        <th></th>
                                        <th>No.</th>
                                        <th>Invoice No</th>
                                        <th>Customer</th>
                                        <th>Amount</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                        <th>Quantity</th>
                                        <th>Amount</th>
                                        <th>amount transfer</th>
                                        <th>payment type</th>
                                        <th>outstanding amount</th>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @endif

                        @if(isset($action) && $action=='create')
                            @include('admin.invoice.create')
                        @endif

                        @if(isset($action) && $action=='edit')
                            @include('admin.invoice.edit')
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="DeleteInvoiceModal">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Remove Invoice</h5>
                </div>
                <div class="modal-body">
                    Are you sure you wish to remove this Invoice?
                </div>
                <div class="modal-footer">
                    <button class="btn btn-default" data-dismiss="modal" type="button">Cancel</button>
                    <button class="btn btn-danger" id="RemoveInvoiceSubmit" type="submit">Remove <i class="fa fa-circle-o-notch fa-spin removeloadericonfa" style="display:none;"></i></button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
<!-- Invoice JS start -->
<script type="text/javascript">
var table;

$('body').on('click', '#AddInvoiceBtn', function () {
    location.href = "{{ route('admin.invoice.add') }}";
});

$('body').on('click', '#editInvoiceBtn', function () {
    var invoice_id = $(this).attr('data-id');
    var url = "{{ url('admin/invoice/edit') }}" + "/" + invoice_id;
    window.open(url,"_blank");
});

$(document).ready(function() {
    invoice_table(true);

    $('#Invoice tbody').on('click', 'td.details-control', function () {
        var tr = $(this).closest('tr');
        var row = table.row( tr );

        if ( row.child.isShown() ) {
            // This row is already open - close it
            row.child.hide();
            tr.removeClass('shown');
        } else {
            // Open this row
            row.child( format(row.data()) ).show();
            tr.addClass('shown');
        }
    });

    $('#language').select2({
        width: '100%',
        placeholder: "Select Language",
        allowClear: false
    });

    $('#customer_name').select2({
        width: '100%',
        placeholder: "Select...",
        allowClear: false
    });

    $('#item_name_1').select2({
        width: '100%',
        placeholder: "Select...",
        allowClear: false
    });

    $(".item_name").each(function() {
        var id = $(this).attr('id');
        $('#'+id).select2({
            width: '100%',
            placeholder: "Select...",
            allowClear: false
        });
    })

    $('#user_id_filter').select2({
        width: '100%',
        placeholder: "Select User",
        allowClear: true
    });
});

function format ( d ) {
    // `d` is the original data object for the row
    return d.table1;
}

function invoice_table(is_clearState=false){
    if(is_clearState){
        $('#Invoice').DataTable().state.clear();
    }
    var user_id_filter = $("#user_id_filter").val();
    var start_date = $("#start_date").val();
    var end_date = $("#end_date").val();
    var hideFromExport = [6];

    table = $('#Invoice').DataTable({
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
        buttons: [
            {
                extend: 'excel',
                // text: 'Export to Excel',
                exportOptions: {
                    /*columns: function ( idx, data, node ) {
                        var isVisible = table.column( idx ).visible();
                        var isNotForExport = $.inArray( idx, hideFromExport ) !== -1;
                        return ((isVisible && !isNotForExport) || !isVisible) ? true : false;
                    },*/
                    columns: [0,1,5,2,3,function ( idx, data, node ) {
                        var isVisible = table.column( idx ).visible();
                        return (!isVisible) ? true : false;
                    }],
                    modifier: {
                        page: 'current'
                    }
                }
            }
        ],
        "ajax":{
            "url": "{{ url('admin/allInvoicelist') }}",
            "dataType": "json",
            "type": "POST",
            "data":{ _token: '{{ csrf_token() }}', user_id_filter: user_id_filter, start_date: start_date, end_date: end_date},
            // "dataSrc": ""
        },
        'columnDefs': [
            { "width": "20px", "targets": 0 },
            { "width": "50px", "targets": 1 },
            { "width": "230px", "targets": 2 },
            { "width": "230px", "targets": 3 },
            { "width": "150px", "targets": 4 },
            { "width": "120px", "targets": 5 },
            { "width": "200px", "targets": 6 },
            { "width": "5px", "visible": false ,"targets": 7 },
            { "width": "5px", "visible": false ,"targets": 8 },
            { "width": "5px", "visible": false ,"targets": 9 },
            { "width": "5px", "visible": false ,"targets": 10 },
            { "width": "5px", "visible": false ,"targets": 11 },
        ],
        "columns": [
            {"className": 'details-control', "orderable": false, "data": null, "defaultContent": ''},
            {data: 'id', name: 'id', class: "text-center", orderable: false ,
                render: function (data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
            {data: 'invoice_no', name: 'invoice_no', orderable: false, class: "text-left"},
            {data: 'customer_info', name: 'customer_info', orderable: false, class: "text-left multirow"},
            {data: 'amount', name: 'amount', orderable: false, class: "text-left multirow"},
            {data: 'invoice_date', name: 'invoice_date', orderable: false, class: "text-left"},
            {data: 'action', name: 'action', orderable: false, searchable: false, class: "text-center"},
            {data: 'quantity', name: 'quantity', orderable: false, searchable: false},
            {data: 'final_amount', name: 'final_amount', orderable: false, searchable: false},
            {data: 'amount_transfer', name: 'amount_transfer', orderable: false, searchable: false},
            {data: 'payment_type', name: 'payment_type', orderable: false, searchable: false},
            {data: 'outstanding_amount', name: 'outstanding_amount', orderable: false, searchable: false},
        ]
    });
}

$("#addrow").click(function(){
    $("#language").prop('disabled',true);
    var addednum = $("#addednum").val();
    var language = $("#language").val();

    $.ajax({
        type: 'POST',
        url: "{{ route('admin.invoice.add_row_item') }}",
        data: {_token: '{{ csrf_token() }}', total_item: addednum, language: language},
        success: function (res) {
            $("#itemstbody").append(res['html']);
            $("#addednum").val(res['next_item']);
            $('#item_name_'+res['next_item']).select2({
                width: '100%',
                placeholder: "Select...",
                allowClear: false
            });
        },
        error: function (data) {

        }
    });
});

function removeRow(rowid,clickfrom){
    $("#"+rowid).closest("tr").remove();
    update_total();
}

$("#language").change(function() {
    var language = $("#language").val();
    $.ajax({
        type: 'POST',
        url: "{{ route('admin.invoice.change_products') }}",
        data: {_token: '{{ csrf_token() }}', language: language},
        success: function (res) {
            $("#item_name_1").empty();
            $("#item_name_1").append(res['html']);
        },
        error: function (data) {

        }
    });
});

$('body').on('change', '.item_name', function () {
    var user_id = $("#customer_name").val();
    var product_id = $(this).val();
    var thi = $(this);

    $.ajax({
        type: 'POST',
        url: "{{ route('admin.invoice.change_product_price') }}",
        data: {_token: '{{ csrf_token() }}', user_id: user_id, product_id: product_id},
        success: function (res) {
            $(thi).parents('.item-row').find('.unitcost').val(res);
            $(thi).parents('.item-row').find('.unitcost').trigger('change');
        },
        error: function (data) {

        }
    });
});

$('body').on('change', '.unitcost', function () {
    var price = $(this).val();
    var qty = $(this).parents('.item-row').find('.quantity').val();

    var final_price_item = (price * qty);

    if(final_price_item > 0) {
        $(this).parents('.item-row').find('.proprice').html(final_price_item);
    }
    else{
        $(this).parents('.item-row').find('.proprice').html("0.00");
    }

    update_total();
});

$('body').on('change', '.quantity', function () {
    var price = $(this).parents('.item-row').find('.unitcost').val();
    var qty = $(this).val();

    var final_price_item = (price * qty);

    if(final_price_item > 0) {
        $(this).parents('.item-row').find('.proprice').html(final_price_item);
    }
    else{
        $(this).parents('.item-row').find('.proprice').html("0.00");
    }

    update_total();
});

$('body').on('change', '.discount', function () {
    var price = $(this).parents('.item-row').find('.unitcost').val();
    var qty = $(this).parents('.item-row').find('.quantity').val();
    var discount = $(this).val();

    var final_price_item = (price * qty) - discount;

    if(final_price_item > 0) {
        $(this).parents('.item-row').find('.proprice').html(final_price_item);
    }
    else{
        $(this).parents('.item-row').find('.proprice').html("0.00");
    }

    update_total();
});

function update_total() {
    var totalUnitcost = 0.00;
    var totalQty = 0;
    var totalDiscount = 0;
    var final_amount = 0;
    var total_payable_amount = 0.00;

    $(".unitcost").each(function() {
        if($(this).val()>0) {
            totalUnitcost = parseFloat(totalUnitcost) + parseFloat($(this).val());
        }
    });

    $(".quantity").each(function() {
        if($(this).val()>0) {
            totalQty = parseFloat(totalQty) + parseFloat($(this).val());
        }
    });

    $(".discount").each(function() {
        if($(this).val()>0) {
            totalDiscount = parseFloat(totalDiscount) + parseFloat($(this).val());
        }
    });

    $(".price").each(function() {
        if($(this).html()>0) {
            final_amount = parseFloat(final_amount) + parseFloat($(this).html());
        }
    });

    if($("#outstanding_amount").val()!=""){
        total_payable_amount = parseFloat(final_amount) + parseFloat($("#outstanding_amount").val());
    }
    else{
        total_payable_amount = final_amount;
    }

    $("#totalUnitcost").html(totalUnitcost);
    $("#totalQty").html(totalQty);
    $("#totalDiscount").html(totalDiscount);
    $("#total").html(final_amount);
    $("#total_payable_amount").html(total_payable_amount);
}

$('body').on('change', '#outstanding_amount', function () {
    update_total();
});

$('body').on('click', '#invoice_submit', function () {
    $(this).prop('disabled',true);
    $(this).find('.loadericonfa').show();

    $("*#item_name-error").hide().html("");
    $("*#price-error").hide().html("");
    $("*#quantity-error").hide().html("");
    var btn = $(this);

    var validate_invoice = validateInvoice();
    var validate_invoice_items = validateInvoiceItems($(btn).attr('action'));

    if(validate_invoice==true && validate_invoice_items==true) {
        var formData = new FormData($('#invoiceForm')[0]);
        var cnt = 1;
        var product_ids = [];
        $('.item-row').each(function () {
            var thi = $(this);
            var InvoiceItemForm = {"item_name":$(thi).find('.item_name').val(),
                "price":$(thi).find('.unitcost').val(),
                "quantity":$(thi).find('.quantity').val(),
                "final_price":$(thi).find('.sub_price').html()};

            formData.append("InvoiceItemForm" + cnt, JSON.stringify(InvoiceItemForm));
            product_ids.push($(thi).find('.item_name').val());
            cnt++;
        });
        formData.append("total_price", $("#totalUnitcost").html());
        formData.append("total_qty", $("#totalQty").html());
        formData.append("final_amount", $("#total").html());
        formData.append("outstanding_amount", $("#outstanding_amount").val());
        formData.append("total_payable_amount", $("#total_payable_amount").html());
        formData.append("language", $("#language").val());
        formData.append("action", $(btn).attr('action'));
        formData.append("total_items", $('.item-row').length);
        formData.append("product_ids", product_ids);

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            type: 'POST',
            url: "{{ route('admin.invoice.save') }}",
            data: formData,
            dataType: 'json',
            cache: false,
            processData: false,
            contentType: false,
            // contentType: 'json',
            success: function (res) {
                if(res['status']==200){
                    location.href = "{{ route('admin.invoice.list') }}";
                    if(res['action'] == "add"){
                        toastr.success("Invoice Added",'Success',{timeOut: 5000});
                    }
                    else if(res['action'] == "update"){
                        toastr.success("Invoice Updated",'Success',{timeOut: 5000});
                    }
                }
            },
            error: function (data) {
                $(btn).prop('disabled',false);
                $(btn).find('.loadericonfa').hide();
                toastr.error("Please try again",'Error',{timeOut: 5000});
            }
        });
    }
    else{
        $(btn).prop('disabled',false);
        $(btn).find('.loadericonfa').hide();
    }
});

function validateInvoice() {
    $("#invoiceForm").validate({
        rules: {
            language : {
                required: true,
            },
            customer_name: {
                required: true,
            },
            invoice_date: {
                required: true,
            },
        },

        messages : {
            language: {
                required: "Please provide a language"
            },
            customer_name: {
                required: "Please provide a customer name",
            },
            invoice_date: {
                required: "Please provide a invoice date",
            },
        }
    });

    var valid = true;
    if (!$("#invoiceForm").valid()) {
        valid = false;
    }

    return valid;
}

function validateInvoiceItems(action) {
    var valid = true;
    $('.item-row').each(function () {
        var thi = $(this);
        if($(thi).find('.item_name').val() == ""){
            valid = false;
            $(thi).find('#item_name-error').show().html("Please Select Item");
            return valid;
        }
        if($(thi).find('.unitcost').val() == ""){
            valid = false;
            $(thi).find('#price-error').show().html("Please Provide Price");
            return valid;
        }
        if($(thi).find('.quantity').val() == "" || $(thi).find('.quantity').val()<=0){
            valid = false;
            $(thi).find('#quantity-error').show().html("Please Provide Quantity");
            return valid;
        }
        if($(thi).find('.quantity').val() != "" && $(thi).find('.quantity').val()>0 && $(thi).find('.item_name').val() != "" && !$(thi).find('.quantity').prop('readonly')){
            var check_stock = $.ajax({
                type: 'POST',
                url: "{{ route('admin.check_stock') }}",
                data: {_token: '{{ csrf_token() }}', product_id: $(thi).find('.item_name').val() , quantity: $(thi).find('.quantity').val(), action: action},
                async:false,
                success: function (res) {

                },
                error: function (data) {

                }
            }).responseText;
            if(check_stock != 1){
                valid = false;
                $(thi).find('#quantity-error').show().html("Item is not available in stock");
                return valid;
            }
        }
    });

    return valid;
}

$('body').on('click', '#deleteInvoiceBtn', function (e) {
    // e.preventDefault();
    var invoice_id = $(this).attr('data-id');
    $("#DeleteInvoiceModal").find('#RemoveInvoiceSubmit').attr('data-id',invoice_id);
});

$('#DeleteInvoiceModal').on('hidden.bs.modal', function () {
    $(this).find("#RemoveInvoiceSubmit").removeAttr('data-id');
});

$('body').on('click', '#RemoveInvoiceSubmit', function (e) {
    $('#RemoveInvoiceSubmit').prop('disabled',true);
    $(this).find('.removeloadericonfa').show();
    e.preventDefault();
    var invoice_id = $(this).attr('data-id');
    $.ajax({
        type: 'GET',
        url: "{{ url('admin/invoice') }}" +'/' + invoice_id +'/delete',
        success: function (res) {
            if(res.status == 200){
                $("#DeleteInvoiceModal").modal('hide');
                $('#RemoveInvoiceSubmit').prop('disabled',false);
                $("#RemoveInvoiceSubmit").find('.removeloadericonfa').hide();
                invoice_table();
                toastr.success("Invoice Deleted",'Success',{timeOut: 5000});
            }

            if(res.status == 400){
                $("#DeleteInvoiceModal").modal('hide');
                $('#RemoveInvoiceSubmit').prop('disabled',false);
                $("#RemoveInvoiceSubmit").find('.removeloadericonfa').hide();
                invoice_table();
                toastr.error("Please try again",'Error',{timeOut: 5000});
            }
        },
        error: function (data) {
            $("#DeleteInvoiceModal").modal('hide');
            $('#RemoveInvoiceSubmit').prop('disabled',false);
            $("#RemoveInvoiceSubmit").find('.removeloadericonfa').hide();
            invoice_table();
            toastr.error("Please try again",'Error',{timeOut: 5000});
        }
    });
});

$('body').on('change', '#user_id_filter', function (e) {
    // e.preventDefault();
    invoice_table(true);
});

$('body').on('change', '#start_date', function (e) {
    // e.preventDefault();
    invoice_table(true);
});

$('body').on('change', '#end_date', function (e) {
    // e.preventDefault();
    invoice_table(true);
});

$("#export_excel_btn").on("click", function() {
    table.button( '.buttons-excel' ).trigger();
});

$('body').on('click', '#export_pdf_btn', function (e) {
    e.preventDefault();
    var user_id_filter = $("#user_id_filter").val();
    var start_date = $("#start_date").val();
    var end_date = $("#end_date").val();
    if(user_id_filter == ""){
        user_id_filter = null;
    }
    if(start_date == ""){
        start_date = null;
    }
    if(end_date == ""){
        end_date = null;
    }
    var url = "{{ url('admin/invoice/report') }}" + "/" + user_id_filter + "/" + start_date + "/" + end_date;
    window.open(url, "_blank");
});

$('body').on('click', '#printBtn', function (e) {
    e.preventDefault();
    var invoice_id = $(this).attr('data-id');
    var url = "{{ url('admin/invoice/pdf') }}" + "/" + invoice_id;
    window.open(url, "_blank");
});

$('body').on('change', '#customer_name', function (e) {
    // e.preventDefault();
    $("#item_name_1").removeAttr('disabled');
    $("#addrow").removeAttr('disabled');
});
</script>
<!-- Invoice JS end -->
@endsection
