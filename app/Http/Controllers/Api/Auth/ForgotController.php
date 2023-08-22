<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use mysql_xdevapi\Exception;
use Validator;
use App\Mail\ForgotCode;
class ForgotController extends Controller
{

    /**
     * Send verification code for password reset.
     *
     * @OA\Post(
     *     path="/api/send_code_from_forgot_password",
     *     summary="Send verification code for password reset",
     *     tags={"Forgot"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Request body for sending verification code for password reset",
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", format="email", description="User's email address")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="code sent to your email")
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
    public function send_code_from_forgot_password(Request $request){
        $rules=array(
            'email' => [
                'required',
                'email',
                Rule::exists('users', 'email')->where(function ($query) {
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
        $get = User::where('email', $request->email)->first();

            if ($get == null){
                return response()->json([
                   'status' => false,
                   'message' => 'Wrong email'
                ],422);
            }


            if ($get->black_list_status == 1){
                return response()->json([
                   'status' => false,
                   'message' => 'user Added in black list'
                ],422);
            }

        $details = [
            'name' => $get->name,
            'code' => $code
        ];
        try{
            Mail::to($request->email)->send(new ForgotCode($details));
        }catch (Exception $e){
            return response()->json([
                'status' => false,
                'message' => 'Sorry I Have Email error'
            ],422);
        }
        $get->update([
           'password' => Hash::make($code),
           'email_forgot_code' => $code
        ]);
        return response()->json([
           'status' => true,
           'message' => 'code sendet your email'
        ],200);
    }

    /**
     * Validate forgot password code.
     *
     * @OA\Post(
     *     path="/api/validation_forgot_code",
     *     summary="Validate forgot password code",
     *     tags={"Forgot"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Request body for validating forgot password code",
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
     *             @OA\Property(property="message", type="string", example="Valid Code")
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
     *             @OA\Property(property="message", type="string", example="Wrong code")
     *         )
     *     )
     * )
     */

    public function validation_forgot_code(Request $request){
        $rules=array(
            'email' => 'required|email|exists:users,email',
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
        $get = User::where('email', $request->email)->where('email_forgot_code', $request->code)->first();
        if ($get == null){
            return response()->json([
               'status' => false,
               'message' => 'Wrong code'
            ],422);
        }
        return response()->json([
           'status' => true,
           'message' => 'Valid Code',
            'nickname' => $get->nickname,
            'email' => $request->email,
            'code' => $request->code
        ],200);
    }


    /**
     * Add new password after forgot password.
     *
     * @OA\Post(
     *     path="/api/add_password_from_forgot",
     *     summary="Add new password after forgot password",
     *     tags={"Forgot"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Request body for adding new password after forgot password",
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", format="email", description="User's email address"),
     *             @OA\Property(property="code", type="string", description="Verification code received by the user"),
     *             @OA\Property(property="password", type="string", description="New password"),
     *             @OA\Property(property="password_confirmation", type="string", description="Confirmation of new password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Password Updated"),
     *             @OA\Property(property="user", example="user data"),
     *             @OA\Property(property="token", type="string", description="Access token for the user")
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
     *             @OA\Property(property="message", type="string", example="wrong code or email")
     *         )
     *     )
     * )
     */


    public function add_password_from_forgot(Request $request){
        $rules=array(
            'email' => 'required|email|exists:users,email',
            'code' => 'required',
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

        $get = User::where('email', $request->email)->where('email_forgot_code', $request->code)->first();

        if ($get == null){
            return response()->json([
               'status' => false,
               'message' => 'wrong code or email'
            ],422);
        }

        $get ->update([
           'password' => Hash::make($request->password),
            'email_forgot_code' => 1
        ]);
        $token = $get->createToken('VerificationToken')->accessToken;

        return response()->json([
           'status' => true,
           'message' => 'Password Updated',
            'user' => $get,
            'token' => $token,
        ],200);


    }
}
