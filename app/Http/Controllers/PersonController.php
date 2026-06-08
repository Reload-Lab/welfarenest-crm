<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Organization;
use App\Models\Person;
use App\Models\Qualification;
use App\Models\ContactType;
use App\Models\ContactUsage;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class PersonController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $term = trim((string) $request->input('q', ''));

        $people = Person::query()
            ->when($term !== '', function ($query) use ($term) {
                $query->where(function ($q) use ($term) {
                    $q->where('first_name', 'like', "%{$term}%")
                        ->orWhere('last_name', 'like', "%{$term}%")
                        ->orWhereRaw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) like ?", ["%{$term}%"]);
                });
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->limit(10)
            ->get(['id', 'first_name', 'last_name'])
            ->map(fn (Person $person) => [
                'id' => $person->id,
                'text' => $person->full_name ?: ('#' . $person->id),
            ])
            ->values();

        return response()->json($people);
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $qualificationId = $request->input('qualification_id');
        $departmentId = $request->input('department_id');
        $sort = $request->input('sort', 'last_name');
        $direction = $request->input('direction', 'asc');
        $perPage = (int) $request->input('per_page', 50);
        $qualificationId = $request->input('qualification_id');
        $departmentId = $request->input('department_id');

        $allowedSorts = [
            'first_name',
            'last_name',
            'created_at',
            'organization_relations_count',
        ];

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'last_name';
        }

        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        if (! in_array($perPage, [10, 20, 50], true)) {
            $perPage = 10;
        }

        $people = Person::query()
            ->withCount('organizationRelations')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                });
            })->when($qualificationId !== null && $qualificationId !== '', function ($query) use ($qualificationId) {
                $query->whereHas('organizationRelations', function ($q) use ($qualificationId) {
                    $q->where('qualification_id', $qualificationId);
                });
            })
            ->when($departmentId !== null && $departmentId !== '', function ($query) use ($departmentId) {
                $query->whereHas('organizationRelations', function ($q) use ($departmentId) {
                    $q->where('department_id', $departmentId);
                });
            })
            ->orderBy($sort, $direction)
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->withQueryString();



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


        return view('people.index', compact(
            'people',
            'search',
            'sort',
            'direction',
            'perPage',
            'qualificationId',
            'departmentId',
            'qualifications',
            'departments',
        ));
    }

    public function create()
    {
        return view('people.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
        ]);

        $person = Person::create($validated);

        return redirect()
            ->route('people.show', $person)
            ->with('success', 'Persona creata con successo.');
    }

    public function show(Person $person)
    {
        $person->load([
            'organizationRelations.organization',
            'organizationRelations.qualification',
            'organizationRelations.department',
            'contactPoints.contactType',
            'contactPoints.contactUsage',
            'consents.consentType',
            'consents.consentVersion',
            'contactPoints.consents.consentType',
            'contactPoints.consents.consentVersion',
        ]);

        $organizations = Organization::query()
            ->orderByRaw('COALESCE(name, legal_name)')
            ->get();

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

        return view('people.show', compact(
            'person',
            'organizations',
            'qualifications',
            'departments',
            'contactTypes',
            'contactUsages',

        ));
    }

    public function edit(Person $person)
    {
        return view('people.edit', compact('person'));
    }

    public function update(Request $request, Person $person)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
        ]);

        $person->update($validated);

        return redirect()
            ->route('people.show', $person)
            ->with('success', 'Persona aggiornata con successo.');
    }

    public function destroy(Person $person)
    {
        $person->delete();

        return redirect()
            ->route('people.index')
            ->with('success', 'Persona eliminata con successo.');
    }

}
