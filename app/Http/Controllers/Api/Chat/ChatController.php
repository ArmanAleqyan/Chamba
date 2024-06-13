<?php

namespace App\Http\Controllers\Api\Chat;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Validator;
use App\Models\ChatRoom;
use App\Models\Chat;
use App\Events\NewMessage;

use App\Notifications\MyCustomNotification;
use Illuminate\Support\Facades\Notification;
use App\Models\UserDevice;

class ChatController extends Controller
{

    /**
     * @OA\Post(
     *     path="api/delete_chat",
     *     operationId="deleteChat",
     *     tags={"Chat"},
     *     summary="Delete chat messages with a user",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="receiver_id", type="integer", example=2),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Chat messages deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Messages Deleted"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation errors or missing receiver_id",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="object", example={"receiver_id": {"The receiver_id field is required."}}),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Wrong receiver_id provided or no chat room found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Wrong receiver_id or no chat room found"),
     *         ),
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     */

    public function delete_chat (Request $request){
        $rules=array(
            'receiver_id' =>  'required'
        );
        $validator=Validator::make($request->all(),$rules);
        if($validator->fails())
        {
            return response()->json([
                'status' => false,
                'message' =>$validator->errors()
            ],400);
        }



        $get_room = ChatRoom::
        where('sender_id', auth()->user()->id)
            ->where('receiver_id', $request->receiver_id)
            ->orwhere('receiver_id', auth()->user()->id)
            ->where('sender_id', $request->receiver_id)
            ->first();

        if ($get_room == null){
            return response()->json([
               'status' => false,
               'message' => 'Wrong receiver_id'
            ],422);
        }

        Chat::where('room_id', $get_room->id)->delete();
        $get_room->delete();
        $message = [
            'type' => 'delete_chat',
            'sender_id' => auth()->user()->id,
            'receiver_id' => $request->receiver_id
        ];

        event(new NewMessage($message));



        return response()->json([
           'status' => true,
           'message' => 'Messages Deleted'
        ],200);

    }


    /**
     * @OA\Post(
     *     path="/api/new_message",
     *     tags={"Chat"},
     *     summary="Send a new message",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Hello, how are you?"),
     *             @OA\Property(property="receiver_id", type="integer", example=2),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success - Message created",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="message created")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request - Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="object", example={"message": {"The message field is required."}})
     *         )
     *     )
     * )
     */
    public function new_message(Request $request){

        $rules=array(
            'message' => 'required',
            'receiver_id' =>  'required'
        );
        $validator=Validator::make($request->all(),$rules);
        if($validator->fails())
        {
            return response()->json([
                'status' => false,
                'message' =>$validator->errors()
            ],400);
        }


        $get_room = ChatRoom::
                where('sender_id', auth()->user()->id)
            ->where('receiver_id', $request->receiver_id)
            ->orwhere('receiver_id', auth()->user()->id)
            ->where('sender_id', $request->receiver_id)
            ->first();
        if ($get_room == null){
            $create_room = ChatRoom::create([
               'sender_id' => auth()->user()->id,
               'receiver_id' => $request->receiver_id
            ]);
        $room_id = $create_room->id;
        }else{
            $room_id = $get_room->id;
        }
        Chat::create([
           'sender_id' => auth()->user()->id,
           'receiver_id' => $request->receiver_id,
           'message' => $request->message,
            'room_id' => $room_id
        ]);



    $count =    Chat::where('receiver_id',$request->receiver_id)->where('status', 1)->count();
    $thiscount  = Chat::where('sender_id', auth()->user()->id)->where('receiver_id', $request->receiver_id)->where('status', 1)->count();
        $message = [
            'type' => 'new_message',
            'all_message_count' => $count,
            'sender_id' =>  $request->receiver_id,
            'receiver_id' => auth()->user()->id,
            'room_id' => $room_id,
             'message' => $request->message,
            'message_sum' => $thiscount,
            'status' =>1,
            'latest_sender' => auth()->user()->id,
            "created_at" => Carbon::now(),
            "updated_at"=>Carbon::now(),
             'sender' => auth()->user(),
        ];
        event(new NewMessage($message));



        $get_device = UserDevice::where('user_id', $request->receiver_id)->get('device_id')->pluck('device_id')->toarray();
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
                    'title' => 'Новое сообщение',
                    'body' => $request->message
                ],
                'data' => [
                    'type' => 'message',
                    'receiver_id' => auth()->user()->id ,
                    'photo' => \auth()->user()->avatar,
                    'name' => \auth()->user()->name
                ],
            ];
            $response = Http::withHeaders($headers)->post($url, $data);
        }




        return response()->json([
           'status' => true,
           'message' =>  'message created',
            'sender_id' => auth()->user()->id,
            'receiver_id' => $request->receiver_id
        ],200);
    }
    /**
     * @OA\Post(
     *     path="/api/get_my_chat_rooms",
     *     tags={"Chat"},
     *     summary="Get chat rooms of the authenticated user",
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search string for filtering chat rooms by sender/receiver names",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success - Chat rooms retrieved",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="data")
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

    public function get_my_chat_rooms(Request $request){

        $user_id = auth()->user()->id;
        $string = $request->search;
        $withoutAtSymbol = ltrim($string, '@');
        if (isset($withoutAtSymbol)) {
            $keyword = $withoutAtSymbol;
            $name_parts = explode(" ", $keyword);
            $get_rooms = ChatRoom::when($user_id, function ($query) use ($user_id) {
                return $query->where(function ($query) use ($user_id) {
                    $query->where('chat_rooms.receiver_id', $user_id)
                        ->orWhere('chat_rooms.sender_id', $user_id);
                })
                    ->whereRaw("IF(chat_rooms.receiver_id = $user_id, chat_rooms.sender_id, chat_rooms.receiver_id) != $user_id")
                    ->selectRaw("*, IF(chat_rooms.sender_id = $user_id, chat_rooms.receiver_id, chat_rooms.sender_id) as sender_id, IF(chat_rooms.sender_id = $user_id, chat_rooms.sender_id, chat_rooms.receiver_id) as receiver_id");
            })
                ->leftJoin('chats', function ($join) {
                    $join->on('chat_rooms.id', '=', 'chats.room_id')
                        ->whereRaw('chats.created_at = (select max(created_at) from chats where chats.room_id = chat_rooms.id)');
                })
                ->orderBy(DB::raw('coalesce(chats.created_at, chat_rooms.created_at)'), 'desc')
                ->withCount(['message_sum as message_sum' => function ($query) use ($user_id) {
                    $query->where('status', 1)
                        ->where('receiver_id', $user_id);
                }])
                ->with('sender')
                ->where(function ($query) use ($name_parts) {
                    foreach ($name_parts as $part) {
                        $query->orWhere(function ($query) use ($part) {
                            $query->whereHas('sender', function ($q) use ($part) {
                                $q->where('name', 'like', "%{$part}%")
                                    ->orWhere('nickname', 'like', "%{$part}%");
                            });
                            $query->orWhereHas('receiver', function ($q) use ($part) {
                                $q->where('name', 'like', "%{$part}%")
                                    ->orWhere('nickname', 'like', "%{$part}%");
                            });
                        });
                    }
                })
                ->paginate(20);


            $latestSenderSubquery = Chat::select('sender_id')
                ->whereIn('room_id', $get_rooms->pluck('room_id'))
                ->latest('id')
                ->distinct('room_id');

            foreach ($get_rooms as $rt) {
                // Get the latest sender_id from the subquery
                $latestSender = $latestSenderSubquery->where('room_id', $rt->room_id)->value('sender_id');
                $rt['latest_sender'] = $latestSender;
            }
        }
        return response()->json([
           'status' => true,
           'data' => $get_rooms
        ],200);
    }


    /**
     * @OA\Post(
     *     path="/api/single_page_chat",
     *     tags={"Chat"},
     *     summary="Get chat messages for a specific chat room",
     *     @OA\Parameter(
     *         name="receiver_id",
     *         in="query",
     *         description="User ID of the chat receiver",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success - Chat messages retrieved",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="data")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request - Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="object", example={"receiver_id": "The receiver_id field is required."})
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - User is not authenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found - Chat room not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Chat room not found.")
     *         )
     *     )
     * )
     */


    public function single_page_chat(Request $request){
        $rules=array(
            'receiver_id' =>  'required'
        );
        $validator=Validator::make($request->all(),$rules);
        if($validator->fails())
        {
            return response()->json([
                'status' => false,
                'message' =>$validator->errors()
            ],400);
        }
        $get_room = ChatRoom::
        where('sender_id', auth()->user()->id)
            ->where('receiver_id', $request->receiver_id)
            ->orwhere('receiver_id', auth()->user()->id)
            ->where('sender_id', $request->receiver_id)

            ->first();

        $get_black_list =
            \App\Models\BlackList::where('sender_id' , auth()->user()->id)->where('receiver_id' , $request->receiver_id)
                                 ->orwhere('sender_id' ,$request->receiver_id)->where( 'receiver_id' , auth()->user()->id)
            ->first();



        if ($get_black_list == null){
            $message = "No Black List moment";
        }else{
            if ($get_black_list->sender_id == auth()->user()->id){
                $message = 'You Blocked This User';
            }else{
                $message = 'This User Blocked You';
            }
        }

        if ($get_room == null){
            return response()->json([
               'status' => true,
               'data' => [],
                'receiver_user' => User::where('id', $request->receiver_id)->first()
            ],200);
        }else{

            $get = Chat::where('room_id', $get_room->id)->with('sender', 'receiver')->orderBy('id', 'desc')->simplepaginate(20);

            Chat::wherein('id', $get->pluck('id')->toarray())->where('receiver_id', auth()->user()->id)->update([
               'status' => 0
            ]);

            $gets = Chat::where('room_id', $get_room->id)->orderBy('id', 'desc')->simplepaginate(20);

            return response()->json([
               'status' => true,
                'receiver_user' => User::where('id', $request->receiver_id)->first(),
               'data' => $gets,
               'black_list_status' => $message
            ],200);
        }
    }
}
