<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <?php wp_head(); ?>
</head>

<body>

    <header>
        <div>
            <div class="center">
                <a class="center homeLink" style="align-items: unset; align-items:flex-start;" href="<?php $permalink = get_permalink(13); ?>">
                    <h3 class="text center">Bara Kläder</h3>
                </a>
            </div>
            <div>
            
            </div>
            <div id="cart_container">
                <div class="cart_container_border">
                    <a href="<?php echo wc_get_cart_url(); ?>">
                        <i class="fas fa-shopping-basket"></i>
                    </a>
                    <a class="cart-customlocation" href="
                    <?php echo wc_get_cart_url(); ?>"
                     title="<?php _e( 'View your shopping cart' ); ?>">
                     <?php echo sprintf ( _n( '%d item', '%d items', 
                     WC()->cart->get_cart_contents_count() ), 
                     WC()->cart->get_cart_contents_count() ); ?>
                      - <?php echo WC()->cart->get_cart_total(); ?></a>
                    </a>
                </div>
            </div>
            <?php wp_nav_menu(array('theme_location' => 'header-menu')) ?>
        </div>
        
    </header>