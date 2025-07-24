<?php

/**
 * Like class
 *
 * @package FS\Likes
 * @since 1.0.0
 */

namespace FS\Likes;

defined( 'ABSPATH' ) || exit;

/**
 * Like class
 */
class Like {

    /**
     * Construct
     */
    public function __construct() {
        add_action( 'fs_likes_post_meta', [ $this, 'render_like_block' ], 10, 2 );
        add_action( 'wp_ajax_fs_likes', [ $this, 'handle_like_action' ] );
        add_action( 'wp_ajax_nopriv_fs_likes', [ $this, 'handle_like_action' ] );
    }
    /**
     * Render like block
     *
     * @param \WP_Post $post Post.
     * @param int $user_id User ID.
     */
    public function render_like_block( \WP_Post $post, int $user_id = 0 ): void {
        $votes_total    = (int) $post->votes_total;
        $user_vote      = (int) $post->user_vote;
        $upvote_state   = 1 === $user_vote ? ' disabled' : '';
        $downvote_state = -1 === $user_vote ? ' disabled' : '';
        $html           = <<<HTML
        <div class="fs-likes" data-post-id="{$post->ID}">
            <button class="fs-likes-button upvote" type="button" value="1"{$upvote_state}></button>
            <span class="fs-likes-count">$votes_total</span>
            <button class="fs-likes-button downvote" type="button" value="-1"{$downvote_state}></button>
        </div>
        HTML;

        echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    /**
     * Handle the like action
     */
    public function handle_like_action(): void {
        check_ajax_referer( 'fs_likes_nonce' );

        $post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
        if ( ! $post_id ) {
            wp_send_json_error( [ 'message' => 'Invalid post ID' ] );
            return;
        } elseif ( ! is_post_publicly_viewable( $post_id ) || 'post' !== get_post_type( $post_id ) ) {
            wp_send_json_error( [ 'message' => 'Post not found' ] );
            return;
        }

        $value = isset( $_POST['value'] ) ? intval( $_POST['value'] ) : 0;
        if ( ! in_array( $value, [ 1, -1 ], true ) ) {
            wp_send_json_error( [ 'message' => 'Invalid value' ] );
            return;
        }

        $user_ip = $_SERVER['REMOTE_ADDR'];
        $user_id = get_current_user_id();

        if ( ! $this->can_user_vote( $post_id, $user_ip, $value, $user_id ) ) {
            wp_send_json_error( [ 'message' => 'You have already voted on this post' ] );
            return;
        }

        $result = DB::get_instance()->vote(
            [
                'post_id' => $post_id,
                'user_id' => $user_id,
                'user_ip' => $user_ip,
                'grade'   => $value,
            ]
        );

        if ( $result ) {
            wp_send_json_success(
                [
                    'message'     => 'Like action handled successfully',
                    'post_id'     => $post_id,
                    'value'       => $value,
                    'votes_total' => DB::get_instance()->get_post_votes_total( $post_id ),
                ]
            );
        } else {
            wp_send_json_error( [ 'message' => 'Failed to handle like action' ] );
        }
    }

    /**
     * Check if the user can vote on a post
     *
     * @param int $post_id Post ID.
     * @param string $user_ip User IP address.
     * @param int $value Vote value (1 or -1).
     * @param int $user_id User ID.
     * @return bool True if the user can vote, false otherwise.
     */
    public function can_user_vote( int $post_id, string $user_ip, int $value, int $user_id = 0 ): bool {
        $user_vote = DB::get_instance()->get_user_vote( $post_id, $user_ip, $user_id );
        return $user_vote !== $value;
    }
}
