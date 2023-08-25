@extends(AdminTheme::wrapper(), ['title' => __('admin.locations'), 'keywords' => 'WemX Dashboard, WemX Panel'])

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
                <div class="card-header">{!! __('admin.locations') !!}</div>

                <div class="card-body">
                    <button type="button" class="btn btn-primary" data-toggle="modal"
                            data-target="#createLocationModal"><i
                            class="fas fa-solid fa-plus"></i> {!! __('client.create_location') !!}</button>
                    <hr>
                    @if($locations->count() == 0)
                        @include(AdminTheme::path('empty-state'), ['title' => 'We couldn\'t find any categories', 'description' => 'You haven\'t created any categories yet.'])
                    @else
                        <table class="table">
                            <thead>
                            <tr>
                                <th>{!! __('admin.id') !!}</th>
                                <th>{!!__('admin.name') !!}</th>
                                <th>{!!__('admin.country') !!}</th>
                                <th>{!!__('admin.stock') !!}</th>
                                <th>{!!__('admin.pterodactyl_id', ['default' => 'Pterodactyl ID']) !!}</th>
                                <th class="text-right">{!! __('admin.actions') !!}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($locations as $location)
                                <tr>
                                    <td>#{{ $location->id }}</td>
                                    <td>{{ $location->name }}</td>
                                    <td>{{ $location->country_code }}</td>
                                    <td>{{ $location->inStock() }} ({{ $location->stock }})</td>
                                    <td>{{ $location->location_id }}</td>
                                    <td class="text-right">
                                        <button type="button" class="btn btn-primary" data-toggle="modal"
                                                data-target="#editLocationModal{{ $location->id }}">
                                            {!! __('admin.edit') !!}
                                        </button>
                                    </td>
                                </tr>

                                <!-- Edit Modal -->
                                <div class="modal fade" id="editLocationModal{{ $location->id }}" tabindex="-1"
                                     role="dialog" aria-labelledby="editLocationModalLabel{{ $location->id }}"
                                     aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="editLocationModalLabel{{ $location->id }}">
                                                    Edit Location #{{ $location->id }}</h5>
                                                <button type="button" class="close" data-dismiss="modal"
                                                        aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <form
                                                action="{{route('pterodactyl.locations.update', ['location' => $location->id])}}"
                                                method="POST">
                                                @csrf
                                                <div class="modal-body">
                                                    <div class="form-group col-md-12 col-12">
                                                        <label>{!! __('admin.display_name') !!}</label>
                                                        <input type="text" class="form-control" name="name"
                                                               value="{{ $location->name }}" required/>
                                                        <small class="form-text text-muted">
                                                            {!! __('admin.ptero_display_name_desc') !!}
                                                        </small>
                                                    </div>

                                                    <div class="form-group col-md-12 col-12">
                                                        <label for="country_code">{!! __('admin.country') !!}</label>
                                                        <select class="form-control select2 select2-hidden-accessible"
                                                                name="country_code" tabindex="-1" aria-hidden="true">
                                                            @foreach (config('utils.countries') as $key => $country)
                                                                <option value="{{ $key }}"
                                                                        @if($location->country_code == $key) selected @endif>{{ $country }}</option>
                                                            @endforeach
                                                        </select>
                                                        <small class="form-text text-muted">
                                                            {!! __('admin.field_not_displayed_user') !!}
                                                        </small>
                                                    </div>

                                                    <div class="form-group col-md-12 col-12">
                                                        <label for="location_id">{!! __('admin.pterodactyl_location') !!}</label>
                                                        <select class="form-control select2 select2-hidden-accessible"
                                                                name="location_id" tabindex="-1" aria-hidden="true">
                                                            @foreach ($pterodactyl_locations as $pterodactyl)
                                                                <option value="{{ $pterodactyl['attributes']['id'] }}"
                                                                        @if($location->location_id == $pterodactyl['attributes']['id']) selected @endif>{{ $pterodactyl['attributes']['short'] }}</option>
                                                            @endforeach
                                                        </select>
                                                        <small class="form-text text-muted">
                                                            {!! __('admin.pterodactyl_location_desc') !!}</small>
                                                    </div>

                                                    <div class="form-group col-md-12 col-12">
                                                        <label>{!! __('admin.stock') !!}</label>
                                                        <input type="number" class="form-control" name="stock"
                                                               value="{{ $location->stock }}" required/>
                                                        <small class="form-text text-muted">
                                                            {!! __('admin.ptero_stock_desc') !!}</small>
                                                    </div>

                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                            data-dismiss="modal">{!! __('admin.close') !!}
                                                    </button>
                                                    <button type="submit" class="btn btn-primary">{!! __('admin.update_location') !!}
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            @endif
                            </tbody>
                        </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Modal -->
    <div class="modal fade" id="createLocationModal" tabindex="-1" role="dialog"
         aria-labelledby="createLocationModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createLocationModalLabel">{!! __('admin.create_location') !!}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{route('pterodactyl.locations.store')}}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group col-md-12 col-12">
                            <label>{!! __('admin.display_name') !!}</label>
                            <input type="text" class="form-control" name="name" placeholder="London, UK" required/>
                            <small class="form-text text-muted">{!! __('admin.ptero_display_name_desc') !!}</small>
                        </div>

                        <div class="form-group col-md-12 col-12">
                            <label for="country_code">{!! __('admin.country') !!}</label>
                            <select class="form-control select2 select2-hidden-accessible" name="country_code"
                                    tabindex="-1" aria-hidden="true">
                                @foreach (config('utils.countries') as $key => $country)
                                    <option value="{{ $key }}">{{ $country }}</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">{!! __('admin.field_not_displayed_user') !!}</small>
                        </div>

                        <div class="form-group col-md-12 col-12">
                            <label for="user">{!! __('admin.pterodactyl_location') !!}</label>
                            <select class="form-control select2 select2-hidden-accessible" name="location_id"
                                    tabindex="-1" aria-hidden="true">
                                @foreach ($pterodactyl_locations as $pterodactyl)
                                    <option
                                        value="{{ $pterodactyl['attributes']['id'] }}">{{ $pterodactyl['attributes']['short'] }}</option>
                                @endforeach
                                <small class="form-text text-muted">{!! __('admin.pterodactyl_location_desc') !!}</small>
                            </select>
                        </div>

                        <div class="form-group col-md-12 col-12">
                            <label>{!! __('admin.stock') !!}</label>
                            <input type="number" class="form-control" name="stock" value="-1" required/>
                            <small class="form-text text-muted">{!! __('admin.ptero_stock_desc') !!}</small>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{!! __('admin.close') !!}</button>
                        <button type="submit" class="btn btn-primary">{!! __('admin.create_location') !!}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
