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
            'company_name.required' =>'Please provide a Company Name',
            'company_logo.image' =>'Please provide a Valid Extension Logo(e.g: .jpg .png)',
            'company_logo.mimes' =>'Please provide a Valid Extension Logo(e.g: .jpg .png)',
            'company_address.required' =>'Please provide a Company Address',
            'company_mobile_no.required' =>'Please provide a Company Mobile Number',
        ];

        $validator = Validator::make($request->all(), [
            'prefix_invoice_no' => 'required',
            'invoice_no' => 'required|numeric',
            'company_name' => 'required',
            'company_logo' => 'image|mimes:jpeg,png,jpg',
            'company_address' => 'required',
            'company_mobile_no' => 'required|numeric|digits:10',
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
        $Settings->company_name = $request->company_name;
        $Settings->company_address = $request->company_address;
        $Settings->company_mobile_no = $request->company_mobile_no;

        $old_image = $Settings->company_logo;
        if ($request->hasFile('company_logo')) {
            $image = $request->file('company_logo');
            $image_name = 'company_logo_' . rand(111111, 999999) . time() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('images/company');
            $image->move($destinationPath, $image_name);
            if(isset($old_image)) {
                $old_image = public_path('images/company/' . $old_image);
                if (file_exists($old_image)) {
                    unlink($old_image);
                }
            }
            $Settings->company_logo = $image_name;
        }

        $Settings->save();
        return response()->json(['status' => '200','Settings' => $Settings]);
    }
}
