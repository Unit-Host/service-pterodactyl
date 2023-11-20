<?php

namespace App\Services\Pterodactyl\Entities;

use App\Models\ErrorLog;
use App\Models\Order;
use Exception;
use Gigabait\PteroApi\PteroApi;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Str;
use JetBrains\PhpStorm\NoReturn;

class Server
{
    private PteroApi $api;
    public ?Node $node = null;
    private Order $order;
    private string $external_id;
    private string $name;
    private int $user_id;
    private int $egg_id;
    private bool $oom_disabled;
    private string $docker_image;
    private string $startup;
    private array $environment;
    private array $limits;
    private array $feature_limits;
    private array $allocations_ids;
    private array $egg;
    private array $params;
    private int $port_count = 1;


    /**
     * @throws BindingResolutionException
     */
    #[NoReturn] public function __construct(Order $order)
    {
        $this->order = $order;
        $this->build();
    }


    /**
     * @throws BindingResolutionException
     * @throws Exception
     */
    public function create(): mixed
    {
        $this->generateParam();
        try {
            $server = $this->api()->servers->create($this->getParams());
            if (!$server->successful()) {
                throw new Exception('Error creating the server. API connect error ' . $server->getBody());
            }

            if (isset($server->json()['errors'])) {
                $details = '';
                foreach ($server->json()['errors'] as $error) {
                    ErrorLog::create([
                        'user_id' => $this->order->user->id,
                        'order_id' => $this->order->id,
                        'source' => 'Pterodactyl Server Creation',
                        'severity' => 'CRITICAL',
                        'message' => $error['detail'],
                    ]);
                    $details .= ' || ' . $error['detail'];
                }
                throw new Exception('Errors encountered while creating the server. Details: ' . $server->getBody());
            }
            return $server;
        } catch (Exception $e) {
            ErrorLog('pterodactyl::service::create', $e->getMessage());
            throw new Exception('Errors encountered while creating the server ' . $e->getMessage());
        }
    }

    public function egg(): array
    {
        if (!isset($this->egg)) {
            $this->egg = Egg::getOne($this->egg_id);
        }
        return $this->egg;
    }

    public function eggModel(): Egg
    {
        return $this->egg['model'];
    }

    /**
     * @throws Exception
     */
    public function node(): void
    {
//        $this->node = Node::query()->where('location_id', $this->location()->location_id)->first();
        $nodes = Node::query()->where('location_id', $this->location()->location_id)->get();
        $memory_limit = $this->order->package['data']['memory_limit'];
        $disk_limit = $this->order->package['data']['disk_limit'];
        foreach ($nodes as $node) {
            if ($node->checkResource($memory_limit, $disk_limit)) {
                $this->node = $node;
                return;
            }
        }
    }

    public function generateParam(): void
    {
        $this->params = [
            'external_id' => $this->getExternalId(),
            "name" => $this->getName(),
            'description' => settings('app_name', 'WemX') . " || {$this->getName()} || {$this->order->user->username}",
            "user" => $this->getUserId(),
            "egg" => $this->getEggId(),
            'oom_disabled' => $this->isOomDisabled(),
            "docker_image" => $this->getDockerImage(),
            "startup" => $this->getStartup(),
            "environment" => $this->getEnvironment(),
            "limits" => $this->getLimits(),
            "allocation" => $this->getAllocationsIds(),
            "feature_limits" => $this->getFeatureLimits(),
            'start_on_completion' => true,
        ];
    }

    /**
     * @throws BindingResolutionException
     * @throws Exception
     */
    private function build(): void
    {
        $this->setExternalId();
        $this->setName();
        $this->setUserId();
        $this->setEggId();
        $this->setOomDisabled();
        $this->setDockerImage();
        $this->setStartup();
        $this->setLimits();
        $this->setFeatureLimits();
        $this->setEnvironment();
        $this->setAllocationsIds();
        $this->egg();
        $this->node();
        $this->prepareEnvAllocations();
        $this->generateParam();
    }

    /**
     * @throws BindingResolutionException
     */
    private function api(): PteroApi
    {
        if (!isset($this->api)) {
            $this->api = Pterodactyl::api();
        }
        return $this->api;
    }

    /**
     * @throws Exception
     */
    private function prepareEnvAllocations(): void
    {
        $env = [];
        $replace_port_keys = [];
        $allocations_ids = [];
        foreach ($this->getEnvironment() as $key => $value) {
            if (str_contains($value, 'AUTO_PORT')) {
                $env[$key] = 'AUTO_PORT';
                $replace_port_keys[] = $key;
                $this->port_count = $this->port_count + 1;
            } elseif (str_contains($value, 'USERNAME')) {
                $env[$key] = str_replace('USERNAME', auth()->user()->username, $value);
            } elseif (str_contains($value, 'RANDOM_TEXT')) {
                $env[$key] = str_replace('RANDOM_TEXT', Str::random(10), $value);
            } elseif (str_contains($value, 'RANDOM_NUMBER')) {
                $env[$key] = str_replace('RANDOM_NUMBER', (int)substr(Str::random(10), 0, 10), $value);
            } elseif (str_contains($value, 'NODE_IP')) {
                $env[$key] = str_replace('NODE_IP', $this->node->ip, $value);
            } else {
                $env[$key] = $value;
            }
        }

        $i = -1;
        foreach ($this->node->fetchRequiredFreePorts($this->port_count) as $allocation_id => $port) {
            if ($i == -1) {
                $allocations_ids['default'] = $allocation_id;
                $i++;
                continue;
            }
            $env[$replace_port_keys[$i]] = $port;
            $allocations_ids['additional'][] = $allocation_id;
            $i++;
        }
        $this->setEnvironment($this->convertValuesAccordingToRules($env));
        $this->setAllocationsIds($allocations_ids);
    }

    private function arrayValueToString(array $array): array
    {
        foreach ($array as $key => &$value) {
            if (is_array($value)) {
                $value = $this->arrayValueToString($value);
            } else {
                $value = strval($value);
            }
        }
        return $array;
    }

    private function package(string $key, mixed $default = NULL): mixed
    {
        if (isset($this->order->package['data'][$key])) {
            return $this->order->package['data'][$key];
        }
        return $default;
    }

    private function location()
    {
        $location_id = $this->order->options['location'] ?? $this->package('locations')[0] ?? null;
        if (isset($location_id)) {
            return Location::find($location_id);
        }
        return Location::where('stock', '!=', 0)->first();
    }

    private function option(string $key, mixed $default = NULL): mixed
    {
        if (isset($this->order->options[$key])) {
            return $this->order->options[$key];
        }
        return $default;
    }

    private function convertValuesAccordingToRules($values): array
    {
        $output = [];
        $variables = $this->egg()['variables'];
        foreach ($variables as $var) {
            if (isset($values[$var['env_variable']])) {
                $rules = explode('|', $var['rules']);
                foreach ($rules as $rule) {
                    if (strpos($rule, 'string') !== false) {
                        $values[$var['env_variable']] = (string)$values[$var['env_variable']];
                    } else if (strpos($rule, 'boolean') !== false || strpos($rule, 'bool') !== false) {
                        $values[$var['env_variable']] = filter_var($values[$var['env_variable']], FILTER_VALIDATE_BOOLEAN);
                    } else if (strpos($rule, 'integer') !== false || strpos($rule, 'int') !== false) {
                        $values[$var['env_variable']] = (int)$values[$var['env_variable']];
                    } else if (strpos($rule, 'numeric') !== false) {
                        $values[$var['env_variable']] = (int)$values[$var['env_variable']];
                    } else if (strpos($rule, 'array') !== false) {
                        $values[$var['env_variable']] = (array)$values[$var['env_variable']];
                    }
                }
                $output[$var['env_variable']] = $values[$var['env_variable']];
            }
        }
        return $output;
    }


    //** SETTERS */
    public function setExternalId(string $external_id = ''): void
    {
        if (empty($external_id)) {
            $external_id = 'wmx-' . $this->order->id;
        }
        $this->external_id = $external_id;
    }

    public function setName(string $name = ''): void
    {
        if (empty($name)) {
            $name = $this->order->name;
        }
        $this->name = $name;
    }

    public function setUserId(int $user_id = 0): void
    {
        if ($user_id == 0) {
            $user_id = Pterodactyl::user($this->order->user)['id'];
        }
        $this->user_id = $user_id;
    }

    public function setEggId(int $egg_id = 0): void
    {
        if ($egg_id == 0) {
            $egg_id = json_decode($this->order->package['data']['egg'], true)['id'];
        }
        $this->egg_id = $egg_id;
    }

    public function setOomDisabled(bool $oom_disabled = false): void
    {
        if (empty($oom_disabled)) {
            $oom_disabled = !$this->package('OOM_KILLER', false);
        }
        $this->oom_disabled = $oom_disabled;
    }

    public function setDockerImage(string $docker_image = ''): void
    {
        if (empty($docker_image)) {
            $docker_image = $this->egg()['docker_image'];
        }
        $this->docker_image = $docker_image;
    }

    public function setStartup(string $startup = ''): void
    {
        if (empty($startup)) {
            $startup = $this->package('startup', $this->egg()['startup']);
        }
        $this->startup = $startup;
    }

    public function setEnvironment(array $environment = []): void
    {
        $packageEnv = is_array($this->package('environment', [])) ? $this->package('environment', []) : [];
        $eggModelEnv = is_array($this->eggModel()->env()) ? $this->eggModel()->env() : [];
        $clientEnv = is_array($this->option('environment', [])) ? $this->option('environment', []) : [];
        if (empty($environment)) {
            $env = array_merge($eggModelEnv, $packageEnv, $clientEnv);
        } else {
            $env = array_merge($eggModelEnv, $packageEnv, $clientEnv, $environment);
        }
        $this->environment = $env;
    }


    public function setLimits(array $limits = []): void
    {
        if (empty($limits)) {
            $limits = [
                "memory" => (integer)$this->package('memory_limit', 0),
                "swap" => (integer)$this->package('swap_limit', 0),
                "disk" => (integer)$this->package('disk_limit', 0),
                "io" => (integer)$this->package('block_io_weight', 500),
                "cpu" => (integer)$this->package('cpu_limit', 100),
            ];
        }
        $this->limits = $limits;
    }

    public function setFeatureLimits(array $feature_limits = []): void
    {
        if (empty($feature_limits)) {
            $feature_limits = [
                "databases" => (integer)$this->package('database_limit', 0),
                "backups" => (integer)$this->package('backup_limit', 0),
                "allocations" => (integer)$this->package('allocation_limit', 0),
            ];
        }
        $this->feature_limits = $feature_limits;
    }

    public function setAllocationsIds(array $allocations_ids = []): void
    {
        if (empty($allocations_ids)) {
            $allocations_ids = [];
        }
        $this->allocations_ids = $allocations_ids;
    }


    /** GETTERS */
    public function getExternalId(): string
    {
        return $this->external_id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getEggId(): int
    {
        return $this->egg_id;
    }

    public function isOomDisabled(): bool
    {
        return $this->oom_disabled;
    }

    public function getDockerImage(): string
    {
        return $this->docker_image;
    }

    public function getStartup(): string
    {
        return $this->startup;
    }

    public function getEnvironment(): array
    {
        return $this->environment;
    }

    public function getLimits(): array
    {
        return $this->limits;
    }

    public function getFeatureLimits(): array
    {
        return $this->feature_limits;
    }

    public function getAllocationsIds(): array
    {
        return $this->allocations_ids;
    }

    public function getParams(): array
    {
        return $this->params;
    }
}
