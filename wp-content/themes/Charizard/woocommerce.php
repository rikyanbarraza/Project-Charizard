<!-- woocommerce.php file in your theme to easily set the default layout of your store -->
<?php get_header(); ?>
<div id="main" class="row">
<div id="content" class="col-lg-12 col-sm-6 col-md-6 col-xs-12">
<?php woocommerce_content(); ?>
</div>
</div>
<?php get_footer(); ?>