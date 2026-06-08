<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ConsentType;
use App\Models\ConsentVersion;

class ConsentVersionSeeder extends Seeder
{
    public function run(): void
    {
        $items = [

            [
                'consent_code' => 'privacy_notice',
                'version_code' => 'v1_2026',
                'title' => 'Informativa privacy 2026',
                'content_text' => 'Testo informativa privacy...',
            ],

            [
                'consent_code' => 'promotional_emails',
                'version_code' => 'v1_2026',
                'title' => 'Consenso comunicazioni promozionali 2026',
                'content_text' => 'Autorizzo l’invio di comunicazioni promozionali...',
            ],

            [
                'consent_code' => 'image_disclosure',
                'version_code' => 'v1_2026',
                'title' => 'Consenso utilizzo immagini 2026',
                'content_text' => 'Autorizzo l’utilizzo delle immagini...',
            ],

        ];

        foreach ($items as $item) {

            $consentType = ConsentType::where('code', $item['consent_code'])->first();

            if (! $consentType) {
                continue;
            }

            ConsentVersion::updateOrCreate(
                [
                    'consent_type_id' => $consentType->id,
                    'version_code' => $item['version_code'],
                ],
                [
                    'title' => $item['title'],
                    'content_text' => $item['content_text'],
                    'published_at' => now(),
                    'is_active' => true,
                ]
            );
        }
    }
}