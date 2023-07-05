<form action="{{ route('package_update', ['package' => $package->id]) }}" method="POST">
    @csrf
        <div class="row">
            <div class="form-group col-4">
                <label for="database_limit">Database Limit</label>
                <div class="input-group mb-2">
                    <input type="number" class="form-control text-right" name="database_limit" id="database_limit"
                        min="0"
                        value="{{ $package->data('database_limit') }}" required />
                    <div class="input-group-append">
                        <div class="input-group-text"><i class="fas fa-solid fa-database"></i></div>
                    </div>
                    <small class="form-text text-muted">
                        The total number of databases a user is allowed to create for this server on Pterodactyl Panel.
                    </small>
                </div>
            </div>

            <div class="form-group col-4">
                <label for="allocation_limit">Allocation Limit</label>
                <div class="input-group mb-2">
                    <input type="number" class="form-control text-right" name="allocation_limit" id="allocation_limit"
                        min="0"
                        value="{{ $package->data('allocation_limit') }}"
                        required />
                    <div class="input-group-append">
                        <div class="input-group-text"><i class="fas fa-solid fa-network-wired"></i></div>
                    </div>
                    <small class="form-text text-muted">
                        The total number of allocations a user is allowed to create for this server Pterodactyl Panel.
                    </small>
                </div>
            </div>

            <div class="form-group col-4">
                <label for="backup_limit">Backup Limit</label>
                <div class="input-group mb-2">
                    <input type="number" class="form-control text-right" name="backup_limit" id="backup_limit"
                        min="0" value="{{ $package->data('backup_limit') }}"
                        required />
                    <div class="input-group-append">
                        <div class="input-group-text"><i class="fas fa-solid fa-download"></i></div>
                    </div>
                    <small class="form-text text-muted">
                        The total number of backups that can be created for this server Pterodactyl Panel.
                    </small>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="form-group col-4">
                <label for="cpu_limit">CPU Limit</label>
                <div class="input-group mb-2">
                    <input type="number" class="form-control text-right" name="cpu_limit" id="cpu_limit" min="0"
                        value="{{ $package->data('cpu_limit') }}" required />
                    <div class="input-group-append">
                        <div class="input-group-text">%</div>
                    </div>
                    <small class="form-text text-muted"> If you do not want to limit CPU usage, set the value to
                        <code>0</code>.
                        To use a single thread set it to 100%, for 4 threads set to 400% etc </small>
                </div>
            </div>

            <div class="form-group col-4">
                <label for="memory_limit">Memory</label>
                <div class="input-group mb-2">
                    <input type="number" class="form-control text-right" name="memory_limit" id="memory_limit"
                        min="0" value="{{ $package->data('memory_limit') }}"
                        required />
                    <div class="input-group-append">
                        <div class="input-group-text">MB</div>
                    </div>
                    <small class="form-text text-muted"> The maximum amount of memory allowed for this container. Setting
                        this
                        to <code>0</code> will allow unlimited memory in a container. </small>
                </div>
            </div>

            <div class="form-group col-4">
                <label for="disk_limit">Disk</label>
                <div class="input-group mb-2">
                    <input type="number" class="form-control text-right" name="disk_limit" id="disk_limit"
                        min="0" value="{{ $package->data('disk_limit') }}"
                        required />
                    <div class="input-group-append">
                        <div class="input-group-text">MB</div>
                    </div>
                    <small class="form-text text-muted"> The maximum amount of memory allowed for this container. Setting
                        this
                        to <code>0</code> will allow unlimited memory in a container. </small>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="form-group col-4">
                <label for="cpu_pinning">CPU Pinning (optional)</label>
                <div class="input-group mb-2">
                    <input type="text" class="form-control text-right"
                        value="{{ $package->data('cpu_pinning') }}"
                        name="cpu_pinning" id="cpu_pinning" />
                    <small class="form-text text-muted">
                        Advanced: Enter the specific CPU threads that this process can run on, or leave blank to allow
                        all
                        threads. This can be a single number, or a comma separated list. Example: 0, 0-1,3, or 0,1,3,4.
                    </small>
                </div>
            </div>

            <div class="form-group col-4">
                <label for="swap_limit">Swap</label>
                <div class="input-group mb-2">
                    <input type="number" class="form-control text-right" name="swap_limit" id="swap_limit"
                        min="0" value="{{ $package->data('swap_limit', 0) }}"
                        required />
                    <div class="input-group-append">
                        <div class="input-group-text">MB</div>
                    </div>
                    <small class="form-text text-muted">
                        Setting this to 0 will disable swap space on this server. Setting to -1 will allow unlimited
                        swap.
                    </small>
                </div>
            </div>

            <div class="form-group col-4">
                <label for="block_io_weight">Block IO Weight</label>
                <div class="input-group mb-2">
                    <input type="number" class="form-control text-right" name="block_io_weight" id="block_io_weight"
                        value="{{ $package->data('block_io_weight', 500) }}"
                        required />
                    <small class="form-text text-muted">
                        Advanced: The IO performance of this server relative to other <em>running</em> containers on the
                        system. Value should be between <code>10</code> and <code>1000</code>. Please see
                        <a href="https://docs.docker.com/engine/reference/run/#block-io-bandwidth-blkio-constraint"
                            target="_blank">this documentation</a> for more information about it.
                    </small>
                </div>
            </div>

            <div class="form-group col-4">
                <label for="block_io_weight">Enable OOM Killer</label>
                <div class="input-group mb-2">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" name="OOM_KILLER" id="customCheck1"
                            value="1" @if($package->data('OOM_KILLER', false)) checked @endif/>
                        <label class="custom-control-label" for="customCheck1">Terminates the server if it breaches
                            the
                            memory limits.</label>
                    </div>
                </div>
            </div>
        </div>

        <hr>
        <div class="row">
            <div class="form-group col-6">
                <label for="location">Allowed Locations</label>
                <div class="input-group mb-2">
                    <select name="locations[]" id="location" class="form-control select2 select2-hidden-accessible"
                        multiple="" tabindex="-1" aria-hidden="true">
                        @foreach(PterodactylLocation::get() as $location) 
                            <option value="{{ $location->id }}" @if(in_array($location->id, $package->data('locations', []))) selected @endif>{{ $location->name }}</option>
                        @endforeach
                    </select>
                    <small class="form-text text-muted">Select the locations that the user is able to select to deploy their server on</small>
                </div>
            </div>

            <div class="form-group col-6">
                <label for="egg">Egg</label>
                <div class="input-group mb-2">
                    <select name="egg" id="egg" class="form-control select2 select2-hidden-accessible"
                        tabindex="-1" aria-hidden="true" onchange="setEgg()">
                        @foreach(Pterodactyl::getEggs() as $egg)
                            <option value="{{ json_encode($egg['attributes']) }}" @if($package->data('egg', false) AND json_decode($package->data('egg'))->id == $egg['attributes']['id']) selected @endif>{{ $egg['attributes']['name'] }}</option>
                        @endforeach
                    </select>
                    <small class="form-text text-muted">Select the Nest that this server will be grouped under.</small>
                </div>
            </div>
        </div>

    <div class="row">
        @if(json_decode($package->data('egg')) !== NULL)

            <div class="form-group col-6">
                <label for="docker_image">Docker Image</label>
                <div class="input-group mb-2">
                    <div class="input-group-append">
                        <div class="input-group-text" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="cursor: pointer"><i class="fas fa-chevron-circle-down"></i></i></div>
                        <div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(0px, 28px, 0px); top: 0px; left: 0px; will-change: transform;">
                            @foreach(json_decode($package->data('egg'))->docker_images as $key => $image)
                                <button type="button" class="dropdown-item" style="cursor: pointer"  onclick="document.getElementById('docker_image').value = '{{$image}}';">{{ $key }}</button>
                            @endforeach
                        </div>
                    </div>

                    <input type="text" class="form-control text-left" name="docker_image" id="docker_image"
                        value="{{ $package->data('docker_image', json_decode($package->data('egg'))->docker_image ) }}"
                        />
                    <small class="form-text text-muted">
                        This is the default Docker image that will be used to run this server. Select an image from the dropdown above, or enter a custom image in the text field.                    
                    </small>
                </div>
            </div>

            <div class="form-group col-6">
                <label for="excluded_variables[]">Exclude variables from checkout</label>
                <div class="input-group mb-2">
                    <select name="excluded_variables[]" id="excluded_variables[]" class="form-control select2 select2-hidden-accessible"
                    multiple="" tabindex="-1" aria-hidden="true">
                        @foreach(json_decode($package->data('egg'))->relationships->variables->data as $variable)
                            @if($variable->attributes->user_editable AND $variable->attributes->user_viewable)
                                <option value="{{ $variable->attributes->env_variable }}" @if(in_array($variable->attributes->env_variable, $package->data('excluded_variables', []))) selected @endif>{{ $variable->attributes->name }}</option>
                            @endif
                        @endforeach
                    </select>
                    <small class="form-text text-muted">Select variables you do not want users to be able to modify at checkout</small>
                </div>
            </div>
            
            <div class="form-group col-12">
                <label>Startup Command</label>
                <input type="text" class="form-control" id="startup" value="{{ $package->data('startup', json_decode($package->data('egg'))->startup ) }}" name="startup">
                <small class="form-text text-muted">
                    The following data substitutes are available for the startup command: <code>&#123;&#123;SERVER_MEMORY&#125;&#125;</code>, <code>&#123;&#123;SERVER_IP&#125;&#125;</code>, and <code>&#123;&#123;SERVER_PORT&#125;&#125;}</code>. They will be replaced with the allocated memory, server IP, and server port respectively.
                </small>
            </div>

            <div class="col-12">
                <hr>
            </div>

            @foreach(json_decode($package->data('egg'))->relationships->variables->data as $variable)
            <div class="form-group col-6">
                <label>{{ $variable->attributes->name }}</label>
                <input type="text" class="form-control" value="@isset($package->data('environment')[$variable->attributes->env_variable]){{ $package->data('environment')[$variable->attributes->env_variable] }}@else{{ $variable->attributes->default_value }}@endisset" name="environment[{{ $variable->attributes->env_variable }}]" @if(Str::contains($variable->attributes->rules, 'required')) @endif> 
                <small class="form-text text-muted">
                    {{ $variable->attributes->description }} <br><br>
                    <strong>Access in Startup:</strong><code> &#123;&#123;{{ $variable->attributes->env_variable }}&#125;&#125;</code><br>
                    <strong>Validation Rules</strong><code> {{ $variable->attributes->rules }}</code>
                </small>
            </div>
            @endforeach
        @endif
    </div>

    <div class="text-right">
        <button class="btn btn-dark" id="submit" type="submit">Update</button>
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