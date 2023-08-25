@extends(AdminTheme::wrapper(), ['title' => __('admin.eggs'), 'keywords' => 'WemX Dashboard, WemX Panel'])

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
                    {!! __('admin.eggs') !!}
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
                            <th>{!! __('admin.name') !!}</th>
                            <th>{!! __('admin.nest') !!}</th>
                            <th>{!! __('admin.variables') !!}</th>
                            <th class="text-right">{!! __('admin.actions') !!}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($eggs as $egg)
                            <tr>
                                <td>{{ $egg['name'] }}</td>
                                <td>{{ $egg['nest_name'] }}</td>
                                <td>{{ count($egg['variables']) }}</td>

                                <td class="text-right">
                                    <a class="btn btn-primary" href="{{ route('pterodactyl.egg_manage', ['egg' => $egg['id']]) }}">
                                        {!! __('admin.manage') !!}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>

@endsection
