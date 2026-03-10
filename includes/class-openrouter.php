<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WPAgentAI_OpenRouter {

    private $api_key;
    private $model;
    private $base_url = 'https://openrouter.ai/api/v1';

    public function __construct() {
        $this->api_key = get_option( 'wp_agent_ai_api_key', '' );
        $this->model   = get_option( 'wp_agent_ai_model', 'openai/gpt-4o-mini' );
    }

    public function generate( $prompt, $content_type, $tone, $length ) {
        if ( empty( $this->api_key ) ) {
            return new WP_Error( 'no_api_key', 'API key is not configured. Please go to Settings -> WP Agent AI.' );
        }

        $system_prompt = $this->build_system_prompt( $content_type, $tone, $length );

        $response = wp_remote_post( $this->base_url . '/chat/completions', [
            'timeout' => 60,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type'  => 'application/json',
            ],
            'body' => json_encode([
                'model'    => $this->model,
                'messages' => [
                    [ 'role' => 'system', 'content' => $system_prompt ],
                    [ 'role' => 'user',   'content' => $prompt ],
                ],
            ]),
        ]);

        if ( is_wp_error( $response ) ) {
            return new WP_Error( 'request_failed', $response->get_error_message() );
        }

        $code = wp_remote_retrieve_response_code( $response );
        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( $code === 429 ) { return new WP_Error( 'rate_limit', 'Rate limit reached. Please wait and try again.' ); }
        if ( $code === 401 ) { return new WP_Error( 'invalid_key', 'Invalid API key. Please check your settings.' ); }
        if ( $code !== 200 ) {
            $msg = $body['error']['message'] ?? 'Unexpected error from AI service.';
            return new WP_Error( 'api_error', $msg );
        }

        $content = $body['choices'][0]['message']['content'] ?? '';
        if ( empty( $content ) ) {
            return new WP_Error( 'empty_response', 'No content was generated. Please try rephrasing your prompt.' );
        }

        return $content;
    }

    private function build_system_prompt( $content_type, $tone, $length ) {
        $length_map = [ 'short' => '1-2 paragraphs', 'medium' => '3-4 paragraphs', 'long' => '5+ paragraphs' ];
        $length_desc = $length_map[ $length ] ?? '3-4 paragraphs';
        return "You are a professional WordPress content writer.
Generate high-quality content for a WordPress page.
Tone: {$tone}
Content type: {$content_type}
Length: {$length_desc}
Use clear headings where appropriate.
Output plain text only - no markdown, no HTML tags.
Write directly without any introduction.";
    }
}
