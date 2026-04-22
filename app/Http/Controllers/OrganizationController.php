<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Organization;
use App\Models\OrganizationType;
use App\Models\OrganizationRole;
use App\Models\Person;
use App\Models\Qualification;
use App\Models\ContactType;
use App\Models\ContactUsage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $term = trim((string) $request->input('q', ''));

        $organizations = Organization::query()
            ->when($term !== '', function ($query) use ($term) {
                $query->where(function ($q) use ($term) {
                    $q->where('name', 'like', "%{$term}%")
                        ->orWhere('legal_name', 'like', "%{$term}%");
                });
            })
            ->orderByRaw('COALESCE(name, legal_name)')
            ->limit(10)
            ->get(['id', 'name', 'legal_name'])
            ->map(fn (Organization $organization) => [
                'id' => $organization->id,
                'text' => $organization->name ?: $organization->legal_name ?: ('#' . $organization->id),
            ])
            ->values();

        return response()->json($organizations);
    }

public function show(Request $request, Organization $organization)
{
    $organization->load([
        'organizationType',
        'personOrganizationRelations' => function ($query) {
            $query->with([
                'person',
                'qualification',
                'department',
            ])->orderByDesc('is_active')
              ->orderBy('id');
        },
        'contactPoints' => function ($query) {
            $query->with([
                'contactType',
                'contactUsage',
            ])->orderByDesc('is_primary')
              ->orderByDesc('is_active')
              ->orderBy('id');
        },
        // 'addresses.addressType',
        // 'notes.author',
    ]);

    $selectedPerson = null;

    if ($personId = $request->old('person_id')) {
        $selectedPerson = Person::query()->find($personId);
    }

    $qualifications = Qualification::query()
        ->where('is_active', true)
        ->orderBy('sort_order')
        ->orderBy('name')
        ->get();

    $departments = Department::query()
        ->where('is_active', true)
        ->orderBy('sort_order')
        ->orderBy('name')
        ->get();

    $contactTypes = ContactType::query()
        ->where('is_active', true)
        ->orderBy('sort_order')
        ->orderBy('name')
        ->get();

    $contactUsages = ContactUsage::query()
        ->where('is_active', true)
        ->orderBy('sort_order')
        ->orderBy('name')
        ->get();

    return view('organizations.show', compact(
        'organization',
        'selectedPerson',
        'qualifications',
        'departments',
        'contactTypes',
        'contactUsages'
    ));
}

    public function clients(Request $request)
    {
        return $this->organizationIndexByScope($request, 'client');
    }

    public function suppliers(Request $request)
    {
        return $this->organizationIndexByScope($request, 'supplier');
    }

    public function index(Request $request)
    {
        return $this->organizationIndexByScope($request, null);
    }

    protected function organizationIndexByScope(Request $request, ?string $scope = null)
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

        $query = Organization::query();

        if ($scope === 'client') {
            $query->whereHas('organizationRoles', function ($q) {
                $q->where('code', 'client');
            });
        }

        if ($scope === 'supplier') {
            $query->whereHas('organizationRoles', function ($q) {
                $q->where('code', 'supplier');
            });
        }

        $organizations = $query
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

        $pageTitle = 'Organizzazioni';
        $pageSubtitle = 'Gestione anagrafiche organizzazioni';
        $pageHeading = 'Organizzazioni';
        $createLabel = 'Nuova organizzazione';
        $indexRoute = 'organizations.index';

        if ($scope === 'client') {
            $pageTitle = 'Clienti';
            $pageSubtitle = 'Gestione anagrafiche clienti';
            $pageHeading = 'Anagrafiche clienti';
            $createLabel = 'Nuovo cliente';
            $indexRoute = 'clients.index';
        }

        if ($scope === 'supplier') {
            $pageTitle = 'Fornitori';
            $pageSubtitle = 'Gestione anagrafiche fornitori';
            $pageHeading = 'Anagrafiche fornitori';
            $createLabel = 'Nuovo fornitore';
            $indexRoute = 'suppliers.index';
        }

        return view('organizations.index', compact(
            'organizations',
            'search',
            'status',
            'sort',
            'direction',
            'perPage',
            'scope',
            'pageTitle',
            'pageSubtitle',
            'pageHeading',
            'createLabel',
            'indexRoute'
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
            'roles' => ['nullable', 'array'],
            'roles.*' => ['in:client,supplier'],
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

        $roleIds = OrganizationRole::query()
            ->whereIn('code', $request->input('roles', []))
            ->pluck('id');

        $organization->organizationRoles()->sync($roleIds);

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
            'roles' => ['nullable', 'array'],
            'roles.*' => ['in:client,supplier'],
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

        $roleIds = OrganizationRole::query()
            ->whereIn('code', $request->input('roles', []))
            ->pluck('id');

        $organization->organizationRoles()->sync($roleIds);        

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
