<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductPriceController extends Controller
{
    public function index($user_id){
        return view('admin.product_prices.list',compact('user_id'));
    }

    public function get_customers_products(){
        $customers = User::where('role',2)->get();
        $products = Product::get();
        return ['customers' => $customers, 'products' => $products];
    }

    public function addorupdateProductPrice(Request $request){
        if($request->action == "update") {
            $messages = [
                'price.required' => 'Please provide a Product price',
            ];

            $validator = Validator::make($request->all(), [
                'price' => 'required',
            ], $messages);
        }
        else{
            $messages = [
                'customer.required' => 'Please select a customer',
                'product.required' => 'Please select a Product',
                'price.required' => 'Please provide a Product price',
            ];

            $validator = Validator::make($request->all(), [
                'customer' => 'required',
                'product' => 'required',
                'price' => 'required',
            ], $messages);
        }

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors(),'status'=>'failed']);
        }

        if($request->action != "update"){
            $Product_Price = ProductPrice::where('user_id',$request->customer)->where('product_id',$request->product)->first();
            if ($Product_Price){
                return response()->json(['error' => "This Customer Price already added",'status' => 401]);
            }
        }

        if(isset($request->action) && $request->action=="update"){
            $action = "update";
            $ProductPrice = ProductPrice::find($request->product_price_id);

            if(!$ProductPrice){
                return response()->json(['status' => '400']);
            }

//            $ProductPrice->user_id = $request->customer;
//            $ProductPrice->product_id = $request->product;
            $ProductPrice->price = $request->price;
        }
        else{
            $action = "add";
            $ProductPrice = new ProductPrice();
            $ProductPrice->user_id = $request->customer;
            $ProductPrice->product_id = $request->product;
            $ProductPrice->price = $request->price;
            $ProductPrice->created_at = new \DateTime(null, new \DateTimeZone('Asia/Kolkata'));
        }

        $ProductPrice->save();
        return response()->json(['status' => '200', 'action' => $action]);
    }

    public function allProductPriceslist(Request $request){
        if ($request->ajax()) {
            $columns = array(
                0 =>'id',
                1=> 'product',
                2=> 'price',
                3=> 'created_at',
                4=> 'action',
            );

            $totalData = ProductPrice::where('user_id',$request->user_id)->count();

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
                $ProductPrices = ProductPrice::with('user','product')
                    ->where('user_id',$request->user_id)
                    ->offset($start)
                    ->limit($limit)
                    ->orderBy($order,$dir)
                    ->get();
            }
            else {
                $search = $request->input('search.value');
                $ProductPrices =  ProductPrice::with('user','product')->where('user_id',$request->user_id);
                $ProductPrices = $ProductPrices->where(function($query) use($search){
                    $query->where('price','LIKE',"%{$search}%")
                        ->orWhereHas('user',function ($Query) use($search) {
                            $Query->where('full_name', 'Like', '%' . $search . '%');
                        })
                        ->orWhereHas('product',function ($Query) use($search) {
                            $Query->where('title_english', 'Like', '%' . $search . '%');
                        });
                    })
                    ->offset($start)
                    ->limit($limit)
                    ->orderBy($order,$dir)
                    ->get();

                $totalFiltered = count($ProductPrices->toArray());
            }

            $data = array();

            if(!empty($ProductPrices))
            {
                foreach ($ProductPrices as $ProductPrice)
                {
                    $action='';
                    $action .= '<button id="editProductPriceBtn" class="btn btn-gray text-blue btn-sm" data-toggle="modal" data-target="#ProductPriceModal" onclick="" data-id="' .$ProductPrice->id. '"><i class="fa fa-pencil" aria-hidden="true"></i></button>';
//                    $action .= '<button id="deleteProductPriceBtn" class="btn btn-gray text-danger btn-sm" data-toggle="modal" data-target="#DeleteProductPriceModal" onclick="" data-id="' .$ProductPrice->id. '"><i class="fa fa-trash-o" aria-hidden="true"></i></button>';

                    $nestedData['product'] = isset($ProductPrice->product)?$ProductPrice->product->title_english:'';
                    $nestedData['price'] = '<i class="fa fa-inr" aria-hidden="true"></i> '.$ProductPrice->price;
                    $nestedData['created_at'] = date('Y-m-d H:i:s', strtotime($ProductPrice->created_at));
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

    public function editProductPrice($id){
        $ProductPrice = ProductPrice::find($id);

        $customers = User::where('role',2)->get();
        $products = Product::get();

        return ['customers' => $customers, 'products' => $products, 'ProductPrice' => $ProductPrice];
    }

    public function get_products_price($product_id){
        $product_price = Product::find($product_id)->price;
        return $product_price;
    }

    public function deleteProductPrice($id){
        $ProductPrice = ProductPrice::find($id);
        if ($ProductPrice){
            $ProductPrice->estatus = 3;
            $ProductPrice->save();

            $ProductPrice->delete();
            return response()->json(['status' => '200']);
        }
        return response()->json(['status' => '400']);
    }
}
