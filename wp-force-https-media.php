<?php
/*
Plugin Name: Force HTTPS Media URLs in REST API
Description: Ensures media attachment URLs in the REST API are served over HTTPS.
Version: 1.0
Author: Tyler Rilling
*/


// Ensure the plugin is being run within WordPress
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Hook into the REST API response for posts
add_filter( 'rest_post_dispatch', 'acf_force_https_in_json_response', 10, 2 );

/**
 * Loop through the REST API response and convert image URLs to HTTPS.
 *
 * @param WP_HTTP_Response $response The HTTP response object.
 * @param WP_REST_Request $request The current request object.
 * @return WP_HTTP_Response The modified response.
 */
function acf_force_https_in_json_response( $response, $request ) {
    // Only modify REST API responses
    if ( ! $response instanceof WP_REST_Response ) {
        return $response;
    }

    // Get the data from the response
    $data = $response->get_data();

    // Recursively search and replace HTTP image URLs with HTTPS
    $data = search_and_replace_http_with_https( $data );

    // Set the modified data back to the response
    $response->set_data( $data );

    return $response;
}

/**
 * Recursively search for any HTTP image URLs and replace them with HTTPS.
 *
 * @param mixed $data The response data to search.
 * @return mixed The modified data with HTTPS image URLs.
 */
function search_and_replace_http_with_https( $data ) {
    // If the data is an array, loop through it recursively
    if ( is_array( $data ) ) {
        foreach ( $data as $key => $value ) {
            $data[$key] = search_and_replace_http_with_https( $value );
        }
    }
    // If the data is a string, check if it contains an image URL
    elseif ( is_string( $data ) ) {
        // Check if it's an image URL and starts with HTTP
        if ( strpos( $data, 'http://' ) === 0 && preg_match( '/\.(jpg|jpeg|png|gif|webp|svg)$/i', $data ) ) {
            $data = str_replace( 'http://', 'https://', $data );
        }
    }

    return $data;
}
