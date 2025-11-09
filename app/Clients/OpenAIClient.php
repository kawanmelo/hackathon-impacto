<?php

namespace App\Clients;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Http\Client\Factory as HttpFactory;

class OpenAIClient
{
    private string $apiKey;
    private string $baseUrl;

    public function __construct(private readonly HttpFactory $httpClient)
    {
        $this->apiKey = config('services.openai.key');
        $this->baseUrl = config('services.openai.base_url');
    }

    public function generateChatResponse(
        string $model,
        array  $input,
        string $instructions = null,
        bool   $searchOnWeb = false,
    ): array
    {
        if ($searchOnWeb) {
            $tools[] = [
                'type' => 'web_search',
                'user_location' => [
                    'type' => 'approximate',
                    'country' => 'BR'
                ]
            ];
        }
        $parameters = [
            'model' => $model,
            'input' => $input,
            'instructions' => $instructions,
            'tools' => $tools ?? null,
            'store' => false
        ];
        return $this->makeRequest('POST', '/responses', $parameters);
    }

    public function generateEmbeddings(string|array $content, string $model): array
    {
        $parameters = [
            'input' => $content,
            'model' => $model
        ];
        return $this->makeRequest('POST', '/embeddings', $parameters);
    }


    private function makeRequest(string $method, string $endpoint, ?array $parameters): array
    {
        try {
            $client = $this->httpClient
                ->baseUrl($this->baseUrl)
                ->timeout(100)
                ->withHeaders($this->getDefaultHeaders());

            $response = match (strtoupper($method)) {
                'GET' => $client->get($endpoint, $parameters),
                'POST' => $client->post($endpoint, $parameters),
                'DELETE' => $client->delete($endpoint),
                default => throw new \InvalidArgumentException("Método HTTP não suportado: {$method}"),
            };
            $response->throw();
            return $response->json();

        } catch (RequestException $e) {
            \Log::error("Erro na API da OpenAI", [
                'status' => $e->response?->status(),
                'body' => $e->response?->body(),
                'error' => $e->getMessage(),
            ]);

            throw new HttpException(
                $e->response?->status() ?? Response::HTTP_BAD_GATEWAY,
                "Erro na resposta da OpenAI."
            );
        } catch (ConnectionException $e) {
            throw new HttpException(
                Response::HTTP_GATEWAY_TIMEOUT,
                "Erro de conexão com a OpenAI."
            );
        }
    }

    private function getDefaultHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->apiKey,
        ];
    }
}
