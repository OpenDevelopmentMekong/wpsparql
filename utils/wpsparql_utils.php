<?php

  define("WPSPARQL_FREQ_NEVER","0");
  define("WPSPARQL_FREQ_POST_SAVED","1");
  define("WPSPARQL_FILTER_ALL","0");
  define("WPSPARQL_FILTER_ONLY_WITH_RESOURCES","1");
  define("WPSPARQL_DEFAULT_LOG","/tmp/wpsparql.log");

  require_once dirname(dirname(__FILE__)) . '/vendor/autoload.php';

  use Analog\Analog;
  use Analog\Handler;

  function wpsparql_edit_post_logic_dataset_metabox($post_ID){
    wpsparql_log("wpsparql_edit_post_logic_datasets_metabox: " . $post_ID);

    if ( ! isset( $_POST['wpsparql_add_related_datasets_nonce'] ) )
    return $post_ID;

    $nonce = $_POST['wpsparql_add_related_datasets_nonce'];

    if ( ! wp_verify_nonce( $nonce, 'wpsparql_add_related_datasets' ) )
    return $post_ID;

    $datasets_json = $_POST['wpsparql_add_related_datasets_datasets'];

    // Update the meta field.
    update_post_meta( $post_ID, 'wpsparql_related_datasets', $datasets_json );

  }

  /*
  * Shortcodes
  */

  function wpsparql_show_query_datasets($atts) {
    wpsparql_log("wpsparql_show_query_datasets "  . print_r($atts,true));

    if (!isset($atts['query']))
      wpsparql_api_call_error("wpsparql_show_query_datasets",null);

    $result;
    try{
      $result = wpsparql_api_query_datasets($atts);
    }catch(Exception $e){
      wpsparql_log($e->getMessage());
    }

    return wpsparql_output_template( plugin_dir_path( __FILE__ ) . '../templates/result_list.php',$result,$atts);
  }

  /*
  * Templates
  */

  function wpsparql_output_template($template_url,$data,$atts){
    ob_start();
    require $template_url;
    $output = ob_get_contents();
    ob_end_clean();
    return $output;
  }

  /*
  * Logging
  */

  function wpsparql_log($text) {
    if (!get_option('wpsparql_setting_log_enabled')) return;

    $bt = debug_backtrace();
    $caller = array_shift($bt);

    if (!wpsparql_is_null_or_empty_string(get_option('wpsparql_setting_log_path')))
      Analog::handler(Handler\File::init (get_option('wpsparql_setting_log_path')));
    else
      Analog::handler(Handler\File::init (WPSPARQL_DEFAULT_LOG));

    Analog::log ( "[ " . $caller['file'] . " | " . $caller['line'] . " ] " . $text );
  }

  /*
  * Utilities
  */

  function wpsparql_is_supported_post_type($post_type){
   $settings_name =  "wpsparql_setting_supported_post_types_" . $post_type;
   return get_option($settings_name);
  }

  function wpsparql_validate_settings_read(){
    return wpsparql_api_ping();
  }

  function wpsparql_sanitize_url($input) {
    $clean_url = esc_url($input);
    if(substr($clean_url, -1) == '/') {
      $clean_url = substr($clean_url, 0, -1);
    }
    return $clean_url;
  }

  function wpsparql_pagination_last($count,$limit,$page) {
    wpsparql_log("wpsparql_pagination_last");
    return (($count >= ($limit * ($page -1 ))) && ($count <= ($limit * $page)));
  }

  function wpsparql_pagination_first($page) {
    wpsparql_log("wpsparql_pagination_first");
    return ($page == 1);
  }

  function wpsparql_is_null_or_empty_string($question){
    return (!isset($question) || trim($question)==='');
  }

  function wpsparql_is_null($question){
    return !isset($question);
  }

  function wpsparql_is_valid_url($url){
    if (filter_var($url, FILTER_VALIDATE_URL) != false){
     return true;
    }
    return false;
  }

  function wpsparql_get_url_extension($url){
    $path = parse_url($url, PHP_URL_PATH);
    return pathinfo($path, PATHINFO_EXTENSION);
  }

  function wpsparql_get_url_extension_or_html($url){
    $ext = wpsparql_get_url_extension($url);
    if ($ext)
     return $ext;
    return 'html';
  }

?>
