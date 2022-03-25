<?php
namespace UiCore;
defined('ABSPATH') || exit();

/**
 * Related Post Component
 */
class RelatedPost
{
    function __construct()
    {
        $filter = get_option('uicore_blogs_related_filter', 'category');
        $style = get_option('uicore_blogs_related_style', 'grid');

        if ($style == 'grid') {
            $post_to_get = 3;
        } else {
            $post_to_get = 6;
        }

        $related = $this->get_related($filter, $post_to_get);

        if ($filter == 'category' && $related) {
            $this->display_related($style, $related);
        } elseif ($filter == 'tag' && $related) {
            $this->display_related($style, $related);
        } else {
            $this->display_related($style, $this->get_related('random', $post_to_get));
        }
    }

    public function get_related($filter, $number)
    {
        global $post;

        if ($filter == 'category') {
            $categories = get_the_category($post->ID);

            if ($categories) {
                $category_ids = [];
                foreach ($categories as $individual_category) {
                    $category_ids[] = $individual_category->term_id;
                }

                $args = [
                    'category__in' => $category_ids,
                    'post__not_in' => [$post->ID],
                    'posts_per_page' => $number,
                    'ignore_sticky_posts' => 1,
                ];
            }
        } elseif ($filter == 'tag') {
            $tags = wp_get_post_tags($post->ID);

            if ($tags) {
                $tag_ids = [];
                foreach ($tags as $individual_tag) {
                    $tag_ids[] = $individual_tag->term_id;
                }
                $args = [
                    'tag__in' => $tag_ids,
                    'post__not_in' => [$post->ID],
                    'posts_per_page' => $number,
                    'ignore_sticky_posts' => 1,
                ];
            }
        } else {
            $args = [
                'post__not_in' => [$post->ID],
                'posts_per_page' => $number,
                'orderby' => 'rand',
            ];
        }

        $related_query = new \wp_query($args);

        if ($related_query->have_posts()) {
            return $related_query;
        } else {
            return false;
        }
    }

    public function display_related($style, $related)
    {
        if ($related) {
            while ($related->have_posts()) {
                $related->the_post();

                if ($style == 'list') { ?>
                    <a href="<?php the_permalink(); ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"> <h4> <?php the_title(); ?></h4></a> 

                <?php } else { ?>
                

                    <article class="uicore-post">
                        <div class="uicore-post-wrapper">
                        <?php
                        if (has_post_thumbnail()) {
                            printf(
                                '<a href="%s" title="%s" class="uicore-post-img" style="background-image: url(%s)"></a>',
                                get_permalink(),
                                esc_html(get_the_title()),
                                get_the_post_thumbnail_url()
                            );
                        }

                        echo '<div class="uicore-post-info">';

                        if (get_option('uicore_blog_category', 'true') == 'true') {
                            echo '<div class="uicore-post-category uicore-body">';
                            the_category(', ');
                            echo '</div>';
                        }

                        printf(
                            '<h2 class="%s"><a href="%s">%s</a></h2>',
                            'uicore-post-title',
                            get_permalink(),
                            esc_html(get_the_title())
                        );

                        if (get_option('uicore_blog_excerpt', 'true') == 'true') {
                            the_excerpt();
                        }

                        echo '<div class="uicore-post-footer uicore-body">';

                        if (get_option('uicore_blog_author', 'true') == 'true') {
                            echo '<span>';
                            the_author();
                            echo '</span>';
                        }

                        if (
                            get_option('uicore_blog_author', 'true') == 'true' &&
                            get_option('uicore_blog_date', 'true') == 'true'
                        ) {
                            echo ' • ';
                        }

                        if (get_option('uicore_blog_date', 'true') == 'true') {
                            echo '<span>';
                            the_time('F j, Y');
                            echo '</span>';
                        }

                        echo '</div>'; //.uicore-post-footer END

                        echo '</div>';

                    //.uicore-post-info END
                    ?>
                        </div>
                    </article>
                <?php }
            }
        }
    }
}
