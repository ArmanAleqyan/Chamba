<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;
    protected $guarded =[];

    public function sender(){
        return $this->belongsto(User::class, 'sender_id');
    }

    public function basketable(){
        return $this->morphTo('parent');
    }
}
