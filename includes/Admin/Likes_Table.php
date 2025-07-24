<?php

/**
 * Table class
 *
 * @package FS\Likes
 * @since 1.0.0
 */

namespace FS\Likes\Admin;

use FS\Likes\DB;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Table class
 */
class Likes_Table extends \WP_List_Table {

    /**
     * Construct
     */
    public function __construct() {
        parent::__construct(
            [
                'singular' => 'like',
                'plural'   => 'likes',
                'ajax'     => false,
            ]
        );
    }

    /**
     * Get columns for the table
     *
     * @return array
     */
    public function get_columns(): array {
        return [
            'cb'      => '<input type="checkbox" />',
            'post'    => __( 'Post' ),
            'user'    => __( 'User' ),
            'user_ip' => __( 'IP' ),
            'grade'   => '<span style="color:green">▲</span> / <span style="color:red">▼</span>',
            'date'    => __( 'Date' ),
        ];
    }

    /**
     * Format the checkbox column
     *
     * @param mixed $item The item data.
     * @return string Checkbox HTML.
     */
    protected function column_cb( mixed $item ): string {
        return '<input type="checkbox" name="likes[]" value="' . esc_attr( $item->id ) . '" />';
    }

    /**
     * Format the post column
     *
     * @param object $item The item data.
     * @return string Formatted post link.
     */
    protected function column_post( object $item ): string {
        return sprintf(
            '<a href="%s" target="_blank">%s</a>',
            esc_url( get_permalink( $item->post ) ),
            esc_html( get_the_title( $item->post ) )
        );
    }

    /**
     * Format the user column
     *
     * @param object $item The item data.
     * @return string Formatted user name.
     */
    protected function column_user( object $item ): string {
        return $item->user ? esc_html( get_the_author_meta( 'display_name', $item->user ) ) : __( 'Anonymous' );
    }

    /**
     * Format the user IP column
     *
     * @param object $item The item data.
     * @return string Formatted user IP.
     */
    protected function column_user_ip( object $item ): string {
        return esc_html( $item->user_ip );
    }

    /**
     * Format the grade column
     *
     * @param object $item The item data.
     * @return string Formatted grade.
     */
    protected function column_grade( object $item ): string {
        return '1' === $item->grade ? '<span style="color:green">▲</span>' : '<span style="color:red">▼</span>';
    }

    /**
     * Format the date column
     *
     * @param object $item The item data.
     * @return string Formatted date.
     */
    protected function column_date( object $item ): string {
        return date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $item->date ) );
    }

    /**
     * Get likes data
     *
     * @param array $args Arguments for fetching likes.
     * @return array
     */
    private function get_likes_data( array $args ): array {
        $likes_raw = DB::get_instance()->get_likes( $args );

        $likes = array_map(
            static function ( object $like ): object {
                return (object) [
                    'id'      => $like->id,
                    'post'    => $like->post_id,
                    'user'    => $like->user_id,
                    'user_ip' => $like->user_ip,
                    'grade'   => $like->grade,
                    'date'    => $like->like_date,
                ];
            },
            $likes_raw
        );

        return $likes;
    }

    /**
     * Prepare items for the table
     */
    public function prepare_items(): void {
        $page     = $this->get_pagenum();
        $per_page = $this->get_items_per_page( 'edit_likes_per_page', 5 );

        $this->_column_headers = [ $this->get_columns(), [], [] ];
        $this->items           = $this->get_likes_data(
            [
                'page'     => $page,
                'per_page' => $per_page,
            ]
        );

        $this->set_pagination_args(
            [
                'total_items' => DB::get_instance()->record_count(),
                'per_page'    => $per_page,
            ]
        );
    }
}
