<?php

namespace App\Services\Pterodactyl\Http\Controllers;

use App\Facades\AdminTheme;
use App\Services\Pterodactyl\Entities\Egg;
use App\Services\Pterodactyl\Entities\Pterodactyl;
use App\Services\Pterodactyl\Entities\Location;
use App\Services\Pterodactyl\Entities\Node;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\Package;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Http;

class PterodactylAdminController extends Controller
{
    /**
     * Update the specified resource in storage.
     * @return Renderable
     */
    public function admin(): Renderable
    {
        $locations = Location::query()->get();
        return view(AdminTheme::serviceView('pterodactyl', 'settings'), compact('locations'));
    }

    /**
     * Update the specified resource in storage.
     * @return Renderable
     * @throws BindingResolutionException
     */
    public function locations(): Renderable
    {
        try {
            $pterodactyl_locations = Pterodactyl::api()->locations->all()['data'];
        } catch (\Exception $e){
            $pterodactyl_locations = [];
            request()->session()->flash('error', '[Pterodactyl] An error occurred while trying to get locations, check if the locations in the pterodactyl panel have been created');
        }
        $locations = Location::query()->paginate(15);
        return view(AdminTheme::serviceView('pterodactyl', 'locations'), compact('locations', 'pterodactyl_locations'));
    }

    /**
     * Store the specified resource in storage.
     * @return RedirectResponse
     */
    public function store(): RedirectResponse
    {
        request()->validate([
            'name' => 'required',
            'country_code' => 'required',
            'location_id' => 'required',
            'stock' => 'numeric',
        ]);

        $location = new Location;
        $location->name = request()->input('name');
        $location->country_code = request()->input('country_code');
        $location->location_id = request()->input('location_id');
        $location->stock = request()->input('stock', '-1');
        $location->save();

        return redirect()->back()->with('success', __('admin.location_has_been_added'));
    }

    /**
     * Update the specified resource in storage.
     * @param Location $location
     * @return RedirectResponse
     */
    public function update(Location $location): RedirectResponse
    {
        request()->validate([
            'name' => 'required',
            'country_code' => 'required',
            'location_id' => 'required',
            'stock' => 'numeric',
        ]);

        $location->name = request()->input('name', $location->name);
        $location->country_code = request()->input('country_code');
        $location->location_id = request()->input('location_id', $location->id);
        $location->stock = request()->input('stock', $location->stock);
        $location->save();

        return redirect()->back()->with('success', __('admin.location_has_been_updated'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param Package $package
     * @return Application|Redirector|RedirectResponse
     */
    public function updatePackage(Request $request, Package $package): Redirector|RedirectResponse|Application
    {
        // apply Pterodactyl's validation rules for env variables
//        $validated = $request->validate($this->validationRules($package)); // Disabled the check due to a placeholder save conflict

        $package->update(['data' => $request->except('_token')]);
        return redirect(route('packages.edit', ['package' => $package->id]))->with('success', __('responses.update_success', ['name' => 'package']));
    }

    /**
     * Retrieve Laravel validation rules for env variables
     *
     * @param Package $package
     * @return array
     */
    private function validationRules(Package $package): array
    {
        $rules = [];
        if (json_decode($package->data('egg')) !== NULL) {
            foreach (json_decode($package->data('egg'))->relationships->variables->data as $variable) {
                $rules['environment.' . $variable->attributes->env_variable] = $variable->attributes->rules;
            }
        }
        return $rules;
    }

    /**
     * @throws BindingResolutionException|\Exception
     */
    public function nodes(): Renderable
    {

        try {
            $nodes = Node::getApiNodes();
        } catch (\Exception $e){
            $nodes = [];
            request()->session()->flash('error', $e->getMessage());
        }
        return view(AdminTheme::serviceView('pterodactyl', 'nodes'), compact('nodes'));
    }

    public function storeNode(): RedirectResponse
    {
        request()->validate([
            'ip' => 'required',
            'ports_range' => 'required',
//            'auto_ports' => 'required',
        ]);

        $node = Node::query()->where('node_id', request()->input('node_id'))->first();
        if (!$node) {
            $node = new Node;
        }
        $node->ip = request()->input('ip');
        $node->location_id = request()->input('location_id');
        $node->ports_range = request()->input('ports_range');
        $node->auto_ports = 1;
        $node->save();

        return redirect()->back()->with('success', __('admin.node_has_been_stored'));
    }

    public function eggs(): Renderable
    {

        try {
            $eggs = Egg::getAll();
        } catch (\Exception $e){
            $eggs = [];
            request()->session()->flash('error', $e->getMessage());
        }
        return view(AdminTheme::serviceView('pterodactyl', 'eggs'), compact('eggs'));
    }

    public function eggManage($egg)
    {
        $eggData = Egg::getOne($egg);
        $egg = Egg::query()->firstOrCreate(
            ['egg_id' => $eggData['id']],
            ['egg_id' => $eggData['id'], 'nest_id' => $eggData['nest'], 'variables' => $eggData['variables']]
        );

        $data = $egg->variables;
        foreach ($eggData['variables'] as $key => $value) {
            if (!array_key_exists($key, $data)){
                $data[$key] = $value;
            }
        }
        $egg->variables = $data;
        return view(AdminTheme::serviceView('pterodactyl', 'eggs_manage'), compact('egg'));
    }

    public function eggManageStore()
    {
        $egg = Egg::where('egg_id', request()->get('egg_id'))->first();
        $eggData = Egg::getOne(request()->get('egg_id'));

        $newData = request()->except(['_token', 'egg_id', 'var_id']);
        $variables = array_replace($eggData['variables'], $egg->variables);
        foreach ($variables as $key => $var) {
            if (array_key_exists($var['env_variable'], $newData)) {
                $variables[$key]['default_value'] = $newData[$var['env_variable']];
                continue;
            }
            unset($variables[$key]);
        }
        $egg->variables = $variables;
        $egg->save();
        return redirect()->route('pterodactyl.eggs')->with('success', __('admin.variables_save_success'));
    }


    public function clearCache()
    {
        Egg::clearCache();
        Node::clearCacheAll();
        Pterodactyl::clearCache();
        $items = ['Nodes', 'Eggs', 'Pterodactyl'];
        return redirect()->back()->with('success', __('admin.clear_pterodactyl_cache', ['items' => implode(', ', $items)]));
    }


    public function checkApiAvailability()
    {
        $url = rtrim(settings('encrypted::pterodactyl::api_url'));
        $secret = settings('encrypted::pterodactyl::sso_secret', null);
        $resp['status'] = false;
        try {
            $resp = [
                'status' => Http::head($url)->successful(),
                'url' => 'URL is ' . (Http::head($url)->successful() ? 'available' : 'not available'),
                'api' => Pterodactyl::api()->checkAuthorization(),
                'sso' => $secret ? $this->checkSsoAuthorization($url, $secret) : 'Not configured'
            ];
        } catch (\Exception $e) {
            $resp = ['url' => 'Error: ' . $e->getMessage()];
        }

        return redirect()->back()->with(['resp' => $resp]);
    }

    protected function checkSsoAuthorization($url, $secret)
    {
        $sso = Http::get($url . '/sso-wemx', [
            'sso_secret' => $secret,
            'user_id' => 1
        ]);
        if ($sso->successful()) {
            return array_key_exists('message', $sso->json()) ? $sso->json()['message'] : 'Authorized';
        } elseif ($sso->getStatusCode() == 501) {
            return ($sso->json()['message'] == 'You cannot automatically login to admin accounts.' ? 'Authorized' : $sso->json()['message']) ?? 'Unknown error';
        }
        return 'Not Authorized';
    }


}
