<?php

  include_once plugin_dir_path( __FILE__ ) . 'wpsparql_utils.php' ;

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

    $guzzle = new GuzzleHttp\Client();
    $client = new CCR\Sparql\SparqlClient($guzzle);
    $client = $client->withEndpoint($endpoint);

    $supported_namespaces = json_decode(get_option('wpsparql_supported_namespaces'),true);
    foreach ($supported_namespaces as $namespace) {
      $client->withPrefix( $namespace["prefix"],$namespace["iri"] );
    }

    $result = null;
    try{
      $result = $client->query($atts['query']);
    }catch(Exception $e){
      wpsparql_log("Error running query" . $e->getMessage());
    }

    return $result;

  }

  function wpsparql_api_ping() {

    $endpoint = get_option('wpsparql_setting_sparql_url');
    $query = urlencode("SELECT * WHERE { ?s ?p ?o } LIMIT 1");

    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_RETURNTRANSFER => 1,
      CURLOPT_URL => $endpoint . '?query=' .  $query . '&format=json',
      CURLOPT_USERAGENT => 'wpsparql'
    ));
    // Send the request & save response to $resp
    $result = curl_exec($curl);
    // Close request to clear up some resources
    curl_close($curl);

    wpsparql_log($result);

    return $result;

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
