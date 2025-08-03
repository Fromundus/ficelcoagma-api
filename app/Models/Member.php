<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $fillable = [
        "account_number",
        "book",
        "name",
        "address",
        "occupant",
        "id_presented",
        "id_number",
        "phone_number",
        "email",
        "created",
        "created_by",
        "status",
    ];
}
