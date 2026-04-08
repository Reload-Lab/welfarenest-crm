<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\PersonOrganizationRelation;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PersonOrganizationRelationController extends Controller
{
    public function store(Request $request, Person $person)
    {
        $validated = $this->validateRelation($request, $person);

        $person->organizationRelations()->create($validated);

        return redirect()
            ->route('people.show', $person)
            ->with('success', 'Relazione aggiunta con successo.');
    }

    public function update(Request $request, Person $person, PersonOrganizationRelation $relation)
    {
        abort_unless($relation->person_id === $person->id, 404);

        $validated = $this->validateRelation($request, $person);

        $relation->update($validated);

        return redirect()
            ->route('people.show', $person)
            ->with('success', 'Relazione aggiornata con successo.');
    }

    protected function validateRelation(Request $request, Person $person): array
    {
        $validated = $request->validate([
            'organization_id' => ['required', 'exists:organizations,id'],
            'qualification_id' => ['nullable', 'exists:qualifications,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_primary' => ['nullable', 'boolean'],
            'is_active' => ['required', 'boolean'],
            'person_id' => ['nullable', Rule::in([$person->id])],
        ]);

        $validated['person_id'] = $person->id;
        $validated['is_primary'] = $request->boolean('is_primary');
        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}
