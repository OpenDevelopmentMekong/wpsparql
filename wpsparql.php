<?php
/**
 * Plugin Name: wpsparql
 * Plugin URI: http://www.lifeformapps.com/portfolio/wpsparql/
 * Description: A wordpress plugin for querying data from an SPARQL endpoint into WP http://wordpress.org/.
 * Version: 0.9.0
 * Author: Alex Corbi (mail@lifeformapps.com)
 * Author URI: http://www.lifeformapps.com
 * License: LGPLv3.
 */
 require 'vendor/autoload.php';
 include_once plugin_dir_path(__FILE__).'utils/query-endpoint-widget.php';
 include_once plugin_dir_path(__FILE__).'utils/wpsparql_exceptions.php';
 include_once plugin_dir_path(__FILE__).'utils/wpsparql_utils.php';
 include_once plugin_dir_path(__FILE__).'utils/wpsparql_api.php';

if (!class_exists('wpsparql')) {
    class wpsparql
    {
        /**
         * Construct the plugin object.
         */
        public function __construct()
        {
            add_action('admin_init', array(&$this, 'wpsparql_admin_init'));
            add_action('admin_menu', array(&$this, 'wpsparql_add_menu'));
            add_action('admin_enqueue_scripts', array(&$this, 'wpsparql_register_plugin_styles'));
            add_action('edit_post', array(&$this, 'wpsparql_edit_post'));
            add_action('save_post', array(&$this, 'wpsparql_save_post'));
            add_action('widgets_init', create_function('', 'register_widget("Wpsparql_Query_Endpoint_Widget");'));
            add_shortcode('wpsparql_query_endpoint', array(&$this, 'wpsparql_do_shortcode_query_datasets'));
        }

        public function wpsparql_register_plugin_styles($hook)
        {
            wpsparql_log('wpsparql_register_plugin_styles');

            wp_register_style('wpsparql_css', plugins_url('wpsparql/css/wpsparql_style.css'));
            wp_enqueue_style('wpsparql_css');
        }

        public function wpsparql_do_shortcode_query_datasets($atts)
        {
            wpsparql_log('wpsparql_do_query_related_datasets: '.print_r($atts, true));

            if (!wpsparql_validate_settings_read()) {
                die;
            }

            return wpsparql_show_query_datasets($atts);
        }

        public function wpsparql_save_post($post_ID)
        {
            wpsparql_log('wpsparql_save_post: '.$post_ID);

            wpsparql_edit_post_logic_dataset_metabox($post_ID);
        }

        public function wpsparql_edit_post($post_ID)
        {
            wpsparql_log('wpsparql_edit_post: '.$post_ID);

          // If this is an autosave, our form has not been submitted,
          //     so we don't want to do anything.
          if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
              return $post_ID;
          }

          // Check the user's permissions.
          if ('page' == $_POST['post_type']) {
              if (!current_user_can('edit_page', $post_ID)) {
                  return $post_ID;
              }
          } else {
              if (!current_user_can('edit_post', $post_ID)) {
                  return $post_ID;
              }
          }

            wpsparql_edit_post_logic_dataset_metabox($post_ID);
        }

        /**
         * Activate the plugin.
         */
        public static function activate()
        {
            // Do nothing
            wpsparql_log('wpsparql plugin activated');
        }

        /**
         * Deactivate the plugin.
         */
        public static function deactivate()
        {
            // Do nothing
            wpsparql_log('wpsparql plugin deactivated');
        }

        /**
         * hook into WP's admin_init action hook.
         */
        public function wpsparql_admin_init()
        {
            $this->init_settings();
        }

        /**
         * Initialize some custom settings.
         */
        public function init_settings()
        {
            register_setting('wpsparql-group', 'wpsparql_setting_sparql_url', 'wpsparql_sanitize_url');
            register_setting('wpsparql-group', 'wpsparql_setting_log_path');
            register_setting('wpsparql-group', 'wpsparql_setting_log_enabled');
            register_setting('wpsparql-group', 'wpsparql_supported_namespaces');

            foreach (get_post_types() as $post_type) {
                $settings_name = 'wpsparql_setting_supported_post_types_'.$post_type;
                register_setting('wpsparql-group', $settings_name);
            }
        }

        /**
         * add a menu.
         */
        public function wpsparql_add_menu()
        {
            add_options_page('WPSPARQL Settings', 'wpsparql', 'manage_options', 'wpsparql', array(&$this, 'plugin_settings_page'));
        }

        /**
         * Menu Callback.
         */
        public function plugin_settings_page()
        {
            if (!current_user_can('manage_options')) {
                wp_die(__('You do not have sufficient permissions to access this page.'));
            }

            wp_register_script('wpsparql_js', plugins_url('wpsparql/js/wpsparql_settings_logic.js'), array('jquery'));
            wp_enqueue_script('wpsparql_js');

            include sprintf('%s/templates/settings.php', dirname(__FILE__));
        }
    }
}

if (class_exists('wpsparql')) {
    // Installation and uninstallation hooks
    register_activation_hook(__FILE__, array('wpsparql', 'activate'));
    register_deactivation_hook(__FILE__, array('wpsparql', 'deactivate'));

    // instantiate the plugin class
    $wpsparql = new wpsparql();

    // Add a link to the settings page onto the plugin page
    if (isset($wpsparql)) {
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
