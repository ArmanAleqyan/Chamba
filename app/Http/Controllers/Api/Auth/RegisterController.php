<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use mysql_xdevapi\Exception;
use Validator;
use App\Mail\RegisterMail;
use App\Models\UserDevice;

class RegisterController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/add_device_id",
     *     summary="Add or update user device information",
     *     tags={"Device"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"device_id", "phone_code"},
     *                 @OA\Property(
     *                     property="device_id",
     *                     type="string",
     *                     description="The device ID of the user's device.",
     *                     example="abc123"
     *                 ),
     *                 @OA\Property(
     *                     property="phone_code",
     *                     type="string",
     *                     description="The phone code of the user's device.",
     *                     example="123456"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Success response",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"status", "message"},
     *                 @OA\Property(
     *                     property="status",
     *                     type="boolean",
     *                     description="Indicates if the request was successful.",
     *                     example=true
     *                 ),
     *                 @OA\Property(
     *                     property="message",
     *                     type="string",
     *                     description="A message indicating the result of the operation.",
     *                     example="Created"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Bad Request",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"status", "message"},
     *                 @OA\Property(
     *                     property="status",
     *                     type="boolean",
     *                     description="Indicates if the request was successful.",
     *                     example=false
     *                 ),
     *                 @OA\Property(
     *                     property="message",
     *                     type="object",
     *                     description="The error messages related to validation failures.",
     *                     example={"device_id": {"The device_id field is required."}, "phone_code": {"The phone_code field is required."}}
     *                 )
     *             )
     *         )
     *     )
     * )
     */

    public function add_device_id(Request $request){
        $rules=array(
            'device_id' => 'required',
            'phone_code' => 'required'
        );
        $validator=Validator::make($request->all(),$rules);
        if($validator->fails())
        {
            return response()->json([
                'status' => false,
                'message' =>$validator->errors()
            ],400);
        }

        UserDevice::updateOrCreate(['user_id' => \auth()->user()->id, 'phone_id' => $request->phone_id],[
           'user_id' => \auth()->user()->id,
           'device_id' => $request->device_id,
           'phone_id' => $request->phone_id,
        ]);


        return response()->json([
           'status' => true,
           'message' => 'Created'
        ],200);





    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     tags={"Auth"},
     *     summary="Logout the user and revoke access tokens",
     *     security={{ "Bearer": {} }},
     *     @OA\Response(
     *         response=200,
     *         description="Success - User has been logged out and access tokens revoked",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Logged out")
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


    public function logout(Request $request){



        $user = \auth()->user();
        $user->tokens()->delete();



        return response()->json([
           'status' => true,
           'message' => 'Logouted'
        ],200);

    }


    /**
     * @OA\Post(
     *     path="/api/login",
     *     tags={"Auth"},
     *     summary="User Login",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string"),
     *             @OA\Property(property="password", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="user", example="user data"),
     *             @OA\Property(property="token", type="string")
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
     *         response=422,
     *         description="Unprocessable Entity",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */

    public function login (Request $request){
        $rules=array(
            'email' => 'required',
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


        $credentials = [
            'email' => $request->email,
            'password' => $request->password
        ];

        $check = Auth::attempt($credentials);


        if($check == false || auth()->user()->email_verify_code != 1){
            return response()->json([
               'status' => false,
               'message' => 'wrong email or password'
            ],422);
        }

        if (\auth()->user()->black_list_status == 1){
            return response()->json([
                'status' => false,
                'message' => 'Your account is blocked'
            ],422);
        }

        $token = auth()->user()->createToken('VerificationToken')->accessToken;

        return response()->json([
           'status' => true,
           'user' => \auth()->user(),
           'token' => $token
        ]);

    }

    /**
     * Register a new user.
     *
     * @OA\Post(
     *     path="/api/register",
     *     summary="Register a new user",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Request body for registering a new user",
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", description="User's name"),
     *             @OA\Property(property="email", type="string", format="email", description="User's email address"),
     *             @OA\Property(property="nickname", type="string", description="User's nickname"),
     *             @OA\Property(property="password", type="string", format="password", description="User's password"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", description="Confirmation of user's password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User created"),
     *             @OA\Property(property="code", type="integer", example=12345)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable Entity",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Sorry, I have an email error")
     *         )
     *     )
     * )
     */

    public function register(Request $request){
        $rules=array(
            'name' => 'required|max:255',
            'email' => [
                'required',
                'email',
                'max:254',
                Rule::unique('users')->where(function ($query) {
                    return $query->where('email_verify_code', 1);
                }),
            ],
            'nickname' => [
                'required',
                'max:254',
                Rule::unique('users')->where(function ($query) {
                    return $query->where('email_verify_code', 1);
                }),
            ],
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


        $code = random_int(10000, 99999);
        $details = [
            'code' => $code,
            'name' => $request->name

        ];

        try{
            Mail::to($request->email)->send(new RegisterMail($details));
        }catch (Exception $e){
            return response()->json([
                'status' => false,
                'message' => 'Sorry I Have Email error'
            ],422);
        }


        User::updateorcreate(['email' => $request->email],[
            'email' => $request->email,
            'email_verify_code' => $code,
            'nickname' => $request->nickname,
            'name' => $request->name,
            'password' => Hash::make($request->password) ,
        ]);


        return response()->json([
           'status' => true,
           'message' => 'User created',
            'code' => $code
        ],200);
    }

    /**
     * Confirm user registration.
     *
     * @OA\Post(
     *     path="/api/confirm_register",
     *     summary="Confirm user registration",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Request body for confirming user registration",
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", format="email", description="User's email address"),
     *             @OA\Property(property="code", type="string", description="Verification code received by the user")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User verified"),
     *             @OA\Property(property="user", type="object", example="user_data"),
     *             @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOi...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable Entity",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="wrong code")
     *         )
     *     )
     * )
     */

    public function confirm_register(Request $request){
        $rules=array(
            'email' => 'required',
            'code' => 'required',
        );
        $validator=Validator::make($request->all(),$rules);
        if($validator->fails())
        {
            return response()->json([
                'status' => false,
                'message' =>$validator->errors()
            ],400);
        }


        $get = User::where('email', $request->email)->whereemail_verify_code($request->code)->first();

        if ($get == null){
            return response()->json([
               'status' => false,
               'message' => 'wrong code'
            ],422);
        }


        $get->update([
            'email_verify_code' => 1
        ]);

        $token = $get->createToken('VerificationToken')->accessToken;


        return response()->json([
           'status' => true,
           'message' => 'user verified',
            'user' => $get,
            'token' => $token
        ],200);

    }
}
