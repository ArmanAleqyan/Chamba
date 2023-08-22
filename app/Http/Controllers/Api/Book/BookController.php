<?php

namespace App\Http\Controllers\Api\Book;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\Book;
class BookController extends Controller
{

    /**
     * @OA\Post(
     *     path="/api/add_post_in_book",
     *     summary="Add or remove a post from the user's book",
     *     tags={"Books"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="post_id", type="integer", description="ID of the post to add or remove from the book")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example="true", description="Status of the response (true/false)"),
     *             @OA\Property(property="message", type="string", example="Book Added or Book Deleted", description="Response message")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example="false", description="Status of the response (true/false)"),
     *             @OA\Property(property="message", type="object", description="Validation error messages",
     *                 @OA\AdditionalProperties(type="array", @OA\Items(type="string"))
     *             )
     *         )
     *     )
     * )
     */
    public  function add_post_in_book(Request $request){
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

        $get = Book::where('user_id', auth()->user()->id)->where('post_id', $request->post_id)->first();

        if ($get == null){
            Book::create([
               'user_id' => auth()->user()->id,
                'post_id' => $request->post_id
            ]);
            return response()->json([
               'status' => true,
               'message' => 'Book Added'
            ],200);
        }else{
            $get->delete();

            return response()->json([
               'status' => true,
               'message' => 'Book Deleted'
            ],200);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/get_my_books",
     *     summary="Get user's books",
     *     tags={"Books"},
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example="true", description="Status of the response (true/false)"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="current_page", type="integer", example="1", description="Current page number"),
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(ref="data "), description="Array of user's books"
     *                 ),
     *                 @OA\Property(property="first_page_url", type="string", description="URL of the first page"),
     *                 @OA\Property(property="from", type="integer", example="1", description="Starting index of the result"),
     *                 @OA\Property(property="last_page", type="integer", example="1", description="Last page number"),
     *                 @OA\Property(property="last_page_url", type="string", description="URL of the last page"),
     *                 @OA\Property(property="next_page_url", type="string", description="URL of the next page"),
     *                 @OA\Property(property="path", type="string", description="Base path of the result"),
     *                 @OA\Property(property="per_page", type="integer", example="10", description="Number of items per page"),
     *                 @OA\Property(property="prev_page_url", type="string", description="URL of the previous page"),
     *                 @OA\Property(property="to", type="integer", example="10", description="Ending index of the result"),
     *                 @OA\Property(property="total", type="integer", example="20", description="Total number of items")
     *             )
     *         )
     *     )
     * )
     */
    public function get_my_books(){
        $get_black_list = \App\Models\BlackList::where('sender_id', auth()->user()->id)->get('receiver_id')->pluck('receiver_id')->toarray();

       $get =   Book::with('post.photo')
           ->whereNotIn('post_id', function ($query) use ($get_black_list) {
               $query->select('id')
                   ->from('posts')
                   ->wherenotIn('user_id', $get_black_list);
           })
                ->orderby('id', 'desc')
                ->where('user_id', auth()->user()->id)->simplepaginate(10);

       return response()->json([
          'status' => true,
          'data' => $get
       ],200);
    }
}
