<?php

namespace App\Http\Controllers\Api\Search;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class SearchUserController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/search_user",
     *     tags={"Search"},
     *     summary="Search Users by Name or Nickname",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="search", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="data", type="object", ref="user_data")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="object")
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
    public function  search_user(Request $request){
        $get =User::query();
        $get_black_list = \App\Models\BlackList::where('sender_id', auth()->user()->id)->get('receiver_id')->pluck('receiver_id')->toarray();

        $string = $request->search;
        $withoutAtSymbol = ltrim($string, '@');
        if(isset($withoutAtSymbol)){
            $keyword =$withoutAtSymbol;
            $name_parts = explode(" ", $keyword);
            foreach ($name_parts as $part) {
                $get->orWhere(function ($query) use ($part) {
                    $query->where('name', 'like', "%{$part}%")
                        ->orwhere('nickname', 'like', "%{$part}%")
                    ;
                });
            }
        }
        $gets = $get->wherenotin('id',$get_black_list)->whereemail_verify_code(1)->with('follow_status_sender','follow_status_receiver')->where('id','!=' , auth()->user()->id)->paginate(10);
        return response()->json([
           'status' => true,
           'data' => $gets
        ],200);
    }



}
