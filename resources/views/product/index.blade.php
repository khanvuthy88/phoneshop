@extends('layouts/backend')
@section('title', trans('app.product'))
@section('content')
    <main class="app-content">
        <div class="tile">
            <h3 class="page-heading">{{ trans('app.product') }}</h3>
            @include('partial/flash-message')
            <div class="card">
                <div class="card-header">
                    <form method="get" action="">
                        <div class="row">
                            <div class="col-lg-2">
                                @include('partial/anchor-create', [
                                    'href' => route('product.create')
                                ])
                            </div>
                            <div class="col-lg-10 pl-1 pr-0">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label for="location">Warehouse</label>
                                        <select name="location" id="location" class="form-control select2">
                                            <option value="">{{ trans('app.all') }}</option>
                                            @foreach($locations as $location)
                                                <option value="{{ $location->id }}" {{ request('location') == $location->id ? 'selected' : '' }}>
                                                    {{ $location->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="prod_type">{{ trans('app.product_category') }}</label>
                                        <select name="prod_type" id="prod_type" class="form-control select2">
                                            <option value="">{{ trans('app.all') }}</option>
                                            @foreach ($productCategories as $t)
                                                <option value="{{ $t->id }}" {{ request('prod_type') == $t->id ? 'selected' : '' }}>
                                                    {{ $t->value }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="brand">{{ trans('app.brand') }}</label>
                                        <select name="brand" id="brand" class="form-control select2">
                                            <option value="">{{ trans('app.all') }}</option>
                                            @foreach ($brands as $brand)
                                                <option value="{{ $brand->id }}" {{ request('brand') == $brand->id ? 'selected' : '' }}>
                                                    {{ $brand->value }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="brand">{{ trans('app.name') }}/{{ trans('app.product_code/sku') }}/{{ trans('app.selling_price') }}</label>
                                        @include('partial.search-input-group')
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <br>
            @include('partial.item-count-label')
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead>
                        <tr>
                            <th>{{ trans('app.no_sign') }}</th>
                            <td>@sortablelink('name', trans('app.name'))</td>
                            <td>QTY</td>
                            <th>{{ trans('app.photo') }}</th>
                            <td>@sortablelink('code', trans('app.product_code'))</td>
                            <td>@sortablelink('sku', trans('app.sku'))</td>
                            <th>{{ trans('app.category') }}</th>
                            <th>{{ trans('app.brand') }}</th>
                            <th>@sortablelink('price', trans('app.price'))</th>
                            <th>{{ trans('app.action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $product)
                            <tr>
                                <td>{{ $offset++ }}</td>
                                <td>{{ $product->name }}[{{ $product->id }}]</td>
                                <td>
                                    <?php 
                                        $location= request('location') ? request('location') : 0;
                                    ?>                                  
                                    {{ $product->getQty($product->id, $location) }}                                   
                                </td>
                                <td>@include('partial.product-photo')</td>
                                <td>{{ $product->code }}</td>
                                <td>{{ $product->sku }}</td>
                                <td>{{ $product->category->value ?? trans('app.n/a') }}</td>
                                <td>{{ brands($product->brand) }}</td>
                                <td>$ {{ decimalNumber($product->price, true) }}</td>
                                <td>
                                    <a href="{{ route('product.list_warehouse', $product->id) }}" class="btn btn-info btn-sm mb-1">
                                        {{ trans('app.stock') }}
                                    </a>
                                    @include('partial.anchor-show', [
                                        'href' => route('product.show', $product->id),
                                    ])
                                    @include('partial.anchor-edit', [
                                        'href' => route('product.edit', $product->id),
                                    ])
                                    @include('partial.button-delete', [
                                        'url' => route('product.destroy', $product->id),
                                        'disabled' => 'disabled',
                                    ])
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                {!! $products->appends(Request::except('page'))->render() !!}
            </div>
        </div>
    </main>
@endsection

@section('js')
    <script src="{{ asset('js/select2.min.js') }}"></script>
    <script src="{{ asset('js/select-box.js') }}"></script>
    <script type="text/javascript">
        $(function () {
            $('#location, #prod_type, #brand').change(function () {
                $(this).parents('form').submit();
            });
        });

    </script>
@endsection
