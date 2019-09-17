$(function () {
    callFileInput('#photo', 1, 5120, ['jpg', 'jpeg', 'png']);
    $('#form-product').validate();

    // Generate product code
    $('#generate-code').click(function () {
        let code = Math.floor(Math.random() * 99999999);
        $('#product_code').val(code);
    });
});
