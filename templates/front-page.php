<?php
/**
 * The front page template file
 */

defined( 'ABSPATH' ) || exit;
?>

<!DOCTYPE html>

<html class="no-js" <?php language_attributes(); ?>>
    <head>

        <meta charset="<?php bloginfo( 'charset' ); ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1.0" >

        <link rel="profile" href="https://gmpg.org/xfn/11">

        <?php wp_head(); ?>

    </head>

    <body <?php body_class(); ?>>

        <?php wp_body_open(); ?>

        <!-- HEADER -->
        <?php
            do_action( 'get_header', null, [] );
        ?>
        <header id="site-header" class="site-header">
            <div class="header-inner">
                <div class="site-branding">
                    <h1 class="site-title"><?php bloginfo( 'name' ); ?></h1>
                </div>
                <nav id="site-navigation" class="main-navigation">
                    <?php
                    wp_nav_menu(
                        [
                            'theme_location' => 'primary',
                            'menu_class'     => 'primary-menu',
                        ]
                    );
                    ?>
                </nav>
            </div>
        </header>
        <!-- /HEADER -->

        <!-- MAIN CONTENT -->
        <main id="site-content" class="site-content">
            <div class="content-inner">
                <?php
                the_archive_title( '<h1 class="area-title">', '</h1>' );
                if ( have_posts() ) {
                    // Start the Loop.
                    while ( have_posts() ) {
                        the_post();
                        ?>
                            <article <?php post_class(); ?> id="post-<?php the_ID(); ?>">
                                <div class="article-inner">
                                    <?php if ( has_post_thumbnail() && ! post_password_required() ) : ?>
                                    <figure class="featured-media">
                                        <div class="featured-media-inner">
                                            <?php
                                            the_post_thumbnail();
                                            $caption = get_the_post_thumbnail_caption();
                                            if ( $caption ) {
                                                ?>
                                                <figcaption class="wp-caption-text"><?php echo wp_kses_post( $caption ); ?></figcaption>
                                                <?php
                                            }
                                            ?>
                                        </div>
                                    </figure>

                                    <div class="entry-content">
                                        <?php the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '">', '</a></h2>' ); ?>
                                        <?php endif; ?>
                                        <?php if ( has_excerpt() ) : ?>
                                        <div class="intro-text"><?php the_excerpt(); ?></div>
                                        <?php endif; ?>
                                        <div class="post-meta">
                                            <span class="author">
                                                <?php esc_html_e( 'Author:' ); ?>
                                                <a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>" class="author-link">
                                                    <?php echo esc_html( get_the_author() ); ?>
                                                </a>
                                            </span>
                                            <?php do_action( 'fs_likes_post_meta', $post, get_current_user_id() ); ?>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        <?php
                    }
                } else {
                    ?>
                    <div class="no-posts">
                        <p><?php esc_html_e( 'No posts found.', 'fs-likes' ); ?></p>
                    </div>
                    <?php
                }
                $posts_pagination = get_the_posts_pagination(
                    [
                        'mid_size'  => 1,
                        'prev_text' => '<span class="nav-prev">Назад</span>',
                        'next_text' => '<span class="nav-next">Вперед</span>',
                    ]
                );
                if ( $posts_pagination ) {
                    ?>
                    <div class="pagination-wrapper section-inner">
                        <?php echo $posts_pagination; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    </div>
                    <?php
                }
                ?>
            </div>
        </main>
        <!-- /MAIN CONTENT -->

        <!-- SIDEBAR -->
        <aside id="sidebar" class="sidebar">
            <div class="sidebar-inner">
                <?php get_sidebar(); ?>
            </div>
        </aside>
        <!-- /SIDEBAR -->

        <!-- FOOTER -->
        <footer id="site-footer" class="site-footer">
            <div class="footer-inner">
                <?php do_action( 'get_footer', null, [] ); ?>
            </div>
        </footer>
        <?php wp_footer(); ?>
        <!-- /FOOTER -->

    </body>
</html>
