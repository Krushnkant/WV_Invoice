<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index(){
        return view('admin.users.list');
    }

    public function addorupdateuser(Request $request){
        $messages = [
            'profile_pic.image' =>'Please provide a Valid Extension Image(e.g: .jpg .png)',
            'profile_pic.mimes' =>'Please provide a Valid Extension Image(e.g: .jpg .png)',
            'full_name.required' =>'Please provide a FullName',
            'mobile_no.required' =>'Please provide a Mobile No.',
            'email.required' =>'Please provide a Email Address.',
            'password.required' =>'Please provide a Password.',
            'address.required' =>'Please provide a Address.',
        ];

        if ($request->role == 1){
            $validator = Validator::make($request->all(), [
                'profile_pic' => 'image|mimes:jpeg,png,jpg',
                'full_name' => 'required',
                'mobile_no' => 'required|numeric|digits:10',
                'email' => 'required|email',
                'password' => 'required',
            ], $messages);
        }
        else{
            $validator = Validator::make($request->all(), [
                'profile_pic' => 'image|mimes:jpeg,png,jpg',
                'full_name' => 'required',
                'mobile_no' => 'required|numeric|digits:10',
                'address' => 'required',
            ], $messages);
        }

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors(),'status'=>'failed']);
        }

        if(isset($request->action) && $request->action=="update"){
            $action = "update";
            $user = User::find($request->user_id);

            if(!$user){
                return response()->json(['status' => '400']);
            }

            $old_image = $user->profile_pic;
            $image_name = $old_image;

            $user->full_name = $request->full_name;
            $user->mobile_no = $request->mobile_no;
            $user->role = $request->role;
            if ($request->role == 1){
                $user->email = $request->email;
                $user->password = Hash::make($request->password);
                $user->decrypted_password = $request->password;
                $user->address = null;
            }
            else{
                $user->email = null;
                $user->password = null;
                $user->decrypted_password = null;
                $user->address = $request->address;
            }
        }
        else{
            $action = "add";
            $user = new User();
            $user->full_name = $request->full_name;
            $user->mobile_no = $request->mobile_no;
            $user->role = $request->role;
            $user->created_at = new \DateTime(null, new \DateTimeZone('Asia/Kolkata'));
            if ($request->role == 1){
                $user->email = $request->email;
                $user->password = Hash::make($request->password);
                $user->decrypted_password = $request->password;
            }
            if ($request->role == 2){
                $user->address = $request->address;
            }
            $image_name=null;
        }

        if ($request->hasFile('profile_pic')) {
            $image = $request->file('profile_pic');
            $image_name = 'profilePic_' . rand(111111, 999999) . time() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('images/profile_pic');
            $image->move($destinationPath, $image_name);
            if(isset($old_image)) {
                $old_image = public_path('images/profile_pic/' . $old_image);
                if (file_exists($old_image)) {
                    unlink($old_image);
                }
            }
            $user->profile_pic = $image_name;
        }

        $user->save();

        if ($request->action!="update" && $request->role==2){
            $products = Product::get();
            foreach ($products as $product){
                $product_price = new ProductPrice();
                $product_price->user_id = $user->id;
                $product_price->product_id = $product->id;
                $product_price->price = $product->price;
                $product_price->save();
            }
        }

        return response()->json(['status' => '200', 'action' => $action]);
    }

    public function alluserslist(Request $request){
        if ($request->ajax()) {
            $tab_type = $request->tab_type;
            if ($tab_type == "customer_user_tab"){
                $role = 2;
            }
            elseif ($tab_type == "admin_user_tab"){
                $role = 1;
            }

            $columns = array(
                0 =>'id',
                1 =>'profile_pic',
                2=> 'contact_info',
                3=> 'role',
                4=> 'created_at',
                5=> 'action',
            );

            $totalData = User::count();
            if (isset($role)){
                $totalData = User::where('role',$role)->count();
            }

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
                $users = User::offset($start)
                    ->limit($limit)
                    ->orderBy($order,$dir)
                    ->get();
                if (isset($role)){
                    $users = User::where('role',$role)
                        ->offset($start)
                        ->limit($limit)
                        ->orderBy($order,$dir)
                        ->get();
                }
            }
            else {
                $search = $request->input('search.value');
                $users =  User::Query();
                if (isset($role)){
                    $users = $users->where('role',$role);
                }
                $users = $users->where(function($query) use($search){
                      $query->where('full_name','LIKE',"%{$search}%")
                            ->orWhere('email', 'LIKE',"%{$search}%")
                            ->orWhere('mobile_no', 'LIKE',"%{$search}%")
                            ->orWhere('decrypted_password', 'LIKE',"%{$search}%")
                            ->orWhere('created_at', 'LIKE',"%{$search}%");
                      })
                      ->offset($start)
                      ->limit($limit)
                      ->orderBy($order,$dir)
                      ->get();

                $totalFiltered = count($users->toArray());
            }

            $data = array();

            if(!empty($users))
            {
                foreach ($users as $user)
                {

                    if(isset($user->profile_pic) && $user->profile_pic!=null){
                        $profile_pic = url('public/images/profile_pic/'.$user->profile_pic);
                    }
                    else{
                        $profile_pic = url('public/images/default_avatar.jpg');
                    }

                    $contact_info = '';
                    if (isset($user->email)){
                        $contact_info = '<span><i class="fa fa-envelope" aria-hidden="true"></i> ' .$user->email .'</span>';
                    }
                    if (isset($user->mobile_no)){
                        $contact_info .= '<span><i class="fa fa-phone" aria-hidden="true"></i> ' .$user->mobile_no .'</span>';
                    }

                    if(isset($user->full_name)){
                        if ($user->role == 2){
                            $full_name = '<a href="'.url('admin/product_prices/'.$user->id).'" target="_blank">'.$user->full_name . " [" . $user->id . "]".'</a>';
                        }
                        else {
                            $full_name = $user->full_name . " [" . $user->id . "]";
                        }
                    }
                    else{
                        $full_name="";
                    }

                    $action='';
                    $action .= '<button id="editUserBtn" class="btn btn-gray text-blue btn-sm" data-toggle="modal" data-target="#UserModal" onclick="" data-id="' .$user->id. '"><i class="fa fa-pencil" aria-hidden="true"></i></button>';
                    $action .= '<button id="deleteUserBtn" class="btn btn-gray text-danger btn-sm" data-toggle="modal" data-target="#DeleteUserModal" onclick="" data-id="' .$user->id. '"><i class="fa fa-trash-o" aria-hidden="true"></i></button>';

                    if ($user->role == 2){
                        $action .= '<button id="ProductPriceBtn" class="btn btn-gray text-blue btn-sm" onclick="" data-id="' .$user->id. '">Product Price</button>';
                    }

                    $nestedData['profile_pic'] = '<img src="'. $profile_pic .'" width="50px" height="50px" alt="Profile Pic"><span>'.$full_name.'</span>';
                    $nestedData['contact_info'] = $contact_info;
                    $nestedData['role'] = ($user->role == 1)?"Admin":"Customer";
                    $nestedData['created_at'] = date('Y-m-d H:i:s', strtotime($user->created_at));
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

    public function changeuserstatus($id){
        $user = User::find($id);
        if ($user->estatus==1){
            $user->estatus = 2;
            $user->save();
            return response()->json(['status' => '200','action' =>'deactive']);
        }
        if ($user->estatus==2){
            $user->estatus = 1;
            $user->save();
            return response()->json(['status' => '200','action' =>'active']);
        }
    }

    public function edituser($id){
        $user = User::find($id);
        return response()->json($user);
    }

    public function deleteuser($id){
        $user = User::find($id);
        if ($user){
            $user->estatus = 3;
            $user->save();

            $user->delete();
            return response()->json(['status' => '200']);
        }
        return response()->json(['status' => '400']);
    }

}
