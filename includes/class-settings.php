<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WPAgentAI_Settings {

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'add_settings_page' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );
        add_action( 'wp_ajax_wp_agent_ai_test_connection', [ $this, 'test_connection' ] );
    }

    public function add_settings_page() {
        add_options_page( 'WP Agent AI Settings', 'WP Agent AI', 'manage_options', 'wp-agent-ai', [ $this, 'render_settings_page' ] );
    }

    public function register_settings() {
        register_setting( 'wp_agent_ai_group', 'wp_agent_ai_api_key' );
        register_setting( 'wp_agent_ai_group', 'wp_agent_ai_model' );
        register_setting( 'wp_agent_ai_group', 'wp_agent_ai_default_tone' );
        register_setting( 'wp_agent_ai_group', 'wp_agent_ai_default_length' );
        add_settings_section( 'wp_agent_ai_main', 'AI Configuration', null, 'wp-agent-ai' );
        add_settings_field( 'wp_agent_ai_api_key', 'OpenRouter API Key', [ $this, 'api_key_callback' ], 'wp-agent-ai', 'wp_agent_ai_main' );
        add_settings_field( 'wp_agent_ai_model', 'AI Model', [ $this, 'model_callback' ], 'wp-agent-ai', 'wp_agent_ai_main' );
        add_settings_field( 'wp_agent_ai_default_tone', 'Default Tone', [ $this, 'tone_callback' ], 'wp-agent-ai', 'wp_agent_ai_main' );
        add_settings_field( 'wp_agent_ai_default_length', 'Default Length', [ $this, 'length_callback' ], 'wp-agent-ai', 'wp_agent_ai_main' );
    }

    public function api_key_callback() {
        $value = get_option( 'wp_agent_ai_api_key', '' );
        echo '<input type="password" name="wp_agent_ai_api_key" value="' . esc_attr( $value ) . '" class="regular-text">';
    }

    public function model_callback() {
        $value = get_option( 'wp_agent_ai_model', 'openai/gpt-4o-mini' );
        $models = [ 'openai/gpt-4o' => 'GPT-4o', 'openai/gpt-4o-mini' => 'GPT-4o Mini', 'anthropic/claude-sonnet-4-5' => 'Claude Sonnet', 'google/gemini-2.0-flash-001' => 'Gemini 2.0 Flash', 'deepseek/deepseek-chat-v3-0324' => 'DeepSeek Chat v3' ];
        echo '<select name="wp_agent_ai_model">';
        foreach ( $models as $id => $label ) {
            echo '<option value="' . esc_attr( $id ) . '" ' . selected( $value, $id, false ) . '>' . esc_html( $label ) . '</option>';
        }
        echo '</select>';
    }

    public function tone_callback() {
        $value = get_option( 'wp_agent_ai_default_tone', 'professional' );
        $tones = [ 'professional' => 'Professional', 'casual' => 'Casual', 'creative' => 'Creative', 'technical' => 'Technical' ];
        echo '<select name="wp_agent_ai_default_tone">';
        foreach ( $tones as $id => $label ) {
            echo '<option value="' . esc_attr( $id ) . '" ' . selected( $value, $id, false ) . '>' . esc_html( $label ) . '</option>';
        }
        echo '</select>';
    }

    public function length_callback() {
        $value = get_option( 'wp_agent_ai_default_length', 'medium' );
        $lengths = [ 'short' => 'Short', 'medium' => 'Medium', 'long' => 'Long' ];
        echo '<select name="wp_agent_ai_default_length">';
        foreach ( $lengths as $id => $label ) {
            echo '<option value="' . esc_attr( $id ) . '" ' . selected( $value, $id, false ) . '>' . esc_html( $label ) . '</option>';
        }
        echo '</select>';
    }

    public function test_connection() {
        check_ajax_referer( 'wp_agent_ai_test', 'nonce' );
        $api_key = get_option( 'wp_agent_ai_api_key', '' );
        $model   = get_option( 'wp_agent_ai_model', 'openai/gpt-4o-mini' );
        if ( empty( $api_key ) ) { wp_send_json_error( [ 'message' => 'API key is not configured.' ] ); }
        $response = wp_remote_post( 'https://openrouter.ai/api/v1/chat/completions', [
            'timeout' => 30,
            'headers' => [ 'Authorization' => 'Bearer ' . $api_key, 'Content-Type' => 'application/json' ],
            'body'    => json_encode([ 'model' => $model, 'messages' => [ [ 'role' => 'user', 'content' => 'Say OK' ] ] ]),
        ]);
        if ( is_wp_error( $response ) ) { wp_send_json_error( [ 'message' => $response->get_error_message() ] ); }
        $code = wp_remote_retrieve_response_code( $response );
        if ( $code === 200 ) { wp_send_json_success( [ 'message' => 'Connection successful!' ] ); }
        else { wp_send_json_error( [ 'message' => 'Error code: ' . $code ] ); }
    }

    public function render_settings_page() { ?>
        <div class="wrap">
            <h1>WP Agent AI Settings</h1>
            <form method="post" action="options.php">
                <?php settings_fields( 'wp_agent_ai_group' ); do_settings_sections( 'wp-agent-ai' ); submit_button(); ?>
            </form>
            <hr><h2>Test Connection</h2>
            <button id="wp-agent-ai-test-btn" class="button button-secondary">Test Connection</button>
            <span id="wp-agent-ai-test-result" style="margin-left:15px;font-weight:bold;"></span>
            <script>
                document.getElementById('wp-agent-ai-test-btn').addEventListener('click', function() {
                    var result = document.getElementById('wp-agent-ai-test-result');
                    result.style.color = 'gray'; result.textContent = 'Testing...';
                    fetch(ajaxurl, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'action=wp_agent_ai_test_connection&nonce=<?php echo wp_create_nonce("wp_agent_ai_test"); ?>' })
                    .then(r => r.json()).then(data => {
                        if (data.success) { result.style.color = 'green'; result.textContent = '? ' + data.data.message; }
                        else { result.style.color = 'red'; result.textContent = '? ' + data.data.message; }
                    });
                });
            </script>
        </div>
    <?php }
}
