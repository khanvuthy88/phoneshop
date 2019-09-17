@extends('layouts/backend')
@section('title', trans('app.purchase'))
@section('content')
    <main class="app-content">
        <div class="tile">
            <h3 class="page-heading">{{ trans('app.purchase') }}</h3>
            @include('partial/flash-message')

            <div class="card">
                <div class="card-header">
                    <form method="get" action="">
                        <div class="row">
                            <div class="col-lg-6">
                                @include('partial.anchor-create', [
                                    'href' => route('purchase.create')
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
                            <td>@sortablelink('purchase_date', trans('app.purchase_date'))</td>
                            <td>@sortablelink('reference_no', trans('app.invoice_id'))</td>
                            <td>{{ trans('app.location') }}</td>
                            <td>{{ trans('app.document') }}</td>
                            <td>@sortablelink('note', trans('app.note'))</td>
                            <td>@sortablelink('purchase_status', trans('app.status'))</td>
                            <td>{{ trans('app.creator') }}</td>
                            <th>{{ trans('app.action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($purchases as $purchase)
                            <tr>
                                <td>{{ $offset++ }}</td>
                                <td>{{ displayDate($purchase->purchase_date) }}</td>
                                <td>{{ $purchase->reference_no }}</td>
                                <td>@include('partial.branch-detail-link', ['branch' => $purchase->warehouse])</td>
                                <td>@include('partial.purchase-doc-view')</td>
                                <td>{{ $purchase->note }}</td>
                                <td>@include('partial.purchase-status-label')</td>
                                <td>{{ $purchase->creator->name ?? trans('app.n/a') }}</td>
                                <td class="text-center">
                                    @include('partial.anchor-show', [
                                        'href' => route('purchase.show', $purchase->id)
                                    ])
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                {!! $purchases->appends(Request::except('page'))->render() !!}
            </div>
        </div>
    </main>
@endsection

@section('js')
    <script src="{{ asset('js/select2.min.js') }}"></script>
    <script src="{{ asset('js/select-box.js') }}"></script>
@endsection
