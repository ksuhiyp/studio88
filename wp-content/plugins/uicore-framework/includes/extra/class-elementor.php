<?php
namespace UiCore;

use Elementor\Controls_Stack;
use Elementor\Plugin;

/**
 * Elementor Related functions
 */
class Elementor
{

    /**
     * Elementor Font Type Name for Typekit
     */
    const TYPEKIT = 'uicore_typekit';

    const TYPEKIT_FONTS_LINK = 'https://use.typekit.net/%s.css';

    /**
     * Elementor Font Type Name for Typekit
     */
    const CUSTOM = 'uicore_custom';


    public function __construct()
    {
        $this->custom_post_elementor_support();
        add_filter('elementor/icons_manager/additional_tabs', [$this, 'add_custom_icons']);
        add_filter('add_post_metadata', [$this, 'update_globals_from_elementor'], 20, 5);
        add_filter('update_post_metadata', [$this, 'update_globals_from_elementor'], 20, 5);

        //Add Suport For theme Builder Locations
        add_action( 'elementor/theme/register_locations', [$this, 'elementor_locations'] );

        //Elementor missing ggogle fonts
        add_filter( 'elementor/fonts/additional_fonts',[$this, 'new_google_fonts'],20,1 );

        add_filter( 'elementor/fonts/groups', [ $this, 'register_fonts_groups' ] );
        add_filter( 'elementor/fonts/additional_fonts', [ $this, 'register_fonts_in_control' ] );
        add_action( 'elementor/fonts/print_font_links/' . self::TYPEKIT, [ $this, 'print_typekit_font_link' ] );

        //Inline css
        add_filter( 'uicore_frontent_css', [ $this, 'print_custom_font_link' ] );

        //Theme Style Button Selectors fix
        add_action( 'elementor/element/kit/section_buttons/after_section_end', [$this, 'override_theme_style_button_control'], 20, 2);

        //Theme Style Container Width
        add_action( 'elementor/element/kit/section_settings-layout/after_section_end', [$this, 'override_theme_style_container_width_control'], 20, 2);

        // WPML String Translation plugin exist check
        if ( defined( 'WPML_ST_VERSION' ) ) {

            if ( class_exists( 'WPML_Elementor_Module_With_Items' ) ) {
                $this->load_wpml_modules();
            }

            add_filter( 'wpml_elementor_widgets_to_translate', [$this, 'add_translatable_nodes'] );
        }

    }

    function load_wpml_modules()
    {
        require_once( UICORE_INCLUDES. '/extra/compatibility/class-wpml-ui-highlighted-text.php');
    }

    function add_translatable_nodes( $nodes_to_translate )
    {
        $nodes_to_translate[ 'highlighted-text' ] = [
			'conditions' => [ 'widgetType' => 'highlighted-text' ],
			'fields'     => [],
			'integration-class' => 'WPML_UI_HighlightedText',
		];
        return $nodes_to_translate;
    }

     /**
     * Change Theme stylle Button selector classes
     *
     * @param \Elementor\Controls_Stack $element
     * @param string $section_id
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 1.2.3
     */
	public function override_theme_style_container_width_control( Controls_Stack $element, $section_id ) {
        $element->update_responsive_control(
			'container_width',
			array(
				'selectors' => [
					'.elementor-section.elementor-section-boxed nav.elementor-container,
                    .elementor-section.elementor-section-boxed > .elementor-container, .uicore-ham-creative .uicore-navigation-content,
                    .container-width .uicore-megamenu > .elementor,
                    #wrapper-navbar.elementor-section.elementor-section-boxed .elementor-container .uicore-megamenu .elementor-section.elementor-section-boxed .elementor-container,
                    #wrapper-navbar.elementor-section.elementor-section-full_width .elementor-container .uicore-megamenu .elementor-section.elementor-section-boxed .elementor-container
                    ' => 'max-width: {{SIZE}}{{UNIT}}',
					'.e-container' => '--container-max-width: {{SIZE}}{{UNIT}}',
				],
			)
		);

	}

    private function get_buttons_class($state='default',$style_type='full'){
        $not = array('.bdt-offcanvas-button');
        $all_style_selectors = array(
			'{{WRAPPER}} input[type="button"]',
			'{{WRAPPER}} input[type="submit"]',
			'{{WRAPPER}} .elementor-button.elementor-button',
            '{{WRAPPER}} .elementor-button:not('.implode('):not(',$not).')',  //maybe not
            '{{WRAPPER}} .bdt-button-primary',
            '{{WRAPPER}} .bdt-ep-button',
            'button.metform-btn',
            'button.metform-btn:not(.toggle)',
            '{{WRAPPER}}.woocommerce #respond input#submit',
            '{{WRAPPER}}.woocommerce a.button:not(.add_to_cart_button):not(.product_type_grouped)',
            '{{WRAPPER}} .bdt-callout a.bdt-callout-button',
            '{{WRAPPER}} .bdt-contact-form .elementor-field-type-submit .elementor-button',
            '{{WRAPPER}}.uicore-woo-page a.checkout-button.button.alt',
            '{{WRAPPER}} [type="submit"]',
            '{{WRAPPER}} .tutor-button',
            '{{WRAPPER}} .tutor-login-form-wrap input[type="submit"]',
            '{{WRAPPER}} .wp-block-button__link',
		);
        $no_padding_selectors = array(
			'.uicore-navbar a.uicore-btn',
            '{{WRAPPER}} .widget.woocommerce a.button',
            '{{WRAPPER}} .woocommerce button.button',
            '{{WRAPPER}} .woocommerce div.product form.cart .button',
            '{{WRAPPER}} .woocommerce-cart-form .button',
            '{{WRAPPER}} .woocommerce #respond input#submit.alt',
            '{{WRAPPER}}.woocommerce a.button.alt',
            '{{WRAPPER}}.woocommerce button.button.alt',
            '{{WRAPPER}}.woocommerce input.button.alt'
		);
        $only_hover = array(
            '.uicore-navbar a.uicore-btn',
            '.uicore-transparent:not(.uicore-scrolled) .uicore-btn.uicore-inverted'
        );
        if($style_type === 'full'){
            $selectors = \array_merge($all_style_selectors,$no_padding_selectors);
        }else{
            $selectors = $all_style_selectors;
        }

        if($state != 'default'){
            $selectors = \array_merge($selectors,$only_hover);
            foreach ($selectors as $selector){
                $new_selector[] = $selector.':hover';
                $new_selector[] = $selector.':focus';
            }
            $selectors = $new_selector;
        }

        return implode( ',', $selectors );

    }

    /**
     * Change Theme stylle Button selector classes
     *
     * @param \Elementor\Controls_Stack $element
     * @param string $section_id
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 1.2.3
     */
	public function override_theme_style_button_control( Controls_Stack $element, $section_id ) {

        $controls_manager = Plugin::$instance->controls_manager;
        $typographyGroup = $controls_manager->get_control_groups('typography');
        foreach ($typographyGroup->get_fields() as $field_key => $field) {
            $control_id = "button_typography_{$field_key}";
            $old_control_data = $controls_manager->get_control_from_stack($element->get_unique_name(), $control_id);
            if($control_id != 'button_typography_font_size'){
                $element->update_control($control_id, [
                    'selectors'  => [
                        $this->get_buttons_class() => isset($old_control_data['selector_value']) ? $old_control_data['selector_value'] : reset($old_control_data['selectors']),
                    ]
                ]);
            }else{
               $element->update_responsive_control(
                    'button_typography_font_size',
                    array(
                        'selectors' => array(
                            $this->get_buttons_class() => 'font-size: {{SIZE}}{{UNIT}};',
                        ),
                    )
                );
            }
        }

        $element->update_control(
			'button_text_color',
			array(
				'selectors' => array(
					$this->get_buttons_class() => 'color: {{VALUE}};',
				),
			)
		);
		$element->update_control(
			'button_background_color',
			array(
				'selectors' => array(
					$this->get_buttons_class() => 'background-color: {{VALUE}};',
				),
			)
		);
        $element->update_control(
            'button_box_shadow',
            array(
                'selector' => $this->get_buttons_class()
            )
        );
        $element->update_control(
            'button_border',
            array(
                'selector' => $this->get_buttons_class()
            )
        );
        $typographyGroup = $controls_manager->get_control_groups('border');

        foreach ($typographyGroup->get_fields() as $field_key => $field) {
            $control_id = "button_border_{$field_key}";
            $old_control_data = $controls_manager->get_control_from_stack($element->get_unique_name(), $control_id);
            $element->update_control($control_id, [
                'selectors'  => [
                    $this->get_buttons_class() => reset($old_control_data['selectors']),
                ]
            ]);
        }
		$border_radius_class = $this->get_buttons_class() . ', .quantity input, .coupon input';
        $element->update_control(
			'button_border_radius',
			array(
				'selectors' => array(
					$border_radius_class => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);
        $element->update_control(
			'button_hover_text_color',
			array(
				'selectors' => array(
					$this->get_buttons_class('hover') => 'color: {{VALUE}};',
				),
			)
		);
		$element->update_control(
			'button_hover_background_color',
			array(
				'selectors' => array(
					$this->get_buttons_class('hover') => 'background-color: {{VALUE}};',
				),
			)
		);
        $element->update_control(
            'button_hover_box_shadow',
            array(
                'selector' => $this->get_buttons_class('hover')
            )
        );
        $element->update_control(
            'button_hover_border',
            array(
                'selector' => $this->get_buttons_class('hover')
            )
        );
        $typographyGroup = $controls_manager->get_control_groups('border');

        foreach ($typographyGroup->get_fields() as $field_key => $field) {
            $control_id = "button_hover_border_{$field_key}";
            $old_control_data = $controls_manager->get_control_from_stack($element->get_unique_name(), $control_id);
            $element->update_control($control_id, [
                'selectors'  => [
                    $this->get_buttons_class('hover') => reset($old_control_data['selectors']),
                ]
            ]);
        }
        $element->update_control(
			'button_hover_border_radius',
			array(
				'selectors' => array(
					$this->get_buttons_class('hover') => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);
        $element->update_responsive_control(
			'button_padding',
			array(
				'selectors' => array(
					$this->get_buttons_class('default','no_padding') => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

	}

    public static function update_globals_from_elementor($check, $object_id, $meta_key, $value, $prev_value)
    {
        $kit_id = get_option('elementor_active_kit');
        if ($object_id == $kit_id && $meta_key == '_elementor_page_settings') {
            //settings prefix
            $prefix = Settings::get_prefix();
            $current_settings = Settings::current_settings();

            $not_uicore = false;
            $the_filter = current_filter();

            //Global colors
            $global_colors = [
                [
                    'option' => 'pColor',
                    'id' => 'uicore_primary',
                    'name' => 'Primary',
                ],
                [
                    'option' => 'sColor',
                    'id' => 'uicore_secondary',
                    'name' => 'Secondary',
                ],
                [
                    'option' => 'aColor',
                    'id' => 'uicore_accent',
                    'name' => 'Accent',
                ],
                [
                    'option' => 'hColor',
                    'id' => 'uicore_headline',
                    'name' => 'Headline',
                ],
                [
                    'option' => 'bColor',
                    'id' => 'uicore_body',
                    'name' => 'Body',
                ],
                [
                    'option' => 'dColor',
                    'id' => 'uicore_dark',
                    'name' => 'Dark Neutral',
                ],
                [
                    'option' => 'lColor',
                    'id' => 'uicore_light',
                    'name' => 'Light Neutral',
                ],
            ];
            if ($value['system_colors'][0]['_id'] !== 'uicore_primary') {
                $not_uicore = true;
            }

            foreach ($global_colors as $id => $color) {
                //let's first check if they are uicore_globals else ovewride them
                if (!$not_uicore) {
                    $to_set = $value['system_colors'][$id]['color'];
                    update_option($prefix . $color['option'], $to_set);
                } else {
                    $value['system_colors'][$id]['color'] = $current_settings[$color['option']];
                    $value['system_colors'][$id]['_id'] = $color['id'];
                    $value['system_colors'][$id]['name'] = $color['name'];
                }
            }

            //Global Fonts
            $global_fonts = [
                [
                    'option' => 'pFont',
                    'id' => 'uicore_primary',
                    'name' => 'Primary',
                ],
                [
                    'option' => 'sFont',
                    'id' => 'uicore_secondary',
                    'name' => 'Secondary',
                ],
                [
                    'option' => 'tFont',
                    'id' => 'uicore_text',
                    'name' => 'Text',
                ],
                [
                    'option' => 'aFont',
                    'id' => 'uicore_accent',
                    'name' => 'Accent',
                ],
            ];
            foreach ($global_fonts as $id => $font) {
                //let's first check if they are uicore_globals else ovewride them
                if (!$not_uicore) {
                    $to_set = [
                        'f' => $value['system_typography'][$id]['typography_font_family'],
                        'st' => $value['system_typography'][$id]['typography_font_weight'],
                    ];
                    update_option($prefix . $font['option'], $to_set);
                } else {
                    $value['system_typography'][$id] = [
                        '_id' => $font['id'],
                        'title' => $font['name'],
                        'typography_font_family' => $current_settings[$font['option']]['f'],
                        'typography_font_weight' => $current_settings[$font['option']]['st'],
                        'typography_typography' => 'custom',
                    ];
                }
            }

            //Buttons are not handled in both ways vbeacause we are forceing to use only UiCore Impl.
            // Settings::update_globals_from_uicore()

            if ($not_uicore) {
                Elementor::uicore_meta_trick($the_filter, $object_id, $meta_key, $value, $prev_value);
                $check = $value;
            }
        } elseif ($object_id == $kit_id && $meta_key == '_elementor_css') {
            $elementor_settings = get_post_meta($kit_id, '_elementor_page_settings', true);

            if (!$elementor_settings) {
                Settings::update_globals_from_uicore();
            }
        }

        return $check;
    }

    static function uicore_meta_trick(
        $filter,
        $object_id,
        $meta_key,
        $meta_value,
        $unique_or_prev_value,
        $old_value = null
    ) {

        // Remove the filters and save the new meta value. Make sure that
        // the priority and number of arguments are exactly the same as
        // when you added the filters.
        remove_filter('add_post_metadata', ['\UiCore\Elementor', 'update_globals_from_elementor'], 20, 5);
        remove_filter('update_post_metadata', ['\UiCore\Elementor', 'update_globals_from_elementor'], 20, 5);

        // Manually save the meta data.
        if ('add_post_metadata' === $filter) {
            add_metadata('post', $object_id, $meta_key, $meta_value, $unique_or_prev_value);
        } elseif ('update_post_metadata' === $filter) {
            update_metadata('post', $object_id, $meta_key, $meta_value, $unique_or_prev_value);
        }

        // // Finally, re-add the filters.
        add_filter('add_post_metadata', ['\UiCore\Elementor', 'update_globals_from_elementor'], 20, 5);
        add_filter('update_post_metadata', ['\UiCore\Elementor', 'update_globals_from_elementor'], 20, 5);

        //just to be sure
        \Elementor\Plugin::$instance->files_manager->clear_cache();
        Settings::clear_cache();
    }

    function custom_post_elementor_support()
    {
        //if exists, assign to $cpt_support var
        $cpt_support = get_option('elementor_cpt_support');

        //check if option DOESN'T exist in db
        if (!$cpt_support) {
            $cpt_support = ['page', 'post', 'portfolio']; //create array of our default supported post types
            update_option('elementor_cpt_support', $cpt_support); //write it to the database
        }

        //if it DOES exist, but portfolio is NOT defined
        elseif (!in_array('portfolio', $cpt_support)) {
            $cpt_support[] = 'portfolio'; //append to array
            update_option('elementor_cpt_support', $cpt_support); //update database
        }

        //otherwise do nothing, portfolio already exists in elementor_cpt_support option
    }


    /**
     * Add Support For custom location used in Theme Builder
     *
     * @param [type] $elementor_theme_manager
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 1.2.0
     */
    function elementor_locations($elementor_theme_manager)
    {
        $elementor_theme_manager->register_all_core_location();
    }

    /**
     * Add new google fonts to elementor
     *
     * @param [type] $old
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 1.2.0
     */
    function new_google_fonts($old)
    {
        return array_merge($old, ["Space Grotesk"=>'googlefonts']);
    }

    public function register_fonts_groups( $font_groups )
    {
		$new_groups = [
            self::CUSTOM =>__( 'UiCore Custom', 'uicore-framework' ),
            self::TYPEKIT =>__( 'UiCore Typekit', 'uicore-framework' ),
        ];
		return array_merge( $new_groups, $font_groups );
	}


    public function register_fonts_in_control( $font_groups )
    {
        $uicore_custom = Data::get_custom_fonts('simple',self::CUSTOM);
        $uicore_typekit = Data::get_typekit_fonts('simple',self::TYPEKIT);

        $new_groups = array_merge($uicore_custom, $uicore_typekit);

		return array_merge( $new_groups, $font_groups );
    }

    function print_custom_font_link( $font )
    {
        $fonts = Helper::get_option('customFonts');
        $css = '';
        if(\is_array($fonts)){
            foreach($fonts as $font){
                $css .= $this->get_font_face_css($font);
            }
        }
        return $css;
    }
    function get_font_face_css( $font )
    {
            $css = '';
			foreach ( $font['variants'] as $key => $variant ) {

                $links = $variant['src'];

                //Font Style
                if (strpos($variant['type'], 'italic') !== false) {
                    $font_style = 'italic';
                } else {
                    $font_style = 'normal';
                }
                //Font Weight
                if ((strpos($variant['type'], 'regular') !== false) ||(strpos($variant['type'], 'normal') !== false)) {
                    $font_weight = '400';
                } else {
                    if (strlen(str_replace('italic', '', $variant['type'])) < 2) {
                        $font_weight = 'normal';
                    } else {
                        $font_weight = str_replace('italic', '', $variant['type']);
                    }
                }

				$css  .= ' @font-face { font-family:"' . esc_attr( $font['family'] ) . '";';
				$css .= 'src:';
				$arr  = array();
				if ( $links['woff'] ) {
					$arr[] = 'url("' . esc_url( $links['woff'] ) . '") format(\'woff\')';
				}
				if ( $links['ttf'] ) {
					$arr[] = 'url("' . esc_url( $links['ttf'] ) . '") format(\'truetype\')';
				}
				if ( $links['eot'] ) {
					$arr[] = 'url(' . esc_url( $links['eot'] ) . ") format('opentype')";
				}
				if ( $links['svg'] ) {
					$arr[] = 'url(' . esc_url( $links['svg'] ) . '#' . esc_attr( strtolower( str_replace( ' ', '_', $font['family'] ) ) ) . ") format('svg')";
				}
				$css .= join( ', ', $arr );
				$css .= ';';
				$css .= 'font-display:auto;font-style:'.$font_style.';font-weight:'.$font_weight.';';
				$css .= '}';
			}

			return $css;

    }

    function print_typekit_font_link( $font )
    {
        $kit_url = sprintf( self::TYPEKIT_FONTS_LINK, $this->get_typekit_kit_id() );
        echo '<link rel="stylesheet" type="text/css" href="' . $kit_url . '">';
    }

    function get_typekit_kit_id(){
        $typekit =  Helper::get_option('typekit', false );
        if(isset($typekit['id'])){
            return $typekit['id'];
        }else{
            return;
        }
    }

    function add_custom_icons($tabs = [])
    {
        $tabs['uicore-icons'] = [
            'name' => 'uicore-icons',
            'label' => __('Themify Icons', 'uicore-framework'),
            'url' => UICORE_ASSETS . '/fonts/themify-icons.css',
            'enqueue' => [UICORE_ASSETS . '/fonts/themify-icons.css'],
            'prefix' => 'ti-',
            'displayPrefix' => 'ti',
            'labelIcon' => 'fas fa-folder-open',
            'ver' => '1.0.0',
            'icons' => [
                'wand',
                'volume',
                'user',
                'unlock',
                'unlink',
                'trash',
                'thought',
                'target',
                'tag',
                'tablet',
                'star',
                'spray',
                'signal',
                'shopping-cart',
                'shopping-cart-full',
                'settings',
                'search',
                'zoom-in',
                'zoom-out',
                'cut',
                'ruler',
                'ruler-pencil',
                'ruler-alt',
                'bookmark',
                'bookmark-alt',
                'reload',
                'plus',
                'pin',
                'pencil',
                'pencil-alt',
                'paint-roller',
                'paint-bucket',
                'na',
                'mobile',
                'minus',
                'medall',
                'medall-alt',
                'marker',
                'marker-alt',
                'arrow-up',
                'arrow-right',
                'arrow-left',
                'arrow-down',
                'lock',
                'location-arrow',
                'link',
                'layout',
                'layers',
                'layers-alt',
                'key',
                'import',
                'image',
                'heart',
                'heart-broken',
                'hand-stop',
                'hand-open',
                'hand-drag',
                'folder',
                'flag',
                'flag-alt',
                'flag-alt-2',
                'eye',
                'export',
                'exchange-vertical',
                'desktop',
                'cup',
                'crown',
                'comments',
                'comment',
                'comment-alt',
                'close',
                'clip',
                'angle-up',
                'angle-right',
                'angle-left',
                'angle-down',
                'check',
                'check-box',
                'camera',
                'announcement',
                'brush',
                'briefcase',
                'bolt',
                'bolt-alt',
                'blackboard',
                'bag',
                'move',
                'arrows-vertical',
                'arrows-horizontal',
                'fullscreen',
                'arrow-top-right',
                'arrow-top-left',
                'arrow-circle-up',
                'arrow-circle-right',
                'arrow-circle-left',
                'arrow-circle-down',
                'angle-double-up',
                'angle-double-right',
                'angle-double-left',
                'angle-double-down',
                'zip',
                'world',
                'wheelchair',
                'view-list',
                'view-list-alt',
                'view-grid',
                'uppercase',
                'upload',
                'underline',
                'truck',
                'timer',
                'ticket',
                'thumb-up',
                'thumb-down',
                'text',
                'stats-up',
                'stats-down',
                'split-v',
                'split-h',
                'smallcap',
                'shine',
                'shift-right',
                'shift-left',
                'shield',
                'notepad',
                'server',
                'quote-right',
                'quote-left',
                'pulse',
                'printer',
                'power-off',
                'plug',
                'pie-chart',
                'paragraph',
                'panel',
                'package',
                'music',
                'music-alt',
                'mouse',
                'mouse-alt',
                'money',
                'microphone',
                'menu',
                'menu-alt',
                'map',
                'map-alt',
                'loop',
                'location-pin',
                'list',
                'light-bulb',
                'Italic',
                'info',
                'infinite',
                'id-badge',
                'hummer',
                'home',
                'help',
                'headphone',
                'harddrives',
                'harddrive',
                'gift',
                'game',
                'filter',
                'files',
                'file',
                'eraser',
                'envelope',
                'download',
                'direction',
                'direction-alt',
                'dashboard',
                'control-stop',
                'control-shuffle',
                'control-play',
                'control-pause',
                'control-forward',
                'control-backward',
                'cloud',
                'cloud-up',
                'cloud-down',
                'clipboard',
                'car',
                'calendar',
                'book',
                'bell',
                'basketball',
                'bar-chart',
                'bar-chart-alt',
                'back-right',
                'back-left',
                'arrows-corner',
                'archive',
                'anchor',
                'align-right',
                'align-left',
                'align-justify',
                'align-center',
                'alert',
                'alarm-clock',
                'agenda',
                'write',
                'window',
                'widgetized',
                'widget',
                'widget-alt',
                'wallet',
                'video-clapper',
                'video-camera',
                'vector',
                'themify-logo',
                'themify-favicon',
                'themify-favicon-alt',
                'support',
                'stamp',
                'split-v-alt',
                'slice',
                'shortcode',
                'shift-right-alt',
                'shift-left-alt',
                'ruler-alt-2',
                'receipt',
                'pin2',
                'pin-alt',
                'pencil-alt2',
                'palette',
                'more',
                'more-alt',
                'microphone-alt',
                'magnet',
                'line-double',
                'line-dotted',
                'line-dashed',
                'layout-width-full',
                'layout-width-default',
                'layout-width-default-alt',
                'layout-tab',
                'layout-tab-window',
                'layout-tab-v',
                'layout-tab-min',
                'layout-slider',
                'layout-slider-alt',
                'layout-sidebar-right',
                'layout-sidebar-none',
                'layout-sidebar-left',
                'layout-placeholder',
                'layout-menu',
                'layout-menu-v',
                'layout-menu-separated',
                'layout-menu-full',
                'layout-media-right-alt',
                'layout-media-right',
                'layout-media-overlay',
                'layout-media-overlay-alt',
                'layout-media-overlay-alt-2',
                'layout-media-left-alt',
                'layout-media-left',
                'layout-media-center-alt',
                'layout-media-center',
                'layout-list-thumb',
                'layout-list-thumb-alt',
                'layout-list-post',
                'layout-list-large-image',
                'layout-line-solid',
                'layout-grid4',
                'layout-grid3',
                'layout-grid2',
                'layout-grid2-thumb',
                'layout-cta-right',
                'layout-cta-left',
                'layout-cta-center',
                'layout-cta-btn-right',
                'layout-cta-btn-left',
                'layout-column4',
                'layout-column3',
                'layout-column2',
                'layout-accordion-separated',
                'layout-accordion-merged',
                'layout-accordion-list',
                'ink-pen',
                'info-alt',
                'help-alt',
                'headphone-alt',
                'hand-point-up',
                'hand-point-right',
                'hand-point-left',
                'hand-point-down',
                'gallery',
                'face-smile',
                'face-sad',
                'credit-card',
                'control-skip-forward',
                'control-skip-backward',
                'control-record',
                'control-eject',
                'comments-smiley',
                'brush-alt',
                'youtube',
                'vimeo',
                'twitter',
                'time',
                'tumblr',
                'skype',
                'share',
                'share-alt',
                'rocket',
                'pinterest',
                'new-window',
                'microsoft',
                'list-ol',
                'linkedin',
                'layout-sidebar-2',
                'layout-grid4-alt',
                'layout-grid3-alt',
                'layout-grid2-alt',
                'layout-column4-alt',
                'layout-column3-alt',
                'layout-column2-alt',
                'instagram',
                'google',
                'github',
                'flickr',
                'facebook',
                'dropbox',
                'dribbble',
                'apple',
                'android',
                'save',
                'save-alt',
                'yahoo',
                'wordpress',
                'vimeo-alt',
                'twitter-alt',
                'tumblr-alt',
                'trello',
                'stack-overflow',
                'soundcloud',
                'sharethis',
                'sharethis-alt',
                'reddit',
                'pinterest-alt',
                'microsoft-alt',
                'linux',
                'jsfiddle',
                'joomla',
                'html5',
                'flickr-alt',
                'email',
                'drupal',
                'dropbox-alt',
                'css3',
                'rss',
                'rss-alt',
            ],
        ];

        return $tabs;
    }
}
new Elementor();
