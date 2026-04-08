<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Organization;
use App\Models\Person;
use App\Models\Qualification;
use Illuminate\Http\Request;

class PersonController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $sort = $request->input('sort', 'last_name');
        $direction = $request->input('direction', 'asc');
        $perPage = (int) $request->input('per_page', 10);

        $allowedSorts = [
            'first_name',
            'last_name',
            'created_at',
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
            })
            ->orderBy($sort, $direction)
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        return view('people.index', compact(
            'people',
            'search',
            'sort',
            'direction',
            'perPage'
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

        return view('people.show', compact(
            'person',
            'organizations',
            'qualifications',
            'departments'
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
}
