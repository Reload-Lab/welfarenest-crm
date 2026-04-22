<?php

namespace App\Http\Controllers;

use App\Models\ContactPoint;
use App\Models\ContactType;
use App\Models\Organization;
use App\Models\Person;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ContactPointController extends Controller
{
    public function storeForOrganization(Request $request, Organization $organization): RedirectResponse
    {
        return $this->storeForOwner(
            request: $request,
            ownerType: 'organization',
            ownerId: $organization->id,
            successRoute: route('organizations.show', $organization),
            errorBag: 'storeContactPoint',
            successMessage: 'Recapito aggiunto con successo.'
        );
    }

    public function storeForPerson(Request $request, Person $person): RedirectResponse
    {
        return $this->storeForOwner(
            request: $request,
            ownerType: 'person',
            ownerId: $person->id,
            successRoute: route('people.show', $person),
            errorBag: 'storeContactPoint',
            successMessage: 'Recapito aggiunto con successo.'
        );
    }

    public function destroy(ContactPoint $contactPoint): RedirectResponse
    {
        $redirectUrl = $this->resolveOwnerShowRoute(
            ownerType: $contactPoint->owner_type,
            ownerId: $contactPoint->owner_id
        );

        $contactPoint->delete();

        return redirect($redirectUrl)
            ->with('success', 'Recapito eliminato con successo.');
    }

    protected function storeForOwner(
        Request $request,
        string $ownerType,
        int $ownerId,
        string $successRoute,
        string $errorBag = 'default',
        string $successMessage = 'Recapito aggiunto con successo.'
    ): RedirectResponse {
        $validated = $request->validateWithBag($errorBag, [
            'contact_type_id' => ['required', 'exists:contact_types,id'],
            'value' => ['required', 'string', 'max:255'],
            'label' => ['nullable', 'string', 'max:255'],
            'contact_usage_id' => ['nullable', 'exists:contact_usages,id'],
            'is_primary' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $contactType = ContactType::query()->findOrFail($validated['contact_type_id']);

        $error = $this->validateValueByCategory(
            $contactType->category,
            $validated['value']
        );

        if ($error) {
            return back()
                ->withErrors(['value' => $error], $errorBag)
                ->withInput();
        }

        $isPrimary = $request->boolean('is_primary');
        $isActive = $request->boolean('is_active', true);

        if ($isPrimary) {
            ContactPoint::query()
                ->where('owner_type', $ownerType)
                ->where('owner_id', $ownerId)
                ->where('contact_type_id', $validated['contact_type_id'])
                ->update(['is_primary' => false]);
        }

        ContactPoint::create([
            'owner_type' => $ownerType,
            'owner_id' => $ownerId,
            'contact_type_id' => $validated['contact_type_id'],
            'contact_usage_id' => $validated['contact_usage_id'] ?? null,
            'value' => trim($validated['value']),
            'label' => filled($validated['label'] ?? null) ? trim($validated['label']) : null,
            'is_primary' => $isPrimary,
            'is_active' => $isActive,
        ]);

        return redirect($successRoute)
            ->with('success', $successMessage);
    }

    protected function validateValueByCategory(?string $category, string $value): ?string
    {
        $value = trim($value);

        return match ($category) {
            'email' => filter_var($value, FILTER_VALIDATE_EMAIL)
                ? null
                : 'Inserisci un indirizzo email valido.',

            'phone' => preg_match('/^[0-9\+\s\-\(\)\/\.]{5,30}$/', $value)
                ? null
                : 'Inserisci un numero di telefono valido.',

            'web' => filter_var($value, FILTER_VALIDATE_URL)
                ? null
                : 'Inserisci un URL valido (includi http:// o https://).',

            'social' => mb_strlen($value) >= 3
                ? null
                : 'Inserisci un valore valido per il contatto social.',

            default => null,
        };
    }

    protected function resolveOwnerShowRoute(string $ownerType, int $ownerId): string
    {
        return match ($ownerType) {
            'organization' => route('organizations.show', $ownerId),
            'person' => route('people.show', $ownerId),
            default => url()->previous(),
        };
    }
}