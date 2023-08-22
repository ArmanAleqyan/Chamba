<?php

namespace App\Http\Controllers\Api\Profile;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Follow;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Image as RTY;
use Validator;
use App\Mail\UpdateMail;
use App\Models\Chat;
use App\Models\City;
use App\Models\Notification;

class ProfileController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/get_city",
     *     operationId="getCity",
     *     tags={"City Management"},
     *     summary="Get list of cities",
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search keyword for city names",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of cities retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="city data")),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated"),
     *         ),
     *     ),
     * )
     */

    public function  get_city(Request $request){
        $get = City::query();

        if (isset($request->search)){
            $get->where('name', 'like', '%'.$request->search.'%');
        }


       $get =   $get->where('parent_id', '!=', null)->get();


        return response()->json([
           'status' => true,
           'data' => $get
        ],200);
    }


    public function add_city(){
        $url = 'https://gist.githubusercontent.com/gorborukov/0722a93c35dfba96337b/raw/435b297ac6d90d13a68935e1ec7a69a225969e58/russia';

        $city = Http::withHeaders([
            'Content-Type' => 'application/json',

        ])->timeout(5000000)->get($url,
            [
            ])->json();

        foreach ($city as $cityies){

                $create_region = City::updateorcreate(['name' => $cityies['region']],['name' => $cityies['region']]);

                City::updateorcreate(['parent_id' => $create_region->id, 'name' => $cityies['city']],['parent_id' => $create_region->id, 'name' => $cityies['city']]);

        }


    }
    /**
     * @OA\Post(
     *     path="/api/update_lk_info",
     *     operationId="updateLkInfo",
     *     tags={"User Management"},
     *     summary="Update user's personal information",
     *     security={{"Bearer":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="data")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User information updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="updated"),
     *             @OA\Property(property="request_data", ref="data"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated"),
     *         ),
     *     ),
     * )
     */

    public function update_lk_info(Request $request){
        auth()->user()->update([
            'city_id' => $request->city_id,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'student' => $request->student,
            'mgu' => $request->mgu,
            'web' => $request->web,
            'phone' => $request->phone,
        ]);

        return response()->json([
           'status' => true,
           'message' => 'updated',
           'request_data' => $request->all()
        ],200);
    }
    /**
     * @OA\Post(
     *     path="/api/validation_password_from_email",
     *     tags={"User"},
     *     summary="Validate Password from Email",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="password", type="string", format="password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success - Valid Password",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Valid Password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable Entity - Invalid Password",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Mo Valid password")
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

    public function validation_password_from_email(Request $request){
        $rules=array(
            'password' => 'required',
        );
        $validator=Validator::make($request->all(),$rules);
        if($validator->fails())
        {
            return response()->json([
                'status' => false,
                'message' =>$validator->errors()
            ],400);
        }

        $check = Hash::check($request->password,auth()->user()->password);


        if ($check == true){
            return response()->json([
               'status' => true,
               'message' => 'Valid Password'
            ],200);
        }else{
            return response()->json([
               'status' => false,
               'message' => 'Mo Valid password'
            ],422);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/single_page_user",
     *     tags={"Search"},
     *     summary="Get Single User by ID",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="data", ref="user data")
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


    public function single_page_user(Request $request){
        $get_user = User::where('id', $request->user_id)->with('follow_status_sender','follow_status_receiver')->where('id', '!=', auth()->user()->id)->first();

        $followers_count = Follow::where('receiver_id', $request->user_id)->count();
        $follower_count = Follow::where('sender_id', $request->user_id)->count();
        $post_count = Post::where('user_id', $request->user_id)->count();

        if ($get_user == null){
            return response()->json([
                'status' => false,
                'message' => 'wrong user_id'
            ],422);
        }
        return response()->json([
            'status' => true,
            'data' => $get_user,
            'followers_count' => $followers_count,
            'follower_count' => $follower_count,
            'post_count' => $post_count,
        ],200);

    }

    /**
     * @OA\Get(
     *     path="/api/auth_user_info",
     *     tags={"User"},
     *     summary="Get Authenticated User Info",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="data", example="user data")
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
    public function auth_user_info(){

        $user = User::where('id', auth()->user()->id)->first();

        $followers_count = Follow::where('receiver_id', auth()->user()->id)->count();
        $follower_count = Follow::where('sender_id', auth()->user()->id)->count();
        $post_count = Post::where('user_id', auth()->user()->id)->count();
        $count_chat =    Chat::where('receiver_id', auth()->user()->id)->where('status', 1)->count();
        $not_count = Notification::where('receiver_id', auth()->user()->id)->where('status',1)->count();
        return response()->json([
             'status' => true,
              'data' => $user,
            'followers_count' => $followers_count,
            'follower_count' => $follower_count,
            'post_count' => $post_count,
            'chat_count' => $count_chat,
            'notification_count' => $not_count
        ],200);
    }
    /**
     * @OA\Post(
     *     path="/api/user_update_profile_photo",
     *     tags={"User"},
     *     summary="Update User Profile Photo",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="photo", type="file")
     *             )
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
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function user_update_profile_photo(Request $request){
        if (isset($request->photo)){

            $photo = $request->photo;
            $TypeImg =$photo->getClientMimeType();
            if($TypeImg == 'image/jpeg' || $TypeImg == 'image/jpg' || $TypeImg == 'image/gif' || $TypeImg == 'image/png'  || $TypeImg == 'image/bmp') {
                $image = $photo;

                if ($image->getSize() > 1000000) {
                    if (auth()->user()->avatar != 'default.png'){
                        $path = public_path()."/uploads/".auth()->user()->avatar;
                        unlink($path);
                    }
                    $input['imagename'] = time() . '.' . $image->getClientOriginalExtension();
                    $destinationPath = public_path('/uploads');
                    $img = RTY::make($image->getRealPath());
                    $width = getimagesize($photo)[0] / 3;
                    $height = getimagesize($photo)[1] / 3;
                    $img->resize($width, $height, function ($constraint) {
                        $constraint->aspectRatio();
                    })->save($destinationPath . '/' . $input['imagename']);

                    auth()->user()->update([
                        'avatar' => $img->basename
                    ]);
                }else{
                    if (auth()->user()->avatar != 'default.png'){
                        $path = public_path()."/uploads/".auth()->user()->avatar;
                        unlink($path);
                    }
                    $destinationPath = 'uploads';
                    $originalFile =  time() . '.' . $photo->getClientOriginalExtension();
                    $photo->move($destinationPath, $originalFile);
                    $files = $originalFile;
                    auth()->user()->update([
                        'avatar' => $originalFile
                    ]);
                }
            }
            return response()->json([
                'status' => true,
                'message' => 'Avatar Updated',
                'avatar' =>$originalFile
            ],200);
        }else{
            auth()->user()->update([
               'avatar' => 'default.png'
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Avatar Updated',
                'avatar' =>'default.png'
            ],200);
        }
        return response()->json([
           'status' => true,
           'message' => 'Avatar Updated',
             'avatar' =>$originalFile
        ],200);
    }

    /**
     * @OA\Post(
     *     path="/api/update_profile",
     *     tags={"User"},
     *     summary="Update User Profile",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="nickname", type="string"),
     *             @OA\Property(property="description", type="string")
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


    public function update_profile(Request $request){
        $rules=array(
            'name' => 'required|max:255',
            'nickname' => [
                'required',
                'max:254',
                Rule::unique('users')->where(function ($query)  {
                    $query->where('email_verify_code', 1)
                        ->where('id', '!=', auth()->user()->id);
                }),
            ],
        );
        $validator=Validator::make($request->all(),$rules);
        if($validator->fails())
        {
            return response()->json([
                'status' => false,
                'message' =>$validator->errors()
            ],400);
        }
        auth()->user()->update([
           'name' => $request->name,
           'nickname' => $request->nickname,
           'description' => $request->description
        ]);
        return response()->json([
           'status' => true,
           'message' => 'user Data Updated'
        ],200);
    }

    /**
     * @OA\Post(
     *     path="/api/update_password",
     *     tags={"User"},
     *     summary="Update User Password",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="old_password", type="string"),
     *             @OA\Property(property="password", type="string"),
     *             @OA\Property(property="password_confirmation", type="string")
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

    public function update_password(Request $request){
        $rules=array(
            'old_password' => 'required',
            'password' => 'max:254|required',
            'password_confirmation' => 'required|same:password|max:254',
        );
        $validator=Validator::make($request->all(),$rules);
        if($validator->fails())
        {
            return response()->json([
                'status' => false,
                'message' =>$validator->errors()
            ],400);
        }
        $check = Hash::check($request->old_password,auth()->user()->password);
        if ($check == false){
            return response()->json([
               'status' => false,
               'message' => 'wrong old password'
            ],422);
        }
        auth()->user()->update([
           'password' => Hash::make($request->password)
        ]);
        return response()->json([
           'status' => true,
           'message' => 'Password Updated'
        ],200);
    }



    /**
     * @OA\Post(
     *     path="/api/update_email_send_code",
     *     tags={"User"},
     *     summary="Send Verification Code to Update User Email",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string")
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

    public function update_email_send_code(Request $request){
        $rules=array(
            'email' => [
                'required',
                'email',
                'max:254',
                Rule::unique('users')->where(function ($query) {
                    return $query->where('email_verify_code', 1);
                }),
            ],

        );
        $validator=Validator::make($request->all(),$rules);
        if($validator->fails())
        {
            return response()->json([
                'status' => false,
                'message' =>$validator->errors()
            ],400);
        }

        $code = random_int(10000,99999);
        $details = [
          'name' => auth()->user()->name,
          'code' => $code
        ];
        try{
            Mail::to($request->email)->send(new UpdateMail($details));
        }catch (\Exception $e){
            return response()->json([
                'status' => false,
                'message' => 'Sorry I Have Email error'
            ],422);
        }


        auth()->user()->update([
             'email_candidate'  => $request->email,
            'email_candidate_code' => $code
        ]);



        return response()->json([
           'status' => true,
           'message' => 'code sended your email'
        ],200);

    }

    /**
     * @OA\Post(
     *     path="/api/validation_update_email_send_code",
     *     tags={"User"},
     *     summary="Validate Verification Code to Update User Email",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="string")
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
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable Entity",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function validation_update_email_send_code(Request $request){
        $rules=array(
         'code' => 'required'
        );
        $validator=Validator::make($request->all(),$rules);
        if($validator->fails())
        {
            return response()->json([
                'status' => false,
                'message' =>$validator->errors()
            ],400);
        }
        if (auth()->user()->email_candidate_code != $request->code){
            return response()->json([
               'status' => false,
               'message' => 'wrong code'
            ],422);
        }
        auth()->user()->update([
           'email' => auth()->user()->email_candidate,
           'email_candidate' => null,
            'email_candidate_code' => null
        ]);
        return response()->json([
           'status' => true,
           'message' => 'email updated'
        ],200);
    }

}
