$(function () {
    // When add product to purchase list
    $('#add-product').click(function () {
        if ($('#product').attr('required', true).valid()) {
            let productId = $('#product').val();
            let isProductAdded = ($('#product-table tbody').find('tr[data-id="' + productId + '"]').length > 0);

            if (!isProductAdded) {
                let productElm = $('#product').find(':selected');
                let productRow =
                    '<tr data-id="' + productId + '">' +
                        '<input type="hidden" name="products[' + productId + '][id]" value="' + productId + '">' +
                        '<input type="hidden" name="products[' + productId + '][name]" value="' + productElm.data('name') + '">' +
                        '<input type="hidden" name="products[' + productId + '][code]" value="' + productElm.data('code') + '">' +
                        '<td>' + productElm.data('name') + '</td>' +
                        '<td>' + (productElm.data('code') || noneLabel) + '</td>' +
                        '<td width="25%">' +
                            '<input type="text" name="products[' + productId + '][quantity]" class="form-control integer-input" min="1" max="10000" required>' +
                        '</td>' +
                        '<td><button type="button" class="btn btn-danger btn-sm" onclick="removeProduct(this)"><i class="fa fa-trash-o"></i></button></td>' +
                    '</tr>';

                $('#product-table tbody').append(productRow);
                formatNumericFields();
            }

            $('#product').attr('required', false).val('').trigger('change');
        }
    });

});

/**
 * Remove product from purchase list.
 *
 * @param buttonElm Button element that has been clicked
 */
function removeProduct(buttonElm) {
    $(buttonElm).parents('#product-table tbody tr').remove();
}
