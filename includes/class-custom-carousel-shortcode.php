<?php

/**
 * Register and handle the shortcode.
 */
class Custom_Carousel_Shortcode {

    /**
     * Constructor.
     */
    public function __construct() {
        add_shortcode('custom_carousel', array($this, 'render_shortcode'));
    }

    /**
     * Render the shortcode.
     *
     * @param array $atts Shortcode attributes.
     * @return string
     */
    public function render_shortcode($atts) {
        $atts = shortcode_atts(
            array(
                'id' => 0,
            ),
            $atts,
            'custom_carousel'
        );

        $carousel_id = absint($atts['id']);

        if (empty($carousel_id)) {
            return '';
        }

        $carousel = get_post($carousel_id);

        if (!$carousel || $carousel->post_type !== 'carousel') {
            return '';
        }

        $images = get_post_meta($carousel_id, '_carousel_images', true);

        if (empty($images)) {
            return '';
        }

        $settings = get_post_meta($carousel_id, '_carousel_settings', true);

        if (empty($settings)) {
            $settings = array(
                'autoplay'             => 'false',
                'autoplay_delay'       => 3000,
                'navigation'           => 'true',
                'pagination'           => 'true',
                'loop'                 => 'true',
                'effect'               => 'fade',
                'speed'                => 500,
                'slides_per_view'      => 1,
                'space_between'        => 10,
                'touch_enabled'        => 'false',
                'desktop_touch_only'   => 'false',
                'mobile_touch_only'    => 'true',
                'lightbox'             => 'true',
                'hide_arrows_mobile'   => 'true',
                'lightbox_pagination'  => 'true',
                'hide_lightbox_arrows_mobile' => 'true',
                'lightbox_touch'       => 'true'
            );
        }

        // Enqueue required scripts and styles
        wp_enqueue_style('custom-carousel-css');
        wp_enqueue_script('custom-carousel-js');

        // Generate a unique id for this carousel instance
        $carousel_instance_id = 'swiper-carousel-' . $carousel_id . '-' . uniqid();

        // Pass settings to JavaScript
        $carousel_data = array(
            'id'               => $carousel_instance_id,
            'autoplay'         => $settings['autoplay'] === 'true',
            'autoplayDelay'    => intval($settings['autoplay_delay']),
            'navigation'       => $settings['navigation'] === 'true',
            'pagination'       => $settings['pagination'] === 'true',
            'loop'             => $settings['loop'] === 'true',
            'effect'           => sanitize_text_field($settings['effect']),
            'speed'            => intval($settings['speed']),
            'slidesPerView'    => intval($settings['slides_per_view']),
            'spaceBetween'     => intval($settings['space_between']),
            'touchEnabled'     => $settings['touch_enabled'] === 'true',
            'desktopTouchOnly' => $settings['desktop_touch_only'] === 'true',
            'mobileTouchOnly'  => $settings['mobile_touch_only'] === 'true',
            'lightbox'         => $settings['lightbox'] === 'true',
            'hideArrowsMobile' => isset($settings['hide_arrows_mobile']) ? $settings['hide_arrows_mobile'] === 'true' : true,
            'lightboxPagination'       => $settings['lightbox_pagination'] === 'true',
            'hideLightboxArrowsMobile' => $settings['hide_lightbox_arrows_mobile'] === 'true',
            'lightboxTouch'    => isset($settings['lightbox_touch']) ? $settings['lightbox_touch'] === 'true' : true,
            'breakpoints'      => array(
                // when window width is >= 320px
                320 => array(
                    'slidesPerView' => 1,
                    'spaceBetween' => 10
                ),
                // when window width is >= 640px
                640 => array(
                    'slidesPerView' => 1,
                    'spaceBetween' => 20
                ),
                // when window width is >= 992px
                992 => array(
                    'slidesPerView' => min(3, intval($settings['slides_per_view'])),
                    'spaceBetween' => intval($settings['space_between'])
                )
            )
        );

        wp_localize_script('custom-carousel-js', 'carouselData_' . str_replace('-', '_', $carousel_instance_id), $carousel_data);

        // Start output buffering
        ob_start();
        ?>
        <div class="swiper-carousel-container">
            <!-- Swiper -->
            <div id="<?php echo esc_attr($carousel_instance_id); ?>" class="swiper custom-carousel">
                <div class="swiper-wrapper">
                    <?php foreach ($images as $attachment_id) :
                        // Get image details
                        $image_src = wp_get_attachment_image_src($attachment_id, 'large');
                        $image_full = wp_get_attachment_image_src($attachment_id, 'full');
                        $image_alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
                    ?>
                        <div class="swiper-slide">
                            <?php
                            // Output image with data-full-size attribute for lightbox
                            echo wp_get_attachment_image(
                                $attachment_id,
                                'large',
                                false,
                                array(
                                    'class' => 'carousel-image',
                                    'data-full-size' => $image_full[0]
                                )
                            );
                            ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($settings['pagination'] === 'true') : ?>
                <!-- Pagination -->
                <div class="swiper-pagination"></div>
                <?php endif; ?>

                <?php if ($settings['navigation'] === 'true') : ?>
                <!-- Navigation buttons -->
                <div class="swiper-button-next" aria-label="Next slide"></div>
                <div class="swiper-button-prev" aria-label="Previous slide"></div>
                <?php endif; ?>
            </div>

            <?php if ($settings['lightbox'] === 'true') : ?>
            <!-- Lightbox container -->
            <div class="carousel-lightbox-overlay" style="display: none;">
                <div class="carousel-lightbox-close">&times;</div>
                <div class="carousel-lightbox-content">
                    <img src="" alt="" class="carousel-lightbox-image">
                </div>
                <div class="carousel-lightbox-prev">&lt;</div>
                <div class="carousel-lightbox-next">&gt;</div>
            </div>
            <?php endif; ?>
        </div>
        <?php
        // Return the buffered content
        return ob_get_clean();
    }
}
