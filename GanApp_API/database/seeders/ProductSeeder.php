<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('products')->insert([
            [
                'name'          => 'Camisa deportiva',
                'description'   => 'Algodon azul talla S',
                'stock'         => 50,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'name'          => 'Gorra',
                'description'   => 'Colo negro',
                'stock'         => 10,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]
        ]);
    }
}
