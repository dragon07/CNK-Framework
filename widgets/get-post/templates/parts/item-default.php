<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<?php
/**
 * Template Item Default
 * Hiển thị từng bài viết dạng hàng đơn giản
 */
?>

<div class="col-12">
    <div class="d-flex border-bottom pb-3 mb-3">
        <?php if ( has_post_thumbnail( $post ) ) : ?>
            <div class="flex-shrink-0 me-3">
                <a href="<?php echo esc_url( get_permalink( $post ) ); ?>">
                    <?php echo get_the_post_thumbnail( $post->ID, 'thumbnail', [ 'class' => 'rounded' ] ); ?>
                </a>
            </div>
        <?php endif; ?>

        <div class="flex-grow-1">
            <h5 class="mb-1">
                <a href="<?php echo esc_url( get_permalink( $post ) ); ?>" class="text-dark text-decoration-none">
                    <?php echo esc_html( get_the_title( $post ) ); ?>
                </a>
            </h5>
            <p class="text-muted small mb-2">
                <?php echo esc_html( wp_trim_words( get_the_excerpt( $post ), 25, '...' ) ); ?>
            </p>
            <a href="<?php echo esc_url( get_permalink( $post ) ); ?>" class="btn btn-sm btn-outline-secondary">
                <?php esc_html_e( 'Đọc thêm', 'cnk' ); ?>
            </a>
        </div>
    </div>
</div>
