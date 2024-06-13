<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AdminLoginRequest;
use App\Http\Requests\AdminUpdatePasswordRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

class AdminLoginController extends Controller
{
    public function pragladka(Request  $request){


        $apiKey = env('chat_gpt_token');
        $temperature = 0.5;
        $top_p = 0.9;
        if (isset($request->temperature)){
            $temperature = $request->temperature;
        }
        if (isset($request->top_p)){
            $top_p = $request->top_p;
        }



        $url = 'https://api.openai.com/v1/chat/completions';
        if (isset($request->message_one)){
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $apiKey,
            ])->post($url, [
                "model" => "gpt-4-1106-preview",
                'messages' => [
                    ['role' => 'user', 'content' => $request->message_one],
                ],
                'temperature' => $temperature,
                'top_p' => $top_p,
            ])->json();

            $message_one = $response['choices'][0]['message']['content']??null;



            return response()->json([
                'status' => true,
                'message_one' => $message_one,
            ]);
        }


        if (isset($request->message_two)){
            $response_two = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $apiKey,
            ])->post($url, [
                "model" => "gpt-4-1106-preview",
                'messages' => [
                    ['role' => 'assistant', 'content' => $request->one_response],
                    ['role' => 'user', 'content' => $request->message_two],
                ],
                'temperature' => $temperature,
                'top_p' => $top_p,
            ])->json();

            $message_two = $response_two['choices'][0]['message']['content'];
            return response()->json([
                'status' => true,
                'message_two' => $message_two,
            ]);
        }

    }
    public function login(){
        return view('admin.login');
    }

    public function logined(Request $request){

        $user = User::where('email', '=' , $request->email)->get();
        if($user->isEmpty()){
            return redirect()->route('login')->with('login', 'неверный логин');
        }
        if(!$user->isEmpty()){
            $user_dataa = $request->only(['email', 'password']);
            if (Auth::attempt($user_dataa)){
                if(Auth()->user()->role_id !=1){
                    auth()->logout();
                    return view('admin.login');
                }
                return redirect()->route('HomePage');
            }else{
                return redirect()->route('login')->with('password', 'неверный пароль');
            }
        }
    }

    public function HomePage(){
        return view('admin.home');
    }

    public function logoutAdmin(){
  auth()->logout();

  return redirect()->route('login');
    }


    public function settingView(){
        return view('admin.AdminSettings');
    }

    public function updatePassword(AdminUpdatePasswordRequest $request){


        $user = User::where('id', auth()->user()->id)->first();
        $hash_check =  Hash::check($request->oldpassword, $user->password);


        if($hash_check == true){
            $updated_password =  User::where('id', auth()->user()->id)->update([
                'password' =>  Hash::make($request->newpassword)
            ]);
            return redirect()->back()->with('succses','succses');
        }else{
            return redirect()->back()->with('nopassword','nopassword');
        }


    }
}
