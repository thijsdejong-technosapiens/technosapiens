<?php

//load composer packages
require_once(realpath(__DIR__ . '/../vendor/autoload.php'));

use Dotenv\Dotenv;

//load .env file
$dotenv = Dotenv::createUnsafeImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

$isDev = getenv('DEV') === 'true';

define('WP_CACHE', getenv('CACHE') === 'true');

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', getenv('DB_NAME'));

/** MySQL database username */
define('DB_USER', getenv('DB_USER'));

/** MySQL database password */
define('DB_PASSWORD', getenv('DB_PASSWORD'));

/** MySQL hostname */
define('DB_HOST', getenv('DB_HOST'));

/** Database charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The database collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

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
define('AUTH_KEY', getenv('AUTH_KEY'));
define('SECURE_AUTH_KEY', getenv('SECURE_AUTH_KEY'));
define('LOGGED_IN_KEY', getenv('LOGGED_IN_KEY'));
define('NONCE_KEY', getenv('NONCE_KEY'));
define('AUTH_SALT', getenv('AUTH_SALT'));
define('SECURE_AUTH_SALT', getenv('SECURE_AUTH_SALT'));
define('LOGGED_IN_SALT', getenv('LOGGED_IN_SALT'));
define('NONCE_SALT', getenv('NONCE_SALT'));

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = getenv('DB_PREFIX');

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

@ini_set('log_errors', $isDev ? 'On' : 'Off');
@ini_set('display_errors', 'Off');
define('WP_DEBUG', $isDev ? true : false);
define('WP_DEBUG_LOG', $isDev ? true : false);
define('WP_DEBUG_DISPLAY', false);

/** Force logins and admin sessions to use SSL https */
define('FORCE_SSL_ADMIN', true);

/** Define WP environment **/
define('WP_ENVIRONMENT_TYPE', $isDev ? 'development' : 'production');

/** Prevent editing by Admin -> Appearance -> Editor **/
define('DISALLOW_FILE_EDIT', true);

/** Prevent WP Schedule System **/
define('DISABLE_WP_CRON', false);

/** Prevent concatenate scripts in the admin **/
define('CONCATENATE_SCRIPTS', false);

/** Disable auto-update **/
define('AUTOMATIC_UPDATER_DISABLED', true);
define('WP_AUTO_UPDATE_CORE', false);

/** Set default theme **/
define('WP_DEFAULT_THEME', getenv('THEME'));

/** Set memory limits */
define('WP_MEMORY_LIMIT', '1024M');
define('WP_MAX_MEMORY_LIMIT', '1024M');

/** Set ACF Pro license key */
define('ACF_PRO_LICENSE', getenv('LICENSE_KEY_ACF'));

/** Set Gravity Forms license key */
define('GF_LICENSE_KEY', getenv('LICENSE_KEY_GF'));

/** Set Akismet license key */
define('WPCOM_API_KEY', getenv('LICENSE_KEY_AKISMET'));

/** Set WPMUDEV API key */
define('WPMUDEV_APIKEY', getenv('LICENSE_KEY_WPMUDEV'));

/** Set maximal number of post revisions to keep */
define('WP_POST_REVISIONS', 50);

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
