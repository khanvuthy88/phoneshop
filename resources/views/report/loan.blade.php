@extends('layouts/backend')
@section('title', trans('app.loan'))
@section('css')
    <link rel="stylesheet" href="{{ asset('css/sweetalert.css') }}">
@endsection
@section('content')
    <main class="app-content">
        <div class="tile">
            <h3 class="page-heading">{{ trans('app.loan_report') . ' - ' . reportLoanStatuses($status) }}</h3>
            <div class="row">
                <div class="col-md-12">
                    {{-- Pending loans --}}
                    <a href="{{ route('report.loan', ReportLoanStatus::PENDING) }}" class="btn btn-warning font-size-16 mb-1 mr-1">
                        {{ trans('app.pending') }} : <span class="text-white font-bold">{{ $pendingLoanCount }}</span>
                    </a>

                    {{-- Active loans --}}
                    <a href="{{ route('report.loan', ReportLoanStatus::ACTIVE) }}" class="btn btn-info font-size-16 mb-1 mr-1">
                        {{ trans('app.progressing') }} : <span class="text-white font-bold">{{ $activeLoanCount }}</span>
                    </a>

                    {{-- Paid loans --}}
                    <a href="{{ route('report.loan', ReportLoanStatus::PAID) }}" class="btn btn-success font-size-16 mb-1 mr-1">
                        {{ trans('app.paid') }} : <span class="text-white font-bold">{{ $paidLoanCount }}</span>
                    </a>

                    {{-- Rejected loans --}}
                    <a href="{{ route('report.loan', ReportLoanStatus::REJECTED) }}" class="btn btn-danger font-size-16 mb-1 mr-1">
                        {{ trans('app.rejected') }} : <span class="text-white font-bold">{{ $rejectedLoanCount }}</span>
                    </a>
                </div>
            </div>
            <hr>

            @include('partial.flash-message')
            @include('partial.item-count-label')
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    @php
                        $isRejectedLoan = ($status == ReportLoanStatus::REJECTED);
                        $statusTitle = reportLoanStatuses($status);

                        switch ($status) {
                            case ReportLoanStatus::PENDING:
                                $labelClass = 'badge badge-warning';
                                break;
                            case ReportLoanStatus::ACTIVE:
                                $labelClass = 'badge badge-info';
                                break;
                            case ReportLoanStatus::PAID:
                                $labelClass = 'badge badge-success';
                                break;
                            case ReportLoanStatus::REJECTED:
                                $labelClass = 'badge badge-danger';
                                break;
                        }
                    @endphp

                    <thead>
                        <tr>
                            <th>{{ trans('app.no_sign') }}</th>
                            <th>{{ trans('app.client') }}</th>
                            <th>{{ trans('app.profile_photo') }}</th>
                            <th>@sortablelink('client_code', trans('app.client_code'))</th>
                            <th>{{ trans('app.first_phone') }}</th>
                            <th>{{ trans('app.branch') }}</th>

                            @if (isAdmin())
                                <th>{{ trans('app.agent') }}</th>
                            @endif

                            <th>{{ trans('app.product') }}</th>
                            <th>{{ trans('app.status') }}</th>

                            @if (isAdmin() && $isRejectedLoan)
                                <th>{{ trans('app.action') }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($filteredLoans as $loan)
                            <tr>
                                <td>{{ $offset++ }}</td>
                                <td>@include('partial.client-detail-link', ['client' => $loan->client])</td>
                                <td>@include('partial.client-profile-photo', ['client' => $loan->client])</td>

                                <td>
                                    @if ($isRejectedLoan)
                                        {{ $loan->client_code }}
                                    @else
                                        @include('partial.loan-detail-link')
                                    @endif
                                </td>

                                <td>{{ $loan->client->first_phone }}</td>
                                <td>{{ $loan->branch->location ?? trans('app.n/a') }}</td>

                                @if (isAdmin())
                                    <td>@include('partial.staff-detail-link', ['staff' => $loan->staff])</td>
                                @endif

                                <td>@include('partial.product-detail-link', ['product' => $loan->product])</td>
                                <td><label class="{{ $labelClass }}">{{ $statusTitle }}</label></td>

                                @if (isAdmin() && $isRejectedLoan)
                                    <td class="text-center">
                                        {{-- Revert rejected loan to pending --}}
                                        <button type="button" class="btn btn-primary btn-sm btn-revert mb-1"
                                            data-redirect-url="{{ route('loan.show', $loan->id) }}"
                                            data-revert-url="{{ route('loan.change_status', [$loan->id, LoanStatus::PENDING]) }}">
                                            {{ trans('app.revert') }}
                                        </button>

                                        {{-- Delete rejected loan --}}
                                        <button type="button" class="btn btn-danger btn-sm btn-delete mb-1"
                                            data-url="{{ route('loan.destroy', $loan->id) }}">
                                            {{ trans('app.delete') }}
                                        </button>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                {!! $filteredLoans->appends(Request::except('page'))->render() !!}
            </div>
        </div>
    </main>
@endsection
@section('js')
    <script src="{{ asset('js/sweetalert.min.js') }}"></script>
    <script src="{{ asset('js/rejected-loan.js') }}"></script>
    <script src="{{ asset('js/delete-item.js') }}"></script>
@endsection
