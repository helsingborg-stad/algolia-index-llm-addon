<?php

namespace AlgoliaIndexLLMAddon;

use AlgoliaIndexLLMAddon\Helper\CacheBust;

class Scripts
{
    private CacheBust $cacheBust;
    
    public function __construct(CacheBust $cacheBust)
    {
        $this->cacheBust = $cacheBust;
        
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueueScripts' ] );
    }


    /**
     * Enqueue required style
     * @return void
     */
    public function enqueueStyles()
    {
        if (!is_search()) {
            return;
        }

        wp_register_style(
            'algolia-index-llm-addon-css',
            ALGOLIA_INDEX_LLM_ADDON_URL . '/dist/' .
            $this->cacheBust->name('css/algolia-index-llm-addon.css')
        );

        wp_enqueue_style('algolia-index-llm-addon-css');
    }

    /**
     * Enqueue required scripts
     * @return void
     */
    public function enqueueScripts()
    {
        if (!is_search()) {
            return;
        }

        wp_register_script(
            'algolia-index-llm-addon-js',
            ALGOLIA_INDEX_LLM_ADDON_URL . '/dist/' .
            $this->cacheBust->name('js/algolia-index-llm-addon.js')
        );

        wp_localize_script( 'algolia-index-llm-addon-js', 'LLMSettings', [
            'apiUrl' => esc_url_raw( rest_url( 'llm/v1/stream' ) ),
            'nonce'  => wp_create_nonce( 'wp_rest' ),
        ] );

        wp_enqueue_script('algolia-index-llm-addon-js');
    }
}