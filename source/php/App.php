<?php

namespace AlgoliaIndexLLMAddon;

class App
{
    public function __construct()
    {
        add_action('admin_enqueue_scripts', array($this, 'enqueueStyles'));
        add_action('admin_enqueue_scripts', array($this, 'enqueueScripts'));

        $this->cacheBust = new \AlgoliaIndexLLMAddon\Helper\CacheBust();
    }

    /**
     * Enqueue required style
     * @return void
     */
    public function enqueueStyles()
    {
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
        wp_register_script(
            'algolia-index-llm-addon-js',
            ALGOLIA_INDEX_LLM_ADDON_URL . '/dist/' .
            $this->cacheBust->name('js/algolia-index-llm-addon.js')
        );

        wp_enqueue_script('algolia-index-llm-addon-js');
    }
}
