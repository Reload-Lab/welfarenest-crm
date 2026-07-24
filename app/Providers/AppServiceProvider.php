<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Models\Person;
use App\Models\ContactPoint;
use App\Models\WnPlusAccount;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Relation::morphMap([
        'person' => Person::class,
        'contact_point' => ContactPoint::class,
        'wn_plus_account' => WnPlusAccount::class,
        ]);
        Paginator::useBootstrapFive();
    }
}
