<?php
/**
 * UniWheels Theme Functions and Definitions
 *
 * @package UniWheels
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

function uniwheels_setup() {
    // Add default title tag support
    add_theme_support( 'title-tag' );

    // Enable support for Post Thumbnails
    add_theme_support( 'post-thumbnails' );

    // Register Navigation Menus
    register_nav_menus( array(
        'primary-menu' => __( 'Primary Header Menu', 'uniwheels' ),
        'footer-menu'  => __( 'Footer Menu', 'uniwheels' ),
    ) );

    // HTML5 markup support
    add_theme_support( 'html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ) );
}
add_action( 'after_setup_theme', 'uniwheels_setup' );

function uniwheels_enqueue_scripts() {
    // Google Fonts: Inter and Outfit
    wp_enqueue_style( 'uniwheels-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;600;700;800;900&display=swap', array(), null );

    // Main Theme Stylesheet
    wp_enqueue_style( 'uniwheels-style', get_template_directory_uri() . '/assets/css/style.css', array(), '1.0.0' );

    // Interactive Frontend Script
    wp_enqueue_script( 'uniwheels-script', get_template_directory_uri() . '/assets/js/main.js', array(), '1.0.0', true );
}
add_action( 'wp_enqueue_scripts', 'uniwheels_enqueue_scripts' );
