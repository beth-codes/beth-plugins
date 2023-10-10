<?php

   /*
   Plugin Name: My Plugin
   Plugin URI: (optional)
   Description: Technical Test
   Version: 1.0.0
   Author: (your name)
   Author URI: (optional)
   License: (optional)
   */

// Add a submenu page under "Settings" menu

// Include the tags page file
require_once plugin_dir_path(__FILE__) . 'tags-page.php';

add_action('admin_menu', 'my_plugin_add_settings_page');

function my_plugin_add_settings_page() {
    add_options_page(
        'My Plugin Settings', // Page title
        'My Plugin', // Menu title
        'manage_options', // Capability required to access the page
        'my-plugin-settings', // Slug
        'my_plugin_render_settings_page' // Callback function to render the page
    );

    // Register the plugin settings
    add_action('admin_init', 'my_plugin_register_settings');
}

// Register the plugin settings
function my_plugin_register_settings() {
    register_setting('my_plugin_settings', 'my_plugin_site_id');
    register_setting('my_plugin_settings', 'my_plugin_post_types');
}

// Callback function to render the settings page
function my_plugin_render_settings_page() {
    ?>
    <div class="wrap">
        <h1>My Plugin Settings</h1>

        <form method="post" action="options.php">
            <?php
            // Output necessary hidden fields for options
            settings_fields('my_plugin_settings');
            do_settings_sections('my_plugin_settings');

            // Get the current Site ID option value
            $site_id = get_option('my_plugin_site_id');
            ?>

            <h2>Site ID</h2>
            <input type="text" name="my_plugin_site_id" value="<?php echo esc_attr($site_id); ?>">

            <h2>Post Types</h2>
            <p>Select the post types where you want to show the extra input field:</p>
            <?php
            // Available post types
            $post_types = get_post_types(array('public' => true), 'objects');

            foreach ($post_types as $post_type) {
                // Check if the post type is selected
                $checked = in_array($post_type->name, (array) get_option('my_plugin_post_types'));

                echo '<input type="checkbox" name="my_plugin_post_types[]" value="' . esc_attr($post_type->name) . '" ' . checked($checked, true, false) . '>';
                echo '<label>' . esc_html($post_type->labels->name) . '</label><br>';
            }
            ?>

            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Add meta box on the edit post screen
add_action('add_meta_boxes', 'my_plugin_add_meta_box');

function my_plugin_add_meta_box() {
    // Get the selected post types from plugin settings
    $selected_post_types = get_option('my_plugin_post_types');

    // Check if the current post type is in the selected post types array
    if (in_array(get_post_type(), $selected_post_types)) {
        // Add meta box for the selected post type
        add_meta_box(
            'my_plugin_tag_reference_meta_box', // Unique ID
            'Tag Reference', // Title
            'my_plugin_render_meta_box', // Callback function
            get_post_type() // Post type
        );
    }
}

// Callback function to render the meta box content
function my_plugin_render_meta_box($post) {
    // Retrieve saved tag reference value for the current post
    $tag_reference = get_post_meta($post->ID, 'my_plugin_tag_reference', true);
    ?>

    <label for="my_plugin_tag_reference">Tag Reference:</label>
    <input type="text" id="my_plugin_tag_reference" name="my_plugin_tag_reference" value="<?php echo esc_attr($tag_reference); ?>">
    <?php
}

// Save/update post meta when the post is saved
add_action('save_post', 'my_plugin_save_post_meta');

function my_plugin_save_post_meta($post_id) {
    // Check if the request is an autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check if the user has proper permissions
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Check if the tag reference value is submitted
    if (isset($_POST['my_plugin_tag_reference'])) {
        // Sanitize the tag reference value
        $tag_reference = sanitize_text_field($_POST['my_plugin_tag_reference']);

        // Save/update the tag reference in post meta
        if (!empty($tag_reference)) {
            update_post_meta($post_id, 'my_plugin_tag_reference', $tag_reference);
         } else {
             delete_post_meta($post_id, 'my_plugin_tag_reference');
         }
     }
 }


