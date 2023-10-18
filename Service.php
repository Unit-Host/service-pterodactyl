<?php

namespace App\Services\Pterodactyl;

use App\Services\Pterodactyl\Entities\Egg;
use App\Services\Pterodactyl\Entities\Pterodactyl;
use App\Services\Pterodactyl\Entities\Location;
use App\Models\Package;
use App\Models\Order;
use App\Models\ErrorLog;
use App\Services\Pterodactyl\Entities\Server;
use Illuminate\Contracts\Container\BindingResolutionException;

class Service
{
    private Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

        /**
     * Returns the meta data about this Server/Service
     *
     * @return object
     */
    public static function metaData(): object
    {
        return (object)
        [
          'display_name' => 'Pterodactyl',
          'author' => 'WemX',
          'version' => '1.0.0',
          'wemx_version' => ['dev', '>=1.8.0'],
        ];
    }

    /**
     * Define the default configuration values required to setup this service
     * i.e host, api key, or other values. Use Laravel validation rules for
     *
     * Laravel validation rules: https://laravel.com/docs/10.x/validation
     *
     * @return collection
     */
    public static function setConfig()
    {
        return collect([
            "encrypted::pterodactyl::api_url" => [
                "name" => "Host",
                "description" => "The hostname of your Pterodactyl Panel i.e panel.example.com",
                "type" => "text", // text, textarea, password, number, date, checkbox, url, email, select
                "rules" => ['required'], // laravel validation rules
            ],
            "encrypted::pterodactyl::api_key" => [
                "name" => "API Key",
                "description" => "The API key for your Pterodactyl Panel",
                "type" => "password",
                "rules" => ['required'], // laravel validation rules
            ],
            "encrypted::pterodactyl::sso_secret" => [
                "name" => "SSO Secret Key",
                "description" => "The SSO key used for automating logging in to Pterodactyl Panel",
                "type" => "password",
                "rules" => ['nullable'], // laravel validation rules
            ]
        ]);
    }

    /**
     * Define the default package configuration values required when creatig
     * new packages. i.e maximum ram usage, allowed databases and backups etc.
     *
     * Laravel validation rules: https://laravel.com/docs/10.x/validation
     *
     * @return collection
     */
    public static function setPackageConfig()
    {
        return collect([
            [
                "col" => "col-4",
                "key" => "database_limit",
                "name" => "Database Limit",
                "description" => "The total number of databases a user is allowed to create for this server on Pterodactyl Panel.",
                "type" => "number",
                "min" => 0,
                "rules" => ['required'], // laravel validation rules
            ],
            [
                "col" => "col-4",
                "key" => "allocation_limit",
                "name" => "Allocation Limit",
                "description" => "The total number of allocations a user is allowed to create for this server Pterodactyl Panel.",
                "type" => "number",
                "min" => 0,
                "rules" => ['required'],
            ],
            [
                "col" => "col-4",
                "key" => "backup_limit",
                "name" => "Backup Limit",
                "description" => "The total number of backups that can be created for this server Pterodactyl Panel.",
                "type" => "number",
                "min" => 0,
                "rules" => ['required'],
            ],
            [
                "col" => "col-4",
                "key" => "cpu_limit",
                "name" => "CPU Limit in %",
                "description" => "If you do not want to limit CPU usage, set the value to0. To use a single thread set it to 100%, for 4 threads set to 400% etc",
                "type" => "number",
                "min" => 0,
                "rules" => ['required'],
            ],
            [
                "col" => "col-4",
                "key" => "memory_limit",
                "name" => "Memory Limit in MB",
                "description" => "The maximum amount of memory allowed for this container. Setting this to 0 will allow unlimited memory in a container.",
                "type" => "number",
                "min" => 0,
                "rules" => ['required'],
            ],
            [
                "col" => "col-4",
                "key" => "disk_limit",
                "name" => "Disk Limit in MB",
                "description" => "The maximum amount of memory allowed for this container. Setting this to 0 will allow unlimited memory in a container.",
                "type" => "number",
                "min" => 0,
                "rules" => ['required'],
            ],
            [
                "col" => "col-4",
                "key" => "cpu_pinning",
                "name" => "CPU Pinning (optional)",
                "description" => __('admin.cpu_pinning_desc'),
                "type" => "text",
                "rules" => ['nullable'],
            ],
            [
                "col" => "col-4",
                "key" => "swap_limit",
                "name" => __('admin.swap'),
                "description" => __('admin.swap_desc'),
                "type" => "number",
                "default_value" => 0,
                "rules" => ['required'],
            ],
            [
                "col" => "col-4",
                "key" => "block_io_weight",
                "name" => __('admin.block_io_weight'),
                "description" =>  __('admin.block_io_weight_desc'),
                "type" => "number",
                "default_value" => 500,
                "rules" => ['required'],
            ],

            // locations
            [
                "key" => "locations[]",
                "name" => __('admin.allowed_locations'),
                "description" =>  __('admin.allowed_locations_desc'),
                "type" => "select",
                "options" => self::getLocations(),
                "multiple" => true,
                "rules" => ['required'],
            ],

            [
                "key" => "egg",
                "name" => __('admin.egg'),
                "description" =>  __('admin.egg_desc'),
                "type" => "select",
                "options" => self::getEggs(),
                "rules" => ['required'],
            ],
        ]);
    }

    protected static function getLocations()
    {
        return Location::all()->mapWithKeys(function ($location) {
            return [$location->id => $location->name];
        });
    }

    protected static function getEggs()
    {
        return Pterodactyl::getEggs()->pluck('attributes')->mapWithKeys(function ($egg) {
            return [$egg['id'] => $egg['name']];
        });
    }

    /**
     * This function is responsible for creating an instance of the
     * service. This can be anything such as a server, vps or any other instance.
     *
     * @param array $data
     * @return void
     * @throws BindingResolutionException
     */
    public function create(array $data = []): void
    {
        $server = new Server($this->order);
        $server->create();
        if ($this->location()->stock !== -1) {
            $this->location()->decrement('stock', 1);
        }
    }

    /**
     * Retrieve the key value configured by admins for this package
     */
    private function package($key, $default = NULL)
    {
        if (isset($this->order->package['data'][$key])) {
            return $this->order->package['data'][$key];
        }
        return $default;
    }

    /**
     * Retrieve data of selected egg for this package
     */
    private function egg()
    {
        return json_decode($this->order->package['data']['egg']);
    }

    /**
     * Retrieve selected location by the user if no location
     * is selected, deploy on best suitable location
     */
    private function location()
    {
        return isset($this->order->options['location'])
            ? Location::find($this->order->options['location'])
            : Location::where('stock', '!=', 0)->first();
    }

    /**
     * Retrieve custom options configured by user at checkout
     */
    private function option($key, $default = NULL)
    {
        if (isset($this->order->options[$key])) {
            return $this->order->options[$key];
        }

        return $default;
    }

    /**
     * This function is responsible for suspending an instance of the
     * service. This method is called when a order is expired or
     * suspended by an admin
     *
     * @return void
     */
    public function suspend(array $data = []): void
    {
        $server = $this->server();
        Pterodactyl::api()->servers->suspend($server['id']);
    }

    /**
     * This function is responsible for unsuspending an instance of the
     * service. This method is called when a order is activated or
     * unsuspended by an admin
     *
     * @return void
     */
    public function unsuspend(array $data = []): void
    {
        $server = $this->server();
        Pterodactyl::api()->servers->unsuspend($server['id']);
    }

    /**
     * This function is responsible for deleting an instance of the
     * service. This can be anything such as a server, vps or any other instance.
     *
     * @return void
     */
    public function terminate(array $data = []): void
    {
        $server = $this->server();
        Pterodactyl::api()->servers->delete($server['id']);
    }

    /**
     * This function retrieves the Pterodactyl server belonging to this order
     * directly from the Pterodactyl API and returns the atributes of that server.
     *
     * @return Server
     */
    private function server()
    {
        return Pterodactyl::server($this->order->id);
    }

}
