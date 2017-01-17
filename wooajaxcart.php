<?php
/*
  Plugin Name: AJAX Cart
  Plugin URI: https://github.com/yahongie2014
  Description: Change WooCommerce Cart page, making AJAX requests when quantity field changes
  Version: 1.1
  Author: Ahmed Saeed Ahmed (yahongie)
  Author URI: https://eg.linkedin.com/in/devahmedsaeed
 */

add_action('init', 'wac_init');
function wac_init() {
    // force to make is_cart() returns true, to make right calculations on class-wc-cart.php (WC_Cart::calculate_totals())
    // this define fix a bug that not show Shipping calculator when is WAC ajax request
    if ( !empty($_POST['is_wac_ajax']) && !defined( 'WOOCOMMERCE_CART' ) ) {
        define( 'WOOCOMMERCE_CART', true );
    }
}

add_action('woocommerce_before_cart_contents', 'wac_cart_demo');
function wac_cart_demo() {
    if ( defined('IS_DEMO')) {
        wac_demo_msg('Change the product quantity to see the AJAX update made by WooAjaxCart plugin');
    }
}

add_action('woocommerce_archive_description', 'wac_shop_demo');
function wac_shop_demo() {
    if ( defined('IS_DEMO')) {
        wac_demo_msg('Add some items to cart and go to the "Cart" page to see this plugin in action');
    }
}

// on submit AJAX form of the cart
// after calculate cart form items
add_action('woocommerce_cart_updated', 'wac_update');
function wac_update() {
    // is_wac_ajax: flag defined on wooajaxcart.js
    
    if ( !empty($_POST['is_wac_ajax'])) {
        $resp = array();
        $resp['update_label'] = __( 'Update Cart', 'woocommerce' );
        $resp['checkout_label'] = __( 'Proceed to Checkout', 'woocommerce' );
        $resp['price'] = 0;
        
        // render the cart totals (cart-totals.php)
        ob_start();
        do_action( 'woocommerce_after_cart_table' );
        do_action( 'woocommerce_cart_collaterals' );
        do_action( 'woocommerce_after_cart' );
        $resp['html'] = ob_get_clean();
        $resp['price'] = 0;
        
        // calculate the item price
        if ( !empty($_POST['cart_item_key']) ) {
            $items = WC()->cart->get_cart();
            $cart_item_key = $_POST['cart_item_key'];
            
            if ( array_key_exists($cart_item_key, $items)) {
                $cart_item = $items[$cart_item_key];
                $_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
                $price = apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key );
                $resp['price'] = $price;
            }
        }

        echo json_encode($resp);
        exit;
    }
}

// on render cart table page
add_action('woocommerce_before_cart_table', 'wac_cart_table');
function wac_cart_table() {
    // add js hacks script
    wp_register_script('wooajaxcart', plugins_url('wooajaxcart.js', __FILE__));
    wp_enqueue_script('wooajaxcart');
}

function wac_demo_msg($text) {
    echo sprintf('<div style="background-color: lightgreen; padding: 5px; margin: 5px; border-radius: 3px">* %s</div>', $text);
}
