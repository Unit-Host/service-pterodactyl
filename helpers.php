<?php

if (!function_exists('egg')) {
    function egg($egg_id = null): array|string
    {
        if ($egg_id == null){
            return App\Services\Pterodactyl\Entities\Egg::class;
        }
        return App\Services\Pterodactyl\Entities\Egg::getOne($egg_id);
    }
}


if (!function_exists('node')) {
    function node(): array|string
    {
        return App\Services\Pterodactyl\Entities\Node::class;
    }
}

//if (!function_exists('api')) {
//    function api(): \Gigabait\PteroApi\PteroApi
//    {
//        return \App\Services\Pterodactyl\Entities\Pterodactyl::api();
//    }
//}

if (!function_exists('pterodactyl')) {
    function pterodactyl()
    {
        return \App\Services\Pterodactyl\Entities\Pterodactyl::class;
    }
}

if (!function_exists('getPteroServerIp')) {
    function getPteroServerIp($order_id)
    {
        return \App\Services\Pterodactyl\Entities\Pterodactyl::serverIP($order_id);
    }
}
