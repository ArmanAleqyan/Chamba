<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;
    protected  $guarded = [];

    public function replay(){
        return $this->hasMany(Comment::class, 'parent_id');
    }
    public function user(){
        return $this->belongsto(User::class, 'user_id');
    }

    public function like_auth_user(){
        return $this->hasMany(CommentLike::class, 'comment_id')->where('user_id', auth()->user()->id);
    }
    public function likes()
    {
        return $this->hasMany(CommentLike::class, 'comment_id');
    }


        public function scopeWithNestedReplies($query)
        {
            return $query->withcount('replay')->withCount('likes')->with([
                'replay' => function ($query) {
                    $query->withNestedReplies();
                },
                'like_auth_user',
                'user'
            ]);
        }




}
