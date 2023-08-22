<?php

namespace App\Http\Controllers\Api\Follow;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;
use Validator;
use App\Models\Follow;
use App\Models\Notification;
use App\Models\UserDevice;

class FollowController extends Controller
{

    /**
     * @OA\Post(
     *     path="/api/delete_other_people_in_my_followers",
     *     tags={"Follow"},
     *     summary="Delete other people in my followers",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         description="User ID to delete from followers",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success - User deleted from followers",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User deleted")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="object", example="Validation errors")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */
    public function delete_other_people_in_my_followers(Request $request){
        $rules=array(
            'user_id' => 'required',
        );
        $validator=Validator::make($request->all(),$rules);
        if($validator->fails())
        {
            return response()->json([
                'status' => false,
                'message' =>$validator->errors()
            ],400);
        }

            Follow::where('sender_id', $request->user_id)->where('receiver_id', auth()->user()->id)->delete();


            return response()->json([
                'status' => true,
                'message' =>  'User deleted'
            ],200);
    }

    /**
     * @OA\Post(
     *     path="/api/get_follower",
     *     tags={"Follow"},
     *     summary="Get Follower",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search keyword for followers",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         description="User ID to get followers for (optional)",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success - Get list of followers",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="data", type="object", ref="follow data"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */

    public function get_follower(Request $request){
        $get =   Follow::query();

        $string = $request->search;
        $withoutAtSymbol = ltrim($string, '@');
        if(isset($withoutAtSymbol) && isset($request->search)){
            $keyword =$withoutAtSymbol;
            $name_parts = explode(" ", $keyword);
            foreach ($name_parts as $part) {
                $get->orWhere(function ($query) use ($part) {
                    $query->whereRelation('follower','name', 'like', "%{$part}%")
                        ->orwhereRelation('follower','nickname', 'like', "%{$part}%")
                    ;
                });
            }
        }
        if (isset($request->user_id)){

            $get->wheresender_id($request->user_id)->paginate(10);
        }else{
            $get->wheresender_id(auth()->user()->id)->paginate(10);

        }
        $gets = $get->with('follower')->paginate(10);
        return response()->json([
            'status' => true,
            'data' => $gets,
        ],200);
    }

    /**
     * @OA\Post(
     *     path="/api/get_followers",
     *     tags={"Follow"},
     *     summary="Get Followers",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search keyword for followers",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         description="User ID to get followers for (optional)",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success - Get list of followers",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="data", type="object", ref="followers data"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function get_followers(Request $request){
      $get =   Follow::query();

        $string = $request->search;
        $withoutAtSymbol = ltrim($string, '@');
        if(isset($withoutAtSymbol) && isset($request->search)){
            $keyword =$withoutAtSymbol;
            $name_parts = explode(" ", $keyword);
            foreach ($name_parts as $part) {
                $get->orWhere(function ($query) use ($part) {
                    $query->whereRelation('followers','name', 'like', "%{$part}%")
                        ->orwhereRelation('followers','nickname', 'like', "%{$part}%")
                    ;
                });
            }
        }
        if (isset($request->user_id)){

            $get->wherereceiver_id($request->user_id)->paginate(10);
        }else{
            $get->wherereceiver_id(auth()->user()->id)->paginate(10);

        }
        $gets = $get->with('followers')->paginate(10);
        return response()->json([
           'status' => true,
           'data' => $gets,
        ],200);
    }


    /**
     * @OA\Post(
     *     path="/api/add_new_follow_or_delete_follow_request_and_follow",
     *     tags={"Follow"},
     *     summary="Add or Delete Follow Request and Follow User",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="user_id", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function add_new_follow_or_delete_follow_request_and_follow(Request $request){
        $rules=array(
            'user_id' => 'required',
        );
        $validator=Validator::make($request->all(),$rules);
        if($validator->fails())
        {
            return response()->json([
                'status' => false,
                'message' =>$validator->errors()
            ],400);
        }
        $get_user = User::where('id', $request->user_id)->first();
        if (auth()->user()->id == $request->user_id || $get_user == null){
            return response()->json([
                'status' => false,
                'message' => 'Wrong user_id'
            ]);
        }

        $get = Follow::where([
            'sender_id' => auth()->user()->id,
            'receiver_id' => $request->user_id,
        ])->first();



        if ($get == null){
            if ($get_user->open_or_close == 'open'){
                $status = 'approved';
            }else{
                $status = 'pending';
            }

            Follow::create([
                'sender_id' => auth()->user()->id,
                'receiver_id' => $request->user_id,
                'status' =>  $status
            ]);


            Notification::create([
                'sender_id' => auth()->user()->id,
                'receiver_id' => $request->user_id,
                'description' => 'подписался(ась) на вас',
                'parent_type' => '\App\Models\User',
                'parent_id' => $request->user_id
            ]);

            $get_device = UserDevice::where('user_id', $request->user_id)->get('device_id')->pluck('device_id')->toarray();
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
                        'title' => 'Подписка',
                        'body' => 'Подписался(ась) на вас'
                    ],
                    'data' => [
                        'type' => 'Follow',
                        'photo' => \auth()->user()->avatar,
                        'name' => \auth()->user()->name
                    ],
                ];
                $response = Http::withHeaders($headers)->post($url, $data);
            }

            return response()->json([
                'status' => true,
                'message' => 'follow sending'
            ],200);
        }else{
            Notification::where('sender_id', auth()->user()->id)->where('receiver_id', $request->user_id)->where('parent_type','\App\Models\User')->delete();

            $get->delete();

            return response()->json([
                'status' => true,
                'message' => 'follow deleted'
            ],200);
        }
    }
}
