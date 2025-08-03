<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = storage_path('app/agmadata.csv');
    
        // Read the file and parse tab-delimited values
        $data = array_map(function ($line) {
            return str_getcsv($line, "\t");
        }, file($path));

        $header = array_shift($data); // Extract column headers

        foreach ($data as $row) {
            if (count($row) < 4) continue;

            DB::table('members')->insert([
                'account_number' => $row[0],
                'book'          => $row[1],
                'name'          => $row[2],
                'address'       => $row[3],
            ]);
        }
    }
}
