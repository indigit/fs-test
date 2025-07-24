<?php

/**
 * Main plugin class
 *
 * @package FS\Likes
 * @since 1.0.0
 */

namespace FS\Likes;

use FS\Likes\Theme\Main_Page;
use FS\Likes\Admin\Likes_Page;

defined( 'ABSPATH' ) || exit;

/**
 * Main plugin class
 */
class Main {

    /**
     * Construct
     */
    public function __construct() {
        // Initialize DB singleton early to register hooks
        DB::get_instance();

        new Main_Page();
        new Like();
        new Likes_Page();
    }

    /**
     * Plugin activation
     *
     * @return void
     */
    public function activation(): void {
        DB::get_instance()->create_table();
        update_option( 'edit_likes_per_page', 5, false );
    }
}
