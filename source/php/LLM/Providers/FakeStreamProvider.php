<?php
namespace AlgoliaIndexLLMAddon\LLM\Providers;


use AlgoliaIndexLLMAddon\Interfaces\LLMStream;
use AlgoliaIndexLLMAddon\Interfaces\Prompt;

class FakeStreamProvider implements LLMStream
{
    private int $chunkSize;

    public function __construct( int $chunkSize = 10 )
    {
        $this->chunkSize = $chunkSize;
    }

    public function stream( Prompt $prompt, callable $onChunk ): void
    {

        $messages = \json_encode( $prompt->toMessages() );
        $text      = "<p>Fake answer for “{$messages}”}</p>";
        $remaining = $text;

        while ( mb_strlen( $remaining, 'UTF-8' ) > 0 ) {
            $part      = mb_substr( $remaining, 0, $this->chunkSize, 'UTF-8' );
            $remaining = mb_substr( $remaining, $this->chunkSize, null, 'UTF-8' );

            $onChunk( $part );
            usleep( 1_000 );
        }
    }
}

