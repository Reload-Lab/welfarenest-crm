<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrganizationRoleSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['code' => 'client',      'name' => 'Cliente'],
            ['code' => 'institution', 'name' => 'Istituzionale'],
            ['code' => 'supplier',    'name' => 'Fornitore'],
        ];

        foreach ($items as $index => $item) {
            DB::table('organization_roles')->insertOrIgnore([
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
