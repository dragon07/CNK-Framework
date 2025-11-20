<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<?php
/**
 * Template Item List
 * Hiển thị bài viết dạng danh sách Bootstrap List Group
 */
?>

<a href="<?php echo esc_url( get_permalink( $post ) ); ?>" class="list-group-item list-group-item-action d-flex align-items-center gap-3">
    <?php if ( has_post_thumbnail( $post ) ) : ?>
        <?php echo get_the_post_thumbnail( $post->ID, 'thumbnail', [ 'class' => 'rounded flex-shrink-0' ] ); ?>
    <?php endif; ?>

    <div class="flex-grow-1">
        <h6 class="mb-1 text-dark"><?php echo esc_html( get_the_title( $post ) ); ?></h6>
        <p class="mb-0 small text-muted"><?php echo esc_html( wp_trim_words( get_the_excerpt( $post ), 18, '...' ) ); ?></p>
    </div>
</a>
