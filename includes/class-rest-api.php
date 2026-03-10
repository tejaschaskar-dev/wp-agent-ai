<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WPAgentAI_REST_API {

    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
    }

    public function register_routes() {
        register_rest_route( 'wp-agent-ai/v1', '/generate', [
            'methods'             => 'POST',
            'callback'            => [ $this, 'handle_generate' ],
            'permission_callback' => [ $this, 'check_permission' ],
            'args'                => [
                'prompt' => [
                    'required'          => true,
                    'sanitize_callback' => 'sanitize_textarea_field',
                ],
                'content_type' => [
                    'required'          => true,
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'tone' => [
                    'required'          => true,
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'length' => [
                    'required'          => true,
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]);
    }

    public function check_permission() {
        return is_user_logged_in() && current_user_can( 'edit_posts' );
    }

    public function handle_generate( WP_REST_Request $request ) {
        $prompt       = $request->get_param( 'prompt' );
        $content_type = $request->get_param( 'content_type' );
        $tone         = $request->get_param( 'tone' );
        $length       = $request->get_param( 'length' );

        $api_key = get_option( 'wp_agent_ai_api_key', '' );
        if ( empty( $api_key ) ) {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'API key is not configured.',
            ], 503 );
        }

        $openrouter = new WPAgentAI_OpenRouter();
        $result     = $openrouter->generate( $prompt, $content_type, $tone, $length );

        if ( is_wp_error( $result ) ) {
            return new WP_REST_Response([
                'success' => false,
                'message' => $result->get_error_message(),
            ], 500 );
        }

        return new WP_REST_Response([
            'success' => true,
            'content' => $result,
        ], 200 );
    }
}
