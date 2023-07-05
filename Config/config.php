<?php

return [

    'name' => 'Pterodactyl Module',
    'icon' => 'https://imgur.png',
    'author' => 'WemX',
    'version' => '1.0.0',
    'wemx_version' => '1.0.0',

    'service' => \App\Services\Pterodactyl\Service::class,
    'controller' => \App\Services\Pterodactyl\Http\Controllers\PterodactylController::class,
    
    'handlers' => [
        'new_order' => \App\Services\Pterodactyl\Handlers\NewOrder::class,
        'renewal' => \App\Services\Pterodactyl\Handlers\Renewal::class,
        'cancel' => \App\Services\Pterodactyl\Handlers\Cancel::class,
    ],

    'elements' => [

        'admin_menu' => 
        [

            [
                'name' => 'Pterodactyl',
                'icon' => '<i class="fas fa-solid fa-dragon"></i>',
                'type' => 'dropdown',
                'items' => [
                    [
                        'name' => 'Configuration',
                        'href' => '/admin/pterodactyl',
                    ],

                    [
                        'name' => 'Locations',
                        'href' => '/admin/pterodactyl/locations',
                    ],
                ],
            ],

        ],

    ],

];
