<?php
namespace UiCore;
defined('ABSPATH') || exit();

/**
 * Here we generate the header
 */
class Posts
{
    function __construct()
    {
        // if ((is_category() || is_day() || is_month() || is_author() || is_year() || is_tag() || is_home() || is_singular('post'))
        // Pagination Template
        if (!class_exists('Uicore\Pagination')) {
            require UICORE_INCLUDES . '/templates/pagination.php';
        }

        // Related Posts Template
        // require UICORE_INCLUDES . '/templates/related-posts.php';

        if (is_singular('portfolio') || is_post_type_archive('portfolio') || is_tax('portfolio_category')) {
            new Portfolio\Template();
        } else if(Blog\Frontend::is_blog()) {
            new Blog\Template();
        }else {
            new Pages();
        }
    }
}
