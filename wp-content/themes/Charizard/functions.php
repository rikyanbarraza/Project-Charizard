<?php

//Theme assets
function add_theme_scripts()
{
    //header scripts
    wp_enqueue_style('style', get_template_directory_uri() . '/css/style.css');
    wp_enqueue_style('style2', get_template_directory_uri() . '/css/style2.css');
    wp_enqueue_style('style3', get_template_directory_uri() . '/css/style3.css');
    
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
<<<<<<< HEAD

=======
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
>>>>>>> parent of c525498 (delete)
?>