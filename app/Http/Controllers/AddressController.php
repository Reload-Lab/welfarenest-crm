<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\AddressType;
use App\Models\Organization;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function storeForOrganization(Request $request, Organization $organization)
    {
        $data = $this->validateAddress($request);

        $this->handlePrimaryAddress(
            ownerType: 'organization',
            ownerId: $organization->id,
            addressTypeId: $data['address_type_id'],
            isPrimary: $request->boolean('is_primary')
        );

        Address::create([
            ...$data,
            'owner_type' => 'organization',
            'owner_id' => $organization->id,
            'is_primary' => $request->boolean('is_primary'),
        ]);

        return redirect()
            ->route('organizations.show', $organization)
            ->with('success', 'Indirizzo aggiunto correttamente.');
    }

    public function updateForOrganization(Request $request, Organization $organization, Address $address)
    {
        $this->ensureAddressBelongsToOwner($address, 'organization', $organization->id);

        $data = $this->validateAddress($request);

        $this->handlePrimaryAddress(
            ownerType: 'organization',
            ownerId: $organization->id,
            addressTypeId: $data['address_type_id'],
            isPrimary: $request->boolean('is_primary'),
            exceptAddressId: $address->id
        );

        $address->update([
            ...$data,
            'is_primary' => $request->boolean('is_primary'),
        ]);

        return redirect()
            ->route('organizations.show', $organization)
            ->with('success', 'Indirizzo aggiornato correttamente.');
    }

    public function destroyForOrganization(Organization $organization, Address $address)
    {
        $this->ensureAddressBelongsToOwner($address, 'organization', $organization->id);

        $address->delete();

        return redirect()
            ->route('organizations.show', $organization)
            ->with('success', 'Indirizzo eliminato correttamente.');
    }

    private function validateAddress(Request $request): array
    {
        return $request->validate([
            'address_type_id' => [
                'required',
                'integer',
                'exists:address_types,id',
            ],
            'label' => ['nullable', 'string', 'max:255'],
            'street' => ['nullable', 'string', 'max:255'],
            'street_number' => ['nullable', 'string', 'max:50'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'city' => ['nullable', 'string', 'max:120'],
            'province' => ['nullable', 'string', 'max:10'],
            'region' => ['nullable', 'string', 'max:120'],
            'country' => ['nullable', 'string', 'max:120'],
            'is_primary' => ['nullable', 'boolean'],
        ]);
    }

    private function handlePrimaryAddress(
        string $ownerType,
        int $ownerId,
        int $addressTypeId,
        bool $isPrimary,
        ?int $exceptAddressId = null
    ): void {
        if (! $isPrimary) {
            return;
        }

        Address::query()
            ->where('owner_type', $ownerType)
            ->where('owner_id', $ownerId)
            ->where('address_type_id', $addressTypeId)
            ->when($exceptAddressId, fn ($query) => $query->where('id', '!=', $exceptAddressId))
            ->update(['is_primary' => false]);
    }

    private function ensureAddressBelongsToOwner(Address $address, string $ownerType, int $ownerId): void
    {
        abort_unless(
            $address->owner_type === $ownerType && (int) $address->owner_id === $ownerId,
            404
        );
    }
}