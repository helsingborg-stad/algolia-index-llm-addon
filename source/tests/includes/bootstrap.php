<?php

// Get around direct access blockers.
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/../../../');
}

define('ALGOLIA_INDEX_LLM_ADDON_PATH', __DIR__ . '/../../../');
define('ALGOLIA_INDEX_LLM_ADDON_URL', 'https://example.com/wp-content/plugins/' . 'modularity-algolia-index-llm-addon');
define('ALGOLIA_INDEX_LLM_ADDON_TEMPLATE_PATH', ALGOLIA_INDEX_LLM_ADDON_PATH . 'templates/');


// Register the autoloader
$loader = require __DIR__ . '/../../../vendor/autoload.php';
$loader->addPsr4('AlgoliaIndexLLMAddon\\Test\\', __DIR__ . '/../php/');

require_once __DIR__ . '/PluginTestCase.php';
