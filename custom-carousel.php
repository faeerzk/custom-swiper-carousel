<?php
/**
 * Plugin Name: Custom Swiper Carousel
 * Description: A simple and customizable carousel plugin
 * Version: 1.3.1
 * Author: M.Faiz
 * Text Domain: custom-swiper-carousel
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('CUSTOM_CAROUSEL_VERSION', '1.0.0');
define('CUSTOM_CAROUSEL_PATH', plugin_dir_path(__FILE__));
define('CUSTOM_CAROUSEL_URL', plugin_dir_url(__FILE__));

// Include required files
require_once CUSTOM_CAROUSEL_PATH . 'includes/class-custom-carousel.php';
require_once CUSTOM_CAROUSEL_PATH . 'includes/class-custom-carousel-post-type.php';
require_once CUSTOM_CAROUSEL_PATH . 'includes/class-custom-carousel-shortcode.php';
require_once CUSTOM_CAROUSEL_PATH . 'admin/class-custom-carousel-admin.php';

/**
 * Begins execution of the plugin.
 */
function run_custom_carousel() {
    // Register activation and deactivation hooks
    register_activation_hook(__FILE__, 'custom_carousel_activate');
    register_deactivation_hook(__FILE__, 'custom_carousel_deactivate');

    // Initialize the main plugin class
    $plugin = new Custom_Carousel();
    $plugin->init();
}

/**
 * The code that runs during plugin activation.
 */
function custom_carousel_activate() {
    // Clear the permalinks after the post type has been registered.
    flush_rewrite_rules();
}

/**
 * The code that runs during plugin deactivation.
 */
function custom_carousel_deactivate() {
    // Clear the permalinks to remove our post type's rules.
    flush_rewrite_rules();
}

// Run the plugin
run_custom_carousel();