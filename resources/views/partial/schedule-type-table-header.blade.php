@if ($scheduleType == PaymentScheduleType::FLAT_INTEREST)
    <th>{{ trans('app.payment_amount') }}</th>
@else
    <th>{{ trans('app.total') }}</th>
    <th>{{ trans('app.principal') }}</th>
    <th>{{ trans('app.interest') }}</th>
@endif
