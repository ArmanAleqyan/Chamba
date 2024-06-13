<?php

namespace App\Http\Controllers\Api\Notification;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use  App\Models\Notification;

class NotificationController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/my_notification",
     *     summary="Get notifications",
     *     tags={"Notifications"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="data", type="object", example="data")
     *         )
     *     )
     * )
     */
    public function my_notification(){
        $get_black_list = \App\Models\BlackList::where('sender_id', auth()->user()->id)->get('receiver_id')->pluck('receiver_id')->toarray();

        $get = Notification::with('sender')->wherenotin('sender_id',$get_black_list)->orderby('status' , 'desc')->orderby('id', 'desc')->with('basketable')->where('receiver_id',auth()->user()->id)->simplepaginate(10);

        foreach ($get as $item) {
            if ($item->parent_type == '\App\Models\Post'){
                $get_photo  = \App\Models\PostPhoto::where('post_id', $item->parent_id)->get();
                $item['photo'] =$get_photo;
            }
        }

        $get_id = Notification::with('sender')->with('basketable')->where('receiver_id',auth()->user()->id)->get('id')->pluck('id')->toarray();


        Notification::wherein('id', $get_id)->update([
           'status' => 0
        ]);


        return response()->json([
           'status' => true,
           'data' => $get
        ],200);
    }
}
