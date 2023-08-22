<?php

namespace App\Http\Controllers\Api\Comment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Notification;
class CommentController extends Controller
{

    /**
     * @OA\Post(
     *     path="/api/get_post_comment",
     *     tags={"Comment"},
     *     summary="Get comments for a specific post",
     *     @OA\Parameter(
     *         name="post_id",
     *         in="query",
     *         description="The ID of the post to get comments for",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64", example=123)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success - Comments fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="data", type="object", ref="data")
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

    public function get_post_comment(Request $request){
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

        $get = Comment::withNestedReplies()->where('parent_id', null)
            ->wherenotin('user_id', $get_black_list)

            ->where('post_id', $request->post_id)
            ->simplePaginate(10);



        return response()->json([
           'status' => true,
           'data' => $get
        ],200);
    }

    /**
     * @OA\Post(
     *     path="/api/add_comment",
     *     tags={"Comment"},
     *     summary="Add a new comment to a post",
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="comment", type="string", example="This is a comment."),
     *             @OA\Property(property="parent_id", type="integer", example=0),
     *             @OA\Property(property="post_id", type="integer", example=123)
     *         ),
     *         required=true,
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success - Comment added successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="comment added")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request - Invalid input data",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="object", example={"comment": "The comment field is required.", "post_id": "The post_id field is required."})
     *         )
     *     )
     * )
     */
    public function add_comment(Request $request){
        $rules=array(
            'comment' => 'required',
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

        Comment::create([
            'comment' => $request->comment,
            'parent_id' => $request->parent_id,
            'user_id' => auth()->user()->id,
            'post_id' => $request->post_id
        ]);

        $get_user = Post::where('id', $request->post_id)->first();

        if (auth()->user()->id !=  $get_user->user_id ){
            Notification::create([
                'sender_id' => auth()->user()->id,
                'receiver_id' => $get_user->user_id,
                'description' => 'Оставил комментарий к вашей публикации',
                'parent_type' => '\App\Models\Post',
                'parent_id' => $request->post_id
            ]);
        }


        return response()->json([
           'status' => true,
           'message' => 'comment added'
        ],200);

    }
}
