@extends('layouts/backend')
@section('title', trans('app.payment'))
@section('content')
    <main class="app-content">
        <div class="tile">
            <h3 class="page-heading">{{ trans('app.payment') }}</h3>
            @include('partial/flash-message')
            <div class="card">
                <div class="card-header">
                    <form method="get" action="{{ route('repayment.index') }}">
                        <div class="row">
                            <div class="col-lg-4"></div>
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
                            <th>{{ trans('app.first_phone') }}</th>
                            <th>{{ trans('app.second_phone') }}</th>
                            <th>{{ trans('app.branch') }}</th>

                            @if (isAdmin())
                                <th>{{ trans('app.agent') }}</th>
                            @endif

                            <th>{{ trans('app.next_payment_date') }}</th>
                            <th>{{ trans('app.payment_amount') }}</th>

                            @if (isAdmin())
                                <th>{{ trans('app.action') }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($loans as $loan)
                            @php $amountToPay = $loan->schedules[0]->total - $loan->schedules[0]->paid_total @endphp
                            <tr>
                                <td>{{ $offset++ }}</td>
                                <td>@include('partial.client-detail-link', ['client' => $loan->client])</td>
                                <td>@include('partial.client-profile-photo', ['client' => $loan->client])</td>
                                <td>@include('partial.loan-detail-link')</td>
                                <td>{{ $loan->client->first_phone }}</td>
                                <td>{{ $loan->client->second_phone ?? trans('app.none') }}</td>
                                <td>{{ $loan->branch->location ?? trans('app.n/a') }}</td>

                                @if (isAdmin())
                                    <td>@include('partial.staff-detail-link', ['staff' => $loan->staff])</td>
                                @endif

                                <td>{{ displayDate($loan->schedules[0]->payment_date) }}</td>
                                <td><b>$ {{ decimalNumber($amountToPay, true) }}</b></td>
                                <td class="text-center">
                                    @if (isAdmin())
                                        {{-- Simple repayment --}}
                                        <a href="{{ route('repayment.show', [$loan->id, RepayType::REPAY]) }}" class="btn btn-success btn-sm mb-1">
                                            {{ trans('app.repay') }}
                                        </a>

                                        {{-- Payoff --}}
                                        <a href="{{ route('repayment.show', [$loan->id, RepayType::PAYOFF]) }}" class="btn btn-success btn-sm mb-1">
                                            {{ trans('app.pay_off') }}
                                        </a>
                                    @endif

                                    {{-- Advance payment --}}
                                    {{--<a href="{{ route('repayment.show', [$loan->id, RepayType::ADVANCE_PAY]) }}" class="btn btn-success btn-sm mb-1">
                                        {{ trans('app.advance_pay') }}
                                    </a>--}}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                @if (count($loans) > 0)
                    {!! $loans->appends(Request::except('page'))->render() !!}
                @endif
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
