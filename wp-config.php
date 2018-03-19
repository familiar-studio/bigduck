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

define('SERVER_NAME', $_SERVER['SERVER_NAME']);

if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != "on") {
		define('SITE_URL', "http://".SERVER_NAME);
		define('SSL', false);

} else {
		define('SITE_URL', "https://".SERVER_NAME);
		define('SSL', true);
}

define('WP_HOME','https://bigducknyc.com');
//
define('WP_SITEURL','https://bigducknyc.com');

// define('WP_HOME', 'https://bigduck.test');
//
// define('WP_SITEURL', 'https://bigduck.test');


define('DB_NAME', 'bigduck_wordpress');

/** MySQL database username */
define('DB_USER', getenv('DB_USER') ? getenv('DB_USER') : 'root');

/** MySQL database password */
define('DB_PASSWORD', getenv('DB_PASS') ? getenv('DB_PASS') : 'root');

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
define('AUTH_KEY',         'JQ^kN<2*NGNrKa6$0h/Uw+9ytuU{$Lsr42F[sO3/&0-s1jmfPAsYq,x_3l Sl-<[');
define('SECURE_AUTH_KEY',  '#z%8M#{V*/$wA?N$2+Q7[:%&Le5X.mh%!o2-NGx8LV9U41Q<!7ynLwLMTiSIKcp ');
define('LOGGED_IN_KEY',    'Rl8dypoE|e_P_%OFWx@|C-q4~oWTh8!,+qAoZUQ{*x6pc+Hj^i}u7hllt~Z^49ZP');
define('NONCE_KEY',        'S`cA6{Q:oIN+W~[s|yC3hl0[w$=aYe$_{zC@x04}T5z)(v&%P9`.~s|cDQ&Q+qg4');
define('AUTH_SALT',        'V1^ lq{f;roj7~(rLUQ*dLjN&rnc{;]V.&#dc,*nw%z5SCxnaSMO$Y>CCEv*054S');
define('SECURE_AUTH_SALT', 'eQd=}*C=,VeBEE~vjeu/!4YRbT.sUOP .sjdU[c*ur0bP>K*Bu_}rn<U~Y~Msm@m');
define('LOGGED_IN_SALT',   '}9!]]`wFAkQ~Vc@som4_h*]gekAt7$$5OzGUvMQ07Cj:k85cy8su6A1l5.[O0v]j');
define('NONCE_SALT',       'hc4E(<h2z7aSxkOLKQg?SX=d~Sl=83~*Jh~@KT90DU52zhCF67t3B2)Kh&5Ik#eK');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

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
define('WP_DEBUG', true);
define( 'WP_DEBUG_LOG', true );


/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
