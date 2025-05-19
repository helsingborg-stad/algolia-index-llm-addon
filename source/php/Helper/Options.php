<?php

namespace AlgoliaIndexLLMAddon\Helper;

class Options
{
    public function isDisabled(): bool
    {
        return (bool) defined('ALGOLIA_INDEX_LLM_ADDON_DISABLE') 
            ? ALGOLIA_INDEX_LLM_ADDON_DISABLE
            : 0;
    }

    public function algoliaIndex(): string
    {
        return defined('ALGOLIAINDEX_INDEX_NAME') && !empty(ALGOLIAINDEX_INDEX_NAME) 
            ? ALGOLIAINDEX_INDEX_NAME
            : \AlgoliaIndex\Helper\Options::indexName();
    }

    public function algoliaAppId(): string
    {
        return defined('ALGOLIAINDEX_APPLICATION_ID') && !empty(ALGOLIAINDEX_APPLICATION_ID) 
            ? ALGOLIAINDEX_APPLICATION_ID
            : '';
    }

    public function algoliaSearchApiKey(): string
    {
        return defined('ALGOLIAINDEX_PUBLIC_API_KEY') && !empty(ALGOLIAINDEX_PUBLIC_API_KEY) 
            ? ALGOLIAINDEX_PUBLIC_API_KEY
            : '';
    }

    public function typesenseSearchApiKey(): string
    {
        return defined('TYPESENSEINDEX_API_KEY') && !empty(TYPESENSEINDEX_API_KEY) 
            ? TYPESENSEINDEX_API_KEY
            : '';
    }

    public function typesenseUrl(): string
    {
        return defined('TYPESENSEINDEX_APPLICATION_ID') && !empty(TYPESENSEINDEX_APPLICATION_ID) 
            ? TYPESENSEINDEX_APPLICATION_ID
            : '';
    }

    public function typesenseCollection(): string
    {
        return defined('TYPESENSEINDEX_INDEX_NAME') && !empty(TYPESENSEINDEX_INDEX_NAME) 
            ? TYPESENSEINDEX_INDEX_NAME
            : AlgoliaIndex\Helper\Options::indexName();
    }

    public function enableMatomo(): bool
    {
        return defined('ALGOLIA_INDEX_LLM_ADDON_ENABLE_MATOMO')
            ? ALGOLIA_INDEX_LLM_ADDON_ENABLE_MATOMO
            : false;
    }

    public function dataLoader(): string
    {
        return apply_filters('AlgoliaIndex/Provider', 'algolia');
    }

    public function useFakeStreamProvider(): bool
    {
        return defined('ALGOLIA_INDEX_LLM_ADDON_FAKE_STREAM_PROVIDER')
            ? ALGOLIA_INDEX_LLM_ADDON_FAKE_STREAM_PROVIDER
            : false;
    }

    public function openAiApiKey(): string
    {
        return defined('ALGOLIA_INDEX_LLM_ADDON_OPEN_AI_API_KEY') && !empty(ALGOLIA_INDEX_LLM_ADDON_OPEN_AI_API_KEY) 
            ? ALGOLIA_INDEX_LLM_ADDON_OPEN_AI_API_KEY
            : '';
    }


    public function algoliaIndexIsEnabled(): bool
    {
        return is_plugin_active("algolia-index/algolia-index.php")
            && class_exists("\AlgoliaIndex\App");
    }

    public function algoliaIndexJsAddonIsEnabled(): bool
    {
        return is_plugin_active("algolia-index-js-searchpage-addon/algolia-index-js-searchpage.php");
    }
}