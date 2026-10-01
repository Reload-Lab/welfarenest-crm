@php
    /**
     * Un gruppo dell'elenco: un'organizzazione con i suoi referenti e, annidati
     * sotto a ciascuno, gli utenti che ha invitato.
     *
     * $group  array prodotto da WnPlusAccountController::buildAccountGroup()
     */
    $organization = $group['organization'];
    $groupKey = $group['key'];
@endphp

<tr class="crm-wnplus-org-row" data-wnplus-org-header="{{ $groupKey }}">
    <td class="crm-cell-start crm-cell-end" colspan="6">
        <div class="d-flex align-items-center gap-2">
            <button type="button"
                    class="btn btn-icon crm-wnplus-org-toggle"
                    data-wnplus-org-toggle="{{ $groupKey }}"
                    aria-expanded="true"
                    title="Mostra/nascondi gli account di questa organizzazione"
                    aria-label="Mostra/nascondi gli account di questa organizzazione">
                <x-icon group="actions" name="chevron-right" class="crm-wnplus-toggle-icon" />
            </button>

            @if($organization)
                <x-crm.avatar
                    :name="$organization->display_name"
                    :image="$organization->avatar_url"
                    type="organization"
                    size="sm"
                />

                <a href="{{ route('organizations.show', $organization) }}" class="crm-wnplus-org-name">
                    {{ $organization->display_name }}
                </a>
            @else
                <x-crm.avatar name="?" type="organization" size="sm" />

                <span class="crm-wnplus-org-name crm-wnplus-org-name--empty">
                    Senza organizzazione collegata
                </span>
            @endif

            <span class="crm-wnplus-org-counts crm-text-muted small ms-auto">
                {{ $group['managersCount'] }}
                {{ $group['managersCount'] === 1 ? 'referente' : 'referenti' }}
                ·
                {{ $group['usersCount'] }}
                {{ $group['usersCount'] === 1 ? 'utente' : 'utenti' }}
            </span>
        </div>
    </td>
</tr>

@foreach($group['managers'] as $managerRow)
    @php($managerKey = $groupKey . '-mgr-' . $managerRow['manager']->id)

    @include('wn-plus.accounts.partials.account-row', [
        'account' => $managerRow['manager'],
        'level' => 'manager',
        'groupKey' => $groupKey,
        'managerKey' => $managerKey,
        'users' => $managerRow['users'],
    ])

    @foreach($managerRow['users'] as $user)
        @include('wn-plus.accounts.partials.account-row', [
            'account' => $user,
            'level' => 'user',
            'groupKey' => $groupKey,
            'managerKey' => $managerKey,
            'users' => collect(),
        ])
    @endforeach
@endforeach

@if($group['looseUsers']->isNotEmpty())
    @if(count($group['managers']) > 0)
        <tr class="crm-wnplus-loose-divider"
            data-wnplus-row
            data-wnplus-org="{{ $groupKey }}">
            <td colspan="6" class="crm-cell-start crm-cell-end">
                Senza referente assegnato
            </td>
        </tr>
    @endif

    @foreach($group['looseUsers'] as $user)
        @include('wn-plus.accounts.partials.account-row', [
            'account' => $user,
            'level' => 'user',
            'groupKey' => $groupKey,
            'managerKey' => null,
            'users' => collect(),
        ])
    @endforeach
@endif
