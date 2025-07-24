<?php

/**
 * Admin page class
 *
 * @package FS\Likes
 * @since 1.0.0
 */

namespace FS\Likes\Admin;

use FS\Likes\Admin\Likes_Table;

defined( 'ABSPATH' ) || exit;

/**
 * Admin page class
 */
class Likes_Page {

    /**
     * Construct
     */
    public function __construct() {
        add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __( 'Likes', 'fs-likes' ),
            __( 'Likes', 'fs-likes' ),
            'manage_options',
            'fs-likes',
            [ $this, 'display_page' ],
            'dashicons-heart',
            30
        );
    }

    /**
     * Display admin page
     */
    public function display_page() {
        $likes_table = new Likes_Table();
        $likes_table->prepare_items();
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <div id="fs-likes-admin">
                <form method="post">
                    <?php
                    $likes_table->display();
                    ?>
                </form>
            </div>
        </div>
        <?php
    }
}
