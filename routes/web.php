<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminLoginController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Admin\PostController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {

    return redirect()->route('login');
});


Route::get('/NoAuth', function () {
    return response()->json([
        'status' => false,
        'message' => 'No Auth user'
    ],401);
})->name('NoAuth');
Route::prefix('admin')->group(function () {
    Route::middleware(['NoAuthUser'])->group(function () {
        Route::get('/login',[AdminLoginController::class,'login'])->name('login');
        Route::post('/logined',[AdminLoginController::class,'logined'])->name('logined');
    });


    Route::get('userss', [UsersController::class, 'userss'])->name('userss');
    Route::get('users_star', [UsersController::class, 'users_star'])->name('users_star');
    Route::get('users_black_list', [UsersController::class, 'users_black_list'])->name('users_black_list');
    Route::post('update_user_data', [UsersController::class, 'update_user_data'])->name('update_user_data');
    Route::get('delete_user_photo/{id}', [UsersController::class, 'delete_user_photo'])->name('delete_user_photo');
    Route::get('single_page_user/{id}', [UsersController::class, 'single_page_user'])->name('single_page_user');
    Route::get('user_star/{id}', [UsersController::class, 'user_star'])->name('user_star');
    Route::get('black_list/{id}', [UsersController::class, 'black_list'])->name('black_list');
    Route::get('delete_user/{id}', [UsersController::class, 'delete_user'])->name('delete_user');


    Route::get('HomePage', [AdminLoginController::class,'HomePage'])->name('HomePage');
    Route::get('logoutAdmin', [AdminLoginController::class,'logoutAdmin'])->name('logoutAdmin');


    Route::get('settingView', [AdminLoginController::class, 'settingView'])->name('settingView');
    Route::post('updatePassword', [AdminLoginController::class, 'updatePassword'])->name('updatePassword');



    Route::get('all_post', [PostController::class, 'all_post'])->name('all_post');
    Route::get('single_page_post/{id}', [PostController::class, 'single_page_post'])->name('single_page_post');
    Route::get('delete_post/{id}', [PostController::class, 'delete_post'])->name('delete_post');
});
