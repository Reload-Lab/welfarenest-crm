<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ConsentType;
use App\Models\ConsentVersion;

/**
 * Versioni REALI delle informative/consensi Welfare Nest (sostituisce le versioni
 * segnaposto 'v1_2026' con i contenuti effettivi ricevuti da Alessio: 14 informative
 * + matrice consensi, convertite in PDF per l'archiviazione).
 *
 * Ogni riga qui sotto è una VARIANTE "per pubblico/ruolo" della stessa tipologia di
 * consenso (es. privacy_notice ha una variante per lead, una per iscritti newsletter,
 * una per dipendenti, ecc.), non una revisione nel tempo dello stesso testo. Per questo
 * version_code incorpora sia il pubblico sia l'anno (es. "08_dipendente_2026_v1") invece
 * del solo anno: più versioni dello stesso consent_type possono essere is_active=true
 * contemporaneamente, e il consenso registrato punta esplicitamente alla versione giusta
 * tramite consent_version_id.
 *
 * Solo 3 tipologie in questo giro (privacy_notice, promotional_emails, image_disclosure):
 * le altre tipologie individuate nella matrice (visibilità WN+, survey, liberatoria
 * materiali, uso logo, atti di designazione/nomina responsabile) sono rimandate a una
 * fase successiva — vedi claude/analisi-consensi-matrice.md nel progetto Claude per il
 * dettaglio completo.
 *
 * NB (10 settembre 2026): "aggiornamenti facoltativi" per referente/membro WN+ è stato
 * mappato su promotional_emails (non su un tipo dedicato "wn_plus_optional_updates",
 * scartato) aggiungendo due varianti 12_referente_wnplus/13_membro_wnplus che puntano
 * agli stessi PDF già usati da privacy_notice/image_disclosure per quei ruoli (le
 * informative 12/13 coprono già tutti i consensi di quel ruolo in un unico documento).
 * Vedi claude/analisi-inviti-wnplus.md.
 *
 * I file .pdf vanno copiati in storage/app/private/consents/ (disco 'local'
 * di default in questo progetto) con lo stesso nome indicato in 'file' qui sotto.
 * content_text non viene valorizzato in questo giro, per scelta esplicita: si collega
 * solo il documento originale via content_file_path.
 */
class ConsentVersionSeeder extends Seeder
{
    public function run(): void
    {
        // Disattiva (senza cancellare: potrebbero già esistere consents collegati)
        // le vecchie versioni segnaposto, così i nuovi consensi non le propongono più.
        ConsentVersion::where('version_code', 'v1_2026')->update(['is_active' => false]);

        $items = [

            // --- privacy_notice: una variante per ciascun ruolo/canale (14 file) ---

            [
                'consent_code' => 'privacy_notice',
                'version_code' => '01_lead_2026_v1',
                'title' => 'Informativa primo contatto e inserimento nel CRM (art. 13)',
                'file' => '01_Informativa_Primo_Contatto_CRM_Art13.pdf',
            ],

            [
                'consent_code' => 'privacy_notice',
                'version_code' => '02_newsletter_2026_v1',
                'title' => 'Informativa newsletter (comunicazioni commerciali)',
                'file' => '02_Informativa_Comunicazioni_Commerciali_e_Consensi.pdf',
            ],

            [
                'consent_code' => 'privacy_notice',
                'version_code' => '03_percorso_formativo_2026_v1',
                'title' => 'Informativa percorso formativo (esterni)',
                'file' => '03_Informativa_Evento_Formativo_Esterni_Immagine_Singolo_Evento.pdf',
            ],

            [
                'consent_code' => 'privacy_notice',
                'version_code' => '03bis_evento_esterno_2026_v1',
                'title' => 'Informativa evento Welfare Nest (esterni, no WN+)',
                'file' => '03_bis_Informativa_Evento_Esterni_Immagine_Singolo_Evento.pdf',
            ],

            [
                'consent_code' => 'privacy_notice',
                'version_code' => '04_eventi_wnplus_2026_v1',
                'title' => 'Informativa eventi Welfare Nest Plus / Lab WN+',
                'file' => '04_Informativa_Eventi_Welfare_Nest_Plus_Immagine_Una_Tantum.pdf',
            ],

            [
                'consent_code' => 'privacy_notice',
                'version_code' => '05_referente_aziendale_2026_v1',
                'title' => 'Informativa referente aziendale (art. 14)',
                'file' => '05_Informativa_Referente_Aziendale_Art14.pdf',
            ],

            [
                'consent_code' => 'privacy_notice',
                'version_code' => '06_azienda_contratto_2026_v1',
                'title' => 'Informativa e consensi commerciali azienda/contratto',
                'file' => '06_Informativa_e_Consensi_Commerciali_Azienda_Contratto.pdf',
            ],

            [
                'consent_code' => 'privacy_notice',
                'version_code' => '07_docente_2026_v1',
                'title' => 'Informativa docente e formatore',
                'file' => '07_Informativa_Docente_E_Formatore_Immagine.pdf',
            ],

            [
                'consent_code' => 'privacy_notice',
                'version_code' => '08_dipendente_2026_v1',
                'title' => 'Informativa dipendente',
                'file' => '08_Informativa_Dipendente_Immagine.pdf',
            ],

            [
                'consent_code' => 'privacy_notice',
                'version_code' => '09_consulente_2026_v1',
                'title' => 'Informativa consulente',
                'file' => '09_Informativa_Consulente_Immagine.pdf',
            ],

            [
                'consent_code' => 'privacy_notice',
                'version_code' => '10_collaboratore_2026_v1',
                'title' => 'Informativa collaboratore/stagista/tirocinante',
                'file' => '10_Informativa_Collaboratore_Stagista_E_Tirocinante_Immagine.pdf',
            ],

            [
                'consent_code' => 'privacy_notice',
                'version_code' => '11_organi_sociali_2026_v1',
                'title' => 'Informativa componente organi sociali',
                'file' => '11_Informativa_Componente_Organi_Sociali_Immagine.pdf',
            ],

            [
                'consent_code' => 'privacy_notice',
                'version_code' => '12_referente_wnplus_2026_v1',
                'title' => 'Informativa referente Welfare Nest Plus (art. 14)',
                'file' => '12_Informativa_Referente_Welfare_Nest_Plus.pdf',
            ],

            [
                'consent_code' => 'privacy_notice',
                'version_code' => '13_membro_wnplus_2026_v1',
                'title' => 'Informativa membro Welfare Nest Plus',
                'file' => '13_Informativa_Membro_Welfare_Nest_Plus.pdf',
            ],

            [
                'consent_code' => 'privacy_notice',
                'version_code' => '14_autore_2026_v1',
                'title' => 'Informativa autore di articoli e materiali (art. 13)',
                'file' => '14_Informativa_e_Liberatoria_Autore_Materiali.pdf',
            ],

            // --- promotional_emails: newsletter personale, preferenza aziendale,
            //     e aggiornamenti facoltativi WN+ (referente/membro) ---

            [
                'consent_code' => 'promotional_emails',
                'version_code' => '02_newsletter_2026_v1',
                'title' => 'Consenso invio newsletter commerciale',
                'file' => '02_Informativa_Comunicazioni_Commerciali_e_Consensi.pdf',
            ],

            [
                'consent_code' => 'promotional_emails',
                'version_code' => '06_preferenza_aziendale_2026_v1',
                'title' => 'Preferenza aziendale per recapito funzionale',
                'file' => '06_Informativa_e_Consensi_Commerciali_Azienda_Contratto.pdf',
            ],

            [
                'consent_code' => 'promotional_emails',
                'version_code' => '12_referente_wnplus_2026_v1',
                'title' => 'Aggiornamenti facoltativi — referente Welfare Nest Plus',
                'file' => '12_Informativa_Referente_Welfare_Nest_Plus.pdf',
            ],

            [
                'consent_code' => 'promotional_emails',
                'version_code' => '13_membro_wnplus_2026_v1',
                'title' => 'Aggiornamenti facoltativi — membro Welfare Nest Plus',
                'file' => '13_Informativa_Membro_Welfare_Nest_Plus.pdf',
            ],

            // --- image_disclosure: uso e divulgazione immagine, una variante per contesto ---

            [
                'consent_code' => 'image_disclosure',
                'version_code' => '03_evento_formativo_2026_v1',
                'title' => 'Consenso immagini — percorso formativo (singolo evento)',
                'file' => '03_Informativa_Evento_Formativo_Esterni_Immagine_Singolo_Evento.pdf',
            ],

            [
                'consent_code' => 'image_disclosure',
                'version_code' => '03bis_evento_esterno_2026_v1',
                'title' => 'Consenso immagini — evento Welfare Nest (singolo evento)',
                'file' => '03_bis_Informativa_Evento_Esterni_Immagine_Singolo_Evento.pdf',
            ],

            [
                'consent_code' => 'image_disclosure',
                'version_code' => '04_wnplus_una_tantum_2026_v1',
                'title' => 'Consenso immagini una tantum — eventi WN+/Lab WN+',
                'file' => '04_Informativa_Eventi_Welfare_Nest_Plus_Immagine_Una_Tantum.pdf',
            ],

            [
                'consent_code' => 'image_disclosure',
                'version_code' => '07_docente_2026_v1',
                'title' => 'Consenso immagini una tantum — docente/formatore',
                'file' => '07_Informativa_Docente_E_Formatore_Immagine.pdf',
            ],

            [
                'consent_code' => 'image_disclosure',
                'version_code' => '08_dipendente_2026_v1',
                'title' => 'Consenso immagini una tantum — dipendente',
                'file' => '08_Informativa_Dipendente_Immagine.pdf',
            ],

            [
                'consent_code' => 'image_disclosure',
                'version_code' => '09_consulente_2026_v1',
                'title' => 'Consenso immagini una tantum — consulente',
                'file' => '09_Informativa_Consulente_Immagine.pdf',
            ],

            [
                'consent_code' => 'image_disclosure',
                'version_code' => '10_collaboratore_2026_v1',
                'title' => 'Consenso immagini una tantum — collaboratore/stagista/tirocinante',
                'file' => '10_Informativa_Collaboratore_Stagista_E_Tirocinante_Immagine.pdf',
            ],

            [
                'consent_code' => 'image_disclosure',
                'version_code' => '11_organi_sociali_2026_v1',
                'title' => 'Consenso immagini una tantum — componente organi sociali',
                'file' => '11_Informativa_Componente_Organi_Sociali_Immagine.pdf',
            ],

            [
                'consent_code' => 'image_disclosure',
                'version_code' => '12_referente_wnplus_2026_v1',
                'title' => 'Consenso immagini individualizzate — referente Welfare Nest Plus',
                'file' => '12_Informativa_Referente_Welfare_Nest_Plus.pdf',
            ],

            [
                'consent_code' => 'image_disclosure',
                'version_code' => '13_membro_wnplus_2026_v1',
                'title' => 'Consenso immagini individualizzate — membro Welfare Nest Plus',
                'file' => '13_Informativa_Membro_Welfare_Nest_Plus.pdf',
            ],

            [
                'consent_code' => 'image_disclosure',
                'version_code' => '14_autore_foto_2026_v1',
                'title' => 'Consenso fotografia autore',
                'file' => '14_Informativa_e_Liberatoria_Autore_Materiali.pdf',
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
                    'content_file_path' => 'consents/' . $item['file'],
                    'published_at' => now(),
                    'is_active' => true,
                ]
            );
        }
    }
}
