<?php

namespace App\Http\Controllers;

use App\Models\ContactPoint;
use App\Models\Lead;
use App\Models\Organization;
use App\Models\Person;
use App\Models\PersonOrganizationRelation;
use App\Models\Note; // usato dal riquadro "Note", al momento disattivato

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
                'label' => 'Istituzioni',
                'value' => Organization::whereHas('organizationRoles', fn ($q) => $q->where('code', 'institution'))->count(),
                'icon_group' => 'entities',
                'icon_name' => 'organization',
                'tone' => 'purple',
                'route' => 'institutions.index',
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
            // Riquadro "Note" disattivato per tenere la griglia piena: le card
            // stanno su tre colonne, e sei riquadri riempiono due righe esatte.
            // Da riattivare quando ne arriveranno altri due, così si torna a
            // nove e a tre righe piene. Nota: 'pink' non esiste in
            // dashboard.css — se si riattiva, dargli un tono definito.
            // [
            //     'label' => 'Note',
            //     'value' => Note::count(),
            //     'icon_group' => 'entities',
            //     'icon_name' => 'note',
            //     'tone' => 'pink',
            //     'route' => null,
            // ],

        ];

        return view('dashboard', compact('stats'));
    }
}