<?php

namespace App\Services\Pterodactyl\Entities;

use Exception;
use Generator;
use Gigabait\PteroApi\PteroApi;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;


/**
 * @property string $name
 * @property string $fqdn
 * @property integer $node_id
 * @property integer $location_id
 * @property string $ip
 * @property string $ports_range
 * @property integer $auto_ports
 */
class Node extends Model
{
    use HasFactory;

    // Error message constants
    private const API_PORTS_ERROR = "[Pterodactyl] An error occurred while trying to get all ports: ";
    private const API_FREE_PORTS_ERROR = "[Pterodactyl] An error occurred while trying to get free ports: ";
    private const API_NODE_FETCH_ERROR = "[Pterodactyl] An error occurred while trying to retrieve nodes: ";
    private const NODE_NOT_FOUND_ERROR = "[Pterodactyl] No nodes found or an error occurred while trying to retrieve nodes.";
    private const PORTS_UNIQUE_COUNT_ERROR = "[Pterodactyl] Not enough available ports to generate the requested number. ";
    private const API_GREAT_ALLOCATIONS_ERROR = "[Pterodactyl] Error creating ports for server";

    private const CACHE_TIME = 60 * 5;

    protected $table = 'pterodactyl_nodes';

    protected $guarded = [];

    protected $casts = [
        'auto_ports' => 'boolean',
    ];

    // Cached Node API data.
    private array $apiNodeData;

    private PteroApi $api;


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
     * Get the IP address of the node. If the IP is not set, get it by resolving the FQDN.
     *
     * @return string
     */
    public function getIp(): string
    {
        return empty($this->ip) ? gethostbyname($this->fqdn) : $this->ip;
    }

    /**
     * Get the port range for the node. If the port range is not set, return a default range.
     *
     * @return string
     */
    public function getPortRange(): string
    {
        return empty($this->ports_range) ? '49152-65535' : $this->ports_range;
    }

    /**
     * Get all allocated ports for the node.
     *
     * @return array
     * @throws Exception
     */
    public function getAllPorts(): array
    {
        try {
            return $this->api()->allocations->getAllPorts($this->node_id);
        } catch (Exception $e) {
            throw new Exception(self::API_PORTS_ERROR . $e->getMessage());
        }
    }

    /**
     * Get all free ports for the node.
     *
     * @return array [id => port]
     * @throws Exception
     */
    public function getFreePorts(): array
    {
        try {
            return $this->api()->allocations->getFreePorts($this->node_id);
        } catch (Exception $e) {
            throw new Exception(self::API_FREE_PORTS_ERROR . $e->getMessage());
        }
    }

    /**
     * Get the data for the node, including model.
     *
     * @return array [id => port]
     * @throws Exception
     */
    public function getAllData(): array
    {
        $node = $this->getApiNode();
        $node['ip'] = $this->getIp();
        $node['node_id'] = $this->node_id;
        $node['auto_ports'] = $this->auto_ports;
        $node['ports_range'] = $this->getPortRange();
        $node['all_ports'] = $this->getAllPorts();
        $node['free_ports'] = $this->getFreePorts();
        return $node;
    }

    /**
     * Fetch the specified amount of free ports for the node.
     *
     * @param int $amount
     * @return array [id => port]
     * @throws Exception
     */
    public function fetchRequiredFreePorts(int $amount): array
    {
        try {
            $freePorts = $this->getFreePorts();

            if (count($freePorts) < $amount) {
                $ports = $this->generateUniquePorts($amount - count($freePorts), $freePorts);
                $params = ['ip' => $this->getIp(), 'ports' => array_map('strval', $ports)];

                $query = $this->api()->allocations->create($this->node_id, $params);
                if ($query->successful()) {
                    $freePorts = $this->api()->allocations->getFreePorts($this->node_id);
                } else {
                    throw new Exception(self::API_GREAT_ALLOCATIONS_ERROR);
                }
            }
            return array_slice($freePorts, 0, $amount, true);
        } catch (Exception $e) {
            throw new Exception(self::API_FREE_PORTS_ERROR . $e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    private function generateUniquePorts(int $numPorts, array $excludedPorts = []): array
    {
        $range = $this->getPortRange();
        [$start, $end] = explode('-', $range);

        $excludedPorts = array_flip($excludedPorts);
        $ports = [];

        foreach ($this->randomPortGenerator($start, $end) as $port) {
            if (isset($excludedPorts[$port])) {
                continue;
            }
            $ports[(string)$port] = $port;
            if (count($ports) >= $numPorts) {
                break;
            }
        }

        if ($numPorts > count($ports)) {
            throw new Exception(self::PORTS_UNIQUE_COUNT_ERROR);
        }
        return $ports;
    }

    private function randomPortGenerator(int $start, int $end): Generator
    {
        $rangeSize = $end - $start + 1;
        while (true) {
            yield $start + mt_rand(0, $rangeSize - 1);
        }
    }

    /**
     * Get the data for the node
     *
     * @return array
     */
    private function getApiNode(): array
    {
        if (!isset($this->apiNodeData)) {
            $this->apiNodeData = Cache::remember("api_node_{$this->node_id}", self::CACHE_TIME, function () {
                return $this->fetchApiNodeData();
            });
        }

        return $this->apiNodeData;
    }

    /**
     * @throws Exception
     */
    private function fetchApiNodeData(): array
    {
        try {
            $node = $this->api()->node->get($this->node_id)->json() ?? [];
            if (empty($node)) {
                throw new Exception(self::NODE_NOT_FOUND_ERROR);
            }
            $node['attributes']['resources'] = self::calculateNodeResources($node['attributes']);
            return $node['attributes'];
        } catch (Exception $e) {
            throw new Exception(self::API_NODE_FETCH_ERROR . $e->getMessage());
        }
    }


    /**
     * Get the data for the all nodes
     *
     * @return array
     * @throws Exception
     */
    public static function getApiNodes(): array
    {
        try {
            $nodes = Pterodactyl::api()->node->all()->json()['data'] ?? [];
            if (empty($nodes)) {
                throw new Exception(self::NODE_NOT_FOUND_ERROR);
            }
            return array_reduce($nodes, function ($carry, $item) {
                $carry[$item['attributes']['id']] = $item['attributes'];
                $carry[$item['attributes']['id']]['resources'] = self::calculateNodeResources($carry[$item['attributes']['id']]);
                return $carry;
            }, []);
        } catch (Exception $e) {
            throw new Exception(self::API_NODE_FETCH_ERROR . $e->getMessage());
        }
    }

    /**
     * @param int $requiredMemory
     * @param int $requiredDisk
     * @return bool
     * @throws Exception
     */
    public function checkResource(int $requiredMemory, int $requiredDisk): bool
    {
        $nodeData = $this->getApiNode();
        if ($nodeData['disk_overallocate'] == '-1' and  $nodeData['memory_overallocate'] == '-1'){
            return true;
        }
        $nodeResources = self::calculateNodeResources($nodeData);
        $requiredMemoryPercent = ($requiredMemory / $nodeResources['memory']['total']) * 100;
        $requiredDiskPercent = ($requiredDisk / $nodeResources['disk']['total']) * 100;
        if ($requiredMemoryPercent > $nodeResources['memory']['available_percent'] ||
            $requiredDiskPercent > $nodeResources['disk']['available_percent']) {
            return false;
        }
        return true;
    }

    /**
     * @param array $node ['attributes']
     * @return array[]
     */
    public static function calculateNodeResources(array $node): array
    {
        return [
            'memory' => self::calculateResourcePercent($node['memory'], $node['allocated_resources']['memory']),
            'disk' => self::calculateResourcePercent($node['disk'], $node['allocated_resources']['disk']),
        ];
    }

    /**
     * @param int $total
     * @param int $used
     * @return array
     */
    private
    static function calculateResourcePercent(int $total, int $used): array
    {
        $availablePercent = round((($total - $used) / $total) * 100, 2);
        return [
            'total' => $total,
            'used' => $used,
            'available_percent' => $availablePercent,
        ];
    }

    /**
     * Clears the node api cache of this object
     * @return void
     */
    public function clearCache(): void
    {
        Cache::forget("api_node_{$this->node_id}");
    }

    public static function clearCacheAll(): void
    {
        foreach (self::query()->get() as $node){
            Cache::forget("api_node_{$node->node_id}");
            $node->delete();
        }
    }
}
