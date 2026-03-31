# BenchGecko PHP SDK

Official PHP client for the [BenchGecko](https://benchgecko.ai) API. Query AI model data, benchmark scores, and run side-by-side comparisons from PHP applications.

BenchGecko tracks every major AI model, benchmark, and provider. This package wraps the public REST API using Guzzle HTTP client with proper exception handling and typed method signatures.

## Installation

```bash
composer require benchgecko/benchgecko
```

Requires PHP 8.1 or later and Guzzle 7.

## Quick Start

```php
<?php

require 'vendor/autoload.php';

use BenchGecko\BenchGecko;

$client = new BenchGecko();

// List all tracked AI models
$models = $client->models();
echo count($models) . " models tracked\n";

// List all benchmarks
$benchmarks = $client->benchmarks();
foreach (array_slice($benchmarks, 0, 5) as $b) {
    echo $b['name'] . "\n";
}

// Compare two models head-to-head
$comparison = $client->compare(['gpt-4o', 'claude-opus-4']);
foreach ($comparison['models'] as $m) {
    echo $m['name'] . ': ' . json_encode($m['scores']) . "\n";
}
```

## API Reference

### `new BenchGecko(string $baseUrl, int $timeout, ?Client $httpClient)`

Create a new client instance.

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `$baseUrl` | string | `https://benchgecko.ai` | API base URL |
| `$timeout` | int | `30` | HTTP timeout in seconds |
| `$httpClient` | Client | `null` | Optional Guzzle client for testing |

### `$client->models(): array`

Fetch all AI models tracked by BenchGecko. Returns an array of associative arrays, each containing model metadata like name, provider, parameter count, pricing, and benchmark scores.

### `$client->benchmarks(): array`

Fetch all benchmarks tracked by BenchGecko. Returns an array of associative arrays with benchmark name, category, and description.

### `$client->compare(array $modelSlugs): array`

Compare two or more models side by side. Pass an array of model slug strings (minimum 2). Returns an associative array with per-model scores, pricing, and capability breakdowns.

## Error Handling

API errors throw `BenchGeckoException` with message and optional HTTP status code:

```php
use BenchGecko\BenchGecko;
use BenchGecko\BenchGeckoException;

try {
    $models = $client->models();
} catch (BenchGeckoException $e) {
    echo "API error ({$e->getStatusCode()}): {$e->getMessage()}\n";
}
```

## Data Attribution

Data provided by [BenchGecko](https://benchgecko.ai). Model benchmark scores are sourced from official evaluation suites. Pricing data is updated daily from provider APIs.

## Links

- [BenchGecko](https://benchgecko.ai) - AI model benchmarks, pricing, and rankings
- [API Documentation](https://benchgecko.ai/api-docs)
- [GitHub Repository](https://github.com/BenchGecko/benchgecko-php)

## License

MIT
