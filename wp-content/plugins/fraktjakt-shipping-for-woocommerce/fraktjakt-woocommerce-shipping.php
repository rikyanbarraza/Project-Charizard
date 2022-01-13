<?php
/*
Plugin Name: Fraktjakt Shipping Method for WooCommerce
Plugin URI: https://www.fraktjakt.se
Description: Fraktjakt shipping method plugin for WooCommerce. Integrates several shipping services through Fraktjakt.
Version: 2.4.3
Author: Fraktjakt AB (Sweden)
Author URI: https://www.fraktjakt.se
Domain Path: /languages
Text Domain: fraktjakt-shipping-for-woocommerce
WC requires at least: 3.2
WC tested up to: 5.6
*/
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if ( !defined( 'FRAKTJAKT_API_VERSION' ) ) {
    define( 'FRAKTJAKT_API_VERSION', '4.0.0' );
}
if ( !defined( 'FRAKTJAKT_PLUGIN_VERSION' ) ) {
    define( 'FRAKTJAKT_PLUGIN_VERSION', '2.4.3' );
}


/**
 * Check if WooCommerce is active
 */
if(is_plugin_active( 'woocommerce/woocommerce.php') || is_plugin_active_for_network( 'woocommerce/woocommerce.php')){

    function fraktjakt_shipping_method_init() {
        if ( ! class_exists( 'WC_Fraktjakt_Shipping_Method' ) ) {
            class WC_Fraktjakt_Shipping_Method extends WC_Shipping_Method {                               
								/** ---------------------------------------------------
                 * Constructor for the Fraktjakt Shipping Method class
                 * @access public
                 * @return void
                 */
                public function __construct() {
                    $this->id                 = 'fraktjakt_shipping_method'; // Shipping method Id. Should be unique.
                    $this->method_title       = __( 'Fraktjakt','fraktjakt-shipping-for-woocommerce' );  // Shipping method Title, as shown in shipping admin view
                    $this->init();
                }

								/** ---------------------------------------------------
                 *  Initialize the shipping method
                 *  @access public
                 *  @return void
                 */
                function init() {
                    // Load the settings
                    $this->init_form_fields(); 
                    $this->init_settings(); 
                    
                    // Define user set variables
                    $this->enable_debug_mode  = isset( $this->settings['enable_debug_mode'] ) ? $this->settings['enable_debug_mode'] : 'no';
                    $this->title    = 'Fraktjakt';
                    $this->fee      = isset( $this->settings['fee'] ) ? $this->settings['fee'] : '';
                    $this->test_mode                = isset( $this->settings['test_mode'] ) ? $this->settings['test_mode'] : 'production';
                    $this->trigger_state            = isset( $this->settings['trigger_state'] ) ? $this->settings['trigger_state'] : 'processing';
                    $this->currency_conversion      = isset( $this->settings['currency_conversion'] ) ? $this->settings['currency_conversion'] : 'default';
                    $this->shipping_company_info    = isset( $this->settings['shipping_company_info'] ) ? $this->settings['shipping_company_info'] : 'no';
                    $this->enable_frontend          = isset( $this->settings['enable_frontend'] ) ? $this->settings['enable_frontend'] : 'no';
                    $this->shipping_product_info    =  'yes'; 
                    $this->distance_closest_delivery_info   = isset( $this->settings['distance_closest_delivery_info'] ) ? $this->settings['distance_closest_delivery_info'] : 'no';
                    $this->enable_tracking_in_email = isset( $this->settings['enable_tracking_in_email'] ) ? $this->settings['enable_tracking_in_email'] : 'yes';
                    $this->enable_agent_email_selection	= isset( $this->settings['enable_agent_email_selection'] ) ? $this->settings['enable_agent_email_selection'] : 'no';
                    $this->enable_agent_checkout_selection	= isset( $this->settings['enable_agent_checkout_selection'] ) ? $this->settings['enable_agent_checkout_selection'] : 'no';
                    $this->estimated_delivery_info  = isset( $this->settings['estimated_delivery_info'] ) ? $this->settings['estimated_delivery_info'] : 'no';
                    $this->fallback_service_name    = isset( $this->settings['fallback_service_name'] ) ? $this->settings['fallback_service_name'] : 'Fraktjakt';
                    $this->fallback_service_price   = isset( $this->settings['fallback_service_price'] ) ? $this->settings['fallback_service_price'] : 50.0;
                    $this->dropoff_title            = isset( $this->settings['dropoff_title'] ) ? $this->settings['dropoff_title'] :"Home delivery";                
                    $this->order_reference_text     = isset( $this->settings['order_reference_text'] ) ? $this->settings['order_reference_text'] : 'Order';
                    $this->order_reference 			    = isset( $this->settings['order_reference'] ) ? $this->settings['order_reference'] : 'order';
                    $this->fraktjakt_admin_email    = isset( $this->settings['fraktjakt_admin_email'] ) ? $this->settings['fraktjakt_admin_email'] : "";
										$this->supports = array(
	                    'settings'
                    );            
                                               
                    if ($this->test_mode=='test') {                     // Fraktjakt TEST API environment
                        $this->uri_query='https://testapi.fraktjakt.se/';
                        $this->consignor_id = isset( $this->settings['consignor_id_test'] ) ? $this->settings['consignor_id_test'] : 'YOUR_CONSIGNOR_ID';
                        $this->consignor_key = isset( $this->settings['consignor_key_test'] ) ? $this->settings['consignor_key_test'] : 'YOUR_CONSIGNOR_KEY';
                        $this->referrer_code = isset( $this->settings['referrer_code_test'] ) ? $this->settings['referrer_code_test'] : '';
                    }
                    else {                                              // Fraktjakt PROD API environment
                        $this->uri_query='https://api.fraktjakt.se/';
                        $this->consignor_id = isset( $this->settings['consignor_id'] ) ? $this->settings['consignor_id'] : 'YOUR_CONSIGNOR_ID';
                        $this->consignor_key = isset( $this->settings['consignor_key'] ) ? $this->settings['consignor_key'] : 'YOUR_CONSIGNOR_KEY';
                        $this->referrer_code = isset( $this->settings['referrer_code'] ) ? $this->settings['referrer_code'] : '';
                    }
                    


                    if(is_admin() && $this->enable_debug_mode == 'yes') {

	                    $args = array(
	                        'post_type' => 'product',
	                        'posts_per_page' => '-1'
	                    );
	                    $product_query = new WP_Query( $args );

											if($product_query->have_posts()) {
		                    $product_errors = 0;

                        $post_count = $product_query->post_count;
                        $posts = $product_query->posts;
                        $problem_products = array();
                        
                        for ($i = 0; $i < $post_count; $i++) {
                            $product = new WC_Product( $posts[$i]->ID );                            
														if ( $product->get_virtual() == 'yes' ) {
															continue;
														}
                            if($product->get_weight() == '' || $product->get_weight() <= 0 || $product->get_length() == '' || $product->get_length() <= 0 || $product->get_width() == '' || $product->get_width() <= 0  || $product->get_height() == '' || $product->get_height() <= 0) {
                                array_push($problem_products, $posts[$i]);
                                $product_errors++;
                            }                                
                        }
                        
                        if ($product_errors > 0) {
		                      add_action('admin_notices', function() use ($product_errors, $problem_products) {
                                $class = "error";
                                $error_message = "<b>".__('Fraktjakt [WARNING]', 'fraktjakt-shipping-for-woocommerce')."</b><br>".$product_errors. __(' products are missing weight or volume: ', 'fraktjakt-shipping-for-woocommerce');
                                echo"<div class=\"$class\"> <p><mark>";
                                echo $error_message;
                                echo "</mark><span style=\"font-size: 10px; line-height: 1;\">";
                                $links = "";
                                for ($i = 0; $i < $product_errors; $i++) {
                                    $links .= edit_post_link($problem_products[$i]->post_name, '', ', ', $problem_products[$i]->ID);
                                }
                                echo "</span></p></div>";
 		                       }, 2);                        
                        }                           
	                    }
                    }
                   
                    // Process the admin options of the shipping method and save them in the database
                    add_action('woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ), 1 );

                }

								/** ---------------------------------------------------
                 *  Initialize the form fields of the admin page of the shipping method
                 *  @access public
                 *  @return void
                 */                
                function init_form_fields() {
                    global $woocommerce;

                    $this->form_fields = array(

                        // Frontend or backend 
                        'enable_frontend' => array(
                            'title' => __('Control mode', 'fraktjakt-shipping-for-woocommerce'),
                            'default' => 'yes',
                            'type' => 'select',
                            'class'         => 'wc-enhanced-select',
                            'options'    => array(
                              'yes'    => __( 'Customer controlled shipping', 'fraktjakt-shipping-for-woocommerce' ),
                              'no'   => __( 'Merchant controlled shipping', 'fraktjakt-shipping-for-woocommerce' ),
                            ),
                            'description' => __("Choose whether it's the customer or the merchant who makes the shipping decision.<br>The settings under Shipping alternatives are only applicable to \"Customer controlled shipping\"" , 'fraktjakt-shipping-for-woocommerce')
                        ),

                        // Trigger state
                        'trigger_state' => array(
                            'title' => __('Auto process', 'fraktjakt-shipping-for-woocommerce'),
                            'default' => 'processing',
                            'type' => 'select',
                            'class'         => 'wc-enhanced-select',
                            'options'    => array(
                              'no'   => __( 'No', 'fraktjakt-shipping-for-woocommerce' ),
                              'processing'    => __( 'Processing', 'fraktjakt-shipping-for-woocommerce' ),
                              'completed'   => __( 'Completed', 'fraktjakt-shipping-for-woocommerce' ),
                            ),
                            'description' => __("Choose which order status to automatically process and create shipments for.<br>If Manual is selected, then no orders will be processed automatically and you can select which orders to send over to Fraktjakt manually." , 'fraktjakt-shipping-for-woocommerce')
                        ),

                        'order_reference_text' => array(
                            'type' => 'text',
												    'css' => 'max-width:200px;',
                            'title' => __('Reference prefix', 'fraktjakt-shipping-for-woocommerce'),
                            'description' => __('Specify a text to prefix the order reference below.', 'fraktjakt-shipping-for-woocommerce'),
                            'default' => 'Order'
                        ),

                        'order_reference' => array(
                            'title' => __('Order reference', 'fraktjakt-shipping-for-woocommerce'),
                            'default' => 'order',
                            'type' => 'select',
                            'class'         => 'wc-enhanced-select',
                            'options'    => array(
                              'order'    => __( 'Order number', 'fraktjakt-shipping-for-woocommerce' ),
                              'customer note'   => __( 'Customer note', 'fraktjakt-shipping-for-woocommerce' ),
                            ),
                            'description' => __("Select how to reference shipments to your WooCommerce orders." , 'fraktjakt-shipping-for-woocommerce')
                        ),

                        'enable_tracking_in_email' => array(
                            'title' => __('Tracking link in email', 'fraktjakt-shipping-for-woocommerce'),
                            'type' => 'checkbox',
                            'label' => __('Add tracking link to your customer emails.', 'fraktjakt-shipping-for-woocommerce'),
                            'default' => 'true',
                            'description' => __('The appearance of the button can be styled with the CSS class tracking_button.', 'fraktjakt-shipping-for-woocommerce')
                        ),

                        // Operation mode
                        'test_mode' => array(
                            'title' => __('Operation Mode', 'fraktjakt-shipping-for-woocommerce'),
                            'type' => 'select',
                            'class'         => 'wc-enhanced-select',
                            'description' => __('Select which server environment to use. Requires a registered account on the chosen server.', 'fraktjakt-shipping-for-woocommerce'),
                            'default' => 'production',
                            'options'    => array(
                              'production'    => __( 'Production', 'fraktjakt-shipping-for-woocommerce' ),
                              'test'   => __( 'Test', 'fraktjakt-shipping-for-woocommerce' ),
                            )
                        ),

                        // Authentication production
                        array(
                            'title' => __('Authentication', 'fraktjakt-shipping-for-woocommerce'),
                            'type' => 'title',
                            'description' => __('Enter your Consignor ID and key from your Fraktjakt integration to connect this extension to your Fraktjakt account.', 'fraktjakt-shipping-for-woocommerce')
	                                            .getLoginLink(0, __( 'Direct link to Fraktjakt PROD API webshop settings', 'fraktjakt-shipping-for-woocommerce' )),
                        ),
		
		                        // Authentication
		                        'consignor_id' => array(
		                            'title' => __( 'Consignor ID', 'fraktjakt-shipping-for-woocommerce' ),
														    'css' => 'max-width:100px;',
		                            'type' => 'text',
		                        ),
		                        'consignor_key' => array(
		                            'title' => __( 'Consignor Key', 'fraktjakt-shipping-for-woocommerce' ),
		                            'type' => 'text',
		                        ),
		                        'referrer_code' => array(
		                            'title' => __( '(Integrator code)', 'fraktjakt-shipping-for-woocommerce' ),
		                            'type' => 'text',
		                            'description' => __('Integrator code for the production server (if supplied by your integrator).', 'fraktjakt-shipping-for-woocommerce'),
		                        ),

                        // Test authentication
                        array(
                            'title' => __('Test authentication', 'fraktjakt-shipping-for-woocommerce'),
                            'type' => 'title',
                            'description' => __('Enter your Consignor ID and key from your test server integration to connect this extension to your Fraktjakt account.', 'fraktjakt-shipping-for-woocommerce')
                                                .getLoginLink(1, __( 'Direct link to Fraktjakt TEST API webshop settings', 'fraktjakt-shipping-for-woocommerce' )),
                        ),

		                        'consignor_id_test' => array(
		                            'title' => __( 'Consignor ID', 'fraktjakt-shipping-for-woocommerce' ),
														    'css' => 'max-width:100px;',
		                            'type' => 'text',
		                        ),
		                        'consignor_key_test' => array(
		                            'title' => __( 'Consignor Key', 'fraktjakt-shipping-for-woocommerce' ),
		                            'type' => 'text',
		                        ),
		                        'referrer_code_test' => array(
		                            'title' => __( '(Integrator code)', 'fraktjakt-shipping-for-woocommerce' ),
		                            'type' => 'text',
		                            'description' => __('Integrator code for the test server (if supplied by your integrator).', 'fraktjakt-shipping-for-woocommerce'),
		                        ),
                        
                        // Shipping alternatives
                        array(
                            'title' => __('Shipping alternatives in customer controlled shipping', 'fraktjakt-shipping-for-woocommerce'),
                            'type' => 'title',
                            'description' => __('Choose which information to display about each shipping alternative in the shipping calculator, cart and checkout.', 'fraktjakt-shipping-for-woocommerce')
                        ),
                        
                            'shipping_company_info' => array(
		                            'title' => __('Shipping company', 'fraktjakt-shipping-for-woocommerce'),
                                'label' => __('Display shipping company names', 'fraktjakt-shipping-for-woocommerce'),
                                'default' => 'yes',
                                'type' => 'checkbox'
                            ),

                            'distance_closest_delivery_info' => array(
		                            'title' => __('Shipping agent', 'fraktjakt-shipping-for-woocommerce'),
                                'label' => __('Display Agent for package retrieval by the customer', 'fraktjakt-shipping-for-woocommerce'),
                                'default' => 'yes',
                                'type' => 'checkbox'
                            ),
                            'dropoff_title' => array(
		                            'title' => __('Home delivery title', 'fraktjakt-shipping-for-woocommerce'),
                                'type' => 'text',
														    'css' => 'max-width:250px;',
                                'description' => __('Only shipping products which include Door-to-Door delivery will display this text.  <br>Displayed in the shipping alternatives customers see in the cart and in checkout.', 'fraktjakt-shipping-for-woocommerce'),
                                'default' => __('Door-to-Door delivery', 'fraktjakt-shipping-for-woocommerce')
                            ),
                            'estimated_delivery_info' => array(
		                            'title' => __('Estimated delivery time', 'fraktjakt-shipping-for-woocommerce'),
                                'type' => 'checkbox',
                                'label' => __('Display Fraktjakts estimated delivery time info', 'fraktjakt-shipping-for-woocommerce'),
                                'default' => 'yes'
                            ),

		                        'enable_agent_checkout_selection' => array(
		                            'title' => __('Agent selection', 'fraktjakt-shipping-for-woocommerce'),
		                            'type' => 'checkbox',
		                            'label' => __('Enable agent selection during checkout.', 'fraktjakt-shipping-for-woocommerce'),
		                            'default' => 'false',
		                            'description' => __('Activate to allow your customers to change their prefered shipping agent through a link during checkout.', 'fraktjakt-shipping-for-woocommerce')
		                        ),
		                        
		                        'enable_agent_email_selection' => array(
		                             'title' => __('Agent selection in email', 'fraktjakt-shipping-for-woocommerce'),
		                             'type' => 'checkbox',
		                             'label' => __('Enable agent selection in email.', 'fraktjakt-shipping-for-woocommerce'),
		                             'default' => 'false',
		                             'description' => __('Activate to allow your customers to change their prefered shipping agent from an email link before you book the shipment.', 'fraktjakt-shipping-for-woocommerce')
		                        ),

		                        // Currency conversion
		                        'currency_conversion' => array(
		                            'title' => __('Currency conversion', 'fraktjakt-shipping-for-woocommerce'),
		                            'default' => 'default',
		                            'type' => 'select',
		                            'class' => 'wc-enhanced-select',
		                            'options'    => array(
		                              'default'   => __( 'Convert shipping costs to your selected currency', 'fraktjakt-shipping-for-woocommerce' ),
		                              'SEK'    => __( 'Always present shipping costs in SEK', 'fraktjakt-shipping-for-woocommerce' ),
		                              'EUR'    => __( 'Always present shipping costs in EUR', 'fraktjakt-shipping-for-woocommerce' )
		                            ),
		                            'description' => __("Choose if you want Fraktjakt to automatically convert the shipping costs to your chosen currency in WooCommerce or if prices should always be shown in a fixed currency value. What currency symbol is used depends on your WooCommerce setting." , 'fraktjakt-shipping-for-woocommerce')
		                        ),

                        // Debug title
                        array(
                            'title' => __('Debugging and fallback options', 'fraktjakt-shipping-for-woocommerce'),
                            'type' => 'title',
                            'description' => __('Set up fallback shipping options and email debug information if any error occurs.', 'fraktjakt-shipping-for-woocommerce')
                        ),
	                            
		                        // Fallback service
		                        'fallback_service_name' => array(
		                            'title' => __('Fallback service', 'fraktjakt-shipping-for-woocommerce'),
		                            'type' => 'text',
		                            'description' => __('This text is shown together with a Fallback price when the webshop does not receive a prompt response from Fraktjakt, <br>for instance, when there is a communications problem over the internet.', 'fraktjakt-shipping-for-woocommerce'),
		                            'default' => __('Standard shipping', 'fraktjakt-shipping-for-woocommerce')
		                        ),
		                        'fallback_service_price' => array(
		                            'title' => __('Fallback price', 'fraktjakt-shipping-for-woocommerce'),
		                            'type' => 'text',
														    'css' => 'max-width:100px;',
		                            'description' => __('The price that is shown together with the fallback text (above).', 'fraktjakt-shipping-for-woocommerce'),
		                            'default' => '50'
		                        ),
		                        // Admin email
		                        'fraktjakt_admin_email' => array(
		                            'title' => __('Admin email address', 'fraktjakt-shipping-for-woocommerce'),
		                            'type' => 'text',
		                            'description' => __('Error messages from the Fraktjakt Shipping Method will be sent to this email address.', 'fraktjakt-shipping-for-woocommerce')
		                        ),
		                        'enable_debug_mode' => array(
		                            'title' => __('Debug mode', 'fraktjakt-shipping-for-woocommerce'),
		                            'type' => 'checkbox',
		                            'label' => __('Enable the Fraktjakt debug mode to track down products missing weight or volume.<br />Initiates a search when you press the save button below. Changing settings requires you to first refresh the page.<br /><b>Important!</b> Please deactive before launching, since this action may significantly slow down your server.', 'fraktjakt-shipping-for-woocommerce'),
		                            'default' => 'false'
		                        )
		                        
                    );
                    
                }

								/** ---------------------------------------------------
                 *  Validate the Fraktjakt consignor id and key
                 *  To communicate successfully with Fraktjakt API's you need a valid Consignor Id/Key pair
                 *  @see validate_settings_fields()
                 */
                 
								public function validate_consignor_key($value)
								{
                    $uri = 'https://api.fraktjakt.se/';
                    $consignor_id = wp_kses_post( trim( stripslashes( $_POST[ $this->plugin_id . $this->id . '_' . 'consignor_id_test' ] ) ) );
                    $consignor_key = wp_kses_post( trim( stripslashes( $_POST[ $this->plugin_id . $this->id . '_' . 'consignor_key_test' ] ) ) );

                    if (($errmsg = authentication_check($consignor_id, $consignor_key, $uri)) != "") {
								        // return $value;
                        return wp_kses_post( trim( stripslashes( $_POST[ $this->plugin_id . $this->id . '_' . $key ] ) ) );
                    }
                    else {
                    
											  add_settings_error(
										        'fraktjaktConsignorKey',
										        'consignor_key',
										        __('Failed authentication', 'fraktjakt-shipping-for-woocommerce'),
										        'error'
										    );                   
                    }
								}

    
								/** ---------------------------------------------------
                 *  Display errors by overriding the display_errors() method 
                 *  @see display_errors()
                 */
                public function display_errors( ) {
                    // loop through each error and display it
                    foreach ( $this->errors as $key => $errmsg ) {
                        $error_message = "<b>".__('Fraktjakt Shipping Method [ERROR]', 'fraktjakt-shipping-for-woocommerce')."</b><br>".$errmsg;
                        $class = "error";
                        echo "<div class=\"$class\"> <p><mark>";
                        echo $error_message;
                        echo "</mark></p></div>";
                    }
                }

								/** ---------------------------------------------------
                 *  calculate_shipping function.
                 * 
                 *  @access public
                 *  @param mixed $package
                 *  @return void
                 */
                public function calculate_shipping( $package = array() ) {
                    global $woocommerce;

									  if( !is_page( 'cart' ) &&  !is_page( 'checkout' ) && !is_cart() && !is_checkout()){
									    return;
									  }
                    $cart_items = $woocommerce->cart->get_cart();

                    // Build the XML that will be sent to the Fraktjakt Query API
                    $xml ='<?xml version="1.0" encoding="UTF-8"?>'."\r\n";
                    $xml.='<shipment>'."\r\n";
                    if($this->referrer_code!='') {
                        $xml.='  <referrer_code>'.$this->referrer_code.'</referrer_code>'."\r\n";
                    }    
								    $fraktjakt_shipping_method_settings = get_option( 'woocommerce_fraktjakt_shipping_method_settings' );
	                  $xml.='  <value>'.($woocommerce->cart->get_cart_contents_total()+$woocommerce->cart->get_cart_contents_tax()).'</value>'."\r\n";
															
										if (empty($this->consignor_id)) {
											return;
										}
                    $xml.= consignor($this->consignor_id,$this->consignor_key);
                   
                    $xml.='  <address_to>'."\r\n";

                    $package['destination']['address']=($package['destination']['address']=='')?'Test street':$package['destination']['address'];
                    $xml.='    <street_address_1>'.$package['destination']['address'].'</street_address_1>'."\r\n";

                    $xml.='    <street_address_2>'.$package['destination']['address_2'].'</street_address_2>'."\r\n";
                    $xml.='    <postal_code>'.$package['destination']['postcode'].'</postal_code>'."\r\n";
                    $xml.='    <city_name>'.$package['destination']['city'].'</city_name>'."\r\n";
                    $xml.='    <residential>1</residential>'."\r\n";
                    $xml.='    <country_subdivision_code>'.$package['destination']['state'].'</country_subdivision_code>'."\r\n";
                    $xml.='    <country_code>'.$package['destination']['country'].'</country_code>'."\r\n";
                    $xml.='  </address_to>'."\r\n";


								    $xml.= commodities($cart_items);

                    $xml.='</shipment>'. "\r\n";

                    if($this->consignor_id!='' && $this->consignor_key!='' && $this->enable_frontend=='yes' && $package['destination']['postcode']!='' && $package['destination']['country']!='') {              
                        $httpHeaders = array(
                            "Expect: ",
                            "Accept-Charset: UTF-8",
                            "Content-type: application/x-www-form-urlencoded"
                        );

												// Convert to UTF8 with fallback if mb_string isn't loaded
												$httpPostParams = character_encode($xml);
										    
                        if (is_array($httpPostParams)) {
                            foreach ($httpPostParams as $key => $value) {
                                $postfields[$key] = $key .'='. urlencode($value);
                            }
                            $postfields = implode('&', $postfields);
                        }
                        $ch = curl_init($this->uri_query."fraktjakt/query_xml");
                        curl_setopt($ch, CURLOPT_FAILONERROR, false); // fail on errors
                        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true); // forces a non-cached connection
                        if ($httpHeaders) curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeaders); // set http headers
                        curl_setopt($ch, CURLOPT_POST, true); // initialize post method
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields); // variables to post
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // return into a variable
                        curl_setopt($ch, CURLOPT_TIMEOUT, 50); // timeout after 50s
                        $response = curl_exec($ch);
                        curl_close($ch);
                        $xml_data = simplexml_load_string( '<root>'.preg_replace( '/<\?xml.*\?>/', '', $response ).'</root>' );
                        $array = json_decode(json_encode($xml_data), true);

											  if ( !empty($array['shipment']) && (array_key_exists('agent_selection_link', $array['shipment'])) && is_checkout() ) {
                            $fraktjakt_agent_selection_link = $array['shipment']['agent_selection_link'];
						  	      			//update_post_meta($order->get_id(), 'fraktjakt_agent_selection_link', $fraktjakt_agent_selection_link);
						  	      			if (!empty($fraktjakt_agent_selection_link)) {
	  	      									setcookie('fraktjakt_agent_selection_link', $fraktjakt_agent_selection_link, time() + (600), "/");
	  	      								}
												}

												$error_happened="false";
                        if( !empty($array['shipment']) && is_array($array['shipment']) && array_key_exists('id', $array['shipment']) ) {
                            if (isset($array['shipment'])) {$fraktjakt_shipment_id=$array['shipment']['id'];} else {$fraktjakt_shipment_id="";}
                            if (isset($array['order'])) {$fraktjakt_order_id=$array['order']['id'];} else {$fraktjakt_order_id="";}
                                                        
                            if(empty($fraktjakt_shipment_id)){
                                //if no shipment_id is returned then show the FALLBACK method (if there is one), otherwise show an ERROR message.                     
                                if($this->fallback_service_name!='' && $this->fallback_service_price!='') {
                                    $rate = array(
                                        'id' => "fraktjakt_fallback",
                                        'label' => $this->fallback_service_name,
                                        'cost'  => $this->fallback_service_price,
                                        'tax_class' => 0,
                                        'meta_data' => array(
                                    				'id' => "fraktjakt_fallback",
                                    				)
                                    );
                                    $this->add_rate($rate);
                                } 
                                else {
                                    wc_add_notice( __('Fraktjakt Shipping Method [ERROR]', 'fraktjakt-shipping-for-woocommerce'), 'error' );
                                    $error_happened="true";
                                    return;                                    
                                }                                 
                            }
                            
                            //get shipping products array_expression
                            if (array_key_exists('shipping_products', $array['shipment'])) {
                              if (!empty($array['shipment']['shipping_products'])) {
                              	$array_expression = $array['shipment']['shipping_products']['shipping_product'];
                              } else { 
                             	  unset($array_expression);
                              }
															if(isset($array_expression) && is_array($array_expression)) {
		                            foreach($array_expression as $key=>$value) {
		                                if(is_array($value)=='') { //just 1 shipping_product
		                                    $array_expression = $array['shipment']['shipping_products'];
		                                    //$error_happened="true";
		                                    break;
		                                }
		                            }
		                            
		                            foreach($array_expression as $key=>$value) {                                
		                                $total_price = $value['price'];
		                                $description = $value['description'];
		                                $label = "";
		
		                                $description_data = explode("-",$description);
		
		                                if($this->shipping_company_info=='yes') {
		                                    $label.=$description_data[0];
		                                }
		                                else if ($value['id'] == 0) {
		                                    $label.=$description_data[0];
		                                }
		                                if($this->shipping_product_info=='yes') {
		                                    unset($description_data[0]);
		                                    if($label!='' && $value['id'] != 0) {
		                                        $label.=", ";
		                                    }
		                                    $label.=implode(" - ",$description_data);
		                                }
		                                if($this->distance_closest_delivery_info=='yes') {
		                                    if((!is_array($value['agent_link']) || !is_array($value['agent_info']))) {
		                                        // $label.='<br /><span style=\"font-weight: 400;\">';
		                                        $label.=', '.__( 'Agent','fraktjakt-shipping-for-woocommerce' ).'';
		                                        $label.=' ';
		                                        // if(!is_array($value['agent_link']) && !is_array($value['agent_info'])) {
		                                        //    $label.='<a href="'. $value['agent_link'] .'" target="_blank" style="color: #666666;">';
		                                        // }
		                                        if(!is_array($value['agent_info'])) {
		                                            $label.=$value['agent_info'];
		                                        }
		                                        // if(!is_array($value['agent_link']) && !is_array($value['agent_info'])) {
		                                        //     $label.='</a></span>';
		                                        // }
		                                    }
		                                    else {
		                                        // if ($this->dropoff_title != "") {
		                                        //     $label.='<br />';
		                                        // }
		                                        $label.=" (".$this->dropoff_title.")";
		                                    }
		                                }
		                                if(!is_array($value['arrival_time']) && $this->estimated_delivery_info=='yes') {
		                                    //$label.='<br /><span style=\"font-weight: 400;\">';
		                                    $label.=", ".__( 'Arrival Time','fraktjakt-shipping-for-woocommerce' ).'';
		                                    $label.=' ';
		                                    $label.=$value['arrival_time'];
		                                    // $label.="</span>";
		                                }
		                                // $label.='<br>';
		                                $rate = array(
		                                    'id' =>$this->id."_".trim($fraktjakt_shipment_id)."_".trim($value['id']),
		                                    'label' => $label,
		                                    'cost' => $total_price,
		                                    'meta_data' => array(
		                                    				'id' =>$this->id."_".trim($fraktjakt_shipment_id)."_".trim($value['id']),
		                                    				)
		                                );
			                            $this->add_rate( $rate );
	
		                            }
		                         }
								                         
														/** ---------------------------------------------------
						                 *  Display warnings for customer controlled queries 
						                 */
														if ((array_key_exists('warning_message', $array['shipment'])) && (!empty($array['shipment']['warning_message']))) {
														  wc_add_notice( __('Warning', 'fraktjakt-shipping-for-woocommerce').': '.$array['shipment']['warning_message'], 'warning' );
														}
		                         
                           } else {$error_happened="true";}
		                         
                        } else {$error_happened="true";}

                        if ($error_happened=='true') {

														if ((array_key_exists('warning_message', $array['shipment'])) && (!empty($array['shipment']['warning_message']))) {
														  wc_add_notice( __('Warning', 'fraktjakt-shipping-for-woocommerce').': '.$array['shipment']['warning_message'], 'error' );
														}
													//	if ((array_key_exists('error_message', $array['shipment'])) && (!empty($array['shipment']['warning_message']))) {
													//	  wc_add_notice( __('Error', 'fraktjakt-shipping-for-woocommerce').': '.$array['shipment']['error_message'], 'error' );
													//	}

                            if($this->fraktjakt_admin_email!='') {

                                $message="<p><div style='padding:20px;background:#f0f0f0;'><span style='font-size:2em;'>&#9888; </span> ".__('This is an automated error message from your WooCommerce integration.', 'fraktjakt-shipping-for-woocommerce')."</div></p>";
	                             	$message.="<p><b style='width:150px;display:inline-block;'>".__('Webshop', 'fraktjakt-shipping-for-woocommerce').":</b> ".get_bloginfo( 'name' )."<br />";
                                $message.="<b style='width:150px;display:inline-block;'>".__('Method', 'fraktjakt-shipping-for-woocommerce').":</b> ".$this->id."<br />";
                                $message.="<b style='width:150px;display:inline-block;'>".__('Function', 'fraktjakt-shipping-for-woocommerce').":</b> calculate_shipping"."</p>";
                                
                                if (empty($xml_data)) {
	                                $message.="<p><b style='width:150px;display:inline-block;'>".__('Reason', 'fraktjakt-shipping-for-woocommerce').":</b> <mark>".__('Missing response.  Using the FALLBACK method.', 'fraktjakt-shipping-for-woocommerce')."</mark>"."</p>";}
                                
																if (isset($array) && isset($array['shipment'])) {
																	if (array_key_exists('status', $array['shipment'])) {
		                                $message.="<p><b style='width:150px;display:inline-block;'>".__('Status', 'fraktjakt-shipping-for-woocommerce').":</b> ".$array['shipment']['status']."</p>";
																	}
																	if ((array_key_exists('warning_message', $array['shipment'])) && (!empty($array['shipment']['warning_message']))) {
		                                $message.="<p><b style='width:150px;display:inline-block;'>".__('Warning', 'fraktjakt-shipping-for-woocommerce').":</b><br/><pre style='white-space: auto;max-width:100%;'>  ".$array['shipment']['warning_message']."</pre></p>";
																	}
																	if (array_key_exists('error_message', $array['shipment'])) {
																		if (is_array($array['shipment']['error_message'])) {
			                                $message.="<p><b style='width:150px;display:inline-block;'>".__('Error', 'fraktjakt-shipping-for-woocommerce').":</b> <mark>".reset($array['shipment']['error_message'])."</mark></p>";
			                              } else { 
			                                $message.="<p><b style='width:150px;display:inline-block;'>".__('Error', 'fraktjakt-shipping-for-woocommerce').":</b> <mark>".$array['shipment']['error_message']."</mark></p>";
			                              } 
																	}
																	if (array_key_exists('shipping_products', $array['shipment'])) {
		                                $message.="<p><b>".__('Returned shipping options', 'fraktjakt-shipping-for-woocommerce').":</b><br /><pre style='white-space: auto;max-width:100%;'> ".(print_r(($array['shipment']['shipping_products']), true))."</pre></p>";
																	}
																}

																if (isset($items)) {
																	$message.="<p><b style='width:150px;display:inline-block;'>".__('Cart items', 'fraktjakt-shipping-for-woocommerce').":</b><ul style='padding-left:118px;'>";
																	foreach ($items as $product) {

															  		//Check if the product is  virtual. If so, then skip it.
															    	$is_virtual = get_post_meta( $product['product_id'], '_virtual', true );
																		if ( $is_virtual == 'yes' ) {
																			continue;
																		}
															      $product_id = $product['product_id'];
																		// If it's a product variation, get the product_data from the variation field instead.
																		$variable_product = new WC_Product_Variation( $product['variation_id'] );
																		if ( preg_match( '/^{"id":0,".*/', $variable_product ) ) {
														        	$product_data = new WC_Product( $product['product_id'] );
																		}
																		else {
																			$product_data = $variable_product;
																		}
														        $message.= '<li>'.$product['quantity'] .' &times; '. $product_data->get_name()."</li>";
																	}
																	$message.="</ul></p>";
																}

                                $message.="<p><b style='width:150px;display:inline-block;'>".__('Destination', 'fraktjakt-shipping-for-woocommerce').":</b> ".__('Postal code', 'fraktjakt-shipping-for-woocommerce').": ".$package['destination']['postcode']."<br />";
                                $message.="<span style='width:150px;display:inline-block;'>&nbsp;</span> ".__('Country', 'fraktjakt-shipping-for-woocommerce').": ".$package['destination']['country']."</p>";

                                $message.="<p><b style='width:150px;display:inline-block;'>".__('Fallback', 'fraktjakt-shipping-for-woocommerce').":</b> ".__('Title', 'fraktjakt-shipping-for-woocommerce').": ".$this->fallback_service_name."<br />";
                                $message.="<span style='width:150px;display:inline-block;'>&nbsp;</span> ".__('Price', 'fraktjakt-shipping-for-woocommerce').": ".$this->fallback_service_price." " . ($this->currency_conversion == 'SEK' ? "SEK" : get_woocommerce_currency()) . "</p>";

                                $message.="<p><b style='width:150px;display:inline-block;'>".__('Time', 'fraktjakt-shipping-for-woocommerce').":</b> ".date("Y-m-d, H:i")."</p>";
                                
                                if (!empty($consignor_id)) {
                               	 $message.="<p><b style='width:150px;display:inline-block;'>".__('Consignor ID', 'fraktjakt-shipping-for-woocommerce').":</b> ".$consignor_id."</p>";
                               	 $message.="<p><b style='width:150px;display:inline-block;'>".__('Server', 'fraktjakt-shipping-for-woocommerce').":</b> ".$fraktjakt_shipping_method_settings['test_mode']."</p>";
                                }
                                
																/**
																 * $code2='<pre><code>'.htmlspecialchars($xml_data, ENT_QUOTES).'</code></pre>';
                                 * $message.="<p><b style='width:150px;display:inline-block;'>".__('Reply', 'fraktjakt-shipping-for-woocommerce').":</b><br />".$code2."</p>";
								                 */

                                if (!empty($xml)) {
													 			 $code1='<pre><code>'.htmlspecialchars($xml, ENT_QUOTES).'</code></pre>';
                                 $message.="<p><b style='width:150px;display:inline-block;'>".__('Query', 'fraktjakt-shipping-for-woocommerce').":</b><br />".$code1."</p>";
																}

																if (!empty($response)) {
																	$response_formated=htmlspecialchars($response, ENT_QUOTES);
	                                $message.="<p><b style='width:150px;display:inline-block;'>".__('Response', 'fraktjakt-shipping-for-woocommerce').":</b><br /><pre style='display:block;white-space:pre-wrap;max-width:100%;hyphens:auto;word-wrap:break-word;word-break:break-all;'>".$response_formated."</pre></p>";
																	if (isset($array)) {
		                                $message.="<p><b style='width:150px;display:inline-block;'>".__('Array', 'fraktjakt-shipping-for-woocommerce').":</b><br /><pre style='display:block;white-space:pre-wrap;max-width:100%;hyphens:auto;word-wrap:break-word;word-break:break-all;'>".(print_r($array, true))."</pre></p>";
		                              }
	                              } else { 
	                                $message.="<p><b style='width:150px;display:inline-block;'>".__('Response', 'fraktjakt-shipping-for-woocommerce').":</b> <mark>".__('Missing response', 'fraktjakt-shipping-for-woocommerce')."</mark></p>";
																}																
																
                                $headers = array('Content-Type: text/html; charset=UTF-8');                                
                                wp_mail($this->fraktjakt_admin_email, __('Error message from Fraktjakt WooCommerce plugin.', 'fraktjakt-shipping-for-woocommerce'), $message,$headers);
                            }
                            //if nothing is returned from Fraktjakt Query API then show the FALLBACK method (if there is one), otherwise show an ERROR message.     
                            if($this->fallback_service_name!='' && $this->fallback_service_price!='') {
                                $label="";
                                $label.=$this->fallback_service_name;

														    $fraktjakt_shipping_method_settings = get_option( 'woocommerce_fraktjakt_shipping_method_settings' );
														    $testmode = $fraktjakt_shipping_method_settings['test_mode'];
																if ((array_key_exists('error_message', $array['shipment'])) && (!empty($array['shipment']['error_message'])) && ($testmode == 'test')) {
	                                $label.=", ".__('Debug info', 'fraktjakt-shipping-for-woocommerce').": ".$array['shipment']['error_message'];
																}

                                $rate = array(
                                    'id' => "fraktjakt_fallback",
                                    'label' => $label,
                                    'cost'  => $this->fallback_service_price,
                                    'meta_data' => array(
                                    				'id' => "fraktjakt_fallback",
                                    				)
                                );
                                $this->add_rate( $rate );
                            } 
                            else {
                                wc_add_notice( __('Shipping calculation error', 'fraktjakt-shipping-for-woocommerce'), 'error' );
                                return;                                    
                            }  
                        }
                    }


                }

            }

        }
    }
}


/** ---------------------------------------------------
 *   CSS loading
 *  ---------------------------------------------------
 */
add_action( 'wp_enqueue_scripts', 'load_fraktjakt_style' );
add_action( 'admin_enqueue_scripts', 'load_fraktjakt_style' );
function load_fraktjakt_style() {
		$options=get_option('woocommerce_fraktjakt_shipping_method_settings');
	  wp_register_style( 'fraktjakt_css', plugins_url( 'css/style.css', plugin_basename( __FILE__ ) ), false, '1.0.6' );
    wp_enqueue_style( 'fraktjakt_css', plugins_url( 'css/style.css', plugin_basename( __FILE__ ) ), false, '1.0.6' );
    wp_enqueue_script( 'fraktjakt_js', plugins_url( 'js/scripts.js', plugin_basename( __FILE__ ) ), array( 'jquery' ), '1.0.6' );
}

add_action( 'woocommerce_shipping_init', 'fraktjakt_shipping_method_init' );

function add_fraktjakt_shipping_method( $methods ) {
    $methods['fraktjakt_shipping_method'] = 'WC_Fraktjakt_Shipping_Method';
    return $methods;
}
add_filter( 'woocommerce_shipping_methods', 'add_fraktjakt_shipping_method' );

//Not used?
function consignor_admin_error_notice() {
    $class = "error";
    $error_message = "Consignor Id OR Consignor Key missing";
        echo"<div class=\"$class\"> <p><mark>$error_message</mark></p></div>"; 
}

/** ---------------------------------------------------
 *   Remove colon from shipping product label
 *  ---------------------------------------------------
 */
add_filter( 'woocommerce_cart_shipping_method_full_label', 'wc_custom_shipping_labels', 10, 2 );
function wc_custom_shipping_labels( $label, $method ) {
		$options=get_option('woocommerce_fraktjakt_shipping_method_settings');

    $label = str_replace(":"," ",$label);
	  $label = str_replace(" ,",",",$label);
    
    return $label;    
}

/** -------------------------------------------------------------------
 *   Get the Fraktjakt API login link
 * 
 *   Used in function init_form_fields (in the Authentication section)
 *  -------------------------------------------------------------------
 */
function getLoginLink($testmode, $linkText) {
    $fraktjakt_shipping_method_settings = get_option( 'woocommerce_fraktjakt_shipping_method_settings' );
    if ($testmode == 1) {
        $uri = 'https://testapi.fraktjakt.se/';
        $consignor_id = $fraktjakt_shipping_method_settings['consignor_id_test'];
        $consignor_key = $fraktjakt_shipping_method_settings['consignor_key_test'];
    }
    else {
        $uri = 'https://api.fraktjakt.se/';
        $consignor_id = $fraktjakt_shipping_method_settings['consignor_id'];
        $consignor_key = $fraktjakt_shipping_method_settings['consignor_key'];
    }
    
    $link = '<br/><a href=\"'.$uri.'webshops/change?consignor_id='.$consignor_id.'&consignor_key='.$consignor_key. '\" target=\"_blank\">'.$linkText.'</a>';
                 
    return $link;
}

/** ---------------------------------------------------
 *   Authentication check
 *  ---------------------------------------------------
 */
function authentication_check($consignor_id, $consignor_key, $server) {
    $fraktjakt_shipping_method_settings = get_option( 'woocommerce_fraktjakt_shipping_method_settings' );
    $xml ='<?xml version="1.0" encoding="UTF-8"?>'."\r\n";
    $xml.='<shipment>'."\r\n";
    $xml.='  <authentication_check>1</authentication_check>'."\r\n";

		$xml.= consignor($consignor_id, $consignor_key);

    $xml.='</shipment>'. "\r\n";
    
    $httpHeaders = array(
      "Expect: ",
      "Accept-Charset: UTF-8",
      "Content-type: application/x-www-form-urlencoded"
    );

		// Convert to UTF8 with fallback if mb_string isn't loaded
		$httpPostParams = character_encode($xml);

    if (is_array($httpPostParams)) {
        foreach ($httpPostParams as $key => $value) {
            $postfields[$key] = $key .'='. urlencode($value);
        }
        $postfields = implode('&', $postfields);
    }
    
    $ch = curl_init($server."fraktjakt/query_xml");
    curl_setopt($ch, CURLOPT_FAILONERROR, false); // fail on errors
    curl_setopt($ch, CURLOPT_FRESH_CONNECT, true); // forces a non-cached connection
    if ($httpHeaders) curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeaders); // set http headers
    curl_setopt($ch, CURLOPT_POST, true); // initialize post method
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields); // variables to post
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // return into a variable
    curl_setopt($ch, CURLOPT_TIMEOUT, 30); // timeout after 30s
    $response = curl_exec($ch);
    curl_close($ch);
    $xml_data = simplexml_load_string( '<root>'.preg_replace( '/<\?xml.*\?>/', '', $response ).'</root>' );
    $array = json_decode(json_encode($xml_data), true);
    
    $error_message="";
    if(is_array($array['shipment'])) {
        if ($array['shipment']['code'] != 0) {
            $error_message.=$array['shipment']['error_message'];
        }
    }
    else {
        $error_message = "Unable to reach $server";
    }
    return $error_message;
}

/** -----------------------------------------------------
 *   Create a shipment using the Fraktjakt Shipment API
 *  -----------------------------------------------------
 */
function fraktjakt_create_shipment($order, $uri_query, $consignor_id, $consignor_key, $referrer_code){

    $fraktjakt_shipping_method_settings = get_option( 'woocommerce_fraktjakt_shipping_method_settings' );
             
    // Build the CreateShipment XML    
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\r\n";     
    $xml.= '<CreateShipment>' . "\r\n";
    if($referrer_code!='') {
        $xml.= '  <referrer_code>'.$referrer_code.'</referrer_code>'."\r\n";
    }

		if (empty($consignor_id)) {
			return;
		}
		$xml.= consignor($consignor_id, $consignor_key);

    if ($fraktjakt_shipping_method_settings['order_reference'] != 'customer note') {
	    $xml.= '  <reference>'.$fraktjakt_shipping_method_settings['order_reference_text']." ". $order->get_order_number() .'</reference>' . "\r\n";
	  } else {
	    $xml.= '  <reference>'.$fraktjakt_shipping_method_settings['order_reference_text']." ". $order->get_customer_note() .'</reference>' . "\r\n";
	  }

		$commodities=commodities($order->get_items());
    $xml.= $commodities;

		if (!empty($commodities)) {

	    $xml.= address_to($order);

	    $xml.= recipient($order);
	
	    $xml.= '</CreateShipment>' . "\r\n";
	
	    $httpHeaders = array(
	        "Expect: ",
	        "Accept-Charset: UTF-8",
	        "Content-type: application/x-www-form-urlencoded"
	    );

			// Convert to UTF8 with fallback if mb_string isn't loaded
			$httpPostParams = character_encode($xml);

	    if (is_array($httpPostParams)) {
	        foreach ($httpPostParams as $key => $value) {
	            $postfields[$key] = $key .'='. urlencode($value);
	        }
	        $postfields = implode('&', $postfields);
	    }
	    $ch = curl_init($uri_query."shipments/shipment_xml");
	    curl_setopt($ch, CURLOPT_FAILONERROR, false); // fail on errors
	    curl_setopt($ch, CURLOPT_FRESH_CONNECT, true); // forces a non-cached connection
	    if ($httpHeaders) curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeaders); // set http headers
	    curl_setopt($ch, CURLOPT_POST, true); // initialize post method
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields); // variables to post
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // return into a variable
	    curl_setopt($ch, CURLOPT_TIMEOUT, 30); // timeout after 30s
	    $response = curl_exec($ch);
	    curl_close($ch);  
	    $xml_data = simplexml_load_string( '<root>'.preg_replace( '/<\?xml.*\?>/', '', $response ).'</root>' );
	    $array = json_decode(json_encode($xml_data), true);
	    
			update_fraktjakt_meta($array, $order);
	  }   
}


/** -----------------------------------------------------
 *  Order a shipment using the Fraktjakt Order API type 1
 *  -----------------------------------------------------
 */
function fraktjakt_send_order_type_1($order, $shipping_product_id, $fraktjakt_shipment_id, $uri_query, $consignor_id, $consignor_key, $referrer_code){   
    
        $fraktjakt_shipping_method_settings = get_option( 'woocommerce_fraktjakt_shipping_method_settings' );
             
        // Build the OrderSpecification XML    
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\r\n";        
        $xml.= '<OrderSpecification>' . "\r\n";
        if($referrer_code!='') {
            $xml.= '  <referrer_code>'.$referrer_code.'</referrer_code>'."\r\n";
        }

				if (empty($consignor_id)) {
					return;
				}
        $xml.= consignor($consignor_id, $consignor_key);

        $xml.= '  <shipment_id>'. $fraktjakt_shipment_id .'</shipment_id>' . "\r\n";
        $xml.= '  <shipping_product_id>'. $shipping_product_id .'</shipping_product_id>' . "\r\n";
  		  if ($fraktjakt_shipping_method_settings['order_reference'] != 'customer note') {
			    $xml.= '  <reference>'.$fraktjakt_shipping_method_settings['order_reference_text']." ". $order->get_order_number() .'</reference>' . "\r\n";
			  } else {
			    $xml.= '  <reference>'.$fraktjakt_shipping_method_settings['order_reference_text']." ". $order->get_customer_note() .'</reference>' . "\r\n";
			  }
			  
		    $xml.= commodities($order->get_items());
		    $xml.= address_to($order);
		    $xml.= recipient($order);
    
        $xml.= '</OrderSpecification>' . "\r\n";
    
        $httpHeaders = array(
            "Expect: ",
            "Accept-Charset: UTF-8",
            "Content-type: application/x-www-form-urlencoded"
        );

				// Convert to UTF8 with fallback if mb_string isn't loaded
				$httpPostParams = character_encode($xml);

        if (is_array($httpPostParams)) {
            foreach ($httpPostParams as $key => $value) {
                $postfields[$key] = $key .'='. urlencode($value);
            }
            $postfields = implode('&', $postfields);
        }
        $ch = curl_init($uri_query."orders/order_xml");
        curl_setopt($ch, CURLOPT_FAILONERROR, false); // fail on errors
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true); // forces a non-cached connection
        if ($httpHeaders) curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeaders); // set http headers
        curl_setopt($ch, CURLOPT_POST, true); // initialize post method
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields); // variables to post
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // return into a variable
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); // timeout after 30s
        $response = curl_exec($ch);
        curl_close($ch);
        $xml_data = simplexml_load_string( '<root>'.preg_replace( '/<\?xml.*\?>/', '', $response ).'</root>' );
        $array = json_decode(json_encode($xml_data), true);
        
				update_fraktjakt_meta($array, $order);
}

/** -------------------------------------------------------
 *   Order a shipment using the Fraktjakt Order API type 2
 *  -------------------------------------------------------
 */
function fraktjakt_send_order_type_2($order, $shipping_product_id, $uri_query, $consignor_id, $consignor_key, $referrer_code){   
    
    $fraktjakt_shipping_method_settings = get_option( 'woocommerce_fraktjakt_shipping_method_settings' );

    $shipping_country = get_post_meta( $order->get_id(), '_shipping_country', true );              

    if ($shipping_country == 'SE' || $shipping_country == 'se' ) {
        $shipping_product_id = ($shipping_product_id == '') ? '84' : $shipping_product_id;
    }
    else {
		
        $shipping_product_id = ($shipping_product_id == '') ? '119' : $shipping_product_id;
    }    
            
    // Build the OrderSpecification XML    
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\r\n";     
    $xml.= '<OrderSpecification>' . "\r\n";
    if($referrer_code!='') {
        $xml.= '  <referrer_code>'.$referrer_code.'</referrer_code>'."\r\n";
    }

		if (empty($consignor_id)) {
			return;
		}
		$xml.= consignor($consignor_id, $consignor_key);
   
    $xml.= '  <shipping_product_id>'. $shipping_product_id .'</shipping_product_id>' . "\r\n";
    if ($fraktjakt_shipping_method_settings['order_reference'] != 'customer note') {
	    $xml.= '  <reference>'.$fraktjakt_shipping_method_settings['order_reference_text']." ". $order->get_order_number() .'</reference>' . "\r\n";
	  } else {
	    $xml.= '  <reference>'.$fraktjakt_shipping_method_settings['order_reference_text']." ". $order->get_customer_note() .'</reference>' . "\r\n";
	  }

    $xml.= commodities($order->get_items());
    $xml.= address_to($order);
    $xml.= recipient($order);

    $xml.= '</OrderSpecification>' . "\r\n";

    $httpHeaders = array(
        "Expect: ",
        "Accept-Charset: UTF-8",
        "Content-type: application/x-www-form-urlencoded"
    );

		// Convert to UTF8 with fallback if mb_string isn't loaded
		$httpPostParams = character_encode($xml);

    if (is_array($httpPostParams)) {
        foreach ($httpPostParams as $key => $value) {
            $postfields[$key] = $key .'='. urlencode($value);
        }
        $postfields = implode('&', $postfields);
    }
    $ch = curl_init($uri_query."orders/order_xml");
    curl_setopt($ch, CURLOPT_FAILONERROR, false); // fail on errors
    curl_setopt($ch, CURLOPT_FRESH_CONNECT, true); // forces a non-cached connection
    if ($httpHeaders) curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeaders); // set http headers
    curl_setopt($ch, CURLOPT_POST, true); // initialize post method
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields); // variables to post
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // return into a variable
    curl_setopt($ch, CURLOPT_TIMEOUT, 30); // timeout after 30s
    $response = curl_exec($ch);
    curl_close($ch);  
    $xml_data = simplexml_load_string( '<root>'.preg_replace( '/<\?xml.*\?>/', '', $response ).'</root>' );
    $array = json_decode(json_encode($xml_data), true);
    
		update_fraktjakt_meta($array, $order);
}

/** ---------------------------------------------------
 *    Determine which Fraktjakt API will be used
 *  ---------------------------------------------------
 */
function Fraktjakt_api_selecter($order_id){
    
    $fraktjakt_shipping_method_settings = get_option( 'woocommerce_fraktjakt_shipping_method_settings' );
    
    $order = new WC_Order( $order_id );

    $testmode = $fraktjakt_shipping_method_settings['test_mode'];
    if ($testmode == 'test') {
        $uri_query = 'https://testapi.fraktjakt.se/';
        $consignor_id = $fraktjakt_shipping_method_settings['consignor_id_test'];
        $consignor_key = $fraktjakt_shipping_method_settings['consignor_key_test'];
        $referrer_code = $fraktjakt_shipping_method_settings['referrer_code_test'];
    }
    else {
        $uri_query = 'https://api.fraktjakt.se/';
        $consignor_id = $fraktjakt_shipping_method_settings['consignor_id'];
        $consignor_key = $fraktjakt_shipping_method_settings['consignor_key'];
        $referrer_code = $fraktjakt_shipping_method_settings['referrer_code'];
    }  
    $enable_frontend = $fraktjakt_shipping_method_settings['enable_frontend'];
    $fraktjakt_order_id = get_post_meta( $order_id, 'fraktjakt_order_id', true);
    $fraktjakt_shipment_id = get_post_meta( $order_id, 'fraktjakt_shipment_id', true); 
    $fraktjakt_access_code = get_post_meta( $order_id, 'fraktjakt_access_code', true); 
    $fraktjakt_access_link = get_post_meta( $order_id, 'fraktjakt_access_link', true); 
    
    $fallback = false;
	
		// changed in 1.7.0 to get the fraktjakt method_id from meta_data instead of the method_id field
		foreach( $order->get_items('shipping') as $item ){
    	// get order item data (in an unprotected array)
    	$item_data = $item->get_data();

    	// get order item meta data (in an unprotected array)
    	$item_meta_data = $item->get_meta_data();
    	
			foreach($item_meta_data as $something) {		
				$metadata = $something->value;
				if (!empty($meta_data)) {
					 if ($meta_data == "fraktjakt_fallback") {
	               	$fallback = true;
	        	}
	       }
				$method=explode("_",$metadata);
      	$shipping_product_id=$method[count($method)-1];
      	if (array_key_exists(count($method)-2, $method)) {
        	if (!empty($method[count($method)-2])) {$shipment_id=$method[count($method)-2];}
        }
			}
		
		}
	
	
    if($enable_frontend=='yes' && $fraktjakt_order_id == '' && $fallback == false && is_numeric($shipping_product_id)) {
        
        if ( ($method[0] == "fraktjakt" && $shipping_product_id==0)) {
            return;  // Stop here, since there is no $shipping_product_id
        }    
        else if ($method[0] == "fraktjakt" && is_numeric($shipment_id)) {
            fraktjakt_send_order_type_1($order, $shipping_product_id, $shipment_id, $uri_query, $consignor_id, $consignor_key, $referrer_code);  // Create order using Order API type 1
        }     
        else{
            fraktjakt_send_order_type_2($order, $shipping_product_id, $uri_query, $consignor_id, $consignor_key, $referrer_code);   // Create order using Order API type 2
        }
    }
    else if ($fraktjakt_shipment_id == '' && $fraktjakt_access_code == '') {
        fraktjakt_create_shipment($order, $uri_query, $consignor_id, $consignor_key, $referrer_code);   // Create shipment using Shipment API (order created manually in Fraktjakt GUI)
    }

}

$fraktjakt_shipping_method_settings = get_option( 'woocommerce_fraktjakt_shipping_method_settings' );
if (isset($fraktjakt_shipping_method_settings['trigger_state'])) {
	if ($fraktjakt_shipping_method_settings['trigger_state']=='processing') {add_action( 'woocommerce_order_status_processing', 'Fraktjakt_api_selecter' );}
	if ($fraktjakt_shipping_method_settings['trigger_state']=='completed') {add_action( 'woocommerce_order_status_completed', 'Fraktjakt_api_selecter' );}
} else {
	add_action( 'woocommerce_order_status_processing', 'Fraktjakt_api_selecter' );
}

/** ---------------------------------------------------
 *   Add a Fraktjakt button to Order admin page
 *  ---------------------------------------------------
 */

add_action( 'add_meta_boxes', 'Fraktjakt_order_meta_box' );

function Fraktjakt_order_meta_box()
{
		$options=get_option('woocommerce_fraktjakt_shipping_method_settings');
    add_meta_box(
        'fraktjakt_woocommerce_shipping_method-order-button',
        __( 'Fraktjakt' ),
        'Fraktjakt_order_meta_box_content',
        'shop_order',
        'side',
        'default'
    );
}

/** ---------------------------------------------------
 *   Make the Fraktjakt button do something
 *  ---------------------------------------------------
 */
function Fraktjakt_order_meta_box_content()
{
    global $woocommerce, $post;
    //Here's the WooCommerce order object  
    $order = new WC_Order($post->ID);  
    //Get the Fraktjakt order_id
    $fraktjakt_order_id = get_post_meta( $order->get_id(), 'fraktjakt_order_id', true); //36250;
    $fraktjakt_shipment_id = get_post_meta( $order->get_id(), 'fraktjakt_shipment_id', true); 
    $fraktjakt_access_code = get_post_meta( $order->get_id(), 'fraktjakt_access_code', true); 
    $fraktjakt_access_link = get_post_meta( $order->get_id(), 'fraktjakt_access_link', true); 
    $fraktjakt_tracking_link = get_post_meta( $order->get_id(), 'fraktjakt_tracking_link', true); 
    
    $fraktjakt_shipping_method_settings = get_option( 'woocommerce_fraktjakt_shipping_method_settings' );
    $testmode = $fraktjakt_shipping_method_settings['test_mode'];
    if ($testmode == 'test') {
        $uri = 'https://testapi.fraktjakt.se/';
        $consignor_id = $fraktjakt_shipping_method_settings['consignor_id_test'];
        $consignor_key = $fraktjakt_shipping_method_settings['consignor_key_test'];
    }
    else {
        $uri = 'https://api.fraktjakt.se/';
        $consignor_id = $fraktjakt_shipping_method_settings['consignor_id'];
        $consignor_key = $fraktjakt_shipping_method_settings['consignor_key'];
    }
    
    // Fraktjakt access link
    if (!empty($fraktjakt_access_link)) {
	        // Fraktjakt button to order
	        echo '<input id="fraktjakt_order_button" class="button-primary" type="button" value="'.__( 'Manage shipment', 'fraktjakt-shipping-for-woocommerce' ).'" title="'.__( 'Manage the order in Fraktjakt', 'fraktjakt-shipping-for-woocommerce' ).'">';    
	        echo "<script type=\"text/javascript\" >
	            jQuery('#fraktjakt_order_button').click(function($) {
	                var data = {
	                    'action': 'fraktjakt-access-shipment',
	                    'postId': '".$post->ID."' 
	                };
                  window.open('".$fraktjakt_access_link."','_blank');
	            });
	        </script>"; 
    } 
    
    // Fraktjakt shipment
    if (empty($fraktjakt_access_link) && $fraktjakt_shipment_id != '' && $fraktjakt_access_code != '') {
        // Fraktjakt button to shipment
        echo "<input id=\"fraktjakt_shipment_button\" class=\"button-primary\" type=\"button\" value=\"".__( 'Manage shipment', 'fraktjakt-shipping-for-woocommerce' )."\" title=\"".__( 'Manage the shipment in Fraktjakt', 'fraktjakt-shipping-for-woocommerce' )."\">";
        echo "<script type=\"text/javascript\" >
            jQuery('#fraktjakt_shipment_button').click(function($) {
                var data = {
                    'action': 'fraktjakt-access-shipment',
                    'postId': '".$post->ID."' 
                };
                window.open('".$uri."shipments/show/".$fraktjakt_shipment_id."?access_code=".$fraktjakt_access_code."','_blank'); 
            });
        </script>";    
    }

    // Fraktjakt trace link
    if (!empty($fraktjakt_tracking_link)) {
	        // Fraktjakt button to order
	        echo '&nbsp; <input id="fraktjakt_trace_button" class="button" type="button" value="'.__( 'Trace shipment', 'fraktjakt-shipping-for-woocommerce' ).'" title="'.__( 'Trace the shipment', 'fraktjakt-shipping-for-woocommerce' ).'">';    
	        echo "<script type=\"text/javascript\" >
	            jQuery('#fraktjakt_trace_button').click(function($) {
	                var data = {
	                    'action': 'fraktjakt-trace-shipment',
	                    'postId': '".$post->ID."' 
	                };
                  window.open('".$fraktjakt_tracking_link."','_blank');
	            });
	        </script>"; 
    } 

    if (empty($fraktjakt_access_link) && empty($fraktjakt_shipment_id)) {
    	echo __( 'Order connection missing', 'fraktjakt-shipping-for-woocommerce' );
    } 
}


/** ---------------------------------------------------
 *   Add Fraktjakt buttons to Order List Page
 *  ---------------------------------------------------
 */

add_filter('woocommerce_admin_order_actions', 'Fraktjakt_order_actions', 10, 2);

//Get the current post type
function get_current_post_type() {
  global $post, $statusnow, $current_screen;
	
  //we have a post so we can just get the post type from that
  if ( $post && $post->post_status )
    return $post->post_status;
    
  //check the global $typenow - set in admin.php
  elseif( $statusnow )
    return $statusnow;
    
  //check the global $current_screen object - set in sceen.php
  elseif( $current_screen && $current_screen->post_status )
    return $current_screen->post_status;
  
  //lastly check the post_type querystring
  elseif( isset( $_REQUEST['post_status'] ) )
    return sanitize_key( $_REQUEST['post_status'] );
	
  //we do not know the post type!
  return null;
}


function Fraktjakt_order_actions($actions, $the_order) {
    $fraktjakt_shipping_method_settings = get_option( 'woocommerce_fraktjakt_shipping_method_settings' );
    
		$options=get_option('woocommerce_fraktjakt_shipping_method_settings');
    $testmode = $fraktjakt_shipping_method_settings['test_mode'];
    
    if ($testmode == 'test') {
            $uri = 'https://testapi.fraktjakt.se/';
            $consignor_id = $fraktjakt_shipping_method_settings['consignor_id_test'];
            $consignor_key = $fraktjakt_shipping_method_settings['consignor_key_test'];
    }
    else {
            $uri = 'https://api.fraktjakt.se/';
            $consignor_id = $fraktjakt_shipping_method_settings['consignor_id'];
            $consignor_key = $fraktjakt_shipping_method_settings['consignor_key'];
    }   
    $fraktjakt_order_id = get_post_meta( $the_order->get_id(), 'fraktjakt_order_id', true);
    $fraktjakt_shipment_id = get_post_meta( $the_order->get_id(), 'fraktjakt_shipment_id', true); 
    $fraktjakt_access_code = get_post_meta( $the_order->get_id(), 'fraktjakt_access_code', true);
    $fraktjakt_access_link = get_post_meta( $the_order->get_id(), 'fraktjakt_access_link', true);
    $fraktjakt_tracking_link = get_post_meta( $the_order->get_id(), 'fraktjakt_tracking_link', true);

    if ((!empty($fraktjakt_shipment_id) && $fraktjakt_access_code != '') || !empty($fraktjakt_access_link)) {
        $url = (!empty($fraktjakt_access_link) ? $fraktjakt_access_link : $uri."shipments/show/".$fraktjakt_shipment_id."?access_code=".$fraktjakt_access_code);
        if (!empty($fraktjakt_access_link)) {
        	$manage=__( 'Manage shipment', 'fraktjakt-shipping-for-woocommerce' );
					$ikon="view fraktjakt-handle-shipment";
        } else {
					$manage=__( 'Manage shipment', 'fraktjakt-shipping-for-woocommerce' );
					$ikon="view fraktjakt-handle-shipment";
        }
        $actions['fraktjakt-view-shipment'] = array(
            'url'       => $url,
            'name'      => $manage,
            'action'    => $ikon
	      );
	      if (!empty($fraktjakt_tracking_link)) {
					$track=__( 'Trace shipment', 'fraktjakt-shipping-for-woocommerce' );
	        $actions['fraktjakt-track-shipment'] = array(
	            'url'       => $fraktjakt_tracking_link,
	            'name'      => $track,
	            'action'    => "view fraktjakt-track-shipment"
		      );
		    }
    }
    
    else {
        $actions['fraktjakt-create--connection'] = array(
            'url'       => wp_nonce_url( admin_url( 'admin-ajax.php?action=fraktjakt_create_order_connection&order_id=' . $the_order->get_id() ), 'fraktjakt-create-shipment' ),
            'name'      => __( 'Create order connection to Fraktjakt', 'fraktjakt-shipping-for-woocommerce' ), //tooltip
            'action'    => "view fraktjakt-create-order-connection" //css classes (view is used to get correct button style)
      );
    }
    return $actions;
}

/** ---------------------------------------------------
 *   Add order connection action
 *  ---------------------------------------------------
 */

add_action( 'wp_ajax_fraktjakt_create_order_connection', 'fraktjakt_create_order_connection_action' );

function fraktjakt_create_order_connection_action() {
    
    $order_id = intval( $_GET['order_id'] );

    Fraktjakt_api_selecter($order_id);

		if (get_current_post_type() == 'wc-processing') {
			header("Location: ".admin_url("edit.php?post_status=wc-processing&post_type=shop_order"));
	   	echo '<html><head><meta http-equiv="refresh" content="0; url='.admin_url("edit.php?post_status=wc-processing&post_type=shop_order").'"></head></html>';
		}
		else {
	  	header("Location: ".admin_url("edit.php?post_type=shop_order"));
	  	echo '<html><head><meta http-equiv="refresh" content="0; url='.admin_url("edit.php?post_type=shop_order").'"></head></html>';
		}
	
    wp_die(); // this is required to terminate immediately and return a proper response
}

/** ---------------------------------------------------
 *   Fraktjakt API Consignor XML
 *  ---------------------------------------------------
 */

function consignor($consignor_id, $consignor_key) {
  $consignor_xml = '  <consignor>' . "\r\n";
  $consignor_xml.= '    <id>'.$consignor_id.'</id>' . "\r\n";
  $consignor_xml.= '    <key>'.$consignor_key.'</key>' . "\r\n";
	$fraktjakt_shipping_method_settings = get_option( 'woocommerce_fraktjakt_shipping_method_settings' );
  $consignor_xml.= '    <currency>' . (empty($fraktjakt_shipping_method_settings['currency_conversion']) ? 'SEK' : ( $fraktjakt_shipping_method_settings['currency_conversion'] == 'SEK' ? "SEK" : ( $fraktjakt_shipping_method_settings['currency_conversion'] == 'EUR' ? "EUR" : get_woocommerce_currency() ) )) . '</currency>' . "\r\n";
  $consignor_xml.= '    <language>' . ((substr(get_locale(), 0, 2) == 'sv') ? 'sv' : 'en') . '</language>' . "\r\n";
  $consignor_xml.= '    <system_name>WooCommerce</system_name>'."\r\n";
  $consignor_xml.= '    <module_version>'.FRAKTJAKT_PLUGIN_VERSION.'</module_version>'."\r\n";
  $consignor_xml.= '    <api_version>'.FRAKTJAKT_API_VERSION.'</api_version>'."\r\n";
  $consignor_xml.= '  </consignor>' . "\r\n";
	return $consignor_xml;
}

/** ---------------------------------------------------
 *   Fraktjakt API commodities XML
 *  ---------------------------------------------------
 */

function commodities($items) {

    $xml_sub= '  <commodities>' . "\r\n";
    foreach ($items as $product) {
    
  		//Check if the product is  virtual. If so, then skip it.
    	$is_virtual = get_post_meta( $product['product_id'], '_virtual', true );
			if ( $is_virtual == 'yes' ) {
				continue;
			}
      $only_virtual = 'no';  
      $product_id = $product['product_id'];
			$product_instance = wc_get_product($product_id);
				
			// If it's a product variation, get the product_data from the variation field instead.
			$variable_product = new WC_Product_Variation( $product['variation_id'] );
			if ( preg_match( '/^{"id":0,".*/', $variable_product ) ) {
        	$product_data = new WC_Product( $product['product_id'] );
			}
			else {
				$product_data = $variable_product;
			}

			$description = get_post_meta( $product_data->get_id(), 'customs_description', true); 
			
			if (empty($description)) {
				$description = $product_data->get_attribute('customs_description');
			}

			if (empty($description)) {
				$terms1 = wc_get_product_terms( $product_id, 'product_brand', array('orderby' => 'term_id', 'order' => 'ASC', 'fields' => 'names') );
				$terms2 = wc_get_product_terms( $product_id, 'pa_brands', array('orderby' => 'term_id', 'order' => 'ASC', 'fields' => 'names')  );
				$terms3 = wc_get_product_terms( $product_id, 'pa_colors', array('orderby' => 'term_id', 'order' => 'ASC', 'fields' => 'names')  );
				$terms4 = wc_get_product_terms( $product_id, 'product_cat', array('orderby' => 'term_id', 'order' => 'ASC', 'fields' => 'names')  );
				$terms5 = wc_get_product_terms( $product_id, 'product_tag', array('orderby' => 'term_id', 'order' => 'ASC', 'fields' => 'names')  );
				$terms = array_merge($terms1, $terms2, $terms3, $terms4, $terms5); 
				$description = implode(", ", $terms);
			}
						
	    if (empty($description)) {
	    	$regular_product_description = $product_instance->get_description();
				$short_product_description = $product_instance->get_short_description();
				$description = ($regular_product_description == '') ? $short_product_description : $regular_product_description;
				$description = ($description == '') ? $product['name'] : $description;
			}									   	

      $description = preg_replace('/[^A-ZÅÄÖa-zåäö0-9\-\,\ ]/', '', $description );

			$hscode = get_post_meta( $product_data->get_id(), 'hscode', true); 
			if (empty($hscode)) {
				$hscode = get_post_meta( $product_data->get_id(), 'taric', true); 
			}
			if (empty($hscode)) {
				$hscode = $product_data->get_attribute('hscode');
				}
			if (empty($hscode)) {
				$hscode = $product_data->get_attribute('taric');
				}
				
			$country = get_post_meta( $product_data->get_id(), 'country', true); 
			if (empty($country)) {
				$country = get_post_meta( $product_data->get_id(), 'Country', true); 
				}
			if (empty($country)) {
				$country = $product_data->get_attribute('country');
				}
			if (empty($country)) {
				$country = $product_data->get_attribute('Country');
				}

	    $fraktjakt_shipping_method_settings = get_option( 'woocommerce_fraktjakt_shipping_method_settings' );

      $xml_sub.= '    <commodity>' . "\r\n";
      $xml_sub.= '      <name>'. str_replace(array("\r","\n"), "", strip_tags($product_data->get_name()) ) .'</name>' . "\r\n";
      $xml_sub.= '      <quantity>'. $product['quantity'] .'</quantity>' . "\r\n";
      $xml_sub.= '      <taric>'. $hscode .'</taric>' . "\r\n";
      $xml_sub.= '      <country_of_manufacture>'. $country .'</country_of_manufacture>' . "\r\n";
      $xml_sub.= '      <quantity_units>EA</quantity_units>' . "\r\n";
      $xml_sub.= '      <description>'. substr(strip_tags($description),0,80) .'</description>' . "\r\n";
      $xml_sub.= '      <article_number>'. $product_data->get_sku() .'</article_number>' . "\r\n";
      $xml_sub.= '      <unit_price>'.  wc_get_price_to_display($product_data) .'</unit_price>' . "\r\n";
		  $xml_sub.= '      <currency>' . (empty($fraktjakt_shipping_method_settings['currency_conversion']) ? 'SEK' : ( $fraktjakt_shipping_method_settings['currency_conversion'] == 'SEK' ? "SEK" : ( $fraktjakt_shipping_method_settings['currency_conversion'] == 'EUR' ? "EUR" : get_woocommerce_currency() ) )) . '</currency>' . "\r\n";

      $xml_sub.= '      <weight>'. (wc_get_weight( $product_data->get_weight(), 'kg' )* $product['quantity']) .'</weight>' . "\r\n";
			$xml_sub.= '      <length>'.wc_get_dimension( $product_data->get_length(), 'cm' ).'</length>'."\r\n";
			$xml_sub.= '      <width>'.wc_get_dimension( $product_data->get_width(), 'cm' ).'</width>'."\r\n";
			$xml_sub.= '      <height>'.wc_get_dimension( $product_data->get_height(), 'cm' ).'</height>'."\r\n";

      $xml_sub.= '    </commodity>' . "\r\n";            
    }
    $xml_sub.= '  </commodities>' . "\r\n";
		return $xml_sub;
}

/** ----------------------------------------------------
 *   Save Fraktjakt data to WooCommerce order meta data
 *  ----------------------------------------------------
 */

function update_fraktjakt_meta($array, $order) {
	  if(is_array($array)) {
        if (isset($array['result']['shipment_id'] )) {
	        $fraktjakt_shipment_id = $array['result']['shipment_id'];
	        update_post_meta($order->get_id(), 'fraktjakt_shipment_id', $fraktjakt_shipment_id);
	      }
        if (isset($array['result']['access_code'] )) {
	        $fraktjakt_access_code = $array['result']['access_code'];
	        update_post_meta($order->get_id(), 'fraktjakt_access_code', $fraktjakt_access_code);
	      }
        if (isset($array['result']['access_link'] )) {
	 	      $fraktjakt_access_link = $array['result']['access_link'];
	        update_post_meta($order->get_id(), 'fraktjakt_access_link', $fraktjakt_access_link);
	      }
        if (isset($array['result']['tracking_link'] )) {
	 	      $fraktjakt_tracking_link = $array['result']['tracking_link'];
	        update_post_meta($order->get_id(), 'fraktjakt_tracking_link', $fraktjakt_tracking_link);
	      }
        if (isset($array['result']['tracking_code'] )) {
	 	      $fraktjakt_tracking_code = $array['result']['tracking_code'];
	        update_post_meta($order->get_id(), 'fraktjakt_tracking_code', $fraktjakt_tracking_code);
	      }
        if (isset($array['result']['agent_selection_link'] )) {
	 	      $fraktjakt_agent_selection_link = $array['result']['agent_selection_link'];
  	      update_post_meta($order->get_id(), 'fraktjakt_agent_selection_link', $fraktjakt_agent_selection_link);
  	      setcookie('fraktjakt_agent_selection_link', $fraktjakt_agent_selection_link, time() + (600), "/");
  	    } else {
  	      setcookie('fraktjakt_agent_selection_link', '', time() + (600), "/");
	      }
        if (isset($array['result']['agent_link'] )) {
	 	      $fraktjakt_agent_link = $array['result']['agent_link'];
  	      update_post_meta($order->get_id(), 'fraktjakt_agent_link', $fraktjakt_agent_link);
  	      setcookie('agent_link', $fraktjakt_agent_link, time() + (600), "/");
  	    } else {
  	      setcookie('agent_link', '', time() + (600), "/");
	      }
        if (isset($array['result']['agent_info'] )) {
	 	      $agent_info = $array['result']['agent_info'];
  	      update_post_meta($order->get_id(), 'agent_info', $agent_info);
  	      setcookie('agent_info', $agent_info, time() + (600), "/");
  	    } else {
  	      setcookie('agent_info', '', time() + (600), "/");
	      }
     }
	  return;
}

/** ---------------------------------------------------
 *   Fraktjakt API address to XML
 *  ---------------------------------------------------
 */

function address_to($order) {

    $shipping_address_1 = get_post_meta( $order->get_id(), '_shipping_address_1', true );
    $shipping_address_2 = get_post_meta( $order->get_id(), '_shipping_address_2', true );
    $shipping_city = get_post_meta( $order->get_id(), '_shipping_city', true );
    $shipping_state = get_post_meta( $order->get_id(), '_shipping_state', true );
    $shipping_postcode = get_post_meta( $order->get_id(), '_shipping_postcode', true );
    $shipping_country = get_post_meta( $order->get_id(), '_shipping_country', true );    
    $shipping_company = get_post_meta( $order->get_id(), '_shipping_company', true );

    $residential = ($shipping_company == '') ? '1' : '0'; 

    $xml_sub = '  <address_to>'."\r\n";
    $xml_sub.= '    <street_address_1>'.$shipping_address_1.'</street_address_1>'."\r\n";
    $xml_sub.= '    <street_address_2>'.$shipping_address_2.'</street_address_2>'."\r\n";
    $xml_sub.= '    <postal_code>'.$shipping_postcode.'</postal_code>'."\r\n";
    $xml_sub.= '    <city_name>'.$shipping_city.'</city_name>'."\r\n";
    $xml_sub.= '    <residential>'.$residential.'</residential>'."\r\n";
    $xml_sub.= '    <country_subdivision_code>'.$shipping_state.'</country_subdivision_code>'."\r\n";
    $xml_sub.= '    <country_code>'.$shipping_country.'</country_code>'."\r\n";
    $xml_sub.= '  </address_to>'."\r\n";   

    if ($shipping_country != 'SE' && $shipping_country != 'se' ) {
      $xml_sub.= '  <export_reason>SALE</export_reason>' . "\r\n";    
    }

		return $xml_sub;
}

/** ---------------------------------------------------
 *   Fraktjakt API reipient XML
 *  ---------------------------------------------------
 */

function recipient($order) {

    if (!is_user_logged_in()) {
        $billing_email = get_post_meta( $order->get_id(), '_billing_email',true );
        $billing_phone = get_post_meta( $order->get_id(), '_billing_phone',true );
        $shipping_first_name = get_post_meta( $order->get_id(), '_shipping_first_name',true );
        $shipping_last_name = get_post_meta( $order->get_id(), '_shipping_last_name',true ); 
        $shipping_company = get_post_meta( $order->get_id(), '_shipping_company', true );
    }
    else {
    		$user_id = get_current_user_id();
        $billing_email = empty(get_post_meta( $order->get_id(), '_billing_email',true )) ? get_user_meta( $user_id, 'billing_email',true ) : get_post_meta( $order->get_id(), '_billing_email',true );
        $billing_phone = empty(get_post_meta( $order->get_id(), '_billing_phone',true )) ? get_user_meta( $user_id, 'billing_phone',true ) : get_post_meta( $order->get_id(), '_billing_phone',true );
        $shipping_first_name = get_post_meta( $order->get_id(), '_shipping_first_name',true );
        $shipping_last_name = get_post_meta( $order->get_id(), '_shipping_last_name',true ); 
        $shipping_company = get_post_meta( $order->get_id(), '_shipping_company', true );
    } 

		$xml_sub = '  <recipient>' . "\r\n";
    if (!empty($shipping_company)) {
        $xml_sub.= '    <company_to>'.$shipping_company.'</company_to>' . "\r\n";
    }    
    $xml_sub.= '    <name_to>'.$shipping_first_name.' '.$shipping_last_name.'</name_to>' . "\r\n";
    $xml_sub.= '    <telephone_to>'.$billing_phone.'</telephone_to>' . "\r\n";
    $xml_sub.= '    <email_to>'.$billing_email.'</email_to>' . "\r\n";
    $xml_sub.= '  </recipient>' . "\r\n";

		return $xml_sub;
}

/** ---------------------------------------------------
 *   Character encode XML to UTF8
 *  ---------------------------------------------------
 */

function character_encode($xml) {
		// Convert to UTF8 with fallback if mb_string isn't loaded
		if (extension_loaded('mbstring')) {
	    $httpPostParams = array(
		    'md5_checksum' => md5($xml),
	      'xml' => mb_convert_encoding($xml, 'UTF-8', mb_detect_encoding($xml))
	    );
    } else {
	    $httpPostParams = array(
		    'md5_checksum' => md5($xml),
	      'xml' => utf8_encode($xml)
	    );
		};
		return $httpPostParams;
}

/** ---------------------------------------------------
 *   Add tracking link & agent selection to emails
 *  ---------------------------------------------------
 */

add_filter( 'woocommerce_email_order_meta', 'custom_woocommerce_email_order_meta', 10, 3 );

function custom_woocommerce_email_order_meta( $order, $sent_to_admin, $plain_text ) {
    $tracking_link=get_post_meta( $order->get_id(), 'fraktjakt_tracking_link', true);  
	  //$agent_link=get_post_meta( $order_id, 'agent_link', true);  
		$fraktjakt_agent_link=get_post_meta( $order->get_id(), 'fraktjakt_agent_link', true);
		$agent_info=get_post_meta( $order->get_id(), 'agent_info', true);

		$options=get_option('woocommerce_fraktjakt_shipping_method_settings');
    if (isset($options['enable_tracking_in_email']) && ($options['enable_tracking_in_email'] == 'yes') && (!empty($tracking_link))){
			if ( $plain_text === false ) {
				echo "<div style='margin-bottom:40px'><h2>".__('Follow the shipment', 'fraktjakt-shipping-for-woocommerce')."</h2>
					<a href='".$tracking_link."' class='tracking_button button button-primary'>".__('Track your package', 'fraktjakt-shipping-for-woocommerce')."</a>\n</div>\r\n";
			} else {
				echo __('Follow the shipment', 'fraktjakt-shipping-for-woocommerce').'\n
				'.__('Track your package', 'fraktjakt-shipping-for-woocommerce').'\n
				'.$tracking_link.'\n\n';	
			}
    };
  	if (isset($options['enable_agent_email_selection']) && ($options['enable_agent_email_selection'] == 'yes') && (!empty($fraktjakt_agent_link))){

			if (empty($agent_info)) {
				$agent_info = __('Select shipping agent', 'fraktjakt-shipping-for-woocommerce');
			}

			if ( $plain_text === false ) {
				echo "<div style='margin-bottom:40px'><h2>".__('Shipping agent', 'fraktjakt-shipping-for-woocommerce')."</h2>
					<a href='".$fraktjakt_agent_link."' class='agent_button button'>".$agent_info."</a>\n<br />
					".__('If you need to change the selected shipping agent, then please do so before the shipment is created.', 'fraktjakt-shipping-for-woocommerce')."\n</div>\r\n";
			} else {
				echo __('Shipping agent', 'fraktjakt-shipping-for-woocommerce').'\n
					'.$agent_info.'\n
					'.$fraktjakt_agent_link.'\n
					'.__('If you need to change the selected shipping agent, then please do so before the shipment is created.', 'fraktjakt-shipping-for-woocommerce')."\n\n";	
			}
    };
    return;
}

/** ---------------------------------------------------
 *   Add agent selection to checkout
 *  ---------------------------------------------------
 */

add_action( 'woocommerce_review_order_after_shipping', 'agent_selection_link');

function agent_selection_link() {
	if (!empty($_COOKIE["fraktjakt_agent_selection_link"])) {
	  $fraktjakt_agent_selection_link = $_COOKIE["fraktjakt_agent_selection_link"].'&orig='.wc_get_checkout_url(); 
	};
	if (!empty($_COOKIE["agent_link"])) {
	  $fraktjakt_agent_selection_link = $_COOKIE["agent_link"].'&orig='.wc_get_checkout_url();
	};

	$options=get_option('woocommerce_fraktjakt_shipping_method_settings');
  if (isset($options['enable_agent_checkout_selection']) && ($options['enable_agent_checkout_selection'] == 'yes') && ($options['enable_frontend'] == 'yes') && (!empty($fraktjakt_agent_selection_link))){
	  echo '<tr><td></td><td><p><a href="'.$fraktjakt_agent_selection_link.'" target="_top">'.__('Change shipping agent', 'fraktjakt-shipping-for-woocommerce').'</a></p></td></tr>';
	};
}

load_plugin_textdomain('fraktjakt-shipping-for-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/languages');        

?>