<?php

/**
 * Database operations class
 *
 * @package FS\Likes
 * @since 1.0.0
 */

namespace FS\Likes;

defined( 'ABSPATH' ) || exit;

/**
 * Database operations class
 */
class DB {

    /**
     * Singleton instance
     *
     * @var DB|null
     */
    private static ?DB $instance = null;

    /**
     * Table name
     *
     * @var string
     */
    private $table_name;

    /**
     * WordPress database object
     *
     * @var \wpdb
     */
    private $wpdb;

    /**
     * Constructor
     */
    private function __construct() {
        global $wpdb;
        $this->wpdb       = $wpdb;
        $this->table_name = $this->wpdb->prefix . 'fs_likes';
    }

    /**
     * Get singleton instance
     *
     * @return DB
     */
    public static function get_instance(): DB {
        if ( null === self::$instance ) {
            self::$instance = new self();
            self::$instance->init();
        }
        return self::$instance;
    }

    /**
     * Initialize hooks and filters
     *
     * @return void
     */
    private function init(): void {
        add_filter( 'posts_clauses', [ $this, 'modify_posts_clauses' ], 10, 2 );
    }

    /**
     * Prevent cloning
     */
    private function __clone() {}

    /**
     * Prevent unserialization
     */
    public function __wakeup() {
        throw new \Exception( 'Cannot unserialize singleton' );
    }

    /**
     * Modify query clauses
     *
     * @param array $clauses Post clauses.
     * @param \WP_Query $query WP Query object.
     * @return array Modified post clauses.
     */
    public function modify_posts_clauses( array $clauses, \WP_Query $query ): array {
        if ( $query->is_main_query() && ( $query->is_front_page() || $query->is_home() ) ) {
            $join_likes = <<<SQL
                LEFT JOIN (
                    SELECT
                        post_id,
                        SUM(grade) as votes_total
                    FROM {$this->table_name}
                    WHERE grade IN (1, -1)
                    GROUP BY post_id
                ) likes ON {$this->wpdb->posts}.ID = likes.post_id
            SQL;

            if ( is_user_logged_in() ) {
                $join_voted = <<<SQL
                    LEFT JOIN {$this->table_name} likes2
                    ON (
                        {$this->wpdb->posts}.ID = likes2.post_id
                        AND likes2.user_id = %d
                    )
                SQL;
                $join_voted = $this->wpdb->prepare( $join_voted, get_current_user_id() ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
            } else {
                $user_ip    = $_SERVER['REMOTE_ADDR'];
                $join_voted = <<<SQL
                    LEFT JOIN {$this->table_name} likes2
                    ON (
                        {$this->wpdb->posts}.ID = likes2.post_id
                        AND likes2.user_id = 0
                        AND likes2.user_ip = %s
                    )
                SQL;
                $join_voted = $this->wpdb->prepare( $join_voted, $user_ip ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
            }

            $field = <<<SQL
                COALESCE(likes.votes_total, 0) AS votes_total,
                (likes2.post_id IS NOT NULL) AS user_voted,
                COALESCE(likes2.grade, 0) AS user_vote
            SQL;

            $clauses['join']   .= " {$join_likes} {$join_voted} ";
            $clauses['fields'] .= ", {$field}";
        }

        return $clauses;
    }

    /**
     * Create custom table on plugin activation
     *
     * @return void
     */
    public function create_table(): void {
        $charset_collate = $this->wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$this->table_name} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            post_id bigint(20) unsigned NOT NULL,
            user_id bigint(20) unsigned DEFAULT 0,
            user_ip varchar(45) NOT NULL,
            grade tinyint(1) NOT NULL DEFAULT 0,
            like_date datetime DEFAULT CURRENT_TIMESTAMP,
            unique_key varchar(100) GENERATED ALWAYS AS (
                CASE
                    WHEN user_id > 0 THEN CONCAT('user_', post_id, '_', user_id)
                    ELSE CONCAT('ip_', post_id, '_', user_ip)
                END
            ) STORED,
            PRIMARY KEY (id),
            UNIQUE KEY unique_vote (unique_key),
            KEY idx_post_id (post_id),
            KEY idx_user_id (user_id),
            KEY idx_like_date (like_date),
            KEY idx_user_ip (user_ip),
            KEY idx_grade (grade)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    /**
     * Vote function to handle inserting or updating likes
     *
     * @param array $args Vote arguments.
     * @return bool
     */
    public function vote( array $args ): bool {
        $post_id = $args['post_id'];
        $user_id = $args['user_id'];
        $user_ip = $args['user_ip'];
        $grade   = $args['grade'];

        $sql = <<<SQL
            INSERT INTO {$this->table_name}
                (post_id, user_id, user_ip, grade, like_date)
            VALUES
                (%d, %d, %s, %d, %s)
            ON DUPLICATE KEY UPDATE
                grade = VALUES(grade),
                like_date = VALUES(like_date),
                user_ip = VALUES(user_ip)
        SQL;

        $prepared_query = $this->wpdb->prepare(
            $sql, // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
            $post_id,
            $user_id,
            $user_ip,
            $grade,
            current_time( 'mysql' )
        );

        return (bool) $this->wpdb->query( $prepared_query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    }

    /**
     * Get user vote for a specific post
     *
     * @param int $post_id Post ID.
     * @param string $user_ip User IP address.
     * @param int $user_id User ID.
     * @return int User vote value (1, -1, or 0 if no vote).
     */
    public function get_user_vote( int $post_id, string $user_ip, int $user_id = 0 ): int {
        if ( $user_id > 0 ) {
            $sql            = <<<SQL
                SELECT grade
                FROM {$this->table_name}
                WHERE post_id = %d AND user_id = %d LIMIT 1
            SQL;
            $prepared_query = $this->wpdb->prepare( $sql, $post_id, $user_id ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        } else {
            $sql            = <<<SQL
                SELECT grade
                FROM {$this->table_name}
                WHERE post_id = %d AND user_id = 0 AND user_ip = %s LIMIT 1
            SQL;
            $prepared_query = $this->wpdb->prepare( $sql, $post_id, $user_ip ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        }

        return (int) $this->wpdb->get_var( $prepared_query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    }

    /**
     * Get total votes for a specific post
     *
     * @param int $post_id Post ID.
     * @return int Total votes for the post.
     */
    public function get_post_votes_total( int $post_id ): int {
        $sql = <<<SQL
            SELECT SUM(grade) FROM {$this->table_name}
            WHERE post_id = %d
        SQL;

        $prepared_query = $this->wpdb->prepare( $sql, $post_id ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

        return (int) $this->wpdb->get_var( $prepared_query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    }

    /**
     * Get likes data with pagination
     *
     * @param array $args Arguments for fetching likes.
     * @return array
     */
    public function get_likes( array $args ): array {
        $args = wp_parse_args(
            $args,
            [
                'page'     => 1,
                'per_page' => 5,
            ]
        );

        $sql = <<<SQL
            SELECT *
            FROM {$this->table_name}
            ORDER BY like_date DESC
            LIMIT %d, %d
        SQL;

        $prepared_query = $this->wpdb->prepare( $sql, ( $args['page'] - 1 ) * $args['per_page'], $args['per_page'] ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

        return $this->wpdb->get_results( $prepared_query ) ?? []; // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    }

    /**
     * Get the total number of records in the likes table
     *
     * @return int
     */
    public function record_count(): int {
        $sql = "SELECT COUNT(*) FROM {$this->table_name}";
        return (int) $this->wpdb->get_var( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    }
}
