<?php

/**
 * Custom template tags for this theme
 *
 * Eventually, some of the functionality here could be replaced by core features.
 *
 * @package Brisk
 */
defined('ABSPATH') || exit;


if (!function_exists('brisk_setup')) :
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support for post thumbnails.
	 */
	function brisk_setup()
	{
		/*
		 * Make theme available for translation.
		 * Translations can be filed in the /languages/ directory.
		 * If you're building a theme based on Brisk, use a find and replace
		 * to change 'brisk' to the name of your theme in all the template files.
		 */
		load_theme_textdomain('brisk', get_template_directory() . '/languages');


		/*
		 * Enable support for Post Thumbnails on posts and pages.
		 *
		 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		 */
		add_theme_support('post-thumbnails');

		add_theme_support('responsive-embeds');


		// This theme uses wp_nav_menu() in one location.
		register_nav_menus(array(
			'primary' => esc_html__('Primary', 'brisk'),
		));

		add_theme_support('html5', array('comment-list', 'comment-form', 'gallery','script', 'style'));

		// Add theme support for selective refresh for widgets.
		add_theme_support('customize-selective-refresh-widgets');

		add_image_size( 'uicore-medium', 650, 650, false );

		//Required
		add_theme_support( 'automatic-feed-links' );
		add_theme_support( "title-tag" );

		// Add support for editor styles.
		add_theme_support('editor-styles');

		if (!class_exists('\UiCore\Core')) {
			add_editor_style();
			add_editor_style('https://rsms.me/inter/inter.css');
		} else {
			add_theme_support('align-wide');
		}
	}
endif;
add_action('after_setup_theme', 'brisk_setup');

