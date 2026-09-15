<?php

namespace App\Console\Commands;

use App\Services\Import\ImportStructureException;
use App\Services\Import\OrganizationImportAnalyzer;
use Illuminate\Console\Command;

class AnalyzeOrganizationImport extends Command
{
    protected $signature = 'import:analyze
                            {file : percorso del file .xlsx, assoluto o relativo a storage/app}
                            {--limit=100 : righe di dettaglio mostrate per sezione}';

    protected $description = 'Analizza un file di importazione organizzazioni senza scrivere nulla';

    public function handle(OrganizationImportAnalyzer $analyzer): int
    {
        $path = $this->argument('file');

        if (! str_starts_with($path, '/')) {
            $path = storage_path('app/'.$path);
        }

        try {
            $result = $analyzer->analyze($path);
        } catch (ImportStructureException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $limit = (int) $this->option('limit');
        $summary = $result['summary'];

        $this->newLine();
        $this->info('Riepilogo');
        $this->table(['', ''], [
            ['Organizzazioni nel file', $summary['organizations']],
            ['  da creare', $summary['create']],
            ['  già presenti (saltate)', $summary['skip']],
            ['  da escludere', $summary['error']],
            ['Indirizzi collegati', $summary['addresses']],
            ['Recapiti collegati', $summary['contacts']],
            ['Righe figlie orfane', $summary['orphan_addresses'] + $summary['orphan_contacts']],
            ['Avvisi', $summary['warnings']],
            ['Organizzazioni già in archivio', $result['existing_count']],
        ]);

        foreach ($result['warnings'] as $warning) {
            $this->warn($warning);
        }

        $this->section('Da escludere', $result['blocks'], OrganizationImportAnalyzer::ACTION_ERROR, $limit);
        $this->skipped($result, $limit);
        $this->orphans($result['orphans'], $limit);

        $this->newLine();

        if ($summary['error'] > 0 || $summary['orphan_addresses'] + $summary['orphan_contacts'] > 0) {
            $this->warn('Il file contiene righe non importabili: correggerle prima di procedere.');
        } else {
            $this->info('Nessun errore bloccante.');
        }

        return self::SUCCESS;
    }

    private function section(string $title, array $blocks, string $action, int $limit): void
    {
        $selected = array_filter($blocks, fn (array $b) => $b['action'] === $action);

        if ($selected === []) {
            return;
        }

        $this->newLine();
        $this->info($title.' ('.count($selected).')');

        foreach (array_slice($selected, 0, $limit) as $block) {
            $this->line(sprintf('  riga %-5d %s', $block['row'], $block['label']));

            foreach ($block['issues'] as $issue) {
                $this->line(sprintf('        <fg=red>%s</> %s', $issue['field'], $issue['message']));
            }

            foreach (['addresses' => 'indirizzo', 'contacts' => 'recapito'] as $bucket => $label) {
                foreach ($block[$bucket] as $entry) {
                    foreach ($entry['issues'] as $issue) {
                        if ($issue['severity'] !== 'error') {
                            continue;
                        }

                        $this->line(sprintf('        <fg=red>%s riga %d</> %s: %s',
                            $label, $entry['row'], $issue['field'], $issue['message']));
                    }
                }
            }
        }

        if (count($selected) > $limit) {
            $this->line(sprintf('  ... e altre %d', count($selected) - $limit));
        }
    }

    private function skipped(array $result, int $limit): void
    {
        $selected = array_filter(
            $result['blocks'],
            fn (array $b) => $b['action'] === OrganizationImportAnalyzer::ACTION_SKIP
        );

        if ($selected === []) {
            return;
        }

        $divergent = array_filter($selected, fn (array $b) => $b['differences'] !== []);
        $identical = count($selected) - count($divergent);

        $this->newLine();
        $this->info('Già presenti in archivio ('.count($selected).')');

        if ($identical > 0) {
            $this->line("  {$identical} coincidono con quanto è già in archivio, non elencate.");
        }

        if ($divergent === []) {
            return;
        }

        $this->newLine();
        $this->line('  Con differenze rispetto al CRM ('.count($divergent).') — nessun dato verrà modificato:');

        foreach (array_slice($divergent, 0, $limit) as $block) {
            $this->line(sprintf('  riga %-5d %s → #%d %s (per %s)',
                $block['row'], $block['label'],
                $block['existing']['id'], $block['existing']['display'], $block['existing']['matched_on']));

            foreach ($block['differences'] as $difference) {
                $this->line(sprintf('        <fg=yellow>%s</> nel file "%s", nel CRM "%s"',
                    $difference['field'], $difference['file'], $difference['crm']));
            }
        }
    }

    private function orphans(array $orphans, int $limit): void
    {
        foreach (['addresses' => 'Indirizzi', 'contacts' => 'Recapiti'] as $bucket => $label) {
            if ($orphans[$bucket] === []) {
                continue;
            }

            $this->newLine();
            $this->info($label.' non collegabili ('.count($orphans[$bucket]).')');

            foreach (array_slice($orphans[$bucket], 0, $limit) as $entry) {
                $this->line(sprintf('  riga %-5d %s', $entry['row'],
                    $entry['data']['organization_ref'] ?? '(organizzazione non indicata)'));

                foreach ($entry['issues'] as $issue) {
                    if ($issue['severity'] === 'error') {
                        $this->line(sprintf('        <fg=red>%s</> %s', $issue['field'], $issue['message']));
                    }
                }
            }
        }
    }
}
