<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="cnk-slider swiper">
  <div class="swiper-wrapper">
    <?php foreach ( (array) $slides as $slide ) : ?>
      <div class="swiper-slide"><h4><?php echo esc_html( isset($slide['title']) ? $slide['title'] : '' ); ?></h4></div>
    <?php endforeach; ?>
  </div>
</div>
