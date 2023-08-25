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


//        $egg = Egg::query()->where('egg_id', $this->egg()->id)->first();
//        $environment = $egg->env($egg->env($this->option('environment', [])));
//
//        try {
//            $server = Pterodactyl::api()->servers->create([
//                'external_id' => (string) "wmx-". $this->order->id,
//                "name" => $this->order->name,
//                "user" => Pterodactyl::user($this->order->user)['id'],
//                "egg" => $egg->egg_id,
//                'oom_disabled' => ($this->package('OOM_KILLER', false)) ? true : false,
//                "docker_image" => $this->egg()->docker_image,
//                "startup" => $this->package('startup', $this->egg()->startup),
//                "environment" => array_merge($this->package('environment'), $this->option('environment', [])),
//                "limits" => [
//                    "memory" => $this->package('memory_limit', 0),
//                    "swap" => $this->package('swap', 0),
//                    "disk" => $this->package('disk_limit', 0),
//                    "io" => $this->package('block_io_weight', 500),
//                    "cpu" => $this->package('cpu_limit', 100),
//                ],
//                "feature_limits" => [
//                    "databases" => $this->package('database_limit', 0),
//                    "backups" => $this->package('backup_limit', 0),
//                    "allocations" => $this->package('allocation_limit', 0),
//                ],
//                'deploy' => [
//                    'locations' => [$this->location()->location_id],
//                    'dedicated_ip' => false,
//                    'port_range' => $this->package('port_range', []),
//                ],
//            ])->json();


//        } catch(\Exception $error) {
//            ErrorLog::create([
//                'user_id' => $this->order->user->id,
//                'order_id' => $this->order->id,
//                'source' => 'Pterodactyl Server Creation',
//                'severity' => 'CRITICAL',
//                'message' => "Failed to connect to Pterodactyl. Make sure the Ptero API details are correct. Error: ". $error->getMessage(),
//            ]);
//        }
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
