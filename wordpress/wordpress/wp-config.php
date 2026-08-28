<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'pos_db' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

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
define( 'AUTH_KEY',         'J2LK$z#3jEMr&1Ndyd)Tz=L16uV.`9hvvSTkC~~pn4ugp]7-XmX=ia`?utQ$NYNk' );
define( 'SECURE_AUTH_KEY',  'JB2Z-tW$c1@{=Xj;YM?;!p:Yvy*Thr=v~VZc+Z_GGZDw%V,RsiQk[*4[l4yuMmz8' );
define( 'LOGGED_IN_KEY',    'eei^no<O@pd9rv7O)*{u,G=W-n3K9sLSVt@Eh=d?ot:s^nRFV$1IWWgJ6w-HOVHY' );
define( 'NONCE_KEY',        'X&2Yt]4AWr$`Ib*VQfK;S+mvn:~<gQ2W@Q feg/B)Y@8HHg4YNQLCe0b(-C@5!5V' );
define( 'AUTH_SALT',        'D2gWJqU4rE*5e%_iBZ)=K6X6PWsVcnr((woqXD>hp1Mm^Q%o8A2QM@jSbpTjwrvK' );
define( 'SECURE_AUTH_SALT', 'nZbr=84f4``F7oa/a>q9XNnB?:8G6T(&p(O;Z4SRE/^wuCO6Y5i#8l^H;2sR}(_.' );
define( 'LOGGED_IN_SALT',   '3fgo=:tpP/&d0:)<?NjS?:gP(=hA)xQ<|E1$^h2=b`?YR_8KFu)m=9NFJP[KGRf!' );
define( 'NONCE_SALT',       'z:;%8MQq<@UenCLvBHs98I<F!^-tf7P|F:5m`Wu<yucxWi`5,[bIDkdfxlOr wjK' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'wp_';

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
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
