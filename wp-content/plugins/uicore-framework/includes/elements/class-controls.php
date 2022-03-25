<?php
namespace UiCore;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Base;

defined('ABSPATH') || exit();

/**
 * Scripts and Styles Class
 */
class Group_Control_Posts extends Group_Control_Base
{
    protected static $fields;

    public static function get_type()
    {
        return 'posts-filter';
    }

    public static function on_export_remove_setting_from_element($element, $control_id)
    {
        unset($element['settings'][$control_id . '_posts_ids']);
        unset($element['settings'][$control_id . '_authors']);

        foreach (self::get_post_types() as $post_type => $label) {
            $taxonomy_filter_args = [
                'show_in_nav_menus' => true,
                'object_type' => [$post_type],
            ];

            $taxonomies = get_taxonomies($taxonomy_filter_args, 'objects');

            foreach ($taxonomies as $taxonomy => $object) {
                unset($element['settings'][$control_id . '_' . $taxonomy . '_ids']);
            }
        }

        return $element;
    }

    protected function init_fields()
    {
        $fields = [];

        $fields['post_type'] = [
            'label' => _x('Source', 'uicore-brisk'),
            'type' => Controls_Manager::SELECT,
        ];

        return $fields;
    }

    protected function prepare_fields($fields)
    {
        $args = $this->get_args();

        $post_types = self::get_post_types($args);

        $post_types_options = $post_types;

        //$post_types_options['by_id'] = __( 'Manual Selection', 'bdthemes-element-pack' );
        //$post_types_options['current_query'] = __( 'Current Query', 'bdthemes-element-pack' );

        $fields['post_type']['options'] = $post_types_options;

        $fields['post_type']['default'] = key($post_types);

        $taxonomy_filter_args = [
            'show_in_nav_menus' => true,
        ];

        if (!empty($args['post_type'])) {
            $taxonomy_filter_args['object_type'] = [$args['post_type']];
        }

        $taxonomies = get_taxonomies($taxonomy_filter_args, 'objects');

        foreach ($taxonomies as $taxonomy => $object) {
            $taxonomy_args = [
                'label' => $object->label,
                'type' => 'query',
                'label_block' => true,
                'multiple' => true,
                'object_type' => $taxonomy,
                'options' => [],
                'condition' => [
                    'post_type' => $object->object_type,
                ],
            ];

            $options = [];

            $taxonomy_args['type'] = Controls_Manager::SELECT2;

            $terms = get_terms([
                'taxonomy' => $object->name,
                'hide_empty' => false,
            ]);

            foreach ($terms as $term) {
                $options[$term->term_id] = $term->name;
            }

            $taxonomy_args['options'] = $options;

            $fields[$taxonomy . '_ids'] = $taxonomy_args;
        }

        return parent::prepare_fields($fields);
    }

    private static function get_post_types($args = [])
    {
        $post_type_args = [
            'show_in_nav_menus' => true,
        ];

        if (!empty($args['post_type'])) {
            $post_type_args['name'] = $args['post_type'];
        }

        $_post_types = get_post_types($post_type_args, 'objects');

        $post_types = [];

        foreach ($_post_types as $post_type => $object) {
            $post_types[$post_type] = $object->label;
        }
        unset($post_types['page']);
        unset($post_types['e-landing-page']);
        unset($post_types['uicore-tb']);
        return $post_types;
    }

    protected function get_default_options()
    {
        return [
            'popover' => false,
        ];
    }
}
\Elementor\Plugin::$instance->controls_manager->add_group_control('posts-filter', new Group_Control_Posts());
