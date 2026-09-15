<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * I code sono espliciti e immutabili: sono la chiave con cui il modulo di
 * importazione e i dati esistenti referenziano la tipologia. I name sono
 * etichette di interfaccia, modificabili dalla gestione anagrafiche: per
 * questo il seeder inserisce solo ciò che manca e non sovrascrive mai.
 */
class OrganizationTypeSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['code' => 'altra_azienda',                'name' => 'Altra azienda'],
            ['code' => 'banca',                        'name' => 'Banca'],
            ['code' => 'big_pharma',                   'name' => 'Big pharma'],
            ['code' => 'broker',                       'name' => 'Broker'],
            ['code' => 'cassa_di_previdenza',          'name' => 'Cassa di previdenza'],
            ['code' => 'compagnia_di_assicurazione',   'name' => 'Compagnia di assicurazione'],
            ['code' => 'ente_bilaterale',              'name' => 'Ente bilaterale'],
            ['code' => 'erogatore_sanitario',          'name' => 'Erogatore sanitario'],
            ['code' => 'ets',                          'name' => 'Ente del Terzo settore'],
            ['code' => 'fondo_pensione',               'name' => 'Fondo pensione'],
            ['code' => 'fondo_sanitario',              'name' => 'Fondo sanitario'],
            ['code' => 'hr',                           'name' => 'Risorse Umane'],
            ['code' => 'istituzione',                  'name' => 'Istituzione'],
            ['code' => 'sgr',                          'name' => 'SGR'],
            ['code' => 'sms',                          'name' => 'Società di mutuo soccorso'],
            ['code' => 'societa_di_comunicazione',     'name' => 'Società di comunicazione'],
            ['code' => 'societa_di_consulenza',        'name' => 'Società di consulenza'],
            ['code' => 'societa_it',                   'name' => 'Società IT'],
            ['code' => 'studio_attuariale',            'name' => 'Studio attuariale'],
            ['code' => 'studio_commercialista',        'name' => 'Studio commercialista'],
            ['code' => 'studio_consulenza_del_lavoro', 'name' => 'Studio consulenza del lavoro'],
            ['code' => 'studio_legale',                'name' => 'Studio legale'],
            ['code' => 'studio_professionale',         'name' => 'Studio professionale'],
            ['code' => 'tpa',                          'name' => 'Third-Party Administrator'],
            ['code' => 'universita_centro_di_ricerca', 'name' => 'Università / Centro di ricerca'],
            ['code' => 'utility',                      'name' => 'Utility'],

            // Ripiego per le organizzazioni importate senza tipo. Disattivato
            // di proposito: non compare nei menu a tendina, lo assegna solo il
            // modulo di importazione, e il filtro sul tipo diventa la lista
            // delle organizzazioni da classificare a mano.
            ['code' => 'da_classificare',              'name' => 'Da classificare', 'is_active' => false],
        ];

        foreach ($items as $index => $item) {
            DB::table('organization_types')->insertOrIgnore([
                'code' => $item['code'],
                'name' => $item['name'],
                'description' => null,
                'sort_order' => $index + 1,
                'is_active' => $item['is_active'] ?? true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
