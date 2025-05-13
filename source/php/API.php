<?php

namespace AlgoliaIndexLLMAddon;

use AlgoliaIndexLLMAddon\Interfaces\LLMStream;
use AlgoliaIndexLLMAddon\LLM\RagTemplate;

class API
{
    private LLMStream $llm;
    private RagTemplate $template;

    public function __construct(LLMStream $llm, RagTemplate $template)
    {
        $this->llm = $llm;
        $this->template = $template;

        add_action('rest_api_init', [$this, 'registerRoutes']);
    }

    public function registerRoutes()
    {
        register_rest_route(
            'llm/v1',
            '/stream',
            [
                'methods'             => 'POST',
                'callback'            => [$this, 'handler'],
                'permission_callback' => [\AlgoliaIndexLLMAddon\Helper\Nonce::class, 'verifyRestNonce'],
                'args'                => [
                    'query' => [
                        'required'          => true,
                        'validate_callback' => fn($p) => is_string($p) && !empty($p),
                    ],
                ],
            ]
        );
    }

    public function handler( \WP_REST_Request $request )
    {
        $query = sanitize_text_field( $request->get_param( 'query' ) );
        $data = sanitize_text_field( $request->get_param( 'data' ) );

        if ( '' === $query ) {
            return new \WP_Error( 'no_query', 'Please provide a query parameter.', [ 'status' => 400 ] );
        }
    
        // disable PHP buffers
        while ( ob_get_level() ) { ob_end_clean(); }
        ob_implicit_flush( true );
        // no proxy buffering
        header( 'X-Accel-Buffering: no' );
        // plain‐text chunks
        header( 'Content-Type: text/plain; charset=utf-8' );
        header( 'Cache-Control: no-cache' );
    
        ignore_user_abort( true );
        set_time_limit( 0 );
    
        $this->llm->stream( 
            $this->template->toPrompt( 
                $query, $data
            )
        );
    
        exit;
    }
}