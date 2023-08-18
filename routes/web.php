<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\ProductOriginController;
use App\Http\Controllers\ProductPartnerController;
use App\Http\Controllers\ProductTestController;
use App\Http\Controllers\PuppeteerController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;

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

Route::get('register', [AuthController::class, 'register'])->name('register');
Route::post('post-register', [AuthController::class, 'postRegister'])->name('register.post');
Route::get('/', [AuthController::class, 'getLogin'])->name('login');
Route::post('post-login', [AuthController::class, 'postLogin'])->name('login.post');

Route::get('logout', [AuthController::class, 'getLogout'])->name('logout');

Route::group(['middleware' => 'checkAdminLogin'], function () {
    Route::get('users', [UserController::class, 'listUser'])->name('listUser');

    Route::get('crawl-data/{id}', [PartnerController::class, 'crawl'])->name('crawl-data');
    //nhóm người dùng
//    Route::prefix('')->name('users.')->middleware('can:users')->group(function (){
//        Route::get('users', [UserController::class, 'listUser'])->name('listUser');
//    });
    Route::get('list-group',[GroupController::class,'listGroup'])->name('list-group');
    Route::get('add-group',[GroupController::class,'addGroup'])->name('add-group');
    Route::post('save-group',[GroupController::class,'saveGroup'])->name('save-group');
    Route::get('edit-group/{id}',[GroupController::class,'editGroup'])->name('edit-group');
    Route::post('update-group/{id}',[GroupController::class,'updateGroup'])->name('update-group');
    Route::get('delete-group/{id}', [GroupController::class, 'deleteGroup']);
    Route::get('permisstion-group/{id}',[GroupController::class,'Permissions']);
    Route::post('permisstion-group/{group}',[GroupController::class,'postPermissions'])->name('postPermission');
    Route::get('add-module',[GroupController::class,'addModule'])->name('add-module');
    Route::post('save-module',[GroupController::class,'saveModule'])->name('save-module');
    //Quyền
    Route::get('add-permission',[GroupController::class,'addPermissions'])->name('add-permission');
    Route::post('save-permission',[GroupController::class,'savePermissions'])->name('save-permission');

//danh muc sp
    Route::get('list-category', [CategoryController::class, 'listCategory'])->name('list-category');
    Route::get('add-category', [CategoryController::class, 'addCategory'])->name('add-category');
    Route::get('edit-category/{id}', [CategoryController::class, 'edit_category'])->name('edit_category');
    Route::post('save-category', [CategoryController::class, 'saveCategory'])->name('save-category');
    Route::post('update-category/{id}', [CategoryController::class, 'update_category'])->name('update_category');
    Route::get('delete-category/{id}', [CategoryController::class, 'delete_category']);
//đối tác
    Route::get('list-partner', [PartnerController::class, 'listPartner'])->name('list-partner');
    Route::get('add-partner', [PartnerController::class, 'addPartner'])->name('add-partner');
    Route::get('edit-partner/{id}', [PartnerController::class, 'edit_partner'])->name('edit_partner');
    Route::post('update-partner/{id}', [PartnerController::class, 'update_partner'])->name('update_partner');

    Route::post('save-partner', [PartnerController::class, 'savePartner'])->name('save-partner');
    Route::get('delete-partner/{id}', [PartnerController::class, 'delete_partner']);
//sản phẩm gốc
    Route::get('list-product-original', [ProductOriginController::class, 'listProductOriginal'])->name('list-product-original');
    Route::get('add-product-original', [ProductOriginController::class, 'addProductOriginal'])->name('add-product-original');
    Route::get('export-product-original', [ProductOriginController::class, 'exportProductOrigin'])->name('exportProductOrigin');
    Route::post('import-product-original', [ProductOriginController::class, 'importProductOriginal'])->name('importProductOrigin');
    Route::post('importJson-product-original', [ProductOriginController::class, 'importJsonfile'])->name('importJsonfile');
    Route::get('edit-product-original/{id}', [ProductOriginController::class, 'editProductOriginal'])->name('edit-product-original');
    Route::post('save-product-original', [ProductOriginController::class, 'saveProductOriginal'])->name('save-product-original');
    Route::post('update-product-original/{id}', [ProductOriginController::class, 'updateProductOriginal'])->name('update-product-original');
    Route::get('delete-product-original/{id}', [ProductOriginController::class, 'delete_ProductOriginal'])->name('delete-product-original');
//sản phầm đối tác

    Route::get('list-product-partner', [ProductPartnerController::class, 'listProductPartner'])->name('list-product-partner');

    Route::get('compare-price', [ProductPartnerController::class, 'comparePrices']);
    Route::get('export-product-partner', [ProductPartnerController::class, 'exportProductPartner'])->name('exportProductPartner');

//kết quả so sánh
    Route::get('resultProduct', [ProductController::class, 'resultProduct'])->name('result');

    Route::get('reportProduct', [ProductController::class, 'reportProduct'])->name('report');

    Route::get('list', [ProductController::class, 'getList'])->name('list');

    Route::get('export-result-product', [ProductController::class, 'exportResultProduct'])->name('exportProductResult');
    //Lịch sử
    Route::get('history', [ProductController::class, 'historyComparePrices'])->name('historyList');
    Route::get('export-history', [ProductController::class, 'exportHistoryProduct'])->name('historyProductExport');

    Route::get('products/filter', [ProductController::class, 'resultProduct'])->name('products.filter');

    // mail
    Route::get('list-email', [ProductController::class, 'listEmail'])->name('listEmail');
    Route::get('add-email', [ProductController::class, 'addMail'])->name('addMail');
    Route::post('save-email', [ProductController::class, 'saveMail'])->name('saveMail');
    Route::get('edit-email/{id}', [ProductController::class, 'editEmail'])->name('editMail');
    Route::post('update-email/{id}', [ProductController::class, 'updateMail'])->name('updateMail');
    Route::get('delete-email/{id}', [ProductController::class, 'deleteMail'])->name('deleteMail');
    Route::get('send-notification/{id}', [ProductController::class, 'sendNotification'])->name('send.notification');

});

Route::get('scraper', [ProductTestController::class, 'scraper']);
Route::get('getdata', [ProductTestController::class, 'getJsondata']);
Route::get('data-lazada', [ProductTestController::class, 'getDataLazada']);
Route::get('data-shoppe', [ProductTestController::class, 'getDataShoppe']);
Route::get('data-tiki', [ProductTestController::class, 'getdataTiki']);
Route::get('data', [PuppeteerController::class, 'getWebsiteData']);
Route::get('sendo', [ProductTestController::class, 'Sendo']);
Route::get('shoppe', [ProductTestController::class, 'shoppe']);
Route::get('mediamart', [ProductTestController::class, 'mediaMart']);
Route::get('dien-may-xanh', [ProductTestController::class, 'dienMayXanh']);
Route::get('pico', [ProductTestController::class, 'pico']);
Route::get('kitchenstore', [ProductTestController::class, 'kitchenStore']);
Route::get('nguyenkim', [ProductTestController::class, 'nguyenKim']);
Route::get('khanhvyhome', [ProductTestController::class, 'khanhvyhome']);
Route::get('dienmaygiakhang', [ProductTestController::class, 'dienmaygiakhang']);
Route::get('mmvietnam', [ProductTestController::class, 'mmvietnam']);
Route::get('kingshop', [ProductTestController::class, 'kingshop']);
Route::get('bestmua', [ProductTestController::class, 'bestmua']);
Route::get('dienmayquan4', [ProductTestController::class, 'dienmayquan4']);
Route::get('dienmaytoanlinh', [ProductTestController::class, 'dienmaytoanlinh']);
Route::get('dienmayhoanghai', [ProductTestController::class, 'dienmayhoanghai']);
Route::get('aeoneshop', [ProductTestController::class, 'aeoneshop']);
Route::get('ecomart', [ProductTestController::class, 'ecomart']);
Route::get('hahahaha', [ProductTestController::class, 'hahahaha']);
Route::get('bepvuson', [ProductTestController::class, 'bepvuson']);
Route::get('dienmaytayho', [ProductTestController::class, 'dienmaytayho']);
Route::get('dienmayquanghanh', [ProductTestController::class, 'dienmayquanghanh']);


