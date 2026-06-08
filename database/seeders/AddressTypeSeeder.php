<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AddressTypeSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['code' => 'administrative', 'name' => 'Sede amministrativa'],
            ['code' => 'domicile', 'name' => 'Domicilio'],
            ['code' => 'legal', 'name' => 'Sede legale'],
            ['code' => 'operational', 'name' => 'Sede operativa'],
            ['code' => 'other', 'name' => 'Altro'],
            ['code' => 'residence', 'name' => 'Residenza'],
            ['code' => 'shipping', 'name' => 'Recapito spedizioni'],
            ['code' => 'work_location', 'name' => 'Sede di lavoro'],
        ];

        $data = [];

        foreach ($items as $index => $item) {

            DB::table('address_types')->updateOrInsert(
                ['code' => $item['code']],
                [
                    'name' => $item['name'],
                    'description' => null,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

        }
    }
}