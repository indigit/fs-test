<?php

/**
 * Main_Page class
 *
 * @package FS\Likes
 * @since 1.0.0
 */

namespace FS\Likes\Theme;

defined( 'ABSPATH' ) || exit;

/**
 * Main_Page class
 */
class Main_Page {

    /**
     * Construct
     */
    public function __construct() {
        add_filter( 'frontpage_template', [ $this, 'get_front_page_template' ], 20 );
        add_filter( 'home_template', [ $this, 'get_front_page_template' ], 20 );
        add_action( 'wp_enqueue_scripts', [ $this, 'manage_assets' ], 20 );
    }

    /**
     * Filters the list of template filenames that are searched for when retrieving a template to use.
     *
     * @param string $template Path to the template..
     * @return string
     */
    public function get_front_page_template( string $template ): string {
        $template_file = path_join( FS_LIKES_PLUGIN_TEMPLATES_PATH, 'front-page.php' );
        return $template_file;
    }

    /**
     * Manage front-end assets.
     *
     * @return void
     */
    public function manage_assets(): void {
        if ( is_front_page() || is_home() ) {
            wp_dequeue_style( 'twentytwenty-style' );
            wp_dequeue_style( 'twentytwenty-print-style' );
            wp_enqueue_style(
                'fs-likes-style',
                FS_LIKES_ASSETS_URL . '/main.css',
                [],
                FS_LIKES_ASSETS_HASH
            );
            wp_enqueue_script(
                'fs-likes-script',
                FS_LIKES_ASSETS_URL . '/main.js',
                [ 'wp-util', 'wp-dom-ready' ],
                FS_LIKES_ASSETS_HASH,
                true
            );
            wp_localize_script(
                'fs-likes-script',
                'fsData',
                [
                    'nonce'  => wp_create_nonce( 'fs_likes_nonce' ),
                    'action' => 'fs_likes',
                ]
            );
        }
    }
}
