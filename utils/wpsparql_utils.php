<?php

  define("WPSPARQL_FREQ_NEVER","0");
  define("WPSPARQL_FREQ_POST_SAVED","1");
  define("WPSPARQL_FILTER_ALL","0");
  define("WPSPARQL_FILTER_ONLY_WITH_RESOURCES","1");
  define("WPSPARQL_DEFAULT_LOG","/tmp/wpsparql.log");

  require dirname(dirname(__FILE__)) . '/vendor/autoload.php';

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

  function wpsparql_show_related_datasets($atts) {
    wpsparql_log("wpsparql_show_related_datasets " . print_r($atts,true));

    $related_datasets_json = get_post_meta( $atts['post_id'], 'wpsparql_related_datasets', true );
    $related_datasets = array();
    if (!wpsparql_is_null_or_empty_string($related_datasets_json))
      $related_datasets = json_decode($related_datasets_json,true);

    if (array_key_exists("group",$atts))
      $filter_group = $atts["group"];
    if (array_key_exists("organization",$atts)){
      $filter_organization = $atts["organization"];
    }

    $limit = 0;
    if (array_key_exists("limit",$atts)){
      $limit = (int)($atts["limit"]);
    }

    $page = 0;
    if (array_key_exists("limit",$atts) && array_key_exists("page",$atts)){
      $page = (int)($atts["page"]);
    }

    $filter = WPSPARQL_FILTER_ALL;
    if (array_key_exists("filter",$atts)){
      $filter = $atts["filter"];
    }

    $filter_fields_json = NULL;
    if (array_key_exists("filter_fields",$atts)){
      $filter_fields_json = json_decode($atts["filter_fields"],true);
    }

    $blank_on_empty = false;
    if (array_key_exists("blank_on_empty",$atts)){
      $blank_on_empty = filter_var( $atts['blank_on_empty'], FILTER_VALIDATE_BOOLEAN );
    }

    $count = 0;
    $dataset_array = array();
    $atts["related_datasets"] = $related_datasets;
    foreach ($related_datasets as $dataset){

      $qualifies_group = false;
      $qualifies_organization = false;

      if (!isset($filter_group))
       $qualifies_group = true;
      if (!isset($filter_organization))
       $qualifies_organization = true;

      // Check if dataset belongs to group
      if (isset($filter_group) && !$qualifies_group){
        $groups = json_decode($dataset["dataset_groups"], true);
        if ($groups){
         foreach ($groups as $group){
           if (strtolower($filter_group) == strtolower($group["name"])){
            $qualifies_group = true;
           }
         }
        }
      }

      // Check if dataset belongs to organization
      if (isset($filter_organization) && isset($dataset["dataset_org"]) && !$qualifies_organization){
        try{
          $organization = wpsparql_api_get_organization($dataset["dataset_org"]);
          if ( $organization["name"] == $filter_organization){
           $qualifies_organization = true;
          }
        }catch(Exception $e){
          wpsparql_log($e->getMessage());
        }
      }

      if (($page == 0) || (($count >= (($page-1) * $limit)) && ($count <= ($page * $limit)))){
       if ($qualifies_organization && $qualifies_group){
         $dataset_atts = array("id" => $dataset["dataset_id"]);
         try{
           if ($filter == WPSPARQL_FILTER_ALL || (($filter == WPSPARQL_FILTER_ONLY_WITH_RESOURCES) && wpsparql_dataset_has_resources($dataset))){
            if (wpsparql_is_null($filter_fields_json) || (!wpsparql_is_null($filter_fields_json) && wpsparql_dataset_has_matching_extras($dataset,$filter_fields_json))){
             array_push($dataset_array,wpsparql_api_get_dataset($dataset_atts));
            }
           }
         }catch(Exception $e){
           wpsparql_log($e->getMessage());
         }
         if (($limit != 0) && (count($dataset_array) >= $limit)) break;
       }
       $count++;
      }
    }
    if ((count($dataset_array) == 0) && $blank_on_empty)
      return "";

    return wpsparql_output_template( plugin_dir_path( __FILE__ ) . '../templates/dataset_list.php',$dataset_array,$atts);
  }

  function wpsparql_show_number_of_related_datasets($atts) {
    wpsparql_log("wpsparql_show_number_of_related_datasets " . print_r($atts,true));

    $related_datasets_json = get_post_meta( $atts['post_id'], 'wpsparql_related_datasets', true );
    $related_datasets = array();
    if (!wpsparql_is_null_or_empty_string($related_datasets_json))
    $related_datasets = json_decode($related_datasets_json,true);

    if (array_key_exists("group",$atts))
      $filter_group = $atts["group"];
    if (array_key_exists("organization",$atts)){
      $filter_organization = $atts["organization"];
    }

    $filter = WPSPARQL_FILTER_ALL;
    if (array_key_exists("filter",$atts)){
      $filter = $atts["filter"];
    }

    $filter_fields_json = NULL;
    if (array_key_exists("filter_fields",$atts)){
      $filter_fields_json = json_decode($atts["filter_fields"],true);
    }

    $blank_on_empty = false;
    if (array_key_exists("blank_on_empty",$atts)){
      $blank_on_empty = filter_var( $atts['blank_on_empty'], FILTER_VALIDATE_BOOLEAN );
    }

    $dataset_array = array();
    foreach ($related_datasets as $dataset){

      $qualifies_group = false;
      $qualifies_organization = false;

      if (!isset($filter_group))
       $qualifies_group = true;
      if (!isset($filter_organization))
       $qualifies_organization = true;

      // Check if dataset belongs to group
      if (isset($filter_group) && !$qualifies_group){
        $groups = json_decode($dataset["dataset_groups"], true);
        if ($groups){
         foreach ($groups as $group){
           if (strtolower($filter_group) == strtolower($group["name"])){
            $qualifies_group = true;
           }
         }
        }
      }

      // Check if dataset belongs to organization
      if (isset($filter_organization) && isset($dataset["dataset_org"]) && !$qualifies_organization){
        try{
          $organization = wpsparql_api_get_organization($dataset["dataset_org"]);
          if ( $organization["name"] == $filter_organization){
           $qualifies_organization = true;
          }
        }catch(Exception $e){
          wpsparql_log($e->getMessage());
        }
      }

      if ($qualifies_organization && $qualifies_group){
        $dataset_atts = array("id" => $dataset["dataset_id"]);
        try{
         if ($filter == WPSPARQL_FILTER_ALL || (($filter == WPSPARQL_FILTER_ONLY_WITH_RESOURCES) && wpsparql_dataset_has_resources($dataset))){
          if (wpsparql_is_null($filter_fields_json) || (!wpsparql_is_null($filter_fields_json) && wpsparql_dataset_has_matching_extras($dataset,$filter_fields_json))){
           array_push($dataset_array,$dataset_atts);
          }
         }
        }catch(Exception $e){
          wpsparql_log($e->getMessage());
        }
        if (array_key_exists("limit",$atts) && (count($dataset_array) >= (int)($atts["limit"]))) break;
      }
    }

    if ((count($dataset_array) == 0) && $blank_on_empty)
      return "";

    return wpsparql_output_template( plugin_dir_path( __FILE__ ) . '../templates/dataset_number.php',$dataset_array,$atts);
  }

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

    return wpsparql_output_template( plugin_dir_path( __FILE__ ) . '../templates/triples_list.php',$result,$atts);
  }

  // function wpsparql_show_query_datasets($atts) {
  //   wpsparql_log("wpsparql_show_query_datasets "  . print_r($atts,true));
  //
  //   $dataset_array = array();
  //   try{
  //     $result = wpsparql_api_query_datasets($atts);
  //     $dataset_array = $result["results"];
  //     $atts["count"] = $result["count"];
  //   }catch(Exception $e){
  //     wpsparql_log($e->getMessage());
  //   }
  //
  //   $filter = WPSPARQL_FILTER_ALL;
  //   if (array_key_exists("filter",$atts)){
  //     $filter = $atts["filter"];
  //   }
  //
  //   $filter_fields_json = NULL;
  //   if (array_key_exists("filter_fields",$atts)){
  //     $filter_fields_json = json_decode($atts["filter_fields"],true);
  //   }
  //
  //   $blank_on_empty = false;
  //   if (array_key_exists("blank_on_empty",$atts)){
  //     $blank_on_empty = filter_var( $atts['blank_on_empty'], FILTER_VALIDATE_BOOLEAN );
  //   }
  //
  //   $filtered_dataset_array = array();
  //   foreach ($dataset_array as $dataset){
  //    if ($filter == WPSPARQL_FILTER_ALL || (($filter == WPSPARQL_FILTER_ONLY_WITH_RESOURCES) && wpsparql_dataset_has_resources($dataset))){
  //     if (wpsparql_is_null($filter_fields_json) || (!wpsparql_is_null($filter_fields_json) && wpsparql_dataset_has_matching_extras($dataset,$filter_fields_json))){
  //      array_push($filtered_dataset_array,$dataset);
  //     }
  //    }
  //   }
  //
  //   if ((count($dataset_array) == 0) && $blank_on_empty)
  //     return "";
  //
  //   if (array_key_exists("format",$atts)){
  //     if ($atts["format"]=="json") {
  //       $json= wpsparql_output_template( plugin_dir_path( __FILE__ ) . '../templates/dataset_list_format_json.php',$filtered_dataset_array,$atts);
  //       return $json;
  //     }
  //   }
  //   else{
  //     return wpsparql_output_template( plugin_dir_path( __FILE__ ) . '../templates/dataset_list.php',$filtered_dataset_array,$atts);
  //   }
  // }

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

  function wpsparql_get_link_to_dataset($dataset_name){
    wpsparql_log("wpsparql_get_link_to_dataset "  . print_r($dataset_name,true));

    return get_option('wpsparql_setting_sparql_url') . "/dataset/" . $dataset_name;
  }

  function wpsparql_get_link_to_resource($dataset_name,$resource_id){
    wpsparql_log("wpsparql_get_link_to_resource "  . print_r($dataset_name,true) . " " . print_r($resource_id,true));

    return wpsparql_get_link_to_dataset($dataset_name) . "/resource/" . $resource_id;
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

  function wpsparql_dataset_has_resources($dataset){
    if (array_key_exists("dataset_num_resources",$dataset)){
     return ($dataset["dataset_num_resources"] >= 1);
    }

    if (array_key_exists("num_resources",$dataset)){
     return ($dataset["num_resources"] >= 1);
    }
    return false;
  }

  function wpsparql_dataset_has_matching_extras($dataset,$filter_fields_json){
    wpsparql_log("wpsparql_dataset_has_matching_extras " . print_r($dataset,true) . print_r($filter_fields_json,true));

    if (array_key_exists("dataset_extras",$dataset)){
     $extras = json_decode($dataset["dataset_extras"], true);
    }else if (array_key_exists("extras",$dataset)){
     $extras = $dataset["extras"];
    }else{
     return false;
    }

    foreach ($extras as $extra){
     $field_value = $filter_fields_json[$extra['key']];
     if (!wpsparql_is_null_or_empty_string($field_value) && strpos(strtolower($extra['value']),strtolower($field_value)) !== false){
      return true;
     }

    }

   return false;
  }

  function wpsparql_cleanup_text_for_archiving($post_content){
    $post_content = wpsparql_detect_and_remove_shortcodes_in_text($post_content);
    $post_content = wpsparql_strip_mqtranslate_tags($post_content);
    return $post_content;
  }

  function wpsparql_detect_and_remove_shortcodes_in_text($text)
  {
    global $post;
    $pattern = get_shortcode_regex();
    $shortcodes = array("wpsparql_related_datasets","wpsparql_number_of_related_datasets","wpsparql_query_datasets");

    foreach($shortcodes as $shortcode){
      if (   preg_match_all( '/'. $pattern .'/s', $post->post_content, $matches )
      && array_key_exists( 2, $matches )
      && in_array( $shortcode, $matches[2] ) )
      {
        foreach($matches as $match){
          $text = str_replace($match,"",$text);
        }
      }
    }
    return $text;
  }

  function wpsparql_detect_and_echo_shortcodes_in_text($text)
  {
    global $post;
    $pattern = get_shortcode_regex();
    $shortcodes = array("wpsparql_related_datasets","wpsparql_number_of_related_datasets","wpsparql_query_datasets");

    foreach($shortcodes as $shortcode){
      if (   preg_match_all( '/'. $pattern .'/s', $post->post_content, $matches )
      && array_key_exists( 2, $matches )
      && in_array( $shortcode, $matches[2] ) )
      {
        foreach($matches as $match){
          $text = str_replace($match,do_shortcode($match),$text);
        }
      }
    }
    return $text;
  }

  function wpsparql_get_complete_url_for_dataset($dataset){
    return get_option('wpsparql_setting_sparql_url') . "/dataset/" . $dataset["name"];
  }

  function wpsparql_get_group_names_for_user(){
    $groups = array();
    $group_names = array();
    try{
      $groups = wpsparql_api_get_group_list_for_user();
    }catch(Exception $e){
      wpsparql_log($e->getMessage());
    }
    foreach ($groups as $group){
      array_push($group_names,$group["display_name"]);
    }
    return $group_names;
  }

  function wpsparql_get_organization_names_for_user(){
    $organizations = array();
    $organization_names = array();
    try{
      $organizations = wpsparql_api_get_organization_list_for_user();
    }catch(Exception $e){
      wpsparql_log($e->getMessage());
    }
    foreach ($organizations as $organization){
      array_push($organization_names,$organization["display_name"]);
    }
    return $organization_names;
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

  function wpsparql_strip_mqtranslate_tags($input) {
    $clean_url = str_replace("<!--:-->", " ", $input);
    $clean_url = strip_tags($clean_url);
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
