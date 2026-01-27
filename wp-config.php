<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'local' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'root' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',          'vw!eW/jNHG_#:?Bj6ZAO-9#[nR6=XkhlNxZhJb4OVzmSFqkMgbg{C,.w=m1Vt)#x' );
define( 'SECURE_AUTH_KEY',   'oV q7V<w0LZy9`9Yre&O{HeN.ex]#IZcAkv,4cq)4W$+>o*8CP=XBpdHIxIkC5a3' );
define( 'LOGGED_IN_KEY',     'lPQV.?QKM}{$pW8i-UkYwdouSed$Hb8.gy?5 =Ro`g9}+_+HwU+.wN`gH-25^i9_' );
define( 'NONCE_KEY',         'UGd#iEbGoc+@j|wjsmT)]|6]^*B=`NbS4Q.CM1<mRp)tXsJ.A -@7sM&]PfY*.r7' );
define( 'AUTH_SALT',         'X&4U,a,HK-$/<$PUBr%*nX.S&|;y{HB~V%Fdf$y@#,f27F1hi)hvlU.i/(jF@/wH' );
define( 'SECURE_AUTH_SALT',  'n7.f9wyNO#&hZIQF`c[YhM)Sf 105s>x%Yt%Q!9o[qm4|Y_c@qf*KafDM|1e(kFk' );
define( 'LOGGED_IN_SALT',    'C9_gbp]U]~.>bbTWq6!!)M%(S>:xqNRej6/mQ$@.=wc`fz,j]CT+CVTSuRL{T^ +' );
define( 'NONCE_SALT',        'c_SaBPeVyu%9I}@ie]jiEK?^u%yGBc3tJwW63G^8avuZ(C/YfS|2C;=_[-FZB:sX' );
define( 'WP_CACHE_KEY_SALT', ' vtE^q%i0vL=t;xkr0Z8 q;zUFJCfz#7GM=Q.D>B4N2k4PXO+e anl=Mnx)l>axd' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */



/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

define( 'WP_ENVIRONMENT_TYPE', 'local' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
