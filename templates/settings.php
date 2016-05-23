<div class="wrap">
    <h2>WPSPARQL -  A plugin for querying a sparql endpoint into WP</h2>
    <form method="post" action="options.php">
        <?php @settings_fields('wpsparql-group'); ?>
        <?php @do_settings_fields('wpsparql-group'); ?>

        <?php
          wpsparql_log('Rendering settings.php');
          $sparql_url = get_option('wpsparql_setting_sparql_url');
          $logging_path = get_option('wpsparql_setting_log_path');
          $logging_enabled = get_option('wpsparql_setting_log_enabled');
          if (!$logging_path) {
              $logging_path = WPSPARQL_DEFAULT_LOG;
          }
          $valid_connection_read = wpsparql_validate_settings_read();
          update_option('setting_sparql_valid_settings_read', $valid_connection_read);
          $supported_namespaces = get_option('wpsparql_supported_namespaces');
        ?>

        <table class="form-table">
          <th scope="row"><label><h3><?php _e('Connecting to SPARQL endpoint', 'wpsparql') ?></h3></label></th>
          <tr valign="top">
              <th scope="row"><label for="wpsparql_setting_sparql_url"><?php _e('Sparql endpoint url', 'wpsparql') ?></label></th>
              <td>
                <input type="text" name="wpsparql_setting_sparql_url" id="wpsparql_setting_sparql_url" value="<?php echo $sparql_url ?>"/>
                <p class="description"><?php _e('Specify protocol such as http:// or https://.', 'wpsparql') ?>.</p>
              </td>
          </tr>
          <!-- Connection status -->
          <tr valign="top">
            <th scope="row"><label><?php _e('Connection status', 'wpsparql') ?></label></th>
            <td>
              <?php if ($valid_connection_read) {
    ?>
                <p class="ok"><?php _e('Sparql endpoint URL specified correctly.', 'wpsparql') ?></p>
              <?php
} else {
    ?>
                <p class="error"><?php _e('Problem connecting to Sparql endpoint. Please, check the specified URL.', 'wpsparql') ?></p>
              <?php
} ?>
            </td>
          </tr>
          <!-- Supported namespaces -->
          <tr valign="top">
            <th scope="row"><label for="settings_supported_namespaces"><?php _e('Supported namespaces', 'wpsparql') ?></label></th>
            <td>
              <ul id="supported_namespaces_list">
              </ul>
              <input type="hidden" id="wpsparql_supported_namespaces" name="wpsparql_supported_namespaces" value='<?php echo($supported_namespaces) ?>'/>
              <li class="namespace_element" id="namespace_element_placeholder"
                style="display:none;">
                <label id="namespace_element_label_prefix" for="namespace_element_input_prefix">Prefix:</label>
                <input id="namespace_element_input_prefix" type="text" name="namespace_element_input_prefix" class="namespace_element_input_prefix"/>
                <label id="namespace_element_label_iri" for="namespace_element_input_iri">IRI:</label>
                <input id="namespace_element_input_iri" type="text" name="namespace_element_input_iri" class="namespace_element_input_iri"/>
                <a id="add_namespace" href="#">Add</a>
                <a id="remove_namespace" href="#">Remove</a>
              </li>
           </td>
          </tr>
          <!-- Related datasets -->
          <tr valign="top">
            <th scope="row"><label for="settings_supported_post_types"><?php _e('Supported post types', 'wpsparql') ?></label></th>
            <td>
             <?php
              foreach (get_post_types() as $post_type) {
                  $settings_name = 'wpsparql_setting_supported_post_types_'.$post_type;
                  ?>
              <p><input type="checkbox" name="<?php echo $settings_name ?>" id="<?php echo $settings_name ?>" <?php if (get_option($settings_name)) {
    echo 'checked="true"';
}
                  ?>><?php echo $post_type ?></input></p>
             <?php
              } ?>
           </td>
          </tr>
          <!-- Logging -->
          <th scope="row"><label><h3><?php _e('Logging', 'wpsparql') ?></h3></label></th>
          <tr valign="top">
            <th scope="row"><label for="wpsparql_setting_log_enabled"><?php _e('Enable log', 'wpsparql') ?></label></th>
            <td>
              <input type="checkbox" name="wpsparql_setting_log_enabled" id="wpsparql_setting_log_enabled" <?php if ($logging_enabled) {
    echo 'checked="true"';
} ?>/>
            </td>
          </tr>
          <tr valign="top">
            <th scope="row"><label for="wpsparql_setting_log_path"><?php _e('Path', 'wpsparql') ?></label></th>
            <td>
              <input type="text" name="wpsparql_setting_log_path" id="wpsparql_setting_log_path" value="<?php echo $logging_path ?>"/>
              <p class="description"><?php _e('Path where logs are going to be stored. Mind permissions.', 'wpsparql') ?></p>
            </td>
          </tr>
        </table>
        <?php @submit_button(); ?>
    </form>
</div>
