# BenchGecko for PHP

**The CoinGecko for AI.** PHP client for accessing AI model benchmarks, comparing language models, estimating inference costs, and discovering AI agents.

BenchGecko tracks 300+ AI models across 50+ providers with real benchmark scores, latency metrics, and transparent pricing. This package gives you structured access to that data in idiomatic PHP with strict typing, readonly properties, and zero external dependencies.

## Installation

Install via Composer:

```bash
composer require benchgecko/benchgecko
```

## Quick Start

```php
<?php

require_once 'vendor/autoload.php';

use BenchGecko\BenchGecko;

// Look up any model
$model = BenchGecko::getModel('claude-3.5-sonnet');
echo $model->name;       // Claude 3.5 Sonnet
echo $model->provider;   // Anthropic
echo $model->score('MMLU');  // 88.7

// List all tracked models
foreach (BenchGecko::listModels() as $id) {
    echo $id . PHP_EOL;
}
```

## Comparing Models

The comparison engine returns structured arrays with benchmark differences and pricing ratios. Positive diff values mean the first model scores higher:

```php
$result = BenchGecko::compareModels('gpt-4o', 'claude-3.5-sonnet');

echo "Cheaper: " . $result['cheaper'];       // gpt-4o
echo "Cost ratio: " . $result['cost_ratio'];  // 0.69

foreach ($result['benchmark_diff'] as $bench => $diff) {
    if ($diff !== null) {
        $winner = $diff >= 0 ? 'GPT-4o' : 'Claude 3.5 Sonnet';
        echo "$bench: $winner by " . abs($diff) . " pts\n";
    }
}
```

## Cost Estimation

Estimate inference costs before committing to a provider. All prices are per million tokens:

```php
$cost = BenchGecko::estimateCost('gpt-4o',
    inputTokens: 2_000_000,
    outputTokens: 500_000
);

echo "Input:  $" . $cost['input_cost'];   // $5.0
echo "Output: $" . $cost['output_cost'];  // $5.0
echo "Total:  $" . $cost['total'];        // $10.0
```

## Finding the Right Model

Filter models by benchmark performance with typed return values:

```php
// All models scoring 87+ on MMLU, sorted by score
$topReasoners = BenchGecko::topModels('MMLU', minScore: 87.0);
foreach ($topReasoners as $model) {
    echo "{$model->name}: {$model->score('MMLU')}\n";
}

// Cheapest model above a quality threshold
$budgetPick = BenchGecko::cheapestAbove('MMLU', 85.0);
if ($budgetPick !== null) {
    echo "{$budgetPick->name} at \${$budgetPick->costPerMillion()}/M tokens\n";
}
```

## Benchmark Categories

BenchGecko organizes 40+ benchmarks into categories covering reasoning, coding, math, instruction following, safety, multimodal, multilingual, and long context evaluation:

```php
foreach (BenchGecko::benchmarkCategories() as $key => $category) {
    echo $category['name'] . ': ' . implode(', ', $category['benchmarks']) . PHP_EOL;
    echo '  ' . $category['description'] . PHP_EOL;
}
```

## Built-in Model Catalog

The package ships with a curated catalog of major models from OpenAI, Anthropic, Google, Meta, Mistral, and DeepSeek. Each entry includes benchmark scores, parameter counts, context window sizes, and per-token pricing. All data is baked into the class with zero runtime dependencies.

```php
$model = BenchGecko::getModel('deepseek-v3');
echo $model->parameters;       // 671
echo $model->context_window;   // 128000
echo $model->costPerMillion(); // 0.685
```

## PHP 8.0+ Features

The package uses modern PHP features including readonly constructor properties, named arguments, match expressions, and strict typing. The `Model` and `Agent` classes are immutable value objects:

```php
$model = BenchGecko::getModel('gpt-4o');

// Readonly properties - safe to pass around
echo $model->id;              // gpt-4o
echo $model->context_window;  // 128000

// Null-safe lookups
$score = BenchGecko::getModel('unknown')?->score('MMLU');
// $score is null -- no exceptions thrown
```

## Use Cases

- **Model selection** -- programmatically pick the cheapest model that meets your quality bar
- **Cost monitoring** -- estimate monthly spend across different configurations
- **Benchmark dashboards** -- pull structured scores into Laravel, Symfony, or WordPress admin panels
- **API wrappers** -- build model routing logic based on benchmark performance

## Resources

- [BenchGecko](https://benchgecko.ai) -- Full platform with interactive comparisons
- [Source Code](https://github.com/BenchGecko/benchgecko-php) -- Contributions welcome

## License

MIT License. See [LICENSE](LICENSE) for details.
