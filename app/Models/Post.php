<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;
    protected $guarded =[];
    public function user(){
        return $this->Belongsto(User::class, 'user_id');
    }
    public function photo(){
        return $this->hasMany(PostPhoto::class, 'post_id');
    }
    public function like(){
        return $this->hasMany(PostLike::class, 'post_id');
    }

    public function like_auth_user(){
        return $this->hasMany(PostLike::class, 'post_id')->where('user_id', auth()->user()->id);
    }
    public function comment(){
        return $this->hasMany(Comment::class, 'post_id');
    }

    public function auth_user_book()
    {
        return $this->hasmany(Book::class, 'post_id')->where('user_id', auth()->user()->id);
    }

}
