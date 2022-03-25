<?php
namespace UiCore;
defined('ABSPATH') || exit();

/**
 * Admin Functions
 *
 * @author Andrei Voica <andrei@uicore.co
 * @since 1.0.0
 */
class Admin
{
    /**
     * Construct Admin
     *
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    public function __construct()
    {
        add_action('admin_menu', [$this, 'admin_menu']);
        add_action('admin_head', [$this, 'add_editor_styles']);
        add_action('admin_head', [$this, 'add_menu_icon_style']);

        add_action('admin_enqueue_scripts', [$this, 'add_page_options'], 10, 1);
        add_filter('display_post_states', [$this, 'portfolio_page']);

        $this->elementor_style();

        $this->transients_cleaning_hooks();

        //Flush rewrite rules on portfolio page edit
        add_action( 'save_post', [$this,'flush_rules_on_portfolio_edit'], 20, 2);

        add_filter( 'upload_mimes', array( $this, 'add_fonts_to_allowed_mimes' ) );
        add_filter( 'wp_check_filetype_and_ext', array( $this, 'update_mime_types' ), 10, 3 );

        //Simple Megamenu
        add_action( 'wp_nav_menu_item_custom_fields', [$this, 'megamenu_fields'], 10, 2 );
        add_action( 'wp_update_nav_menu_item', [$this, 'save_mega'],10, 3);

        //Refresh sethings after activate woocomercee or tutorlms
        add_action( 'activated_plugin', [ $this, 'plugin_3rd_party_refresh_style' ] );

    }

    /**
     * Register Admin Menu
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    public function admin_menu()
    {
        global $submenu;

        $capability = 'manage_options';
        $slug = 'uicore';
        $name = apply_filters('uicore_theme_name', UICORE_NAME);
        $icon_url = get_template_directory_uri()."/assets/img/dashboard-icon.svg";
        $icon_url = apply_filters('uicore_theme_icon_url', $icon_url);
        $icon = file_exists(get_template_directory()."/assets/img/dashboard-icon.svg") ? $icon_url : 'dashicons-warning';

        $hook = add_menu_page(
            $name,
            $name,
            $capability,
            $slug,
            [$this, 'plugin_page'],
            $icon,
            2
        );
        // prettier-ignore
        if (current_user_can($capability) && file_exists(get_template_directory()."/assets/img/dashboard-icon.svg")) {
            $submenu[$slug][] = [__('Get Started', 'uicore-framework'), $capability, 'admin.php?page=' . $slug . '#/'];
            $submenu[$slug][] = [__('Theme Options', 'uicore-framework'),$capability,'admin.php?page=' . $slug . '#/theme-options'];
            $submenu[$slug][] = [__('System', 'uicore-framework'), $capability, 'admin.php?page=' . $slug . '#/system'];
        }

        add_action('load-' . $hook, [$this, 'init_hooks']);
    }

    /**
     * Initialize our hooks for the admin page
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    public function init_hooks()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('admin_enqueue_scripts', 'wp_enqueue_media');
    }

    /**
     * Enqueue Scripts and style
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    public function enqueue_scripts()
    {
        wp_enqueue_editor();
        wp_enqueue_style('uicore-admin');
        wp_enqueue_script('uicore-admin');
        wp_enqueue_style('uicore-admin-icons');
        wp_enqueue_style('uicore-admin-font');
        wp_add_inline_script('uicore-vendor', 'var uicore_data = ' . Data::get_admin_data('json'), 'before');
    }

    /**
     * Render Admin Page
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    public function plugin_page()
    {
        //prettier-ignore
        echo '
        <style>
        .uicore_dark_scheme {
            --uicore-color-9: #242837;
            --uicore-color-8: #fff;
            --uicore-color-7: #e0e3eb;
            --uicore-color-6: #6e778a;
            --uicore-color-5: #5a6172;
            --uicore-color-4: #3f4657;
            --uicore-color-3: #262b3b;
            --uicore-color-2: #171c29;
            --uicore-color-1: #121623;
        }
        .uicore_light_scheme {
            --uicore-color-9: #e7eaef;
            --uicore-color-8: #172b4d;
            --uicore-color-7: #1c2c4e;
            --uicore-color-6: #1c2c4e;
            --uicore-color-5: #5f6875;
            --uicore-color-4: #b0b8ca;
            --uicore-color-3: #eef0f5;
            --uicore-color-2: #fff;
            --uicore-color-1: #f4f5f7;
        }

        #uicore-wrap > #uicore{
            max-width: 1200px;
            border-radius: 5px;
            box-shadow: 0 9px 65px 1px hsla(0,0%,55%,.15);
            position: relative;
            background: var(--uicore-color-2,#171c29);
            transition: all .7s ease;
            border: 1px solid #cfd4df;
            min-height:100vh;
            overflow:hidden;
        }
        #uicore-wrap > #uicore::before{
            content: "";
            position:absolute;
            top: 0;
            left:0;
            bottom:0;
            background-color:#121623;
            width: 260px;
            border-right: 1px solid var(--uicore-color-9,#e7eaef);
        }
        </style>';

        if (get_option(Settings::get_prefix() . '_scheme', 'dark') === 'dark') {
            $class = 'uicore_dark_scheme';
        } else {
            $class = 'uicore_light_scheme';
        }
        echo '<div id="uicore-wrap" class="wrap ' .
            $class .
            '" >
                    <div id="uicore">
                    </div>
                </div>';
    }

    /**
     * Elementor Editor Style, Fonts and Scripts
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    public function elementor_style()
    {
        add_action('elementor/editor/before_enqueue_scripts', function () {
            echo '<style id="uicore-csss" >
            #wrapper-navbar{
                pointer-events:none;
            }
            body #elementor .animated.zoomIn{
                animation-fill-mode:forwards!important;
            }
            .uicore-template-library-templates-container{
                margin-left: -15px;
                margin-right: -15px;
                box-shadow: none!important;
            }

            #uicore-template-library-templates .elementor-template-library-template-page img{
                width:100%;
                height: 239px;
            }
            #uicore-template-library-templates .elementor-template-library-template-page .elementor-template-library-template-body {
                height: 239px;
            }
            .uicore-lib-dialog{
                transform: translate3d(-50%, -50%, 0);
                left: 50%;
                top: 50%;
            }
            .metform-template-item--pro {
                display: none;
            }
            .uicore-library-logo {
              height: 28px;
              width: 28px;
              margin-right: 10px;
              border-radius: 3px;
              background-color: #532df5;
              background-image: url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'16\' height=\'16\' viewBox=\'0 0 16 16\' xml:space=\'preserve\'%3E%3Cpath d=\'M5.383 15.217c3.1 0 5.4-2.3 5.4-5.3v-7.9h-2.9v7.9c0 1.4-1.1 2.5-2.5 2.5s-2.5-1.1-2.5-2.5v-7.9h-2.9v7.9c0 3 2.3 5.3 5.4 5.3zM14.283 4.117c1 0 1.7-.7 1.7-1.7s-.7-1.7-1.7-1.7-1.7.7-1.7 1.7.7 1.7 1.7 1.7zM15.683 15.017v-9.6h-2.8v9.6z\' fill=\'%23fff\'/%3E%3C/svg%3E");
              background-size: 16px;
              background-position: center;
              background-repeat: no-repeat;
            }
            .uicore-tag {
              position: absolute;
              top: 7px;
              right: 7px;
              background: #532df5;
              border-radius: 4.5px;
              color: #fff;
              font-size: 10px;
              line-height: 13px;
              font-weight: 600;
              padding: 4px 6px;
              letter-spacing: .4px;
              text-transform: uppercase;
              -webkit-font-smoothing: antialiased;
            }
            .uicore-green {
              background: #1eaa69;
            }
            .uicore-red {
              background: #dc4545;
            }

            .elementor-panel-menu-item-theme-style-typography,
            .elementor-panel-menu-item-theme-style-buttons,
            .uicore-hide {
                display: none!important;
            }
            .elementor-template-library-template-remote{
                transition:opacity .3s ease-in;
            }
            </style>

            <script>
            var uicore_data = {"v": "' .
                UICORE_VERSION .
                '", "root": "' .
                get_site_url() .
                '"}
            var uicore_blocks = ' .
                json_encode(Data::get_library('blocks')) .
                ';
            var uicore_pages = ' .
                json_encode(Data::get_library('pages')) .
                ';
            var uicore_frontend_data = ' .
                json_encode(Settings::current_settings()) .
                ';
            </script>
            ';

            $prefix = (( defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ) || defined('UICORE_LOCAL')) ? '' : '.min';

            wp_enqueue_script(
                'uicore-library-manifest',
                UICORE_ASSETS . '/js/manifest' . $prefix . '.js',
                ['jquery'],
                filemtime(UICORE_PATH . '/assets/js/manifest' . $prefix . '.js'),
                true
            );
            wp_enqueue_script(
                'uicore-library-vendor',
                UICORE_ASSETS . '/js/vendor' . $prefix . '.js',
                ['jquery'],
                filemtime(UICORE_PATH . '/assets/js/vendor' . $prefix . '.js'),
                true
            );
            wp_enqueue_script(
                'uicore-library',
                UICORE_ASSETS . '/js/library' . $prefix . '.js',
                ['jquery'],
                filemtime(UICORE_PATH . '/assets/js/library' . $prefix . '.js'),
                true
            );

            // wp_add_inline_script('uicore-vendor', 'var uicore_frontend_data = ' . json_encode(Data::get_frontend_data()), 'before');
        });
        add_action('elementor/frontend/after_enqueue_styles', function () {
            $google_fonts = get_option('uicore_fonts');
            //If Google font url is setted add it to registred style
            if ($google_fonts) {
                wp_enqueue_style('uicore_fonts', $google_fonts);
            }
        });
    }

    /**
     * Gutenberg style
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    function add_editor_styles()
    {

        echo '
        <style id="uicore-editor">
        ' .get_option('uicore_blog_css') . '
        </style>
        ';
        echo get_option('uicore_blog_fonts')
            ? '<link rel="stylesheet" id="uicore-blog-fonts" href="' .
                get_option('uicore_blog_fonts') .
                '" type="text/css" media="all">'
            : null;
    }

    /**
     * Enqueue Scripts and Style for Page Options
     *
     * @param string $hook
     * @return void
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    function add_page_options($hook)
    {
        if ($hook == 'post-new.php' || $hook == 'post.php') {
            // if ('page' === $post->post_type) {
            $this->enqueue_scripts();
            // }
        }
    }

    /**
     * Portfolio Page Archieve
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    function portfolio_page($states)
    {
        global $post;

        $page = Helper::get_option('portfolio_page');

        $portfolio_page_id = $page['id'] ?? 0;

        if (
            isset($post->ID) &&
            'page' == get_post_type($post->ID) &&
            $post->ID == $portfolio_page_id &&
            $portfolio_page_id != '0'
        ) {
            $states[] = __('Portfolio Page', 'uicore-framework');
        }

        return $states;
    }

    /**
     * Add hooks and clear transients
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    function transients_cleaning_hooks()
    {
        //Clear menu and footer on menu update
        foreach (
            [
                'rest_after_save_widget',
                'wp_ajax_save-widget',
                'wp_ajax_widgets-order',
                'wp_ajax_customize_save',
                'wp_update_nav_menu',
                'save_post',
                'delete_post'
            ]
            as $action
        ) {
            add_action(
                $action,
                function () {
                    Helper::delete_frontend_transients();
                },
                1
            ); //must use priority, example 1
        }
    }

    /**
     * Add inline style for Admin Menu Icon
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 1.0.1
     */
    function add_menu_icon_style()
    {
        echo'<style id="uicore-icon">
        .toplevel_page_uicore .wp-menu-image img, 
        #toplevel_page_edit-post_type-uicore-tb .wp-menu-image img{
            padding:7px 0 0 0!important;
            opacity:1!important;
            max-height:20px;
        }
        .notice-wpmet-jhanda-holidaydeal2021banner,
        .toplevel_page_metform-menu ul li:last-child,
        .toplevel_page_element_pack_options ul li:last-child,
        #element-pack-notice-id-license-issue,
        #bdt-element_pack_license_settings,
        .wpmet-notice.notice-metform-_plugin_rating_msg_used_in_day{
            display:none!important
        }
        </style>';
    }


    /**
     * Check if portfolio page was saved and flush the rewrite rules
     *
     * @param [type] $id
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 1.1.0
     */
    function flush_rules_on_portfolio_edit($id)
    {
       $portfolio_id = Helper::get_option('portfolio_page');
       $portfolio_id = isset($portfolio_id['id']) ? $portfolio_id['id'] : 0;

       if( (int) $id === (int) $portfolio_id){
            flush_rewrite_rules();
       }

    }

    /**
     * Allow fonts to be uploaded
     *
     * @param [type] $mimes
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 1.2.0
     */
    public function add_fonts_to_allowed_mimes( $mimes ) {
        $mimes['woff']  = 'application/x-font-woff';
        $mimes['woff2'] = 'application/x-font-woff2';
        $mimes['ttf']   = 'application/x-font-ttf';
        $mimes['svg']   = 'image/svg+xml';
        $mimes['eot']   = 'application/vnd.ms-fontobject';
        $mimes['otf']   = 'font/otf';

        //Temp to ensure support for using wp older than 5.8 ( TO BE REMOVED IN THE FUTURE )
        $mimes['webp'] = 'image/webp';

        return $mimes;
    }

    /**
     * add support for fonts
     *
     * @param $defaults
     * @param $file
     * @param $filename
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 1.2.0
     */
    public function update_mime_types( $defaults, $file, $filename ) {
        if ( 'ttf' === pathinfo( $filename, PATHINFO_EXTENSION ) ) {
            $defaults['type'] = 'application/x-font-ttf';
            $defaults['ext']  = 'ttf';
        }

        if ( 'otf' === pathinfo( $filename, PATHINFO_EXTENSION ) ) {
            $defaults['type'] = 'application/x-font-otf';
            $defaults['ext']  = 'otf';
        }

        return $defaults;
    }

    /**
     * Save Simple megamenu custom field
     *
     * @param [type] $menu_id
     * @param [type] $menu_item_db_id
     * @param [type] $args
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 1.2.6
     */
    function save_mega($menu_id, $menu_item_db_id, $args )
    {
        // Verify this came from our screen and with proper authorization.
        if ( ! isset( $_POST['_menu_item_mega_nonce_name'] ) || ! wp_verify_nonce( $_POST['_menu_item_mega_nonce_name'], 'mega_menu_meta_nonce' ) ) {
            return $menu_id;
        }

        if ( isset( $_POST['menu-item-mega'][$menu_item_db_id]  ) ) {
            $sanitized_data = sanitize_text_field( $_POST['menu-item-mega'][$menu_item_db_id] );
            update_post_meta( $menu_item_db_id, '_menu_item_mega', $sanitized_data );
        } else {
            delete_post_meta( $menu_item_db_id, '_menu_item_mega' );
        }
    }

    /**
     * Add simple megamenu custom field to menu item
     *
     * @param [type] $item_id
     * @param [type] $item
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 1.2.6
     */
    function megamenu_fields( $item_id, $item )
    {
        wp_nonce_field( 'mega_menu_meta_nonce', '_menu_item_mega_nonce_name' );
        $custom_menu_meta = get_post_meta( $item_id, '_menu_item_mega', true );

        $css = null;
        if($item->object === 'uicore-tb'){
            $post_type_object = get_post_type_object( 'uicore-tb' );
            if ( ! $post_type_object ) {
                return;
            }

            if ( ! current_user_can( 'edit_post', $item_id ) ) {
                return;
            }

            if ( $post_type_object->_edit_link ) {
                $link = admin_url( sprintf( $post_type_object->_edit_link . '&action=elementor', $item_id ) );
            } else {
                $link = '';
            }

            $css = 'display:none;';
            echo '<a style="margin: 12px 0;float:left" href='.$link.'>Edit with Elementor</a>';
        }
        ?>
        <div class="field-custom_menu_meta description-wide" style="margin: 5px 0;<?php echo $css; ?>">
            <input type="hidden" class="nav-menu-id" value="<?php echo $item_id ;?>" />

            <div class="logged-input-holder">
                <input type="checkbox" name="menu-item-mega[<?php echo $item_id ;?>]" id="menu-item-mega-for-<?php echo $item_id ;?>" value="1" <?php checked( $custom_menu_meta, 1 ); ?> />
                <label for="menu-item-mega-for-<?php echo $item_id ;?>">
                    <?php _e( 'Simple Megamenu', 'uicore-framework'); ?>
                </label>
            </div>

        </div>

        <?php
    }

    function plugin_3rd_party_refresh_style($plugin)
    {
        $plugins = [
            'woocommerce/woocommerce.php',
            'tutor/tutor.php'
        ];
        if(in_array($plugin,$plugins)){
            $settings = Settings::current_settings();
            Settings::update_style($settings);
        }
    }
}
