<?php

namespace App\Services\Pterodactyl;

use App\Facades\Service;
use App\Models\Settings;

class Pterodactyl extends Service
{
    /**
     * @var string $key
     */
    public $key = 'pterodactyl';


    public function __construct()
    {
        //
    }

    /**
     * Returns the meta data about this Server/Service
     *
     * @return object
     */
    protected function service(): object
    {
        return (object) 
        [
          'name' => 'Pterodactyl',
          'autor' => 'WemX',
          'version' => '1.0.0', 
          'wemx_version' => ['dev', '>=1.8.0'], // supported wemx versions
        ];
    }

    /**
     * This function returns a config value set by administrators such 
     * as host or api key. Use additional param to default to a value
     *
     * @return mixed
     */
    protected function config(string $key, $default = null): mixed
    {
        return Settings::get("{$this->key}::$key", $default);
    }

    /**
     * Define the default configuration values required to setup this service
     * i.e host, api key, or other values. Use Laravel validation rules for 
     *
     * Laravel validation rules: https://laravel.com/docs/10.x/validation
     * 
     * @return array
     */
    public function setConfig(): array 
    {
        return [
            [
                "key" => "host",
                "name" => "Host",
                "description" => "The hostname of your Pterodactyl Panel i.e panel.example.com",
                "type" => "text",
                "rules" => ['required'], // laravel validation rules
            ],
            [
                "key" => "api_key",
                "name" => "API Key",
                "description" => "The API key for your Pterodactyl Panel",
                "type" => "text",
                "rules" => ['required'], // laravel validation rules
            ],
            [
                "key" => "sso_key",
                "name" => "SSO Secret Key",
                "description" => "The API key for your Pterodactyl Panel",
                "type" => "text",
                "rules" => ['nullable'], // laravel validation rules
            ]
        ];
    }

    /**
     * Define the default package configuration values required when creatig
     * new packages. i.e maximum ram usage, allowed databases and backups etc.
     * 
     * Laravel validation rules: https://laravel.com/docs/10.x/validation
     *
     * @return array
     */
    public function setPackageConfig(): array 
    {
        return [
            [
                "key" => "database_limit",
                "name" => "Database Limit",
                "description" => "The total number of databases a user is allowed to create for this server on Pterodactyl Panel.",
                "type" => "number",
                "rules" => ['required'], // laravel validation rules
            ],
            [
                "key" => "allocation_limit",
                "name" => "Allocation Limit",
                "description" => "The total number of allocations a user is allowed to create for this server Pterodactyl Panel.",
                "type" => "number",
                "rules" => ['required'],
            ],
            [
                "key" => "backup_limit",
                "name" => "Backup Limit",
                "description" => "The total number of backups that can be created for this server Pterodactyl Panel.",
                "type" => "number",
                "rules" => ['required'],
            ],
            [
                "key" => "cpu_limit",
                "name" => "CPU Limit in %",
                "description" => "If you do not want to limit CPU usage, set the value to0. To use a single thread set it to 100%, for 4 threads set to 400% etc",
                "type" => "number",
                "rules" => ['required'],
            ],
            [
                "key" => "memory_limit",
                "name" => "Memory Limit in MB",
                "description" => "The maximum amount of memory allowed for this container. Setting this to 0 will allow unlimited memory in a container.",
                "type" => "number",
                "rules" => ['required'],
            ],
            [
                "key" => "disk_limit",
                "name" => "Disk Limit in MB",
                "description" => "The maximum amount of memory allowed for this container. Setting this to 0 will allow unlimited memory in a container.",
                "type" => "number",
                "rules" => ['required'],
            ],
        ];
    }

    /**
     * Define the checkout config that is required at checkout and is fillable by
     * the client. Its important to properly sanatize all inputted data with rules
     * 
     * Laravel validation rules: https://laravel.com/docs/10.x/validation
     *
     * @return array
     */
    public function setCheckoutConfig(): array 
    {
        return [];
    }

    /**
     * initialize connection with Pterodactyl
     *
     * @return PteroAPI
     * @throws BindingResolutionException
     */
    public function api(): PteroApi
    {
        return new PteroApi($this->config('api_key'), $this->config('host'));
    }

    /**
     * Test the connection to the API server
     *
     * @return mixed
     */
    public function testConnection()
    {
        return;
    }

    /**
     * This function is responsible for creating an instance of the
     * service.
     *
     * @return void
     */
    public function create(array $data = []): array
    {
        return [];
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
        //
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
        //
    }

    /**
     * This function is responsible for deleting an instance of the
     * service. This can be anything such as a server, vps or any other instance.
     *
     * @return void
     */
    public function terminate(array $data = []): void
    {
        //
    }

    /**
     * This function is responsible for creating an user on the
     * Server with similar details as authenticated user
     *
     * @return array
     */
    public function createUser(array $data = []): array
    {
        return [];
    }

    /**
     * Return the server from the Service Provider
     *
     * @return mixed
     */
    public function server(array $data = []): mixed
    {
        return [];
    }
}
