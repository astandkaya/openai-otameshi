<?php

namespace OpenAI;

/**
 * Class OpenAI
 * @package OpenAI
 */
class OpenAI
{
    /**
     * API URL for OpenAI.
     *
     * @var string
     */
    protected string $baseUri = 'https://api.openai.com';

    /**
     * Endpoints of OpenAI API.
     *
     * @var array
     */
    protected array $endPoints = [
        'models' => '/v1/models/%1$s',
        'completion' => '/v1/completions',
        'chat' => '/v1/chat/completions',
        'edits' => '/v1/edits',
    ];

    /**
     * OpenAI constructor.
     *
     * @param string $apiKey
     * @param string $organization
     * @param string $completionModel
     * @param string $chatModel
     */
    public function __construct(
        protected string $apiKey,
        protected string $organization = '',
        protected string $completionModel = 'text-davinci-003',
        protected string $chatModel = 'gpt-3.5-turbo',
        protected string $editsModel = 'text-davinci-edit-001',
    ) {
    }



    /**
     * Generates the complete URL for an endpoint with given arguments.
     *
     * @param string $endPoint
     * @param string|null ...$arg
     * @return string
     */
    protected function genUrl(string $endPoint, ?string ...$arg): string
    {
        return rtrim($this->baseUri . sprintf($this->endPoints[$endPoint], ...$arg), "/");
    }

    /**
     * Generates the header to use in the HTTP request.
     *
     * @param array $added
     * @return array
     */
    protected function genHeader(array $added = []): array
    {
        return [
            ...[
                "Content-Type: application/json",
                "Authorization: Bearer {$this->apiKey}",
                "OpenAI-Organization: {$this->organization}",
            ],
            ...$added,
        ];
    }

    /**
     * Executes curk
     *
     * @param string $url
     * @param array $header
     * @param array|null $body
     * @return array
     */
    protected function curl(string $url, array $header, ?array $body = null): array
    {
        $ch = curl_init($url);
        $ch instanceof \CurlHandle ?: throw new \Exception("curl_init is Failed.");

        curl_setopt_array($ch, array_filter([
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $header,
            CURLOPT_POSTFIELDS => is_null($body) ? null : json_encode($body),
        ]));

        $response = curl_exec($ch);
        is_string($response) ?: throw new \Exception("response is not string.");

        curl_close($ch);

        return json_decode($response, true);
    }



    /**
     * Executes a request to the models endpoint.
     *
     * @param string|null $name
     * @return array
     */
    public function execModels(?string $name = null): array
    {
        return $this->curl(
            url: $this->genUrl('models', $name),
            header: $this->genHeader(),
        );
    }

    /**
     * Executes a request to the completion endpoint.
     *
     * @param string $prompt
     * @param string|null $model
     * @return array
     */
    public function execCompletion(string $prompt, ?string $model = null): array
    {
        return $this->curl(
            url: $this->genUrl('completion'),
            header: $this->genHeader(),
            body: [
                'model' => $model ?? $this->completionModel,
                'prompt' => $prompt,
                'max_tokens' => 1000,
                'temperature' => 0.7,
                'top_p' => 1,
                'n' => 1,
                'stream' => false,
                'logprobs' => null,
                'echo' => false,
                'stop' => "\n",
                'presence_penalty' => 0,
                'frequency_penalty' => 0,
                'best_of' => 1,
                // 'logit_bias' => '',
                // 'user' => '',
            ],
        );
    }

    /**
     * Executes a request to the chat endpoint.
     *
     * @param string $message
     * @param string|null $model
     * @return array
     */
    public function execChat(string $message, ?string $model = null): array
    {
        return $this->curl(
            url: $this->genUrl('chat'),
            header: $this->genHeader(),
            body: [
                'model' => $model ?? $this->chatModel,
                'messages' => [
                    [
                        "role" => "system",
                        "content" => $message,
                    ],
                ],
                'temperature' => 0.7,
                'top_p' => 1,
                'n' => 1,
                'stream' => false,
                'stop' => null,
                'max_tokens' => 1000,
                'presence_penalty' => 0,
                'frequency_penalty' => 0,
                // 'logit_bias' => '',
                // 'user' => '',
            ],
        );
    }

    /**
     * Executes a request to the edits endpoint.
     *
     * @param string $input
     * @param string $instruction
     * @param string|null $model
     * @return array
     */
    public function execEdits(string $input, string $instruction, ?string $model = null): array
    {
        return $this->curl(
            url: $this->genUrl('edits'),
            header: $this->genHeader(),
            body: [
                'model' => $model ?? $this->editsModel,
                'input' => $input,
                'instruction' => $instruction,
                'n' => 1,
                'temperature' => 0.7,
                'top_p' => 1,
            ],
        );
    }
}
