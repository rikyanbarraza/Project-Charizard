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
            <?php wp_nav_menu(array('theme_location' => 'header-menu')) ?>
        </div>
    </header>