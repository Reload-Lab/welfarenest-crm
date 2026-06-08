<?php

return [

    'dashboard' => [
        'title' => 'Dashboard',
        'breadcrumbs' => [
            [
                'label' => 'Dashboard',
                'route' => 'dashboard',
                'icon' => ['group' => 'navigation', 'name' => 'dashboard'],
            ],
        ],
    ],

    'organizations.index' => [
        'title' => 'Organizzazioni',
        'breadcrumbs' => [
            [
                'label' => 'Dashboard',
                'route' => 'dashboard',
                'icon' => ['group' => 'navigation', 'name' => 'dashboard'],
            ],
            [
                'label' => 'Organizzazioni',
            ],
        ],
    ],

    'organizations.create' => [
        'title' => 'Nuova organizzazione',
        'breadcrumbs' => [
            [
                'label' => 'Dashboard',
                'route' => 'dashboard',
                'icon' => ['group' => 'navigation', 'name' => 'dashboard'],
            ],
            [
                'label' => 'Organizzazioni',
                'route' => 'organizations.index',
            ],
            [
                'label' => 'Nuova',
            ],
        ],
    ],

    'organizations.show' => [
        'title' => fn (array $params) => $params['organization']->display_name,
        'breadcrumbs' => [
            [
                'label' => 'Dashboard',
                'route' => 'dashboard',
                'icon' => ['group' => 'navigation', 'name' => 'dashboard'],
            ],
            [
                'label' => 'Organizzazioni',
                'route' => 'organizations.index',
            ],
            [
                'label' => fn (array $params) => $params['organization']->display_name,
            ],
        ],
    ],

    'organizations.edit' => [
        'title' => 'Modifica organizzazione',
        'breadcrumbs' => [
            [
                'label' => 'Dashboard',
                'route' => 'dashboard',
                'icon' => ['group' => 'navigation', 'name' => 'dashboard'],
            ],
            [
                'label' => 'Organizzazioni',
                'route' => 'organizations.index',
            ],
            [
                'label' => fn (array $params) => $params['organization']->display_name,
                'route' => fn (array $params) => route('organizations.show', $params['organization']),
            ],
            [
                'label' => 'Modifica',
            ],
        ],
    ],

    'people.index' => [
        'title' => 'Persone',
        'breadcrumbs' => [
            [
                'label' => 'Dashboard',
                'route' => 'dashboard',
                'icon' => ['group' => 'navigation', 'name' => 'dashboard'],
            ],
            [
                'label' => 'Persone',
            ],
        ],
    ],

'people.show' => [
    'title' => 'Persone',
    'breadcrumbs' => [
        [
            'label' => 'Dashboard',
            'route' => 'dashboard',
            'icon' => ['group' => 'navigation', 'name' => 'dashboard'],
        ],
        [
            'label' => 'Persone',
            'route' => 'people.index',
        ],
        [
            'label' => fn (array $params) => $params['person']->full_name ?: 'Scheda persona',
        ],
    ],
],

'people.create' => [
    'title' =>  'Nuova persona',
    'breadcrumbs' => [
        [
            'label' => 'Dashboard',
            'route' => 'dashboard',
            'icon' => ['group' => 'navigation', 'name' => 'dashboard'],
        ],
        [
            'label' => 'Persone',
            'route' => 'people.index',
        ],
        [
            'label' => 'Nuova persona',
        ],
    ],
],


'people.edit' => [
    'title' =>  'Nuova persona',
    'breadcrumbs' => [
        [
            'label' => 'Dashboard',
            'route' => 'dashboard',
            'icon' => ['group' => 'navigation', 'name' => 'dashboard'],
        ],
        [
            'label' => 'Persone',
            'route' => 'people.index',
        ],
        [
             'label' => fn (array $params) => $params['person']->full_name ?: 'Scheda persona',
        ],
    ],
],

'clients.index' => [
    'title' => 'Clienti',
    'breadcrumbs' => [
        [
            'label' => 'Dashboard',
            'route' => 'dashboard',
            'icon' => ['group' => 'navigation', 'name' => 'dashboard'],
        ],
        [
            'label' => 'Clienti',
        ],
    ],
],

'clients.create' => [
    'title' => 'Nuovo cliente',
    'breadcrumbs' => [
        [
            'label' => 'Dashboard',
            'route' => 'dashboard',
            'icon' => ['group' => 'navigation', 'name' => 'dashboard'],
        ],
        [
            'label' => 'Clienti',
            'route' => 'clients.index',
        ],
        [
            'label' => 'Nuovo',
        ],
    ],
],

'clients.edit' => [
    'title' => 'Modifica cliente',
    'breadcrumbs' => [
        [
            'label' => 'Dashboard',
            'route' => 'dashboard',
            'icon' => ['group' => 'navigation', 'name' => 'dashboard'],
        ],
        [
            'label' => 'Clienti',
            'route' => 'clients.index',
        ],
        [
            'label' => 'Modifica',
        ],
    ],
],

'suppliers.index' => [
    'title' => 'Fornitori',
    'breadcrumbs' => [
        [
            'label' => 'Dashboard',
            'route' => 'dashboard',
            'icon' => ['group' => 'navigation', 'name' => 'dashboard'],
        ],
        [
            'label' => 'Fornitori',
        ],
    ],
],

'suppliers.create' => [
    'title' => 'Nuovo fornitore',
    'breadcrumbs' => [
        [
            'label' => 'Dashboard',
            'route' => 'dashboard',
            'icon' => ['group' => 'navigation', 'name' => 'dashboard'],
        ],
        [
            'label' => 'Fornitori',
            'route' => 'suppliers.index',
        ], 
        [
            'label' => 'Nuovo',
        ],
    ],
],

'suppliers.edit' => [
    'title' => 'Modifica fornitore',
    'breadcrumbs' => [
        [
            'label' => 'Dashboard',
            'route' => 'dashboard',
            'icon' => ['group' => 'navigation', 'name' => 'dashboard'],
        ],
        [
            'label' => 'Fornitori',
            'route' => 'suppliers.index',
        ],
        [
            'label' => 'Modifica',
        ],
    ],
],

'clients.show' => [
    'title' => fn (array $params) => $params['organization']->display_name,
    'breadcrumbs' => [
        [
            'label' => 'Dashboard',
            'route' => 'dashboard',
            'icon' => ['group' => 'navigation', 'name' => 'dashboard'],
        ],
        [
            'label' => 'Clienti',
            'route' => 'clients.index',
        ],
        [
            'label' => fn (array $params) => $params['organization']->display_name,
        ],
    ],
],

'suppliers.show' => [
    'title' => fn (array $params) => $params['organization']->display_name,
    'breadcrumbs' => [
        [
            'label' => 'Dashboard',
            'route' => 'dashboard',
            'icon' => ['group' => 'navigation', 'name' => 'dashboard'],
        ],
        [
            'label' => 'Fornitori',
            'route' => 'suppliers.index',
        ],
        [
            'label' => fn (array $params) => $params['organization']->display_name,
        ],
    ],
],

];