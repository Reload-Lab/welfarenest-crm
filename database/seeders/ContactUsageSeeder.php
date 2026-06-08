<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContactUsageSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['code' => 'administrative', 'name' => 'Amministrativo'],
            ['code' => 'commercial', 'name' => 'Commerciale'],
            ['code' => 'direct', 'name' => 'Diretto'],
            ['code' => 'office', 'name' => 'Ufficio'],
            ['code' => 'personal', 'name' => 'Personale'],
            ['code' => 'support', 'name' => 'Supporto'],
            ['code' => 'work', 'name' => 'Lavoro'],
        ];

        $data = [];




        foreach ($items as $index => $item) {

            DB::table('contact_usages')->updateOrInsert(
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