<?php

/**
 * Brisk functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Brisk
 */
defined('ABSPATH') || exit;

add_action( 'wp_enqueue_scripts', 'brisk_enqueue_styles' );
function brisk_enqueue_styles() {
   if (!class_exists('\UiCore\Frontend')) {
     wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
   }
}


/* YOU CAN START EDITING FROM HERE! */

/*
* Hooks and FIlters
*
* 1 - Frontend Actions
* 1.1 uicore_before_body_content
* 1.2 uicore_before_page_content
* 1.3 uicore_page
* 1.4 uicore_before_content
* 1.5 uicore_content_end
* 1.6 uicore_body_end

* 2 - Frontend Filters
* 2.1 - uicore_logo_link
* 2.2 - uicore_portfolio_slug
*/

/* 1.1 - Before body hook - Uncomment to activate */
// add_action('uicore_before_body_content', 'brisk_child_before_body_content_hook');
// function brisk_child_before_body_content_hook() {
// 	echo 'my super content';
// }

/* 1.2 - Before page content hook - Uncomment to activate */
// add_action('uicore_before_page_content', 'brisk_child__before_page_content__hook');
// function brisk_child__before_page_content__hook() {
// 	echo 'my super content';
// }

/* 1.3 - Page hook - Uncomment to activate */
// add_action('uicore_page', 'brisk_child__page__hook');
// function brisk_child__page__hook() {
// 	echo 'my super content';
// }

/* 1.4 - Before content hook - Uncomment to activate */
// add_action('uicore_before_content', 'brisk_child__before_content__hook');
// function brisk_child__before_content__hook() {
// 	echo 'my super content';
// }

/* 1.5 - Content end hook - Uncomment to activate */
// add_action('uicore_content_end', 'brisk_child__content_end__hook');
// function brisk_child__content_end__hook() {
// 	echo 'my super content';
// }

/* 1.6 - Body end hook - Uncomment to activate */
// add_action('uicore_body_end', 'brisk_child__body_end__hook');
// function brisk_child__body_end__hook() {
// 	echo 'my super content';
// }


/* 2.1 - Change logo link - Uncomment to activate */
// add_filter('uicore_logo_link', 'brisk_child__logo_link__filter');
// function brisk_child__logo_link__filter() {
// 	return 'https://new-link.com';
// }

/* 2.2 - Portfolio slug change - Uncomment to activate */
/* AFTER EVERY CHANGE YOU DO TO PORTFOLIO SLUG YOU NEED TO GO TO SETTINGS->PERMALINK AND HIT SAVE*/
// add_filter('uicore_portfolio_slug', 'brisk_child__portfolio_slug__filter');
// function brisk_child__portfolio_slug__filter() {
// 	return 'my-new-slug';
// }
