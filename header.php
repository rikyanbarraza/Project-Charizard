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
        <div class="burger-container center" id="burger-container">
            <i class="fas fa-bars"></i>
        </div>

        <div style="display: none; position:absolute" id="mobileMenu">
            <ul>
                <li>
                    <a href="/start">Start</a>
                </li>
                <li>
                    <a href="/ekologiska-klader">Ekologiska Kläder</a>
                </li>
                <li>
                    <a href="/tshirts-2">T-shirts</a>
                </li>
                <li>
                    <a href="/shorts">Shorts</a>
                </li>
                <li>
                    <a href="/tshirts">Accessoarer</a>
                </li>
                <li>
                    <a href="/shop">Alla Produkter</a>
                </li>
                <li>
                    <a href="/cart">Cart</a>
                </li>
            </ul>
        </div>

        <div>
            <div class="center">
                <a class="center homeLink" style="align-items: unset; align-items:flex-start;" href="https://www.webbstyrka.se/">
                    <h3 class="text center">Eko Kläder</h3>
                </a>
            </div>
            <?php wp_nav_menu(array('theme_location' => 'header-menu')) ?>
        </div>
    </header>