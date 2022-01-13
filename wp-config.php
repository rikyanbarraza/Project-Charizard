<?php
/**
 * Baskonfiguration för WordPress.
 *
 * Denna fil används av wp-config.php-genereringsskript under installationen.
 * Du behöver inte använda webbplatsens installationsrutin, utan kan kopiera
 * denna fil direkt till "wp-config.php" och fylla i alla värden.
 *
 * Denna fil innehåller följande konfigurationer:
 *
 * * Inställningar för MySQL
 * * Säkerhetsnycklar
 * * Tabellprefix för databas
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL-inställningar - MySQL-uppgifter får du från ditt webbhotell ** //
/** Namnet på databasen du vill använda för WordPress */
define( 'DB_NAME', 'charizardone' );

/** MySQL-databasens användarnamn */
define( 'DB_USER', 'admin_tim' );

/** MySQL-databasens lösenord */
define( 'DB_PASSWORD', 'Sk4SkQxgh3FILVukHyK@1LHD' );

/** MySQL-server */
define( 'DB_HOST', 'localhost' );

/** Teckenkodning för tabellerna i databasen. */
define( 'DB_CHARSET', 'utf8mb4' );

/** Kollationeringstyp för databasen. Ändra inte om du är osäker. */
define('DB_COLLATE', '');

/**#@+
 * Unika autentiseringsnycklar och salter.
 *
 * Ändra dessa till unika fraser!
 * Du kan generera nycklar med {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * Du kan när som helst ändra dessa nycklar för att göra aktiva cookies obrukbara, vilket tvingar alla användare att logga in på nytt.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '<qk,nj9*/LxU<!*FCUBpGvme%%`NTgr4KK3s-k<GG;}!o5|LaBSc*T=-aNAR{M1u' );
define( 'SECURE_AUTH_KEY',  'W(a&Ui.f&%Iuv.9CgxCl-i$|^eYCC[v+DRzU#aZ?aZ<bPD1[FkEa;3vsqeWZA_h6' );
define( 'LOGGED_IN_KEY',    '&fi:gLk5A@bG<Jl=V#=k0t_?6**.XyGQ`my).$[Gd!qu?^0G?i/Yns1PrB5VQ;/r' );
define( 'NONCE_KEY',        '{WD/,j[b:3K/jVbjMgjeEZN%F)1_vR}SMuR)GJ[sq70@a=m&XvLj6ebpo,_Iyus=' );
define( 'AUTH_SALT',        '|iG2r .{Z)F$@p,Qe1qD^/,wpfJ^&U$s#idy_D!q(|P3w?,~`+6*?s6h_uvhL%u/' );
define( 'SECURE_AUTH_SALT', ';@,s1_v(,9#r8j#&njXXBgv?7/J~i+WAW5gGN{+J3omDaGnswc(EctGlN:!eFvku' );
define( 'LOGGED_IN_SALT',   'Op.Zfx>#3S?&]UvZ/n+NlhMb5d,),z}M2un2k,JFH9PyUj[}xCW%P2I3hV,tJupA' );
define( 'NONCE_SALT',       'O|4vaKeMNv7gNid?/Yqw|FWrlisFIFJ8l$g,RckL~Hq,ttOsrMQ> bF#>cuc5IDQ' );

/**#@-*/

/**
 * Tabellprefix för WordPress-databasen.
 *
 * Du kan ha flera installationer i samma databas om du ger varje installation ett unikt
 * prefix. Använd endast siffror, bokstäver och understreck!
 */
$table_prefix = 'wp_';

/** 
 * För utvecklare: WordPress felsökningsläge. 
 * 
 * Ändra detta till true för att aktivera meddelanden under utveckling. 
 * Det rekommenderas att man som tilläggsskapare och temaskapare använder WP_DEBUG 
 * i sin utvecklingsmiljö. 
 *
 * För information om andra konstanter som kan användas för felsökning, 
 * se dokumentationen. 
 * 
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */ 
define('WP_DEBUG', false);
/* Lägg in eventuella anpassade värden mellan denna rad och raden med "sluta redigera här". */




/* Det var allt, sluta redigera här och börja publicera! */

/** Absolut sökväg till WordPress-katalogen. */
if ( !defined('ABSPATH') )
	define('ABSPATH', __DIR__ . '/');

/** Anger WordPress-värden och inkluderade filer. */
require_once(ABSPATH . 'wp-settings.php');