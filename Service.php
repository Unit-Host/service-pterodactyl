<?php

namespace App\Services\Pterodactyl;

use App\Services\ServiceInterface;
use App\Services\Pterodactyl\Entities\Egg;
use App\Services\Pterodactyl\Entities\Pterodactyl;
use App\Services\Pterodactyl\Entities\Location;
use App\Models\Package;
use App\Models\Order;
use App\Models\ErrorLog;
use App\Services\Pterodactyl\Entities\Server;
use Illuminate\Contracts\Container\BindingResolutionException;

class Service implements ServiceInterface
{
    private Order $order;

    /**
     * Unique key used to store settings
     * for this service.
     *
     * @return string
     */
    public static $key = 'pterodactyl';

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
          'autor' => 'WemX',
          'version' => '1.0.0',
          'wemx_version' => ['*'],
        ];
    }

    /**
     * Define the default configuration values required to setup this service
     * i.e host, api key, or other values. Use Laravel validation rules for
     *
     * Laravel validation rules: https://laravel.com/docs/10.x/validation
     *
     * @return array
     */
    public static function setConfig(): array
    {
        return [];
    }

    /**
     * Define the default package configuration values required when creatig
     * new packages. i.e maximum ram usage, allowed databases and backups etc.
     *
     * Laravel validation rules: https://laravel.com/docs/10.x/validation
     *
     * @return array
     */
    public static function setPackageConfig(): array
    {
        return [];
    }

    /**
     * Define the checkout config that is required at checkout and is fillable by
     * the client. Its important to properly sanatize all inputted data with rules
     *
     * Laravel validation rules: https://laravel.com/docs/10.x/validation
     *
     * @return array
     */
    public static function setCheckoutConfig(): array
    {
        return [];
    }

    /**
     * Define buttons shown at order management page
     *
     * @return array
     */
    public static function setServiceButtons(): array
    {
        $login_to_panel = settings('encrypted::pterodactyl::sso_secret') ? [
            "type" => 'default',
            "name" => __('client.login_to_panel'),
            "icon" => '<i class="bx bx-terminal"></i>',
            "color" => "primary",
            "href" => route('pterodactyl.login'),
            "target" => "_blank",
        ] : [];

        $server_ip = [
            "type" => 'default',
            "name" => Pterodactyl::serverIP(request()->order->id) ?? '127.0.0.1',
            "color" => "primary",
        ];

        return [$login_to_panel, $server_ip];
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
