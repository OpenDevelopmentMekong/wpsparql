<?php if (is_null($related_datasets)) die(); ?>

<?php if (wpsparql_validate_settings_read()){ ?>

  <label for="wpsparql_related_datasets_add_field"><b><?php _e('Add related datasets','wpsparql_related_datasets_add_title') ?></b></label>
  <p>
    <input id="wpsparql_related_datasets_add_field" class="new typeahead" onInput="wpsparql_related_dataset_metabox_on_input();" wpsparql-base-url="<?php echo get_option('setting_ckan_url'); ?>" wpsparql-api-url="<?php echo wpsparql_get_ckan_settings()["baseUrl"]; ?>" placeholder="Type for suggestions" type="text" name="wpsparql_related_datasets_add_field" value="" size="25" />
    <input id="wpsparql_related_datasets_add_button" class="button add disabled" type="button" value="Add" onClick="wpsparql_related_dataset_metabox_add();" />
  </p>
  <div id="wpsparql_related_datasets_list"></div>
  <input id="wpsparql_add_related_datasets_datasets" name="wpsparql_add_related_datasets_datasets" type="hidden" value='<?php echo $related_datasets_json; ?>'/>

<?php } else { ?>

  <p class="error"><?php _e( 'wpsparql is not correctly configured. Please, check the ', 'related_datasets_metabox_config_error' ) ?><a href="options-general.php?page=wpsparql">Settings</a></p>

<?php }?>
