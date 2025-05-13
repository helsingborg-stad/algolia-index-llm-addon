<?php

namespace AlgoliaIndexLLMAddon\Interfaces;

use AlgoliaIndexLLMAddon\Interfaces\Prompt;

interface LLMStream
{
    public function stream( Prompt $prompt): void;
}
