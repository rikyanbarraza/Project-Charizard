<?php get_header(); ?>
<div id="ttr_main" class="row">
<div id="ttr_content" class="col-lg-8 col-sm-8 col-md-8 col-xs-12">
<div class="row">
<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
<div class="col-lg-6 col-sm-6 col-md-6 col-xs-12">
<h1><?php the_title(); ?></h1>
<p><?php the_content(__('(more...)')); ?></p>
</div>
<?php endwhile; else: ?>
<p><?php _e('Sorry, no posts matched your criteria.'); ?></p>
<?php endif; ?>
</div>
</div>
<?php get_sidebar(); ?>
</div>
<?php get_footer(); ?>
<!-- <main>
<h1>Hello World from index</h1>
<h1 class="rubrik">Nyheter (hook)</h1>
<section class="containerOneBigFourSmall">
    <div class="bigItem">
    </div>
    <div class="twoByTwoRow">
        <div class="itemOne"></div>
        <div class="itemTwo"></div>
    </div>
    <div class="twoByTwoRow">
        <div class="itemThree"></div>
        <div class="itemFour"></div>
    </div>
</section>
<section class="heroSection">

</section>
<h1 class="rubrik">Bästsäljare (hook)</h1>
<section class="containerFourSmallRow">
    <div class="itemOne"></div>
    <div class="itemTwo"></div>
    <div class="itemThree"></div>
    <div class="itemFour"></div>
</section>
<h1 class="rubrik">Populära varumärken (hook)</h1>
<section class="containerOneBigFourSmall">
    <div class="bigItem">
    </div>
    <div class="twoByTwoRow">
        <div class="itemOne"></div>
        <div class="itemTwo"></div>
    </div>
    <div class="twoByTwoRow">
        <div class="itemThree"></div>
        <div class="itemFour"></div>
    </div>
</section>
</main> -->