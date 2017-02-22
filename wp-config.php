<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'thecodesta_cherokee');


/** MySQL database username */
define('DB_USER', 'thecodesta_cherokee');


/** MySQL database password */
define('DB_PASSWORD', 'Fw5GwJyi');


/** MySQL hostname */
define('DB_HOST', 'localhost');


/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');


/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '*4N@oa]D)k`|fyB1$-h6Q3Q[`?<]KVs-C5/J}t5Pau9:%MZVn #bl^A=qN`ZN=!m');

define('SECURE_AUTH_KEY',  'mz)#|]yisESd;I&yI*x5N;oA{Brln`2|ZN>ygT^}cd$3].#(`=fR+r&~.~D``A@>');

define('LOGGED_IN_KEY',    'I:vH;Ynad3`/tSp9v_wl,@AjGl4%%nC0_C3lcqwL>3M<{NKj=zL&*!g9-AR,ikNu');

define('NONCE_KEY',        '+r1*]qV=J.Cg9bq6D.j#T1x+o@[xZ)}=^rR=:mmrekO|<#$ (-sI&:)I,1p0bJog');

define('AUTH_SALT',        'Ew.L?Npi6S9d|u[8fd{rT>/{Nf(z.59Mg`~ V}QC$ }a]>KTHIkf(0 j{rKbr?j=');

define('SECURE_AUTH_SALT', 'E@H^NW}cb7_N{BJh/c.|&`J;*eX2`CqN9y7`k_*j27NW<MF1amq8idpouV2hd?Wi');

define('LOGGED_IN_SALT',   '&!Jf2G|Uve~y[yTx9I?=3qHp2T&8*Hov7it/KYP#Gz&~,%|9F41[)G;>2i d)Vy0');

define('NONCE_SALT',       'KOqbCx0ndiZfQp;g,iTU6(n|d(YQF8Ji%5^e18}Nm{*Qim-O E%e*F*Ar+MUSOik');


/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'crk_';


/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);
define( 'DISALLOW_FILE_EDIT', true );
// define( 'DISALLOW_FILE_MODS', true );
 # Disable all automatic updates:
 // define( 'AUTOMATIC_UPDATER_DISABLED', true );
/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
