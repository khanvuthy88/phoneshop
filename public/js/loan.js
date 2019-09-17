$(function () {
    $('#wing_code').mask('0000 0000');

    // Calculate payment schedule automatically when show loan detail
    if (formType == formShowType) {
        calcPaymentSchedules();
    }

    // Validate form fields
    $('#loan-form').validate({
        'branch': { required: true },
        'agent': { required: true },
        'client': { required: true },
        'wing_code': { required: true },
        'client_code': { required: true },
        'product': { required: true },
        'schedule_type': { required: true },
        'loan_amount': { required: true, min: 1 },
        'depreciation_amount': { required: true, min: 0 },
        'interest_rate': { required: true, min: 0 },
        'installment': { required: true, min: 1 },
        'loan_start_date': { required: true },
    });

    // Update loan amount and down payment when change product, product price, or depreciation amount
    $('#product, #product_price, #depreciation_amount').on('change paste keyup', function () {
        var productPrice = $('#product_price').val();
        productPrice = (productPrice > 0 ? productPrice : $('#product').find(':selected').data('product-price'));
        var loanAmount = downPaymentAmount = productPrice;
        var depreciationAmount = $('#depreciation_amount').val();

        if (productPrice != '') {
            downPaymentAmount = decimalNumber(downPaymentAmount - depreciationAmount);
        }

        $('#loan_amount').val(loanAmount);
        $('#down_payment_amount').val(downPaymentAmount);
    });

    // When change payment schedule type
    $('#schedule_type').change(function () {
        var scheduleType = $(this).val();
        $(this).removeClass('text-danger');

        switch (scheduleType) {
            case '':
            case flatInterestSchedule:
                $('#interest_rate').attr('disabled', true);
                $('#rate_sign').text('');
                break;
            case equalPaymentSchedule:
            case declineInterestSchedule:
                $('#interest_rate').attr('disabled', false);
                $('#rate_sign').text('*');
                $('#rate_text').text(scheduleType == equalPaymentSchedule ? loanRateLabel : interestRateLabel);
                break;
            default:
                $(this).addClass('text-danger').focus();
                return false;
        }
    });

    // When click button to calculate payment schedule
    $('#calculate-payment').click(function () {
        $('#error-msg').text('');

        // If fields are invalid
        if (!($('#product, #schedule_type, #loan_amount, #depreciation_amount, #down_payment_amount, #installment, #loan_start_date').attr('required', true).valid())
            || ([equalPaymentSchedule, declineInterestSchedule].includes($('#schedule_type').val()) && !($('#interest_rate').attr({'required': true, 'min': 0}).valid()))
        ) {
            $('#schedule-table').html('');
            return false;
        }

        calcPaymentSchedules();
    });

    // Reject loan
    $('#reject_loan').click(function () {
        confirmPopup($(this).data('url'), 'warning');
    });

    // Approve loan
    $('#approve_loan').click(function () {
        confirmPopup($(this).data('url'), 'success');
    });
});

/**
 * Calculate and display table of payment schedule
 */
function calcPaymentSchedules() {
    var scheduleType = $('#schedule_type').val();

    $.ajax({
        url: scheduleRetrievalUrl,
        data: {
            // Payment schedule data
            schedule_type: scheduleType,
            down_payment_amount: $('#down_payment_amount').val(),
            interest_rate: $('#interest_rate').val(),
            installment: $('#installment').val(),
            payment_per_month: $('#payment_per_month').val(),
            loan_start_date: $('#loan_start_date').val(),
            first_payment_date: $('#first_payment_date').val(),
        },
        success: function (data) {
            var isFlatInterestSchedule = (scheduleType == flatInterestSchedule);
            var grandTotalAmount = totalInterest = 0;
            var scheduleData = '<thead><tr><th>' + noLabel + '</th><th>' + paymentDateLabel + '</th>';

            if (isFlatInterestSchedule) {
                scheduleData += '<th>' + paymentAmountLabel + '</th>';
            } else {
                scheduleData +=
                    '<th>' + totalLabel + '</th>' +
                    '<th>' + principalLabel + '</th>' +
                    '<th>' + interestLabel + '</th>';
            }
            scheduleData += '<th>' + outstandingLabel + '</th></tr></thead><tbody>';

            $.each(data, function (key, value) {
                grandTotalAmount += decimalNumber(value.total);
                totalInterest += decimalNumber(value.interest);
                scheduleData += '<tr><td>' + ++key + '</td><td>' + value.payment_date + '</td>';

                if (isFlatInterestSchedule) {
                    scheduleData += '<td>$ ' + value.principal + '</td>';
                } else {
                    scheduleData +=
                        '<td>$ ' + value.total + '</td>' +
                        '<td>$ ' + value.principal + '</td>' +
                        '<td>$ ' + value.interest + '</td>';
                }

                scheduleData += '<td>$ ' + value.outstanding + '</td></tr>';
            });

            if (!isFlatInterestSchedule) {
                scheduleData += '<tr><td></td> <td></td>' +
                    '<td><b>$ ' + $.number(grandTotalAmount) + '</b></td>' +
                    '<td></td>' +
                    '<td><b>$ ' + $.number(totalInterest) + '</b></td>' +
                    '<td></td></tr>';
            }

            scheduleData += '</tbody>';
            $('#schedule-table').html(scheduleData).show();
        },
        error: function (xhr, status, error) {
            $('#error-msg').text(xhr.responseJSON.message);
            $('#schedule-table').html('').hide();
        }
    });
}
