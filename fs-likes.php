<?php

/**
 * Plugin Name: Likes
 * Plugin URI: https://t.me/cbing
 * Description: A WordPress plugin for managing likes functionality.
 * Version: 1.0.0
 * Author: indigit
 * Author URI: https://t.me/cbing
 * Text Domain: fs-likes
 * Domain Path: /languages
 * Requires at least: 6.5
 * Tested up to: 6.8
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

declare(strict_types=1);

use FS\Likes\Main;

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/vendor/autoload.php';

// Define plugin constants
define( 'FS_LIKES_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'FS_LIKES_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'FS_LIKES_PLUGIN_FILE', __FILE__ );
define( 'FS_LIKES_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'FS_LIKES_PLUGIN_TEMPLATES_PATH', path_join( FS_LIKES_PLUGIN_PATH, 'templates' ) );
define( 'FS_LIKES_ASSETS_PATH', path_join( FS_LIKES_PLUGIN_PATH, 'assets' ) );
define( 'FS_LIKES_ASSETS_URL', FS_LIKES_PLUGIN_URL . 'assets' );

require FS_LIKES_ASSETS_PATH . '/assets_hash.php';

/**
 * Assets hash
 */
define( 'FS_LIKES_ASSETS_HASH', $assets_hash );

$fs_main = new Main();

/**
 * Plugin activation.
 */
register_activation_hook( __FILE__, [ $fs_main, 'activation' ] );
