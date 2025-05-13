<?php

namespace AlgoliaIndexLLMAddon\Interfaces;

interface Prompt
{
    public function toMessages(): array;
}
