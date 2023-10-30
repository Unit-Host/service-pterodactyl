<?php

namespace App\Services\Pterodactyl\Entities;

use App\Models\Order;
use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Auth;
use Gigabait\PteroApi\PteroApi;
use Illuminate\Support\Facades\Cache;

class Pterodactyl
{
    /**
     * initialize connection with Pterodactyl
     *
     * @return PteroAPI
     * @throws BindingResolutionException
     */
    public static function api(): PteroApi
    {
        if (!settings('encrypted::pterodactyl::api_key', false) or !settings('encrypted::pterodactyl::api_url', false)) {
            app()->make('redirect')->to('/admin/pterodactyl')->with('error', 'Please setup your Pterodactyl Panel API credentials')->send();
        }

        return new PteroApi(settings('encrypted::pterodactyl::api_key'), rtrim(settings('encrypted::pterodactyl::api_url'), '/'));
    }

    /**
     * Return all available eggs
     *
     * @return collection
     * @throws BindingResolutionException
     */
    public static function getEggs()
    {
        $eggs = [];
        $nests = Pterodactyl::api()->nests->all()->json();

        // Get the eggs from each nest
        foreach ($nests['data'] as $nest) {
            $nested_eggs = Pterodactyl::api()->eggs->all($nest['attributes']['id'])->json();
            // Merge the eggs with the existing eggs array
            $eggs = array_merge($eggs, $nested_eggs['data']);
        }

        return collect($eggs);
    }

    /**
     * retrieve the Pterodactyl user from external_id
     *
     * @return array
     * @throws BindingResolutionException
     */
    public static function user($user = false)
    {
        if (!$user) {
            $user = Auth::user();
        }

        try {
            $pterodactyl_user = Pterodactyl::api()->users->getExternal("wmx-" . $user->id);
        } catch (\Exception $e) {
            ErrorLog("pterodactyl::user", "Failed to retrieve Pterodactyl user from its external id wmx-{$user->id} : {$e->getMessage()}");
        }

        if ($pterodactyl_user->status() !== 200) {
            return Pterodactyl::createUser($user);
        }

        return $pterodactyl_user->json()['attributes'];
    }

    /**
     * retrieve the Pterodactyl server from external_id
     *
     * @param $id
     * @return array
     * @throws BindingResolutionException
     */
    public static function server($id): array
    {
        $server = Pterodactyl::api()->servers->getExternal("wmx-{$id}");

        if ($server->status() !== 200) {
            throw new \Exception("[Pterodactyl] Could not locate server with external id wmx-{$id} on Pterodactyl.");
        }

        return $server->json()['attributes'];
    }

    /**
     * create user on the Pterodactyl Panel
     *
     * @return User
     * @throws BindingResolutionException
     */
    public static function createUser($user)
    {
        $authUser = $user;

        // check whether a user with same email as authenticated user already exists on Pterodactyl
        // this is mainly for users that are migrating over and have existing pterodactyl users
        $user = Pterodactyl::api()->users->all("?filter[email]=" . $authUser->email);
//        $user = Pterodactyl::api()->users->getExternal("wmx-" . $authUser->id);
        if (isset($user['data'][0]['attributes'])) {

            // edit this users external id so next call it gets easier.
            $params = [
                "external_id" => "wmx-" . $authUser->id,
                "email" => $authUser->email,
                "username" => $authUser->username,
                "first_name" => $authUser->first_name,
                "last_name" => $authUser->last_name,
            ];

            Pterodactyl::api()->users->update($user['data'][0]['attributes']['id'], $params);
            return $user['data'][0]['attributes'];
        }

        // create a brand new pterodactyl user
        $user = [
            'external_id' => (string)"wmx-" . $authUser->id,
            'email' => $authUser->email,
            'username' => $authUser->username . rand(1, 1000),
            'first_name' => $authUser->first_name,
            'last_name' => $authUser->last_name,
        ];

        return Pterodactyl::api()->users->create($user)->json()['attributes'];
    }


    public static function serverDetails($order_id)
    {
        return Cache::remember("server-details-{$order_id}", 86400, function () use ($order_id) {
            try {
                $response = self::api()->servers->getExternal('wmx-' . $order_id);
                if ($response->status() == 200) {
                    return $response->json()['attributes'];
                }
            } catch (Exception $e) {
                return null;
            }
            return null;
        });
    }

    public static function serverIP($order_id): string|null
    {
        return Cache::remember("serverIP.order.{$order_id}", 86400, function () use ($order_id) {
            try {
                $data = self::serverDetails($order_id);
                $allocation_id = $data['allocation'];
                foreach ($data['relationships']['allocations']['data'] as $allocation) {
                    if ($allocation['attributes']['id'] == $allocation_id) {
                        if ($allocation['attributes']['alias'] != null) {
                            return $allocation['attributes']['alias'] . ':' . $allocation['attributes']['port'];
                        }
                        return $allocation['attributes']['ip'] . ':' . $allocation['attributes']['port'];
                    }
                }
            } catch (Exception $e) {
                return null;
            }
            return null;
        });
    }


    public static function clearCache(): void
    {
        foreach (Order::query()->get() as $order){
            Cache::forget("server-details-{$order->id}");
            Cache::forget("serverIP.order.{$order->id}");
        }
    }
}
