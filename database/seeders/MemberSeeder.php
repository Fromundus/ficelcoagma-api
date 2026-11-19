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
        $path = storage_path('app/consumers.csv');
    
        // Read the file and parse tab-delimited values
        $data = array_map(function ($line) {
            return str_getcsv($line, "\t");
        }, file($path));

        $header = array_shift($data); // Extract column headers

        foreach ($data as $row) {
            if (count($row) < 4) continue;

            DB::table('members')->insert([
                'account_number' => $row[0],
                'area'          => $row[1],
                'book'          => $row[2],
                'name'          => $row[3],
                'address'       => $row[4],
            ]);
        }
    }
}
