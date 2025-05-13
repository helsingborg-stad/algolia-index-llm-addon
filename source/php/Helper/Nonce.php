<?php

namespace AlgoliaIndexLLMAddon\Helper;

class Nonce
{
    public static function verifyRestNonce( \WP_REST_Request $request ) {
        $nonce = $request->get_header( 'X-WP-Nonce' );
        if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
            return new \WP_Error(
                'rest_forbidden',
                __( 'Sorry, you are not allowed to do that.' ),
                [ 'status' => 403 ]
            );
        }

        return true;
    }
}