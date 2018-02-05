<?php

  include_once plugin_dir_path( __FILE__ ) . 'wpsparql_utils.php' ;
  use BorderCloud\SPARQL\SparqlClient;
  require_once dirname(dirname(__FILE__)) . '/vendor/autoload.php';

  /*
  * Api
  */

  function wpsparql_api_get_dataset($atts) {

    return [];

  }

  function wpsparql_api_query_datasets($atts) {

    if (!isset($atts['query']))
      wpsparql_api_call_error("wpsparql_api_query_datasets",null);

    $endpoint = get_option('wpsparql_setting_sparql_url');
    $sc = new SparqlClient();
    $sc->setEndpointRead($endpoint);

    $supported_namespaces = json_decode(get_option('wpsparql_supported_namespaces'),true);

    /*foreach ($supported_namespaces as $namespace) {
      $db->ns( $namespace["prefix"],$namespace["iri"] );
    }*/

    $fields = null;
    try{
      $result = $sc->query($atts['query'], 'rows');
      $fields = $rows["result"]["rows"];
    }catch(Exception $e){
      wpsparql_log($e->getMessage());
    }

    return $fields;

  }

  function wpsparql_api_ping() {

    $endpoint = get_option('wpsparql_setting_sparql_url');

    // Connecting to invalid endpoint should fail
    $alive = true;
    $sc = new SparqlClient();
    $sc->setEndpointRead($endpoint);
    try{
      if (false === $sc->query("select * where {?x ?y ?z.} LIMIT 5")):
        throw new Exception('Alive returned false');
      endif;
    }catch(Exception $e){
      wpsparql_log($e->getMessage());
      $alive = false;
    }

    return $alive;

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
