<?php

/**
 * Custom template tags for this theme
 *
 * Eventually, some of the functionality here could be replaced by core features.
 *
 * @package Brisk
 */
defined('ABSPATH') || exit;


if (!class_exists('\UiCore\Core')) {
	/**
	 * Register widget area.
	 *
	 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
	 */
	function brisk_widgets_init()
	{
		register_sidebar(
			array(
				'name' =>__( 'Left Sidebar', 'brisk' ),
				'id'            => 'left-sidebar',
				'before_widget' => '<div class="uicore-sidebar-element">',
				'after_widget' => '</div>',
				'before_title' => '<h4 class="brisk-title">',
				'after_title' => '</h4>',
			)
		);
		register_sidebar(
			array(
				'name' => __( 'Right Sidebar', 'brisk' ),
				'id'            => 'right-sidebar',
				'before_widget' => '<div class="uicore-sidebar-element">',
				'after_widget' => '</div>',
				'before_title' => '<h4 class="brisk-title">',
				'after_title' => '</h4>',
			)
		);
	}
	add_action('widgets_init', 'brisk_widgets_init');


	/**
	 * Custom template tags for this theme.
	 */
	require get_template_directory() . '/inc/template-tags.php';


	/**
	 * Proper way to enqueue scripts and styles
	 */
	function brisk_default_theme()
	{
		wp_enqueue_style('brisk', get_stylesheet_uri(), array(), UICORE_THEME_VERSION);
		wp_enqueue_style('brisk-icons', get_template_directory_uri() . '/assets/fonts/brisk-icons.css');
		wp_enqueue_style('brisk-font', "//rsms.me/inter/inter.css");
		wp_enqueue_script('brisk-script', get_template_directory_uri() . '/assets/js/main.js', array('jquery'), UICORE_THEME_VERSION, true);
	}
	add_action('wp_enqueue_scripts', 'brisk_default_theme');

	function brisk_editor_style_for_page()
	{
		echo '<style id="uicore-editor" >
				.post-type-post .editor-styles-wrapper [data-block],
				.post-type-post .editor-styles-wrapper .wp-block {
					max-width: 625px !important;
				}
				.edit-post-layout__content .edit-post-visual-editor{
					padding-top:0;
				}
			</style>';
	}
	add_action('admin_head', 'brisk_editor_style_for_page');

	function brisk_body_classes($classes)
	{
		if (is_active_sidebar('left-sidebar')) {
			$classes[] = 'left-sidebar';
		}
		if (is_active_sidebar('right-sidebar')) {
			$classes[] = 'right-sidebar';
		}
		return $classes;
	}
	add_filter('body_class', 'brisk_body_classes');

	/**
	 * Add a pingback url auto-discovery header for single posts, pages, or attachments.
	 */
	function brisk_pingback_header()
	{
		if (is_singular() && pings_open()) {
			printf('<link rel="pingback" href="%s">', esc_url(get_bloginfo('pingback_url')));
		}
	}
	add_action('wp_head', 'brisk_pingback_header');

	function brisk_excerpt_length($length)
	{
		return 17;
	}
	add_filter('excerpt_length', 'brisk_excerpt_length', 999);

	function brisk_pagination_style($default){
		$args = array(
			'before'           => '<nav aria-label="'.__('Posts navigation', 'brisk') .'" class="brisk-pagination brisk-pages"> <ul>',
			'after'            => '</ul></nav>',
			'link_before'      => '<li class="brisk-page-item">',
			'link_after'       => '</li>',
			'aria_current'     => 'page',
			'next_or_number'   => 'number',
			'separator'        => ' ',
			'nextpagelink'     => __( 'Next page', 'brisk' ),
			'previouspagelink' => __( 'Previous page', 'brisk'  ),
			'pagelink'         => '%',
			'echo'             => 1,
		);

		$parsed_args = wp_parse_args( $args, $default );

		return $parsed_args;
	}
	add_filter('wp_link_pages_args', 'brisk_pagination_style');

	function brisk_pagination()
	{

		//global $query;
		$args = wp_parse_args(
			array(
				'mid_size'           => 2,
				'prev_next'          => true,
				'prev_text'          => __('', 'brisk'),
				'next_text'          => __('', 'brisk'),
				'screen_reader_text' => __('Posts navigation', 'brisk'),
				'type'               => 'array',
				'current'            => max(1, get_query_var('paged')),
				'base' => get_pagenum_link(1) . '%_%',
			)
		);

		$links = paginate_links($args);
		if (is_array($links) || is_object($links)) {
?>
			<nav aria-label="<?php echo esc_attr($args['screen_reader_text']); ?>" class="brisk-pagination">
				<ul>
					<?php
					foreach ($links as $key => $link) {
					?>
						<li class="brisk-page-item <?php echo strpos($link, 'current') ? 'brisk-active' : ''; ?>">
							<?php echo str_replace('page-numbers', 'brisk-page-link', $link); ?>
						</li>
					<?php
					}
					?>
				</ul>
			</nav>
			<?php
		}
	}


	function brisk_page_title()
	{
		echo '<h1>';
		if (is_search()) {
            echo sprintf(
                esc_html__('Search Results for: %s', 'brisk'),
                '<span>' . get_search_query() . '</span>'
			);
		} else if (is_home()) {
			echo esc_html__('Latest Posts', 'brisk');
		} else if ((is_category() || is_day() || is_month() || is_author() || is_year() || is_tag())) {
			the_archive_title();
		} else if (is_singular()) {
			the_title();
		}
		if (class_exists('WooCommerce')) {
			if (is_product_taxonomy()) {
				single_cat_title();
			}
			if (is_shop()) {
				esc_html_e('Shop', 'brisk');
			}
			// if (is_product()) {
			// 	the_title();
			// }
		}
		echo '</h1>';
	}

	if (class_exists('WooCommerce')) {
		function brisk_woocommerce_setup()
		{
			add_theme_support('woocommerce');
			add_theme_support('wc-product-gallery-zoom');
			add_theme_support('wc-product-gallery-lightbox');
			add_theme_support('wc-product-gallery-slider');
		}
		add_action('after_setup_theme', 'brisk_woocommerce_setup');

		function brisk_woo_widgets_init()
		{
			register_sidebar(
				array(
					'name' => __( 'Product Sidebar', 'brisk' ),
					'id'            => 'product-sidebar',
					'before_widget' => '<div class="uicore-sidebar-element %2$s">',
					'after_widget' => '</div>',
					'before_title' => '<h4 class="brisk-title">',
					'after_title' => '</h4>',
				)
			);
			register_sidebar(
				array(
					'name' => __( 'Shop Sidebar', 'brisk' ),
					'id'            => 'shop-sidebar',
					'before_widget' => '<div class="uicore-sidebar-element %2$s">',
					'after_widget' => '</div>',
					'before_title' => '<h4 class="brisk-title">',
					'after_title' => '</h4>',
				)
			);
		}
		add_action('widgets_init', 'brisk_woo_widgets_init');

		//remove woo default sidebar
		// remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );
		//remove woo default breadcrumb
		remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);
		//remove woo default page title
		add_filter('woocommerce_show_page_title', '__return_false', 20);

		//Zoom wrapper
		add_action('woocommerce_before_shop_loop_item', function () {
			echo '<div class="uicore-zoom-wrapper">';
		}, 10);
		add_action('woocommerce_before_shop_loop_item_title', function () {
			echo '</div>';
		}, 10);

		//Add to cart&price hover effect
		remove_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10);
		remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
		add_action('woocommerce_after_shop_loop_item', function () {
			echo '<div class="uicore-reveal-wrapper"><div class="uicore-reveal">';
		}, 6);
		add_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_price', 8);
		add_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 9);
		add_action('woocommerce_after_shop_loop_item', function () {
			echo '</div></div>';
		}, 11);

		//Wrap the woo pages
		remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
		remove_action('woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);
		if (!function_exists('uicore_wrapper_before')) {
			function uicore_wrapper_before()
			{
			?>
				<div id="primary" class="content-area">
					<main class="uicore-container alt-container">
						<?php
						if (is_product() && is_active_sidebar('product-sidebar')) {
						?>
							<aside class="left-widget-area uicore-col-lg-3">
								<?php dynamic_sidebar('product-sidebar'); ?>
							</aside>
						<?php } elseif (!is_product() && is_active_sidebar('shop-sidebar')) {
						?>
							<aside class="left-widget-area uicore-col-lg-3">
								<?php dynamic_sidebar('shop-sidebar'); ?>
							</aside>
						<?php } ?>
						<div class="uicore-row">
						<?php
					}
				}
				add_action('woocommerce_before_main_content', 'uicore_wrapper_before');
				if (!function_exists('uicore_wrapper_after')) {
					function uicore_wrapper_after()
					{
						?>

						</div>
					</main>
	<?php
					}
				}
				add_action('woocommerce_after_main_content', 'uicore_wrapper_after');
			}
		}
