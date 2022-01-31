<?php

//Theme assets
function add_theme_scripts()
{
    //header scripts
    wp_enqueue_style('fontAwesome', get_template_directory_uri() . '/css/all.min.css');
    wp_enqueue_style('style', get_template_directory_uri() . '/css/style.css');
    wp_enqueue_style('style2', get_template_directory_uri() . '/css/style2.css');
    wp_enqueue_style('style3', get_template_directory_uri() . '/css/style3.css');
    wp_enqueue_style('style4', get_template_directory_uri() . '/css/style4.css');
	wp_enqueue_style('style5', get_template_directory_uri() . '/css/style5.css');
    wp_enqueue_style('style6', get_template_directory_uri() . '/css/style6.css');
    
}
add_action('wp_enqueue_scripts', 'add_theme_scripts');

function add_logic_scripts() {
    wp_enqueue_script( 'logic', get_stylesheet_directory_uri() . '/js/logic.js' );
    wp_enqueue_script( 'burgerLogic', get_stylesheet_directory_uri() . '/js/burgerLogic.js' );
   }
add_action( 'wp_enqueue_scripts', 'add_logic_scripts' ); 

//visar vilken sida i template hierarkien som man är i(när man är inloggad)
function show_template_page()
{
    if (is_super_admin()) {
        global $template;
        print_r($template);
    }
}
add_action('wp_footer', 'show_template_page');

//Theme support
add_theme_support('menus');
add_theme_support('widgets');
add_theme_support('post-thumbnails');
add_theme_support( 'woocommerce' );


//Registrerar en header meny
function register_menu()
{
    register_nav_menu('header-menu', 'Header Menu');
}
add_action('after_setup_theme', 'register_menu', 'customtheme_add_woocommerce_support');
//ta bort archive produkter i shoppingsidan
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
// ----------------------------------------------------------------------------------------------------


//ändra i varukorg
add_filter( 'gettext', 'woocommerce_rename_coupon_field_on_cart', 10, 3 );
add_filter( 'gettext', 'woocommerce_rename_coupon_field_on_cart', 10, 3 );
add_filter('woocommerce_coupon_error', 'rename_coupon_label', 10, 3);
add_filter('woocommerce_coupon_message', 'rename_coupon_label', 10, 3);
add_filter('woocommerce_cart_totals_coupon_label', 'rename_coupon_label',10, 1);
add_filter( 'woocommerce_checkout_coupon_message', 'woocommerce_rename_coupon_message_on_checkout' );


function woocommerce_rename_coupon_field_on_cart( $translated_text, $text, $text_domain ) {
	// bail if not modifying frontend woocommerce text
	if ( is_admin() || 'woocommerce' !== $text_domain ) {
		return $translated_text;
	}
	if ( 'Coupon:' === $text ) {
		$translated_text = 'Rabattkod:';
	}

	if ('Coupon has been removed.' === $text){
		$translated_text = 'Rabattkod har blivit bortagen.';
	}

	if ( 'Apply coupon' === $text ) {
		$translated_text = 'Aktivera Rabattkod';
	}

	if ( 'Coupon code' === $text ) {
		$translated_text = 'Rabattkod';
	
	} 
    if ( 'Update cart' === $text){
		$translated_text = 'Uppdatera';
    }

	return $translated_text;
}


// rename the "Have a Coupon?" message on the checkout page
function woocommerce_rename_coupon_message_on_checkout() {
	return 'Have an Offer Code?' . ' ' . __( 'Click here to enter your code', 'woocommerce' ) . '';
}


function rename_coupon_label($err, $err_code=null, $something=null){

	$err = str_ireplace("Coupon","Offer Code ",$err);

	return $err;
}

/**
 * WooCommerce Show Product Image in Checkout Page
*/

add_filter( 'woocommerce_cart_item_name', 'ts_product_image_on_checkout', 10, 3 );

function ts_product_image_on_checkout( $name, $cart_item, $cart_item_key ) {  

    /* Return if not checkout page */
    if ( ! is_checkout() ) {
        return $name;
    }

    /* Get product object */
    $_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );

    /* Get product thumbnail */
    $thumbnail = $_product->get_image();

    /* Add wrapper to image and add some css */
    $image = '<div class="ts-product-image">'
                . $thumbnail .
            '</div>';

    /* Prepend image to name and return it */
    return $image . $name;

}
// mini cart i headern
add_filter( 'woocommerce_add_to_cart_fragments', 'header_add_to_cart_fragment', 30, 1 );
function header_add_to_cart_fragment( $fragments ) {
    global $woocommerce;

    ob_start();

    ?>
    <a class="cart-customlocation" href="<?php echo esc_url(wc_get_cart_url()); ?>" 
    title="<?php _e('View your shopping cart', 'woothemes'); ?>">
    <?php echo sprintf(_n('%d item', '%d items', $woocommerce->cart->cart_contents_count, 'woothemes'), $woocommerce->cart->cart_contents_count);?>
     - <?php echo $woocommerce->cart->get_cart_total(); ?></a>
    <?php
    $fragments['a.cart-customlocation'] = ob_get_clean();

    return $fragments;
}
