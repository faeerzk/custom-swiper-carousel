<?php

/**
 * Register custom post type for carousels.
 */
class Custom_Carousel_Post_Type {

    /**
     * Constructor.
     */
    public function __construct() {
        // Register the custom post type
        add_action('init', array($this, 'register_post_type'));

        // Register meta boxes
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));

        // Save meta box data
        add_action('save_post', array($this, 'save_meta_boxes'), 10, 2);
    }

    /**
     * Register the 'carousel' post type.
     */
    public function register_post_type() {
        $labels = array(
            'name'               => _x('Carousels', 'post type general name', 'custom-carousel'),
            'singular_name'      => _x('Carousel', 'post type singular name', 'custom-carousel'),
            'menu_name'          => _x('Carousels', 'admin menu', 'custom-carousel'),
            'name_admin_bar'     => _x('Carousel', 'add new on admin bar', 'custom-carousel'),
            'add_new'            => _x('Add New', 'carousel', 'custom-carousel'),
            'add_new_item'       => __('Add New Carousel', 'custom-carousel'),
            'new_item'           => __('New Carousel', 'custom-carousel'),
            'edit_item'          => __('Edit Carousel', 'custom-carousel'),
            'view_item'          => __('View Carousel', 'custom-carousel'),
            'all_items'          => __('All Carousels', 'custom-carousel'),
            'search_items'       => __('Search Carousels', 'custom-carousel'),
            'parent_item_colon'  => __('Parent Carousels:', 'custom-carousel'),
            'not_found'          => __('No carousels found.', 'custom-carousel'),
            'not_found_in_trash' => __('No carousels found in Trash.', 'custom-carousel')
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array('slug' => 'carousel'),
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => 20,
            'menu_icon'          => 'dashicons-images-alt2',
            'supports'           => array('title')
        );

        register_post_type('carousel', $args);
    }

    /**
     * Add meta boxes for carousel settings.
     */
    public function add_meta_boxes() {
        add_meta_box(
            'carousel_images',
            __('Carousel Images', 'custom-carousel'),
            array($this, 'carousel_images_callback'),
            'carousel',
            'normal',
            'high'
        );

        add_meta_box(
            'carousel_settings',
            __('Carousel Settings', 'custom-carousel'),
            array($this, 'carousel_settings_callback'),
            'carousel',
            'side',
            'default'
        );
    }

    /**
     * Callback for carousel images meta box.
     *
     * @param WP_Post $post The post object.
     */
    public function carousel_images_callback($post) {
        wp_nonce_field('custom_carousel_images_meta_box', 'custom_carousel_images_meta_box_nonce');

        $images = get_post_meta($post->ID, '_carousel_images', true);
        ?>
        <div id="carousel-images-container">
            <p>
                <button type="button" class="button" id="carousel-add-images">
                    <?php _e('Add Images', 'custom-carousel'); ?>
                </button>
            </p>

            <ul id="carousel-images-list" class="carousel-images-list">
                <?php
                if (!empty($images)) {
                    foreach ($images as $attachment_id) {
                        $attachment = wp_get_attachment_image($attachment_id, 'thumbnail');
                        echo '<li data-attachment-id="' . esc_attr($attachment_id) . '">';
                        echo $attachment;
                        echo '<a href="#" class="carousel-remove-image"><span class="dashicons dashicons-no"></span></a>';
                        echo '<input type="hidden" name="carousel_images[]" value="' . esc_attr($attachment_id) . '">';
                        echo '</li>';
                    }
                }
                ?>
            </ul>
        </div>
        <script>
            jQuery(document).ready(function($) {
                var mediaUploader;

                $('#carousel-add-images').on('click', function(e) {
                    e.preventDefault();

                    if (mediaUploader) {
                        mediaUploader.open();
                        return;
                    }

                    mediaUploader = wp.media({
                        title: '<?php _e('Select Images', 'custom-carousel'); ?>',
                        button: {
                            text: '<?php _e('Add to carousel', 'custom-carousel'); ?>'
                        },
                        multiple: true
                    });

                    mediaUploader.on('select', function() {
                        var attachments = mediaUploader.state().get('selection').toJSON();

                        $.each(attachments, function(i, attachment) {
                            $('#carousel-images-list').append(
                                '<li data-attachment-id="' + attachment.id + '">' +
                                '<img src="' + attachment.sizes.thumbnail.url + '" width="150" height="150">' +
                                '<a href="#" class="carousel-remove-image"><span class="dashicons dashicons-no"></span></a>' +
                                '<input type="hidden" name="carousel_images[]" value="' + attachment.id + '">' +
                                '</li>'
                            );
                        });
                    });

                    mediaUploader.open();
                });

                $(document).on('click', '.carousel-remove-image', function(e) {
                    e.preventDefault();
                    $(this).parent().remove();
                });

                $('#carousel-images-list').sortable({
                    cursor: 'move'
                });
            });
        </script>
        <style>
            .carousel-images-list {
                margin: 0;
                padding: 0;
            }

            .carousel-images-list li {
                display: inline-block;
                margin: 5px;
                position: relative;
                cursor: move;
            }

            .carousel-remove-image {
                position: absolute;
                top: 0;
                right: 0;
                background: rgba(255, 255, 255, 0.7);
                color: #ff0000;
                display: block;
                width: 20px;
                height: 20px;
                text-align: center;
                line-height: 20px;
            }
        </style>
        <?php
    }

    /**
     * Callback for carousel settings meta box.
     *
     * @param WP_Post $post The post object.
     */
    public function carousel_settings_callback($post) {
        wp_nonce_field('custom_carousel_settings_meta_box', 'custom_carousel_settings_meta_box_nonce');

        $settings = get_post_meta($post->ID, '_carousel_settings', true);

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
        ?>
        <p>
            <label for="carousel_autoplay">
                <input type="checkbox" id="carousel_autoplay" name="carousel_settings[autoplay]" value="true" <?php checked($settings['autoplay'], 'true'); ?>>
                <?php _e('Autoplay', 'custom-carousel'); ?>
            </label>
        </p>

        <p>
            <label for="carousel_autoplay_delay"><?php _e('Autoplay Delay (ms)', 'custom-carousel'); ?></label>
            <input type="number" id="carousel_autoplay_delay" name="carousel_settings[autoplay_delay]" value="<?php echo esc_attr($settings['autoplay_delay']); ?>" min="1000" step="500">
        </p>

        <p>
            <label for="carousel_navigation">
                <input type="checkbox" id="carousel_navigation" name="carousel_settings[navigation]" value="true" <?php checked($settings['navigation'], 'true'); ?>>
                <?php _e('Show Navigation Arrows', 'custom-carousel'); ?>
            </label>
        </p>

        <p>
            <label for="carousel_pagination">
                <input type="checkbox" id="carousel_pagination" name="carousel_settings[pagination]" value="true" <?php checked($settings['pagination'], 'true'); ?>>
                <?php _e('Show Pagination Dots', 'custom-carousel'); ?>
            </label>
        </p>

        <p>
            <label for="carousel_loop">
                <input type="checkbox" id="carousel_loop" name="carousel_settings[loop]" value="true" <?php checked($settings['loop'], 'true'); ?>>
                <?php _e('Infinite Loop', 'custom-carousel'); ?>
            </label>
        </p>

    <!--    <p>
            <label for="carousel_touch_enabled">
                <input type="checkbox" id="carousel_touch_enabled" name="carousel_settings[touch_enabled]" value="true" <?php checked($settings['touch_enabled'], 'true'); ?>>
                <?php _e('Enable Touch/Swipe in Desktop', 'custom-carousel'); ?>
            </label>
        </p> -->

       <!-- <p>
            <label for="carousel_desktop_touch_only">
                <input type="checkbox" id="carousel_desktop_touch_only" name="carousel_settings[desktop_touch_only]" value="true" <?php checked($settings['desktop_touch_only'], 'true'); ?>>
                <?php _e('Desktop Touch Only', 'custom-carousel'); ?>
            </label>
        </p> -->

        <p>
            <label for="carousel_lightbox">
                <input type="checkbox" id="carousel_lightbox" name="carousel_settings[lightbox]" value="true" <?php checked($settings['lightbox'], 'true'); ?>>
                <?php _e('Enable Lightbox', 'custom-carousel'); ?>
            </label>
        </p>

        <p>
            <label for="carousel_lightbox_pagination">
                <input type="checkbox" id="carousel_lightbox_pagination" name="carousel_settings[lightbox_pagination]" value="true" <?php checked($settings['lightbox_pagination'] ?? 'true', 'true'); ?>>
                <?php _e('Show Pagination in Lightbox', 'custom-carousel') ?>
            </label>
        </p>

        <p>
            <label for="carousel_effect"><?php _e('Transition Effect', 'custom-carousel'); ?></label>
            <select id="carousel_effect" name="carousel_settings[effect]">
                <option value="slide" <?php selected($settings['effect'], 'slide'); ?>><?php _e('Slide', 'custom-carousel'); ?></option>
                <option value="fade" <?php selected($settings['effect'], 'fade'); ?>><?php _e('Fade', 'custom-carousel'); ?></option>
                <option value="cube" <?php selected($settings['effect'], 'cube'); ?>><?php _e('Cube', 'custom-carousel'); ?></option>
                <option value="coverflow" <?php selected($settings['effect'], 'coverflow'); ?>><?php _e('Coverflow', 'custom-carousel'); ?></option>
                <option value="flip" <?php selected($settings['effect'], 'flip'); ?>><?php _e('Flip', 'custom-carousel'); ?></option>
                <option value="cards" <?php selected($settings['effect'], 'cards'); ?>><?php _e('Cards', 'custom-carousel'); ?></option>
            </select>
        </p>

        <p>
            <label for="carousel_speed"><?php _e('Animation Speed (ms)', 'custom-carousel'); ?></label>
            <input type="number" id="carousel_speed" name="carousel_settings[speed]" value="<?php echo esc_attr($settings['speed']); ?>" min="100" step="100">
        </p>

        <p>
            <label for="carousel_slides_per_view"><?php _e('Slides Per View', 'custom-carousel'); ?></label>
            <input type="number" id="carousel_slides_per_view" name="carousel_settings[slides_per_view]" value="<?php echo esc_attr($settings['slides_per_view']); ?>" min="1" max="10">
        </p>

        <p>
            <label for="carousel_space_between"><?php _e('Space Between Slides (px)', 'custom-carousel'); ?></label>
            <input type="number" id="carousel_space_between" name="carousel_settings[space_between]" value="<?php echo esc_attr($settings['space_between']); ?>" min="0" max="100">
        </p>

        <h4 class="settings-group-title"><?php _e('Mobile Settings', 'custom-carousel'); ?></h4>
        <div class="settings-group">
            <p>
                <label for="carousel_hide_arrows_mobile">
                    <input type="checkbox" id="carousel_hide_arrows_mobile" name="carousel_settings[hide_arrows_mobile]" value="true" <?php checked($settings['hide_arrows_mobile'], 'true'); ?>>
                    <?php _e('Hide Navigation Arrows on Mobile Carousel', 'custom-carousel'); ?>
                </label>
            </p>

            <p>
            <label for="carousel_mobile_touch_only">
                <input type="checkbox" id="carousel_mobile_touch_only" name="carousel_settings[mobile_touch_only]" value="true" <?php checked($settings['mobile_touch_only'], 'true'); ?>>
                <?php _e('Mobile Touch Only', 'custom-carousel'); ?>
            </label>
            </p>

            <p>
            <label for="carousel_hide_lightbox_arrows_mobile">
                <input type="checkbox" id="carousel_hide_lightbox_arrows_mobile" name="carousel_settings[hide_lightbox_arrows_mobile]" value="true" <?php checked($settings['hide_lightbox_arrows_mobile'] ?? 'false', 'true'); ?>>
                <?php _e('Hide Navigation Arrows in Lightbox on Mobile', 'custom-carousel') ?>
            </label>
            </p>

            <p>
                <label for="carousel_lightbox_touch">
                    <input type="checkbox" id="carousel_lightbox_touch" name="carousel_settings[lightbox_touch]" value="true" <?php checked($settings['lightbox_touch'], 'true'); ?>>
                    <?php _e('Enable Touch Swipe in Lightbox', 'custom-carousel'); ?>
                </label>
            </p>
        </div>

        <p>
            <strong><?php _e('Shortcode:', 'custom-carousel'); ?></strong>
            <span class="shortcode-display">
                <code id="carousel-shortcode-<?php echo $post->ID; ?>">[custom_carousel id="<?php echo $post->ID; ?>"]</code>
                <button type="button" class="copy-shortcode button" data-clipboard-target="#carousel-shortcode-<?php echo $post->ID; ?>">
                    <span class="dashicons dashicons-clipboard"></span> <?php _e('Copy', 'custom-carousel'); ?>
                </button>
            </span>
            <script>
                jQuery(document).ready(function($) {
                    $('.copy-shortcode').on('click', function(e) {
                        e.preventDefault();
                        var shortcodeText = $($(this).data('clipboard-target')).text();

                        // Create a temporary textarea to copy from
                        var tempTextarea = $('<textarea>');
                        $('body').append(tempTextarea);
                        tempTextarea.val(shortcodeText).select();
                        document.execCommand('copy');
                        tempTextarea.remove();

                        // Visual feedback
                        var originalText = $(this).html();
                        $(this).html('<span class="dashicons dashicons-yes"></span> <?php _e('Copied!', 'custom-carousel'); ?>');
                        setTimeout(function() {
                            $('.copy-shortcode').html(originalText);
                        }, 2000);
                    });
                });
            </script>
        </p>

        <p>
            <strong><?php _e('Shortcode:', 'custom-carousel'); ?></strong>
            <code>[custom_carousel id="<?php echo $post->ID; ?>"]</code>
        </p>

        <?php
    }

    /**
     * Save meta box data.
     *
     * @param int     $post_id The post ID.
     * @param WP_Post $post    The post object.
     */
    public function save_meta_boxes($post_id, $post) {
        // Check if our nonce is set for images
        if (!isset($_POST['custom_carousel_images_meta_box_nonce'])) {
            return;
        }

        // Verify the nonce for images
        if (!wp_verify_nonce($_POST['custom_carousel_images_meta_box_nonce'], 'custom_carousel_images_meta_box')) {
            return;
        }

        // Check if our nonce is set for settings
        if (!isset($_POST['custom_carousel_settings_meta_box_nonce'])) {
            return;
        }

        // Verify the nonce for settings
        if (!wp_verify_nonce($_POST['custom_carousel_settings_meta_box_nonce'], 'custom_carousel_settings_meta_box')) {
            return;
        }

        // If this is an autosave, our form has not been submitted, so we don't want to do anything
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check the user's permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save carousel images
        if (isset($_POST['carousel_images'])) {
            $carousel_images = array_map('absint', $_POST['carousel_images']);
            update_post_meta($post_id, '_carousel_images', $carousel_images);
        } else {
            delete_post_meta($post_id, '_carousel_images');
        }

        // Save carousel settings
        if (isset($_POST['carousel_settings'])) {
            $carousel_settings = array(
                'autoplay'             => isset($_POST['carousel_settings']['autoplay']) ? 'true' : 'false',
                'autoplay_delay'       => absint($_POST['carousel_settings']['autoplay_delay']),
                'navigation'           => isset($_POST['carousel_settings']['navigation']) ? 'true' : 'false',
                'pagination'           => isset($_POST['carousel_settings']['pagination']) ? 'true' : 'false',
                'loop'                 => isset($_POST['carousel_settings']['loop']) ? 'true' : 'false',
                'effect'               => sanitize_text_field($_POST['carousel_settings']['effect']),
                'speed'                => absint($_POST['carousel_settings']['speed']),
                'slides_per_view'      => absint($_POST['carousel_settings']['slides_per_view']),
                'space_between'        => absint($_POST['carousel_settings']['space_between']),
                'touch_enabled'        => isset($_POST['carousel_settings']['touch_enabled']) ? 'true' : 'false',
                'desktop_touch_only'   => isset($_POST['carousel_settings']['desktop_touch_only']) ? 'true' : 'false',
                'mobile_touch_only'    => isset($_POST['carousel_settings']['mobile_touch_only']) ? 'true' : 'false',
                'lightbox'             => isset($_POST['carousel_settings']['lightbox']) ? 'true' : 'false',
                'hide_arrows_mobile'   => isset($_POST['carousel_settings']['hide_arrows_mobile']) ? 'true' : 'false',
                'lightbox_pagination'  => isset($_POST['carousel_settings']['lightbox_pagination']) ? 'true' : 'false',
                'hide_lightbox_arrows_mobile' => isset($_POST['carousel_settings']['hide_lightbox_arrows_mobile']) ? 'true' : 'false',
                'lightbox_touch'       => isset($_POST['carousel_settings']['lightbox_touch']) ? 'true' : 'false'
            );
            update_post_meta($post_id, '_carousel_settings', $carousel_settings);
        }
    }
}
