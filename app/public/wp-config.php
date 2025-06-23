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
define( 'AUTH_KEY',          '#CxD-GxgXbh5(876mKxeIy4w|[36;fQ@8eRlC<FtS,L+;20,9J!~MEcMlZl~c@z,' );
define( 'SECURE_AUTH_KEY',   'Ulq;32MSf[J@LMME[6Eu6,aDXP.KtINjOwd+Fa9C+IGF3r2%IdJheRZ5jxByg6A@' );
define( 'LOGGED_IN_KEY',     'v1lbZ$1lxdVSc;mWHg*;(UM{42MH4>m3n1zTdz ::=q&nls=KJ88{j$::fj.dagB' );
define( 'NONCE_KEY',         '1o8*4XETxymn+3QAMz^rY*~8|}3#uL8%MV}!xsd#3J(i<bzgenTgnq!)WmNeS.~d' );
define( 'AUTH_SALT',         'W!s`j2Xt!WwZXSRdZy=V+EQW%Y=[$Qm4f(}a/UQ4+SdwBcCFFGy![/]BzJCKr)sB' );
define( 'SECURE_AUTH_SALT',  '>dwy{~;{[9uisC!=bWJ}3aX6;H0t5MFT`YF/Z7;BlL`WphwD[QaK1 6RXM>+h%(K' );
define( 'LOGGED_IN_SALT',    '!94NG;A)ErnIP`< <#j^kw$[[`GVF:^c[fj}WRjMkw&|`KSZrffA,*?22N&]*STW' );
define( 'NONCE_SALT',        ',S+SVr?w?V(Mw231]_UVr|,$4F9:(n.Ep}7d%,=pj<#Sqe-2!._b(y{%1vnT::;A' );
define( 'WP_CACHE_KEY_SALT', ' zrvpL4TM?GhZHL/cs:7qsqm&|z/4%a-YGLvsQoRt_KQ06w[8]l]=EinB[t,q+-]' );


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
