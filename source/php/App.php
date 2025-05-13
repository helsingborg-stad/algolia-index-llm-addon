<?php
namespace AlgoliaIndexLLMAddon;


use AlgoliaIndexLLMAddon\LLM\RagTemplate;
use AlgoliaIndexLLMAddon\LLM\StreamFactory;

class App
{
    public function __construct()
    {
        new API( StreamFactory::createFromEnv(), RagTemplate::createFromEnv());
        new Scripts(  new \AlgoliaIndexLLMAddon\Helper\CacheBust() );
    }
}
