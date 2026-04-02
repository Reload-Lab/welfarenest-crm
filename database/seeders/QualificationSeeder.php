<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QualificationSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['code' => 'consulente', 'name' => 'Consulente'],
            ['code' => 'consulente_attuariale', 'name' => 'Consulente attuariale'],
            ['code' => 'consulente_comunicazione', 'name' => 'Consulente comunicazione'],
            ['code' => 'consulente_del_lavoro', 'name' => 'Consulente del lavoro'],
            ['code' => 'consulente_it', 'name' => 'Consulente IT'],
            ['code' => 'consulente_legale', 'name' => 'Consulente legale'],
            ['code' => 'consulente_medico_sanitario', 'name' => 'Consulente medico-sanitario'],
            ['code' => 'direttore', 'name' => 'Direttore'],
            ['code' => 'docente', 'name' => 'Docente'],
            ['code' => 'organi_sociali', 'name' => 'Organi sociali'],
            ['code' => 'presidente', 'name' => 'Presidente'],
            ['code' => 'responsabile_ufficio_area', 'name' => 'Responsabile Ufficio/Area'],
        ];

        $data = [];

        foreach ($items as $index => $item) {
            $data[] = [
                'code' => $item['code'],
                'name' => $item['name'],
                'description' => null,
                'sort_order' => $index + 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('qualifications')->insert($data);
    }
}