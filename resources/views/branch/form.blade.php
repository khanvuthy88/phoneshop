@extends('layouts/backend')
@section('title', trans('app.branch'))
@section('css')
    <link rel="stylesheet" href="{{ asset('css/bootstrap-fileinput.css') }}">
@endsection
@section('content')
    @php
        $isFormShowType = ($formType == FormType::SHOW_TYPE);
        $disabledFormType = ($isFormShowType ? 'disabled' : '');
    @endphp

    <main class="app-content">
        <div class="tile">
            <h3 class="page-heading">{{ trans('app.branch') . ' - ' . $title }}</h3>
            @include('partial/flash-message')

            <form action="{{ route('branch.save', $branch) }}" method="post" class="validated-form" enctype="multipart/form-data">
                @csrf

                {{-- Save or edit button --}}
                <div class="row">
                    <div class="col-lg-12 text-right">
                        @if ($formType == FormType::SHOW_TYPE)
                            @include('partial/anchor-edit', [
                                'href' => route('branch.edit', $branch)
                            ])
                        @else
                            @include('partial/button-save')
                        @endif
                    </div>
                </div>

                <div class="row">
                    {{-- Name --}}
                    <div class="col-lg-6 form-group">
                        <label for="name">
                            {{ trans('app.name') }} <span class="required">*</span>
                        </label>
                        <input type="text" name="name" id="name" class="form-control" required
                               value="{{ old('name') ?? $branch->name }}" {{ $disabledFormType }}>
                    </div>

                    {{-- Location --}}
                    <div class="col-lg-6 form-group">
                        <label for="location">
                            {{ trans('app.location') }} <span class="required">*</span>
                        </label>
                        <input type="text" name="location" id="location" class="form-control" required
                               value="{{ old('location') ?? $branch->location }}" {{ $disabledFormType }}>
                    </div>
                </div>

                <div class="row">
                    {{-- First phone --}}
                    <div class="col-lg-6 form-group">
                        <label for="first_phone">
                            {{ trans('app.first_phone') }} <span class="required">*</span>
                        </label>
                        <input type="text" name="first_phone" id="first_phone" class="form-control phone"
                               value="{{ old('first_phone') ?? $branch->phone_1 }}" required {{ $disabledFormType }}>
                    </div>

                    {{-- Second phone --}}
                    <div class="col-lg-6 form-group">
                        <label for="second_phone">
                            {{ trans('app.second_phone') }}
                        </label>
                        <input type="text" name="second_phone" id="second_phone" class="form-control phone"
                               value="{{ old('second_phone') ?? $branch->phone_2 }}" {{ $disabledFormType }}>
                    </div>
                </div>

                <div class="row">
                    {{-- Third phone --}}
                    <div class="col-lg-6 form-group">
                        <label for="third_phone">
                            {{ trans('app.third_phone') }}
                        </label>
                        <input type="text" name="third_phone" id="third_phone" class="form-control phone"
                               value="{{ old('third_phone') ?? $branch->phone_3 }}" {{ $disabledFormType }}>
                    </div>

                    {{-- Fourth phone --}}
                    <div class="col-lg-6 form-group">
                        <label for="fourth_phone">
                            {{ trans('app.fourth_phone') }}
                        </label>
                        <input type="text" name="fourth_phone" id="fourth_phone" class="form-control phone"
                               value="{{ old('fourth_phone') ?? $branch->phone_4 }}" {{ $disabledFormType }}>
                    </div>
                </div>

                <div class="row">
                    {{-- Branch type --}}
                    <div class="col-lg-6 form-group">
                        <label for="type">{{ trans('app.branch_type') }} <span class="required">*</span></label>
                        @if ($isFormShowType)
                            <input type="text" class="form-control" value="{{ branchTypes($branch->type ?? '') }}" disabled>
                        @else
                            <select name="type" id="type" class="form-control select2-no-search" required>
                                <option value="">{{ trans('app.select_option') }}</option>
                                @foreach ($branchTypes as $typeKey => $typeTitle)
                                    <option value="{{ $typeKey }}" {{ selectedOption($typeKey, old('type'), $branch->type) }}>
                                        {{ $typeTitle }}
                                    </option>
                                @endforeach
                            </select>
                        @endif
                    </div>

                    {{-- Address --}}
                    <div class="col-lg-6 form-group">
                        <label for="address">{{ trans('app.address') }}</label>
                        <input type="text" name="address" id="address" class="form-control"
                               value="{{ old('address') ?? $branch->address }}" {{ $disabledFormType }}>
                    </div>
                </div>

                <div class="row">
                    {{-- First logo --}}
                    <div class="col-lg-6 form-group">
                        <label for="first_logo" class="control-label">
                            {{ trans('app.first_logo') }}
                        </label>
                        @if ($isFormShowType)
                            <div class="text-left">
                                @if (isset($branch->logo))
                                    <img src="{{ asset($branch->logo) }}" alt="" width="50%" class="img-responsive">
                                @else
                                    {{ trans('app.no_picture') }}
                                @endif
                            </div>
                        @else
                            <input type="file" name="first_logo" id="first_logo" class="form-control" accept=".jpg, .jpeg, .png">
                        @endif
                    </div>

                    {{-- Second logo --}}
                    <div class="col-lg-6 form-group">
                        <label for="second_logo" class="control-label">
                            {{ trans('app.second_logo') }}
                        </label>
                        @if ($isFormShowType)
                            <div class="text-left">
                                @if (isset($branch->logo_2))
                                    <img src="{{ asset($branch->logo_2) }}" alt="" width="50%" class="img-responsive">
                                @else
                                    {{ trans('app.no_picture') }}
                                @endif
                            </div>
                        @else
                            <input type="file" name="second_logo" id="second_logo" class="form-control" accept=".jpg, .jpeg, .png">
                        @endif
                    </div>
                </div>
                <div class="row">
                    {{-- Contract text --}}
                    <div class="col-lg-6 form-group">
                        <label for="contract_text">
                            {{ trans('app.contract_text') }}
                        </label>
                        <textarea name="contract_text" id="contract_text" class="tinymce">{{ old('contract_text') ?? $branch->contract_text }}</textarea>
                    </div>
                </div>

                {{-- Save or edit button --}}
                <div class="row">
                    <div class="col-lg-12 text-right">
                        @if ($formType == FormType::SHOW_TYPE)
                            @include('partial/anchor-edit', [
                                'href' => route('branch.edit', $branch)
                            ])
                        @else
                            @include('partial/button-save')
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </main>
@endsection
@section('js')
    <script src="{{ asset('js/bootstrap-fileinput.js') }}"></script>
    <script src="{{ asset('js/bootstrap-fileinput-fa-theme.js') }}"></script>
    <script src="{{ asset('js/init-file-input.js') }}"></script>
    <script src="{{ asset('plugins/tinymce/tinymce.min.js') }}"></script>
    <script src="{{ asset('js/tinymce.js') }}"></script>
    <script src="{{ asset('js/select2.min.js') }}"></script>
    <script src="{{ asset('js/select-box.js') }}"></script>
    <script src="{{ asset('js/jquery-mask.min.js') }}"></script>
    <script src="{{ asset('js/mask.js') }}"></script>
    <script src="{{ asset('js/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('js/form-validation.js') }}"></script>
    <script src="{{ asset('js/branch-form.js') }}"></script>
@endsection
