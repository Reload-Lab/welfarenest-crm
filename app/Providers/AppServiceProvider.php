<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Models\Person;
use App\Models\ContactPoint;


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
        ]);
        Paginator::useBootstrapFive();
    }
}
