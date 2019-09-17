@extends('layouts/backend')
@section('title', trans('app.stock_transfer'))
@section('content')
    <main class="app-content">
        <div class="tile">
            <h3 class="page-heading">{{ trans('app.stock_transfer') }}</h3>
            @include('partial/flash-message')

            <div class="card">
                <div class="card-header">
                    <form method="get" action="">
                        <div class="row">
                            <div class="col-lg-6">
                                @include('partial.anchor-create', [
                                    'href' => route('transfer.create')
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
                            <td>@sortablelink('transfer_date', trans('app.transfer_date'))</td>
                            <td>@sortablelink('reference_no', trans('app.invoice_id'))</td>
                            <th>{{ trans('app.original_location') }}</th>
                            <th>{{ trans('app.target_location') }}</th>
                            <th>{{ trans('app.document') }}</th>
                            <td>@sortablelink('note', trans('app.note'))</td>
                            <th>{{ trans('app.creator') }}</th>
                            <th>{{ trans('app.action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($transfers as $transfer)
                            <tr>
                                <td>{{ $offset++ }}</td>
                                <td>{{ displayDate($transfer->transfer_date) }}</td>
                                <td>{{ $transfer->reference_no }}</td>
                                <td>@include('partial.branch-detail-link', ['branch' => $transfer->fromWarehouse])</td>
                                <td>@include('partial.branch-detail-link', ['branch' => $transfer->toWarehouse])</td>
                                <td>@include('partial.transfer-doc-view')</td>
                                <td>{{ $transfer->note }}</td>
                                <td>{{ $transfer->creator->name ?? trans('app.n/a') }}</td>
                                <td class="text-center">
                                    @include('partial.anchor-show', [
                                        'href' => route('transfer.show', $transfer->id)
                                    ])
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                {!! $transfers->appends(Request::except('page'))->render() !!}
            </div>
        </div>
    </main>
@endsection

@section('js')
    <script src="{{ asset('js/select2.min.js') }}"></script>
    <script src="{{ asset('js/select-box.js') }}"></script>
@endsection
