$(function () {
    formatNumericFields();
});

function formatNumericFields() {
    $('.integer-input').number(true, 0, '.', '');
    $('.decimal-input').number(true, 2, '.', '');
    $('.decimal-display').number(true, 2);
}
