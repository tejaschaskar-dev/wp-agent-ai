<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WPAgentAI_Settings {
    public function __construct() {
        add_action( 'admin_menu', [ $this, 'add_settings_page' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );
    }

    public function add_settings_page() {
        add_options_page(
            'WP Agent AI Settings',
            'WP Agent AI',
            'manage_options',
            'wp-agent-ai',
            [ $this, 'render_settings_page' ]
        );
    }

    public function register_settings() {
        register_setting( 'wp_agent_ai_group', 'wp_agent_ai_api_key' );
        add_settings_section( 'wp_agent_ai_main', 'API Configuration', null, 'wp-agent-ai' );
        add_settings_field(
            'wp_agent_ai_api_key',
            'OpenRouter API Key',
            [ $this, 'api_key_callback' ],
            'wp-agent-ai',
            'wp_agent_ai_main'
        );
    }

    public function api_key_callback() {
        $value = get_option( 'wp_agent_ai_api_key', '' );
        echo '<input type="password" name="wp_agent_ai_api_key" value="' . esc_attr( $value ) . '" class="regular-text">';
        echo '<p class="description">Enter your OpenRouter API key here.</p>';
    }

    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1>WP Agent AI Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields( 'wp_agent_ai_group' );
                do_settings_sections( 'wp-agent-ai' );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
}
