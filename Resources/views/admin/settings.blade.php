@extends(AdminTheme::wrapper(), ['title' => 'Dashboard', 'keywords' => 'WemX Dashboard, WemX Panel'])

@section('css_libraries')
<link rel="stylesheet" href="{{ asset(AdminTheme::assets('modules/summernote/summernote-bs4.css')) }}" />
<link rel="stylesheet" href="{{ asset(AdminTheme::assets('modules/select2/dist/css/select2.min.css')) }}">

@endsection

@section('js_libraries')
<script src="{{ asset(AdminTheme::assets('modules/summernote/summernote-bs4.js')) }}"></script>
<script src="{{ asset(AdminTheme::assets('modules/select2/dist/js/select2.full.min.js')) }}"></script>
@endsection

@section('container')
<div class="row">
    <div class="col-12">
        <div class="card">
            <form action="{{ route('admin.settings.store') }}" method="POST">
            <div class="card-header">
              <h4>Pterodactyl Settings</h4>
            </div>
            <div class="card-body">
                @csrf
              <div class="row">

                <div class="form-group col-6">
                    <label>Pterodactyl URL</label>
                    <input type="url" class="form-control" name="encrypted::pterodactyl::api_url" id="api_url" value="@settings('encrypted::pterodactyl::api_url')" required="">
                    <small class="form-text text-muted">
                        Enter the URL to your Pterodactyl panel to allow the API to communicate with Pterodactyl. Example: <code>https://panel.example.com</code>
                    </small>
                </div>

                <div class="form-group col-6">
                    <label>Pterodactyl API Key</label>
                    <input type="password" class="form-control" name="encrypted::pterodactyl::api_key" id="api_key" value="@settings('encrypted::pterodactyl::api_key')" required="">
                    <small class="form-text text-muted">
                        You can generate a new api key on <code>https://panel.pterodactyl.com/admin/api</code> Make sure to give READ & WRITE permissions to all permissions
                    </small>
                </div>

                <div class="form-group col-6">
                  <label>Pterodactyl Client API Key</label>
                  <input type="password" class="form-control" name="encrypted::pterodactyl::client_api" id="client_api" value="@settings('encrypted::pterodactyl::client_api')" required="">
                  <small class="form-text text-muted">
                      You can generate a new api key on <code>https://panel.pterodactyl.com/account/api</code> Make sure you are an admin user.
                  </small>
              </div>

              </div>
            </div>
            <div class="card-footer text-right">
              <button type="submit" class="btn btn-primary">Submit</button>
            </div>
          </div>
        </form>
    </div>
</div>

@endsection