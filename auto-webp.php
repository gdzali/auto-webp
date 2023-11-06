<?php
/**
 * Plugin Name: Auto WebP Converter 
 * Description: Automatically convert and optimize images to WebP format on upload and rename them if attached to a post.
 * Version: 0.1 Beta
 * Author: Ali G.
 * Author URI: https://github.com/gdzali
 */

use WebPConvert\WebPConvert;

// Hook into the image upload process
function webp_converter_convert_images($attachment_id) {
    // Get the uploaded image file
    $file = get_attached_file($attachment_id);

    // Check if it's an image file (JPEG, PNG, GIF)
    if (wp_attachment_is_image($attachment_id)) {
        // Optimize the image
        webp_converter_optimize_image($file);

        // Convert the optimized image to WebP format using the webp-convert library
        webp_converter_generate_webp($file, $attachment_id);
    }
}

add_action('add_attachment', 'webp_converter_convert_images');

// Function to optimize an image using GD library
function webp_converter_optimize_image($file) {
    // (Previous optimization code here)
}

// Function to convert an image to WebP using webp-convert library
function webp_converter_generate_webp($file, $attachment_id) {
    require_once('vendor/autoload.php'); // Adjust the path as needed

    $destination = preg_replace('/\.(jpe?g|png|gif)$/', '.webp', $file);

    try {
        WebPConvert::convert($file, $destination);

        // Check if the attachment is associated with a post or page
        $parent_post = get_post($attachment_id);

        if ($parent_post && in_array($parent_post->post_type, ['post', 'page'])) {
            // Rename the WebP file to match the page's slug
            $slug = sanitize_title($parent_post->post_name);
            $new_destination = pathinfo($destination);
            $new_destination['filename'] = $slug;
            $new_destination = $new_destination['dirname'] . '/' . $new_destination['filename'] . '.' . $new_destination['extension'];
            rename($destination, $new_destination);
            $destination = $new_destination;
        }

        // Update the post metadata to point to the renamed WebP version of the image
        update_post_meta($attachment_id, '_wp_attached_file', str_replace(basename($file), basename($destination), $file));
    } catch (Exception $e) {
        // Handle conversion errors here
        error_log('WebP conversion error: ' . $e->getMessage());
    }
}
