<?php
namespace UiCore;

use Elementor\Controls_Manager;
use Elementor\Controls_Stack;

defined('ABSPATH') || exit();

/**
 *  Elementor extra features
 */
class Extender
{
    public function __construct()
    {
		//Floating Widget
        add_action( 'elementor/element/before_section_end', [ $this, 'register_controls_for_float' ], 10, 3 );

		 //Fluid Gradient extender
		 add_action( 'elementor/element/section/section_advanced/before_section_start', [$this, 'fluid_gradient_controls'] );
		 add_action( 'elementor/frontend/section/before_render', [$this, 'fluid_gradient_render'],10, 1 );
		 add_action( 'elementor/section/print_template', [$this, 'fluid_gradient_print_template'],10, 1 );
    }

    public function get_name() {
		return 'uicore_extender';
	}


	public function fluid_gradient_print_template($template){
			$template = 	'
			<#
			if ( settings.section_fluid_on === \'yes\' ) {
			#>
				<div class="ui-fluid-gradient-wrapper">
					<div class="ui-fluid-gradient"></div>
				</div>
			<# } #>
				'. $template ;
		return $template;

	}

	public function fluid_gradient_render($section) {
		$active = $section->get_settings('section_fluid_on');

		if ('yes' === $active) {
			$section->add_render_attribute('_wrapper', 'class', 'has-ui-fluid-gradient');

			?>
				<div class="ui-fluid-gradient-pre">
					<div class="ui-fluid-gradient"></div>
				</div>
			<?php
		}
	}

	 /**
     * Fluid Gradient extender
     *
     * @param \Elementor\Controls_Stack $element
     * @param string $section_id
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 3.2.1
     */
    function fluid_gradient_controls(Controls_Stack $section)
    {
		$section->start_injection(
			[
				'type' => 'control',
				'at'   => 'after',
				'of'   => 'background_background',
			] );

		$section->add_control(
			'section_fluid_on',
			[
				'label'        => esc_html__( 'Fluid Gradient', 'uicore-framework' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'return_value' => 'yes',
				'description'  => esc_html__( 'Enable Fluid Gradient background.', 'uicore-framework' ),
				'separator'    => [ 'before' ],
				'render_type'  => 'template',
				'frontend_available' => false,
			]
		);

		$section->add_control(
			'uicore_fluid_animation',
			[
				'label' => __( 'Animation', 'uicore-framework' ),
				'type' => Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					'' => __( 'None', 'uicore-framework' ),
					'ui-fluid-animation-1' => __( 'Style 1', 'uicore-framework' ),
					'ui-fluid-animation-2' => __( 'Style 2', 'uicore-framework' ),
					'ui-fluid-animation-3' => __( 'Style 3', 'uicore-framework' ),
					'ui-fluid-animation-4' => __( 'Style 4', 'uicore-framework' ),
					'ui-fluid-animation-5' => __( 'Style 5', 'uicore-framework' ),
				],
				'condition' => array(
                    'section_fluid_on' => 'yes',
                  ),
				'prefix_class' => ' ',
			]
        );

		$section->add_control(
			'ui_fluid_opacity',
			[
				'label' => __( 'Opacity', 'uicore-framework' ),
				'type' => Controls_Manager::SLIDER,
				'condition' => [
					'section_fluid_on' => 'yes',
				],
				'range' => [
					'px' => [
						'min'  => 0.05,
						'max'  => 1,
						'step' => 0.05,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .ui-fluid-gradient-wrapper' => 'opacity: {{SIZE}}',
				],
			]
        );

		$section->add_control(
			'section_fluid_color_1',
			[
				'label'     => esc_html__( 'Color 1', 'uicore-framework' ),
				'type'      => Controls_Manager::COLOR,
				'condition' => [
					'section_fluid_on' => 'yes',
				],
				'selectors' => [
					'{{WRAPPER}}' => '--ui-fluid-1: {{VALUE}}',
				],
			]
		);
		$section->add_control(
			'section_fluid_color_2',
			[
				'label'     => esc_html__( 'Color 2', 'uicore-framework' ),
				'type'      => Controls_Manager::COLOR,
				'condition' => [
					'section_fluid_on' => 'yes',
				],
				'selectors' => [
					'{{WRAPPER}}' => '--ui-fluid-2: {{VALUE}}',
				],
			]
		);
		$section->add_control(
			'section_fluid_color_3',
			[
				'label'     => esc_html__( 'Color 3', 'uicore-framework' ),
				'type'      => Controls_Manager::COLOR,
				'condition' => [
					'section_fluid_on' => 'yes',
				],
				'selectors' => [
					'{{WRAPPER}}' => '--ui-fluid-3: {{VALUE}}',
				],
			]
		);
		$section->add_control(
			'section_fluid_color_4',
			[
				'label'     => esc_html__( 'Color 4', 'uicore-framework' ),
				'type'      => Controls_Manager::COLOR,
				'condition' => [
					'section_fluid_on' => 'yes',
				],
				'selectors' => [
					'{{WRAPPER}}' => '--ui-fluid-4: {{VALUE}}',
				],
			]
		);

		$section->end_injection();
    }


    function register_controls_for_float($widget, $widget_id, $args)
    {
        static $widgets = [
			'section_effects', /* Section */
		];

		if ( ! in_array( $widget_id, $widgets ) ) {
			return;
		}

        $widget->add_control(
			'uicore_enable_float',
			[
				'label'        => esc_html__( 'Enable floating animation', 'uicore-framework' ),
				'description'  => esc_html__( 'Add a looping up-down animation.' , 'uicore-framework' ),
				'type'         => Controls_Manager::SWITCHER,
				'separator'    => 'before',
                'default' => '',
				'prefix_class' => 'ui-float-',
				'return_value' => 'widget',
                'frontend_available' => false,
			]
		);
		$widget->add_control(
			'uicore_float_size',
			[
				'label' => __( 'Floating height', 'uicore-framework' ),
				'type' => Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					'ui-float-s' => __( 'Small', 'uicore-framework' ),
					'' => __( 'Default', 'uicore-framework' ),
					'ui-float-l' => __( 'Large', 'uicore-framework' ),
				],
				'condition' => array(
                    'uicore_enable_float' => 'widget',
                  ),
				'prefix_class' => ' ',
			]
        );
    }

}

new Extender;
