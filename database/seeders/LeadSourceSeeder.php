<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LeadSourceSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['code' => 'website',    'name' => 'Sito'],
            ['code' => 'event',      'name' => 'Convegni'],
            ['code' => 'newsletter', 'name' => 'Newsletter'],
            ['code' => 'referral',   'name' => 'Segnalazione'],
            ['code' => 'direct',     'name' => 'Contatto diretto'],
        ];

        foreach ($items as $index => $item) {
            DB::table('lead_sources')->insertOrIgnore([
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
