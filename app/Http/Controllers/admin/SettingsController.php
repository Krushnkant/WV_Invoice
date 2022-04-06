<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SettingsController extends Controller
{
    public function index(){
        $Settings = Setting::where('estatus',1)->first();
        return view('admin.settings.list',compact('Settings'));
    }

    public function editSettings(){
        $Settings = Setting::find(1);
        return response()->json($Settings);
    }

    public function updateInvoiceSetting(Request $request){
        $messages = [
            'prefix_invoice_no.required' =>'Please provide a Prefix For Invoice No',
            'invoice_no.required' =>'Please provide a Invoice No',
        ];

        $validator = Validator::make($request->all(), [
            'prefix_invoice_no' => 'required',
            'invoice_no' => 'required|numeric',
        ], $messages);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors(),'status'=>'failed']);
        }

        $Settings = Setting::find(1);
        if(!$Settings){
            return response()->json(['status' => '400']);
        }
        $Settings->prefix_invoice_no = $request->prefix_invoice_no;
        $Settings->invoice_no = $request->invoice_no;
        $Settings->save();
        return response()->json(['status' => '200','prefix_invoice_no' => $Settings->prefix_invoice_no, 'invoice_no' => $Settings->invoice_no]);
    }
}
