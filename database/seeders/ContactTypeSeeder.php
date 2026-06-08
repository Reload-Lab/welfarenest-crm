<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContactTypeSeeder extends Seeder
{

    public function run(): void
    {
        $items = [
            ['code' => 'email', 'name' => 'Email', 'category' => 'email'],
            ['code' => 'pec', 'name' => 'PEC', 'category' => 'email'],
            ['code' => 'linkedin', 'name' => 'LinkedIn', 'category' => 'social'],
            ['code' => 'facebook', 'name' => 'Facebook', 'category' => 'social'],
            ['code' => 'instagram', 'name' => 'Instagram', 'category' => 'social'],
            ['code' => 'phone', 'name' => 'Telefono', 'category' => 'phone'],
            ['code' => 'mobile', 'name' => 'Cellulare', 'category' => 'phone'],
            ['code' => 'website', 'name' => 'Sito web', 'category' => 'web'],
        ];

        foreach ($items as $index => $item) {

            DB::table('contact_types')->updateOrInsert(
                ['code' => $item['code']],
                [
                    'name' => $item['name'],
                    'category' => $item['category'],
                    'description' => null,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

        }
    }


}