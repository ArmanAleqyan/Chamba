<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Post;
class UsersController extends Controller
{

    public function delete_user($id){
        User::where('id', $id)->delete();
        return redirect()->route('userss');
    }

    public function users_black_list(Request $request){
        $users = User::query();

        if (isset($request->search)){
            $string = $request->search;
            $withoutAtSymbol = ltrim($string, '@');
            $keyword =$withoutAtSymbol;
            $name_parts = explode(" ", $keyword);
            foreach ($name_parts as $part) {
                $users->orWhere(function ($query) use ($part) {
                    $query->where('name', 'like', "%{$part}%")
                        ->orwhere('nickname', 'like', "%{$part}%")
                        ->orwhere('email', 'like', "%{$part}%")
                    ;
                });
            }
        }
        $count = $users->where('black_list_status',1)->count();

        $get = $users->where('black_list_status',1)->paginate(20);

        return view('admin.Users.Users', compact('get', 'count'));
    }
    public function users_star(Request $request){
        $users = User::query();

        if (isset($request->search)){
            $string = $request->search;
            $withoutAtSymbol = ltrim($string, '@');
            $keyword =$withoutAtSymbol;
            $name_parts = explode(" ", $keyword);
            foreach ($name_parts as $part) {
                $users->orWhere(function ($query) use ($part) {
                    $query->where('name', 'like', "%{$part}%")
                        ->orwhere('nickname', 'like', "%{$part}%")
                        ->orwhere('email', 'like', "%{$part}%")
                    ;
                });
            }
        }
        $count = $users->where('star',1)->count();

        $get = $users->where('star',1)->paginate(20);

        return view('admin.Users.Users', compact('get', 'count'));
    }

    public function userss(Request $request){

        $users = User::query();

        if (isset($request->search)){
            $string = $request->search;
            $withoutAtSymbol = ltrim($string, '@');
            $keyword =$withoutAtSymbol;
            $name_parts = explode(" ", $keyword);
            foreach ($name_parts as $part) {
                $users->orWhere(function ($query) use ($part) {
                    $query->where('name', 'like', "%{$part}%")
                        ->orwhere('nickname', 'like', "%{$part}%")
                        ->orwhere('email', 'like', "%{$part}%")
                    ;
                });
            }
        }
        $count = $users->where('star',0)->count();

        $get = $users->where('star',0)->paginate(20);

        return view('admin.Users.Users', compact('get', 'count'));
    }

    public function single_page_user($id){
        $get = User::where('id', $id)->first();

        if ($get == null){
            return redirect()->back();
        }
        $post = Post::where('user_id', $id)->paginate(10);
        return view('admin.Users.single', compact('get', 'post'));
    }


    public function delete_user_photo($id){
        User::where('id', $id)->update([
           'avatar' => 'default.png'
        ]);
        return redirect()->back();
    }
    public function update_user_data(Request $request){
        $get = User::where('id', $request->user_id)->first();

        if ($get == null){
            return redirect()->back();
        }
        if (isset($request->password)){
            $get->update([
               'password' => Hash::make($request->password)
            ]);
        }
        $get->update([
           'name' => $request->name
        ]);
        return redirect()->back();
    }

    public function user_star($id){
        $get = User::where('id', $id)->first();

        if ($get == null){
            return redirect()->back();
        }


        if ($get->star == 1){
            $get->update([
               'star' => 0
            ]);
        }else{
            $get->update([
               'star' => 1
            ]);
        }
        return redirect()->back();
    }


    public function black_list($id){
        $get = User::where('id', $id)->first();

        if ($get == null){
            return redirect()->back();
        }

        if ($get->black_list_status == null){
            $get->update([
                'black_list_status' => 1
            ]);
        }else{
            $get->update([
                'black_list_status' => null
            ]);
        }

        return redirect()->back();

    }
}
