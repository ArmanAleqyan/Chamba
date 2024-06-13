<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\ForgotController;
use App\Http\Controllers\Api\Profile\ProfileController;
use App\Http\Controllers\Api\Search\SearchUserController;
use App\Http\Controllers\Api\Follow\FollowController;
use App\Http\Controllers\Api\Posts\PostController;
use App\Http\Controllers\Api\Like\PostLikeController;
use App\Http\Controllers\Api\Like\CommentLikeController;
use App\Http\Controllers\Api\Comment\CommentController;
use App\Http\Controllers\Api\BlackList\BlackListController;
use App\Http\Controllers\Api\Chat\ChatController;
use App\Http\Controllers\Api\Notification\NotificationController;
use App\Http\Controllers\Api\Book\BookController;
use App\Http\Controllers\Admin\AdminLoginController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::post('/pragladka',[AdminLoginController::class,'pragladka'])->name('pragladka');
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('register', [RegisterController::class, 'register']);

Route::post('confirm_register', [RegisterController::class, 'confirm_register']);
Route::post('send_code_from_forgot_password', [ForgotController::class, 'send_code_from_forgot_password']);
Route::post('validation_forgot_code', [ForgotController::class, 'validation_forgot_code']);
Route::post('add_password_from_forgot', [ForgotController::class, 'add_password_from_forgot']);
Route::post('login', [RegisterController::class, 'login']);
Route::post('add_city', [ProfileController::class, 'add_city']);
Route::post('get_city', [ProfileController::class, 'get_city']);


Route::group(['middleware' => ['auth:api']], function () {

    Route::post('add_device_id', [RegisterController::class, 'add_device_id']);

    Route::post('search_user', [SearchUserController::class,'search_user']);
    Route::post('logout', [RegisterController::class, 'logout']);
    Route::get('auth_user_info', [ProfileController::class, 'auth_user_info']);
Route::post('user_update_profile_photo', [ProfileController::class, 'user_update_profile_photo']);
Route::post('update_profile', [ProfileController::class, 'update_profile']);
Route::post('update_password', [ProfileController::class, 'update_password']);
Route::post('update_email_send_code', [ProfileController::class, 'update_email_send_code']);
Route::post('validation_update_email_send_code', [ProfileController::class, 'validation_update_email_send_code']);
Route::post('single_page_user', [ProfileController::class, 'single_page_user']);
Route::post('validation_password_from_email', [ProfileController::class, 'validation_password_from_email']);
Route::post('update_lk_info', [ProfileController::class, 'update_lk_info']);


Route::post('add_new_follow_or_delete_follow_request_and_follow', [FollowController::class,'add_new_follow_or_delete_follow_request_and_follow']);
Route::post('get_followers', [FollowController::class,'get_followers']);
Route::post('get_follower', [FollowController::class,'get_follower']);
Route::post('delete_other_people_in_my_followers', [FollowController::class,'delete_other_people_in_my_followers']);

Route::post('view_post_count', [PostController::class, 'view_post_count']);
Route::post('add_new_post', [PostController::class ,'add_new_post']);
Route::post('single_page_post', [PostController::class ,'single_page_post']);
Route::get('lents', [PostController::class ,'lents']);
Route::post('edit_post', [PostController::class,'edit_post']);
Route::post('delete_post', [PostController::class,'delete_post']);
Route::post('get_all_post_auth_user_or_other_user', [PostController::class,'get_all_post_auth_user_or_other_user']);

Route::post('post_like', [PostLikeController::class, 'post_like']);
Route::post('get_user_liked_post', [PostLikeController::class, 'get_user_liked_post']);
Route::post('comment_like', [CommentLikeController::class, 'comment_like']);

Route::post('add_comment' , [CommentController::class , 'add_comment']);
Route::post('get_post_comment' , [CommentController::class , 'get_post_comment']);
Route::post('delete_comment' , [CommentController::class , 'delete_comment']);

Route::post('add_user_in_black_list', [BlackListController::class, 'add_user_in_black_list']);
Route::get('get_my_black_list_users', [BlackListController::class, 'get_my_black_list_users']);

Route::post('new_message', [ChatController::class, 'new_message']);
Route::post('get_my_chat_rooms', [ChatController::class, 'get_my_chat_rooms']);
Route::post('single_page_chat', [ChatController::class, 'single_page_chat']);
Route::post('delete_chat', [ChatController::class, 'delete_chat']);

Route::get('my_notification', [NotificationController::class, 'my_notification']);
Route::post('add_post_in_book', [BookController::class, 'add_post_in_book']);
Route::get('get_my_books', [BookController::class, 'get_my_books']);
});
