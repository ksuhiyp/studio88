<?php
namespace UiCore;

defined('ABSPATH') || exit();

/**
 * Scripts and Styles Class
 */
class Elements
{
    public function __construct()
    {
        add_action('elementor/elements/categories_registered', [$this, 'create_custom_category'], 2);
        add_action('elementor/controls/controls_registered', [$this, 'init_controls']);
        add_action('elementor/widgets/widgets_registered', [$this, 'init_widgets']);
    }

    public function init_widgets()
    {
        require_once UICORE_INCLUDES . '/elements/post-grid.php';
        require_once UICORE_INCLUDES . '/elements/highlighted-text.php';
    }

    public function init_controls()
    {
        require_once UICORE_INCLUDES . '/elements/class-controls.php';
    }

    function create_custom_category($elements_manager)
    {
        $elements_manager->add_category('uicore', [
            'title' => __('UiCore', 'uicore'),
            'icon' => 'fa fa-plug',
        ]);
    }

    public static function get_query_args($control_id, $settings)
    {
        $defaults = [
            $control_id . '_post_type' => 'post',
            $control_id . '_posts_ids' => [],
            'orderby' => 'date',
            'order' => 'desc',
            'posts_per_page' => 3,
            'offset' => 0,
        ];

        $settings = wp_parse_args($settings, $defaults);

        $post_type = $settings[$control_id . '_post_type'];

        if (get_query_var('paged')) {
            $paged = get_query_var('paged');
        } elseif (get_query_var('page')) {
            $paged = get_query_var('page');
        } else {
            $paged = 1;
        }

        $query_args = [
            'orderby' => $settings['orderby'],
            'order' => $settings['order'],
            'ignore_sticky_posts' => 0,
            'post_status' => 'publish', // Hide drafts/private posts for admins
            'paged' => $paged,
        ];

        $query_args['post_type'] = $post_type;
        $query_args['posts_per_page'] = $settings['posts_per_page'];
        $query_args['tax_query'] = [];

        $taxonomies = get_object_taxonomies($post_type, 'objects');

        foreach ($taxonomies as $object) {
            $setting_key = $control_id . '_' . $object->name . '_ids';

            if (!empty($settings[$setting_key])) {
                $query_args['tax_query'][] = [
                    'taxonomy' => $object->name,
                    'field' => 'term_id',
                    'terms' => $settings[$setting_key],
                ];
            }
        }

        return $query_args;
    }
}
new Elements();
