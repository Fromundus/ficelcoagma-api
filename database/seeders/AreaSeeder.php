<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AreaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
     $file = storage_path('app/area.csv');

        if (!file_exists($file)) {
            $this->command->error("CSV file not found: $file");
            return;
        }

        // Parse tab-separated file
        $csv = array_map(function ($line) {
            return str_getcsv($line, "\t");
        }, file($file));

        // If first row looks like a header, drop it
        if (isset($csv[0]) && strtolower(trim($csv[0][0])) === 'account_number') {
            array_shift($csv);
        }

        foreach ($csv as $row) {
            if (count($row) < 2) {
                continue;
            }

            $accountNumber = trim($row[0]); // account number
            $area = trim($row[1]);          // area code

            if ($accountNumber && $area) {
                $updated1 = DB::table('members')
                    ->where('account_number', $accountNumber)
                    ->update(['area' => $area]);

                $updated2 = DB::table('registered_members')
                    ->where('account_number', $accountNumber)
                    ->update(['area' => $area]);

                if (!$updated1 && !$updated2) {
                    $this->command->warn("Account not found: $accountNumber");
                }
            }
        }

        $this->command->info("Area seeding completed.");   
    }
}
