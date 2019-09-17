@extends('layouts/backend')
@section('title', trans('app.stock_adjustment'))
@section('content')
    <main class="app-content">
        <div class="tile">
            <h3 class="page-heading">{{ trans('app.stock_adjustment') }}</h3>
            @include('partial/flash-message')

            <div class="card">
                <div class="card-header">
                    <form method="get" action="">
                        <div class="row">
                            <div class="col-lg-6">
                                @include('partial.anchor-create', [
                                    'href' => route('adjustment.create')
                                ])
                            </div>
                            <div class="col-lg-6">
                                @include('partial.search-input-group')
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <br>

            @include('partial.item-count-label')
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th>{{ trans('app.no_sign') }}</th>
                            <td>@sortablelink('adjustment_date', trans('app.adjustment_date'))</td>
                            <th>{{ trans('app.location') }}</th>
                            <th>{{ trans('app.product') }}</th>
                            <td>@sortablelink('quantity', trans('app.quantity'))</td>
                            <td>@sortablelink('action', trans('app.adjustment_action'))</td>
                            <td>@sortablelink('reason', trans('app.reason'))</td>
                            <th>{{ trans('app.creator') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($adjustments as $adjustment)
                            <tr>
                                <td>{{ $offset++ }}</td>
                                <td>{{ displayDate($adjustment->adjustment_date) }}</td>
                                <td>@include('partial.branch-detail-link', ['branch' => $adjustment->warehouse])</td>
                                <td>@include('partial.product-detail-link', ['product' => $adjustment->product])</td>
                                <td>{{ $adjustment->quantity }}</td>
                                <td>{{ stockTypes($adjustment->action) }}</td>
                                <td>{{ $adjustment->reason }}</td>
                                <td>{{ $adjustment->creator->name ?? trans('app.n/a') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                {!! $adjustments->appends(Request::except('page'))->render() !!}
            </div>
        </div>
    </main>
@endsection

@section('js')
    <script src="{{ asset('js/select2.min.js') }}"></script>
    <script src="{{ asset('js/select-box.js') }}"></script>
@endsection
