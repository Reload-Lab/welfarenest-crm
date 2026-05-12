<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrganizationTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Altre aziende'],
            ['name' => 'Banca'],
            ['name' => 'Big pharma'],
            ['name' => 'Broker'],
            ['name' => 'Cassa di previdenza'],
            ['name' => 'Compagnia di assicurazione'],
            ['name' => 'Ente bilaterale'],
            ['name' => 'Erogatore sanitario'],
            ['name' => 'ETS'],
            ['name' => 'Fondo pensione'],
            ['name' => 'Fondo sanitario'],
            ['name' => 'Istituzioni'],
            ['name' => 'SGR'],
            ['name' => 'SMS'],
            ['name' => 'Società di consulenza'],
            ['name' => 'Società di consulenza comunicazione'],
            ['name' => 'Società di consulenza IT'],
            ['name' => 'Studio attuariale'],
            ['name' => 'Studio commercialista'],
            ['name' => 'Studio consulenza del lavoro'],
            ['name' => 'Studio legale'],
            ['name' => 'Studi professionali'],
            ['name' => 'TPA'],
            ['name' => 'Università/Centro di ricerca'],
            ['name' => 'Utility'],
        ];

        $data = [];

        foreach ($types as $index => $type) {

            $code = strtolower($type['name']);
            $code = str_replace(
                ['à','è','é','ì','ò','ù'],
                ['a','e','e','i','o','u'],
                $code
            );

            $code = preg_replace('/[^a-z0-9]+/', '_', $code);
            $code = trim($code, '_');

            DB::table('organization_types')->updateOrInsert(
                ['code' => $code],
                [
                    'name' => $type['name'],
                    'description' => null,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

        }

        DB::table('organization_types')->insert($data);
    }
}