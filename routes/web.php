<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('admin',[\App\Http\Controllers\admin\AuthController::class,'index'])->name('admin.login');
Route::post('adminpostlogin', [\App\Http\Controllers\admin\AuthController::class, 'postLogin'])->name('admin.postlogin');
Route::get('logout', [\App\Http\Controllers\admin\AuthController::class, 'logout'])->name('admin.logout');

Route::group(['prefix'=>'admin','middleware'=>['auth'],'as'=>'admin.'],function () {
    Route::get('dashboard',[\App\Http\Controllers\admin\DashboardController::class,'index'])->name('dashboard');

    Route::get('users',[\App\Http\Controllers\admin\UserController::class,'index'])->name('users.list');
    Route::post('addorupdateuser',[\App\Http\Controllers\admin\UserController::class,'addorupdateuser'])->name('users.addorupdate');
    Route::post('alluserslist',[\App\Http\Controllers\admin\UserController::class,'alluserslist'])->name('alluserslist');
//    Route::get('changeuserstatus/{id}',[\App\Http\Controllers\admin\UserController::class,'changeuserstatus'])->name('users.changeuserstatus');
    Route::get('users/{id}/edit',[\App\Http\Controllers\admin\UserController::class,'edituser'])->name('users.edit');
    Route::get('users/{id}/delete',[\App\Http\Controllers\admin\UserController::class,'deleteuser'])->name('users.delete');

    Route::get('products',[\App\Http\Controllers\admin\ProductController::class,'index'])->name('products.list');
    Route::post('addorupdateProduct',[\App\Http\Controllers\admin\ProductController::class,'addorupdateProduct'])->name('products.addorupdate');
    Route::post('allProductslist',[\App\Http\Controllers\admin\ProductController::class,'allProductslist'])->name('allProductslist');
    Route::get('products/{id}/edit',[\App\Http\Controllers\admin\ProductController::class,'editProduct'])->name('products.edit');
    Route::get('products/{id}/delete',[\App\Http\Controllers\admin\ProductController::class,'deleteProduct'])->name('products.delete');

});
