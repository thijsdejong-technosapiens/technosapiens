<?php

/**
 * @source https://wordpress.stackexchange.com/questions/76465/initialize-wordpress-environment-to-use-in-command-line-script
 * @return bool|string|null
 */
function find_wordpress_base_path()
{
    $dir = dirname(__FILE__);
    do {
        if (file_exists($dir . "/wp-config.php")) return $dir;
    } while ($dir = realpath("$dir/.."));
    return null;
}

define('BASE_PATH', find_wordpress_base_path() . "/");
define('WP_USE_THEMES', false);
global $wp, $wp_query, $wp_the_query, $wp_rewrite, $wp_did_header;
require(BASE_PATH . 'wp-load.php');