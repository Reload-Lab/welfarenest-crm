<?php

namespace App\Console\Commands;

use App\Services\Import\ImportExecutor;
use App\Services\Import\ImportStructureException;
use Illuminate\Console\Command;

class RunOrganizationImport extends Command
{
    protected $signature = 'import:run
                            {file : percorso del file .xlsx, assoluto o relativo a storage/app}
                            {--exclude=* : numeri di riga del foglio Organizzazioni da non importare}
                            {--force : procedi anche se lo stesso file è già stato importato}
                            {--yes : salta la richiesta di conferma (console non interattive)}';

    protected $description = 'Importa le organizzazioni di un file già analizzato';

    public function handle(ImportExecutor $executor): int
    {
        $path = $this->argument('file');

        if (! str_starts_with($path, '/')) {
            $path = storage_path('app/'.$path);
        }

        if (! is_file($path)) {
            $this->error("File non trovato: {$path}");

            return self::FAILURE;
        }

        $previous = $executor->previousBatchFor($path);

        if ($previous !== null && ! $this->option('force')) {
            $this->warn(sprintf(
                'Questo identico file è già stato importato il %s (batch #%d, %d organizzazioni create).',
                $previous->created_at->format('d/m/Y H:i'), $previous->id, $previous->organizations_created
            ));
            $this->line('Le organizzazioni già presenti verrebbero comunque saltate. Usa --force per procedere.');

            return self::FAILURE;
        }

        try {
            $analysis = $executor->analyze($path);
        } catch (ImportStructureException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $excluded = array_map('intval', $this->option('exclude'));
        $summary = $analysis['summary'];
        $toCreate = $summary['create'] - count($excluded);

        $this->newLine();
        $this->table(['', ''], [
            ['Organizzazioni da creare', $toCreate],
            ['  escluse a mano', count($excluded)],
            ['Già presenti (saltate)', $summary['skip']],
            ['Non importabili', $summary['error']],
            ['Indirizzi', $summary['addresses']],
            ['Recapiti', $summary['contacts']],
        ]);

        if ($summary['error'] > 0) {
            $this->warn(sprintf(
                '%d organizzazioni non verranno importate perché contengono errori. '
                .'Correggile nel file e rilancia: quelle già inserite verranno riconosciute e saltate.',
                $summary['error']
            ));
        }

        if ($toCreate < 1) {
            $this->info('Niente da importare.');

            return self::SUCCESS;
        }

        if (! $this->option('yes')) {
            // Su console non interattive confirm() restituisce il valore
            // predefinito senza chiedere nulla: meglio fermarsi dicendolo,
            // invece di non importare in silenzio.
            if (! $this->input->isInteractive()) {
                $this->warn('La console non è interattiva: impossibile chiedere conferma.');
                $this->line('Rilancia con --yes per procedere.');

                return self::FAILURE;
            }

            if (! $this->confirm("Procedere con l'importazione di {$toCreate} organizzazioni?", false)) {
                $this->line('Annullato.');

                return self::SUCCESS;
            }
        }

        $result = $executor->execute($path, null, $excluded);
        $batch = $result['batch'];

        $this->newLine();
        $this->info("Importazione completata (batch #{$batch->id})");
        $this->table(['', ''], [
            ['Organizzazioni create', $batch->organizations_created],
            ['Indirizzi creati', $batch->addresses_created],
            ['Recapiti creati', $batch->contacts_created],
            ['Blocchi saltati (già presenti)', $batch->blocks_skipped],
            ['Blocchi esclusi', $batch->blocks_excluded],
        ]);

        return self::SUCCESS;
    }
}
