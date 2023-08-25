@if(node()::get()->count() <= 0)
    <div class="alert alert-danger" role="alert">
        <h4 class="alert-heading">{!! __('admin.warn') !!}</h4>
        <p>{!! __('admin.pterodactyl_node_warn') !!}</p>
        <hr>
        <p class="mb-0">{!! __('admin.co_to_configure') !!}  <a class="btn btn-warning" href="{{route('pterodactyl.nodes')}}">{!! __('admin.nodes') !!}</a></p>
    </div>
@endif


<form action="{{ route('package_update', ['package' => $package->id]) }}" method="POST">
    @csrf
    <div class="row">
        <div class="form-group col-4">
            <label for="database_limit">{!! __('admin.database_limit') !!}</label>
            <div class="input-group mb-2">
                <input type="number" class="form-control text-right" name="database_limit" id="database_limit"
                       min="0"
                       value="{{ $package->data('database_limit') }}" required/>
                <div class="input-group-append">
                    <div class="input-group-text"><i class="fas fa-solid fa-database"></i></div>
                </div>
                <small class="form-text text-muted">
                    {!! __('admin.database_limit_desc') !!}
                </small>
            </div>
        </div>

        <div class="form-group col-4">
            <label for="allocation_limit">{!! __('admin.allocation_limit') !!}</label>
            <div class="input-group mb-2">
                <input type="number" class="form-control text-right" name="allocation_limit" id="allocation_limit"
                       min="0"
                       value="{{ $package->data('allocation_limit') }}"
                       required/>
                <div class="input-group-append">
                    <div class="input-group-text"><i class="fas fa-solid fa-network-wired"></i></div>
                </div>
                <small class="form-text text-muted">
                    {!! __('admin.allocation_limit_desc') !!}
                </small>
            </div>
        </div>

        <div class="form-group col-4">
            <label for="backup_limit">{!! __('admin.backup_limit') !!}</label>
            <div class="input-group mb-2">
                <input type="number" class="form-control text-right" name="backup_limit" id="backup_limit"
                       min="0" value="{{ $package->data('backup_limit') }}"
                       required/>
                <div class="input-group-append">
                    <div class="input-group-text"><i class="fas fa-solid fa-download"></i></div>
                </div>
                <small class="form-text text-muted">
                    {!! __('admin.backup_limit_desc') !!}
                </small>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="form-group col-4">
            <label for="cpu_limit">{!! __('admin.cpu_limit') !!}</label>
            <div class="input-group mb-2">
                <input type="number" class="form-control text-right" name="cpu_limit" id="cpu_limit" min="0"
                       value="{{ $package->data('cpu_limit') }}" required/>
                <div class="input-group-append">
                    <div class="input-group-text">%</div>
                </div>
                <small class="form-text text-muted">
                    {!! __('admin.cpu_limit_desc') !!}
                </small>
            </div>
        </div>

        <div class="form-group col-4">
            <label for="memory_limit">{!! __('admin.memory') !!}</label>
            <div class="input-group mb-2">
                <input type="number" class="form-control text-right" name="memory_limit" id="memory_limit"
                       min="0" value="{{ $package->data('memory_limit') }}"
                       required/>
                <div class="input-group-append">
                    <div class="input-group-text">MB</div>
                </div>
                <small class="form-text text-muted">
                    {!! __('admin.memory_desc') !!}
                </small>
            </div>
        </div>

        <div class="form-group col-4">
            <label for="disk_limit">{!! __('admin.disk') !!}</label>
            <div class="input-group mb-2">
                <input type="number" class="form-control text-right" name="disk_limit" id="disk_limit"
                       min="0" value="{{ $package->data('disk_limit') }}"
                       required/>
                <div class="input-group-append">
                    <div class="input-group-text">MB</div>
                </div>
                <small class="form-text text-muted">
                    {!! __('admin.disk_desc') !!}
                </small>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="form-group col-4">
            <label for="cpu_pinning">{!! __('admin.cpu_pinning') !!} {!! __('admin.optional') !!}</label>
            <div class="input-group mb-2">
                <input type="text" class="form-control text-right"
                       value="{{ $package->data('cpu_pinning') }}"
                       name="cpu_pinning" id="cpu_pinning"/>
                <small class="form-text text-muted">
                    {!! __('admin.cpu_pinning_desc') !!}
                </small>
            </div>
        </div>

        <div class="form-group col-4">
            <label for="swap_limit">{!! __('admin.swap') !!}</label>
            <div class="input-group mb-2">
                <input type="number" class="form-control text-right" name="swap_limit" id="swap_limit"
                       value="{{ $package->data('swap_limit', 0) }}"
                       required/>
                <div class="input-group-append">
                    <div class="input-group-text">MB</div>
                </div>
                <small class="form-text text-muted">
                    {!! __('admin.swap_desc') !!}
                </small>
            </div>
        </div>

        <div class="form-group col-4">
            <label for="block_io_weight">{!! __('admin.block_io_weight') !!}</label>
            <div class="input-group mb-2">
                <input type="number" class="form-control text-right" name="block_io_weight" id="block_io_weight"
                       value="{{ $package->data('block_io_weight', 500) }}"
                       required/>
                <small class="form-text text-muted">
                    {!! __('admin.block_io_weight_desc') !!}
                </small>
            </div>
        </div>

        <div class="form-group col-4">
            <label for="block_io_weight">{!! __('admin.enable_oom_killer') !!}</label>
            <div class="input-group mb-2">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" name="OOM_KILLER" id="customCheck1"
                           value="1" @if($package->data('OOM_KILLER', false)) checked @endif/>
                    <label class="custom-control-label" for="customCheck1">
                        {!! __('admin.enable_oom_killer_desc') !!}
                    </label>
                </div>
            </div>
        </div>
    </div>

    <hr>
    <div class="row">
        <div class="form-group col-6">
            <label for="location">{!! __('admin.allowed_locations') !!}</label>
            <div class="input-group mb-2">
                <select name="locations[]" id="location" class="form-control select2 select2-hidden-accessible"
                        multiple="" tabindex="-1" aria-hidden="true">
                    @foreach(PterodactylLocation::get() as $location)
                        <option value="{{ $location->id }}"
                                @if(in_array($location->id, $package->data('locations', []))) selected @endif>{{ $location->name }}</option>
                    @endforeach
                </select>
                <small class="form-text text-muted">{!! __('admin.allowed_locations_desc') !!}</small>
            </div>
        </div>

        <div class="form-group col-6">
            <label for="egg">{!! __('admin.egg') !!}</label>
            <div class="input-group mb-2">
                <select name="egg" id="egg" class="form-control select2 select2-hidden-accessible"
                        tabindex="-1" aria-hidden="true" onchange="setEgg()">
                    @foreach(Pterodactyl::getEggs() as $egg)
                        <option value="{{ json_encode($egg['attributes']) }}"
                                @if($package->data('egg', false) AND json_decode($package->data('egg'))->id == $egg['attributes']['id']) selected @endif>{{ $egg['attributes']['name'] }}</option>
                    @endforeach
                </select>
                <small class="form-text text-muted">{!! __('admin.egg_desc') !!}</small>
            </div>
        </div>
    </div>

    <div class="row">
        @if(json_decode($package->data('egg')) !== NULL)

            <div class="form-group col-6">
                <label for="docker_image">{!! __('admin.docker_image') !!}</label>
                <div class="input-group mb-2">
                    <div class="input-group-append">
                        <div class="input-group-text" type="button" id="dropdownMenuButton" data-toggle="dropdown"
                             aria-haspopup="true" aria-expanded="false" style="cursor: pointer"><i
                                class="fas fa-chevron-circle-down"></i></div>
                        <div class="dropdown-menu" x-placement="bottom-start"
                             style="position: absolute; transform: translate3d(0px, 28px, 0px); top: 0px; left: 0px; will-change: transform;">
                            @foreach(json_decode($package->data('egg'))->docker_images as $key => $image)
                                <button type="button" class="dropdown-item" style="cursor: pointer"
                                        onclick="document.getElementById('docker_image').value = '{{$image}}';">{{ $key }}</button>
                            @endforeach
                        </div>
                    </div>

                    <input type="text" class="form-control text-left" name="docker_image" id="docker_image"
                           value="{{ $package->data('docker_image', json_decode($package->data('egg'))->docker_image ) }}"
                    />
                    <small class="form-text text-muted">
                        {!! __('admin.docker_image_desc') !!}
                    </small>
                </div>
            </div>

            <div class="form-group col-6">
                <label for="excluded_variables[]">{!! __('admin.exclude_variables_checkout') !!}</label>
                <div class="input-group mb-2">
                    <select name="excluded_variables[]" id="excluded_variables[]"
                            class="form-control select2 select2-hidden-accessible"
                            multiple="" tabindex="-1" aria-hidden="true">
                        @foreach(json_decode($package->data('egg'))->relationships->variables->data as $variable)
                            @if($variable->attributes->user_editable AND $variable->attributes->user_viewable)
                                <option value="{{ $variable->attributes->env_variable }}"
                                        @if(in_array($variable->attributes->env_variable, $package->data('excluded_variables', []))) selected @endif>{{ $variable->attributes->name }}</option>
                            @endif
                        @endforeach
                    </select>
                    <small class="form-text text-muted">{!! __('admin.exclude_variables_checkout_desc') !!}</small>
                </div>
            </div>

            <div class="form-group col-12">
                <label>{!! __('admin.startup_command') !!}</label>
                <input type="text" class="form-control" id="startup"
                       value="{{ $package->data('startup', json_decode($package->data('egg'))->startup ) }}"
                       name="startup">
                <small class="form-text text-muted">
                    {!! __('admin.startup_command_desc') !!}
                </small>
            </div>

            <div class="col-12">
                <hr>
            </div>

            @foreach(json_decode($package->data('egg'))->relationships->variables->data as $variable)
                <div class="form-group col-6">
                    <label>{{ $variable->attributes->name }}</label>
                    <input type="text" class="form-control"
                           value="@isset($package->data('environment')[$variable->attributes->env_variable]){{ $package->data('environment')[$variable->attributes->env_variable] }}@else{{ $variable->attributes->default_value }}@endisset"
                           name="environment[{{ $variable->attributes->env_variable }}]" @if(Str::contains($variable->attributes->rules, 'required')) @endif>
                    <small class="form-text text-muted">
                        {{ $variable->attributes->description }} <br><br>
                        <strong>{!! __('admin.access_in_startup') !!}:</strong><code> &#123;&#123;{{ $variable->attributes->env_variable }}
                            &#125;&#125;</code><br>
                        <strong>{!! __('admin.validation_rules') !!}</strong><code> {{ $variable->attributes->rules }}</code>
                    </small>
                </div>
            @endforeach
        @endif
    </div>

    <div class="text-right">
        <button class="btn btn-dark" id="submit" type="submit">{!! __('admin.update') !!}</button>
    </div>
</form>


<script>
    function setEgg() {

        if (document.getElementById("docker_image")) {
            document.getElementById("docker_image").value = '';
        }

        if (document.getElementById("startup")) {
            document.getElementById("startup").value = '';
        }

        var button = document.getElementById("submit");
        button.click();
    }

</script>
