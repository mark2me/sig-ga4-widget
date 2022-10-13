<?php
/*
Plugin Name: Sig GA4 Widget
Plugin URI: https://github.com/mark2me/sig-ga4-widget
Description: Use Google Analytics 4 data to display website pageviews widget
Author: Simon Chuang
Author URI: https://github.com/mark2me
Text Domain: sig-ga4-widget
Domain Path: /languages/
Version: 1.0.1
*/

define( 'SIGA4W_VERSION', '1.0.1' );
define( 'SIGA4W_OPTION', '_siga4w_setting' );
define( 'SIGA4W_OPTION_GROUP', '_siga4w_setting_group' );
define( 'SIGA4W_DIR', dirname(__FILE__) );
define( 'SIGA4W_PLUGIN_URL', plugin_dir_url(__FILE__) );
define( 'SIGA4W_WIDGET_ID', 'siga4w_widget');
define( 'SIGA4W_CACHE_TIME' , 3600 );
define( 'SIGA4W_BEGIN_DATE', '2020-01-01');


require_once( SIGA4W_DIR . '/vendor/autoload.php' );
require_once( SIGA4W_DIR . '/inc/helper.php' );
require_once( SIGA4W_DIR . '/inc/upgrade.php' );

new SIGA4W_init();

class SIGA4W_init{

    public $options;

    public $def_pv_label = '';

    public $pv_label = '';

    public function __construct() {

        $this->options = get_option(SIGA4W_OPTION);

        $upgrade = new SIGA4W_upgrade($this->options);
        $upgrade->run();


        /* translators: %s is post pageviews. */
        $this->def_pv_label = __( 'Pageviews: %s', 'sig-ga4-widget' );

        add_filter( 'plugin_action_links_'.plugin_basename(__FILE__), [ $this , 'add_settings_link' ] );

        add_action( 'wp_enqueue_scripts', [ $this, 'add_wp_enqueues' ] );

        add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );

        add_action( 'admin_enqueue_scripts', [ $this, 'add_admin_enqueues' ] );

        add_action( 'admin_init', [ $this, 'register_settings_fields' ] );

        add_filter( 'the_content', [ $this, 'add_post_pv' ], 9999 );

        add_action( 'wp_ajax_siga4w-widget', [ $this, 'add_wpajax_widget_data' ] );
        add_action( 'wp_ajax_nopriv_siga4w-widget', [ $this, 'add_wpajax_widget_data' ] );
        add_action( 'wp_ajax_siga4w-delete', [ $this, 'delete_json_file' ] );

        if( !empty($this->options['json_key']) && !empty($this->options['property_id']) ){
            putenv( 'GOOGLE_APPLICATION_CREDENTIALS='. $this->options['json_key'] );
            require_once( SIGA4W_DIR . '/classes/widget.php' );
            require_once( SIGA4W_DIR . '/classes/ga4.php' );
        }

    }


    /**
     * add link
     */
    public function add_settings_link($links){
        $settings_link = '<a href="' . admin_url('admin.php?page=siga4w_setting') . '">'.__( 'Settings' , 'sig-ga4-widget' ).'</a>';
        array_unshift( $links, $settings_link );
        return $links;
    }

    /**
     *  add admin menu
     *  filters: $capability
     */
    public function add_admin_menu() {

        $capability  = apply_filters( 'siga4w/plugin_capabilities', 'manage_options' );

        add_menu_page( __('GA4 charts' , 'sig-ga4-widget'), __( 'GA4 charts', 'sig-ga4-widget' ), $capability, 'siga4w_page', [ $this, 'cb_page_show' ] , 'dashicons-chart-line' );
        add_options_page( __('GA4 widget config', 'sig-ga4-widget'), __( 'GA4 widget config', 'sig-ga4-widget' ), $capability, 'siga4w_setting', [ $this, 'cb_page_setting' ] );
    }

    /**
     *  Setting page
     */
    public function cb_page_setting() {

        if( empty($this->options['json_key']) or empty($this->options['property_id']) ){

            add_settings_error(
                'siga4w-settings-notices',
                'settings_json_id',
                __( 'Please upload the json file and fill in the web property id.', 'sig-ga4-widget' ),
                'error'
            );

        }else{

            $test = siga4w_get_data();

            if( !empty($test['message']) ){
                add_settings_error( 'siga4w-settings-notices', 'settings_test',
                    /* translators: %1$s is error message, %2$s is status. */
                    sprintf( __( 'Error: %1$s<br>Status: %2$s', 'sig-ga4-widget' ), $test['message'], (!empty($test['status']) ? $test['status']:'') ),
                    'error'
                );
            }
        }

        require_once( SIGA4W_DIR . '/inc/page_setting.php' );
    }

    /**
     *  Statistics page
     */
    public function cb_page_show() {
        require_once( SIGA4W_DIR . '/inc/page_show.php' );
    }

    public function register_settings_fields() {
        register_setting( SIGA4W_OPTION_GROUP, SIGA4W_OPTION, [ $this, 'cb_file_upload' ] );
    }

    /**
     *  Load jquery
     */
    public function add_wp_enqueues() {
        wp_enqueue_script( 'jquery' );
    }

    /**
     * Add style & script files
     */
    public function add_admin_enqueues($hook){

        if( in_array( $hook, array( 'settings_page_siga4w_setting', 'toplevel_page_siga4w_page' ) ) ) {
            wp_enqueue_style( 'bootstrap4', SIGA4W_PLUGIN_URL . 'assets/css/bootstrap-grid.min.css' );
        }

        if( in_array($hook, array('toplevel_page_siga4w_page')) ) {
            wp_enqueue_style( 'chart', SIGA4W_PLUGIN_URL . 'assets/js/morris.css' );
            wp_enqueue_script( 'raphael', SIGA4W_PLUGIN_URL . 'assets/js/raphael-min.js', array('jquery') );
            wp_enqueue_script( 'chart', SIGA4W_PLUGIN_URL . 'assets/js/morris.min.js', array('jquery') );
        }
    }

    /**
     *  Handle upload file
     *  register_settings_fields()
     */
    public function cb_file_upload($option) {

        if( !empty($_FILES["json_key"]["tmp_name"]) ) {

            add_filter( 'upload_mimes', [ $this, 'cb_add_upload_mimes' ] );

            $file = sanitize_text_field( $_FILES['json_key'] );
            $filename = sanitize_text_field( $_FILES['json_key']['name'] );

            $ext = pathinfo( $filename, PATHINFO_EXTENSION );

            if ( 'json' !== $ext ) {

                unlink( sanitize_text_field( $_FILES['json_key']['tmp_name'] ) );
                add_settings_error( 'unsupported_file', 'unsupported_file', esc_html__( 'Only json files are allowed', 'sig-ga4-widget' ) );

            }else{

                if ( ! function_exists( 'wp_handle_upload' ) ) {
                    require_once ABSPATH . 'wp-admin/includes/file.php';
                }

                $temp = wp_handle_upload( $_FILES["json_key"], [
                    'test_form' => false,
                    'unique_filename_callback' => [ $this, 'cb_rename_file' ]
                ]);

                if( isset( $temp['error'] ) ){
                    add_settings_error( 'unsupported_file', 'unsupported_file', esc_attr($temp['error']) );
                }else if ( $temp && !isset( $temp['error'] ) ) {
                    $option['json_key'] = $temp['file'];
                }
            }

        }else{

            $option['property_id'] = preg_replace('/\D/', '', $option['property_id']);
            $option['begin_date'] = preg_replace('/[^\d-]/', '', $option['begin_date']);
            $option['cache_time'] = preg_replace('/[^\d]/', '', $option['cache_time']);

            $option['post_pv_label'] = strip_tags($option['post_pv_label']);
            $option['post_pv_pos'] = strip_tags($option['post_pv_pos']);
        }

        //clear transient
        siga4w_del_cache();

        return $option;
    }

    /**
     *  support .json
     */
    public function cb_add_upload_mimes ( $existing_mimes=array() ) {
        $existing_mimes['json'] = 'application/json';
        return $existing_mimes;
    }

    /**
     *  rename upload filename
     */
    public function cb_rename_file($dir, $name, $ext) {
        return time().'_'.md5($name).$ext;
    }

    /**
     *  echo widget data
     */
    public function add_wpajax_widget_data() {

        if( !empty($_GET['id']) ){

            $id = sanitize_text_field( wp_unslash( $_GET['id'] ) );

            if( check_ajax_referer( 'siga4w-widget-'.$id, false, false ) === 1){
                $array = explode("-",$id);
                if( count($array) == 2 ){

                    $widget_id = end($array);

                    if( !empty($widget_id) ){
                        $obj = new SIGA4W_widget();
                        echo $obj->show_ajax_content($widget_id);
                    }
                }
            }
        }else{
            echo '-';
        }

        wp_die();
    }

    /**
     *  Add pageviews on post content
     *  @since 1.0.1 add option:post_pv_pos
     */
    public function add_post_pv($content) {

        if( !is_front_page() && is_singular() ) {

            if( !empty($this->options['post_pv'][get_post_type()]) && $this->options['post_pv'][get_post_type()] == 'yes' ){

                $pv_label = ( !empty($this->options['post_pv_label']) ) ? $this->options['post_pv_label'] : $this->def_pv_label;
                $pv_label = sprintf( $pv_label, number_format($this->get_pageviews()) );

                $pos = ( !empty($this->options['post_pv_pos']) ) ? $this->options['post_pv_pos'] : 'top-left';
                $pos = explode('-',$pos);

                if( !empty($pos[1]) ){
                    switch ($pos[1]){
                        case 'center':
                            $pv_label = "<div style=\"text-align:center\">{$pv_label}</div>";
                        break;
                        case 'right':
                            $pv_label = "<div style=\"text-align:right\">{$pv_label}</div>";
                        break;
                        default:
                            $pv_label = "<div style=\"text-align:left\">{$pv_label}</div>";
                        break;
                    }
                }else{
                    $pv_label = "<div>{$pv_label}</div>";
                }

                if( isset($pos[0]) && $pos[0] == 'bottom' ){
                    $content = $content . $pv_label;
                }else{
                    $content = $pv_label . $content;
                }
            }
        }

        return $content;
    }


    /**
     *  Delete json file
     */
    public function delete_json_file() {

        if( check_ajax_referer( 'delete_json_file', false, false ) !== 1){

            wp_send_json([
                'success' => false,
                'message' => __( 'Not allow do this action', 'sig-ga4-widget' )
            ]);

        }else{

            //do unlink
            wp_delete_file($this->options['json_key']);

            //update option
            $new_option = $this->options;
            $new_option['json_key'] = null;
            update_option( [SIGA4W_OPTION], $new_option);

            wp_send_json([
                'success' => true,
                'message' => 'OK'
            ]);
        }

        wp_die();
    }

    /**
     *  Get post pageviews
     *  @return int
     */
    private function get_pageviews($postId=0) {

        if( empty($postId) ) $postId = get_the_ID();

        $page_path = str_replace( home_url(), '', get_permalink($postId) );
        if( empty($page_path) ) return 0;

        $begin_date = ( !empty($this->options['begin_date']) ) ? $this->options['begin_date'] : SIGA4W_BEGIN_DATE;

        $data = siga4w_get_data([
            'dateRange' => [ $begin_date, 'today' ],
            'dimensions' => ['pageTitle','pagePath'],
            'metrics' => ['screenPageViews'],
            'dimensionFilter' => $page_path

        ],'siga4w_page_'.$postId);

        if( isset($data[0]['screenPageViews']) ){
            return $data[0]['screenPageViews'];
        }else{
            return 0;
        }

    }

}
