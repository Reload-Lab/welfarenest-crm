<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use App\Listeners\AccessLogSubscriber;
use App\Models\Consent;
use App\Models\Organization;
use App\Models\Person;
use App\Models\PersonOrganizationRelation;
use App\Models\ContactPoint;
use App\Models\Lead;
use App\Models\WnPlusAccount;
use App\Models\User;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(\App\Services\Import\LookupResolver::class);
    }

    public function boot(): void
    {
        Relation::morphMap([
        'person' => Person::class,
        'contact_point' => ContactPoint::class,
        'lead' => Lead::class,
        'wn_plus_account' => WnPlusAccount::class,
        // Aggiunti per i log: getMorphClass() deve restituire le stesse
        // stringhe controllate gia usate come owner_type, mai la FQCN.
        'organization' => Organization::class,
        'person_organization_relation' => PersonOrganizationRelation::class,
        'consent' => Consent::class,
        ]);
        Paginator::useBootstrapFive();

        Event::subscribe(AccessLogSubscriber::class);

        /*
         | Chi puo consultare i registri.
         |
         | Finche non esiste il sistema di ruoli (Priorita 4), qualunque utente
         | CRM autenticato puo vedere i log. Quando i ruoli arriveranno bastera
         | cambiare il corpo di questa closure: route, view e controller usano
         | gia 'view-logs' e non vanno toccati.
         */
        Gate::define('view-logs', fn (User $user) => true);
    }
}
