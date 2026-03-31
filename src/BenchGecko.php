<?php

declare(strict_types=1);

namespace BenchGecko;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Exception thrown when the BenchGecko API returns an error.
 */
class BenchGeckoException extends \RuntimeException
{
    private ?int $statusCode;

    public function __construct(string $message, ?int $statusCode = null, ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->statusCode = $statusCode;
    }

    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }
}

/**
 * Official PHP client for the BenchGecko API.
 *
 * Provides methods to query AI models, benchmarks, and perform
 * side-by-side model comparisons.
 *
 * @example
 * $client = new BenchGecko();
 * $models = $client->models();
 * echo count($models) . " models tracked\n";
 */
class BenchGecko
{
    private const DEFAULT_BASE_URL = 'https://benchgecko.ai';
    private const VERSION = '0.1.0';

    private Client $httpClient;
    private string $baseUrl;

    /**
     * Create a new BenchGecko client.
     *
     * @param string $baseUrl API base URL (default: https://benchgecko.ai)
     * @param int $timeout HTTP timeout in seconds (default: 30)
     * @param Client|null $httpClient Optional Guzzle client for testing
     */
    public function __construct(
        string $baseUrl = self::DEFAULT_BASE_URL,
        int $timeout = 30,
        ?Client $httpClient = null
    ) {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->httpClient = $httpClient ?? new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => $timeout,
            'headers' => [
                'User-Agent' => 'benchgecko-php/' . self::VERSION,
                'Accept' => 'application/json',
            ],
        ]);
    }

    /**
     * Send a GET request to the BenchGecko API.
     *
     * @throws BenchGeckoException
     */
    private function request(string $path, array $params = []): mixed
    {
        try {
            $options = [];
            if (!empty($params)) {
                $options['query'] = $params;
            }
            $response = $this->httpClient->get($path, $options);
            $body = $response->getBody()->getContents();
            return json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (GuzzleException $e) {
            $statusCode = method_exists($e, 'getResponse') && $e->getResponse()
                ? $e->getResponse()->getStatusCode()
                : null;
            throw new BenchGeckoException(
                'API request failed: ' . $e->getMessage(),
                $statusCode,
                $e
            );
        } catch (\JsonException $e) {
            throw new BenchGeckoException('Failed to parse JSON response', null, $e);
        }
    }

    /**
     * List all AI models tracked by BenchGecko.
     *
     * Returns an array of model data, each containing metadata like
     * name, provider, parameter count, pricing, and benchmark scores.
     *
     * @return array<int, array<string, mixed>>
     * @throws BenchGeckoException
     */
    public function models(): array
    {
        return $this->request('/api/v1/models');
    }

    /**
     * List all benchmarks tracked by BenchGecko.
     *
     * Returns an array of benchmark data with name, category,
     * and description.
     *
     * @return array<int, array<string, mixed>>
     * @throws BenchGeckoException
     */
    public function benchmarks(): array
    {
        return $this->request('/api/v1/benchmarks');
    }

    /**
     * Compare two or more AI models side by side.
     *
     * @param string[] $modelSlugs Array of model slugs (minimum 2)
     * @return array<string, mixed> Comparison result with per-model data
     * @throws \InvalidArgumentException If fewer than 2 models provided
     * @throws BenchGeckoException
     */
    public function compare(array $modelSlugs): array
    {
        if (count($modelSlugs) < 2) {
            throw new \InvalidArgumentException('At least 2 models are required for comparison.');
        }
        return $this->request('/api/v1/compare', [
            'models' => implode(',', $modelSlugs),
        ]);
    }
}
