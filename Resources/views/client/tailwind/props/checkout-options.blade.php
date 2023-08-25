@php
    $inputClass = 'bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500';
    $checkboxClass = 'w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[\'\'] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600';
    $pVariables = $package->data('environment');
@endphp

<div class="relative p-4 bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
    <div class="custom-note">
        <div class="flex justify-between mb-3 rounded-t sm:mb-3">
            <div class="text-lg text-gray-900 md:text-xl dark:text-white">
                <h3 class="font-semibold">{!! __('client.ptero_server_options') !!}</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">{!! __('client.ptero_server_options_desc') !!}</p>
            </div>
            <div></div>
        </div>

        <div class="mb-4">
            <label for="location"
                   class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{!! __('client.ptero_server_location') !!}</label>
            <select id="location" name="location"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                    required>
                @foreach($package->data('locations', []) as $location)
                    @if(PterodactylLocation::whereId($location)->exists())
                        <option value="{{ PterodactylLocation::find($location)->id }}"
                                @if(PterodactylLocation::find($location)->stock == 0) disabled @endif>{{ PterodactylLocation::find($location)->name }}
                            ({{ PterodactylLocation::find($location)->inStock() }})
                        </option>
                    @endif
                @endforeach
            </select>
        </div>

    </div>


    <div class="grid gap-4 sm:grid-cols-2 sm:gap-6">
        @if(json_decode($package->data('egg')) !== NULL)
            @foreach(json_decode($package->data('egg'))->relationships->variables->data as $variable)
                @php $variable = $variable->attributes; @endphp

                @if($variable->user_viewable AND !in_array($variable->env_variable, $package->data('excluded_variables', [])))
                    <div class="w-full">

                        {{--        Label        --}}
                        <label for="environment" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                            {{ $variable->name }}
                            @if(Str::contains($variable->rules, 'required'))
                                <span class="text-red-500">*</span>
                            @endif
                        </label>


                        @if(Str::contains($variable->rules, 'boolean'))
                            {{--        Boolean        --}}
                            <label class="relative inline-flex cursor-pointer flex flex-row justify-end">
                                <input type="checkbox" name="environment[{{$variable->env_variable}}]"
                                       class="sr-only peer"
                                       @if(isset($pVariables[$variable->env_variable]) && $pVariables[$variable->env_variable]) checked @endif>
                                <div class="{{$checkboxClass}}"></div>
                            </label>

                        @elseif(Str::contains(str_replace(' ', '', $variable->rules), '|in:true,false') || Str::contains(str_replace(' ', '', $variable->rules), '|in:false,true'))
                            {{-- Boolean Slider for TRUE and FALSE --}}
                            <label class="relative inline-flex cursor-pointer flex flex-row justify-end">
                                <input type="checkbox" name="environment[{{$variable->env_variable}}]"
                                       value="{{$pVariables[$variable->env_variable]}}" class="sr-only peer"
                                       @if(isset($pVariables[$variable->env_variable]) && $pVariables[$variable->env_variable] == 'true') checked @endif>
                                <div class="{{$checkboxClass}}"></div>
                            </label>

                        @elseif(Str::contains(str_replace(' ', '', $variable->rules), '|in:'))
                            {{--        Array        --}}
                            @php $options = explode(',', Str::after($variable->rules, 'in:'));@endphp
                            <select name="environment[{{$variable->env_variable}}]" class="{{$inputClass}}" required>
                                @foreach($options as $option)
                                    <option value="{{ trim($option) }}"
                                            @if(isset($pVariables[$variable->env_variable]) && $pVariables[$variable->env_variable] == trim($option)) selected @endif>{{ ucfirst(trim($option)) }}</option>
                                @endforeach
                            </select>

                        @else
                            {{--        String        --}}
                            <input @if(!$variable->user_editable) disabled
                                   @else name="environment[{{$variable->env_variable}}]" @endif type="text" id="brand"
                                   class="{{$inputClass}}"
                                   value="@isset($pVariables[$variable->env_variable]){{ $pVariables[$variable->env_variable] }}@else{{ $variable->default_value }}@endisset"
                                   @if(Str::contains($variable->rules, 'required')) required @endif>

                        @endif

                        {{--        Description        --}}
                        <p id="helper-text-explanation"
                           class="mt-2 text-sm text-gray-500 dark:text-gray-400">{!! __($variable->description) !!}</p>


                    </div>
                @endif
            @endforeach
        @endif
    </div>

</div>
