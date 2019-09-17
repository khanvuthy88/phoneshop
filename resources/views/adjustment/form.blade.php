@extends('layouts/backend')
@section('title', trans('app.stock_adjustment'))
@section('css')
    <link rel="stylesheet" href="{{ asset('css/bootstrap-fileinput.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sweetalert.css') }}">
@endsection
@section('content')
    <main class="app-content">
        <div class="tile">
            <h3 class="page-heading">{{ trans('app.stock_adjustment') . ' - ' . $title }}</h3>
            @include('partial/flash-message')

            <form method="post" id="adjustment-form" class="validated-form no-auto-submit"
                  action="{{ route('adjustment.save') }}" enctype="multipart/form-data">
                @csrf

                <div class="row">
                    {{-- Adjustment action --}}
                    <div class="col-lg-4 form-group">
                        <label for="action" class="control-label">
                            {{ trans('app.action') }} <span class="required">*</span>
                        </label>
                        <select name="action" id="action" class="form-control select2-no-search" required>
                            <option value="">{{ trans('app.select_option') }}</option>
                            @foreach ($stockTypes as $typeKey => $typeTitle)
                                <option value="{{ $typeKey }}" {{ selectedOption($typeKey, old('action')) }}>
                                    {{ $typeTitle }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Warehouse --}}
                    <div class="col-lg-4 form-group">
                        <label for="warehouse" class="control-label">
                            {{ trans('app.location') }} <span class="required">*</span>
                        </label>
                        <select name="warehouse" id="warehouse" class="form-control select2" required>
                            <option value="">{{ trans('app.select_option') }}</option>
                            @foreach ($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" {{ selectedOption($warehouse->id, old('warehouse')) }}>
                                    {{ $warehouse->location }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Product --}}
                    <div class="col-lg-4 form-group">
                        <label for="product" class="control-label">
                            {{ trans('app.product') }} <span class="required">*</span>
                        </label>
                        <select name="product" id="product" class="form-control select2" required>
                            <option value="">{{ trans('app.select_option') }}</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}" {{ selectedOption($product->id, old('product')) }}
                                        data-name="{{ $product->name }}" data-code="{{ $product->code }}"
                                        data-stock-qty="{{ $product->stock_qty }}">
                                    {{ $product->name }} ({{ trans('app.code') . ' : ' . ($product->code ?? trans('app.none')) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row">
                    {{-- Quantity --}}
                    <div class="col-lg-4 form-group">
                        <label for="quantity" class="control-label">
                            {{ trans('app.quantity') }} <span class="required">*</span>
                            ({{ trans('app.in-stock_quantity') }} : <span id="stock-qty">{{ $productStockQty }}</span>)
                        </label>
                        <input type="text" name="quantity" id="quantity" class="form-control integer-input"
                               value="{{ old('quantity') }}" min="1" max="10000" required>
                    </div>

                    {{-- Adjustment date --}}
                    <div class="col-lg-4 form-group">
                        <label for="adjustment_date" class="control-label">
                            {{ trans('app.adjustment_date') }} <span class="required">*</span>
                        </label>
                        <input type="text" name="adjustment_date" id="adjustment_date" class="form-control date-picker" required
                               placeholder="{{ trans('app.date_placeholder') }}" value="{{ old('adjustment_date') ?? date('d-m-Y') }}">
                    </div>

                    {{-- Reason --}}
                    <div class="col-lg-4 form-group">
                        <label for="note" class="control-label">
                            {{ trans('app.reason') }} <span class="required">*</span>
                        </label>
                        <input type="text" name="reason" id="reason" class="form-control" value="{{ old('reason') }}" required>
                    </div>
                </div>

                {{-- Button save or edit --}}
                <div class="row">
                    <div class="col-lg-12 text-right">
                        @include('partial.button-save', ['onClick' => 'confirmFormSubmission($("#adjustment-form"))'])
                    </div>
                </div>
            </form>
        </div>
    </main>
@endsection
@section('js')
    <script>
        var stockQtyRetrievalUrl = '{{ route('adjustment.get_stock_quantity', [':warehouseId', ':productId']) }}';
        var NALabel = '{{ trans('app.n/a') }}';
    </script>
    <script src="{{ asset('js/bootstrap-fileinput.js') }}"></script>
    <script src="{{ asset('js/bootstrap-fileinput-fa-theme.js') }}"></script>
    <script src="{{ asset('js/init-file-input.js') }}"></script>
    <script src="{{ asset('js/jquery-number.min.js') }}"></script>
    <script src="{{ asset('js/number.js') }}"></script>
    <script src="{{ asset('js/jquery-mask.min.js') }}"></script>
    <script src="{{ asset('js/mask.js') }}"></script>
    <script src="{{ asset('js/date-time-picker.js') }}"></script>
    <script src="{{ asset('js/select2.min.js') }}"></script>
    <script src="{{ asset('js/select-box.js') }}"></script>
    <script src="{{ asset('js/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('js/form-validation.js') }}"></script>
    <script src="{{ asset('js/sweetalert.min.js') }}"></script>
    <script src="{{ asset('js/adjustment.js') }}"></script>
@endsection
