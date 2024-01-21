<?php
/*
Plugin Name: 0_D.M.T_0 Custom Form Plugin
Description: One Stop Google sheets light weight form.
Version: 1.1
Author: John Mogi
Author Email: john@dmt-ltd.com
*/

// Initialize the plugin
function custom_form_plugin_init()
{
    // Register admin menu for managing forms
    add_action('admin_menu', 'custom_form_plugin_menu');

    // Register shortcode for displaying forms
    add_shortcode('custom_form', 'custom_form_shortcode');
}
add_action('init', 'custom_form_plugin_init');

// Create admin menu for form management
function custom_form_plugin_menu()
{
    add_menu_page(
        'DMT Form Management', // Page title
        'DMT Form Management', // Menu title
        'manage_options',      // Capability
        'custom-form-management', // Menu slug
        'custom_form_management_page', // Function to display the page content
        'dashicons-format-status' // Dashicon icon class
    );
}

function custom_form_enqueue_assets()
{
    // Enqueue JavaScript
    wp_enqueue_script(
        'custom-form-handler',
        plugin_dir_url(__FILE__) . 'js/form_handle.js',
        array('jquery'),
        '1.0.0',
        true
    );
    // Localize script with the AJAX URL
    wp_localize_script('custom-form-handler', 'ajax_object', array(
        'ajax_url' => admin_url('admin-ajax.php')
    ));
    // Enqueue CSS
    wp_enqueue_style(
        'custom-form-styles',
        plugin_dir_url(__FILE__) . 'css/form_styles.css',
        array(),
        '1.0.0'
    );
}
add_action('wp_enqueue_scripts', 'custom_form_enqueue_assets');

// Admin page for managing forms
function custom_form_management_page()
{
    echo '<div class="wrap">';
    echo '<h1>DMT Form Management</h1>';
    echo '<p>One place to rule them all: Set your global submission handler URL below.</p>';

    if (isset($_POST['submission_url'])) {
        update_option('custom_form_submission_url', sanitize_text_field($_POST['submission_url']));
    }

    $submissionUrl = get_option('custom_form_submission_url', '');

    // Form for updating the Submission Handler URL
    echo '<form method="post">';
    echo '<label for="submission_url">Submission Handler URL:</label><br>';
    echo '<input type="text" id="submission_url" name="submission_url" value="' . esc_attr($submissionUrl) . '"><br>';
    echo '<input type="submit" value="Save URL">';
    echo '</form>';

    echo '<h2>Available Forms</h2>';
    $form_templates_dir = plugin_dir_path(__FILE__) . 'forms/'; // Adjusted path
    if (is_dir($form_templates_dir)) {
        foreach (new DirectoryIterator($form_templates_dir) as $file) {
            if ($file->isFile() && $file->getExtension() == 'php') {
                $form_id = $file->getBasename('.php');
                echo '<p><strong>' . esc_html($form_id) . ':</strong> [custom_form id="' . esc_attr($form_id) . '"]</p>';
            }
        }
    } else {
        echo '<p>No form templates found.</p>';
    }


    echo '</div>';
}

// Shortcode function for displaying forms
function custom_form_shortcode($atts)
{
    $form_id = isset($atts['id']) ? sanitize_text_field($atts['id']) : 'default';

    ob_start();
    $submissionUrl = get_option('custom_form_submission_url', '');
    $template_path = plugin_dir_path(__FILE__) . 'forms/' . $form_id . '.php'; // Removed the extra '-form'
    if (file_exists($template_path)) {
        include($template_path);
    } else {
        echo "<p>Form template not found.</p>";
    }
    return ob_get_clean();
}


function handle_custom_form_ajax_submission() {
    $email = "john@dmt-ltd.com";
    $site_name = get_bloginfo('name');
    $subject = "ליד חדש מאתר " . $site_name;
    $body = "";

  $field_mappings = array(
        'form-identifier' => 'מזהה טופס',
        'source' => 'נשלח מ',
        'customer-name' => 'שם',
        'phone-number' => 'טלפון',
        'אני מאשר/ת קבלת חומר שיווקי' => 'אני מאשר/ת קבלת חומר שיווקי'
    );

    foreach ($_POST as $key => $value) {
        // Skip fields you don't want to include
        if (in_array($key, ['mobile-phone', 'date'])) {
            continue;
        }

        // Translate field names and handle checkbox for marketing consent
        $translated_key = isset($field_mappings[$key]) ? $field_mappings[$key] : $key;
        $translated_value = ($key === 'אני מאשר/ת קבלת חומר שיווקי' && $value === 'on') ? 'כן' : $value;

        $body .= sanitize_text_field($translated_key) . ': ' . sanitize_text_field($translated_value) . "\n";
    }


    wp_mail($email, $subject, $body);
    echo 'Email sent successfully';
    wp_die();
}


// Hook for logged-in users
add_action('wp_ajax_submit_custom_form', 'handle_custom_form_ajax_submission');

// Hook for non-logged-in users
add_action('wp_ajax_nopriv_submit_custom_form', 'handle_custom_form_ajax_submission');



/**
 * Processes form submissions collect into json
 * custom newletter form
 */
function handle_json_form_submission() {
    // Check if the form has been submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Handle form submission
        $data = array(
            'email' => filter_input(INPUT_POST, 'customer-email', FILTER_SANITIZE_EMAIL),
            'source' => filter_input(INPUT_POST, 'source', FILTER_SANITIZE_STRING),
            'submitted_at' => date('Y-m-d H:i:s')
        );

        $filename = 'submissions.json'; // Specify your file path here
        $existing_data = file_exists($filename) ? json_decode(file_get_contents($filename), true) : [];
        $existing_data[] = $data;
        file_put_contents($filename, json_encode($existing_data, JSON_PRETTY_PRINT));

        // return "<p>Submission saved.</p>";
        echo "<p>Submission saved.</p>";
        wp_die(); // Terminate AJAX request
    } else {
        // Form has not been submitted, display the form
        ob_start();
        // Adjust the path as needed to point to your form template
        include(plugin_dir_path(__FILE__) . 'forms/newsletter-form.php');
        return ob_get_clean();
    }
}

function register_json_form_submission_shortcode() {
    add_shortcode('json_form_submission', 'handle_json_form_submission');
}
add_action('init', 'register_json_form_submission_shortcode');


// END custom newletter form
