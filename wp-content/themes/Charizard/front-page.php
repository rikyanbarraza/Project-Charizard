<?php get_header() ?>

<section id="heroSection">
    <?php
    if (have_posts()) {
        while (have_posts()) {
            the_post();
    ?>
            <img src="<?php echo get_template_directory_uri() . './images/heroWithHat.jpg' ?>" alt="">
            <?php /*  the_post_thumbnail(); */ ?>
    <?php
        }
    }
    ?>


</section>

<div style="display: flex; justify-content: center">
    <h3>Våra Bästsäljare!</h3>
</div>

<section class="center">

    <?php

    if (have_posts()) {
        while (have_posts()) {
            the_post();
            the_content();
        }
    }

    ?>

</section>

<section class="center">
    <button class="heroButton btnHover">
        <h1>Köp Kläder i Ekologisk Bomull</h1>
    </button>
    <div class="section-fullScreen">
        <div class="itemContainerMedium">
            <img class="imgFit" src="<?php echo get_template_directory_uri() . './images/tjejTshirt.jpg' ?>" alt="">

        </div>

        <section>

        </section>

        <div class="sideSection">
            <div class="itemContainerSmall">
                <img class="imgFit" src="<?php echo get_template_directory_uri() . './images/viktaKlader.jpg' ?>" alt="">
            </div>
            <div class="itemContainerSmall">
                <img class="imgFit" src="<?php echo get_template_directory_uri() . './images/snubbeMedSkegg.jpg' ?>" alt="">
            </div>
        </div>
    </div>


</section>
<div class="cookie-banner" style="display: none">
	<p>By using our website, you agree to our <a href="<?php echo site_url('/refund_returns') ?>">cookie policy</a></p>
	<button class="close">&times;</button>
</div>

<!-- <div>

    php $attachmentID = 16;
    $imageSizeName = "thumbnail";
    $img = wp_get_attachment_image_src($attachmentID, $imageSizeName); ?>
    <img class="imgFit" src=" php echo $img[0]; ?>" />

</div> -->



<?php get_footer(); ?>