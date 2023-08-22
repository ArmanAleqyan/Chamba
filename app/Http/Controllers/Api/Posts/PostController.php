<?php

namespace App\Http\Controllers\Api\Posts;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Validator;
use App\Models\Post;
use App\Models\PostPhoto;
use Image as RTY;
use App\Models\Follow;
class PostController extends Controller
{

    /**
     * @OA\Post(
     *     path="/api/view_post_count",
     *     tags={"Post"},
     *     summary="Increment the view count of a post",
     *     @OA\RequestBody(
     *         required=true,
     *         description="JSON object containing the post_id",
     *         @OA\JsonContent(
     *             @OA\Property(property="post_id", type="integer", example=123)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success - View count updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="View added")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request - Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="object", example={"post_id": "The post_id field is required."})
     *         )
     *     )
     * )
     */
    public function view_post_count(Request $request){
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
        $get =   Post::where('id', $request->post_id)->first();
        if ($get->user_id != auth()->user()->id){
            Post::where('id', $request->post_id)->update([
                'view_count' =>  $get->view_count  +1
            ]);
            return response()->json([
                'status' => true,
                'message' => 'View added'
            ],200);
        }else{
            return response()->json([
                'status' => true,
                'message' => 'View no added'
            ],422);
        }



    }


    /**
     * @OA\Get(
     *     path="/api/lents",
     *     tags={"Posts"},
     *     summary="Get posts from the user's followers",
     *     @OA\Response(
     *         response=200,
     *         description="Success - Returns the posts from the user's followers",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=5),
     *                 @OA\Property(property="per_page", type="integer", example=10),
     *                 @OA\Property(property="total", type="integer", example=50),
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="user_id", type="integer", example=1),
     *                         @OA\Property(property="description", type="string", example="This is a post"),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2023-07-19 12:34:56"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time", example="2023-07-19 12:34:56"),
     *                         @OA\Property(property="like_count", type="integer", example=5),
     *                         @OA\Property(property="comment_count", type="integer", example=10),
     *                         @OA\Property(property="photos", type="array",
     *                             @OA\Items(type="object",
     *                                 @OA\Property(property="id", type="integer", example=1),
     *                                 @OA\Property(property="post_id", type="integer", example=1),
     *                                 @OA\Property(property="photo", type="string", example="photo1.jpg"),
     *                                 @OA\Property(property="created_at", type="string", format="date-time", example="2023-07-19 12:34:56"),
     *                                 @OA\Property(property="updated_at", type="string", format="date-time", example="2023-07-19 12:34:56")
     *                             )
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */


    public function lents(){

        $get_black_list = \App\Models\BlackList::where('sender_id', auth()->user()->id)->get('receiver_id')->pluck('receiver_id')->toarray();

        $get_folower_id = Follow::where('sender_id', auth()->user()->id)->get('receiver_id')->pluck('receiver_id')->toarray();
        $get_follower_id = array_merge($get_folower_id, [auth()->user()->id]);
        $get_stars_user = User::where('star', 1)->get('id')->pluck('id')->toarray();
        $get_follower_id = array_merge($get_follower_id,$get_stars_user);
        $get = Post::query();
        $gets =   $get->wherenotin('user_id',$get_black_list)->wherein('user_id', $get_follower_id)->withcount('like')->withcount('comment')->with('photo','user','like_auth_user','auth_user_book')->orderby('id','desc')->simplepaginate(10);


     return response()->json([
        'status' => true,
        'data' => $gets
     ],200);
    }

    /**
     * @OA\Post(
     *     path="/api/single_page_post",
     *     tags={"Post"},
     *     summary="Get a single post by its ID",
     *     @OA\Parameter(
     *         name="post_id",
     *         in="query",
     *         description="Post ID to fetch the single post",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success - Post fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="data", ref="post")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found - Post with specified ID not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Post not found")
     *         )
     *     )
     * )
     */
    public function single_page_post(Request $request){
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



        $get = Post::where('id', $request->post_id)->with('user','auth_user_book','like_auth_user','photo')->withcount('like')->withcount('comment')->first();
        Post::where('id', $request->post_id)->update([
            'view_count' => $get->view_count +1
        ]);

        if ($get != null){
            return response()->json([
               'status' => true,
               'data' => $get
            ],200);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'wrong post id'
            ],422);
        }

    }


    /**
     * @OA\Post(
     *     path="/api/get_all_post_auth_user_or_other_user",
     *     tags={"Post"},
     *     summary="Get all posts for the authenticated user or other specified user",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         description="User ID to fetch posts for a specific user (optional)",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success - Posts fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="post"))
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

    public function get_all_post_auth_user_or_other_user(Request $request){
        $get_black_list = \App\Models\BlackList::where('sender_id', auth()->user()->id)->get('receiver_id')->pluck('receiver_id')->toarray();

        $get = Post::query();
        if (isset($request->user_id)){
            $get->where('user_id', $request->user_id);
        }else{
            $get->where('user_id',auth()->user()->id);
        }

       $get =  $get->wherenotin('user_id',$get_black_list)->withcount('like')->withcount('comment')->orderby('id','desc')->with('photo','user','auth_user_book', 'like_auth_user')->simplepaginate(10);

        return response()->json([
           'status' => true,
           'data' => $get
        ],200);
    }





    /**
     * @OA\Post(
     *     path="/api/edit_post",
     *     tags={"Post"},
     *     summary="Edit an existing post",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Post data",
     *         @OA\JsonContent(
     *             @OA\Property(property="post_id", type="integer", example=1),
     *             @OA\Property(property="description", type="string", example="Updated post description.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success - Post updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Post updated")
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
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable Entity",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Wrong Post Id")
     *         )
     *     )
     * )
     */

    public function edit_post(Request $request){
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

        $get = Post::where('id', $request->post_id)->first();


        if ($get == null || $get->user_id != auth()->user()->id){
            return response()->json([
               'status' => false,
               'message' => 'Wrong Post Id'
            ],422);
        }


        $get->update([
           'description' => $request->description
        ]);


        return response()->json([
           'status' => true,
           'message' => 'post updated'
        ],200);
     }


    /**
     * @OA\Post(
     *     path="/api/add_new_post",
     *     tags={"Post"},
     *     summary="Add a new post with photos",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Post data with photos",
     *         @OA\JsonContent(
     *             @OA\Property(property="description", type="string", example="This is a new post."),
     *             @OA\Property(property="photos", type="array", @OA\Items(type="string", format="binary"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description=" Post added",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Post added"),
     *             @OA\Property(property="post_id", type="integer", example=1)
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
    public function add_new_post(Request $request){
        $rules=array(
            'photos' => ['required', 'array', 'max:10'],
            'photos.*' => ['image', 'mimes:jpeg,png,jpg,gif' ], // Adjust the max file size as needed (in KB)
        );
        $validator=Validator::make($request->all(),$rules);
        if($validator->fails())
        {
            return response()->json([
                'status' => false,
                'message' =>$validator->errors()
            ],400);
        }
            $post = Post::create([
                'user_id' => auth()->user()->id,
                'description' => $request->description
            ]);
        $time = time();
        foreach ($request->photos as $photo){

            $TypeImg =$photo->getClientMimeType();
            if($TypeImg == 'image/jpeg' || $TypeImg == 'image/jpg' || $TypeImg == 'image/gif' || $TypeImg == 'image/png'  || $TypeImg == 'image/bmp') {
                if ($photo->getSize() > 1000000) {
                    $input['imagename'] = $time++ . '.' . $photo->getClientOriginalExtension();
                    $destinationPath = public_path('/uploads');
                    $img = RTY::make($photo->getRealPath());
                    $width = getimagesize($photo)[0] / 3;
                    $height = getimagesize($photo)[1] / 3;
                    $img->resize($width, $height, function ($constraint) {
                        $constraint->aspectRatio();
                    })->save($destinationPath . '/' . $input['imagename']);
                    PostPhoto::create([
                        'post_id' => $post->id,
                        'photo' => $input['imagename']
                    ]);
                }else{
                    $destinationPath = 'uploads';
                    $originalFile =  $time++ . '.' . $photo->getClientOriginalExtension();
                    $photo->move($destinationPath, $originalFile);
                    PostPhoto::create([
                       'post_id' => $post->id,
                        'photo' => $originalFile
                    ]);
                }
            }
        }


        return response()->json([
           'status' => true,
           'message' => 'post added',
           'post_id' => $post->id
        ],200);
    }
}
