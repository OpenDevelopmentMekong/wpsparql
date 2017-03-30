<?php

class Wpsparql_Query_Endpoint_Widget extends WP_Widget {

 /**
  * Sets up the widgets name etc
  */
 public function __construct() {
  // widget actual processes
  parent::__construct(
   'wpsparql_query_endpoint_widget',
   __('WPSPARQL query endpoint', 'odi'),
   array('description' => __('Query a sparql endpoint and displays results in a post or page.', 'odi'))
  );
 }

 /**
  * Outputs the content of the widget
  *
  * @param array $args
  * @param array $instance
  */
 public function widget( $args, $instance ) {

  global $post;

   $shortcode = '[wpsparql_query_endpoint query=\'' . $instance['query'] . '\']';

   $output = do_shortcode($shortcode);

   if (!empty($output) && $output != ""){

     echo $args['before_widget'];
     if ( ! empty( $instance['title'] ) ) {
      echo $args['before_title'] . apply_filters( 'widget_title', __( $instance['title'], 'odi')). $args['after_title'];
     }

     echo $output;

     echo $args['after_widget'];

   }

 }

 /**
  * Outputs the options form on admin
  *
  * @param array $instance The widget options
  */
 public function form( $instance ) {
  // outputs the options form on admin
  $title = ! empty( $instance['title'] ) ? __( $instance['title'], 'odi') : __( 'WPSPARQL query endpoint', 'odi' );
  $query = ! empty( $instance['query'] ) ? $instance['query']: "";
  ?>
  <p>
   <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
   <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>"/>
   <label for="<?php echo $this->get_field_id( 'query' ); ?>"><?php _e( 'Query:' ); ?></label>
   <textarea class="widefat" id="<?php echo $this->get_field_id( 'query' ); ?>" name="<?php echo $this->get_field_name( 'query' ) ?>" rows="10"><?php echo esc_textarea($query); ?></textarea>
  </p>
  <?php
 }

 /**
  * Processing widget options on save
  *
  * @param array $new_instance The new options
  * @param array $old_instance The previous options
  */
 public function update( $new_instance, $old_instance ) {
  // processes widget options to be saved
  $instance = array();
  $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
  $instance['query'] = ( ! empty( $new_instance['query'] ) ) ? $new_instance['query'] : '';

  return $instance;
 }
}

add_action('widgets_init', create_function('', 'register_widget("Wpsparql_Query_Endpoint_Widget");'));
