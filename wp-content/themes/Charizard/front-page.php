<?php get_header() ?>
<section id="heroSection">
    <?php
    if (have_posts()) {
        while (have_posts()) {
            the_post();
    ?>
            <img src="<?php echo get_template_directory_uri() . '/images/heroWithHat.jpg' ?>" alt="">
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
        <a href="https://www.webbstyrka.se/ekologiska-klader/">
            <h1>Köp Ekologiska Kläder</h1>
        </a>
    </button>
    <div class="section-fullScreen">
        <div class="itemContainerMedium">
            <img class="imgFit" src="<?php echo get_template_directory_uri() . '/images/tjejTshirt.jpg' ?>" alt="ekologiska kläder">
        </div>
        <section>
        </section>
        <div class="sideSection">
            <div class="itemContainerSmall">
                <img class="imgFit" src="<?php echo get_template_directory_uri() . '/images/viktaKlader.jpg' ?>" alt="ekologiska kläder">
            </div>
            <div class="itemContainerSmall">
                <img class="imgFit" src="<?php echo get_template_directory_uri() . '/images/snubbeMedSkegg.jpg' ?>" alt="ekologiska kläder">
            </div>
        </div>
    </div>
</section>
<?php get_footer(); ?>