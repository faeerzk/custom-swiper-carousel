<?php

/**
 * The main plugin class.
 */
class Custom_Carousel {

    /**
     * The post type instance.
     *
     * @var Custom_Carousel_Post_Type
     */
    private $post_type;

    /**
     * The shortcode instance.
     *
     * @var Custom_Carousel_Shortcode
     */
    private $shortcode;

    /**
     * The admin instance.
     *
     * @var Custom_Carousel_Admin
     */
    private $admin;

    /**
     * Initialize the plugin.
     */
    public function init() {
        // Load plugin text domain
        add_action('plugins_loaded', array($this, 'load_plugin_textdomain'));

        // Initialize components
        $this->post_type = new Custom_Carousel_Post_Type();
        $this->shortcode = new Custom_Carousel_Shortcode();

        if (is_admin()) {
            $this->admin = new Custom_Carousel_Admin();
        }

        // Register scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'register_scripts'));
    }

    /**
     * Load the plugin text domain for translation.
     */
    public function load_plugin_textdomain() {
        load_plugin_textdomain(
            'custom-swiper-carousel',
            false,
            dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );
    }

    /**
     * Register and enqueue scripts and styles.
     */
    public function register_scripts() {
        // Register Swiper styles
        wp_register_style(
            'swiper-css',
            CUSTOM_CAROUSEL_URL . 'assets/vendor/swiper/swiper-bundle.min.css',
            array(),
            CUSTOM_CAROUSEL_VERSION
        );

        wp_register_style(
            'custom-carousel-css',
            CUSTOM_CAROUSEL_URL . 'assets/css/custom-carousel.css',
            array('swiper-css'),
            CUSTOM_CAROUSEL_VERSION
        );

        // Register Swiper scripts
        wp_register_script(
            'swiper-js',
            CUSTOM_CAROUSEL_URL . 'assets/vendor/swiper/swiper-bundle.min.js',
            array(), // No jQuery dependency
            CUSTOM_CAROUSEL_VERSION,
            true
        );

        wp_register_script(
            'custom-carousel-js',
            CUSTOM_CAROUSEL_URL . 'assets/js/custom-carousel.js',
            array('swiper-js'),
            CUSTOM_CAROUSEL_VERSION,
            true
        );
    }
}
