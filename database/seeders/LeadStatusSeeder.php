<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LeadStatusSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['code' => 'new',         'name' => 'Nuovo',           'is_won' => false, 'is_lost' => false],
            ['code' => 'contacted',   'name' => 'Contattato',      'is_won' => false, 'is_lost' => false],
            ['code' => 'qualified',   'name' => 'Qualificato',     'is_won' => false, 'is_lost' => false],
            ['code' => 'negotiation', 'name' => 'In valutazione',  'is_won' => false, 'is_lost' => false],
            ['code' => 'converted',   'name' => 'Convertito',      'is_won' => true,  'is_lost' => false],
            ['code' => 'lost',        'name' => 'Perso',           'is_won' => false, 'is_lost' => true],
            ['code' => 'unqualified', 'name' => 'Non qualificato', 'is_won' => false, 'is_lost' => true],
        ];

        foreach ($items as $index => $item) {
            DB::table('lead_statuses')->insertOrIgnore([
                'code' => $item['code'],
                'name' => $item['name'],
                'description' => null,
                'is_won' => $item['is_won'],
                'is_lost' => $item['is_lost'],
                'sort_order' => $index + 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
