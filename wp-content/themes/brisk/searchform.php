<?php
/**
 * The template for displaying search forms in brisk
 *
 * @package brisk
 * @since Brisk 1.0
 */
?>
    <form method="get" id="searchform" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>" role="search">
        <label>
            <span class="screen-reader-text">Search for:</span>
            <input type="search" class="search-field" placeholder="<?php esc_attr_e( 'Search &hellip;', 'brisk' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" name="s" x-webkit-speech>
        </label>
        <input type="submit" class="search-submit" value="<?php esc_attr_e( 'Search', 'brisk' ); ?>" />
    </form>
