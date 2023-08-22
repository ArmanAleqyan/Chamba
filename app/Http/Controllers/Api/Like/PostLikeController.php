<?php

namespace App\Http\Controllers\Api\Like;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Validator;
use App\Models\PostLike;
use App\Models\Notification;
use App\Models\Post;
use App\Models\UserDevice;
class PostLikeController extends Controller
{

    /**
     * @OA\Post(
     *     path="/api/post_like",
     *     tags={"Like"},
     *     summary="Like or unlike a post",
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="post_id", type="integer", example=123)
     *         ),
     *         required=true,
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success - Post liked or unliked successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="like added or deleted")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request - Invalid input data",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="object", example={"post_id": "The post_id field is required."})
     *         )
     *     )
     * )
     */

    public function post_like(Request  $request){
        $rules=array(
            'post_id' => 'required',
        );
        $validator=Validator::make($request->all(),$rules);
        if($validator->fails())
        {
            return response()->json([
                'status' => false,
                'message' =>$validator->errors()
            ],400);
        }
        $get_post = Post::where('id', $request->post_id)->first();
        $get = PostLike::where('user_id', auth()->user()->id)->wherepost_id($request->post_id)->first();
        if ($get == null){

            if (auth()->user()->id != $get_post->user_id){
                Notification::create([
                    'sender_id' => auth()->user()->id,
                    'receiver_id' => $get_post->user_id,
                    'description' => 'поставил(а) нравится вашей публикации',
                    'parent_type' => '\App\Models\Post',
                    'parent_id' => $request->post_id
                ]);

            }


            PostLike::create([
               'post_id' => $request->post_id,
               'user_id' => auth()->user()->id
            ]);


            $get_device = UserDevice::where('user_id', $get_post->user_id)->get('device_id')->pluck('device_id')->toarray();
            if (isset($get_device )){
                $deviceToken = $get_device;
                $serverKey =  env('FCM_SERVER_KEY');
                $url = 'https://fcm.googleapis.com/fcm/send';
                $headers = [
                    'Authorization' => 'key=' . $serverKey,
                    'Content-Type' => 'application/json',
                ];
                $data = [
                    'registration_ids' => $deviceToken,
                    'notification' => [
                        'title' => 'Отметка нравитса',
                        'body' => 'Нравитса ваша публикация'
                    ],
                    'data' => [
                        'type' => 'like post',
                        'post_id' => $request->post_id,
                        'receiver_id' => auth()->user()->id ,
                        'photo' => \auth()->user()->avatar,
                        'name' => \auth()->user()->name
                    ],
                ];
                $response = Http::withHeaders($headers)->post($url, $data);
            }


            return response()->json([
               'status' => true,
               'message' => 'like added'
            ],200);
        }else{
            Notification::where('sender_id', auth()->user()->id)->where('receiver_id', $get_post->user_id)->where('parent_type', '\App\Models\Post')->where('parent_id', $request->post_id)->delete();
            $get->delete();
            return response()->json([
               'status' => true,
               'message' => 'like deleted'
            ],200);
        }
    }

    /**
     * @OA\Post(
     *     path="api/get_user_liked_post",
     *     summary="Get users who liked a specific post",
     *     tags={"Like"},
     *     @OA\Parameter(
     *         name="post_id",
     *         in="query",
     *         description="ID of the post to get the liked users.",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Success response",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"status", "data"},
     *                 @OA\Property(
     *                     property="status",
     *                     type="boolean",
     *                     description="Indicates if the request was successful.",
     *                     example=true
     *                 ),
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(
     *                         required={"id", "user_id", "created_at", "updated_at", "user"},
     *                         @OA\Property(property="id", type="integer", description="Post like ID"),
     *                         @OA\Property(property="user_id", type="integer", description="User ID who liked the post"),
     *                         @OA\Property(property="created_at", type="string", description="Creation timestamp"),
     *                         @OA\Property(property="updated_at", type="string", description="Update timestamp"),
     *                         @OA\Property(
     *                             property="user",
     *                             type="object",
     *                             description="User who liked the post",
     *                             @OA\Property(property="id", type="integer", description="User ID"),
     *                             @OA\Property(property="name", type="string", description="User name"),
     *                             @OA\Property(property="email", type="string", description="User email"),
     *                             @OA\Property(property="created_at", type="string", description="User creation timestamp"),
     *                             @OA\Property(property="updated_at", type="string", description="User update timestamp")
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Bad Request",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"status", "message"},
     *                 @OA\Property(
     *                     property="status",
     *                     type="boolean",
     *                     description="Indicates if the request was successful.",
     *                     example=false
     *                 ),
     *                 @OA\Property(
     *                     property="message",
     *                     type="object",
     *                     description="Validation error message.",
     *                     @OA\Property(
     *                         property="post_id",
     *                         type="array",
     *                         @OA\Items(type="string", example="The post_id field is required.")
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */

    public function get_user_liked_post(Request $request){
        $rules=array(
            'post_id' => 'required',
        );
        $validator=Validator::make($request->all(),$rules);
        if($validator->fails())
        {
            return response()->json([
                'status' => false,
                'message' =>$validator->errors()
            ],400);
        }

        $get_black_list = \App\Models\BlackList::where('sender_id', auth()->user()->id)->get('receiver_id')->pluck('receiver_id')->toarray();


        $get = PostLike::where('post_id', $request->post_id)->wherenotin('user_id', $get_black_list)->orderby('id','desc')->with('user')->simplepaginate(10);


        return response()->json([
           'status' => true,
           'data' => $get
        ],200);
    }
}
