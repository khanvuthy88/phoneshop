$(function () {
    $('#original_warehouse').change(function () {
        $('#product').html(emptyOptionElm);
        $('#product-table tbody').html('');
        let originalWarehouseId = $(this).val();

        if (originalWarehouseId != '') {
            $.ajax({
                url: productRetrievalUrl.replace(':warehouseId', originalWarehouseId),
                success: function (data) {
                    let productData = emptyOptionElm;

                    $.each(data.products, function (key, product) {
                        productData += '<option value="' + product.id + '" data-name="' + product.name + '"' +
                                    ' data-code="' + product.code + '" data-stock-qty="' + product.stock_qty + '">' +
                                    product.name + ' (' + codeLabel + ' : ' + (product.code || noneLabel) + ')</option>';
                    });

                    $('#product').html(productData);
                }
            });
        }
    });

    // When add product to transfer list
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
                        '<input type="hidden" name="products[' + productId + '][stock_qty]" value="' + productElm.data('stock-qty') + '">' +
                        '<td>' + productElm.data('name') + '</td>' +
                        '<td>' + (productElm.data('code') || noneLabel) + '</td>' +
                        '<td>' + productElm.data('stock-qty') + '</td>' +
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
 * Remove product from transfer list.
 *
 * @param buttonElm Button element that has been clicked
 */
function removeProduct(buttonElm) {
    $(buttonElm).parents('#product-table tbody tr').remove();
}
