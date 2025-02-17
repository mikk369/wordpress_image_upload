<?php
/*
Plugin Name: React Image Upload
Description: Laeb üles menüü pildi.
Version: 1.0
Author: Mikk
*/

function react_image_upload_page() {
    add_menu_page(
        'React Image Upload',               // Page title
        'Lisa menüü pilt',                     // Menu title
        'manage_options',                   // Capability required to access
        'image-upload',               // Menu slug
        'react_image_upload_page_callback', // Callback function
        'dashicons-format-image',           // Icon for the menu
        6                                   // Position in the menu
    );
}

// Hook to add custom admin page
add_action('admin_menu', 'react_image_upload_page');

function react_image_upload_enqueue_scripts($hook) {
    if ($hook !== 'toplevel_page_image-upload') {
        return;
    }

    // Construct the path to the manifest file (file system path)
    $manifest_file = plugin_dir_path(__FILE__) . 'dist/.vite/manifest.json';

    // Check if the manifest file exists
    if (file_exists($manifest_file)) {
        // Decode the JSON content of the asset manifest file
        $manifest = json_decode(file_get_contents($manifest_file), true);

        // Check if the 'index.html' entry exists in the manifest
        if (isset($manifest['index.html'])) {
            // Get the JS file path from the manifest
            $js_file = $manifest['index.html']['file'] ?? null;

            // Enqueue the React app JS if it exists
            if ($js_file) {
                wp_enqueue_script(
                    'custom-calendar-main-js',
                    plugin_dir_url(__FILE__) . 'dist/' . $js_file, // Dynamically get JS file path
                    ['wp-element', 'wp-i18n'],
                    null, // No version (the file name includes the hash)
                    true // Load in the footer
                );
            }

            // Get the CSS files from the manifest and loop through them
            $css_files = $manifest['index.html']['css'] ?? [];
            foreach ($css_files as $css_file) {
                wp_enqueue_style(
                    'custom-calendar-main-css-' . md5($css_file), // Unique handle for each CSS file
                    plugin_dir_url(__FILE__) . 'dist/' . $css_file, // Dynamically get CSS file path
                    array(), // No dependencies
                    null // No version (the file name includes the hash)
                );
            }
        }
    } else {
        error_log('Manifest file not found at: ' . $manifest_file);
    }
}

// Enqueue React and build assets
add_action('admin_enqueue_scripts', 'react_image_upload_enqueue_scripts');

// Callback function to render the React app
function react_image_upload_page_callback() {
    // Render a div where React will be mounted
    echo '<div id="react-image-upload-app"></div>';
}
