<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\ProductStock;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mpdf\Mpdf;
use Spipu\Html2Pdf\Exception\ExceptionFormatter;
use Spipu\Html2Pdf\Exception\Html2PdfException;
use Spipu\Html2Pdf\Html2Pdf;

class InvoiceController extends Controller
{
    public function index(){
        $action = "list";
        $users = User::where('role',2)->get();
        return view('admin.invoice.list',compact('action','users'));
    }

    public function create(){
        $action = "create";
        $customers = User::where('role',2)->where('estatus',1)->get();
        $products = Product::where('estatus',1)->get();

        $settings = Setting::find(1);
        $invoice_no = '';
        if (isset($settings->prefix_invoice_no)){
            $invoice_no .= $settings->prefix_invoice_no;
        }
        if (isset($settings->invoice_no)){
            $invoice_no .= $settings->invoice_no;
        }

        return view('admin.invoice.list',compact('action','customers','invoice_no','products'));
    }

    public function add_row_item(Request $request){
        $language = $request->language;
        $next_item = $request->total_item + 1;

        $products = Product::where('estatus',1)->get();
        $html_product = '';
        foreach ($products as $product){
            if ($language == "English"){
                $title = $product->title_english;
            }
            elseif ($language == "Hindi"){
                $title = $product->title_english." | ".$product->title_hindi;
            }
            elseif ($language == "Gujarati"){
                $title = $product->title_english." | ".$product->title_gujarati;
            }
            $html_product .= '<option value="'.$product->id.'">'.$title.'</option>';
        }


        $html = '<tr class="item-row" id="table-row-'.$next_item.'">
                <td class="item-name">
                    <div class="delete-wpr">
                        <select name="item_name" id="item_name_'.$next_item.'" class="item_name">
                            <option></option>'.$html_product.'
                        </select>
                        <label id="item_name-error" class="error invalid-feedback animated fadeInDown" for="item_name"></label>
                        <a class="delete" onclick="removeRow(\'table-row-'.$next_item.'\',0)" href="javascript:;" title="Remove row">X</a>
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
           </tr>';

        return ['html' => $html, 'next_item' => $next_item];
    }

    public function change_products(Request $request){
        $language = $request->language;
        $products = Product::where('estatus',1)->get();
        $html_product = '<option></option>';
        foreach ($products as $product){
            if ($language == "English"){
                $title = $product->title_english;
            }
            elseif ($language == "Hindi"){
                $title = $product->title_english." | ".$product->title_hindi;
            }
            elseif ($language == "Gujarati"){
                $title = $product->title_english." | ".$product->title_gujarati;
            }
            $html_product .= '<option value="'.$product->id.'">'.$title.'</option>';
        }

        return ['html' => $html_product];
    }

    public function change_product_price(Request $request){
        $price = ProductPrice::where('user_id',$request->user_id)->where('product_id',$request->product_id)->pluck('price')->first();
        return $price;
    }

    public function save(Request $request){
        if ($request->action == "add"){
            $invoice = new Invoice();
            $invoice->user_id = $request->customer_name;
        }
        elseif ($request->action == "update"){
            $invoice = Invoice::find($request->invoice_id);
        }
        $invoice->language = $request->language;
        $invoice->invoice_no = $request->invoice_no;
        $invoice->invoice_date = date("Y-m-d", strtotime($request->invoice_date));
        $invoice->total_qty = $request->total_qty;
        $invoice->final_amount = $request->final_amount;
        $invoice->outstanding_amount = isset($request->outstanding_amount)?$request->outstanding_amount:0;
        $invoice->total_payable_amount = $request->total_payable_amount;
        $invoice->save();

        $deleted_product_ids = array();
        if ($request->action == "update"){
            $invoice_items = InvoiceItem::where('invoice_id',$request->invoice_id)->get();
            foreach ($invoice_items as $invoice_item){
                $invoice_item->estatus = 3;

                $temp['product_id'] = $invoice_item->product_id;
                $temp['qty'] = $invoice_item->quantity;
                array_push($deleted_product_ids,$temp);
                //update stock
                if (!in_array($invoice_item->product_id,explode(",",$request->product_ids))){
                    $product = Product::find($invoice_item->product_id);
                    $product->stock = $product->stock + $invoice_item->quantity;
                    $product->save();
                }

                $invoice_item->save();
                $invoice_item->delete();
            }
        }

        for ($i = 1; $i <= $request->total_items; $i++){
            $form = 'InvoiceItemForm'.$i;
            $item = json_decode($request[$form],true);
            $invoice_item = new InvoiceItem();
            $invoice_item->invoice_id = $invoice->id;
            $invoice_item->product_id = $item['item_name'];
            $invoice_item->price = $item['price'];
            $invoice_item->quantity = $item['quantity'];
            $invoice_item->final_price = $item['final_price'];
            $invoice_item->save();

            //update stock
            if ($request->action == "add") {
                $product = Product::find($invoice_item->product_id);
                $product->stock = $product->stock - $invoice_item->quantity;
                $product->save();
            }
            elseif ($request->action == "update"){
                foreach ($deleted_product_ids as $deleted_product_id) {
                    if ($deleted_product_id['product_id']==$invoice_item->product_id && $deleted_product_id['qty']!=$invoice_item->quantity){
                        if ($invoice_item->quantity > $deleted_product_id['qty']){
                            $qty = $invoice_item->quantity - $deleted_product_id['qty'];
                            $product = Product::find($invoice_item->product_id);
                            $product->stock = $product->stock - $qty;
                            $product->save();
                        }
                        elseif ($invoice_item->quantity < $deleted_product_id['qty']){
                            $qty = $deleted_product_id['qty'] - $invoice_item->quantity;
                            $product = Product::find($invoice_item->product_id);
                            $product->stock = $product->stock + $qty;
                            $product->save();
                        }
                    }
                }
            }
        }

        if ($request->action == "add") {
            $settings = Setting::find(1);
            $settings->invoice_no = $settings->invoice_no + 1;
            $settings->save();
        }

        return ['status' => 200, 'action' => $request->action];
    }

    public function allInvoicelist(Request $request){
        if ($request->ajax()) {
            $columns = array(
                0 =>'id',
                1 =>'invoice_no',
                2=> 'customer_info',
                3=> 'amount',
                4=> 'invoice_date',
                5=> 'action',
            );

            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');

            if($order == "id"){
                $order = "created_at";
                $dir = 'DESC';
            }

            $totalData = Invoice::count();
            $totalFiltered = $totalData;

            if(empty($request->input('search.value')))
            {
                $Invoices = Invoice::with('invoice_item.product','user');
                if (isset($request->user_id_filter) && $request->user_id_filter!=""){
                    $Invoices = $Invoices->where('user_id',$request->user_id_filter);
                }
                if (isset($request->start_date) && $request->start_date!="" && isset($request->end_date) && $request->end_date!=""){
                    $Invoices = $Invoices->whereRaw("invoice_date between '".$request->start_date."' and '".$request->end_date."'");
                }
                $Invoices = $Invoices->offset($start)
                    ->limit($limit)
                    ->orderBy($order,$dir)
                    ->get();

                $totalFiltered = count($Invoices->toArray());
            }
            else {
                $search = $request->input('search.value');
                $Invoices = Invoice::with('invoice_item.product','user');
                if (isset($request->user_id_filter) && $request->user_id_filter!=""){
                    $Invoices = $Invoices->where('user_id',$request->user_id_filter);
                }
                if (isset($request->start_date) && $request->start_date!="" && isset($request->end_date) && $request->end_date!=""){
                    $Invoices = $Invoices->whereRaw("invoice_date between '".$request->start_date."' and '".$request->end_date."'");
                }
                $Invoices = $Invoices->where(function($query) use($search){
                    $query->where('invoice_no','LIKE',"%{$search}%")
                        ->orWhere('invoice_date', 'LIKE',"%{$search}%")
                        ->orWhere('total_qty', 'LIKE',"%{$search}%")
                        ->orWhere('final_amount', 'LIKE',"%{$search}%")
                        ->orWhereHas('user',function ($Query) use($search) {
                            $Query->where('full_name', 'Like', '%' . $search . '%');
                        });
                    })
                    ->offset($start)
                    ->limit($limit)
                    ->orderBy($order,$dir)
                    ->get();

                $totalFiltered = count($Invoices->toArray());
            }

            $data = array();

            if(!empty($Invoices))
            {
                foreach ($Invoices as $Invoice)
                {
                    $amount = '';
                    if (isset($Invoice->total_qty)){
                        $amount .= '<span>Total Quantity: '.$Invoice->total_qty;
                    }
                    if (isset($Invoice->final_amount)){
                        $amount .= '<span>Final Amount: <i class="fa fa-inr" aria-hidden="true"></i> '.$Invoice->final_amount;
                    }

                    $table = '<table cellpadding="5" cellspacing="0" border="1" width="100%" id="items_table">';
                    $table .= '<tbody>';
                    $table .='<tr style="width: 100%">';
                    $table .= '<th style="text-align: center">Item No.</th>';
                    $table .= '<th>Item Name</th>';
                    $table .= '<th style="text-align: center">Price</th>';
                    $table .= '<th style="text-align: center">Quantity</th>';
                    $table .= '<th style="text-align: right">Final Price</th>';
                    $table .= '</tr>';
                    $item = 1;
                    foreach ($Invoice->invoice_item as $invoice_item){
                        $product = Product::withTrashed()->find($invoice_item->product_id);
                        $product_title = '';
                        if ($Invoice->language == "English" && isset($product)){
                            $product_title = $product->title_english;
                        }
                        elseif ($Invoice->language == "Hindi" && isset($product)){
                            $product_title = $product->title_english." | ".$product->title_hindi;
                        }
                        elseif ($Invoice->language == "Gujarati" && isset($product)){
                            $product_title = $product->title_english." | ".$product->title_gujarati;
                        }
                        $table .='<tr>';
                        $table .= '<td style="text-align: center">'.$item.'</td>';
                        $table .= '<td>'.$product_title.'</td>';
                        $table .= '<td style="text-align: center"><i class="fa fa-inr" aria-hidden="true"></i> '.$invoice_item->price.'</td>';
                        $table .= '<td style="text-align: center">'.$invoice_item->quantity.'</td>';
                        $table .= '<td style="text-align: right"><i class="fa fa-inr" aria-hidden="true"></i> '.$invoice_item->final_price.'</td>';
                        $table .= '</tr>';
                        $item++;
                    }
                    $table .='</tbody>';
                    $table .='</table>';

                    $action = '';
                    $action .= '<button id="printBtn" class="btn btn-gray text-warning btn-sm" data-id="'.$Invoice->id.'"><i class="fa fa-print" aria-hidden="true"></i></button>';
                    $action .= '<button id="editInvoiceBtn" class="btn btn-gray text-blue btn-sm" data-id="'.$Invoice->id.'"><i class="fa fa-pencil" aria-hidden="true"></i></button>';
                    $action .= '<button id="deleteInvoiceBtn" class="btn btn-gray text-danger btn-sm" data-toggle="modal" data-target="#DeleteInvoiceModal" data-id="'.$Invoice->id.'"><i class="fa fa-trash-o" aria-hidden="true"></i></button>';

                    $nestedData['invoice_no'] = $Invoice->invoice_no;
                    $nestedData['customer_info'] = isset($Invoice->user->full_name)?$Invoice->user->full_name:'';
                    $nestedData['amount'] = $amount;
                    $nestedData['invoice_date'] = date("d-m-Y", strtotime($Invoice->invoice_date));
                    $nestedData['action'] = $action;
                    $nestedData['quantity'] = $Invoice->total_qty;
                    $nestedData['final_amount'] = $Invoice->final_amount;
                    $nestedData['amount_transfer'] = '';
                    $nestedData['payment_type'] = '';
                    $nestedData['outstanding_amount'] = '';
                    $nestedData['table1'] = $table;
                    $data[] = $nestedData;
                }
            }

            $json_data = array(
                "draw"            => intval($request->input('draw')),
                "recordsTotal"    => intval($totalData),
                "recordsFiltered" => intval($totalFiltered),
                "data" => $data,
            );

            echo json_encode($json_data);
        }
    }

    public function edit($id){
        $action = "edit";
        $customers = User::where('role',2)->where('estatus',1)->get();
        $products = Product::where('estatus',1)->get();
        $invoice = Invoice::with('invoice_item')->where('id',$id)->first();

        return view('admin.invoice.list',compact('action','customers','products','invoice'));
    }

    public function delete($id){
        $Invoice = Invoice::with('invoice_item')->where('id',$id)->first();
        if ($Invoice){
            $Invoice->estatus = 3;
            $Invoice->save();
            $Invoice->delete();

            foreach ($Invoice->invoice_item as $invoice_item){
                $invoice_item->estatus = 3;
                $invoice_item->save();

                $product = Product::find($invoice_item->product_id);
                $product->stock = $product->stock + $invoice_item->quantity;
                $product->save();

                $invoice_item->delete();
            }

            return response()->json(['status' => '200']);
        }
        return response()->json(['status' => '400']);
    }

    public function generate_pdf1($id){
        try{
            $invoice = Invoice::with('invoice_item.product','user')->where('id',$id)->first();
            $settings = Setting::find(1);
            $f = new \NumberFormatter( locale_get_default(), \NumberFormatter::SPELLOUT );

            $image = '';
            if (isset($settings->company_logo)){
                $image = '<img style="width: 100%;" src="'.url('public/images/company/'.$settings->company_logo).'" alt="Logo">';
            }

            $HTMLContent = '<style type="text/css">
                            <!--
                            table { vertical-align: top; }
                            tr    { vertical-align: top; }
                            td    { vertical-align: top; }
                            -->
                            </style>';
            $HTMLContent .= '<page backcolor="#FEFEFE" style="font-size: 12pt">
                        <bookmark title="Lettre" level="0" ></bookmark>
                        <p style="text-align: center; font-size: 7pt; margin-bottom: 0;">SHREE GANESHAY NAMAH</p>
                        <p style="text-align: right; font-size: 10pt; margin-bottom: 0;">Mo.: '.$settings->company_mobile_no.'</p>
                        <table cellspacing="0" style="width: 100%; border-bottom: dotted 1px black;">
                            <tr>
                                <td style="width: 15%;">
                                    '.$image.'
                                </td>
                                <td style="width: 20%"></td>
                                <td style="width: 65%;">
                                	<h3 style="text-align: left; font-size: 20pt; margin: 0;">'.$settings->company_name.'</h3>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3"><p style="text-align: center;font-size: 10pt;">'.$settings->company_address.'</p></td>
                            </tr>
                        </table>
                        <br>
                       
                        <table cellspacing="0" style="width: 100%;">
                            <colgroup>
                                <col style="width: 12%;">
                                <col style="width: 60%;">
                                <col style="width: 12%;">
                                <col style="width: 16%;">
                            </colgroup>
                            <tbody>
                                <tr>
                                    <td style="font-size: 12pt; padding:2px 0;">
                                        Name
                                    </td>
                                    <td style="font-size: 12pt; padding:2px 0;">
                                        : <b>'.$invoice->user->full_name.'</b>
                                    </td>
                                    <td style="font-size: 10pt; padding:2px 0;">
                                        Invoice No
                                    </td>
                                    <td style="font-size: 10pt; padding:2px 0;">
                                        : '.$invoice->invoice_no.'
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-size: 10pt; padding:2px 0;">
                                        Mobile No
                                    </td>
                                    <td style="font-size: 10pt; padding:2px 0;">
                                        : '.$invoice->user->mobile_no.'
                                    </td>
                                    <td style="font-size: 10pt; padding:2px 0;">
                                        Date
                                    </td>
                                    <td style="font-size: 10pt; padding:2px 0;">
                                        : '.date('d M, Y', strtotime($invoice->invoice_date)).'
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-size: 10pt; padding:2px 0;">
                                        Address
                                    </td>
                                    <td style="font-size: 10pt; padding:2px 0;">
                                        : '.$invoice->user->address.'
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <table cellspacing="0" style="width: 100%; margin-top:10px;  font-size: 10pt; margin-bottom:0px;" align="center" border="1">
                            <colgroup>
                                <col style="width: 10%; text-align: center">
                                <col style="width: 50%; text-align: left">
                                <col style="width: 20%; text-align: center">
                                <col style="width: 10%; text-align: center">
                                <col style="width: 10%; text-align: center">
                            </colgroup>
                            <thead>
                                <tr style="background: #ffe6e6;">
                                    <th colspan="5" style="text-align: center; padding:8px 0;"> Item Details </th>
                                </tr>
                                <tr>
                                    <th style="padding:8px 0;">No.</th>
                                    <th style="padding:8px 0;">Item</th>
                                    <th style="padding:8px 0;">Qty</th>
                                    <th style="padding:8px 0;">Price</th>
                                    <th style="padding:8px 0;">Total</th>
                                </tr>
                            </thead>
                            <tbody>';

            $no = 1;
            foreach ($invoice->invoice_item as $invoice_item){
                if ($invoice->language == "English"){
                    $item = $invoice_item->product->title_english;
                }
                elseif ($invoice->language == "Hindi"){
                    $item = $invoice_item->product->title_english." | ".$invoice_item->product->title_hindi;
                }
                elseif ($invoice->language == "Gujarati"){
                    $item = $invoice_item->product->title_english." | ".$invoice_item->product->title_gujarati;
                }

                $HTMLContent .= '<tr>
                                    <th style="font-weight : 10px; padding:8px 0;">'.$no.'</th>
                                    <th style="font-weight : 10px; padding:8px 0;"><b>'.$item.'</b></th>
                                    <th style="font-weight : 10px; padding:8px 0;">'.$invoice_item->quantity.' KG</th>
                                    <th style="font-weight : 10px; padding:8px 0;">'.number_format($invoice_item->price, 2, '.', ',').'</th>
                                    <th style="font-weight : 10px; padding:8px 0;">'.number_format($invoice_item->final_price, 2, '.', ',').'</th>
                                </tr>';
                $no++;
            }

            $HTMLContent .= '<tr>
                                    <th colspan="2" style="padding:10px 0;">Total</th>
                                    <th  style="padding:10px 0;">'.$invoice->total_qty.'</th>
                                    <th  style="padding:10px 0;"></th>
                                    <th  style="padding:10px 0;">'.number_format($invoice->final_amount, 2, '.', ',').'</th>
                             </tr>
                            </tbody>
                        </table>';

            $HTMLContent .= '<p style="font-size: 8pt;">AMOUNT IN WORDS: '.strtoupper($f->format($invoice->final_amount)).' RUPEES ONLY</p>';

            $HTMLContent .= '<table cellspacing="0" style="width: 100%; margin-top: 0px;">
                                <tr>
                                    <td  style="padding-top: 40px;padding-bottom: 10px; width :50%; border-bottom: solid 1px gray; text-align:left; color:gray;">Customer Signature</td>
                                    <td  style="padding-top: 40px;padding-bottom: 10px; width :50%; border-bottom: solid 1px gray; text-align:right; color:gray;"><b>For, '.$settings->company_name.'</b></td>
                                </tr>
                            </table>
                        </page>';

            $html2pdf = new Html2Pdf('P', 'A5', 'fr', true, "UTF-8");
            $html2pdf->setDefaultFont('freeserif');
            $html2pdf->pdf->SetDisplayMode('fullpage');
            $html2pdf->writeHTML($HTMLContent);
            $html2pdf->output($invoice->invoice_no.'.pdf');
        } catch (Html2PdfException $e) {
            $html2pdf->clean();

            $formatter = new ExceptionFormatter($e);
            echo $formatter->getHtmlMessage();
        }
    }

    public function generate_pdf($id){
        $invoice = Invoice::with('invoice_item.product','user')->where('id',$id)->first();
        $settings = Setting::find(1);

        $image = '';
        if (isset($settings->company_logo)){
            $image = '<img src="'.url('public/images/company/'.$settings->company_logo).'" alt="Logo" width="100px" height="100px">';
        }

        $HTMLContent = '<style type="text/css">
                            <!--
                            table { vertical-align: top; }
                            tr    { vertical-align: top; }
                            td    { vertical-align: top; }
                            .d-flex{ display: flex;}
                            -->
                            </style>';
        $HTMLContent .= '<page backcolor="#FEFEFE" style="font-size: 12pt">
                        <bookmark title="Lettre" level="0" ></bookmark>
                        <div>
                        <p style="font-size: 7pt;margin: 0;float: left; width: 60%; text-align: right;">SHREE GANESHAY NAMAH</p>
                        <p style="margin: 0;font-size: 7pt;float: left; width: 40%; text-align: right;">Mo.: '.$settings->company_mobile_no.'</p>
                        </div>

                        <table cellspacing="0" cellpadding="0" style="width: 100%;">
                            <tr>
                                <td style="width: 15%;height: 15%" rowspan="2">
                                    '.$image.'
                                </td>
                                <td style="width: 75%;">
                                	<h3 style="text-align: left; font-size: 25pt; margin: 0;">'.$settings->company_name.'</h3>
                                </td>
                            </tr>
                            <tr>
                                <td align="left">'.$settings->company_address.'</td>
                            </tr>
                        </table>
                        
                        <hr style="height: 1px">
                        
                        <div>
                        <p style="font-size: 10pt;margin: 0;float: left; width: 50%; text-align: left;">Name: <b>'.$invoice->user->full_name.'</b></p>
                        <p style="margin: 0;font-size: 10pt;float: left; width: 50%; text-align: right;">Invoice No: '.$invoice->invoice_no.'</p>
                        </div>
                        <div>
                        <p style="font-size: 10pt;margin: 0;float: left; width: 50%; text-align: left;">Mobile No: '.$invoice->user->mobile_no.'</p>
                        <p style="margin: 0;font-size: 10pt;float: left; width: 50%; text-align: right;">Date: '.date("d-m-Y", strtotime($invoice->invoice_date)).'</p>
                        </div>
                        <div>
                        <p style="font-size: 10pt;margin: 0;float: left; width: 50%; text-align: left;">Address: '.$invoice->user->address.'</p>
                        </div>

                        <table cellspacing="0" style="width: 100%; margin-top:10px; font-size: 8pt; margin-bottom:0px;border: 1px solid grey;" align="center" >
                            <colgroup>
                                <col style="width: 10%; text-align: center">
                                <col style="width: 50%; text-align: left">
                                <col style="width: 10%; text-align: center">
                                <col style="width: 15%; text-align: center">
                                <col style="width: 15%; text-align: right">
                            </colgroup>
                            <thead>
                                <tr style="background: lightgray;">
                                    <th colspan="5" style="text-align: center; padding:8px 0;border: 1px solid grey;"> Item Details </th>
                                </tr>
                                <tr>
                                    <th style="padding:8px 0;border: 1px solid grey;width: 10%;">No.</th>
                                    <th style="padding:8px 0;text-align: left;padding-left: 5px;border: 1px solid grey;width: 50%;">Item</th>
                                    <th style="padding:8px 0;border: 1px solid grey;width: 10%;">Qty (Kg)</th>
                                    <th style="padding:8px 0;border: 1px solid grey;width: 15%;">Price</th>
                                    <th style="padding:8px 0;border: 1px solid grey;padding-right: 5px;text-align: right;width: 15%;">Total</th>
                                </tr>
                            </thead>
                            <tbody>';

        $no = 1;
        foreach ($invoice->invoice_item as $invoice_item){
            $product = Product::withTrashed()->find($invoice_item->product_id);
            $product_title = '';
            if ($invoice->language == "English" && isset($product)){
                $product_title = '<b>'.$product->title_english.'</b>';
            }
            elseif ($invoice->language == "Hindi" && isset($product)){
                $product_title = '<b>'.$product->title_english." | ".$product->title_hindi.'</b>';
            }
            elseif ($invoice->language == "Gujarati" && isset($product)){
                $product_title = '<b>'.$product->title_english."</b> | ".$product->title_gujarati;
            }

            $HTMLContent .= '<tr>
                                    <th style="padding:8px 0;text-align: center;border: 1px solid grey;width: 10%;">'.$no.'</th>
                                    <td style="padding:8px 0;padding-left: 5px;border: 1px solid grey;width: 50%;text-align: left">'.$product_title.'</td>
                                    <th style="padding:8px 0;text-align: center;border: 1px solid grey;width: 10%;">'.$invoice_item->quantity.'</th>
                                    <th style="padding:8px 0;text-align: center;border: 1px solid grey;width: 15%;">'.number_format($invoice_item->price, 2, '.', ',').'</th>
                                    <th style="padding:8px 0;text-align: right;border: 1px solid grey;padding-right: 5px;width: 15%;">'.number_format($invoice_item->final_price, 2, '.', ',').'</th>
                                </tr>';
            $no++;
        }

        $HTMLContent .= '<tr>
                                    <th colspan="2" style="padding:10px 0;border: 1px solid grey;">Total</th>
                                    <th  style="padding:10px 0;border: 1px solid grey;">'.$invoice->total_qty.'</th>
                                    <th  style="padding:10px 0;border: 1px solid grey;"></th>
                                    <th  style="padding:10px 0;border: 1px solid grey;text-align: right;padding-right: 5px">'.number_format($invoice->final_amount, 2, '.', ',').'</th>
                         </tr>
                         <tr>
                            <th colspan="4" style="padding:10px 0;border: 1px solid grey;text-align: left;padding-left: 5px">Previous Outstanding Amount</th>
                            <th style="padding:10px 0;border: 1px solid grey;text-align: right;padding-right: 5px">'.number_format($invoice->outstanding_amount, 2, '.', ',').'</th>
                        </tr>
                        <tr>
                            <th colspan="4" style="padding:10px 0;border: 1px solid grey;text-align: left;padding-left: 5px">Total Payable Amount</th>
                            <th style="padding:10px 0;border: 1px solid grey;text-align: right;padding-right: 5px">'.number_format($invoice->total_payable_amount, 2, '.', ',').'</th>
                        </tr>
                        </tbody>
                        </table>';

        $HTMLContent .= '<p style="font-size: 8pt;padding-bottom: 40px">AMOUNT IN WORDS: '.strtoupper(numberTowords($invoice->total_payable_amount)).'</p>';

//        $HTMLContent .= '<table cellspacing="0" style="width: 100%; margin-top:10px; font-size: 10pt; margin-bottom:0px;border: 1px solid grey;" align="left">
//                            <thead>
//                                <tr>
//                                    <td colspan="5" style="text-align: left; padding:18px 0; padding-left: 5px; color:gray;border: 1px solid grey;"> Notes </td>
//                                </tr>
//                            </thead>
//                         </table>';

        $HTMLContent .= '<htmlpagefooter name="footer">
                        <table cellspacing="0" style="width: 100%; margin-top: 0px;padding-bottom: 40px">
                                <tr>
                                    <td  style="padding-top: 50px;padding-bottom: 10px; width :50%; border-bottom: solid 1px gray; text-align:left; color:gray;">Customer Signature</td>
                                    <td  style="padding-top: 50px;padding-bottom: 10px; width :50%; border-bottom: solid 1px gray; text-align:right; color:gray;"><b>For, '.$settings->company_name.'</b></td>
                                </tr>
                            </table>
                        </htmlpagefooter>
                        <sethtmlpagefooter name="footer" />
                        </page>';

        $filename = "Invoice_".$invoice->invoice_no.".pdf";
        $mpdf = new Mpdf(["autoScriptToLang" => true, "autoLangToFont" => true, 'mode' => 'utf-8', 'format' => 'A5-P', 'margin_left' => 5, 'margin_right' => 5, 'margin_top' => 5, 'margin_bottom' => 5, 'margin_header' => 0, 'margin_footer' => 0]);
        $mpdf->WriteHTML($HTMLContent);
        $mpdf->Output($filename,"I");
    }

    public function report_pdf($user_id, $start_date, $end_date){
        $invoices = Invoice::with('invoice_item.product', 'user');
        if (isset($user_id) && $user_id!="null") {
            $invoices = $invoices->where('user_id', $user_id);
        }

        $date = '';
        if (isset($start_date) && $start_date!="null" && isset($end_date) && $end_date!="null"){
            $date = $start_date." - ".$end_date;
            $invoices = $invoices->whereRaw("invoice_date between '".$start_date."' and '".$end_date."'");
        }
        elseif (isset($start_date) && $start_date!="null"){
            $date = $start_date;
            $invoices = $invoices->where('invoice_date',$start_date);
        }
        elseif (isset($end_date) && $end_date!="null"){
            $date = $end_date;
            $invoices = $invoices->where('invoice_date',$end_date);
        }

        $invoices = $invoices->get();

        $HTMLContent = '<style type="text/css">
                            <!--
                            table { vertical-align: top; }
                            tr    { vertical-align: top; }
                            td    { vertical-align: top; }
                            -->
                            </style>';

        $HTMLContent .= '<page backcolor="#FEFEFE" style="font-size: 12pt">
                        <bookmark title="Lettre" level="0" ></bookmark>
                        <h2 style="text-align: center;margin: 0">Daily Report</h2>';

        foreach ($invoices as $invoice){
            $HTMLContent .= '<hr style="height: 1px">
                        <div>
                        <p style="font-size: 10pt;margin: 0;float: left; width: 50%; text-align: left;">Name: '.$invoice->user->full_name.'</p>
                        <p style="margin: 0;font-size: 10pt;float: left; width: 50%; text-align: right;">Invoice No: '.$invoice->invoice_no.'</p>
                        </div>
                        <div>
                        <p style="margin: 0;font-size: 10pt;float: left; text-align: right;">Date: '.date("d-m-Y", strtotime($invoice->invoice_date)).'</p>
                        </div>

                        <table cellspacing="0" style="width: 100%; margin-top:10px; font-size: 8pt; margin-bottom:0px;border: 1px solid grey;" align="center">
                            <colgroup>
                                <col style="width: 10%; text-align: center">
                                <col style="width: 50%; text-align: left">
                                <col style="width: 20%; text-align: center">
                                <col style="width: 10%; text-align: left">
                                <col style="width: 10%; text-align: right">
                            </colgroup>
                            <thead>
                                <tr>
                                    <th style="padding:8px 0;border: 1px solid grey;">No.</th>
                                    <th style="padding:8px 0;text-align: left;padding-left: 5px;border: 1px solid grey;">Item</th>
                                    <th style="padding:8px 0;border: 1px solid grey;">Qty (Kg)</th>
                                    <th style="padding:8px 0;text-align: left;padding-left: 5px;border: 1px solid grey;">Price</th>
                                    <th style="padding:8px 0;text-align: right;border: 1px solid grey;padding-right: 5px">Total</th>
                                </tr>
                            </thead>
                            <tbody>';

            $no = 1;
            foreach ($invoice->invoice_item as $invoice_item){
                $product = Product::withTrashed()->find($invoice_item->product_id);
                $product_title = '';
                if ($invoice->language == "English" && isset($product)){
                    $product_title = $product->title_english;
                }
                elseif ($invoice->language == "Hindi" && isset($product)){
                    $product_title = $product->title_english." | ".$product->title_hindi;
                }
                elseif ($invoice->language == "Gujarati" && isset($product)){
                    $product_title = $product->title_english." | ".$product->title_gujarati;
                }

                $HTMLContent .= '<tr>
                                    <td style="font-weight : 10px; padding:8px 0;text-align: center;border: 1px solid grey;">'.$no.'</td>
                                    <td style="font-weight : 10px; padding:8px 0;text-align: left;padding-left: 5px;border: 1px solid grey;">'.$product_title.'</td>
                                    <td style="font-weight : 10px; padding:8px 0;text-align: center;border: 1px solid grey;">'.$invoice_item->quantity.'</td>
                                    <td style="font-weight : 10px; padding:8px 0;text-align: left;padding-left: 5px;border: 1px solid grey;">'.number_format($invoice_item->price, 2, '.', ',').'</td>
                                    <td style="font-weight : 10px; padding:8px 0;text-align: right;padding-right: 5px;border: 1px solid grey;">'.number_format($invoice_item->final_price, 2, '.', ',').'</td>
                                </tr>';
                $no++;
            }
            $HTMLContent .= '<tr>
                                    <th colspan="2" style="padding:10px 0;border: 1px solid grey;">Total</th>
                                    <th  style="padding:10px 0;border: 1px solid grey;">'.$invoice->total_qty.'</th>
                                    <th  style="padding:10px 0;border: 1px solid grey;"></th>
                                    <th  style="padding:10px 0;text-align: right;padding-right: 5px;border: 1px solid grey;">'.number_format($invoice->final_amount, 2, '.', ',').'</th>
                             </tr>
                            </tbody>
                        </table>';
        }

        $product_stocks = ProductStock::with('product');
        if (isset($start_date) && $start_date!="null" && isset($end_date) && $end_date!="null"){
            $product_stocks = $product_stocks->whereRaw("stock_date between '".$start_date."' and '".$end_date."'");
        }
        elseif (isset($start_date) && $start_date!="null"){
            $product_stocks = $product_stocks->where('stock_date',$start_date);
        }
        elseif (isset($end_date) && $end_date!="null"){
            $product_stocks = $product_stocks->where('stock_date',$end_date);
        }
        $product_stocks = $product_stocks->get();

        if(isset($product_stocks) && count($product_stocks)>0) {
            $HTMLContent .= '<hr style="height: 1px"><h3 style="text-align: center;margin: 0">Stock</h3>';

            $HTMLContent .= '<table cellspacing="0" style="width: 100%; margin-top:10px;  font-size: 8pt; margin-bottom:0px;border: 1px solid grey;" align="center">
                            <colgroup>
                                <col style="width: 10%; text-align: center">
                                <col style="width: 50%; text-align: left">
                                <col style="width: 20%; text-align: left">
                                <col style="width: 20%; text-align: center">
                            </colgroup>
                            <thead>
                                <tr>
                                    <th style="padding:8px 0;text-align: center;border: 1px solid grey;">No.</th>
                                    <th style="padding:8px 0;text-align: left;padding-left: 5px;border: 1px solid grey;">Product</th>
                                    <th style="padding:8px 0;text-align: left;padding-left: 5px;border: 1px solid grey;">Purchase From</th>
                                    <th style="padding:8px 0;text-align: center;border: 1px solid grey;">Qty (Kg)</th>
                                </tr>
                            </thead>
                            <tbody>';
            $no = 1;
            foreach ($product_stocks as $product_stock) {
                $HTMLContent .= '<tr>
                                <td style="padding:8px 0;text-align: center;border: 1px solid grey;">' . $no . '</td>
                                <td style="padding:8px 0;text-align: left;padding-left: 5px;border: 1px solid grey;">' . $product_stock->product->title_english . '</td>
                                <td style="padding:8px 0;text-align: left;padding-left: 5px;border: 1px solid grey;">' . $product_stock->purchase_from . '</td>
                                <td style="padding:8px 0;text-align: center;border: 1px solid grey;">' . $product_stock->stock . '</td>
                             </tr>';
            }

            $HTMLContent .= '</tbody>
                        </table>';
        }

        $HTMLContent .= '</page>';

        $filename = "report_".time().".pdf";
        $mpdf = new Mpdf(["autoScriptToLang" => true, "autoLangToFont" => true, 'mode' => 'utf-8', 'format' => 'A4-P', 'margin_top' => 3, 'margin_bottom' => 3]);
        $mpdf->WriteHTML($HTMLContent);
        $mpdf->Output($filename,"I");
    }
}

