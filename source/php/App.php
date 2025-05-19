<?php
namespace AlgoliaIndexLLMAddon;


use AlgoliaIndexLLMAddon\Helper\Options;
use AlgoliaIndexLLMAddon\LLM\RagTemplate;
use AlgoliaIndexLLMAddon\LLM\StreamFactory;

class App
{
    private Options $options;

    public function __construct()
    {
        $this->options = new Options();
        
        if (!$this->isConfigured()) {
            add_action("admin_notices", [$this, "showAdminNotice"]);
            return;
        }

        new API( StreamFactory::createFromEnv(), RagTemplate::createFromEnv());
        new Scripts(  new \AlgoliaIndexLLMAddon\Helper\CacheBust(), $this->options );
    }

    public function notices()
    {
        $conditions = [
            [!$this->options->algoliaIndexIsEnabled(), __("AlgoliaIndex plugin is not activated.", "algolia-index-llm-addon")],
            [empty($this->options->openAiApiKey()) && !$this->options->useFakeStreamProvider(), __("ALGOLIA_INDEX_LLM_ADDON_OPEN_AI_API_KEY is not defined.", "algolia-index-llm-addon")],
        ];
        
        return array_filter(array_map(function($item) {
            [$condition, $message] = $item;
            return $condition ? $message : null;
        }, $conditions));
    }

    public function isConfigured()
    {
        return empty($this->notices());
    }

    public function showAdminNotice()
    {
        echo "<div class='notice notice-error'><p>";
        echo _e("Algolia Index LLM Add-on (Plugin) - The following issues need to be resolved:", "algolia-index-llm-addon") . "<br>";
        foreach ($this->notices() as $notice) {
            echo esc_html($notice) . "<br>";
        }
        echo "</p></div>";
    }
}
