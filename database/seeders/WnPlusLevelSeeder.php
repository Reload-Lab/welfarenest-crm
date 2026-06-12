<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WnPlusLevel;

class WnPlusLevelSeeder extends Seeder
{
    public function run(): void
    {
        $levels = [
            [
                'code' => 'base',
                'name' => 'Base',
                'description' => 'Accesso standard.',
                'sort_order' => 10,
            ],
            [
                'code' => 'premium',
                'name' => 'Premium',
                'description' => 'Accesso avanzato.',
                'sort_order' => 20,
            ],
            [
                'code' => 'pro',
                'name' => 'Pro',
                'description' => 'Accesso completo.',
                'sort_order' => 30,
            ],
        ];

        foreach ($levels as $level) {
            WnPlusLevel::updateOrCreate(
                ['code' => $level['code']],
                $level
            );
        }
    }
}