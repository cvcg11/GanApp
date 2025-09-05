<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CapitalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('capital')->insertOrIgnore([
            [   
                'current_amount'    => 1000.0,
                'last_update'       => now()
            ],
        ]);
    }
}
