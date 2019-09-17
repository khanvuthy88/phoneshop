@extends('layouts/backend')
@section('title', trans('app.product'))
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
            <h3 class="page-heading">{{ trans('app.product') . ' - ' . $title }}</h3>
            @include('partial/flash-message')
            <form id="form-product" method="post" action="{{ route('product.save', $product) }}" enctype="multipart/form-data">
                <input type="hidden" name="form_type" value="{{ $formType }}">
                @csrf

                {{-- Button save or edit --}}
                <div class="row">
                    <div class="col-lg-12 text-right">
                        @if ($isFormShowType)
                            @include('partial/anchor-edit', [
                                'href' => route('product.edit', $product->id)
                            ])
                        @else
                            @include('partial/button-save')
                        @endif
                    </div>
                </div>

                <div class="row">
                    {{-- Name --}}
                    <div class="col-lg-6 form-group">
                        <label for="name" class="control-label">
                            {{ trans('app.name') }} <span class="required">*</span>
                        </label>
                        <input type="text" name="name" id="name" class="form-control"
                               value="{{ old('name') ?? $product->name }}" required {{ $disabledFormType }}>
                    </div>

                    {{-- Product category --}}
                    <div class="col-lg-6 form-group">
                        <label for="category" class="control-label">
                            {{ trans('app.product_category') }} <span class="required">*</span>
                        </label>
                        @if ($isFormShowType)
                            <input type="text" class="form-control" value="{{ $product->category->value ?? trans('app.n/a') }}" disabled>
                        @else
                            <select name="category" id="category" class="form-control select2" required>
                                <option value="">{{ trans('app.select_option') }}</option>
                                @foreach ($productCategories as $category)
                                    <option value="{{ $category->id }}" {{ selectedOption($category->id, old('category'), $product->category_id) }}>
                                        {{ $category->value }}
                                    </option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                </div>

                <div class="row">
                    {{-- Brand --}}
                    <div class="col-lg-6 form-group">
                        <label for="brand" class="control-label">
                            {{ trans('app.brand') }} <span class="required">*</span>
                        </label>
                        @if ($isFormShowType)
                            <input type="text" class="form-control" value="{{ brands($product->brand ?? '') }}" disabled>
                        @else
                            <select name="brand" id="brand" class="form-control select2" required>
                                <option value="">{{ trans('app.select_option') }}</option>
                                @foreach ($brands as $brand)
                                    <option value="{{ $brand->id }}" {{ selectedOption($brand->id, old('brand'), $product->brand) }}>
                                        {{ $brand->value }}
                                    </option>
                                @endforeach
                            </select>
                        @endif
                    </div>

                    {{-- Product code/SKU --}}
                    <div class="col-lg-6 form-group">
                        <label for="product_code" class="control-label">
                            {{ trans('app.product_code/sku') }} <span class="required">*</span>
                        </label>
                        <div class="input-group">
                            <input type="text" name="product_code" id="product_code" class="form-control integer-input" required
                                   placeholder="{{ trans('app.code') . ' *' }}" value="{{ old('product_code') ?? $product->code }}">
                            <button type="button" id="generate-code" class="btn btn-primary">{{ trans('app.generate') }}</button>
                            <input type="text" name="product_sku" id="product_sku" class="form-control ml-2"
                                   placeholder="{{ trans('app.sku') }}" value="{{ old('product_sku') ?? $product->sku }}">
                        </div>
                    </div>
                </div>

                <div class="row">
                    {{-- Cost --}}
                    <div class="col-lg-6 form-group">
                        <label for="cost" class="control-label">
                            {{ trans('app.cost') }} ($)
                        </label>
                        <input type="text" name="cost" id="cost" class="form-control decimal-input"
                               value="{{ old('cost') ?? $product->cost }}" {{ $disabledFormType }}>
                    </div>

                    {{-- Price --}}
                    <div class="col-lg-6 form-group">
                        <label for="price" class="control-label">
                            {{ trans('app.selling_price') }} ($) <span class="required">*</span>
                        </label>
                        <input type="text" name="price" id="price" class="form-control decimal-input"
                               value="{{ old('price') ?? $product->price }}" required {{ $disabledFormType }}>
                    </div>
                </div>

                <div class="row">
                    {{-- Alert quantity --}}
                    <div class="col-lg-6 form-group">
                        <label for="alert_quantity" class="control-label">
                            {{ trans('app.alert_quantity') }} <span class="required">*</span>
                        </label>
                        <input type="text" name="alert_quantity" id="alert_quantity" class="form-control integer-input"
                               value="{{ old('alert_quantity') ?? $product->alert_quantity }}" required>
                    </div>

                    {{-- Description --}}
                    <div class="col-lg-6 form-group">
                        <label for="description" class="control-label">
                            {{ trans('app.description') }}
                        </label>
                        <input type="text" name="description" id="description" class="form-control"
                               value="{{ old('description') ?? $product->description }}" {{ $disabledFormType }}>
                    </div>
                </div>

                <div class="row">
                    {{-- Photo --}}
                    <div class="col-lg-6 form-group">
                        <label for="photo" class="control-label">
                            {{ trans('app.photo') }} @empty($product->photo) <span class="required">*</span> @endempty
                        </label>
                        @if ($isFormShowType)
                            <div class="text-left">
                                @if (isset($product->photo))
                                    <img src="{{ asset($product->photo) }}" alt="" width="100%" class="img-responsive">
                                @else
                                    {{ trans('app.no_picture') }}
                                @endif
                            </div>
                        @else
                            <input type="file" name="photo" id="photo" class="form-control" accept=".jpg, .jpeg, .png"
                                   @empty($product->photo) required @endempty>
                        @endif
                    </div>
                </div>

                {{-- Button save or edit --}}
                <div class="row">
                    <div class="col-lg-12 text-right">
                        @if ($isFormShowType)
                            @include('partial/anchor-edit', [
                                'href' => route('product.edit', $product->id)
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
    <script src="{{ asset('js/jquery-number.min.js') }}"></script>
    <script src="{{ asset('js/number.js') }}"></script>
    <script src="{{ asset('js/jquery-mask.min.js') }}"></script>
    <script src="{{ asset('js/mask.js') }}"></script>
    <script src="{{ asset('js/select2.min.js') }}"></script>
    <script src="{{ asset('js/select-box.js') }}"></script>
    <script src="{{ asset('js/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('js/product.js') }}"></script>
@endsection
