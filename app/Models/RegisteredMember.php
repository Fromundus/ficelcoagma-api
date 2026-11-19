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
        "occupant_2",
        "id_presented",
        "id_presented_2",
        "id_number",
        "id_number_2",
        "phone_number",
        "email",
        "created_by",
        "status",
        "area",
        "reference_number",
        "registration_method",
    ];
}
