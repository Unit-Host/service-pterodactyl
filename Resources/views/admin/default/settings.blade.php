@extends(AdminTheme::wrapper(), ['title' => __('admin.pterodactyl_settings'), 'keywords' => 'WemX Dashboard, WemX Panel'])

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
              <h4>{!! __('admin.pterodactyl_settings') !!}</h4>
            </div>
            <div class="card-body">
                @csrf
              <div class="row">

                <div class="form-group col-6">
                    <label>{!! __('admin.pterodactyl_url') !!} <a href="https://docs.wemx.net/en/third-party/pterodactyl#configuration" target="_blank">{!! __('admin.documentation') !!}</a></label>
                    <input type="url" class="form-control" name="encrypted::pterodactyl::api_url" id="api_url" value="@settings('encrypted::pterodactyl::api_url')" required="">
                    <small class="form-text text-muted">
                        {!! __('admin.pterodactyl_url_desc') !!}
                    </small>
                </div>

                <div class="form-group col-6">
                    <label>{!! __('admin.pterodactyl_api_key') !!}</label>
                    <input type="password" class="form-control" name="encrypted::pterodactyl::api_key" id="api_key" value="@settings('encrypted::pterodactyl::api_key')" required="">
                    <small class="form-text text-muted">
                       {!! __('admin.pterodactyl_api_key_desc') !!}
                    </small>
                </div>

                <div class="form-group col-6">
                  <label>{!! __('admin.pterodactyl_sso_key') !!}</label>
                  <input type="password" class="form-control" name="encrypted::pterodactyl::sso_secret" id="sso_secret" value="@settings('encrypted::pterodactyl::sso_secret')">
                  <small class="form-text text-muted">
                      {!! __('admin.pterodactyl_sso_key_desc') !!} <a href="https://docs.wemx.net/en/third-party/pterodactyl#pterodactyl-sso" target="_blank">{!! __('admin.documentation') !!}</a>
                  </small>
              </div>

              </div>
            </div>
            <div class="card-footer text-right">
              <button type="submit" class="btn btn-primary">{!! __('admin.submit') !!}</button>
            </div>
          </div>
        </form>
    </div>
</div>

@endsection
