<?php

  include_once plugin_dir_path( __FILE__ ) . 'wpsparql_utils.php' ;

  require dirname(dirname(__FILE__)) . '/vendor/autoload.php';

  /*
  * Api
  */

  function wpsparql_api_get_dataset($atts) {

    return [];

  }

  function wpsparql_api_query_datasets($atts) {

    return [];

  }

  function wpsparql_api_search_package_with_id($id){

    return [];

  }

  function wpsparql_api_ping() {

    $endpoint = get_option('setting_sparql_url');

    // Connecting to invalid endpoint should fail
    $alive = true;
    $db = new SparQL\Connection($endpoint);
    wpsparql_log($endpoint);
    try{
      $alive = $db->alive(1000);
    }catch(Exception $e){
      wpsparql_log($e->getMessage());
      $alive = false;
    }

    return $alive;

  }

  function wpsparql_api_status_show() {

    return [];

  }

  /*
  * Errors
  */

  function wpsparql_api_parameter_error($function,$message){
    $error_log = "ERROR Parameters on " . $function . " message: " . $message;
    $error_message = "Something went wrong, check your connection details";
    wpsparql_log($error_log);
    throw new WpsparqlApiParametersException($error_message);
  }

  function wpsparql_api_call_error($function,$message){
    $error_log = "ERROR API CALL on " . $function . " message: " . $message;
    $error_message = "Something went wrong, check your connection details";
    wpsparql_log($error_log);
    throw new WpsparqlApiCallException($error_message);
  }

  function wpsparql_api_settings_error($function,$message){
    $error_log = "ERROR SETTINGS on " . $function . " message: " . $message;
    $error_message = "Please, specify CKAN URL and API Key";
    wpsparql_log($error_log);
    throw new WpsparqlApiSettingsException($error_message);
  }

?>
