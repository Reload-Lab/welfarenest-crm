<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrganizationRoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['code' => 'client', 'name' => 'Cliente'],
            ['code' => 'internal', 'name' => 'Interno'],
            ['code' => 'supplier', 'name' => 'Fornitore'],
        ];

        $data = [];

        foreach ($roles as $index => $role) {
            $data[] = [
                'code' => $role['code'],
                'name' => $role['name'],
                'description' => null,
                'sort_order' => $index + 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('organization_roles')->insert($data);
    }
}