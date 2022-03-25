<?php
namespace UiCore\Blog;
use UiCore\Pagination as Pagination;
use UiCore\Helper as Helper;
defined('ABSPATH') || exit();

/**
 * Frontend Blog Archive and Single
 *
 * @author Andrei Voica <andrei@uicore.co
 * @since 2.0.2
 */
class Template
{
    /**
     * __construct
     *
     * @return void
     */
    function __construct($type = 'full')
    {
        if ($type == 'full') {
            if (is_single()) {
                if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'single' ) ) {
                    $this->render_blog_single();
                }
            } else {
                if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'archive' ) ) {
                    $this->render_blog_archive();
                }
            }
        }
    }

    /**
     * render_blog_single
     *
     * @return void
     */
    public function render_blog_single()
    {
        $this->single_layout();
    }

    /**
     * render_blog_archive
     *
     * @return void
     */
    public function render_blog_archive()
    {
        ?>
        <main id="main" class="site-main elementor-section elementor-section-boxed uicore">
        <div class="uicore elementor-container uicore-content-wrapper uicore-blog-animation">
            <div class="uicore-archive uicore-post-content">
                <?php if (!$this->is_search_has_results() && is_search()) {
                    $this->not_found_layout();
                } else {
                    $this->blog_layout();
                    new Pagination();
                } ?>
            </div>
            <?php do_action('uicore_sidebar'); ?>
        </div>
    </main>
    <?php
    }

    /**
     * blog_layout
     *
     * @return void
     */
    public function blog_layout($wp_query = false, $grid_type = null, $col_no = null, $hover_effect = null, $ratio = null)
    {
        $is_widget = ' uicore-blog-widget';
        if (!$wp_query && $grid_type === null) {
            $is_widget = ' ';
            global $wp_query;
        }
        //global variables
        $grid_type = $grid_type ?? Helper::get_option('blog_layout', 'grid');
        $hover_effect = $hover_effect ?? Helper::get_option('blog_hover_effect', 'zoom');
        $item_style = Helper::get_option('blog_item_style');
        $ratio = $ratio ?? Helper::get_option('blog_ratio', 'portrait');
        $hover_effect = str_replace(' ', '-', $hover_effect);
        $item_style = str_replace(' ', '-', $item_style);

        $col_no = $col_no ?? Helper::get_option('blog_col', '3');
        if ($col_no == '1') {
            $col = '12';
        }
        if ($col_no == '2') {
            $col = '6';
        }
        if ($col_no == '3') {
            $col = '4';
        }
        if ($col_no == '4') {
            $col = '3';
        }

        $grid_space = Helper::get_option('blog_col_space', 'extra large');
        $grid_space = str_replace(' ', '-', $grid_space);

        if ($grid_space == 'extra-large') {
            $grid_space_no = 80;
        }
        if ($grid_space == 'large') {
            $grid_space_no = 50;
        }
        if ($grid_space == 'medium') {
            $grid_space_no = 30;
        }
        if ($grid_space == 'small') {
            $grid_space_no = 15;
        }
        if ($grid_space == 'extra-small') {
            $grid_space_no = 5;
        }
        if ($grid_space == 'none') {
            $grid_space_no = 0;
        }

        echo '<div class="uicore-grid-container uicore-blog-grid uicore-grid-row';
        echo ' uicore-' . $grid_type;
        echo ' uicore-' . $ratio . '-ratio';
        echo ' uicore-' . $grid_space . '-space';
        echo ' animate-'.$col_no;
		echo ' ui-st-' . $item_style;
        echo $is_widget;

        if ($grid_type == 'masonry') {
            wp_enqueue_script('uicore-grid');
            echo '" data-grid="masonryb"';
            echo ' data-col="' . $col_no . '"';
            echo ' data-space="' . $grid_space_no . '">';
        } else {
            echo '">';
        }

        //Start the loop
        while ($wp_query->have_posts()) {

            $wp_query->the_post();
            global $post;

            //post specific varables
            $post_link = get_permalink();
            $terms = get_the_category($post->ID);
            $category = false;
            $post_thumbnail = false;
            if ($terms) {
                foreach ($terms as $t) {
                    $term_name[] =
                        '<a href="' . get_term_link($t) . '" title="View ' . $t->name . ' posts">' . $t->name . '</a>';
                }
                $category = implode(', ', $term_name);
                $term_name = null;
            }

            //get the post thumbnail
            if (!empty(get_the_post_thumbnail())) {
                //pic url
                $post_thumbnail =
                    '<a href="' . esc_url($post_link) . '" title="View Post: ' . esc_attr(get_the_title()) . '" >';

                if ($grid_type != 'masonry') {
                    $post_thumbnail .=
                        '  <div class="uicore-blog-img-container uicore-zoom-wrapper">
                                            <div class="uicore-cover-img" style="background-image: url(' .
                        get_the_post_thumbnail_url($post->ID, 'uicore-medium') .
                        ')"></div>
                                        </div>';
                } else {
                    $pic_id = get_post_thumbnail_id($post->ID);
                    $post_thumbnail .=
                        '  <div class="uicore-blog-img-container uicore-zoom-wrapper">
                                            <img class="uicore-cover-img" srcset="' .
                        wp_get_attachment_image_srcset($pic_id, 'uicore-medium') .
                        '"
                        sizes="' .
                        wp_get_attachment_image_sizes($pic_id, 'uicore-medium') .
                        '" alt="' .
                        esc_attr(get_the_title()) .
                        '"/>
                                        </div>';
                }

                $post_thumbnail .= '</a>';
            }

            $extra_post_classes = ['uicore-grid-item'];
            if ($col != '12') {
                array_push($extra_post_classes, 'uicore-col-md-6');
            }
            array_push($extra_post_classes, 'uicore-col-lg-' . $col);
            array_push($extra_post_classes, ' uicore-' . $hover_effect);
            array_push($extra_post_classes, 'uicore-animate');
            ?>

            <div <?php post_class($extra_post_classes); ?> >
                <article class="uicore-post">
                    <div class="uicore-post-wrapper">

                        <?php echo $post_thumbnail; ?>

                        <div class="uicore-post-info">
                            <div class="uicore-post-info-wrapper">
                                <?php
                                if ($category && Helper::get_option('blog_category', 'true') == 'true') { ?>
                                <div class="uicore-post-category uicore-body">
                                    <?php echo $category; ?>
                                </div>
                              <?php }
                                echo '<a href="';
                                echo esc_url($post_link);
                                echo '" title="View Post: ' . esc_html(get_the_title()) . ' ">';
                                ?>
                              <h4 class="uicore-post-title"><span><?php echo esc_html(get_the_title()); ?></span></h4>
                              <?php
                              echo '</a>';

                              if (Helper::get_option('blog_excerpt', 'true') === 'true') {
                                  echo '<p>';
                                  echo wp_trim_excerpt(get_the_excerpt());
                                  echo '</p>';
                              }
							  $isautor = (Helper::get_option('blog_author', 'true') === 'true');
							  $isdate = (Helper::get_option('blog_date', 'true') === 'true');

 						  	  if ($isautor || $isdate) {
	                              echo '<div class="uicore-post-footer uicore-body">';

	                              if ($isautor) {
	                                  echo '<span>';
	                                  echo get_the_author_posts_link();
	                                  echo '</span>';
	                              }

	                              if ($isautor && $isdate) {
	                                  echo Helper::get_separator();
	                              }

	                              if ($isdate) {
	                                  echo '<span>';
	                                  echo get_the_date();
	                                  echo '</span>';
	                              }

	                              echo '</div>';
							  }

            //.uicore-post-footer END
            ?>
                            </div>
                        </div>

                    </div>
                </article>
            </div>
        <?php
        }
        wp_reset_query();
        echo '</div>'; //.uicore-post-list END
    }

    /**
     * single_layout
     *
     * @return void
     */
    public function single_layout()
    {
        global $post;
        $page_title_type = apply_filters('uicore_blogs_title', Helper::get_option('blogs_title'), $post);
        $navigation = (Helper::get_option('blogs_navigation', 'false') === 'false' ? false : true);

        while (have_posts()):
            the_post();
        if (
            $page_title_type === 'simple creative' && is_singular('post')
        ) {
            echo '<div class="elementor-section elementor-section-boxed">';
                echo '<div class="elementor-container">';
                    $this->single_title('creative');
                echo '</div>';
            echo '</div>';
        }


        ?>
        <main id="main" class="site-main elementor-section elementor-section-boxed uicore">
			<div class="uicore elementor-container uicore-content-wrapper uicore-blog-animation">

				<div class="uicore-type-post uicore-post-content uicore-animate">

                    <article id="post-<?php the_ID(); ?>" <?php post_class('blog-fonts'); ?>>

                    <?php if ( ($page_title_type === 'simple page title' || Helper::po('pagetitle', 'pagetitle', 'true', get_the_ID()) === 'false') && is_singular('post')  ) {
                        $this->single_title();
                    } ?>

                        <div class="entry-content">
                            <?php
                            the_content(
                                sprintf(
                                    wp_kses(
                                        /* translators: %s: Name of current post. Only visible to screen readers */
                                        _x('Continue reading<span class="screen-reader-text"> "%s"</span>', 'Frontend - Blog', 'uicore-framework'),
                                        [
                                            'span' => [
                                                'class' => [],
                                            ],
                                        ]
                                    ),
                                    get_the_title()
                                )
                            );

                            wp_link_pages([
                                'before' => '<div class="page-links">' . esc_html_x('Pages:', 'Frontend - Blog', 'uicore-framework'),
                                'after' => '</div>',
                            ]);
                            ?>
                        </div><!-- .entry-content -->

                        <?php if (Helper::get_option('blogs_tags', 'true') == 'true') { ?>
                        <footer class="entry-footer">
                            <?php
                            $tags_list = get_the_tag_list('', ' ');
                            if ($tags_list) {
                                echo '<div class="tags-links">' . $tags_list . '</div>';
                            }
                            ?>
                        </footer><!-- .entry-footer -->
                        <?php } ?>

                    </article><!-- #post-<?php the_ID(); ?> -->
                    <?php

                    if($navigation){
                        $this->get_post_navigation();
                    }

                    //prettier-ignore
                    // If comments are open or we have at least one comment, load up the comment template.
                    if (comments_open() || get_comments_number()):
                        comments_template();
                    endif;

                    ?>
                </div>
            <?php do_action('uicore_sidebar', $post); ?>
        </div>
    </main>
    <?php
    endwhile;// End of the loop.
    }

    /**
     * single_title
     *
     * @return void
     */
    public function single_title($type = 'simple')
    {
        ?>
        <header class="uicore-single-header <?php
        echo ($type === 'creative') ? ' ui-simple-creative' : '';
        ?>">

        <?php

        the_title('<h1 class="entry-title uicore-animate">', '</h1>');

        $description = '<div class="uicore-entry-meta uicore-animate">';

        if (Helper::get_option('blogs_author') == 'true') {
            $description .= '<div>';
            $description .= get_the_author_posts_link();
            $description .= Helper::get_separator();
            $description .= '</div>';
        }

        if (Helper::get_option('blogs_date') == 'true') {
            $date = get_the_date() ?? '';
            $update_date = get_the_modified_date() ?? '';
            if(Helper::get_option('blogs_date_type') === 'published' || Helper::get_option('blogs_date_type') === 'both'){
                $date = get_the_date() ?? '';
                $description .= '<span class="ui-blog-date ui-published">';
                if(Helper::get_option('blogs_date_type') === 'both' && $date != $update_date){
                    $description .= esc_attr_x('Posted On:','Frontend - Blog Meta','uicore-framework');
                }
                $description .= $date;
                $description .= ' </span>';
                $description .= Helper::get_separator();
            }
            if(Helper::get_option('blogs_date_type') === 'updated' || Helper::get_option('blogs_date_type') === 'both'){
                if(Helper::get_option('blogs_date_type') === 'updated' || $date != $update_date){
                    $date = get_the_modified_date() ?? '';
                    $description .= '<span class="ui-blog-date ui-updated">';
                    if(Helper::get_option('blogs_date_type') === 'both'){
                        $description .= esc_attr_x('Updated On:','Frontend - Blog Meta','uicore-framework');
                    }
                    $description .= $date;
                    $description .= ' </span>';
                    $description .= Helper::get_separator();
                }
            }
        }

        if (Helper::get_option('blogs_category') == 'true') {
            $description .= '<div class="uicore-post-category uicore-body">';
            ob_start();
            the_category(', ');
            $description .= ob_get_clean();
            $description .= '</div>';
        }
        $description .= '</div>';

        echo $description;

        if (Helper::get_option('blogs_img') == 'true' && $type === 'simple') {
            echo '<div class="uicore-feature-img-wrapper uicore-animate">';
            the_post_thumbnail('large');
            echo '</div>';
        } ?>
    </header>
    <?php
    }

    /**
     * is_search_has_results
     *
     * @return void
     */
    function is_search_has_results()
    {
        global $wp_query;
        $result = 0 != $wp_query->found_posts ? true : false;
        return $result;
    }

    function not_found_layout()
    {
        ?>
		<div class="uicore-animate ui-no-results">
            <h2 class="ui-search-title"><?php echo esc_attr_x('No results', 'Frontend - Search', 'uicore-framework'); ?></h2>
            <p><?php echo esc_attr_x('We did not find any article that matches this search. Try using other search criteria:', 'Frontend - Search', 'uicore-framework'); ?></p>
            <?php get_search_form(); ?>
            </div>
		<?php
    }

    function get_post_navigation()
    {
        ?>
        <hr/>
        <div class="ui-post-nav">
            <div class="ui-post-nav-item ui-prev">
            <?php
            $prev_post = get_previous_post();
            if ( ! empty( $prev_post ) ): ?>
                <a href="<?php echo get_permalink( $prev_post->ID ); ?>" rel="prev">
                <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" fill="none" stroke="#444" stroke-width="2" x="0px" y="0px" viewBox="0 0 24 24" xml:space="preserve" width="24" height="24">
                    <g>
                        <line stroke-miterlimit="10" x1="22" y1="12" x2="2" y2="12" stroke-linejoin="miter" stroke-linecap="butt"></line>
                        <polyline stroke-linecap="square" stroke-miterlimit="10" points="9,19 2,12 9,5 " stroke-linejoin="miter"></polyline>
                    </g>
                </svg>
                <span class="ui-post-nav-info"><?php echo esc_attr_x('Previous Article', 'Frontend - Blog', 'uicore-framework'); ?></span>
                    <h4 title="<?php echo apply_filters( 'the_title', $prev_post->post_title ); ?>"><?php echo apply_filters( 'the_title', $prev_post->post_title ); ?></h4>
                </a>
            <?php endif; ?>
            </div>
            <div class="ui-post-nav-item ui-next">
            <?php
            $next_post = get_next_post();
            if ( ! empty( $next_post ) ): ?>
                <a href="<?php echo get_permalink( $next_post->ID ); ?>" rel="next">
                <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" fill="none" stroke="#444" stroke-width="2" x="0px" y="0px" viewBox="0 0 24 24" xml:space="preserve" width="24" height="24">
                    <g transform="rotate(180 12,12) ">
                        <line stroke-miterlimit="10" x1="22" y1="12" x2="2" y2="12" stroke-linejoin="miter" stroke-linecap="butt"></line>
                        <polyline stroke-linecap="square" stroke-miterlimit="10" points="9,19 2,12 9,5 " stroke-linejoin="miter"></polyline>
                    </g>
                </svg>
                <span class="ui-post-nav-info"><?php echo esc_attr_x('Next Article', 'Frontend - Blog', 'uicore-framework'); ?></span>
                   <h4 title="<?php echo apply_filters( 'the_title', $next_post->post_title ); ?>"><?php echo apply_filters( 'the_title', $next_post->post_title ); ?></h4>
                </a>
            <?php endif; ?>
            </div>
        </div>
        <?php
    }
}
