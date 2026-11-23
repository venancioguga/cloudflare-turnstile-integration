<?php
/*
Plugin Name: Cloudflare Turnstile Integration
Description: Adds Cloudflare Turnstile CAPTCHA to WordPress login and comment forms.
Version: 1.2
Author: Guga Venancio
License: MIT
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Create the menu item for the settings page
function turnstile_settings_menu() {
    add_options_page(
        'Cloudflare Turnstile Settings',
        'Turnstile Settings',
        'manage_options',
        'turnstile-settings',
        'turnstile_settings_page'
    );
}
add_action('admin_menu', 'turnstile_settings_menu');

// Display the settings page
function turnstile_settings_page() {
    ?>
    <div class="wrap">
        <h1>Cloudflare Turnstile Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('turnstile_settings_group');
            do_settings_sections('turnstile-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Initialize the settings
function turnstile_settings_init() {
    register_setting('turnstile_settings_group', 'turnstile_site_key');
    register_setting('turnstile_settings_group', 'turnstile_secret_key');

    add_settings_section(
        'turnstile_settings_section',
        'Cloudflare Turnstile Configuration',
        null,
        'turnstile-settings'
    );

    add_settings_field(
        'turnstile_site_key',
        'Site Key',
        'turnstile_site_key_render',
        'turnstile-settings',
        'turnstile_settings_section'
    );

    add_settings_field(
        'turnstile_secret_key',
        'Secret Key',
        'turnstile_secret_key_render',
        'turnstile-settings',
        'turnstile_settings_section'
    );
}
add_action('admin_init', 'turnstile_settings_init');

function turnstile_site_key_render() {
    $site_key = get_option('turnstile_site_key');
    echo "<input type='text' name='turnstile_site_key' value='" . esc_attr($site_key) . "'>";
}

function turnstile_secret_key_render() {
    $secret_key = get_option('turnstile_secret_key');
    echo "<input type='password' autocomplete='off' name='turnstile_secret_key' value='" . esc_attr($secret_key) . "'>";
}

// Load Turnstile scripts
function load_turnstile_scripts() {
    wp_enqueue_script('cf-turnstile', 'https://challenges.cloudflare.com/turnstile/v0/api.js', [], null, true);
}
add_action('wp_enqueue_scripts', 'load_turnstile_scripts');
add_action('login_enqueue_scripts', 'load_turnstile_scripts');

// Add Turnstile to comment form
function add_turnstile_to_comment_form() {
    if (!turnstile_is_configured()) {
        return;
    }
    $site_key = get_option('turnstile_site_key');
    echo '<div class="cf-turnstile" data-sitekey="' . esc_attr($site_key) . '"></div>';
}
add_action('comment_form_after_fields', 'add_turnstile_to_comment_form');

// True when both keys are saved. Without keys the plugin stays inactive (prevents locking everyone out).
function turnstile_is_configured() {
    return get_option('turnstile_site_key') && get_option('turnstile_secret_key');
}

// Asks Cloudflare whether the token sent by the form is valid.
function turnstile_verify_token() {
    $response = isset($_POST['cf-turnstile-response']) ? sanitize_text_field(wp_unslash($_POST['cf-turnstile-response'])) : '';
    if ($response === '') {
        return false;
    }

    $verify = wp_remote_post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
        'timeout' => 10,
        'body'    => [
            'secret'   => get_option('turnstile_secret_key'),
            'response' => $response,
            'remoteip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '',
        ],
    ]);

    if (is_wp_error($verify)) {
        return false;
    }

    $result = json_decode(wp_remote_retrieve_body($verify));
    return is_object($result) && !empty($result->success);
}

// Validate Turnstile response for comments (visitors only: logged-in users never see the widget)
function validate_turnstile_comment($commentdata) {
    if (!turnstile_is_configured() || is_user_logged_in() || is_admin()) {
        return $commentdata;
    }
    // Pingbacks and trackbacks have no form
    if (!empty($commentdata['comment_type']) && in_array($commentdata['comment_type'], ['pingback', 'trackback'], true)) {
        return $commentdata;
    }

    if (!turnstile_verify_token()) {
        wp_die(esc_html__('CAPTCHA validation failed. Please go back and try again.'), '', ['response' => 403, 'back_link' => true]);
    }

    return $commentdata;
}
add_filter('preprocess_comment', 'validate_turnstile_comment');

// Add Turnstile to login form
function add_turnstile_to_login_form() {
    if (!turnstile_is_configured()) {
        return;
    }
    $site_key = get_option('turnstile_site_key');
    echo '<div class="cf-turnstile" data-sitekey="' . esc_attr($site_key) . '"></div>';
}
add_action('login_form', 'add_turnstile_to_login_form');

// Validate Turnstile response for login (only the wp-login.php form; XML-RPC, REST and app passwords are untouched)
function validate_turnstile_login($user, $password) {
    if (is_wp_error($user) || !turnstile_is_configured()) {
        return $user;
    }
    if ((defined('XMLRPC_REQUEST') && XMLRPC_REQUEST) || (defined('REST_REQUEST') && REST_REQUEST)) {
        return $user;
    }
    if (!isset($GLOBALS['pagenow']) || $GLOBALS['pagenow'] !== 'wp-login.php') {
        return $user;
    }

    if (!turnstile_verify_token()) {
        return new WP_Error('captcha_invalid', __('CAPTCHA validation failed. Please try again.'));
    }

    return $user;
}
add_filter('wp_authenticate_user', 'validate_turnstile_login', 10, 2);
