<?php

/**
 * Plugin Name:       Algolia Index LLM Addon
 * Plugin URI:        https://github.com/helsingborg-stad/algolia-index-llm-addon
 * Description:       Algolia Index LLM Addon
 * Version:           1.0.0
 * Author:            Nikolas Ramstedt
 * Author URI:        https://github.com/helsingborg-stad
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 * Text Domain:       algolia-index-llm-addon
 * Domain Path:       /languages
 */

 // Protect agains direct file access
if (! defined('WPINC')) {
    die;
}

define('ALGOLIA_INDEX_LLM_ADDON_PATH', plugin_dir_path(__FILE__));
define('ALGOLIA_INDEX_LLM_ADDON_URL', plugins_url('', __FILE__));
define('ALGOLIA_INDEX_LLM_ADDON_TEMPLATE_PATH', ALGOLIA_INDEX_LLM_ADDON_PATH . 'templates/');
define('ALGOLIA_INDEX_LLM_ADDON_TEXT_DOMAIN', 'algolia-index-llm-addon');

load_plugin_textdomain(ALGOLIA_INDEX_LLM_ADDON_TEXT_DOMAIN, false, ALGOLIA_INDEX_LLM_ADDON_PATH . '/languages');

require_once ALGOLIA_INDEX_LLM_ADDON_PATH . 'Public.php';

// Register the autoloader
require __DIR__ . '/vendor/autoload.php';

// Acf auto import and export
add_action('acf/init', function () {
    $acfExportManager = new \AcfExportManager\AcfExportManager();
    $acfExportManager->setTextdomain('algolia-index-llm-addon');
    $acfExportManager->setExportFolder(ALGOLIA_INDEX_LLM_ADDON_PATH . 'source/php/AcfFields/');
    $acfExportManager->autoExport(array(
        'algolia-index-llm-addon-settings' => 'group_61ea7a87e8aaa' //Update with acf id here, settings view
    ));
    $acfExportManager->import();
});

// Start application
new AlgoliaIndexLLMAddon\App();
