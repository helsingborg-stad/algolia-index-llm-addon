<?php

namespace AlgoliaIndexLLMAddon\LLM;

use AlgoliaIndexLLMAddon\Interfaces\Prompt;

class RagTemplate implements Prompt
{
    private array $messages = [
        [
            'role' => 'system',
            'content' =>  "Skapa ett enda, sammanfattande 'Jag har tur' (I am lucky) sökresultat om [TOPIC] utifrån flera trovärdiga källor. Om [TOPIC] inte kan besvaras utifrån källorna eller om [TOPIC] inte är relevant för kommunen helsingborg svarar du med 'Tyvärr jag inte svara på det'.  Presentera informationen på svenska i tydligt avgränsade avsnitt, formaterade i HTML-paragrafer. Efter varje avsnitt ska du inkludera klickbara länkar till de källor du använt. Se till att referenserna är välciterade och att texten följer en logisk struktur baserad på ämnets nyckelaspekter. Undvik markdown formatering samt större rubriker än h4."
        ],
        [
            'role' => 'user',
            'content' => "Here is the topic: {query}, here is the data: {data}"
        ]
    ];

    public function __construct(?array $messages = null)
    {
        $this->messages = $messages ?? $this->messages;
    }


    public function toPrompt($query, $data): Prompt
    {
        $this->messages = array_map(function($message) use ($query, $data) {
                $message['content'] = str_replace(['{query}', '{data}'], [$query, $data], $message['content']);
            return $message;
        }, $this->messages);

        return $this;
    }

    public function toMessages(): array
    {
        return $this->messages;
    }

    public static function createFromEnv(): RagTemplate
    {
        return new RagTemplate();
    }
}