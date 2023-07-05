<?php

namespace App\Services\Pterodactyl\Entities;

use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\Auth;
use Gigabait\PteroApi\PteroApi;
use Illuminate\Http\Request;

class Pterodactyl
{
    /**
     * initize connection with Pterodactyl
     *
     * @return ApplicationAPI
     */
    public static function api()
    {
        if(!settings('encrypted::pterodactyl::api_key', false) OR !settings('encrypted::pterodactyl::api_url', false)) {
            app()->make('redirect')->to('/admin/pterodactyl')->with('error', 'Please setup your Pterodactyl Panel API credentials')->send();
        }

        return new PteroApi(settings('encrypted::pterodactyl::api_key'), settings('encrypted::pterodactyl::api_url'));
    }

    /**
     * Return all available eggs
     *
     * @return array
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

        return $eggs;
    }

    /**
     * retrieve the Pterodactyl user from external_id
     *
     * @return array
     */
    public static function user()
    {
        $user = Pterodactyl::api()->users->getExternal("wmx-". Auth::user()->id);

        if($user->status() !== 200) {
            return Pterodactyl::createUser();
        }

        return $user->json()['attributes'];
    }

    /**
     * retrieve the Pterodactyl server from external_id
     *
     * @return array
     */
    public static function server($id)
    {
        $server = Pterodactyl::api()->servers->getExternal("wmx-{$id}");

        if($server->status() !== 200)
        {
            throw new \Exception("[Pterodactyl] Could not locate server with external id wmx-{$id} on Pterodactyl.");
        }

        return $server->json()['attributes'];
    }

    /**
     * create user on the Pterodactyl Panel
     *
     * @return User
     */
    public static function createUser()
    {
        $authUser = Auth::user(); 

        // check whether a user with same email as authenticated user already exists on Pterodactyl
        // this is mainly for users that are migrating over and have existing pterodactyl users
        $user = Pterodactyl::api()->users->all("?filter[email]=". Auth::user()->email);
        if(isset($user['data'][0]['attributes'])) {

            // edit this users external id so next call it gets easier.
            $params = [
                "external_id" => "wmx-". $authUser->id,
                "email" => $authUser->email,
                "username" => $authUser->username . rand(1,1000),
                "first_name" => $authUser->first_name,
                "last_name" => $authUser->last_name,
            ];

            $response = Pterodactyl::api()->users->update($user['data'][0]['attributes']['id'], $params);
            return $user['data'][0]['attributes'];
        }

        // create a brand new pterodactyl user
        $user = [
            'external_id' => (string) "wmx-" . $authUser->id,
            'email' => $authUser->email,
            'username' => $authUser->username . rand(1,1000),
            'first_name'=> $authUser->first_name,
            'last_name' => $authUser->last_name,
        ];

        return Pterodactyl::api()->users->create($user)->json()['attributes'];
    }
}