<?php
/**
 * Plugin Name: wpsparql
 * Plugin URI: http://www.lifeformapps.com/portfolio/wpsparql/
 * Description: A wordpress plugin for querying data from an SPARQL endpoint into WP http://wordpress.org/.
 * Version: 0.9.0
 * Author: Alex Corbi (mail@lifeformapps.com)
 * Author URI: http://www.lifeformapps.com
 * License: LGPLv3
 */

 require 'vendor/autoload.php';
 include_once plugin_dir_path( __FILE__ ) . 'utils/wpsparql_exceptions.php' ;
 include_once plugin_dir_path( __FILE__ ) . 'utils/wpsparql_utils.php' ;
 include_once plugin_dir_path( __FILE__ ) . 'utils/wpsparql_api.php' ;

if(!class_exists('wpsparql'))
{
    class wpsparql
    {
        /**
         * Construct the plugin object
         */
        public function __construct()
        {
          add_action('admin_init', array(&$this, 'wpsparql_admin_init'));
          add_action('admin_menu', array(&$this, 'wpsparql_add_menu'));
          add_action('admin_enqueue_scripts', array( &$this, 'wpsparql_register_plugin_styles' ) );
          add_action('edit_post', array(&$this, 'wpsparql_edit_post'));
          add_action('save_post', array(&$this, 'wpsparql_save_post'));
          add_action('add_meta_boxes', array(&$this, 'wpsparql_add_meta_boxes'));
          add_shortcode('wpsparql_related_datasets', array(&$this, 'wpsparql_do_shortcode_get_related_datasets'));
          add_shortcode('wpsparql_number_of_related_datasets', array(&$this, 'wpsparql_do_shortcode_get_number_of_related_datasets'));
          add_shortcode('wpsparql_query_datasets', array(&$this, 'wpsparql_do_shortcode_query_datasets'));
        }

        public function wpsparql_register_plugin_styles($hook) {
          wpsparql_log("wpsparql_register_plugin_styles");

          wp_register_style( 'wpsparql_css', plugins_url( 'wpsparql/css/wpsparql_style.css'));
          wp_enqueue_style( 'wpsparql_css' );
        }

        function wpsparql_do_shortcode_get_related_datasets($atts) {
          wpsparql_log("wpsparql_do_shortcode_get_related_datasets: " . print_r($atts,true));

          if (!wpsparql_validate_settings_read()) return;

          $atts["post_id"] = get_the_ID();
          return wpsparql_show_related_datasets($atts);
        }

        function wpsparql_do_shortcode_get_number_of_related_datasets($atts) {
          wpsparql_log("wpsparql_do_shortcode_get_number_of_related_datasets: " . print_r($atts,true));

          if (!wpsparql_validate_settings_read()) return;

          $atts["post_id"] = get_the_ID();
          return wpsparql_show_number_of_related_datasets($atts);
        }

        function wpsparql_do_shortcode_query_datasets($atts) {
          wpsparql_log("wpsparql_do_query_related_datasets: " . print_r($atts,true));

          if (!wpsparql_validate_settings_read()) die;

          return wpsparql_show_query_datasets($atts);
        }

        function wpsparql_add_meta_boxes($post_type) {
          wpsparql_log("wpsparql_add_meta_boxes: " . $post_type . " " . print_r(get_post_types(),true));

          $post_types = apply_filters('wpsparql_filter_post_types', get_post_types());
          if (in_array( $post_type, $post_types ) && wpsparql_is_supported_post_type($post_type)) {
           add_meta_box('wpsparql_add_related_datasets',__( 'Add related CKAN content', 'wpsparql_add_related_datasets_title' ),array(&$this, 'wpsparql_render_dataset_meta_box'),$post_type,'side','high');
          }

          wp_register_script( 'wpsparql_bloodhound', plugins_url( 'wpsparql/vendor/twitter/typeahead.js/dist/bloodhound.min.js'), array('jquery') );
          wp_enqueue_script( 'wpsparql_bloodhound');
          wp_register_script( 'wpsparql_typeahead', plugins_url( 'wpsparql/vendor/twitter/typeahead.js/dist/typeahead.jquery.js'), array('jquery') );
          wp_enqueue_script( 'wpsparql_typeahead');
          wp_register_script( 'wpsparql_js', plugins_url( 'wpsparql/js/wpsparql_metabox_logic.js'), array('jquery') );
          wp_enqueue_script( 'wpsparql_js');
        }

        function wpsparql_render_dataset_meta_box( $post ) {
          wpsparql_log("wpsparql_render_dataset_meta_box: " . print_r($post,true));

          wp_nonce_field('wpsparql_add_related_datasets', 'wpsparql_add_related_datasets_nonce');
          $related_datasets_json = get_post_meta( $post->ID, 'wpsparql_related_datasets', true );
          $related_datasets = array();
          if (!wpsparql_is_null_or_empty_string($related_datasets_json))
            $related_datasets = json_decode($related_datasets_json,true);

          //We do not use wpsparql_output_template here, just require.
          require 'templates/related_datasets_metabox.php';
        }

        function wpsparql_save_post( $post_ID ) {
          wpsparql_log("wpsparql_save_post: " . $post_ID);

          wpsparql_edit_post_logic_dataset_metabox($post_ID);
        }

        function wpsparql_edit_post( $post_ID ) {
          wpsparql_log("wpsparql_edit_post: " . $post_ID);

          // If this is an autosave, our form has not been submitted,
          //     so we don't want to do anything.
          if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
             return $post_ID;

          // Check the user's permissions.
          if ( 'page' == $_POST['post_type'] ) {

            if ( ! current_user_can( 'edit_page', $post_ID ) )
              return $post_ID;

          } else {

            if ( ! current_user_can( 'edit_post', $post_ID ) )
              return $post_ID;
          }

          wpsparql_edit_post_logic_dataset_metabox($post_ID);
        }

        /**
         * Activate the plugin
         */
        public static function activate()
        {
            // Do nothing
            wpsparql_log('wpsparql plugin activated');
        }

        /**
         * Deactivate the plugin
         */
        public static function deactivate()
        {
            // Do nothing
            wpsparql_log('wpsparql plugin deactivated');
        }

        /**
         * hook into WP's admin_init action hook
         */
        public function wpsparql_admin_init()
        {
            $this->init_settings();

        }

        /**
         * Initialize some custom settings
         */
        public function init_settings()
        {
            register_setting('wpsparql-group', 'setting_sparql_url' , 'wpsparql_sanitize_url');
            register_setting('wpsparql-group', 'setting_ckan_api');
            register_setting('wpsparql-group', 'setting_ckan_organization');
            register_setting('wpsparql-group', 'setting_ckan_group');
            register_setting('wpsparql-group', 'setting_ckan_valid_settings_read');
            register_setting('wpsparql-group', 'setting_ckan_valid_settings_write');
            register_setting('wpsparql-group', 'setting_log_path');
            register_setting('wpsparql-group', 'setting_log_enabled');

            foreach (get_post_types() as $post_type){
             $settings_name =  "setting_supported_post_types_" . $post_type;
             register_setting('wpsparql-group', $settings_name);
            }
        }

        /**
         * add a menu
         */
        public function wpsparql_add_menu()
        {
            add_options_page('WPSPARQL Settings', 'wpsparql', 'manage_options', 'wpsparql', array(&$this, 'plugin_settings_page'));
        }

        /**
         * Menu Callback
         */
        public function plugin_settings_page()
        {
            if(!current_user_can('manage_options'))
            {
                wp_die(__('You do not have sufficient permissions to access this page.'));
            }

            include(sprintf("%s/templates/settings.php", dirname(__FILE__)));
        }

    }
}


if(class_exists('wpsparql'))
{
    // Installation and uninstallation hooks
    register_activation_hook(__FILE__, array('wpsparql', 'activate'));
    register_deactivation_hook(__FILE__, array('wpsparql', 'deactivate'));

    // instantiate the plugin class
    $wpsparql = new wpsparql();

    // Add a link to the settings page onto the plugin page
    if(isset($wpsparql))
    {
        // Add the settings link to the plugins page
        function wpsparql_plugin_settings_link($links)
        {
            $settings_link = '<a href="options-general.php?page=wpsparql">Settings</a>';
            array_unshift($links, $settings_link);
            return $links;
        }

        $plugin = plugin_basename(__FILE__);
        add_filter("plugin_action_links_$plugin", 'wpsparql_plugin_settings_link');
    }
}

?>
