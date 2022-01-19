<?php

//Theme assets
function add_theme_scripts()
{
    //header scripts
    wp_enqueue_style('style', get_template_directory_uri() . '/css/style.css');
    wp_enqueue_style('style2', get_template_directory_uri() . '/css/style2.css');
    wp_enqueue_style('style3', get_template_directory_uri() . '/css/style3.css');
    wp_enqueue_style('style4', get_template_directory_uri() . '/css/style4.css');
	wp_enqueue_style('style5', get_template_directory_uri() . '/css/style5.css');
    
}
add_action('wp_enqueue_scripts', 'add_theme_scripts');

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

?>