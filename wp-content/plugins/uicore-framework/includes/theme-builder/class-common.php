<?php
namespace UiCore\ThemeBuilder;

use UiCore\Settings as Settings;

defined('ABSPATH') || exit();

/**
 * Theme Builder generic functions
 *
 * @author Andrei Voica <andrei@uicore.co
 * @since 2.0.0
 */
class Common
{

    /**
     * Construct Theme Builder generic functions
     *
     * @author Andrei Voica <andrei@uicore.co
     * @since 2.0.0
     */
    public function __construct()
    {
        add_action('init', [$this, 'register_ctp'], 0);
        add_filter('single_template', [$this, 'custom_templates']);
        add_action( 'elementor/documents/register', [ $this, 'register_tb_types' ] );
        add_filter( 'theme_uicore-tb_templates', [$this, 'custom_templates_list'] );
        add_shortcode('uicore-block', [$this, 'blocks_shortcode']);
    }

    /**
     * Register uicore Theme Builder Elementor Document Type
     *
     * @param [type] $documents_manager
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 2.0.0
     */
	public function register_tb_types( $documents_manager )
    {
        $documents_manager->register_document_type( 'uicore-tb', UiCoreTB::get_class_full_name() );
	}

    /**
     * Register Custom Post for Theme Builder
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 2.0.0
     */
    function register_ctp()
    {
        $name =  __('Theme Builder','uicore-framework');
        $slug = 'uicore-tb';

        register_taxonomy(
            'tb_type',
            [],
            [
                'hierarchical' => false,
                'public' => false,
                'label' => _x( 'Type', 'Theme Builder', 'uicore-framework' ),
                'show_ui' => false,
                'show_admin_column' => false,
                'query_var' => true,
                'show_in_rest' => false,
                'rewrite' => false,
            ]
        );
        register_taxonomy(
            'tb_rule',
            [],
            [
                'hierarchical' => false,
                'public' => false,
                'show_ui' => false,
                'show_admin_column' => false,
                'query_var' => true,
                'show_in_rest' => false,
                'rewrite' => false,
            ]
        );

        register_post_type($slug, [
            'labels' => [
                'name' => $name,
                'singular_name' => $name,
            ],
            'has_archive' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'show_in_admin_bar' => false,
            'show_in_nav_menus' => true,
            'taxonomies' => ['tb_type','tb_rule'],
            'menu_icon' => 'dashicons-format-gallery',
            'public' => true,
            'rewrite' => false,
            'show_in_rest' => false,
			'exclude_from_search' => true,
			'capability_type' => 'post',
			'hierarchical' => false,
			'supports' => [ 'title', 'thumbnail', 'author', 'elementor' ],
        ]);
    }

    /**
     * Force specific template for theme builder
     *
     * @param [type] $single
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 2.0.0
     */
    function custom_templates($single)
    {
        global $post;

        /* Checks for single template by post type */
        if ( $post->post_type == 'uicore-tb' ) {

            //we need more controll on popup
            if(self::get_the_type($post->ID) === 'popup'){
                return UICORE_INCLUDES . '/theme-builder/templates/popup.php';
            }
            //default
            return UICORE_INCLUDES . '/theme-builder/templates/canvas.php';
        }
        return $single;
    }

    /**
     * Add Theme Builder Canvas to Templates
     *
     * @param [type] $post_templates
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 2.0.0
     */
    function custom_templates_list( $post_templates )
    {
        $post_templates[UICORE_INCLUDES . '/theme-builder/templates/canvas.php'] = "ThemeBuilder Canvas";
        $post_templates[UICORE_INCLUDES . '/theme-builder/templates/popup.php'] = "ThemeBuilder Popup";
        return $post_templates;
    }

    /**
     * Get Elementor content for display
     *
     * @param [type] $content_id
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 2.0.0
     */
    static function get_elementor_content( $content_id )
    {
        $content = '';
        $content_id = apply_filters( 'wpml_object_id', $content_id, 'post', true );    
        if(\class_exists('\Elementor\Plugin')){
            $elementor_instance = \Elementor\Plugin::instance();
            $content = $elementor_instance->frontend->get_builder_content_for_display( $content_id, true );
        }
        return $content;
    }

    /**
     * Get ThemeBuilder element Type
     *
     * @param int $post_id
     * @return string
     * @author Andrei Voica <andrei@uicore.co>
     * @since 2.0.0
     */
    static function get_the_type($post_id)
    {
        $type = wp_get_post_terms($post_id, 'tb_type', ['fields' => 'names']);
        $type = $type[0] ? str_replace('_type_', '', $type[0] ) : '';
        $type = ($type === 'mm') ? 'mega menu' : $type;
        return $type;
    }

    /**
     * Get Rule locations list
     *
     * @return array
     * @author Andrei Voica <andrei@uicore.co>
     * @since 2.0.0
     */
	public static function get_location_selections() {
		$args = array(
			'public'   => true,
			'_builtin' => true,
		);

		$post_types = get_post_types( $args, 'objects' );
		unset( $post_types['attachment'] );

		$special_pages = array(
			array(
				'name'    => __( '404 Page', 'uicore-framework' ),
				'value'    => 'special-404'
			),
			array(
				'name'    => __( 'Search Page', 'uicore-framework' ),
				'value'    => 'special-search'
			),
			array(
				'name'    => __( 'Blog / Posts Page', 'uicore-framework' ),
				'value'    => 'special-blog'
			),
			array(
				'name'    => __( 'Front Page', 'uicore-framework' ),
				'value'    => 'special-front'
			),
			array(
				'name'    => __( 'Date Archive', 'uicore-framework' ),
				'value'    => 'special-date'
			),
			array(
				'name'    => __( 'Author Archive', 'uicore-framework' ),
				'value'    => 'special-author'
			)
		);

		if ( class_exists( 'WooCommerce' ) ) {
			$special_pages[] = array(
				'name'    => __( 'WooCommerce Shop Page', 'uicore-framework' ),
				'value'    => 'special-woo-shop'
			);
		}

		$selection_options = array(
			'basic'         => array(
				'label' => __( 'Basic', 'uicore-framework' ),
				'value' => array(
					array(
						'name'    => __( 'Entire Website', 'uicore-framework' ),
						'value'    => 'basic-global'
					),
					array(
						'name'    => __( 'All Pages', 'uicore-framework' ),
						'value'    => 'basic-page'
					),
					array(
						'name'    => __( 'All Blog Posts', 'uicore-framework' ),
						'value'    => 'basic-single'
					),
                    array(
						'name'    => __( 'All Portfolio Posts', 'uicore-framework' ),
						'value'    => 'basic-portfolio'
					),
					array(
						'name'    => __( 'All Archives', 'uicore-framework' ),
						'value'    => 'basic-archives'
					),

				),
			),

			'special-pages' => array(
				'label' => __( 'Special Pages', 'uicore-framework' ),
				'value' => $special_pages,
			),
		);


		$selection_options['specific-target'] = array(
			'label' => __( 'Specific Target', 'uicore-framework' ),
			'value' => array(
				array(
					'name'    => __( 'Specific Pages / Posts / Taxonomies, etc.', 'uicore-framework' ),
					'value'    => 'specifics'
				),
			),
		);

		return $selection_options;
	}

    /**
     * Shortcode function for blocks
     *
     * @param [type] $atts
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 2.0.0
     */
    function blocks_shortcode($atts)
    {
        $atr = shortcode_atts(
            [
                'id' => false,
            ],
            $atts
        );
        if($atr['id']){
            return Common::get_elementor_content($atr['id']);
        }
    }

    static function popup_markup($content, $id)
    {
        //check first if is in edit mode but on a page where is embeded and hide it if so
        if( isset($_GET['elementor-preview']) && $_GET['elementor-preview'] != $id ){
            return;
        }

        $is_editor = isset($_GET['elementor-preview']);
        $is_prev = isset($_GET['ui-popup-preview']);

        //we don't need it if is prev iframe
        if($is_prev){
            ?>
            <style>
            #wpadminbar { display:none !important;}
            </style>
            <?php
            return null;
        }

        $css = null;
        $css_wrapp = null;
        $css_close = null;
        $css_class = $is_editor ? $id . ' ui-popup-active' : $id;

        $trigger = false;
		$settings = get_post_meta($id, 'tb_settings', true);
        if(is_array($settings)){

            $trigger = $settings['trigger'];

            function ui_position_filter($value){
                if($value === 'bottom' || $value === 'right'){
                    return 'flex-end';
                }elseif($value === 'top' || $value === 'left'){
                    return 'flex-start';
                }else{
                    return $value;
                }
            }



            if($settings['width']['mode'] === 'custom'){
                $css .= 'width:' . $settings['width']['size'] . 'px;';
            }elseif($settings['width']['mode'] === 'full'){
                $css .= 'min-width:100vh;';
            }

            if($settings['height']['mode'] === 'custom'){
                $css .= 'max-height:' . $settings['height']['size'] . 'px;';
            }elseif($settings['height']['mode'] === 'full'){
                $css .= 'min-height:100vh;';
            }

            $position = explode(" ", $settings['position']);
            $css_wrapp .= 'justify-content:'.ui_position_filter($position[0]).';';
            $css_wrapp .= 'align-items:'.ui_position_filter($position[1]).';';

            if($settings['close'] === 'true'){
                $css_close = '.ui-popup-close {';
                $css_close .= 'color:' . Settings::color_filter($settings['closeColor']['default']);
                $css_close .= '}';
                $css_close .= '.ui-popup-close:hover {';
                $css_close .= 'color:' . Settings::color_filter($settings['closeColor']['hover']);
                $css_close .= '}';
            }

            $animation = str_replace(' ', '', ucwords($settings['animation']) );
            $css .= 'animation-name:uicore'.$animation;
        }
        // print_r($settings);
	?>
        <style>
            .ui-popup-background, .ui-popup-wrapper, .ui-popup-overlay{
                position: fixed;
                width: 100vw;
                height:100vh;
                top: 0;
                left: 0;
            }
            .ui-popup-overlay{
                background-color: rgba(0, 0 ,0, 70%);
            }
            .ui-popup-wrapper{
                display: none;
                z-index: 9999;
                animation-name: uicoreFadeIn;
	            animation-timing-function: ease-in-out;
                animation-duration: .4s;
                <?php echo $css_wrapp; ?>
            }
            .ui-popup-active{
                display: flex;
            }
            .ui-popup{
                display: none;
                position: relative;
                width: 100%;
                max-width: 100vw;
                max-height: 95vh;
                animation-duration: .6s;
				overflow: hidden;
                <?php echo $css; ?>
            }
            .ui-popup-active .ui-popup{
                display: flex;
            }
            .ui-popup-close{
                position: absolute;
                right: 6px;
                top: 6px;
                padding: 10px;
                font-size: 20px;
                z-index: 1;
                line-height: 1;
                cursor: pointer;
                transition: all .3s ease-in-out;
            }
            <?php echo $css_close; ?>

            .ui-popup-wrapper [data-elementor-type=uicore-tb] {
                overflow: hidden auto;
                width: 100%;

            }
            .ui-popup-wrapper [data-elementor-type=uicore-tb] .elementor-section-wrap:not(:empty)+#elementor-add-new-section {
                display: none;
            }
			.elementor-add-section:not(.elementor-dragging-on-child) .elementor-add-section-inner {
				background-color: #fff;
			}
        </style>

        <div class="ui-popup-wrapper ui-popup-<?php echo $css_class; ?>">

            <?php
            if($settings['overlay'] === 'true'){
            ?>
                <div class="ui-popup-overlay"></div>
            <?php }
            ?>

            <div class="ui-popup">
            <?php
                if($settings['close'] === 'true'){
            ?>
                <div class="ui-popup-close">
                    <i class="eicon-close"></i>
                </div>
            <?php }

                echo $content;
            ?>
            </div>
        </div>
        <?php
        if($trigger && !$is_editor){
            $js = null;
            $extra = null;


            function is_active($option){
                return $option['enable'] === 'true';
            }

            //run the triggers js only if we need to show the popup again
            if( is_active($trigger['maxShow']) ){
                $js .= "
                if(!localStorage.getItem('uicore_popup_".$id."') || (localStorage.getItem('uicore_popup_".$id."') < ".$trigger['maxShow']['amount']." ) ){
                    ";

                $extra .= "
                localStorage.setItem('uicore_popup_".$id."', Number(localStorage.getItem('uicore_popup_".$id."')) + 1); ";
            }


            $condition = 'true';


            //responsive
            if($settings['responsive']['desktop'] === 'true'){
                $condition .= " && !window.matchMedia( '(min-width: 1025px)' ).matches";
            }
            if($settings['responsive']['tablet'] === 'true'){
                $condition .= " && !window.matchMedia( '(min-width: 768px) and ( max-width: 1025px)' ).matches";
            }
            if($settings['responsive']['mobile'] === 'true'){
                $condition .= " && !window.matchMedia( '(max-width: 767px)' ).matches";
            }

            $js .= "if(".$condition."){
                ";


            if( $settings['pageScroll']  === 'true'){
                $extra .= ' document.body.setAttribute("style","overflow:hidden;"); ';
            }

            //Triggers
            $js .=  'var uipopupTrigger = function() {
                        jQuery(".ui-popup-'.$id.'").addClass("ui-popup-active");'
                        .$extra.'
                        };';

            if( is_active($trigger['pageLoad']) ){
                $js .= "
                jQuery( document ).ready(function() {
                    setTimeout(function(){
                            uipopupTrigger();
                    }, ".($trigger['pageLoad']['delay'] * 1000)." );
                });
                ";
            }
            if( is_active($trigger['pageScroll']) ){
                $direction = ($trigger['pageScroll']['direction'] === 'down')
                ? '> previousScroll && (currentScroll/(docheight-winheight)) > scrolltrigger'
                : '< previousScroll';
                $js .= "
                jQuery( document ).ready(function() {
                    var previousScroll = 0;
                    var scrolltrigger = 0.".$trigger['pageScroll']['amount'].";

                    window.addEventListener('scroll', pageScrollTrigger);
                    function pageScrollTrigger() {
                        var currentScroll = jQuery(this).scrollTop();
                        var docheight = jQuery(document).height();
                        var winheight = jQuery(window).height();
                        if (currentScroll ". $direction ."){
                            uipopupTrigger();
                            window.removeEventListener('scroll', pageScrollTrigger);
                        }
                        previousScroll = currentScroll;
                    };
                });
                ";
            }
            if( is_active($trigger['scrollToElement']) ){
                $element = $trigger['scrollToElement']['selector'];
                $js .= "
                jQuery( document ).ready(function() {
                    window.addEventListener('scroll', scrollElementTrigger);
                    function scrollElementTrigger() {
                        var top= jQuery('".$element."').offset().top;
                        var bottom = jQuery('".$element."').offset().top + jQuery('".$element."').outerHeight();
                        var toBottom= jQuery(window).scrollTop() + jQuery(window).innerHeight();
                        var toTop = jQuery(window).scrollTop();

                        if ((toBottom > top) && (toTop < bottom)){
                            uipopupTrigger();
                            window.removeEventListener('scroll', scrollElementTrigger);
                        }
                    }
                });
                ";
            }
            if( is_active($trigger['click']) ){
                $no = $trigger['click']['clicks'];
                $js .= "
                jQuery( document ).ready(function() {
                    var clicks = 0;
                    var maxClicks = ".$no.";
                    window.addEventListener('click', clickTrigger);
                    function clickTrigger() {
                        clicks++;
                        if (clicks > maxClicks){
                            uipopupTrigger();
                            window.removeEventListener('click', clickTrigger);
                        }
                    }
                });
                ";
            }
            if( is_active($trigger['clickOnElement']) ){
                $element = $trigger['clickOnElement']['selector'];
                $js .= "
                jQuery( document ).ready(function() {
                    jQuery('".$element."').bind('click', uipopupTrigger);
                });
                ";
            }
            if( is_active($trigger['onExit']) ){
                $js .= "
                jQuery( document ).ready(function() {
                    document.addEventListener('mouseout', onExitTrigger);
                    function onExitTrigger(event) {
                        if (!event.toElement && !event.relatedTarget) {
                            uipopupTrigger();
                            document.removeEventListener('mouseout', onExitTrigger);
                        }
                    }
                });
                ";
            }

            //run the triggers js only if we need to show the popup again (close the js if)
            if( is_active($trigger['maxShow']) ){
                $js .= "
                    }
                    ";
            }

            //responsive
            $js .= "
            }
            ";

            //close on overlay
            if($settings['overlay'] && $settings['closeOnOverlay'] === 'true'){
                $extra_class_for_close = ", .ui-popup-overlay";
            }else{
                $extra_class_for_close = "";
            }
            ?>
            <script>
                        <?php echo $js; ?>

                jQuery('.ui-popup-close, #ui-close-popup <?php echo $extra_class_for_close; ?>').on('click', function(){
                    jQuery(this).closest('.ui-popup-active').removeClass('ui-popup-active');
                    document.body.setAttribute("style","overflow:auto;");
                })
            </script>
            <?php
        }
    }
}
new Common();
