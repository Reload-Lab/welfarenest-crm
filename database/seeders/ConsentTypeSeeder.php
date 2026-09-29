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

            // --- Consensi gestiti dall'utente nell'area riservata WN+ ---
            // Introdotti il 29/9/2026 sulla base della matrice consensi e delle
            // informative 12 (referente) e 13 (membro). Prima di questa data solo
            // privacy_notice e image_disclosure erano modellati, e "aggiornamenti
            // facoltativi" era mappato impropriamente su promotional_emails.

            [
                'code' => 'profile_visibility_basic',
                'name' => 'Visibilità di nome, cognome e azienda',
                'category' => 'consent',
                'description' => 'Visibilità del profilo base nella directory della community WN+',
                'is_active' => true,
            ],

            [
                'code' => 'profile_visibility_email',
                'name' => 'Visibilità dell\'indirizzo email',
                'category' => 'consent',
                'description' => 'Visibilità dell\'email professionale nella directory WN+ (subordinata al profilo base)',
                'is_active' => true,
            ],

            [
                'code' => 'profile_visibility_phone',
                'name' => 'Visibilità del numero di telefono',
                'category' => 'consent',
                'description' => 'Visibilità del numero di telefono nella directory WN+ (subordinata al profilo base)',
                'is_active' => true,
            ],

            [
                'code' => 'profile_visibility_photo',
                'name' => 'Visibilità della fotografia del profilo',
                'category' => 'consent',
                'description' => 'Visibilità della fotografia caricata nel profilo WN+',
                'is_active' => true,
            ],

            [
                'code' => 'service_updates',
                'name' => 'Aggiornamenti facoltativi sul servizio',
                'category' => 'consent',
                'description' => 'Avvisi non strettamente necessari su nuove funzionalità e contenuti dell\'area riservata',
                'is_active' => true,
            ],

            [
                'code' => 'identifiable_surveys',
                'name' => 'Survey personali identificabili',
                'category' => 'consent',
                'description' => 'Trattamento delle risposte a survey personali identificabili (le rilevazioni aggregate non lo richiedono)',
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
