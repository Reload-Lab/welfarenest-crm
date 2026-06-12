<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WnPlusRole;

class WnPlusRoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'code' => 'manager',
                'name' => 'Referente',
                'description' => 'Gestisce gli utenti della propria organizzazione.',
                'sort_order' => 10,
            ],
            [
                'code' => 'user',
                'name' => 'Utente',
                'description' => 'Utente standard di Welfare Nest Plus.',
                'sort_order' => 20,
            ],
        ];

        foreach ($roles as $role) {
            WnPlusRole::updateOrCreate(
                ['code' => $role['code']],
                $role
            );
        }
    }
}
