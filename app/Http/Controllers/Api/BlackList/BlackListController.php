<?php

namespace App\Http\Controllers\Api\BlackList;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\BlackList;
use App\Events\NewMessage as ChanelBlackList;
class BlackListController extends Controller
{

    /**
     * @OA\Post(
     *     path="/api/add_user_in_black_list",
     *     tags={"BlackList"},
     *     summary="Add or remove a user from the black list",
     *     @OA\RequestBody(
     *         required=true,
     *         description="JSON object containing the user_id",
     *         @OA\JsonContent(
     *             @OA\Property(property="user_id", type="integer", example=123)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success - User added/removed from the black list",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="added in black list or deleted from black list")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request - Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="object", example={"user_id": "The user_id field is required."})
     *         )
     *     )
     * )
     */
    public function add_user_in_black_list(Request $request){
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

        $get = BlackList::where('sender_id', auth()->user()->id)->where('receiver_id', $request->user_id)->first();

        if ($get == null){
            BlackList::create([
               'sender_id' => auth()->user()->id,
               'receiver_id' => $request->user_id
            ]);

            $message = [
                'type' => 'black_list_add',
                'sender_id' => auth()->user()->id,
                'receiver_id' => $request->user_id
            ];

            event(new ChanelBlackList($message));

            return response()->json([
               'status' => true,
               'message' => 'added in black list'
            ],200);
        }else{

            $get->delete();
            $message = [
                'type' => 'black_list_delete',
                'sender_id' => auth()->user()->id,
                'receiver_id' => $request->user_id
            ];

            event(new ChanelBlackList($message));

            return response()->json([
               'status' => true,
               'message' => 'deleted in black list'
            ],200);
        }

    }
    /**
     * @OA\Get(
     *     path="/api/get_my_black_list_users",
     *     tags={"BlackList"},
     *     summary="Get the list of users in the black list",
     *     @OA\Response(
     *         response=200,
     *         description="Success - List of blacklisted users retrieved",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="current_page",
     *                     type="integer",
     *                     example=1
     *                 ),
     *                 @OA\Property(
     *                     property="last_page",
     *                     type="integer",
     *                     example=2
     *                 ),
     *                 @OA\Property(
     *                     property="per_page",
     *                     type="integer",
     *                     example=20
     *                 ),
     *                 @OA\Property(
     *                     property="total",
     *                     type="integer",
     *                     example=25
     *                 ),
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(ref="data")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - User is not authenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function get_my_black_list_users(){
        $get = BlackList::where('sender_id', auth()->user()->id)->with('receiver')->simplepaginate(20);


        return response()->json([
           'status' => true,
           'data' => $get
        ],200);
    }
}
