<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ConsentType;

class ConsentTypeSeeder extends Seeder
{
    public function run(): void
    {
        $items = [

            [
                'code' => 'privacy_notice',
                'name' => 'Informativa privacy',
                'category' => 'privacy_notice',
                'description' => 'Presa visione informativa privacy GDPR',
                'is_active' => true,
            ],

            [
                'code' => 'promotional_emails',
                'name' => 'Comunicazioni promozionali',
                'category' => 'consent',
                'description' => 'Invio newsletter e comunicazioni marketing',
                'is_active' => true,
            ],

            [
                'code' => 'image_disclosure',
                'name' => 'Utilizzo e divulgazione immagini',
                'category' => 'consent',
                'description' => 'Autorizzazione utilizzo immagini e materiale fotografico',
                'is_active' => true,
            ],

        ];

        foreach ($items as $item) {

            ConsentType::updateOrCreate(
                [
                    'code' => $item['code'],
                ],
                $item
            );

        }
    }



}

