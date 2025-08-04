<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $fillable = [
       "user_id",
       "name",
       "fullname",
       "description",
    ];
}