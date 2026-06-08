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

        foreach ($roles as $index => $item) {   

            DB::table('organization_roles')->updateOrInsert(
                ['code' => $item['code']],
                [
                    'code' => $item['code'],
                    'name' => $item['name'],
                    'description' => null,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

        }





    }
}