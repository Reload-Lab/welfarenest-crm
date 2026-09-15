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
            ['code' => 'legal',          'name' => 'Sede legale'],
            ['code' => 'operational',    'name' => 'Sede operativa'],
            ['code' => 'other',          'name' => 'Altro'],
            ['code' => 'shipping',       'name' => 'Indirizzo spedizione'],
        ];

        foreach ($items as $index => $item) {
            DB::table('address_types')->insertOrIgnore([
                'code' => $item['code'],
                'name' => $item['name'],
                'description' => null,
                'sort_order' => $index + 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
