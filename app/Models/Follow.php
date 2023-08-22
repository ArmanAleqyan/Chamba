<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Follow extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function followers(){
        return $this->belongsto(User::class, 'sender_id');
    }
    public function follower(){
        return $this->belongsto(User::class, 'receiver_id');
    }

}
