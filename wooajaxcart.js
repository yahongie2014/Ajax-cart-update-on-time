jQuery(document).ready(function($){
    $('.qty').on('change', function(){
        form = $(this).closest('form');

        // emulates button Update cart click
        $("<input type='hidden' name='update_cart' id='update_cart' value='1'>").appendTo(form);
        
        // plugin flag
        $("<input type='hidden' name='is_wac_ajax' id='is_wac_ajax' value='1'>").appendTo(form);

        el_qty = $(this);
        matches = $(this).attr('name').match(/cart\[(\w+)\]/);
        cart_item_key = matches[1];
        form.append( $("<input type='hidden' name='cart_item_key' id='cart_item_key'>").val(cart_item_key) );

        // get the form data before disable button...
        formData = form.serialize();
        
        $("input[name='update_cart']").val('Updating…').prop('disabled', true);

        $("a.checkout-button.wc-forward").addClass('disabled').html('Updating…');

        $.post( form.attr('action'), formData, function(resp) {
            // ajax response
            $('.cart-collaterals').html(resp.html);
            
            el_qty.closest('.cart_item').find('.product-subtotal').html(resp.price);
            
            $('#update_cart').remove();
            $('#is_wac_ajax').remove();
            $('#cart_item_key').remove();
            
            $("input[name='update_cart']").val(resp.update_label).prop('disabled', false);

            $("a.checkout-button.wc-forward").removeClass('disabled').html(resp.checkout_label);

            // when changes to 0, remove the product from cart
            if ( el_qty.val() == 0 ) {
                el_qty.closest('tr').remove();
            }
        },
        'json'
        );
    });
});
