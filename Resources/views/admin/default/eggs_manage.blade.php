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
                    {!! __('admin.variables') !!}
                    <a href="{{ route('pterodactyl.clear_cache') }}" class="btn btn-info"
                       onclick="return confirm('{!! __('admin.clear_cache_desc') !!}')">
                        {!! __('admin.clear_cache') !!}
                    </a>
                </div>


                <div class="card-body">
                    <a class="btn btn-link" href="{{ route('pterodactyl.clear_cache') }}"
                       onclick="return confirm('{!! __('admin.clear_cache_desc') !!}')">{!! __('admin.pterodactyl_cache_btn_desc') !!} {!! __('admin.clear_cache') !!}</a><br>
{{--                    <span>{!! __('admin.available_placeholders') !!}  <strong>AUTO_PORT, USERNAME, RANDOM_TEXT, RANDOM_NUMBER, NODE_IP</strong></span>--}}
                    <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#placeholdersInfo" aria-expanded="false" aria-controls="placeholdersInfo">
                        <span>{!! __('admin.available_placeholders') !!}  <strong>AUTO_PORT, USERNAME, RANDOM_TEXT, RANDOM_NUMBER, NODE_IP</strong></span>
                    </button>
                    <div class="collapse m-3" id="placeholdersInfo">
                        <p>Placeholders work for any variable</p>
                        <p><strong>AUTO_PORT</strong> - Generates a port and adds additional allocation for the server</p>
                        <p><strong>USERNAME</strong> - Assigns the username to the variable</p>
                        <p><strong>RANDOM_TEXT</strong> - Generates a random text of 10 characters</p>
                        <p><strong>RANDOM_NUMBER</strong> - Generates a 10-digit random number</p>
                        <p><strong>NODE_IP</strong> - Sets the IP address of the node being used</p>
                    </div>
                    <hr>
                    <form action="{{ route('pterodactyl.egg_manage_store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="egg_id" value="{{ $egg->egg_id }}">
                    <table id="variablesTable" class="table">
                        <thead>
                        <tr>
                            <th>{!! __('admin.variable') !!}</th>
                            <th>{!! __('admin.key') !!}</th>
                            <th>{!! __('admin.value') !!}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($egg->variables as $var)
                            <tr>
                                <input type="hidden" name="var_id" value="{{ $var['id'] }}">
                                <td>{{ $var['name'] }}</td>
                                <td>{{ $var['env_variable'] }}</td>
                                <td><input class="form-control" name="{{ $var['env_variable'] }}" value="{{ $var['default_value'] }}"></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                        <div class="text-right mr-4"><button type="submit" class="btn btn-primary">{{__('admin.submit')}}</button></div>

                    </form>

                </div>
            </div>
        </div>
    </div>
@endsection
