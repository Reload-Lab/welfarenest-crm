<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrganizationController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        $allowedSorts = [
            'id',
            'name',
            'legal_name',
            'vat_number',
            'tax_code',
            'is_active',
        ];

        $sort = in_array($request->sort, $allowedSorts, true) ? $request->sort : 'id';
        $direction = $request->direction === 'asc' ? 'asc' : 'desc';

        $organizations = Organization::query()
            ->when($search, function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('legal_name', 'like', "%{$search}%")
                        ->orWhere('vat_number', 'like', "%{$search}%")
                        ->orWhere('tax_code', 'like', "%{$search}%");
                });
            })
            ->orderBy($sort, $direction)
            ->paginate(10)
            ->appends($request->query());

        return view('organizations.index', compact('organizations', 'search', 'sort', 'direction'));
    }

    public function create()
    {
        return view('organizations.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateOrganization($request);

        Organization::create($validated);

        return redirect()
            ->route('organizations.index')
            ->with('success', 'Organizzazione creata correttamente.');
    }

    public function edit(Organization $organization)
    {
        return view('organizations.edit', compact('organization'));
    }

    public function update(Request $request, Organization $organization)
    {
        $validated = $this->validateOrganization($request);

        $organization->update($validated);

        return redirect()
            ->route('organizations.index')
            ->with('success', 'Organizzazione aggiornata.');
    }

    public function destroy(Organization $organization)
    {
        $organization->delete();

        return redirect()
            ->route('organizations.index')
            ->with('success', 'Organizzazione eliminata.');
    }

    protected function validateOrganization(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'organization_type_id' => ['nullable', 'integer', 'exists:organization_types,id'],
            'vat_number' => ['nullable', 'string', 'max:50'],
            'tax_code' => ['nullable', 'string', 'max:50'],
            'sdi_code' => ['nullable', 'string', 'max:20'],
            'is_split_payment' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $name = trim((string) ($validated['name'] ?? ''));
        $legalName = trim((string) ($validated['legal_name'] ?? ''));

        if ($name === '' && $legalName === '') {
            return back()
                ->withErrors([
                    'name' => 'Compila almeno Nome oppure Ragione sociale.',
                    'legal_name' => 'Compila almeno Nome oppure Ragione sociale.',
                ])
                ->withInput()
                ->throwResponse();
        }

        $validated['is_split_payment'] = $request->boolean('is_split_payment');
        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}