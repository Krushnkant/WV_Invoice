<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductStockController extends Controller
{
    public function index(){
        return view('admin.product_stock.list');
    }

    public function addProductStock(Request $request){
        $messages = [
            'product.required' =>'Please provide a Product.',
            'stock.required' =>'Please provide a stock.',
            'purchase_from.required' =>'Please provide a purchase from.',
            'stock_date.required' =>'Please provide a stock date.',
        ];

        $validator = Validator::make($request->all(), [
            'product' => 'required',
            'stock' => 'required',
            'purchase_from' => 'required',
            'stock_date' => 'required',
        ], $messages);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors(),'status'=>'failed']);
        }

        $action = "add";
        $ProductStock = new ProductStock();
        $ProductStock->product_id = $request->product;
        $ProductStock->stock = $request->stock;
        $ProductStock->purchase_from = $request->purchase_from;
        $ProductStock->stock_date = date("Y-m-d", strtotime($request->stock_date));
        $ProductStock->created_at = new \DateTime(null, new \DateTimeZone('Asia/Kolkata'));
        $ProductStock->save();

        $ProductStock->product->stock = $ProductStock->product->stock + $request->stock;
        $ProductStock->product->save();
        return response()->json(['status' => '200', 'action' => $action]);
    }

    public function allProductStocklist(Request $request){
        if ($request->ajax()) {
            $columns = array(
                0 =>'id',
                1=> 'product',
                2=> 'stock',
                3=> 'purchase_from',
                4=> 'created_at',
                5=> 'action',
            );

            $totalData = ProductStock::count();

            $totalFiltered = $totalData;

            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');

            if($order == "id"){
                $order == "created_at";
                $dir = 'ASC';
            }

            if(empty($request->input('search.value')))
            {
                $ProductStocks = ProductStock::with('product')
                    ->offset($start)
                    ->limit($limit)
                    ->orderBy($order,$dir)
                    ->get();
            }
            else {
                $search = $request->input('search.value');
                $ProductStocks =  ProductStock::with('product');
                $ProductStocks = $ProductStocks->where(function($query) use($search){
                    $query->where('stock','LIKE',"%{$search}%")
                        ->where('purchase_from','LIKE',"%{$search}%")
                        ->orWhereHas('product',function ($Query) use($search) {
                            $Query->where('title_english', 'Like', '%' . $search . '%');
                        });
                    })
                    ->offset($start)
                    ->limit($limit)
                    ->orderBy($order,$dir)
                    ->get();

                $totalFiltered = count($ProductStocks->toArray());
            }

            $data = array();

            if(!empty($ProductStocks))
            {
                foreach ($ProductStocks as $ProductStock)
                {
                    $action='';
                    $action .= '<button id="deleteProductStockBtn" class="btn btn-gray text-danger btn-sm" data-toggle="modal" data-target="#DeleteProductStockModal" onclick="" data-id="' .$ProductStock->id. '"><i class="fa fa-trash-o" aria-hidden="true"></i></button>';

                    $nestedData['product'] = isset($ProductStock->product)?$ProductStock->product->title_english:'';
                    $nestedData['stock'] = $ProductStock->stock.' Kg';
                    $nestedData['purchase_from'] = $ProductStock->purchase_from;
                    $nestedData['created_at'] = date('d-m-Y', strtotime($ProductStock->stock_date));
                    $nestedData['action'] = $action;
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

    public function deleteProductStock($id){
        $ProductStock = ProductStock::find($id);
        if ($ProductStock){
            $ProductStock->estatus = 3;
            $ProductStock->save();

            $ProductStock->product->stock = $ProductStock->product->stock - $ProductStock->stock;
            $ProductStock->product->save();

            $ProductStock->delete();
            return response()->json(['status' => '200']);
        }
        return response()->json(['status' => '400']);
    }

    public function check_stock(Request $request){
        $product = Product::find($request->product_id);

        if ($product->stock >= $request->quantity){
            return true;
        }
        return false;
    }
}
