$(function () {
	//add product into cart
	$('#add-product').on('click', function(){
		_sale_frm = $('#sale-form');
		if($('#product').valid()){
			//$(this).attr("disabled", true);

			//get product row
        	sale_product_row();

        	//Clear product selection and disable button Add
			$('#product').val(null);
		}
	});
    //show inline client field into form
    $('#add-client').on('click', function(){
        $('.client-addition').toggleClass('hide');
    });

	//Update line total and check for quantity not greater than max quantity
    $('table#sale-product-table tbody').on('change', 'input.quantity', function() {
        // var max_qty = parseFloat($(this).data('rule-max'));
        var entered_qty = $(this).val();

        var tr = $(this).parents('tr');

        var unit_price_inc_tax = tr.find('input.unit_price').val();
        var line_total = entered_qty * unit_price_inc_tax;

        tr.find('input.sub_total').val(line_total);

        calculateTotal();
    });

    //Update balance
    $('table#sale-product-table tfoot').on('change', 'input.paid_amount', function() {
        calculate_balance_due();
    });

    calculateTotal();
});
//Add product row into product cart
//---------------------
function sale_product_row() {
	if ($('#product').attr('required', true).valid()) {
		let productElm = $('#product').find(':selected');
		var pro_ctn = $('#sale-product-table tbody');
		//check product stock qty
		if(!productElm.data('stock-qty')){
			alert('This product is out of stock.');
			return;
		}
        let productId = $('#product').val();
        let isProductAdded = (pro_ctn.find('tr[data-id="' + productId + '"]').length > 0);

        if (!isProductAdded) {
            
            let productRow =
                '<tr data-id="' + productId + '">' +
                    '<input type="hidden" name="products[' + productId + '][id]" value="' + productId + '">' +
                    '<input type="hidden" name="products[' + productId + '][name]" value="' + productElm.data('name') + '">' +
                    '<input type="hidden" name="products[' + productId + '][code]" value="' + productElm.data('code') + '">' +
                    '<td>' + productElm.data('name') + '</td>' +
                    '<td>' + (productElm.data('code') || noneLabel) + '</td>' +
                    '<td>' + (productElm.data('stock-qty') || noneLabel) + '</td>' +
                    '<td width="25%">' +
                        '<input type="text" name="products[' + productId + '][quantity]" class="form-control integer-input quantity" min="1" max="10000" required value="1">' +
                    '</td>' +
                    '<td width="25%">' +
                        '<input type="text" name="products[' + productId + '][price]" class="form-control integer-input unit_price" min="1" max="10000" required value="'+productElm.data('price')+'">' +
                    '</td>' +
                    '<td width="25%">' +
                        '<input type="text" name="products[' + productId + '][sub_total]" class="form-control integer-input sub_total" min="1" max="10000" required value="'+productElm.data('price')+'" readonly>' +
                    '</td>' +
                    '<td><button type="button" class="btn btn-danger btn-sm" onclick="rmProduct(this)"><i class="fa fa-trash-o"></i></button></td>' +
                '</tr>';

            pro_ctn.append(productRow);
            formatNumericFields();
            calculateTotal();
        }

        $('#product').attr('required', false).val('').trigger('change');
    }
}

function rmProduct(buttonElm) {
    $(buttonElm).parents('#sale-product-table tbody tr').remove();
}

function calculateTotal(){
	var total_quantity = 0;
    var price_total = 0;

    $('table#sale-product-table tbody tr').each(function() {
        total_quantity += parseInt($(this).find('input.quantity').val());
        price_total += parseInt($(this).find('input.sub_total').val());
    });

    //updating shipping charges
    // $('span#shipping_charges_amount').text(
    //     __currency_trans_from_en(__read_number($('input#shipping_charges_modal')), false)
    // );

    // $('span.total_quantity').each(function() {
    //     $(this).html(__number_f(total_quantity));
    // });

    //$('span.unit_price_total').html(unit_price_total);
    $('span.shown_total_price').html(price_total);
    $('input.total_price').val(price_total);

    calculate_billing_details(price_total);
}

function calculate_billing_details(price_total) {
    // var discount = pos_discount(price_total);
    // var order_tax = pos_order_tax(price_total, discount);

    //Add shipping charges.
    // var shipping_charges = __read_number($('input#shipping_charges'));

    // var total_payable = price_total + order_tax - discount + shipping_charges;

    // __write_number($('input#final_total_input'), total_payable);
    // var curr_exchange_rate = 1;
    // if ($('#exchange_rate').length > 0 && $('#exchange_rate').val()) {
    //     curr_exchange_rate = __read_number($('#exchange_rate'));
    // }
    // var shown_total = total_payable * curr_exchange_rate;
    // $('span#total_payable').text(__currency_trans_from_en(shown_total, false));

    // $('span.total_payable_span').text(__currency_trans_from_en(total_payable, true));

    //Check if edit form then don't update price.
    // if ($('form#edit_pos_sell_form').length == 0) {
    //     __write_number($('.payment-amount').first(), total_payable);
    // }

    calculate_balance_due();
}

function calculate_balance_due() {
	var total_amount = $('input.total_price').val();
	var paid_amount = $('input.paid_amount').val();
	var balance_amount = total_amount - paid_amount;

	$('span.shown_balance_amount').html(balance_amount);
    $('input.balance_amount').val(balance_amount);

    // var total_payable = __read_number($('#final_total_input'));
    // var total_paying = 0;
    // $('#payment_rows_div')
    //     .find('.payment-amount')
    //     .each(function() {
    //         if (parseFloat($(this).val())) {
    //             total_paying += __read_number($(this));
    //         }
    //     });
    // var bal_due = total_payable - total_paying;
    // var change_return = 0;

    // //change_return
    // if (bal_due < 0 || Math.abs(bal_due) < 0.05) {
    //     __write_number($('input#change_return'), bal_due * -1);
    //     $('span.change_return_span').text(__currency_trans_from_en(bal_due * -1, true));
    //     change_return = bal_due * -1;
    //     bal_due = 0;
    // } else {
    //     __write_number($('input#change_return'), 0);
    //     $('span.change_return_span').text(__currency_trans_from_en(0, true));
    //     change_return = 0;
    // }

    // __write_number($('input#total_paying_input'), total_paying);
    // $('span.total_paying').text(__currency_trans_from_en(total_paying, true));

    // __write_number($('input#in_balance_due'), bal_due);
    // $('span.balance_due').text(__currency_trans_from_en(bal_due, true));

    // __highlight(bal_due * -1, $('span.balance_due'));
    // __highlight(change_eturn * -1, $('span.change_return_span'));
}

//Get product row from server by Ajax
//---------------------
// function sale_product_row(prod_id, customer_id) {
//     //Get item addition method
//     var item_addtn_method = 0;
//     var add_via_ajax = true;

//     // if ($('#item_addition_method').length) {
//     //     item_addtn_method = $('#item_addition_method').val();
//     // }

//     if (item_addtn_method == 0) {
//         add_via_ajax = true;
//     } else {
//         var is_added = false;

//         //Search for variation id in each row of pos table
//         $('#sale-product-table tbody')
//             .find('tr')
//             .each(function() {
//                 var row_v_id = $(this)
//                     .find('.row_variation_id')
//                     .val();
//                 var enable_sr_no = $(this)
//                     .find('.enable_sr_no')
//                     .val();
//                 var modifiers_exist = false;
//                 if ($(this).find('input.modifiers_exist').length > 0) {
//                     modifiers_exist = true;
//                 }

//                 if (
//                     row_v_id == variation_id &&
//                     enable_sr_no !== '1' &&
//                     !modifiers_exist &&
//                     !is_added
//                 ) {
//                     add_via_ajax = false;
//                     is_added = true;

//                     //Increment product quantity
//                     qty_element = $(this).find('.pos_quantity');
//                     var qty = __read_number(qty_element);
//                     __write_number(qty_element, qty + 1);
//                     qty_element.change();

//                     round_row_to_iraqi_dinnar($(this));

//                     $('input#search_product')
//                         .focus()
//                         .select();
//                 }
//             });
//     }

//     if (add_via_ajax) {
//         var product_row = $('input#product_row_count').val();
//         var location_id = $('input#location_id').val();
//         var customer_id = $('select#customer_id').val();
//         var is_direct_sell = false;
//         if (
//             $('input[name="is_direct_sale"]').length > 0 &&
//             $('input[name="is_direct_sale"]').val() == 1
//         ) {
//             is_direct_sell = true;
//         }

//         var price_group = '';
//         if ($('#price_group').length > 0) {
//             price_group = $('#price_group').val();
//         }

//         $.ajax({
//             method: 'GET',
//             url: '/sells/pos/get_product_row/' + variation_id + '/' + location_id,
//             async: false,
//             data: {
//                 product_row: product_row,
//                 customer_id: customer_id,
//                 is_direct_sell: is_direct_sell,
//                 price_group: price_group,
//             },
//             dataType: 'json',
//             success: function(result) {
//                 if (result.success) {
//                     $('table#pos_table tbody')
//                         .append(result.html_content)
//                         .find('input.pos_quantity');
//                     //increment row count
//                     $('input#product_row_count').val(parseInt(product_row) + 1);
//                     var this_row = $('table#pos_table tbody')
//                         .find('tr')
//                         .last();
//                     pos_each_row(this_row);

//                     //For initial discount if present
//                     var line_total = __read_number(this_row.find('input.pos_line_total'));
//                     this_row.find('span.pos_line_total_text').text(line_total);

//                     pos_total_row();
//                     if (result.enable_sr_no == '1') {
//                         var new_row = $('table#pos_table tbody')
//                             .find('tr')
//                             .last();
//                         new_row.find('.add-pos-row-description').trigger('click');
//                     }

//                     round_row_to_iraqi_dinnar(this_row);
//                     __currency_convert_recursively(this_row);

//                     $('input#search_product')
//                         .focus()
//                         .select();

//                     //Used in restaurant module
//                     if (result.html_modifier) {
//                         $('table#pos_table tbody')
//                             .find('tr')
//                             .last()
//                             .find('td:first')
//                             .append(result.html_modifier);
//                     }
//                 } else {
//                     toastr.error(result.msg);
//                     $('input#search_product')
//                         .focus()
//                         .select();
//                 }
//             },
//         });
//     }
// }