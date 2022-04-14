<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(){
        $customers = User::where('role',2)->count();
        $products = Product::count();
        $invoice_today = Invoice::whereRaw("DATE(created_at) = '".date('Y-m-d')."'")->count();
        $amount_invoice_today = Invoice::whereRaw("DATE(created_at) = '".date('Y-m-d')."'")->sum('final_amount');

        $record = Invoice::select(DB::raw("COUNT(*) as count"), DB::raw("final_amount as amount"), \DB::raw("DATE(created_at) as date"))
            ->where('created_at', '>', Carbon::today()->subDay(6))
            ->groupBy('amount','date')
            ->orderBy('date')
            ->get();
        $chart_data = [];
        foreach($record as $row) {
            $chart_data['label'][] = $row->date;
            $chart_data['data'][] = $row->amount;
        }
        $final_chart_data = json_encode($chart_data);
//        $labels = $chart_data['label'];
//        $data = $chart_data['data'];
//        dd($chart_data,$labels,$data);

        return view('admin.dashboard',compact('customers','products','invoice_today','amount_invoice_today','final_chart_data'));
    }
}
