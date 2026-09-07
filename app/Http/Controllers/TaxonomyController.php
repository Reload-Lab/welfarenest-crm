<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TaxonomyController extends Controller
{
    /**
     * Pagina di ingresso: elenco di tutte le tabelle di anagrafica configurate.
     */
    public function home()
    {
        $types = collect(config('taxonomies', []))
            ->map(function (array $config, string $slug) {
                $model = $config['model'];

                return [
                    'slug' => $slug,
                    'label' => $config['label'],
                    'label_plural' => $config['label_plural'],
                    'description' => $config['description'] ?? null,
                    'locked' => $config['locked'] ?? false,
                    'icon' => $config['icon'] ?? null,
                    'count' => $model::count(),
                ];
            })
            ->values();

        return view('taxonomies.home', compact('types'));
    }

    /**
     * Elenco delle voci di una singola tabella di anagrafica.
     */
    public function index(string $type)
    {
        $config = $this->configFor($type);

        $rows = $config['model']::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('taxonomies.index', [
            'type' => $type,
            'config' => $config,
            'rows' => $rows,
        ]);
    }

    public function store(Request $request, string $type): RedirectResponse
    {
        $config = $this->configFor($type);

        if ($config['locked'] ?? false) {
            return back()->with('error', 'Questa tabella è di sistema e non è modificabile da qui.');
        }

        $validated = $this->validated($request, $config);

        $config['model']::create($validated);

        return redirect()
            ->route('taxonomies.index', $type)
            ->with('success', $config['label'].' creata con successo.');
    }

    public function update(Request $request, string $type, int $id): RedirectResponse
    {
        $config = $this->configFor($type);

        if ($config['locked'] ?? false) {
            return back()->with('error', 'Questa tabella è di sistema e non è modificabile da qui.');
        }

        $row = $config['model']::findOrFail($id);

        $validated = $this->validated($request, $config, $row->id);

        $row->update($validated);

        return redirect()
            ->route('taxonomies.index', $type)
            ->with('success', $config['label'].' aggiornata con successo.');
    }

    public function toggleActive(string $type, int $id): RedirectResponse
    {
        $config = $this->configFor($type);

        if ($config['locked'] ?? false) {
            return back()->with('error', 'Questa tabella è di sistema e non è modificabile da qui.');
        }

        $row = $config['model']::findOrFail($id);
        $row->update(['is_active' => ! $row->is_active]);

        return back()->with('success', $row->is_active ? 'Voce riattivata.' : 'Voce disattivata.');
    }

    public function destroy(string $type, int $id): RedirectResponse
    {
        $config = $this->configFor($type);

        if ($config['locked'] ?? false) {
            return back()->with('error', 'Questa tabella è di sistema e non è modificabile da qui.');
        }

        $row = $config['model']::findOrFail($id);

        $usageCount = ($config['usage'] ?? fn () => 0)($row->id);

        if ($usageCount > 0) {
            $label = $config['usage_label'] ?? 'record';

            return back()->with(
                'error',
                "Impossibile eliminare \"{$row->name}\": è utilizzata da {$usageCount} {$label}. Disattivala invece di eliminarla."
            );
        }

        $row->delete();

        return redirect()
            ->route('taxonomies.index', $type)
            ->with('success', $config['label'].' eliminata con successo.');
    }

    protected function configFor(string $type): array
    {
        $config = config("taxonomies.{$type}");

        if (! $config) {
            throw new NotFoundHttpException("Tabella di anagrafica sconosciuta: {$type}");
        }

        return $config;
    }

    protected function validated(Request $request, array $config, ?int $ignoreId = null): array
    {
        $table = (new $config['model'])->getTable();

        $rules = [
            'code' => [
                'required', 'string', 'max:255',
                Rule::unique($table, 'code')->ignore($ignoreId),
            ],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'boolean'],
        ];

        foreach ($config['extra_fields'] ?? [] as $field => $fieldConfig) {
            $fieldRules = [($fieldConfig['required'] ?? false) ? 'required' : 'nullable', 'string', 'max:255'];

            if (($fieldConfig['type'] ?? null) === 'select') {
                $fieldRules[] = Rule::in(array_keys($fieldConfig['options'] ?? []));
            }

            $rules[$field] = $fieldRules;
        }

        $validated = $request->validate($rules);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        return $validated;
    }
}
