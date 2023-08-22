<?php

namespace App\Http\Controllers\Api\Like;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\CommentLike;
class CommentLikeController extends Controller
{

    /**
     * @OA\Post(
     *     path="/api/comment_like",
     *     tags={"Comment"},
     *     summary="Like or unlike a comment",
     *     @OA\Parameter(
     *         name="comment_id",
     *         in="query",
     *         description="The ID of the comment to like or unlike",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64", example=123)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success - Like added or deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Like added")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request - Invalid input data",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="object", example={"comment_id": "The comment_id field is required."})
     *         )
     *     )
     * )
     */
    public function comment_like(Request $request){
        $rules=array(
            'comment_id' => 'required',
        );
        $validator=Validator::make($request->all(),$rules);
        if($validator->fails())
        {
            return response()->json([
                'status' => false,
                'message' =>$validator->errors()
            ],400);
        }

        $get = CommentLike::where('comment_id', $request->comment_id)->whereuser_id(auth()->user()->id)->first();
        if ($get == null){
            CommentLike::create([
               'comment_id' => $request->comment_id,
               'user_id' => auth()->user()->id
            ]);
            return response()->json([
               'status' => true,
                'message' => 'Like added'
            ],200);
        }else{
            $get->delete();
            return response()->json([
               'status' => true,
               'message' => 'Like Deleted'
            ],200);
        }
    }
}
