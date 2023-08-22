<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Post;
class PostController extends Controller
{
    public function all_post(){
        $get =Post::orderby('id', 'desc')->paginate(10);
        $new_count = Post::where('created_at', '>=', Carbon::now()->subDay())->count();
        return view('admin.Post.all', compact('get', 'new_count'));
    }

    public function single_page_post($id){
        $get = Post::where('id', $id)->first();
        if ($get == null){
            return redirect()->back();
        }

        return view('admin.Post.single', compact('get'));
    }


    public function delete_post($id){
        Post::where('id', $id)->delete();


        return redirect()->route('all_post');
    }
}
