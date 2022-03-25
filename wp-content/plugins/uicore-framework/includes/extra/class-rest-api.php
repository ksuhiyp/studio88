<?php
namespace UiCore;
defined('ABSPATH') || exit();

use WP_Error;
use WP_REST_Response;

class Api
{
    private $wp_error;

    public function __construct()
    {
        add_action('rest_api_init', [$this, 'add_route']);
    }

    /**
     * Add routes
     */
    public function add_route()
    {
        //settings Api
        register_rest_route('uicore/v1', 'settings', [
            [
                'methods' => 'GET',
                'permission_callback' => [$this, 'check_for_permission'],
                'callback' => [$this, 'get_settings'],
                'show_in_index' => false,
            ],
            [
                'methods' => 'POST',
                'permission_callback' => [$this, 'check_for_permission'],
                'callback' => [$this, 'rest_update_settings'],
                'args' => [],
                'show_in_index' => false,
            ],
        ]);

        //admin settings Api
        register_rest_route('uicore/v1', 'admin', [
            [
                'methods' => 'POST',
                'permission_callback' => [$this, 'check_for_permission'],
                'callback' => [$this, 'admin_utility'],
                'show_in_index' => false,
            ],
        ]);
        register_rest_route('uicore/v1', 'import-log', [
            [
                'methods' => 'GET',
                'permission_callback' => [$this, 'check_for_permission'],
                'callback' => [$this, 'get_import_log'],
                'show_in_index' => false,
            ],
        ]);
        register_rest_route('uicore/v1', 'import-log', [
            [
                'methods' => 'POST',
                'permission_callback' => [$this, 'check_for_permission'],
                'callback' => [$this, 'clear_import_log'],
                'show_in_index' => false,
            ],
        ]);

        //import Api
        register_rest_route('uicore/v1', 'import', [
            [
                'methods' => 'POST',
                'permission_callback' => [$this, 'check_for_permission'],
                'callback' => [$this, 'import'],
                'show_in_index' => false,
            ],
        ]);

        //Theme Builder 
        // UiCore\ThemeBuilderApi::class;
    }

    public function check_for_permission()
    {
        return current_user_can('manage_options');
    }

    /**
     * Get Current Theme Options Settings
     *
     * @return object
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    public static function get_settings()
    {
        $current = Settings::current_settings();
        return rest_ensure_response($current);
    }

    /**
     * Update Theme Options Settings
     *
     * @param \WP_REST_Request $request
     * @return void
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    public function rest_update_settings(\WP_REST_Request $request)
    {
        $settings = $request->get_json_params();
        $response = Settings::update_settings($settings);
        // $response = ['status'=> 'success'];
        return rest_ensure_response($response);
    }

    /**
     * Do Admin Utility functions from 'admin' API endpoint
     *
     * @param \WP_REST_Request $request
     * @return array Action Response
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    public function admin_utility(\WP_REST_Request $request)
    {
        //default response
        // $response = array("status"=>"error");

        if ($request['advanced_mode']) {
            $settings = ThemeOptions::get_admin_db_options();
            $settings['advanced_mode'] = $request['advanced_mode'];
            update_option( UICORE_SETTINGS.'_admin', $settings, false);
            return new WP_REST_Response(['status' => 'success']);
        }
        if ($request['backgrounds']) {
            $settings = ThemeOptions::get_admin_db_options();
            $settings['backgrounds'] = $request['backgrounds'];
            update_option( UICORE_SETTINGS.'_admin', $settings, false);
            return new WP_REST_Response(['status' => 'success']);
        }
        if ($request['scheme']) {
            $settings = ThemeOptions::get_admin_db_options();
            $settings['scheme'] = $request['scheme'];
            update_option( UICORE_SETTINGS.'_admin', $settings, false);
            return new WP_REST_Response(['status' => 'success']);
        }
        if ($request['presets']) {
            return $this->update_presets($request['presets']);
        }
        if ($request['demos']) {
            return $this->get_demos();
        }
        if ($request['reset']) {
            return $this->reset_settings();
        }
        if ($request['refresh']) {
            return $this->refresh_transients();
        }
        if ($request['typekit']) {
            return $this->sync_typekit($request['typekit']);
        }
        
        if ($request['purchase']) {
            return $this->remove_purchase($request['purchase']);
        }
        if($request['clear_cache']){
            Settings::clear_cache();
            $this->refresh_transients();
            return array("status"=>"succes");
        }
    }

    /**
     * Process Import Data
     *
     * @param \WP_REST_Request $request
     * @return array
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    public function import(\WP_REST_Request $request)
    {
        if (!class_exists('\UiCore\Import')) {
            require_once UICORE_INCLUDES . '/extra/class-import.php';
        }
        $import = new Import($request);
        return rest_ensure_response($import->response);
    }

    /**
     * Process Import Data
     *
     * @param \WP_REST_Request $request
     * @return array
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    public function update(\WP_REST_Request $request)
    {
        if (!class_exists('\UiCore\Import')) {
            require_once UICORE_INCLUDES . '/extra/class-import.php';
        }
        $import = new Import($request);
        return rest_ensure_response($import->response);
    }

    /**
     * Get Demo List and save it for 7 days
     *
     * @return array
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    public function get_demos()
    {
        $demos = [];
        $api_response = wp_remote_get(UICORE_API . '/demos');
        if(!is_wp_error($api_response)){
            $demos = wp_remote_retrieve_body($api_response);
            set_transient('uicore_demos', $demos, 7 * DAY_IN_SECONDS);
        }
        return new WP_REST_Response($demos);
    }

    /**
     * Delete all saved frontend settings
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 1.0.0
     */
    public function reset_settings()
    {
        delete_option( UICORE_SETTINGS);
        delete_option( UICORE_SETTINGS.'_admin');

        $new = ThemeOptions::get_all_defaults();
        Helper::delete_frontend_transients();
        
        //update elementor options and glbals
		Settings::elementor_update($new);

		//Update all styles and scripts
		Settings::update_style($new);

        return new WP_REST_Response($new);
    }

    /**
     * Update Preset Manager local List
     *
     * @param array $presets
     * @return \WP_REST_Response
     * @author Andrei Voica <andrei@uicore.co>
     * @since 1.0.0
     */
    public function update_presets(array $presets)
    {
        $settings = ThemeOptions::get_admin_db_options();
        $settings['presets'] = $presets;
        $update = update_option( UICORE_SETTINGS.'_admin', $settings, false);

        if (is_wp_error($update)) {
            $response = ['status' => 'error'];
        } else {
            $response = ['status' => 'succes'];
        }

        return new WP_REST_Response($response);
    }

    /**
     * Refresh all Uicore Admin Data Transients; 
     * eg:demos, changelog, blocks, pages
     *
     * @return \WP_REST_Response
     * @author Andrei Voica <andrei@uicore.co>
     * @since 1.0.0
     */
    public function refresh_transients()
    {
        if (!class_exists('\Uicore\Data')) {
            require_once UICORE_INCLUDES . '/extra/class-data.php';
        }
        delete_transient('uicore_pages');
        delete_transient('uicore_demos');
        $new = $this->get_demos();

        delete_transient('uicore_changelog');
        $new = Data::get_changelog();

        delete_transient('uicore_library_blocks');
        $new = Data::get_library('blocks');

        delete_transient('uicore_library_pages');
        $new = Data::get_library('pages');

        delete_transient('uicore_last_version');

        $response = ['status' => 'succes'];
        return new WP_REST_Response($response);
    }

    /**
     * Get Import Log
     *
     * @return WP_REST_Response
     * @author Andrei Voica <andrei@uicore.co>
     * @since 1.0.1
     */
    public function get_import_log()
    {
        return new WP_REST_Response(get_option('uicore_imported_demos', []));
    }

    /**
     * Get Import Log
     *
     * @return WP_REST_Response
     * @author Andrei Voica <andrei@uicore.co>
     * @since 1.0.1
     */
    public function clear_import_log($request)
    {
        if($request['clear']){
            return new WP_REST_Response(update_option('uicore_imported_demos', []));
        }else{
            return new WP_Error('Task Imposible');
        }
    }

    /**
     * Sync Typekit Fonts
     *
     * @return WP_REST_Response
     * @author Andrei Voica <andrei@uicore.co>
     * @since 1.0.1
     */
    public function sync_typekit($typekit)
    {
        
        $data = wp_remote_get(
            'https://typekit.com/api/v1/json/kits/' . $typekit["id"] . '/published',
            array(
                'timeout' => '30',
            )
        );
        if ( is_wp_error( $data ) || wp_remote_retrieve_response_code( $data ) !== 200 ) {
            return new WP_REST_Response(['error'=>'Connection to Typekit was failed or bad Project ID. Please try again!']);
        }
        $typekit_info = array();
        $data     = json_decode( wp_remote_retrieve_body( $data ), true );
        $families = $data['kit']['families'];

        foreach ( $families as $family ) {

            $family_name = str_replace( ' ', '-', $family['name'] );

            $typekit_info[ $family_name ] = array(
                'family'   => $family_name,
                'fallback' => str_replace( '"', '', $family['css_stack'] ),
                'weights'  => array(),
            );

            foreach ( $family['variations'] as $variation ) {

                $variations = str_split( $variation );

                switch ( $variations[0] ) {
                    case 'n':
                        $style = 'normal';
                        break;
                    default:
                        $style = 'normal';
                        break;
                }

                $weight = $variations[1] . '00';

                if ( ! in_array( $weight, $typekit_info[ $family_name ]['weights'] ) ) {
                    $typekit_info[ $family_name ]['weights'][] = $weight;
                }
            }

            $typekit_info[ $family_name ]['slug']      = $family['slug'];
            $typekit_info[ $family_name ]['css_names'] = $family['css_names'];
        }



        if($typekit['id']){
            return new WP_REST_Response(Data::get_typekit_fonts(null,null,['fonts'=>$typekit_info]));
        }else{
            return new WP_REST_Response(['error'=>'Connection to Typekit was failed or bad Project ID. Please try again!']);
        }
    }


    function remove_purchase($mode)
    {
        $settings = get_option(UICORE_SETTINGS.'_admin');
        if($mode === 'remove'){
            unset($settings['purchase_info']);
        }else{
            $settings['purchase_info'] = $mode;
        }
        update_option( UICORE_SETTINGS.'_admin', $settings, false);

        return ["status"=>"succes"];
    }
}
new Api();
