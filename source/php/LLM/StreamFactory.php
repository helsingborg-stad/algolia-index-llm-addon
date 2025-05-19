<?php

namespace AlgoliaIndexLLMAddon\LLM;

use AlgoliaIndexLLMAddon\Helper\Options;
use AlgoliaIndexLLMAddon\Interfaces\LLMStream;
use AlgoliaIndexLLMAddon\LLM\Providers\FakeStreamProvider;
use AlgoliaIndexLLMAddon\LLM\Providers\OpenAIStreamProvider;

class StreamFactory
{
    public static function createFromEnv(Options $options = new Options()): LLMStream
    {
        return $options->useFakeStreamProvider()
            ? new FakeStreamProvider(10)
            : new OpenAIStreamProvider($options->openAiApiKey() );
    }
}