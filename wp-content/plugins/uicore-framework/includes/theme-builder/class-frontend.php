<?php
namespace UiCore\ThemeBuilder;

use UiCore\Footer;
use UiCore\Helper;

defined('ABSPATH') || exit();

/**
 * Theme Builder Frontend functions
 *
 * @author Andrei Voica <andrei@uicore.co
 * @since 2.0.0
 */
class Frontend
{

    protected $is_header;
    protected $header_id;
   
    protected $is_footer;
    protected $footer_id;

    protected $is_popup;
    protected $popup_id;


    /**
     * Construct Theme Builder Frontend functions
     *
     * @author Andrei Voica <andrei@uicore.co
     * @since 2.0.0
     */
    public function __construct()
    {
         add_action('wp', function () {
            $this->init();
            
            add_action('uicore_content_end', [$this, 'footer'],5);
            add_action('uicore_page', [$this, 'header'],5);
            add_action('uicore_content_end', [$this, 'popup'],999);

        });

        //Change the menu walker so we can inject the megamenu
        add_filter('walker_nav_menu_start_el',[$this, 'megamenu_content_in_nav'],10,4);
        add_filter('nav_menu_css_class',[$this, 'megamenu_item_class'],10,2);

    }
    
    function false(){
        return false;
    }

    /**
     * Get Frontend Themebuilder elements to display
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 2.0.0
     */
    function init()
    {
        $this->get_settings('footer');
        $this->get_settings('header');
        $this->get_settings('popup');
    }

    /**
     * Get frontend elments
     *
     * @param [type] $type
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 2.0.0
     */
    function get_settings($type)
    {
        $templates = self::get_template_id( $type );
        $template = ! is_array( $templates ) ? $templates : $templates[0];
        $template = apply_filters( "uicore_tb_get_settings_{$type}", $template );
        
        if ( '' !== $template ) {
            switch ($type){
                case 'footer':
                    $this->is_footer = true;
                    $this->footer_id = $template;

                    $settings = get_post_meta($template, 'tb_settings', true);
                    if($settings['keep_default'] != 'true'){
                        add_filter('uicore_show_default_footer', [$this, 'false']);
                    }
                    break;

                case 'header':
                    $this->is_header = true;
                    $this->header_id = $template;

                    $settings = get_post_meta($template, 'tb_settings', true);
                    if($settings['keep_default'] != 'true'){
                        add_filter('uicore_show_default_header', [$this, 'false']);
                    }
                    break;

                case 'popup':
                    $this->is_popup = true;
                    $this->popup_id = $template;
                    break;
                
            }
        }

    }

    /**
     * Get Item ID to display is is anny
     *
     * @param [type] $type
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 2.0.0
     */
    public static function get_template_id( $type ) {
        $option = [
            'include'=>'tb_rule_include',
            'exclude' =>'tb_rule_exclude',
            'type'=>'_type_'.$type
        ];

		$item_list = \UiCore\ThemeBuilder\Rule::get_instance()->get_posts_by_conditions( $option );

		foreach ( $item_list as $item ) {
			if ( Common::get_the_type($item['id']) === $type ) {
				return $item['id'];
			}
		}

		return '';
	}

    /**
     * Hook footer elements in page
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 2.0.0
     */
    function footer()
    {
        if($this->is_footer){
            $this->display('footer'); 
        }
    }

    /**
     * Hook header elements in page
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 2.0.0
     */
    function header()
    {
        if($this->is_header){
            $this->display('header'); 
        }
    }

    /**
     * Hook header elements in page
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 2.0.0
     */
    function popup()
    {
        if($this->is_popup){
            $this->display('popup'); 
        }
    }


    /**
     * Display item makrup
     *
     * @param string $type
     * @param [type] $id
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 2.0.0
     */
    function display($type, $id=null)
    {
        if($type === 'header'){
            $id = $this->header_id;
            ?>
            <header id="uicore-tb-header" itemscope="itemscope" itemtype="https://schema.org/WPHeader">
			    <?php echo Common::get_elementor_content($id); ?>
		    </header>
            <?php
        }

        if($type === 'footer'){
            $id = $this->footer_id;
            ?>
            <footer id="uicore-tb-footer" itemscope="itemscope" itemtype="https://schema.org/WPFooter">
			    <?php echo Common::get_elementor_content($id); ?>
		    </footer>
            <?php
        }
        if($type === 'popup'){
            $id = $this->popup_id;
            $content = Common::get_elementor_content($id);
            ?>
            <?php Common::popup_markup($content,$id); ?>
            <?php
        }


    }

    /**
     * Inject Megamenu in navbar
     *
     * @param [type] $item_output
     * @param [type] $item
     * @param [type] $depth
     * @param [type] $args
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 2.0.0
     */
    function megamenu_content_in_nav($item_output, $item, $depth, $args )
    {
        if($item->object === 'uicore-tb'){
            $atts = null;
            $settings = get_post_meta($item->object_id, 'tb_settings', true);
            if($settings['width'] === 'custom'){
                $atts = 'style="--uicore-max-width:' . $settings['widthCustom'] . 'px"';
            } 
            //add bdt-navbar-dropdown for bdt navbar element
            $extra_class = 'uicore-megamenu bdt-navbar-dropdown';
            $item_output .= '<ul class="sub-menu '.$extra_class.'" ' . $atts . '>'; 
            $item_output .= Common::get_elementor_content($item->object_id); 
            $item_output .= '</ul>'; 
        }
        return $item_output;
    }

    /**
     * Add Magamenu item class in navbar
     *
     * @param [type] $classes
     * @param [type] $item
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 2.0.0
     */
    function megamenu_item_class($classes, $item)
    {
        if($item->object === 'uicore-tb'){
            $item->url = apply_filters( "uicore_tb_megamenu_url_{$item->object_id}", '#' );
            $classes[] = "menu-item-has-children";
            $classes[] = "menu-item-has-megamenu";
            $settings = get_post_meta($item->object_id, 'tb_settings', true);
            if($settings['width'] === 'custom') {
                $classes[] = 'custom-width';
            }
            if($settings['width'] === 'container') {
                $classes[] = 'container-width';
            }
        }
        return $classes;
    }
}
new Frontend(); 