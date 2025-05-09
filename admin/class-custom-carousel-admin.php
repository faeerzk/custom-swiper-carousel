<?php

/**
 * The admin-specific functionality of the plugin.
 */
class Custom_Carousel_Admin {

    /**
     * Constructor.
     */
    public function __construct() {
        // Add admin scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));

        // Add settings page
        add_action('admin_menu', array($this, 'add_settings_page'));

        // Register settings
        add_action('admin_init', array($this, 'register_settings'));
    }

    /**
     * Enqueue admin scripts and styles.
     *
     * @param string $hook The current admin page.
     */
    public function enqueue_admin_scripts($hook) {
        global $post;

        // Only enqueue on carousel edit screen
        if ($hook === 'post.php' || $hook === 'post-new.php') {
            if (isset($post) && $post->post_type === 'carousel') {
                wp_enqueue_media();

                wp_enqueue_script(
                    'jquery-ui-sortable'
                );

                wp_enqueue_style(
                    'custom-carousel-admin-css',
                    CUSTOM_CAROUSEL_URL . 'admin/css/custom-carousel-admin.css',
                    array(),
                    CUSTOM_CAROUSEL_VERSION
                );
            }
        }

        // Only enqueue on settings page
        if ($hook === 'carousels_page_custom-carousel-settings') {
            wp_enqueue_style(
                'custom-carousel-admin-settings-css',
                CUSTOM_CAROUSEL_URL . 'admin/css/custom-carousel-admin-settings.css',
                array(),
                CUSTOM_CAROUSEL_VERSION
            );
        }
    }

    /**
     * Add settings page.
     */
    public function add_settings_page() {
        add_submenu_page(
            'edit.php?post_type=carousel',
            __('Custom Carousel Settings', 'custom-carousel'),
            __('Settings', 'custom-carousel'),
            'manage_options',
            'custom-carousel-settings',
            array($this, 'render_settings_page')
        );
    }

    /**
     * Register settings.
     */
    public function register_settings() {
        register_setting(
            'custom_carousel_settings',
            'custom_carousel_settings',
            array($this, 'sanitize_settings')
        );

        add_settings_section(
            'custom_carousel_general_settings',
            __('General Settings', 'custom-carousel'),
            array($this, 'render_general_settings_section'),
            'custom-carousel-settings'
        );

        add_settings_field(
            'custom_carousel_default_image_size',
            __('Default Image Size', 'custom-carousel'),
            array($this, 'render_default_image_size_field'),
            'custom-carousel-settings',
            'custom_carousel_general_settings'
        );

        add_settings_field(
            'custom_carousel_default_effect',
            __('Default Transition Effect', 'custom-carousel'),
            array($this, 'render_default_effect_field'),
            'custom-carousel-settings',
            'custom_carousel_general_settings'
        );

        add_settings_field(
            'custom_carousel_theme',
            __('Carousel Theme', 'custom-carousel'),
            array($this, 'render_theme_field'),
            'custom-carousel-settings',
            'custom_carousel_general_settings'
        );
    }

    /**
     * Sanitize settings.
     *
     * @param array $input The value being sanitized.
     * @return array
     */
    public function sanitize_settings($input) {
        $output = array();

        if (isset($input['default_image_size'])) {
            $output['default_image_size'] = sanitize_text_field($input['default_image_size']);
        }

        if (isset($input['default_effect'])) {
            $output['default_effect'] = sanitize_text_field($input['default_effect']);
        }

        if (isset($input['theme'])) {
            $output['theme'] = sanitize_text_field($input['theme']);
        }

        return $output;
    }

    /**
     * Render the settings page.
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields('custom_carousel_settings');
                do_settings_sections('custom-carousel-settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Render the general settings section.
     */
    public function render_general_settings_section() {
        echo '<p>' . __('Configure the default settings for all carousels.', 'custom-carousel') . '</p>';
    }

    /**
     * Render the default image size field.
     */
    public function render_default_image_size_field() {
        $options = get_option('custom_carousel_settings', array());
        $default_image_size = isset($options['default_image_size']) ? $options['default_image_size'] : 'large';

        $image_sizes = get_intermediate_image_sizes();
        $image_sizes[] = 'full';
        ?>
        <select name="custom_carousel_settings[default_image_size]" id="custom_carousel_default_image_size">
            <?php foreach ($image_sizes as $size) : ?>
                <option value="<?php echo esc_attr($size); ?>" <?php selected($default_image_size, $size); ?>>
                    <?php echo esc_html($size); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <p class="description">
            <?php _e('Select the default image size to use in carousels.', 'custom-carousel'); ?>
        </p>
        <?php
    }

    /**
     * Render the default effect field.
     */
    public function render_default_effect_field() {
        $options = get_option('custom_carousel_settings', array());
        $default_effect = isset($options['default_effect']) ? $options['default_effect'] : 'slide';

        $effects = array(
            'slide' => __('Slide', 'custom-carousel'),
            'fade' => __('Fade', 'custom-carousel'),
            'cube' => __('Cube', 'custom-carousel'),
            'coverflow' => __('Coverflow', 'custom-carousel'),
            'flip' => __('Flip', 'custom-carousel'),
            'cards' => __('Cards', 'custom-carousel')
        );
        ?>
        <select name="custom_carousel_settings[default_effect]" id="custom_carousel_default_effect">
            <?php foreach ($effects as $value => $label) : ?>
                <option value="<?php echo esc_attr($value); ?>" <?php selected($default_effect, $value); ?>>
                    <?php echo esc_html($label); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <p class="description">
            <?php _e('Select the default transition effect for new carousels.', 'custom-carousel'); ?>
        </p>
        <?php
    }

    /**
     * Render the theme field.
     */
    public function render_theme_field() {
        $options = get_option('custom_carousel_settings', array());
        $theme = isset($options['theme']) ? $options['theme'] : 'default';

        $themes = array(
            'default' => __('Default', 'custom-carousel'),
            'dark' => __('Dark', 'custom-carousel'),
            'light' => __('Light', 'custom-carousel'),
            'minimal' => __('Minimal', 'custom-carousel')
        );
        ?>
        <select name="custom_carousel_settings[theme]" id="custom_carousel_theme">
            <?php foreach ($themes as $value => $label) : ?>
                <option value="<?php echo esc_attr($value); ?>" <?php selected($theme, $value); ?>>
                    <?php echo esc_html($label); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <p class="description">
            <?php _e('Select the visual theme for the carousels.', 'custom-carousel'); ?>
        </p>
        <?php
    }
}
