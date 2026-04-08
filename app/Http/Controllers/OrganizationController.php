<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\OrganizationType;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{

    public function show(Organization $organization)
    {
        $organization->load([
            'organizationType',
            // future relations:
            // 'people',
            // 'contactPoints.contactType',
            // 'contactPoints.contactUsage',
            // 'addresses.addressType',
            // 'notes.author',
        ]);

        return view('organizations.show', compact('organization'));
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $status = $request->input('status');
        $sort = $request->input('sort', 'name');
        $direction = $request->input('direction', 'asc');
        $perPage = (int) $request->input('per_page', 10);

        $allowedSorts = [
            'name',
            'legal_name',
            'vat_number',
            'tax_code',
            'is_active',
            'created_at',
        ];

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'name';
        }

        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        if (! in_array($perPage, [10, 20, 50], true)) {
            $perPage = 10;
        }

        $organizations = Organization::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('legal_name', 'like', "%{$search}%")
                        ->orWhere('vat_number', 'like', "%{$search}%")
                        ->orWhere('tax_code', 'like', "%{$search}%");
                });
            })
            ->when($status !== null && $status !== '', function ($query) use ($status) {
                if ($status === 'active') {
                    $query->where('is_active', true);
                }

                if ($status === 'inactive') {
                    $query->where('is_active', false);
                }
            })
            ->orderBy($sort, $direction)
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        return view('organizations.index', compact(
            'organizations',
            'search',
            'status',
            'sort',
            'direction',
            'perPage'
        ));
    }

    public function create()
    {
        $organizationTypes = OrganizationType::orderBy('name')->get();

        return view('organizations.create', compact('organizationTypes'));
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'organization_type_id' => ['required', 'exists:organization_types,id'],
            'vat_number' => ['nullable', 'string', 'max:50'],
            'tax_code' => ['nullable', 'string', 'max:50'],
            'sdi_code' => ['nullable', 'string', 'max:20'],
            'is_split_payment' => ['nullable', 'boolean'],
            'is_active' => ['required', 'boolean'],
        ]);

        if (blank($validated['name'] ?? null) && blank($validated['legal_name'] ?? null)) {
            return back()
                ->withErrors([
                    'name' => 'Compila almeno Nome oppure Ragione sociale.',
                ])
                ->withInput();
        }

        $validated['is_split_payment'] = $request->boolean('is_split_payment');
        $validated['is_active'] = $request->boolean('is_active');

        $organization = Organization::create($validated);

        return redirect()
            ->route('organizations.show', $organization)
            ->with('success', 'Organizzazione creata con successo.');
    }


    public function edit(Organization $organization)
    {
        $organizationTypes = OrganizationType::orderBy('name')->get();

        return view('organizations.edit', compact('organization', 'organizationTypes'));
    }

    public function update(Request $request, Organization $organization)
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'organization_type_id' => ['required', 'exists:organization_types,id'],
            'vat_number' => ['nullable', 'string', 'max:50'],
            'tax_code' => ['nullable', 'string', 'max:50'],
            'sdi_code' => ['nullable', 'string', 'max:20'],
            'is_split_payment' => ['nullable', 'boolean'],
            'is_active' => ['required', 'boolean'],
        ]);

        if (blank($validated['name'] ?? null) && blank($validated['legal_name'] ?? null)) {
            return back()
                ->withErrors([
                    'name' => 'Compila almeno Nome oppure Ragione sociale.',
                ])
                ->withInput();
        }

        $validated['is_split_payment'] = $request->boolean('is_split_payment');
        $validated['is_active'] = $request->boolean('is_active');

        $organization->update($validated);

        return redirect()
            ->route('organizations.show', $organization)
            ->with('success', 'Organizzazione aggiornata con successo.');
    }

    public function destroy(Organization $organization)
    {
        $organization->delete();

        return redirect()
            ->route('organizations.index')
            ->with('success', 'Organizzazione eliminata con successo.');
    }
}