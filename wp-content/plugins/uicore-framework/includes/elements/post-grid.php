<?php
namespace UiCore;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
defined('ABSPATH') || exit();

/**
 * Scripts and Styles Class
 */
class UiCorePostGrid extends Widget_Base
{
    public function __construct($data = [], $args = null)
    {
        parent::__construct($data, $args);
    }

    public function get_name()
    {
        return 'uicore-post-grid';
    }
    public function get_categories()
    {
        return ['uicore'];
    }

    public function get_title()
    {
        return __('Post Grid', 'uicore-framework');
    }

    public function get_icon()
    {
        return 'eicon-gallery-grid';
    }

    public function get_keywords()
    {
        return ['post', 'grid', 'blog', 'recent', 'news'];
    }

    public function on_import($element)
    {
        if (!get_post_type_object($element['settings']['posts-filter_post_type'])) {
            $element['settings']['posts-filter_post_type'] = 'post';
        }

        return $element;
    }

    public function on_export($element)
    {
        $element = Group_Control_Posts::on_export_remove_setting_from_element($element, 'posts-filter');
        return $element;
    }

    public function get_query()
    {
        return $this->_query;
    }

    protected function _register_controls()
    {
        $default_columns = Helper::get_option('blog_col');

        $this->start_controls_section('section_post_grid_def', [
            'label' => esc_html__('Query', 'uicore-framework'),
        ]);

        $this->add_group_control('posts-filter', [
            'name' => 'posts-filter',
            'label' => esc_html__('Posts', 'uicore-framework'),
        ]);

        $this->add_control('item_limit', [
            'label' => esc_html__('Item Limit', 'uicore-framework'),
            'type' => Controls_Manager::SLIDER,
            'reder_type' => 'template',
            'range' => [
                'px' => [
                    'min' => 1,
                    'max' => 30,
                ],
            ],
            'default' => [
                'size' => 3,
            ],
        ]);
        $this->add_control('col_number', [
            'label' => esc_html__('Columns Number', 'uicore-framework'),
            'type' => Controls_Manager::SLIDER,
            'reder_type' => 'template',
            'range' => [
                'px' => [
                    'min' => 1,
                    'max' => 4,
                ],
            ],
            'default' => [
                'size' => $default_columns,
            ],
        ]);

        $this->add_control(
			'box_style',
			[
				'label' => __( 'Item Style', 'uicore-framework' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'default',
				'options' => [
					'default'  => __( 'Default', 'uicore-framework' ),
					'simple' => __( 'Simple', 'uicore-framework' ),
					'boxed' => __( 'Boxed', 'uicore-framework' ),
					'boxed-creative' => __( 'Boxed Creative', 'uicore-framework' ),
					'cover' => __( 'Cover', 'uicore-framework' ),
				],
                'condition' => array(
                    'posts-filter_post_type' => 'post',
                  ),
			]
		);

        $this->end_controls_section();
    }

    public function query_posts($posts_per_page, $type = null)
    {
        $query_args = Elements::get_query_args('posts-filter', $this->get_settings());

		if($type === 'portfolio') {
			$query_args['orderby'] = 'menu_order date';
		}

        $query_args['posts_per_page'] = $posts_per_page;

        $this->_query = new \WP_Query($query_args);
    }

    protected function render()
    {
        $settings = $this->get_settings();
        $id = $this->get_id();

        $col = $settings['col_number']['size'];
        $type = $settings['posts-filter_post_type'];

        $blog_item_style = $settings['box_style'];
        if($type != 'portfolio'){

            $type = str_replace(' ', '-', $blog_item_style);
            if( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
                $content = '';
                if($blog_item_style != 'default'){
                    $content .= \file_get_contents(UICORE_ASSETS . '/css/blog/item-style-'.$type.'.css');
                }
                $content .= \file_get_contents( Assets::get_global('uicore-blog.css') );
                ?>
                <style>
                    <?php echo $content; ?>
                </style>
                <?php
            }
            else{
				if($blog_item_style != 'default'){
                	wp_enqueue_style('uicore_blog_grid_'.$type, UICORE_ASSETS . '/css/blog/item-style-'.$type.'.css', UICORE_VERSION);
				}
            }
        }

        $this->query_posts($settings['item_limit']['size'], $type);
        $wp_query = $this->get_query();

        if (!$wp_query->found_posts) {
            echo 'No Posts Found!';
            return;
        }


        if ($type === 'portfolio') {
            if( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
                $content = '';
                $content .= \file_get_contents( Assets::get_global('uicore-portfolio.css') );
                ?>
                <style>
                    <?php echo $content; ?>
                </style>
                <?php
            }
            if(!class_exists('\UiCore\Portfolio\Frontend')){
                require_once UICORE_INCLUDES . '/portfolio/class-template.php';
                require_once UICORE_INCLUDES . '/portfolio/class-frontend.php';
            }
            Portfolio\Frontend::frontend_css(true);
            $portfolio = new Portfolio\Template('display');
            $portfolio->portfolio_layout($wp_query, null, $col);
        } else {
            if(!class_exists('\UiCore\Blog\Frontend')){
                require_once UICORE_INCLUDES . '/blog/class-template.php';
                require_once UICORE_INCLUDES . '/blog/class-frontend.php';
            }
            Blog\Frontend::frontend_css(true);
            $blog = new Blog\Template('display');
            $blog->blog_layout($wp_query, null, $col);
        }

    }
}
\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new UiCorePostGrid());
