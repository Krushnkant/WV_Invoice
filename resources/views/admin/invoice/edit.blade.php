<div class="row">
    <div id="page-wrap" class="table-textarea">

        <form method="post" id="invoiceForm">
            {{ csrf_field() }}
            <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">
            <div class="row">
                <div class="col-lg-3 col-md-3 col-sm-12">
                    <div class="form-group row mb-0">
                        <label class="col-lg-12 col-form-label" for="">Which Language you want to use for product? <span class="text-danger">*</span></label>
                        <div class="col-lg-9 col-md-9 col-sm-12">
                            <select name="language" id="language" disabled>
                                <option value="English" @if($invoice->language=="English") selected @endif>English</option>
                                <option value="Hindi" @if($invoice->language=="Hindi") selected @endif>Hindi</option>
                                <option value="Gujarati" @if($invoice->language=="Gujarati") selected @endif>Gujarati</option>
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
                                                <select name="customer_name" id="customer_name" disabled>
                                                    <option></option>
                                                    @foreach($customers as $customer)
                                                        <option value="{{ $customer->id }}" @if($invoice->user_id==$customer->id) selected @endif>{{ $customer->full_name }} [{{ $customer->id }}]</option>
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
                                                <input type="text" name="invoice_no" id="invoice_no" value="{{ $invoice->invoice_no }}" readonly class="form-control input-flat">
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
                                        <input class="form-control custom_date_picker" type="text" id="invoice_date" name="invoice_date" placeholder="dd-mm-yyyy" data-date-format="dd-mm-yyyy" value="{{ date("d-m-Y", strtotime($invoice->invoice_date)) }}">
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
            <?php $i = 1; ?>
            @foreach($invoice->invoice_item as $invoice_item)
            <tr class="item-row" id="table-row-{{ $i }}">
                <td class="item-name">
                    <div class="delete-wpr">
                        <select name="item_name" id="item_name_{{ $i }}" class="item_name">
                            <option></option>
                            @foreach($products as $product)
                                <?php
                                if ($invoice->language == "English"){
                                    $product_title = $product->title_english;
                                }
                                elseif ($invoice->language == "Hindi"){
                                    $product_title = $product->title_english." | ".$product->title_hindi;
                                }
                                elseif ($invoice->language == "Gujarati"){
                                    $product_title = $product->title_english." | ".$product->title_gujarati;
                                }
                                ?>
                                <option value="{{ $product->id }}" @if($invoice_item->product_id==$product->id) selected @endif>{{ $product_title }}</option>
                            @endforeach
                        </select>
                        <label id="item_name-error" class="error invalid-feedback animated fadeInDown" for="item_name"></label>
                        @if($i != 1)
                            <a class="delete" onclick="removeRow('table-row-{{ $i }}',0)" href="javascript:;" title="Remove row">X</a>
                        @endif
                    </div>
                </td>
                <td width="200px">
                    <input class="form-control quantity qty" name="quantity" type="number" min="1" value="{{ $invoice_item->quantity }}" readonly>
                    <label id="quantity-error" class="error invalid-feedback animated fadeInDown" for="quantity"></label>
                </td>
                <td width="200px">
                    <input class="form-control unitcost cost" placeholder="0.00" type="number" name="price" value="{{ $invoice_item->price }}">
                    <label id="price-error" class="error invalid-feedback animated fadeInDown" for="price"></label>
                </td>
                <td class="subt_price"><div class="prse"><i class="fa fa-inr" aria-hidden="true"></i><span class="price proprice sub_price">{{ $invoice_item->final_price }}</span></div></td>
            </tr>
            <?php $i++; ?>
            @endforeach
            </tbody>

            <tfoot>
            <tr>
                <td colspan="5">
                    <button type="button" class="btn btn-light" id="addrow">New Item</button>
                </td>
            </tr>
            <tr class="fullrow">
                <td class="total-line">Total</td>
                <td><div class=""><span id="totalQty" class="totalQty">{{ $invoice->total_qty }}</span></div></td>
                <td></td>
                <td class="total-value"><div id="total">{{ $invoice->final_amount }}</div><i class="fa fa-inr" aria-hidden="true"></i></td>
            </tr>
            <tr class="fullrow">
                <td colspan="3">Outstanding Amount</td>
                <td><input type="number" name="outstanding_amount" id="outstanding_amount" class="form-control" value="{{ $invoice->outstanding_amount }}"></td>
            </tr>
            <tr class="fullrow">
                <td colspan="3">Total Payable Amount</td>
                <td class="total-value"><div id="total_payable_amount">{{ $invoice->total_payable_amount }}</div><i class="fa fa-inr" aria-hidden="true"></i></td>
            </tr>
            </tfoot>
        </table>

        <input type="hidden" id="addednum" name="addednum" value="{{ count($invoice->invoice_item) }}">
        <button type="button" class="btn btn-primary mt-3" id="invoice_submit" name="invoice_submit" action="update">Save <i class="fa fa-circle-o-notch fa-spin loadericonfa" style="display:none;"></i></button>
    </div>
</div>
