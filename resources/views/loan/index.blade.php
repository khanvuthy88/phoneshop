@extends('layouts/backend')
@section('title', trans('app.loan'))
@section('content')
    <main class="app-content">
        <div class="tile">
            <h3 class="page-heading">{{ trans('app.loan') }}</h3>
            @include('partial/flash-message')
            <div class="card">
                <div class="card-header">
                    <form method="get" action="">
                        <div class="row">
                            <div class="col-lg-4">
                                @include('partial/anchor-create', [
                                    'href' => route('loan.create')
                                ])
                            </div>
                            @include('partial.loan-search-fields')
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
                            <th>{{ trans('app.client') }}</th>
                            <th>{{ trans('app.profile_photo') }}</th>
                            <th>@sortablelink('client_code', trans('app.client_code'))</th>
                            <th>{{ trans('app.branch') }}</th>

                            @if (isAdmin())
                                <th>{{ trans('app.agent') }}</th>
                            @endif

                            <th>{{ trans('app.product') }}</th>
                            <th>{{ trans('app.next_payment_date') }}</th>
                            <th>{{ trans('app.payment_amount') }}</th>
                            <th>@sortablelink('status', trans('app.status'))</th>
                            <th>{{ trans('app.action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($loans as $loan)
                            @php $dueSchedule = $loan->schedules()->first(); @endphp
                            <tr>
                                <td>{{ $offset++ }}</td>
                                <td>@include('partial.client-detail-link', ['client' => $loan->client])</td>
                                <td>@include('partial.client-profile-photo', ['client' => $loan->client])</td>
                                <td>{!! $loan->client_code !!}</td>
                                <td>{{ $loan->branch->location ?? trans('app.n/a') }}</td>

                                @if (isAdmin())
                                    <td>@include('partial.staff-detail-link', ['staff' => $loan->staff])</td>
                                @endif

                                <td>@include('partial.product-detail-link', ['product' => $loan->product])</td>
                                <td>{{ displayDate($loan->schedules[0]->payment_date ?? null) }}</td>
                                <td><b>$ {{ decimalNumber($dueSchedule['total']) }}</b></td>
                                <td class="text-center">@include('partial.loan-status-label')</td>
                                <td class="text-center">
                                    <a href="{{ route('loan.show', $loan->id) }}" class="btn btn-success btn-sm mb-1">
                                        {{ trans('app.detail') }}
                                    </a>
                                    @if (in_array($loan->status, [LoanStatus::ACTIVE, LoanStatus::PAID]))
                                        <a href="{{ route('loan.print_contract', $loan) }}" title="{{ trans('app.print_contract') }}"
                                           class="btn btn-success btn-sm mb-1" target="_blank">
                                            {{ trans('app.print') }}
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                {!! $loans->appends(Request::except('page'))->render() !!}
            </div>
        </div>
    </main>
@endsection

@section('js')
    <script>
        var agentSelectLabel = '<option value="">{{ trans('app.agent') }}';
        var agentRetrievalUrl = '{{ route('staff.get_agents', ':branchId') }}';
    </script>
    <script src="{{ asset('js/select2.min.js') }}"></script>
    <script src="{{ asset('js/select-box.js') }}"></script>
    <script src="{{ asset('js/agent-retrieval.js') }}"></script>
@endsection
