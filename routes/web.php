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

    Route::get('product_prices/{id}',[\App\Http\Controllers\admin\ProductPriceController::class,'index'])->name('product_prices.list');
    Route::get('get_customers_products',[\App\Http\Controllers\admin\ProductPriceController::class,'get_customers_products'])->name('product_prices.get_customers_products');
    Route::post('addorupdateProductPrice',[\App\Http\Controllers\admin\ProductPriceController::class,'addorupdateProductPrice'])->name('product_prices.addorupdate');
    Route::post('allProductPriceslist',[\App\Http\Controllers\admin\ProductPriceController::class,'allProductPriceslist'])->name('allProductPriceslist');
    Route::get('product_prices/{id}/edit',[\App\Http\Controllers\admin\ProductPriceController::class,'editProductPrice'])->name('product_prices.edit');
    Route::get('get_products_price/{product_id}',[\App\Http\Controllers\admin\ProductPriceController::class,'get_products_price'])->name('product_prices.get_products_price');
    Route::get('product_prices/{id}/delete',[\App\Http\Controllers\admin\ProductPriceController::class,'deleteProductPrice'])->name('product_prices.delete');
    Route::get('product_prices/pdf/{id}',[\App\Http\Controllers\admin\ProductPriceController::class,'generate_pdf'])->name('product_prices.pdf');

    Route::get('settings',[\App\Http\Controllers\admin\SettingsController::class,'index'])->name('settings.list');
    Route::post('updateInvoiceSetting',[\App\Http\Controllers\admin\SettingsController::class,'updateInvoiceSetting'])->name('settings.updateInvoiceSetting');
    Route::get('settings/edit',[\App\Http\Controllers\admin\SettingsController::class,'editSettings'])->name('settings.edit');

    Route::get('invoice',[\App\Http\Controllers\admin\InvoiceController::class,'index'])->name('invoice.list');
    Route::get('invoice/create',[\App\Http\Controllers\admin\InvoiceController::class,'create'])->name('invoice.add');
    Route::post('invoice/add_row_item',[\App\Http\Controllers\admin\InvoiceController::class,'add_row_item'])->name('invoice.add_row_item');
    Route::post('invoice/change_products',[\App\Http\Controllers\admin\InvoiceController::class,'change_products'])->name('invoice.change_products');
    Route::post('invoice/change_product_price',[\App\Http\Controllers\admin\InvoiceController::class,'change_product_price'])->name('invoice.change_product_price');
    Route::post('invoice/save',[\App\Http\Controllers\admin\InvoiceController::class,'save'])->name('invoice.save');
    Route::post('allInvoicelist',[\App\Http\Controllers\admin\InvoiceController::class,'allInvoicelist'])->name('allInvoicelist');
    Route::get('invoice/edit/{id}',[\App\Http\Controllers\admin\InvoiceController::class,'edit'])->name('invoice.edit');
    Route::get('invoice/{id}/delete',[\App\Http\Controllers\admin\InvoiceController::class,'delete'])->name('invoice.delete');
    Route::get('invoice/pdf/{id}',[\App\Http\Controllers\admin\InvoiceController::class,'generate_pdf'])->name('invoice.pdf');
    Route::get('invoice/report/{user_id}/{start_date}/{end_date}',[\App\Http\Controllers\admin\InvoiceController::class,'report_pdf'])->name('report.pdf');

    Route::get('product_stock',[\App\Http\Controllers\admin\ProductStockController::class,'index'])->name('product_stock.list');
    Route::post('addProductStock',[\App\Http\Controllers\admin\ProductStockController::class,'addProductStock'])->name('product_stock.add');
    Route::post('allProductStocklist',[\App\Http\Controllers\admin\ProductStockController::class,'allProductStocklist'])->name('allProductStocklist');
    Route::get('product_stock/{id}/delete',[\App\Http\Controllers\admin\ProductStockController::class,'deleteProductStock'])->name('product_stock.delete');
    Route::post('check_stock',[\App\Http\Controllers\admin\ProductStockController::class,'check_stock'])->name('check_stock');

});
