<?php
/**
 * Plugin Name: WP Agent AI
 * Description: AI-powered content generator for Gutenberg
 * Version: 1.0.0
 * Author: Tejas
 * Text Domain: wp-agent-ai
 */

defined('ABSPATH') || exit;

define('WP_AGENT_AI_VERSION', '1.0.0');
define('WP_AGENT_AI_PATH', plugin_dir_path(__FILE__));
define('WP_AGENT_AI_URL', plugin_dir_url(__FILE__));

// Load Settings class
require_once WP_AGENT_AI_PATH . 'includes/class-settings.php';

// Initialize settings only in admin
if ( is_admin() ) {
    new WP_Agent_AI_Settings();
}

// Register block
add_action('init', function() {
    register_block_type(WP_AGENT_AI_PATH . 'build/block');
});