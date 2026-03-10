<?php
/**
 * Plugin Name: WP Agent AI
 * Description: AI-powered content generator for Gutenberg
 * Version: 1.0.0
 * Author: Tejas
 * Text Domain: wp-agent-ai
 */

defined( 'ABSPATH' ) || exit;

define( 'WP_AGENT_AI_VERSION', '1.0.0' );
define( 'WP_AGENT_AI_PATH', plugin_dir_path( __FILE__ ) );
define( 'WP_AGENT_AI_URL', plugin_dir_url( __FILE__ ) );

require_once WP_AGENT_AI_PATH . 'includes/class-openrouter.php';
require_once WP_AGENT_AI_PATH . 'includes/class-rest-api.php';
require_once WP_AGENT_AI_PATH . 'includes/class-settings.php';

new WPAgentAI_REST_API();

if ( is_admin() ) {
    new WPAgentAI_Settings();
}

add_action( 'init', function() {
    register_block_type( WP_AGENT_AI_PATH . 'build/block' );
} );
