<?php

namespace App\Http\Controllers;

use App\Models\Organization;
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
            ->to($this->redirectUrl($request, $person, $validated['organization_id']))
            ->with('success', 'Relazione aggiunta con successo.');
    }

    public function storeFromOrganization(Request $request, Organization $organization)
    {
        $person = Person::query()
            ->whereKey($request->validate([
                'person_id' => ['required', 'exists:people,id'],
            ])['person_id'])
            ->firstOrFail();

        $validated = $this->validateRelation($request, $person, $organization);

        $person->organizationRelations()->create($validated);

        return redirect()
            ->to($this->redirectUrl($request, $person, $organization->id))
            ->with('success', 'Relazione aggiunta con successo.');
    }

    public function update(Request $request, Person $person, PersonOrganizationRelation $relation)
    {
        abort_unless($relation->person_id === $person->id, 404);

        $validated = $this->validateRelation($request, $person);

        $relation->update($validated);

        return redirect()
            ->to($this->redirectUrl($request, $person, $validated['organization_id']))
            ->with('success', 'Relazione aggiornata con successo.');
    }

    public function updateFromOrganization(Request $request, Organization $organization, PersonOrganizationRelation $relation)
    {
        $person = Person::query()
            ->whereKey($request->validate([
                'person_id' => ['required', 'exists:people,id'],
            ])['person_id'])
            ->firstOrFail();

        abort_unless(
            $relation->organization_id === $organization->id
            && $relation->person_id === $person->id,
            404
        );

        $validated = $this->validateRelation($request, $person, $organization);

        $relation->update($validated);

        return redirect()
            ->to($this->redirectUrl($request, $person, $organization->id))
            ->with('success', 'Relazione aggiornata con successo.');
    }


    public function destroy(Person $person, PersonOrganizationRelation $relation)
    {
        abort_unless($relation->person_id === $person->id, 404);

        $organizationId = $relation->organization_id;

        $relation->delete();

        return redirect()
            ->to($this->redirectUrl(request(), $person, $organizationId))
            ->with('success', 'Relazione eliminata con successo.');
    }

    public function destroyFromOrganization(Organization $organization, PersonOrganizationRelation $relation)
    {
        abort_unless($relation->organization_id === $organization->id, 404);

        $person = $relation->person;
        $organizationId = $organization->id;

        $relation->delete();

        return redirect()
            ->to($this->redirectUrl(request(), $person, $organizationId))
            ->with('success', 'Relazione eliminata con successo.');
    }


    protected function validateRelation(Request $request, Person $person, ?Organization $organization = null): array
    {
        $organizationIdRules = ['required', 'exists:organizations,id'];

        if ($organization) {
            $organizationIdRules[] = Rule::in([$organization->id]);
        }

        $validated = $request->validate([
            'organization_id' => $organizationIdRules,
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

    protected function redirectUrl(Request $request, Person $person, int $organizationId): string
    {
        if ($request->input('return_to') === 'organization') {
            return route('organizations.show', $organizationId);
        }

        return route('people.show', $person);
    }
}
