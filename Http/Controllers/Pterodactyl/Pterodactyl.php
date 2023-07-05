<?php

namespace App\Services\Pterodactyl\Http\Controllers\Pterodactyl;

use App\Services\Pterodactyl\Http\Controllers\Pterodactyl\Application\Application;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class Pterodactyl extends Controller
{
    /**
     * Return application api
     *
     * @return ApplicationAPI
     */
    public static function application()
    {
        return new Application;
    }

}