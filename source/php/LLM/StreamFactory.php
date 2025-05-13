<?php

namespace AlgoliaIndexLLMAddon\LLM;

use AlgoliaIndexLLMAddon\Interfaces\LLMStream;
use AlgoliaIndexLLMAddon\LLM\Providers\FakeStreamProvider;
use AlgoliaIndexLLMAddon\LLM\Providers\OpenAIStreamProvider;

class StreamFactory
{
    public static function createFromEnv(): LLMStream
    {
        $useFake = 1;
        return $useFake
            ? new FakeStreamProvider(10)
            : new OpenAIStreamProvider(OPENAI_API_KEY );
    }
}