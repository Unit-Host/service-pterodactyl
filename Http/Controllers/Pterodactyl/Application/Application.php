<?php

namespace App\Services\Pterodactyl\Http\Controllers\Pterodactyl\Application;

use App\Services\Pterodactyl\Http\Controllers\Pterodactyl\Application\Server;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Facades\Http;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class Application extends Controller
{
    public static $endpoint = '/api/application';

    /**
     * Return application api
     *
     * @return Server
     */
    public static function servers()
    {
        return new Server;
    }

    public static function api(string $endpoint, string $method = 'GET',  array $data = [])
    {
        // Prepare the headers with the bearer token
        $endpoint = settings('pterodactyl::api_url') . self::$endpoint .$endpoint;
        $headers = [
            'Authorization' => 'Bearer ' . settings('pterodactyl::api_key'),
            'Accept' => 'application/json',
        ];

        $method = strtolower($method);
        $allowedMethods = ['get', 'post', 'put', 'delete'];
        if (!in_array($method, $allowedMethods)) {
            throw new \InvalidArgumentException('Invalid HTTP method.');
        }
        
        $response = Http::withHeaders($headers)->$method($endpoint, $data);
        if($response->failed()) {
            if($response->object()->errors[0]->detail !== '') {
                return throw new \Exception("[Pterodactyl] " . $response->object()->errors[0]->detail);
            }

            return throw new \Exception("[Pterodactyl] API request failed - Ensure api details for Pterodactyl Service are configured");
        }

        // Return the response
        return $response->object();
    }
}