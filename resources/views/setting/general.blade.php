@extends('layouts/backend')
@section('title', trans('app.general_setting'))
@section('css')
    <link rel="stylesheet" href="{{ asset('css/bootstrap-fileinput.css') }}">
@endsection
@section('content')
    <div class="app-content">
        <div class="tile">
            <h3 class="page-heading">{{ trans('app.general_setting') }}</h3>
            @include('partial.flash-message')

            <form action="{{ route('general_setting.save') }}" class="validated-form" method="post" enctype="multipart/form-data">
                @csrf

                <div class="row">
                    {{-- Site title --}}
                    <div class="col-lg-6 form-group">
                        <label for="site_title" class="control-label">
                            {{ trans('app.site_title') }} <span class="required">*</span>
                        </label>
                        <input type="text" name="site_title" id="site_title" class="form-control"
                               value="{{ old('site_title') ?? $setting->site_title }}" required>
                    </div>

                    {{-- Site logo --}}
                    <div class="col-lg-6 form-group">
                        <label for="site_logo" class="control-label">
                            {{ trans('app.site_logo') }}
                        </label>
                        <input type="file" name="site_logo" id="site_logo" class="form-control" accept=".jpg, .jpeg, .png">

                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12 text-right">
                        @include('partial.button-save')
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
@section('js')
    <script src="{{ asset('js/bootstrap-fileinput.js') }}"></script>
    <script src="{{ asset('js/bootstrap-fileinput-fa-theme.js') }}"></script>
    <script src="{{ asset('js/init-file-input.js') }}"></script>
    <script src="{{ asset('js/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('js/form-validation.js') }}"></script>
    <script src="{{ asset('js/general-setting.js') }}"></script>
@endsection
