<?php

namespace App\Exports;

use App\Models\RegisteredMember;
use Maatwebsite\Excel\Concerns\FromCollection;

class RegisteredMembersExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return RegisteredMember::all();
    }
}
