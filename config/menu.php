<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Application Navigation Menu Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the navigation menu structure for the application.
    | Menus are filtered automatically based on user permissions.
    |
    | Structure:
    | - label: Display text
    | - route: Named route
    | - icon: Icon identifier (optional)
    | - permission: Required permission (optional)
    | - role: Required role (optional)
    | - children: Submenu items (optional)
    |
    */

    'main' => [
        [
            'label' => 'Tableau de bord',
            'route' => 'dashboard',
            'icon' => 'home',
            'active' => 'dashboard',
        ],
        [
            'label' => 'Tickets',
            'route' => 'tickets.index',
            'icon' => 'ticket',
            'active' => 'tickets.*',
        ],
        [
            'label' => 'Rapports',
            'route' => 'reports.agent-performance',
            'icon' => 'chart-bar',
            'permission' => 'view_reports',
            'active' => 'reports.*',
        ],
        [
            'label' => 'Administration',
            'icon' => 'cog',
            'type' => 'dropdown',
            // Dropdown will show if user has ANY of the child permissions
            // MenuService filters children automatically
            'active' => 'users.*|roles.*|services.*|permissions.*|settings.*',
            'children' => [
                [
                    'label' => 'Utilisateurs',
                    'route' => 'users.index',
                    'permission' => 'view_users',
                ],
                [
                    'label' => 'Rôles',
                    'route' => 'roles.index',
                    'permission' => 'view_roles',
                ],
                [
                    'label' => 'Services',
                    'route' => 'services.index',
                    'permission' => 'view_services',
                ],
                [
                    'label' => 'Permissions',
                    'route' => 'permissions.index',
                    'permission' => 'view_roles', // Same as roles
                ],
                [
                    'type' => 'separator',
                ],
                [
                    'label' => 'Paramètres',
                    'route' => 'settings.index',
                    'permission' => 'manage_settings',
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Profile Menu
    |--------------------------------------------------------------------------
    */
    'user' => [
        [
            'label' => 'Profile',
            'route' => 'profile.edit',
        ],
        [
            'type' => 'separator',
        ],
        [
            'label' => 'Déconnexion',
            'route' => 'logout',
            'method' => 'post',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Mobile/Responsive Navigation
    |--------------------------------------------------------------------------
    */
    'mobile' => [
        [
            'label' => 'Dashboard',
            'route' => 'dashboard',
            'active' => 'dashboard',
        ],
        [
            'label' => 'Tickets',
            'route' => 'tickets.index',
            'active' => 'tickets.*',
        ],
        [
            'label' => 'Rapports',
            'route' => 'reports.agent-performance',
            'permission' => 'view_reports',
            'active' => 'reports.*',
        ],
        [
            'label' => 'Utilisateurs',
            'route' => 'users.index',
            'permission' => 'view_users',
            'active' => 'users.*',
        ],
        [
            'label' => 'Rôles',
            'route' => 'roles.index',
            'permission' => 'view_roles',
            'active' => 'roles.*',
        ],
        [
            'label' => 'Services',
            'route' => 'services.index',
            'permission' => 'view_services',
            'active' => 'services.*',
        ],
        [
            'label' => 'Paramètres',
            'route' => 'settings.index',
            'permission' => 'manage_settings',
            'active' => 'settings.*',
        ],
    ],
];
