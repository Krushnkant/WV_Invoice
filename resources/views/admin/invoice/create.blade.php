<div class="row">
    <div id="page-wrap" class="table-textarea">

        <form method="post" id="invoiceForm">
        {{ csrf_field() }}
        <div class="row">
            <div class="col-lg-3 col-md-3 col-sm-12">
                <div class="form-group row mb-0">
                    <label class="col-lg-12 col-form-label" for="">Which Language you want to use for product? <span class="text-danger">*</span></label>
                    <div class="col-lg-9 col-md-9 col-sm-12">
                        <select name="language" id="language">
                            <option value="English" selected>English</option>
                            <option value="Hindi">Hindi</option>
                            <option value="Gujarati">Gujarati</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="customer_details row">
            <div class="col-sm-6 cust_info">
                <div class="row">
                    <div class="col-sm-6">
                        <table id="cstinfo">
                            <tbody><tr>
                                <td class="form_title m-0">
                                    <div class="form-group row mb-0">
                                        <label class="col-lg-12 col-form-label" for="">Customer Name <span class="text-danger">*</span></label>
                                        <div class="col-lg-12">
                                            <select name="customer_name" id="customer_name">
                                                <option></option>
                                                @foreach($customers as $customer)
                                                    <option value="{{ $customer->id }}">{{ $customer->full_name }} [{{ $customer->id }}]</option>
                                                @endforeach
                                            </select>
                                            <label id="customer_name-error" class="error invalid-feedback animated fadeInDown" for="customer_name"></label>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            </tbody></table>
                    </div>

                    <div class="col-sm-6">
                        <table style="width: 100%;">
                            <tbody><tr>
                                <td class="form_title m-0">
                                    <div class="form-group row mb-0">
                                        <label class="col-lg-12 col-form-label" for="">Invoice No</label>
                                        <div class="col-lg-12">
                                            <input type="text" name="invoice_no" id="invoice_no" value="{{ $invoice_no }}" readonly class="form-control input-flat">
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-sm-2 offset-md-4 billinfo">
                <table id="meta" style="width: 100%;">
                    <tbody><tr>
                        <td>
                            <div class="form-group row">
                                <label class="col-lg-12 col-form-label text-left" for="">Date</label>
                                <div class="col-lg-12">
                                    <input class="form-control custom_date_picker" type="text" id="invoice_date" name="invoice_date" placeholder="dd-mm-yyyy" data-date-format="dd-mm-yyyy" value="{{ date("d-m-Y") }}">
                                    <label id="invoice_date-error" class="error invalid-feedback animated fadeInDown" for="invoice_date"></label>
                                </div>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
        </form>

        <table id="items">
            <thead>
                <tr>
                    <th>Item</th>
                    <th width="200px">Quantity (Kg)</th>
                    <th width="200px">Unit Cost</th>
                    <th>Price</th>
                </tr>
            </thead>

            <tbody id="itemstbody">
            <tr class="item-row">
                <td class="item-name">
                    <div class="delete-wpr">
                        <select name="item_name" id="item_name_1" class="item_name" disabled>
                            <option></option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->title_english }}</option>
                            @endforeach
                        </select>
                        <label id="item_name-error" class="error invalid-feedback animated fadeInDown" for="item_name"></label>
                    </div>
                </td>
                <td width="200px">
                    <input class="form-control quantity qty" name="quantity" type="number" min="1">
                    <label id="quantity-error" class="error invalid-feedback animated fadeInDown" for="quantity"></label>
                </td>
                <td width="200px">
                    <input class="form-control unitcost cost" placeholder="0.00" type="number" name="price" value="">
                    <label id="price-error" class="error invalid-feedback animated fadeInDown" for="price"></label>
                </td>
                <td class="subt_price"><div class="prse"><i class="fa fa-inr" aria-hidden="true"></i><span class="price proprice sub_price">0.00</span></div></td>
            </tr>
            </tbody>

            <tfoot>
            <tr>
                <td colspan="5">
                    <button type="button" class="btn btn-light" id="addrow" disabled>New Item</button>
                </td>
            </tr>
            <tr class="fullrow">
                <td class="total-line">Total</td>
                <td><div class=""><span id="totalQty" class="totalQty"></span></div></td>
                <td></td>
                <td class="total-value"><div id="total">0.00</div><i class="fa fa-inr" aria-hidden="true"></i></td>
            </tr>
            <tr class="fullrow">
                <td colspan="3">Outstanding Amount</td>
                <td><input type="number" name="outstanding_amount" id="outstanding_amount" class="form-control"></td>
            </tr>
            <tr class="fullrow">
                <td colspan="3">Total Payable Amount</td>
                <td class="total-value"><div id="total_payable_amount">0.00</div><i class="fa fa-inr" aria-hidden="true"></i></td>
            </tr>
            </tfoot>
        </table>

        <input type="hidden" id="addednum" name="addednum" value="1">
        <button type="button" class="btn btn-primary mt-3" id="invoice_submit" name="invoice_submit" action="add">Save <i class="fa fa-circle-o-notch fa-spin loadericonfa" style="display:none;"></i></button>
    </div>
</div>
