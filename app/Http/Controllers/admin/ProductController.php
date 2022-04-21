<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\ProductStock;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index(){
        return view('admin.products.list');
    }

    public function addorupdateProduct(Request $request){
        $messages = [
            'image.image' =>'Please provide a Valid Extension Image(e.g: .jpg .png)',
            'image.mimes' =>'Please provide a Valid Extension Image(e.g: .jpg .png)',
            'title_english.required' =>'Please provide a Product Title',
            'title_hindi.required' =>'Please provide a Product Title',
            'title_gujarati.required' =>'Please provide a Product Title',
            'price.required' =>'Please provide a Product Price.',
        ];

        $validator = Validator::make($request->all(), [
            'image' => 'image|mimes:jpeg,png,jpg',
            'title_english' => 'required',
            'title_hindi' => 'required',
            'title_gujarati' => 'required',
            'price' => 'required|numeric',
        ], $messages);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors(),'status'=>'failed']);
        }

        if(isset($request->action) && $request->action=="update"){
            $action = "update";
            $Product = Product::find($request->product_id);

            if(!$Product){
                return response()->json(['status' => '400']);
            }

            $old_price = $Product->price;
            $old_image = $Product->image;
            $image_name = $old_image;

            $Product->title_english = $request->title_english;
            $Product->title_hindi = $request->title_hindi;
            $Product->title_gujarati = $request->title_gujarati;
            $Product->description = $request->description;
            $Product->price = $request->price;
        }
        else{
            $action = "add";
            $Product = new Product();
            $Product->title_english = $request->title_english;
            $Product->title_hindi = $request->title_hindi;
            $Product->title_gujarati = $request->title_gujarati;
            $Product->description = $request->description;
            $Product->price = $request->price;
            $Product->created_at = new \DateTime(null, new \DateTimeZone('Asia/Kolkata'));
            $image_name=null;
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $image_name = 'Product_' . rand(111111, 999999) . time() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('images/product');
            $image->move($destinationPath, $image_name);
            if(isset($old_image)) {
                $old_image = public_path('images/product/' . $old_image);
                if (file_exists($old_image)) {
                    unlink($old_image);
                }
            }
            $Product->image = $image_name;
        }

        $Product->save();

        if($request->action!="update"){
            $customers = User::where('role',2)->get();
            foreach ($customers as $customer){
                $product_price = new ProductPrice();
                $product_price->user_id = $customer->id;
                $product_price->product_id = $Product->id;
                $product_price->price = $Product->price;
                $product_price->save();
            }
        }

        if(isset($request->action) && $request->action=="update"){
            if (isset($request->is_update_for_all) && $request->is_update_for_all==1){
                $product_prices = ProductPrice::where('product_id',$Product->id)->get();
                foreach ($product_prices as $product_price){
                    $product_price->price = $Product->price;
                    $product_price->save();
                }
            }
            else{
                $product_prices = ProductPrice::where('product_id',$Product->id)->where('price',$old_price)->get();
                foreach ($product_prices as $product_price){
                    $product_price->price = $Product->price;
                    $product_price->save();
                }
            }
        }

        return response()->json(['status' => '200', 'action' => $action]);
    }

    public function allProductslist(Request $request){
        if ($request->ajax()) {
            $columns = array(
                0 =>'id',
                1 =>'image',
                2=> 'title',
                3=> 'description',
                4=> 'price',
                5=> 'stock',
                6=> 'created_at',
                7=> 'action',
            );

            $totalData = Product::count();

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
                $Products = Product::offset($start)
                    ->limit($limit)
                    ->orderBy($order,$dir)
                    ->get();
            }
            else {
                $search = $request->input('search.value');
                $Products =  Product::Query();
                $Products = $Products->where(function($query) use($search){
                    $query->where('title_english','LIKE',"%{$search}%")
                        ->orWhere('title_hindi', 'LIKE',"%{$search}%")
                        ->orWhere('title_gujarati', 'LIKE',"%{$search}%")
                        ->orWhere('description', 'LIKE',"%{$search}%")
                        ->orWhere('price', 'LIKE',"%{$search}%")
                        ->orWhere('stock', 'LIKE',"%{$search}%")
                        ->orWhere('created_at', 'LIKE',"%{$search}%");
                    })
                    ->offset($start)
                    ->limit($limit)
                    ->orderBy($order,$dir)
                    ->get();

                $totalFiltered = count($Products->toArray());
            }

            $data = array();

            if(!empty($Products))
            {
                foreach ($Products as $Product)
                {

                    if(isset($Product->image) && $Product->image!=null){
                        $image = url('public/images/product/'.$Product->image);
                    }
                    else{
                        $image = url('public/images/placeholder_image.png');
                    }

                    $action='';
                    $action .= '<button id="editProductBtn" class="btn btn-gray text-blue btn-sm" data-toggle="modal" data-target="#ProductModal" onclick="" data-id="' .$Product->id. '"><i class="fa fa-pencil" aria-hidden="true"></i></button>';
                    $action .= '<button id="deleteProductBtn" class="btn btn-gray text-danger btn-sm" data-toggle="modal" data-target="#DeleteProductModal" onclick="" data-id="' .$Product->id. '"><i class="fa fa-trash-o" aria-hidden="true"></i></button>';

                    $title = '';
                    $title .= '<span>English: '.$Product->title_english.'</span>';
                    $title .= '<span>Hindi: '.$Product->title_hindi.'</span>';
                    $title .= '<span>Gujarati: '.$Product->title_gujarati.'</span>';

                    $nestedData['image'] = '<img src="'. $image .'" width="50px" height="50px" alt="Product Image">';
                    $nestedData['title'] = $title;
                    $nestedData['description'] = $Product->description;
                    $nestedData['price'] = '<i class="fa fa-inr" aria-hidden="true"></i> '.$Product->price;
                    $nestedData['stock'] = $Product->stock." KG";
                    $nestedData['created_at'] = date('Y-m-d H:i:s', strtotime($Product->created_at));
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

    public function editProduct($id){
        $Product = Product::find($id);
        return response()->json($Product);
    }

    public function deleteProduct($id){
        $Product = Product::find($id);
        if ($Product){
            $Product->estatus = 3;
            $Product->save();
            $Product->delete();

            $product_prices = ProductPrice::where('product_id',$id)->delete();

            $product_stocks = ProductStock::where('product_id',$id)->delete();
            return response()->json(['status' => '200']);
        }
        return response()->json(['status' => '400']);
    }
}
