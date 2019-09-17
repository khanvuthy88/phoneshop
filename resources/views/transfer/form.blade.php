@extends('layouts/backend')
@section('title', trans('app.stock_transfer'))
@section('css')
    <link rel="stylesheet" href="{{ asset('css/bootstrap-fileinput.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sweetalert.css') }}">
@endsection
@section('content')
    <main class="app-content">
        <div class="tile">
            <h3 class="page-heading">{{ trans('app.stock_transfer') . ' - ' . $title }}</h3>
            @include('partial/flash-message')

            <form method="post" id="transfer-form" class="validated-form no-auto-submit"
                  action="{{ route('transfer.save') }}" enctype="multipart/form-data">
                @csrf

                <div class="row">
                    {{-- Original warehouse --}}
                    <div class="col-lg-4 form-group">
                        <label for="original_warehouse" class="control-label">
                            {{ trans('app.original_location') }} <span class="required">*</span>
                        </label>
                        <select name="original_warehouse" id="original_warehouse" class="form-control select2" required>
                            <option value="">{{ trans('app.select_option') }}</option>
                            @foreach ($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" {{ selectedOption($warehouse->id, old('original_warehouse')) }}>
                                    {{ $warehouse->location }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Target warehouse --}}
                    <div class="col-lg-4 form-group">
                        <label for="target_warehouse" class="control-label">
                            {{ trans('app.target_location') }} <span class="required">*</span>
                        </label>
                        <select name="target_warehouse" id="target_warehouse" class="form-control select2" required>
                            <option value="">{{ trans('app.select_option') }}</option>
                            @foreach ($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" {{ selectedOption($warehouse->id, old('target_warehouse')) }}>
                                    {{ $warehouse->location }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Transfer status --}}
                    <div class="col-lg-4 form-group">
                        <label for="status" class="control-label">
                            {{ trans('app.transfer_status') }} <span class="required">*</span>
                        </label>
                        <select name="status" id="status" class="form-control select2-no-search" required>
                            @foreach ($transferStatuses as $statusKey => $statusTitle)
                                <option value="{{ $statusKey }}" {{ selectedOption($statusKey, old('status')) }}>
                                    {{ $statusTitle }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row">
                    {{-- Product --}}
                    <div class="col-lg-4 form-group">
                        <label for="product" class="control-label">
                            {{ trans('app.product') }}
                        </label>
                        <div class="input-group">
                            <select name="product" id="product" class="form-control select2">
                                <option value="">{{ trans('app.select_option') }}</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}" {{ selectedOption($product->id, old('product')) }}
                                        data-name="{{ $product->name }}" data-code="{{ $product->code }}"
                                        data-stock-qty="{{ $product->stock_qty }}">
                                        {{ $product->name }} ({{ trans('app.code') . ' : ' . ($product->code ?? trans('app.none')) }})
                                    </option>
                                @endforeach
                            </select>
                            <button type="button" id="add-product" class="btn btn-primary">{{ trans('app.add') }}</button>
                        </div>
                    </div>
                </div>

                {{-- Product list --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>{{ trans('app.product_table') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="table-responsive">
                                    <table id="product-table" class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>{{ trans('app.name') }}</th>
                                                <th>{{ trans('app.code') }}</th>
                                                <th>{{ trans('app.in-stock_quantity') }}</th>
                                                <th>{{ trans('app.transfer_quantity') }}</th>
                                                <th>{{ trans('app.delete') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {{-- When form validation has error (s) --}}
                                            @foreach ($transferredProducts as $product)
                                                <tr data-id="{{ $product['id'] }}">
                                                    <input type="hidden" name="products[{{ $product['id'] }}][id]" value="{{ $product['id'] }}">
                                                    <input type="hidden" name="products[{{ $product['id'] }}][name]" value="{{ $product['name'] }}">
                                                    <input type="hidden" name="products[{{ $product['id'] }}][code]" value="{{ $product['code'] }}">
                                                    <input type="hidden" name="products[{{ $product['id'] }}][stock_qty]" value="{{ $product['stock_qty'] }}">
                                                    <td>{{ $product['name'] }}</td>
                                                    <td>{{ $product['code'] ?? trans('app.none') }}</td>
                                                    <td>{{ $product['stock_qty'] }}</td>
                                                    <td width="25%">
                                                        <input type="text" name="products[{{ $product['id'] }}][quantity]" min="1" max="10000"
                                                               class="form-control integer-input" value="{{ $product['quantity'] }}" required>
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-danger btn-sm" onclick="removeProduct(this)">
                                                            <i class="fa fa-trash-o"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    {{-- Transfer date --}}
                    <div class="col-lg-4 form-group">
                        <label for="transfer_date" class="control-label">
                            {{ trans('app.transfer_date') }} <span class="required">*</span>
                        </label>
                        <input type="text" name="transfer_date" id="transfer_date" class="form-control date-picker" required
                               placeholder="{{ trans('app.date_placeholder') }}" value="{{ old('transfer_date') ?? date('d-m-Y') }}">
                    </div>

                    {{-- Invoice number --}}
                    <div class="col-lg-4 form-group">
                        <label for="invoice_id" class="control-label">
                            {{ trans('app.invoice_id') }}
                        </label>
                        <input type="text" name="invoice_id" id="invoice_id" class="form-control" value="{{ old('invoice_id') }}">
                    </div>

                    {{-- Shipping cost --}}
                    <div class="col-lg-4 form-group">
                        <label for="shipping_cost" class="control-label">
                            {{ trans('app.shipping_cost') }} ($)
                        </label>
                        <input type="text" name="shipping_cost" id="shipping_cost" class="form-control decimal-input"
                               min="0" value="{{ old('shipping_cost') }}">
                    </div>
                </div>
                <div class="row">
                    {{-- Document --}}
                    <div class="col-lg-4 form-group">
                        <label for="document" class="control-label">
                            {{ trans('app.document') }}
                        </label>
                        <input type="file" name="document" id="document" class="form-control">
                    </div>

                    {{-- Note --}}
                    <div class="col-lg-4 form-group">
                        <label for="note" class="control-label">
                            {{ trans('app.note') }}
                        </label>
                        <input type="text" name="note" id="note" class="form-control" value="{{ old('note') }}">
                    </div>
                </div>

                {{-- Button save or edit --}}
                <div class="row">
                    <div class="col-lg-12 text-right">
                        @include('partial.button-save', ['onClick' => 'confirmFormSubmission($("#transfer-form"))'])
                    </div>
                </div>
            </form>
        </div>
    </main>
@endsection
@section('js')
    <script>
        var productRetrievalUrl = '{{ route('transfer.get_products', ':warehouseId') }}';
        var codeLabel = '{{ trans('app.code') }}';
        var noneLabel = '{{ trans('app.none') }}';
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
    <script src="{{ asset('js/transfer.js') }}"></script>
@endsection
