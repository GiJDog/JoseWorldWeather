<?php
/*
Plugin Name: Jose World Weather
Plugin URI: www.thenextprojectinmind.com
Description: Reads the World Weather REST API and Provides a shortcode and widget. Also does Google Reverse Goecoding.
Version: 1.0
Author: Jose Dominguez
Author URI: www.thenextprojectinmind.com
Text Domain: Jose-plugin
License: GPLv3
 */

register_activation_hook(__FILE__, 'jose_world_weather_install');

function jose_world_weather_install(){
    global $wp_version;

    if( version_compare($wp_version, '4.1', '<')){
        wp_die('This plugin requires WordPress Version 4.1 or higher.');
    }
}

register_deactivation_hook(__FILE__, 'jose_world_weather_deactivate');

function jose_world_weather_deactivate(){
    //do something when deactivating
}

add_shortcode( 'world', 'get_world_json_shortcode' );

function get_world_json_shortcode($atts, $content = null){

    $url = 'http://api.worldweatheronline.com/premium/v1/weather.ashx?key=dcb05bcdd89e45a182600557171911&q=34.6463901,-102.7143376&num_of_days=1&tp=3&format=json';

    //Get JSON into array
    $data = get_world_json($url);

    $output = '';

    $output .= $data['minutely']['summary'];
    $output .='<br>';
    $output .= "Temp:  " . round($data['currently']['temperature']) . " °F";

    return $output;

}

/**
 * @param $apikey
 * @param $lat
 * @param $lon
 *
 * Constructs the Google Maps Geocoding URL
 * @return string
 */
function get_google_reverse_geocode_url($apikey, $lat, $lon){
    //https://maps.googleapis.com/maps/api/geocode/json?latlng=40.714224,-73.961452&key=YOUR_API_KEY
    $google_url = 'https://maps.googleapis.com/maps/api/geocode/json?';
    $google_url .= 'latlng=' . $lat . ',' . $lon;
    $google_url .= '&key=' . $apikey;

    return $google_url;
}

/**
 * @param $url
 * Gets JSON from the Google Reverse Geocode API
 * @return array|mixed|object|string
 */
function get_google_reverse_geocode_json($url){

    $request = wp_remote_get( $url );

    if( is_wp_error( $request ) ) {
        return 'could not obtain data'; // Bail early
    }else {

        //retreive message body from web service
        $body = wp_remote_retrieve_body( $request );

        //obtain JSON - as object or array
        $data = json_decode( $body, true );

        return $data;
    }
}

/**
 * @param $apikey
 * @param $lat
 * @param $lon
 *
 * Constructs the World Weather API URL
 *
 * @return string
 */

http://api.worldweatheronline.com/premium/v1/weather.ashx?key=xxxxxxxxxxxxx&q=48.85,2.35&num_of_days=2&tp=3&format=xml

function get_weather_url($apikey, $lat, $lon){

    $world_url = 'http://api.worldweatheronline.com/premium/v1/weather.ashx?key=';
    $world_url .= $apikey . '&q=';
    $world_url .= $lat . ',' . $lon . '&num_of_days=1&tp=3&format=json';

    return $world_url;
}

/**
 * @param $url
 * Gets JSON from the World Weather API
 * @return array|mixed|object|string
 */
function get_world_json($url){

    $request = wp_remote_get( $url );

    if( is_wp_error( $request ) ) {
        return 'could not obtain data'; // Bail early
    }else {

        //retreive message body from web service
        $body = wp_remote_retrieve_body( $request );

        //obtain JSON - as object or array
        $data = json_decode( $body, true );

        return $data;
    }
}

add_action( 'widgets_init', 'jose_world_weather_create_widgets' );

function jose_world_weather_create_widgets() {
    register_widget( 'Jose_Worldly_Weather' );
}

class Jose_Worldly_Weather extends WP_Widget {
    // Construction function
    function __construct () {
        parent::__construct( 'Jose_Worldly_Weather', 'World Weather',
            array( 'description' =>
                'Displays current weather from the World Weather API' ) );
    }

    /**
     * @param array $instance
     * Code to show the administrative interface for the Widget
     */
    function form( $instance ) {
        // Retrieve previous values from instance
        // or set default values if not present
        $world_api_key = ( !empty( $instance['world_api_key'] ) ?
            esc_attr( $instance['world_api_key'] ) :
            'error' );

        $world_api_lat = ( !empty( $instance['world_api_lat'] ) ?
            esc_attr( $instance['world_api_lat'] ) : 'error');

        $world_api_lon = ( !empty( $instance['world_api_lon'] ) ?
            esc_attr( $instance['world_api_lon'] ) :
            'error' );

        $google_maps_api_key = ( !empty( $instance['google_maps_api_key'] ) ?
            esc_attr( $instance['google_maps_api_key'] ) :
            'error' );

        $widget_title = ( !empty( $instance['widget_title'] ) ?
            esc_attr( $instance['widget_title'] ) :
            'World Weather' );

        ?>
        <!-- Display fields to specify title and item count -->
        <p>
            <label for="<?php echo
            $this->get_field_id( 'widget_title' ); ?>">
                <?php echo 'Widget Title:'; ?>
                <input type="text"
                       id="<?php echo
                       $this->get_field_id( 'widget_title' );?>"
                       name="<?php
                       echo $this->get_field_name( 'widget_title' ); ?>"
                       value="<?php echo $widget_title; ?>" />
            </label>
        </p>
        <p>
            <label for="<?php echo
            $this->get_field_id( 'world_api_key' ); ?>">
                <?php echo 'World Weather API Key:'; ?>
                <input type="text"
                       id="<?php echo
                       $this->get_field_id( 'world_api_key' );?>"
                       name="<?php
                       echo $this->get_field_name( 'world_api_key' ); ?>"
                       value="<?php echo $world_api_key; ?>" />
            </label>
        </p>
        <p>
            <label for="<?php echo
            $this->get_field_id( 'world_api_lat' ); ?>">
                <?php echo 'World Weather API Latitude:'; ?>
                <input type="text"
                       id="<?php echo
                       $this->get_field_id( 'world_api_lat' );?>"
                       name="<?php
                       echo $this->get_field_name( 'world_api_lat' ); ?>"
                       value="<?php echo $world_api_lat; ?>" />
            </label>
        </p>
        <p>
            <label for="<?php echo
            $this->get_field_id( 'world_api_lon' ); ?>">
                <?php echo 'World Weather API Longitude:'; ?>
                <input type="text"
                       id="<?php echo
                       $this->get_field_id( 'world_api_lon' );?>"
                       name="<?php
                       echo $this->get_field_name( 'world_api_lon' ); ?>"
                       value="<?php echo $world_api_lon; ?>" />
            </label>
        </p>
        <p>
            <label for="<?php echo
            $this->get_field_id( 'google_maps_api_key' ); ?>">
                <?php echo 'Google Maps API Key:'; ?>
                <input type="text"
                       id="<?php echo
                       $this->get_field_id( 'google_maps_api_key' );?>"
                       name="<?php
                       echo $this->get_field_name( 'google_maps_api_key' ); ?>"
                       value="<?php echo $google_maps_api_key; ?>" />
            </label>
        </p>
        <script>
            jQuery(document).ready(function(){
                if(navigator.geolocation){
                    navigator.geolocation.getCurrentPosition(showLocation);
                }else{
                    console.log('Geolocation is not supported by this browser.');
                    jQuery('#location').html('Geolocation is not supported by this browser.');
                }
            });

            function showLocation(position){
                var latitude = position.coords.latitude;

                console.log("latitude: " + latitude);

                document.getElementById('<?php echo $this->get_field_id( 'world_api_lat' ); ?>')
                    .setAttribute('value', latitude);

                var longitude = position.coords.longitude;

                console.log("longitude: " + longitude);

                document.getElementById('<?php echo $this->get_field_id( 'world_api_lon' ); ?>')
                    .setAttribute('value', longitude);

            }
        </script>

    <?php }

    /**
     * @param array $new_instance
     * @param array $instance
     *
     * Code to update the admin interface for the widget
     *
     * @return array
     */
    function update( $new_instance, $instance ) {

        $instance['widget_title'] =
            sanitize_text_field( $new_instance['widget_title'] );

        $instance['world_api_key'] =
            sanitize_text_field( $new_instance['world_api_key'] );

        $instance['world_api_lat'] =
            sanitize_text_field( $new_instance['world_api_lat'] );

        $instance['world_api_lon'] =
            sanitize_text_field( $new_instance['world_api_lon'] );

        $instance['google_maps_api_key'] =
            sanitize_text_field( $new_instance['google_maps_api_key'] );

        return $instance;
    }

    /**
     * @param array $args
     * @param array $instance
     *
     * Code for the display of the widget
     *
     */
    function widget( $args, $instance ) {

        // Extract members of args array as individual variables
        extract( $args );

        $widget_title = ( !empty( $instance['widget_title'] ) ?
            esc_attr( $instance['widget_title'] ) :
            'World Weather' );

        $widget_world_api_key = ( !empty( $instance['world_api_key'] ) ?
            esc_attr( $instance['world_api_key'] ) :
            '0' );

        $widget_lat = ( !empty( $instance['world_api_lat'] ) ?
            esc_attr( $instance['world_api_lat'] ) :
            '0' );

        $widget_lon = ( !empty( $instance['world_api_lon'] ) ?
            esc_attr( $instance['world_api_lon'] ) :
            '0' );

        $widget_google_maps_api_key = ( !empty( $instance['google_maps_api_key'] ) ?
            esc_attr( $instance['google_maps_api_key'] ) :
            '0' );

        //get URLs
        $url_world = get_weather_url($widget_world_api_key, $widget_lat, $widget_lon);
        $url_google = get_google_reverse_geocode_url($widget_google_maps_api_key, $widget_lat, $widget_lon);

        //obtain JSON - as object or array
        $data_world = get_world_json($url_world);
        $data_google = get_google_reverse_geocode_json($url_google);
        $date=date_create($data_world['data']['weather'][0]['date']);

        //$output .= print_r($data_world);

        // Display widget title
        echo $before_widget . $before_title;
        echo apply_filters( 'widget_title', $widget_title );
        echo $after_title;

        //echo "Weather information for: " . $data_google['results'][0]['address_components']['long_name'];
        echo '<table class="weatherTable"><tr><td>';



        echo "<b>Weather info for " . $data_google['results'][0]['address_components'][6]['long_name'] .":</b><br>";
        /* echo $data_google['results'][0]['address_components'][2]['long_name'] . ", ";
         echo $data_google['results'][0]['address_components'][3]['long_name'] . ", ";
         echo $data_google['results'][0]['address_components'][4]['long_name'] . ", ";
         echo $data_google['results'][0]['address_components'][5]['long_name']; */
        echo '<br>';
        echo date_format($date,"m/d/Y");
        //https://wordpress.stackexchange.com/questions/60230/how-to-call-images-from-your-plugins-image-folder
        /*echo '<img src="' . plugin_dir_url( __FILE__ ) .
            'World-icons/PNG/' . $data_world['currently']['icon'] . '.png">';*/
        echo '<br>';
        echo '<table><tr><td colspan="2" class="weatherCellcenter">';
        echo '<img src="' . $data_world['data']['current_condition'][0]['weatherIconUrl'][0]['value'].'">';
        echo '</td></tr><tr><td colspan="2" class="weatherCellcenter">';
        echo  $data_world['data']['current_condition'][0]['weatherDesc'][0]['value'];
        echo '</td></tr><tr><td style="width:50%"  class="weatherCell">';
        echo "Temperature: ";
        echo '</td><td style="width:50%">';
        echo  round($data_world['data']['current_condition'][0]['temp_F']) . " °F";
        echo '</td></tr><tr><td class="weatherCell">';
        echo "Dew Point: ";
        echo '</td><td>';
        echo round($data_world['data']['weather'][0]['hourly'][0]['DewPointF']) . " °F";
        echo '</td></tr><tr><td class="weatherCell">';
        echo "Humdity: ";
        echo '</td><td>';
        echo (floatval($data_world['data']['current_condition'][0]['humidity'])) . "%";
        echo '</td></tr><tr><td class="weatherCell">';
        echo "Wind Direction: ";
        echo '</td><td>';
        echo $data_world['data']['current_condition'][0]['winddirDegree'] . "°";
        echo '</td></tr><tr><td class="weatherCell">';
        echo "Wind Speed: ";
        echo '</td><td>';
        echo round($data_world['data']['current_condition'][0]['windspeedMiles']) . " mph";
        echo '</td></tr><tr><td class="weatherCell">';
        echo "Pressure: ";
        echo '</td><td>';
        echo round($data_world['data']['current_condition'][0]['pressure']) . " mb";
        echo '</td></tr></table>';
        echo '</td></tr></table>';

        echo $after_widget;
    }
}
?>