<?php

namespace App\Services\Pterodactyl\Entities;

use Exception;
use Gigabait\PteroApi\PteroApi;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @property array $variables
 */
class Egg extends Model
{

    protected $table = 'pterodactyl_eggs';
    protected $guarded = [];

    protected $casts = [
        'variables' => 'array'
    ];
    private static PteroApi $api;

    private const CACHE_TIME = 60 * 60 * 24;
    private const EGG_ID_ERROR = 'Egg with ID not found';
    private const API_CONNECT_ERROR = 'API connection error or you have no Nests created';

    private array $placeholders = [
        'AUTO_PORT', 'USERNAME', 'RANDOM_TEXT'
    ];


    public function env(array $merge = []): array
    {
        return array_merge($this->transformEnv(), $merge);
    }

    private function transformEnv()
    {
        return array_reduce($this->variables, function($carry, $item) {
            $carry[$item['env_variable']] = $item['default_value'];
            return $carry;
        }, []);
    }


    /**
     * @throws BindingResolutionException
     */
    private static function api(): PteroApi
    {
        if (!isset(self::$api)) {
            self::$api = Pterodactyl::api();
        }
        return self::$api;
    }

    /**
     * @return array
     * @throws Exception
     */
    public static function getAll(): array
    {
        return Cache::remember('eggs.data', self::CACHE_TIME, function () {
            $nestsResponse = self::api()->nests->all();
            if (!$nestsResponse->successful() || empty($nestsResponse)) {
                throw new Exception(self::API_CONNECT_ERROR);
            }

            $eggsData = [];
            foreach ($nestsResponse->json()['data'] as $nest) {
                $eggsResponse = self::api()->eggs->all($nest['attributes']['id'], ['variables']);

                if ($eggsResponse->successful() && !empty($eggsResponse->json()['data'])) {
                    foreach ($eggsResponse->json()['data'] as $egg) {
                        $eggId = $egg['attributes']['id'];
                        $eggAttributes = $egg['attributes'];
                        $eggAttributes['nest_name'] = $nest['attributes']['name'];
                        $variablesData = $eggAttributes['relationships']['variables']['data'];

                        $eggVariables = [];
                        foreach ($variablesData as $variable) {
                            if (array_key_exists('attributes', $variable)) {
                                $variableId = $variable['attributes']['id'];
                                $eggVariables[$variableId] = $variable['attributes'];
                            }
                        }
                        unset($eggAttributes['relationships']);

                        $eggsData[$eggId] = array_merge($eggAttributes, ['variables' => $eggVariables]);
                    }
                }
            }
            return $eggsData;
        });
    }

    public static function getOne(int $eggId): array
    {
        return Cache::remember("eggs.data.{$eggId}", self::CACHE_TIME, function () use ($eggId) {
            $nestsResponse = self::api()->nests->all();
            if (!$nestsResponse->successful() || empty($nestsResponse)) {
                throw new Exception(self::API_CONNECT_ERROR);
            }

            foreach ($nestsResponse->json()['data'] as $nest) {
                $eggsResponse = self::api()->eggs->all($nest['attributes']['id'], ['variables']);

                if ($eggsResponse->successful() && !empty($eggsResponse->json()['data'])) {
                    foreach ($eggsResponse->json()['data'] as $egg) {
                        if ($egg['attributes']['id'] == $eggId) {
                            $eggAttributes = $egg['attributes'];
                            $eggAttributes['nest_name'] = $nest['attributes']['name'];
                            $variablesData = $eggAttributes['relationships']['variables']['data'];

                            $eggVariables = [];
                            foreach ($variablesData as $variable) {
                                if (array_key_exists('attributes', $variable)) {
                                    $variableId = $variable['attributes']['id'];
                                    $eggVariables[$variableId] = $variable['attributes'];
                                }
                            }
                            unset($eggAttributes['relationships']);
                            $model = self::query()->firstOrCreate(
                                ['egg_id' => $egg['attributes']['id']],
                                ['egg_id' => $egg['attributes']['id'], 'nest_id' => $egg['attributes']['nest'], 'variables' => $eggVariables]
                            );
                            $eggAttributes['model'] = $model ?? null;
                            return array_merge($eggAttributes, ['variables' => $eggVariables]);
                        }
                    }
                }
            }
            throw new Exception(self::EGG_ID_ERROR);
        });
    }


    public static function clearCache(): void
    {
        Cache::forget('eggs.data');
        foreach (self::query()->get() as $egg){
            Cache::forget("eggs.data.{$egg->egg_id}");
        }
    }

}
