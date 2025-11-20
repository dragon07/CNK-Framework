<?php if (! defined('ABSPATH')) exit; ?>
<?php
/**
 * Template Item Grid
 * Hiển thị từng bài viết trong lưới
 */

?>

<article class="col-12 col-sm-6 col-lg-4">
    <div class="card h-100 border-0 shadow-sm">
        <?php if (has_post_thumbnail()) : ?>
            <a href="<?php echo esc_url(get_permalink()); ?>" class="ratio ratio-16x9">
                <?php echo get_the_post_thumbnail(); ?>
            </a>
        <?php endif; ?>

        <div class="card-body d-flex flex-column">
            <h5 class="card-title mb-2">
                <a href="<?php echo esc_url(get_permalink()); ?>" class="text-decoration-none text-dark">
                    <?php echo esc_html(get_the_title()); ?>
                </a>
            </h5>
            <p class="card-text text-muted small mb-3">
                <?php echo esc_html(wp_trim_words(get_the_excerpt(), 20, '...')); ?>
            </p>

            <div class="mt-auto">
                <a href="<?php echo esc_url(get_permalink()); ?>" class="btn btn-outline-primary btn-sm w-100">
                    <?php esc_html_e('Xem chi tiết', 'cnk'); ?>
                </a>
            </div>
        </div>
    </div>
</article>
