<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['code' => 'area_commerciale', 'name' => 'Area commerciale'],
            ['code' => 'area_comunicazione', 'name' => 'Area comunicazione'],
            ['code' => 'area_formazione', 'name' => 'Area formazione'],
            ['code' => 'area_liquidazioni', 'name' => 'Area liquidazioni'],
            ['code' => 'area_operativa', 'name' => 'Area operativa'],
            ['code' => 'direzione', 'name' => 'Direzione'],
            ['code' => 'presidenza', 'name' => 'Presidenza'],
        ];

        $data = [];

        foreach ($items as $index => $item) {
            $data[] = [
                'code' => $item['code'],
                'name' => $item['name'],
                'description' => null,
                'sort_order' => $index + 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('departments')->insert($data);
    }
}