
$(function () {
    datePicker();
});

function datePicker() {
    /*$('.date-picker').datetimepicker({
        format: 'DD-MM-YYYY',
    });*/
    $('.date-picker').mask('00-00-0000');
}
