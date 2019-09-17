$(function () {
// Validate form elements when submit
    $('#payment-form').validate({
        payment_date: { required: true, dateISO: true },
        payment_amount: { required: true, min: 1},
        payment_method: { required: true },
    });

    // For advanced payment
    $('.schedule').on('change', function () {
        var paymentAmountElm = $('#payment_amount');
        var paymentAmount = parseFloat(paymentAmountElm.val());
        if (isNaN(paymentAmount)) {
            paymentAmount = 0;
        }

        var checkStatus = $(this).prop('checked');
        var principal = parseFloat($(this).data('principal'));
        var scheduleId = $(this).data('schedule-id');

        if (checkStatus) {
            paymentAmount += principal;
            $('#payment-form').append('<input type="hidden" name="selected_schedules[]" id="' + scheduleId + '" value="' + scheduleId + '">');
        } else {
            paymentAmount -= principal;
            $('#' + scheduleId).remove();
        }

        paymentAmountElm.val(paymentAmount);
    });
});
