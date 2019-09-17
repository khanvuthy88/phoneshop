@if ($scheduleType == PaymentScheduleType::FLAT_INTEREST)
    <td>{{ ($currencySign ?? '') . number_format($schedule->principal) }}</td>
@else
    @php $decimalNumber = ($schedule->interest == 0 ? 2 : 0) @endphp
    <td><b>{{ ($currencySign ?? '') . number_format($schedule->total, $decimalNumber) }}</b></td>
    <td>{{ ($currencySign ?? '') . number_format($schedule->principal, $decimalNumber) }}</td>
    <td>{{ ($currencySign ?? '') . number_format($schedule->interest) }}</td>
@endif
