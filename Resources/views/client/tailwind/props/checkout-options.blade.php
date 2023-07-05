<div class="relative p-4 bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
    <div class="custom-note">
        <div class="flex justify-between mb-3 rounded-t sm:mb-3">
            <div class="text-lg text-gray-900 md:text-xl dark:text-white">
                <h3 class="font-semibold">Server Options</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Please select your desired options below.</p>
            </div>
            <div></div>
        </div>

        <div class="mb-4">
            <label for="location" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Server Location</label>
            <select id="location" name="location" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" required>
                @foreach($package->data('locations', []) as $location)
                    @if(PterodactylLocation::whereId($location)->exists())
                        <option value="{{ PterodactylLocation::find($location)->id }}" @if(PterodactylLocation::find($location)->stock == 0) disabled @endif>{{ PterodactylLocation::find($location)->name }} ({{ PterodactylLocation::find($location)->inStock() }})</option>
                    @endif
                @endforeach
            </select>
        </div>
        
    </div>

    <div class="grid gap-4 sm:grid-cols-2 sm:gap-6">
    @if(json_decode($package->data('egg')) !== NULL)
        @foreach(json_decode($package->data('egg'))->relationships->variables->data as $variable)
        @if($variable->attributes->user_viewable AND !in_array($variable->attributes->env_variable, $package->data('excluded_variables', [])))
        <div class="w-full">
            <label for="environment" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ $variable->attributes->name }} @if(Str::contains($variable->attributes->rules, 'required'))<span class="text-red-500">*</span> @endif</label>
            <input @if(!$variable->attributes->user_editable) disabled @endif type="text" name="environment[{{$variable->attributes->env_variable}}]" id="brand" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
            value="@isset($package->data('environment')[$variable->attributes->env_variable]){{ $package->data('environment')[$variable->attributes->env_variable] }}@else{{ $variable->attributes->default_value }}@endisset" name="environment[{{ $variable->attributes->env_variable }}]" @if(Str::contains($variable->attributes->rules, 'required')) required @endif>
            <p id="helper-text-explanation" class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ $variable->attributes->description }}</p>
        </div>
        @endif
        @endforeach
    @endif
    </div>

</div>