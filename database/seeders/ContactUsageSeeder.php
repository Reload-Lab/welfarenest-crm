<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContactUsageSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['code' => 'main',           'name' => 'Principale'],
            ['code' => 'administrative', 'name' => 'Ufficio Amministrativo'],
            ['code' => 'commercial',     'name' => 'Ufficio Commerciale'],
            ['code' => 'support',        'name' => 'Supporto / Servizio clienti'],
            ['code' => 'legal',          'name' => 'Ufficio Legale'],
            ['code' => 'presidential',   'name' => 'Presidenza / Direzione'],
        ];

        foreach ($items as $index => $item) {
            DB::table('contact_usages')->insertOrIgnore([
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
