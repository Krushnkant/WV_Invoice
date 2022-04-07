<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Spipu\Html2Pdf\Exception\ExceptionFormatter;
use Spipu\Html2Pdf\Exception\Html2PdfException;
use Spipu\Html2Pdf\Html2Pdf;

class InvoiceController extends Controller
{
    public function index(){
        $action = "list";
        return view('admin.invoice.list',compact('action'));
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
                $title = $product->title_hindi;
            }
            elseif ($language == "Gujarati"){
                $title = $product->title_gujarati;
            }
            $html_product .= '<option value="'.$product->id.'">'.$title.' ('.$language.')</option>';
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
                <td>
                    <input class="form-control unitcost cost" placeholder="0.00" type="number" name="price" value="">
                    <label id="price-error" class="error invalid-feedback animated fadeInDown" for="price"></label>
                </td>
                <td>
                    <input class="form-control quantity qty" name="quantity" type="number" value="1" min="1">(KG)
                    <label id="quantity-error" class="error invalid-feedback animated fadeInDown" for="quantity"></label>
                </td>
                <td>
                    <input class="form-control discount disc" placeholder="0.00" type="number" name="discount" min="1" value="">
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
                $title = $product->title_hindi;
            }
            elseif ($language == "Gujarati"){
                $title = $product->title_gujarati;
            }
            $html_product .= '<option value="'.$product->id.'">'.$title.' ('.$language.')</option>';
        }

        return ['html' => $html_product];
    }

    public function change_product_price(Request $request){
        $price = ProductPrice::where('user_id',$request->user_id)->where('product_id',$request->product_id)->pluck('price')->first();
        return $price;
    }

    public function save(Request $request){
//        dd($request->all(),json_decode($request->InvoiceItemForm1,true));
        if ($request->action == "add"){
            $invoice = new Invoice();
        }
        elseif ($request->action == "update"){
            $invoice = Invoice::find($request->invoice_id);
        }
        $invoice->language = $request->language;
        $invoice->invoice_no = $request->invoice_no;
        $invoice->user_id = $request->customer_name;
        $invoice->invoice_date = $request->invoice_date;
        $invoice->total_price = $request->total_price;
        $invoice->total_qty = $request->total_qty;
        $invoice->total_discount = $request->total_discount;
        $invoice->final_amount = $request->final_amount;
        $invoice->save();

        if ($request->action == "update"){
            $invoice_items = InvoiceItem::where('invoice_id',$request->invoice_id)->get();
            foreach ($invoice_items as $invoice_item){
                $invoice_item->estatus = 3;
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
            if (isset($item['discount']) && $item['discount']!="") {
                $invoice_item->discount = $item['discount'];
            }
            $invoice_item->final_price = $item['final_price'];
            $invoice_item->save();
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
                $dir = 'desc';
            }

            $totalData = Invoice::count();
            $totalFiltered = $totalData;

            if(empty($request->input('search.value')))
            {
                $Invoices = Invoice::with('invoice_item.product','user');
                $Invoices = $Invoices->offset($start)
                    ->limit($limit)
                    ->orderBy($order,$dir)
                    ->get();
            }
            else {
                $search = $request->input('search.value');
                $Invoices = Invoice::with('invoice_item.product','user');
                $Invoices = $Invoices->where(function($query) use($search){
                    $query->where('invoice_no','LIKE',"%{$search}%")
                        ->orWhere('invoice_date', 'LIKE',"%{$search}%")
                        ->orWhere('total_price', 'LIKE',"%{$search}%")
                        ->orWhere('total_qty', 'LIKE',"%{$search}%")
                        ->orWhere('total_discount', 'LIKE',"%{$search}%")
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
                    if (isset($Invoice->total_price)){
                        $amount .= '<span>Total Price: '.$Invoice->total_price;
                    }
                    if (isset($Invoice->total_qty)){
                        $amount .= '<span>Total Quantity: '.$Invoice->total_qty;
                    }
                    if (isset($Invoice->total_discount)){
                        $amount .= '<span>Total Discount: '.$Invoice->total_discount;
                    }
                    if (isset($Invoice->final_amount)){
                        $amount .= '<span>Final Amount: '.$Invoice->final_amount;
                    }

                    $table = '<table class="subTable text-left" cellpadding="5" cellspacing="0" border="0" width="100%" id="items_table">';
                    $table .= '<tbody>';
                    $table .='<tr style="width: 100%">';
                    $table .= '<th style="text-align: center">Item No.</th>';
                    $table .= '<th>Item Name</th>';
                    $table .= '<th>Price</th>';
                    $table .= '<th>Quantity</th>';
                    $table .= '<th>Discount</th>';
                    $table .= '<th>Final Price</th>';
                    $table .= '</tr>';
                    $item = 1;
                    foreach ($Invoice->invoice_item as $invoice_item){
                        if ($Invoice->language == "English"){
                            $product = $invoice_item->product->title_english;
                        }
                        elseif ($Invoice->language == "Hindi"){
                            $product = $invoice_item->product->title_hindi;
                        }
                        elseif ($Invoice->language == "Gujarati"){
                            $product = $invoice_item->product->title_gujarati;
                        }
                        $table .='<tr>';
                        $table .= '<td style="text-align: center">'.$item.'</td>';
                        $table .= '<td>'.$product.'</td>';
                        $table .= '<td><i class="fa fa-inr" aria-hidden="true"></i> '.$invoice_item->price.'</td>';
                        $table .= '<td>'.$invoice_item->quantity.'</td>';
                        $table .= '<td><i class="fa fa-inr" aria-hidden="true"></i> '.$invoice_item->discount.'</td>';
                        $table .= '<td><i class="fa fa-inr" aria-hidden="true"></i> '.$invoice_item->final_price.'</td>';
                        $table .= '</tr>';
                        $item++;
                    }
                    $table .='</tbody>';
                    $table .='</table>';

                    $action = '';
                    $action .= '<button id="printBtn" class="btn btn-gray text-blue btn-sm" onclick="getInvoiceData(\''.$Invoice->id.'\')"><i class="fa fa-print" aria-hidden="true"></i></button>';
                    $action .= '<button id="editInvoiceBtn" class="btn btn-gray text-blue btn-sm" data-id="'.$Invoice->id.'"><i class="fa fa-pencil" aria-hidden="true"></i></button>';
                    $action .= '<button id="deleteInvoiceBtn" class="btn btn-gray text-danger btn-sm" data-toggle="modal" data-target="#DeleteInvoiceModal" data-id="'.$Invoice->id.'"><i class="fa fa-trash-o" aria-hidden="true"></i></button>';

                    $nestedData['invoice_no'] = $Invoice->invoice_no;
                    $nestedData['customer_info'] = $Invoice->user->full_name;
                    $nestedData['amount'] = $amount;
                    $nestedData['invoice_date'] = $Invoice->invoice_date;
                    $nestedData['action'] = $action;
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
                $invoice_item->delete();
            }

            return response()->json(['status' => '200']);
        }
        return response()->json(['status' => '400']);
    }

    public function generate_pdf($id){
        try{
            $invoice = Invoice::with('invoice_item.product','user')->where('id',$id)->first();
            $settings = Setting::find(1);
            $Icon = url('public/images/avatar.png');

            $HTMLContent = '<style type="text/css">
                            <!--
                            table { vertical-align: top; }
                            tr    { vertical-align: top; }
                            td    { vertical-align: top; }
                            -->
                            </style>';
            $HTMLContent .= '<page backcolor="#FEFEFE" style="font-size: 12pt">
                        <bookmark title="Lettre" level="0" ></bookmark>
                        <table cellspacing="0" style="width: 100%; text-align: center; font-size: 14px; border-bottom: dotted 1px black;">
                            <tr>
                                <td style="width: 25%; color: #444444;">
                                    <img style="width: 100%;" src="'.url('public/images/company/'.$settings->company_logo).'" alt="Logo"><br>
                                </td>
                                <td style="width: 50%;">
                                	<h3 style="text-align: center; font-size: 20pt; margin-bottom: 0;">'.$settings->company_name.'</h3>
			                        <h5 style="text-align: center; margin-bottom: 0;">webvedant@gmail.com</h5>
			                        <h5 style="text-align: center; margin-bottom: 0;">'.$settings->company_mobile_no.'</h5>
			                        <p style="padding-bottom:10px; text-align: center; font-size: 10pt margin-bottom: 0;">'.$settings->company_address.'</p>
                                </td>
                                <td style="width: 25%;">
                                </td>
                            </tr>
                        </table>
                        <br>
                        <div style="width:100%; margin-top:0px; padding-top:0px; text-align: center; font-size: 15pt;"><b>Invoice</b></div>
                        <table cellspacing="0" style="width: 100%;">
                            <colgroup>
                                <col style="width: 12%;">
                                <col style="width: 62%;">
                                <col style="width: 12%;">
                                <col style="width: 14%;">
                            </colgroup>
                            <tbody>
                                <tr>
                                    <td style="font-size: 12pt; padding:2px 0;">
                                        Customer
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
                                        Language
                                    </td>
                                    <td style="font-size: 10pt; padding:2px 0;">
                                        : '.$invoice->language.'
                                    </td>
                                    <td style="font-size: 10pt; padding:2px 0;">
                                        Date
                                    </td>
                                    <td style="font-size: 10pt; padding:2px 0;">
                                        : '.date('d M, Y', strtotime($invoice->invoice_date)).'
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <table cellspacing="0" style="width: 100%; margin-top:10px;  font-size: 10pt; margin-bottom:10px;" align="center">
                            <colgroup>
                                <col style="width: 10%; text-align: center">
                                <col style="width: 40%; text-align: center">
                                <col style="width: 16%; text-align: center">
                                <col style="width: 10%; text-align: center">
                                <col style="width: 10%; text-align: center">
                                <col style="width: 14%; text-align: center">
                            </colgroup>
                            <thead>
                                <tr style="background: #ffe6e6;   ">
                                    <th colspan="6" style="text-align: center; border-top : solid 1px gray; border-bottom: solid 1px grey;  padding:8px 0;"> Item Details </th>
                                </tr>
                                <tr>
                                    <th style="border-bottom: solid 1px gray; padding:8px 0;">No.</th>
                                    <th style="border-bottom: solid 1px gray; padding:8px 0;">Item</th>
                                    <th style="border-bottom: solid 1px gray; padding:8px 0;">Price</th>
                                    <th style="border-bottom: solid 1px gray; padding:8px 0;">Qty</th>
                                    <th style="border-bottom: solid 1px gray; padding:8px 0;">Disc</th>
                                    <th style="border-bottom: solid 1px gray; padding:8px 0;">Total</th>
                                </tr>
                            </thead>
                            <tbody>';

            $no = 1;
            foreach ($invoice->invoice_item as $invoice_item){
                if ($invoice->language == "English"){
                    $item = $invoice_item->product->title_english;
                }
                elseif ($invoice->language == "Hindi"){
                    $item = $invoice_item->product->title_hindi;
                }
                elseif ($invoice->language == "Gujarati"){
                    $item = $invoice_item->product->title_gujarati;
                }

                $HTMLContent .= '<tr>
                                    <th style="font-weight : 10px; padding:8px 0;">'.$no.'</th>
                                    <th style="font-weight : 10px; padding:8px 0;"><b>'.$item.'</b></th>
                                    <th style="font-weight : 10px; padding:8px 0;">'.number_format($invoice_item->price, 2, '.', ',').'</th>
                                    <th style="font-weight : 10px; padding:8px 0;">'.$invoice_item->quantity.' KG</th>
                                    <th style="font-weight : 10px; padding:8px 0;">'.number_format($invoice_item->discount, 2, '.', ',').'</th>
                                    <th style="font-weight : 10px; padding:8px 0;">'.number_format($invoice_item->final_price, 2, '.', ',').'</th>
                                </tr>';
                $no++;
            }

            $HTMLContent .= '<tr>
                                    <td colspan="6" style="padding:4px 0;"></td>
                             </tr>
                             <tr>
                                    <th colspan="2" style="padding:10px 0; border-top : solid 0.5px black; border-bottom: solid 1px black;">Total</th>
                                    <th  style="padding:10px 0; border-top : solid 0.5px black; border-bottom: solid 1px black;">'.number_format($invoice->total_price, 2, '.', ',').'</th>
                                    <th  style="padding:10px 0; border-top : solid 0.5px black; border-bottom: solid 1px black;">'.$invoice->total_qty.'</th>
                                    <th  style="padding:10px 0; border-top : solid 0.5px black; border-bottom: solid 1px black;">'.number_format($invoice->total_discount, 2, '.', ',').'</th>
                                    <th  style="padding:10px 0; border-top : solid 0.5px black; border-bottom: solid 1px black;">'.number_format($invoice->final_amount, 2, '.', ',').'</th>
                             </tr>
                             <tr>
                                    <td colspan="2" style="padding:8px 0; border-bottom: solid 1px black;"></td>
                                    <td colspan="3" style="padding:8px 0; text-align:left; padding-left : 10px; border-bottom: solid 1px black; border-left: solid 1px black;">Total Amount</td>
                                    <td style="padding:8px 0; border-bottom: solid 1px black;"><span style="font-family: DejaVu Sans; sans-serif;">&#8377;</span>'.number_format($invoice->final_amount, 2, '.', ',').'</td>
                             </tr>
                            </tbody>
                        </table>
                        <br>';

            $HTMLContent .= '<table cellspacing="0" style="width: 100%; margin-top: 10px;">
                                <tr>
                                    <td  style="padding:10px 0; width :50%; border-top : solid 0.5px gray; border-bottom: solid 1px gray; text-align:left; color:gray;"><i>[This Document is computer generated.]</i> </td>
                                    <td  style="padding:10px 0; width :50%; border-top : solid 0.5px gray; border-bottom: solid 1px gray; text-align:right; color:gray;">Invoice No : <b>('.$invoice->invoice_no.')</b> '.date('d/m/Y', strtotime($invoice->invoice_date)).'</td>
                                </tr>
                            </table>
                        </page>';

            $html2pdf = new Html2Pdf('P', 'A4', 'fr');
            $html2pdf->pdf->SetDisplayMode('fullpage');
            $html2pdf->writeHTML($HTMLContent);
            $html2pdf->output($invoice->invoice_no.'.pdf');
        } catch (Html2PdfException $e) {
            $html2pdf->clean();

            $formatter = new ExceptionFormatter($e);
            echo $formatter->getHtmlMessage();
        }
    }
}

