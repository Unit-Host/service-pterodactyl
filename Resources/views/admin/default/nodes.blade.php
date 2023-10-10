@extends(AdminTheme::wrapper(), ['title' => __('admin.nodes'), 'keywords' => 'WemX Dashboard, WemX Panel'])

@section('css_libraries')
    <link rel="stylesheet" href="{{ asset(AdminTheme::assets('modules/summernote/summernote-bs4.css')) }}"/>
    <link rel="stylesheet" href="{{ asset(AdminTheme::assets('modules/select2/dist/css/select2.min.css')) }}">

@endsection

@section('js_libraries')
    <script src="{{ asset(AdminTheme::assets('modules/summernote/summernote-bs4.js')) }}"></script>
    <script src="{{ asset(AdminTheme::assets('modules/select2/dist/js/select2.full.min.js')) }}"></script>
@endsection

@section('container')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    {!! __('admin.nodes') !!}
                    <a href="{{ route('pterodactyl.clear_cache') }}" class="btn btn-info"
                       onclick="return confirm('{!! __('admin.clear_cache_desc') !!}')">
                        {!! __('admin.clear_cache') !!}
                    </a>
                </div>

                <div class="card-body">

                    <hr>
                    <table class="table">
                        <thead>
                        <tr>
                            <th>{!! __('admin.uuid') !!}</th>
                            <th>{!! __('admin.name') !!}</th>
                            <th>{!! __('admin.auto_ports') !!}</th>
                            <th>{!! __('admin.ports_range') !!}</th>
                            <th>{!! __('admin.ip') !!}</th>
                            <th>{!! __('admin.location_id') !!}</th>
                            <th class="text-right">{!! __('admin.actions') !!}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($nodes as $node)
                            @php
                                $nodeModel = node()::firstOrCreate(
                                    ['node_id' => $node['id']],
                                    ['node_id' => $node['id'], 'location_id' => $node['location_id'], 'fqdn' => $node['fqdn'], 'name' => $node['name']]
                                );
                            @endphp
                            <tr>
                                <td>{{ $node['uuid'] }}</td>
                                <td>{{ $node['name'] }}</td>
                                <td>@if($nodeModel->auto_ports == 1) {!! __('admin.active') !!}@else {!! __('admin.inactive') !!}@endif</td>
                                <td>{{ $nodeModel->getPortRange() }}</td>
                                <td>{{ $nodeModel->getIp() }}</td>
                                <td>{{ $nodeModel->location_id }}</td>

                                <td class="text-right">
                                    <button type="button" class="btn btn-primary" data-toggle="modal"
                                            data-target="#nodeModal{{ $node['id'] }}">
                                        {!! __('admin.manage') !!}
                                    </button>
                                </td>
                            </tr>

                            <div class="modal fade" id="nodeModal{{ $node['id'] }}" tabindex="-1"
                                 role="dialog" aria-labelledby="nodeModalLabel{{ $node['id'] }}"
                                 aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="nodeModalLabel{{ $node['id'] }}">
                                                {!! __('admin.pterodactyl_edit_node') !!} #{{ $node['id'] }}</h5>
                                            <button type="button" class="close" data-dismiss="modal"
                                                    aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <form action="{{route('pterodactyl.nodes.store')}}" method="POST">
                                            @csrf
                                            <input type="hidden" name="node_id" value="{{ $node['id'] }}">
                                            <input type="hidden" name="location_id" value="{{ $node['location_id'] }}">
                                            <div class="modal-body">

                                                <div class="form-group col-md-12 col-12">
                                                    <label for="ports_range">{!! __('admin.ports_rang_label') !!}</label>
                                                    <input type="text" class="form-control" name="ports_range"
                                                           value="{{ $nodeModel->getPortRange() }}" required/>
                                                    <small class="form-text text-muted">
                                                        {!! __('admin.ports_rang_desc') !!}
                                                    </small>
                                                </div>


                                                <div class="form-group col-md-12 col-12">
                                                    <label for="ip">{!! __('admin.allocation_addresses_label') !!}</label>
                                                    <input type="text" class="form-control" name="ip"
                                                           value="{{ $nodeModel->getIp() }}" required/>
                                                    <small class="form-text text-muted">
                                                        {!! __('admin.allocation_addresses_desc') !!}
                                                    </small>
                                                </div>

                                            </div>

                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                        data-dismiss="modal">{!! __('admin.close') !!}
                                                </button>
                                                <button type="submit"
                                                        class="btn btn-primary">{!! __('admin.update') !!}
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>

@endsection
