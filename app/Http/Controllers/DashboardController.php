<?php

namespace App\Http\Controllers;

use App\Models\ContactPoint;
use App\Models\Lead;
use App\Models\Organization;
use App\Models\Person;
use App\Models\PersonOrganizationRelation;
use App\Models\Note;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            [
                'label' => 'Clienti',
                'value' => Organization::whereHas('organizationRoles', fn ($q) => $q->where('code', 'client'))->count(),
                'icon_group' => 'entities',
                'icon_name' => 'client',
                'tone' => 'blue',
                'route' => 'clients.index',
            ],
            [
                'label' => 'Fornitori',
                'value' => Organization::whereHas('organizationRoles', fn ($q) => $q->where('code', 'supplier'))->count(),
                'icon_group' => 'entities',
                'icon_name' => 'supplier',
                'tone' => 'orange',
                'route' => 'suppliers.index',
            ],
            [
                'label' => 'Persone',
                'value' => Person::count(),
                'icon_group' => 'entities',
                'icon_name' => 'person',
                'tone' => 'teal',
                'route' => 'people.index',
            ],
            [
                'label' => 'Relazioni',
                'value' => PersonOrganizationRelation::count(),
                'icon_group' => 'entities',
                'icon_name' => 'relation',
                'tone' => 'indigo',
                'route' => null,
            ],
            [
                'label' => 'Recapiti',
                'value' => ContactPoint::count(),
                'icon_group' => 'contact',
                'icon_name' => 'contact_point',
                'tone' => 'green',
                'route' => null,
            ],
            [
                'label' => 'Note',
                'value' => Note::count(),
                'icon_group' => 'entities',
                'icon_name' => 'note',
                'tone' => 'pink',
                'route' => null,
            ],

        ];

        return view('dashboard', compact('stats'));
    }
}