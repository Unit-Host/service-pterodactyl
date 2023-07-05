<?php

namespace App\Services\Pterodactyl\Http\Controllers\Pterodactyl\Application;

use App\Services\Pterodactyl\Http\Controllers\Pterodactyl\Pterodactyl;
use Illuminate\Http\Request;

class Server
{

    /**
     * Get all servers from Pterodactyl API
     *
     * @return data
     */
    public static function getAllServers()
    {
        return Pterodactyl::application()->api("/servers?include=egg,nest,allocations,user,node,location");
    }

    /**
     * Get a server from its id
     *
     * @return data
     */
    public static function get($server_id)
    {
        return Pterodactyl::application()->api("/servers/$server_id?include=egg,nest,allocations,user,node,location");
    }

    /**
     * Get a server from its id
     *
     * @return data
     */
    public static function getFromExternalId($external_id)
    {
        return Pterodactyl::application()->api("/servers/external/$external_id?include=egg,nest,allocations,user,node,location");
    }

    /**
     * create
     *
     * @return ApplicationAPI
     */
    public function create(array $data)
    {
        
    }

}