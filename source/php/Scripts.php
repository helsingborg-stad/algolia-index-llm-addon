<?php

namespace AlgoliaIndexLLMAddon;

use AlgoliaIndexLLMAddon\Helper\CacheBust;
use AlgoliaIndexLLMAddon\Helper\Options;

class Scripts
{
    private CacheBust $cacheBust;

    private Options $options;
    
    public function __construct(CacheBust $cacheBust, ?Options $options = null)
    {
        $this->cacheBust = $cacheBust;
        $this->options = $options ?: new Options();
        
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
        if (!is_search() || \is_paged() || $this->options->isDisabled()) {
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
            'isDisabled' => $this->options->isDisabled(),
            'algoliaIndex' => $this->options->algoliaIndex(),
            'algoliaAppId' => $this->options->algoliaAppId(),
            'algoliaSearchApiKey' =>  $this->options->algoliaSearchApiKey(),
            'typesenseSearchApiKey' => $this->options->typesenseSearchApiKey(),
            'typesenseUrl' => $this->options->typesenseUrl(),
            'typesenseCollection' =>  $this->options->typesenseCollection(),
            'enableMatomo' =>  $this->options->enableMatomo(),
            'dataLoader' =>  $this->options->dataLoader(),
            'algoliaIndexJsAddonIsEnabled' => $this->options->algoliaIndexJsAddonIsEnabled(),
        ]);

        wp_enqueue_script('algolia-index-llm-addon-js');
    }
}