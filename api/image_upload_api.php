<?php
/*
Plugin Name: Image upload API
Description: API pildi ülse laadimiseks.
Version: 1.0
Author: Mikk
*/

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

// require_once statements are needed because the functions used to handle file uploads (media_handle_upload()) are not automatically loaded in WordPress when processing REST API requests.
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

// Add CORS headers
function add_cors_headers() {
    header("Access-Control-Allow-Origin: https://lohvik.ee/");
    header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
}
add_action('send_headers', 'add_cors_headers', 15);

// Upload image to wordpress media 
add_action('rest_api_init', function() {
    register_rest_route(
        'image/v1', 
        '/upload_image', [
        'methods' => 'POST',
        'callback' => 'upload_image_callback',
    ]);
});

// GET LATEST uploaded image from wordpress API
add_action('rest_api_init', function() {
    register_rest_route(
        'image/v1',
        '/latest_menu', [
        'methods' => 'GET',
        'callback' => 'get_latest_menu_image',
    ]);
});

// Upload menüü image 
function upload_image_callback() {
    error_log(print_r($_FILES, true));

    if (empty($_FILES['file'])) {
        return new WP_Error('no_file', 'No file uploaded.', ['status' => 400]);
    }
   
    $media = media_handle_upload('file', 0);

    if (is_wp_error($media)) {
        error_log(print_r($media, true));
        return new WP_Error('upload_error', 'Image upload failed.', ['status' => 400]);
    }

    return rest_ensure_response(['image_url' => wp_get_attachment_url($media)]);
}

// GET latest menüü image 
function get_latest_menu_image() {
    $args = [
        'numberposts' => 1, // Get only the latest image
        'post_type'   => 'attachment',
        'orderby'     => 'date',
        'order'       => 'DESC',
    ];
    
    $latest_image = get_posts($args);
    
    if (!empty($latest_image)) {
        $image_url = wp_get_attachment_url($latest_image[0]->ID);
        return rest_ensure_response(['image_url' => $image_url]);
    }

    return rest_ensure_response(['error' => 'No menu image found']);
}