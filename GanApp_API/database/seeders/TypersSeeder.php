<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TypersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('types')->insertOrIgnore([
        [
            'name'          => 'Ingreso',
            'created_at'    => now(),
            'updated_at'    => now()
        ],
        [   'name'          => 'Gasto',
            'created_at'    => now(),
            'updated_at'    => now()
        ],
        ]);
    }
}
