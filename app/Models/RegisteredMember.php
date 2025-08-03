<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegisteredMember extends Model
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
        "created_by",
        "status",
        "reference_number",
        "registration_method",
    ];
}
