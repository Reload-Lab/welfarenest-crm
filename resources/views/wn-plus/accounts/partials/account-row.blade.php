@php
    use App\Models\ConsentType;

    /**
     * Una riga account dell'elenco WN+.
     *
     * $account   WnPlusAccount da mostrare
     * $level     'manager' | 'user' — decide rientro e peso visivo della riga
     * $groupKey  chiave del gruppo organizzazione (serve al JS per mostrare/nascondere)
     * $managerKey  chiave del referente: sulla riga del referente identifica il suo
     *              gruppo di utenti, sulla riga di un utente dice da chi dipende
     * $users     utenti invitati (solo per le righe referente)
     */
    $level = $level ?? 'user';
    $users = $users ?? collect();
    $managerKey = $managerKey ?? null;
    $isManager = $level === 'manager';
@endphp

<tr
    class="crm-table__row crm-wnplus-row crm-wnplus-row--{{ $level }}"
    data-wnplus-row
    data-wnplus-org="{{ $groupKey }}"
    @if(! $isManager && $managerKey) data-wnplus-manager="{{ $managerKey }}" @endif
>
    <td class="crm-cell-start">
        <div class="d-flex align-items-center gap-2 crm-wnplus-name">
            @if($isManager && $users->isNotEmpty())
                <button type="button"
                        class="btn btn-icon crm-wnplus-toggle"
                        data-wnplus-toggle="{{ $managerKey }}"
                        aria-expanded="false"
                        title="Mostra/nascondi gli utenti di questo referente"
                        aria-label="Mostra/nascondi gli utenti di questo referente">
                    <x-icon group="actions" name="chevron-right" class="crm-wnplus-toggle-icon" />
                </button>
            @else
                <span class="crm-wnplus-toggle-spacer" aria-hidden="true"></span>
            @endif

            <x-crm.avatar :name="$account->full_name" type="person" size="sm" />

            <div class="min-w-0">
                <a href="{{ route('wn-plus.accounts.show', $account) }}"
                   class="crm-entity-link d-inline-block text-truncate">
                    {{ $account->full_name }}
                </a>

                @if($isManager && $users->isNotEmpty())
                    <span class="crm-text-muted small">
                        ({{ $users->count() }} {{ $users->count() === 1 ? 'utente' : 'utenti' }})
                    </span>
                @endif

                <span class="crm-meta-text text-truncate">{{ $account->email }}</span>
            </div>
        </div>
    </td>

    <td>
        <x-crm.tag
            :label="$isManager ? 'Referente' : 'Utente'"
            :variant="$isManager ? 'primary' : 'default'"
        />
    </td>

    <td class="text-center">
        {{-- Stessa forma e stessi toni della colonna Stato qui accanto:
             prima questa cella usava crm-status-badge (pillola piena) e le due
             icone sembravano appartenere a due interfacce diverse. --}}
        <button
            type="button"
            class="crm-status-icon crm-status-icon--{{ $account->consentBadgeVariant(ConsentType::PRIVACY_NOTICE) }} crm-status-icon--button"
            title="{{ $account->consentStatusLabel(ConsentType::PRIVACY_NOTICE) }}"
            aria-label="{{ $account->consentStatusLabel(ConsentType::PRIVACY_NOTICE) }}"
            data-bs-toggle="modal"
            data-bs-target="#wnplusConsentsModal{{ $account->id }}">
            <x-icon group="entities" name="consent" />
        </button>
    </td>

    <td class="text-center">
        <x-crm.status
            :label="$account->statusLabel()"
            :variant="$account->statusBadgeVariant()"
            icon-group="status"
            :icon-name="$account->statusIcon()"
            mode="icon"
        />
    </td>

    <td>
        <span class="text-nowrap">{{ $account->last_login_at?->format('d/m/Y H:i') ?? '—' }}</span>
    </td>

    <td class="text-end crm-cell-end">
        <x-crm.row-actions
            :view="route('wn-plus.accounts.show', $account)"
            :edit="route('wn-plus.accounts.edit', $account)"
            :delete="route('wn-plus.accounts.destroy', $account)"
            delete-confirm="Confermi l'eliminazione di questo account WN+?"
            :actions="[
                [
                    'label' => 'Genera invito',
                    'route' => route('wn-plus.accounts.invite', $account),
                    'method' => 'POST',
                    'icon' => 'send',
                    'show' => $account->status !== 'active',
                ],
                [
                    'route' => route('wn-plus.accounts.suspend', $account),
                    'label' => 'Sospendi account',
                    'icon' => 'archive',
                    'show' => ! in_array($account->status, ['suspended', 'disabled'], true),
                ],
                [
                    'route' => route('wn-plus.accounts.reactivate', $account),
                    'label' => 'Riattiva account',
                    'icon' => 'archive-restore',
                    'show' => in_array($account->status, ['suspended', 'disabled'], true),
                ],
                [
                    'route' => route('wn-plus.accounts.disable', $account),
                    'label' => 'Disabilita account',
                    'icon' => 'close',
                    'show' => $account->status !== 'disabled',
                ],
            ]"
        />
    </td>
</tr>
