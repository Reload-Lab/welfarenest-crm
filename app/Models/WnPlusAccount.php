<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Concerns\HasConsents;
use App\Models\Concerns\Auditable;

class WnPlusAccount extends Model
{

    use HasConsents;
    use Auditable;
    
    protected $fillable = [
        'uuid',
        'organization_id',
        'person_id',
        'first_name',
        'last_name',
        'email',
        'password',
        'wn_plus_role_id',
        'wn_plus_level_id',
        'status',
        'invited_by_account_id',
        'created_by_user_id',
        'email_verified_at',
        'last_login_at',
        'account_type',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(WnPlusRole::class, 'wn_plus_role_id');
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(WnPlusLevel::class, 'wn_plus_level_id');
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(WnPlusInvitation::class);
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(WnPlusAccount::class, 'invited_by_account_id');
    }

    public function invitedAccounts(): HasMany
    {
        return $this->hasMany(WnPlusAccount::class, 'invited_by_account_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Il tipo di account che corrisponde a un ruolo WN+.
     *
     * Il ruolo sta in due posti: la tabella wn_plus_roles (codici 'manager' e
     * 'user', quella che si vede nel menu a tendina) e la colonna account_type,
     * che è quella che comanda davvero — accesso all'area referente, permesso di
     * invitare, raggruppamento in elenco. Finché le due cose restano separate
     * devono almeno essere scritte insieme: questo metodo è l'unico punto in cui
     * si traduce l'una nell'altra, così create e update non possono divergere.
     *
     * Qualsiasi ruolo che non sia 'manager' vale come utente semplice: è il verso
     * prudente, perché un ruolo nuovo aggiunto alla tabella non deve regalare da
     * solo i permessi del referente.
     */
    public static function accountTypeForRole(?int $roleId): string
    {
        $code = WnPlusRole::query()
            ->whereKey($roleId)
            ->value('code');

        return $code === 'manager' ? 'manager' : 'user';
    }

    /**
     * Ha utenti invitati ancora in piedi? Vale per sospensione, disabilitazione,
     * eliminazione e retrocessione a utente semplice: sono tutti modi diversi di
     * togliere di mezzo un referente, e lasciare dietro utenti che nessuno può
     * più gestire dall'area riservata è lo stesso danno in tutti e quattro.
     */
    public function hasActiveInvitedAccounts(): bool
    {
        return $this->invitedAccounts()
            ->where('status', '!=', 'disabled')
            ->exists();
    }

    public function getManagedUsersCountAttribute(): int
    {
        return $this->invitedAccounts()
            ->where('account_type', 'user')
            ->count();
    }

    public function statusBadgeVariant(): string
    {
        return match ($this->status) {
            'active' => 'success',
            'invited' => 'warning',
            'suspended' => 'danger',
            'disabled' => 'muted',
            default => 'muted',
        };
    }

    public function statusIcon(): string
    {
        return match ($this->status) {
            'active' => 'active',
            'invited', 'suspended' => 'warning',
            'disabled' => 'inactive',
            default => 'inactive',
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'active' => 'Account attivo',
            'invited' => 'Invito in attesa',
            'suspended' => 'Account sospeso',
            'disabled' => 'Account disattivato',
            default => 'Stato sconosciuto',
        };
    }


}