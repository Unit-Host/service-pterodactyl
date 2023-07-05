<?php

namespace App\Services\Pterodactyl\Http\Controllers;

use App\Services\Pterodactyl\Entities\Pterodactyl;
use App\Services\Pterodactyl\Entities\Location;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\Package;
use Gigabait\PteroApi\PteroApi;

class PterodactylAdminController extends Controller
{
    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function admin()
    {
        $locations = Location::get();
        return view('pterodactyl::admin.settings', compact('locations'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function locations()
    {
        $pterodactyl_locations = Pterodactyl::api()->locations->all()['data'];
        $locations = Location::paginate(15);

        return view('pterodactyl::admin.locations', compact('locations', 'pterodactyl_locations'));
    }

    /**
     * Store the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function store()
    {
        $validated = request()->validate([
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

        return redirect()->back()->withSuccess('Location has been added');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Location $location)
    {
        $validated = request()->validate([
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

        return redirect()->back()->withSuccess('Location has been updated');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function updatePackage(Request $request, Package $package)
    {
        // apply Pterodactyl's validation rules for env variables
        $validated = $request->validate($this->validationRules($package));

        $package->update(['data' => $request->except('_token')]);
        return redirect(route('packages.edit', ['package' => $package->id]))->with('success', __('responces.update_success', ['name' => 'package']));
    }

    /**
     * Retrieve laravels validation rules for env variables
     * 
     * @return array
     */
    private function validationRules(Package $package): array
    {
        $rules = [];
        if(json_decode($package->data('egg')) !== NULL) {
            foreach(json_decode($package->data('egg'))->relationships->variables->data as $variable) {
                $rules['environment.' . $variable->attributes->env_variable] = $variable->attributes->rules;
            }
        }

        return $rules;
    }
}