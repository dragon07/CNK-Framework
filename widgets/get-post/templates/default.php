<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<?php
/**
 * Template: Default
 * Hiển thị bài viết theo dạng danh sách cơ bản
 */
?>

<div class="cnk-getpost cnk-template-default container my-4">
    <?php if ( $query && $query->have_posts() ) : ?>
        <div id="<?php echo esc_attr($widget_id); ?>" class="row g-4 cnk-post-list">
            <?php while ( $query->have_posts() ) : $query->the_post(); ?>
                <?php 
                    $tpl_path    = CNK_WIDGETS . "{$widget_slug}/templates/parts/item-{$template}.php";
                    CNK_Template_Loader::load($tpl_path, [ 'post' => get_post() ]); 
                ?>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
    <?php else : ?>
        <div class="alert alert-warning text-center">
            <?php esc_html_e( 'Không có bài viết phù hợp.', 'cnk' ); ?>
        </div>
    <?php endif; ?>
</div>
