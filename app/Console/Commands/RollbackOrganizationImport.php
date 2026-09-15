<?php

namespace App\Console\Commands;

use App\Models\ImportBatch;
use App\Services\Import\ImportRollback;
use Illuminate\Console\Command;

class RollbackOrganizationImport extends Command
{
    protected $signature = 'import:rollback
                            {batch? : id del batch da annullare (default: l\'ultimo)}
                            {--force : procedi anche cancellando i dati aggiunti dopo l\'importazione}';

    protected $description = 'Annulla un\'importazione, cancellando ciò che aveva creato';

    public function handle(ImportRollback $rollback): int
    {
        $batch = $this->argument('batch') !== null
            ? ImportBatch::find($this->argument('batch'))
            : ImportBatch::latest('id')->first();

        if ($batch === null) {
            $this->error('Nessun batch da annullare.');

            return self::FAILURE;
        }

        $this->newLine();
        $this->info("Batch #{$batch->id} — {$batch->filename} del {$batch->created_at->format('d/m/Y H:i')}");
        $this->table(['', ''], [
            ['Organizzazioni create', $batch->organizations_created],
            ['Indirizzi creati', $batch->addresses_created],
            ['Recapiti creati', $batch->contacts_created],
        ]);

        $foreign = $rollback->foreignData($batch);

        if ($foreign !== []) {
            $this->newLine();
            $this->warn('Alle organizzazioni di questo batch sono stati aggiunti dati che l\'importazione non ha creato:');

            foreach ($foreign as $table => $count) {
                $this->line("  {$table}: {$count} righe");
            }

            if (! $this->option('force')) {
                $this->newLine();
                $this->error('Annullamento interrotto: cancellare le organizzazioni lascerebbe queste righe orfane.');
                $this->line('Usa --force per cancellare anche quelle, oppure elimina prima quei dati dal CRM.');

                return self::FAILURE;
            }

            $this->warn('Con --force verranno cancellate anche queste righe.');
        }

        if (! $this->confirm("Annullare il batch #{$batch->id}? L'operazione non è reversibile.", false)) {
            $this->line('Annullato.');

            return self::SUCCESS;
        }

        $deleted = $rollback->rollback($batch, (bool) $this->option('force'));

        $this->newLine();
        $this->info('Batch annullato.');

        foreach ($deleted as $table => $count) {
            $this->line(sprintf('  %-20s %d righe eliminate', $table, $count));
        }

        return self::SUCCESS;
    }
}
