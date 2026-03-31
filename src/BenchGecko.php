<?php

declare(strict_types=1);

namespace BenchGecko;

/**
 * BenchGecko - The CoinGecko for AI
 *
 * Data platform for AI model benchmarks, pricing, and agent comparison.
 * https://benchgecko.ai
 */
class BenchGecko
{
    public const VERSION = '0.1.0';

    /**
     * Built-in model catalog with benchmark scores and pricing.
     */
    private static array $models = [
        'gpt-4o' => [
            'name' => 'GPT-4o',
            'provider' => 'OpenAI',
            'parameters' => 200,
            'context_window' => 128000,
            'input_price' => 2.50,
            'output_price' => 10.00,
            'benchmarks' => ['MMLU' => 88.7, 'HumanEval' => 90.2, 'GSM8K' => 95.8, 'GPQA' => 53.6],
        ],
        'claude-3.5-sonnet' => [
            'name' => 'Claude 3.5 Sonnet',
            'provider' => 'Anthropic',
            'parameters' => null,
            'context_window' => 200000,
            'input_price' => 3.00,
            'output_price' => 15.00,
            'benchmarks' => ['MMLU' => 88.7, 'HumanEval' => 92.0, 'GSM8K' => 96.4, 'GPQA' => 59.4],
        ],
        'gemini-2.0-flash' => [
            'name' => 'Gemini 2.0 Flash',
            'provider' => 'Google',
            'parameters' => null,
            'context_window' => 1000000,
            'input_price' => 0.10,
            'output_price' => 0.40,
            'benchmarks' => ['MMLU' => 85.2, 'HumanEval' => 84.0, 'GSM8K' => 92.1],
        ],
        'llama-3.1-405b' => [
            'name' => 'Llama 3.1 405B',
            'provider' => 'Meta',
            'parameters' => 405,
            'context_window' => 128000,
            'input_price' => 3.00,
            'output_price' => 3.00,
            'benchmarks' => ['MMLU' => 88.6, 'HumanEval' => 89.0, 'GSM8K' => 96.8, 'GPQA' => 50.7],
        ],
        'mistral-large' => [
            'name' => 'Mistral Large',
            'provider' => 'Mistral',
            'parameters' => 123,
            'context_window' => 128000,
            'input_price' => 2.00,
            'output_price' => 6.00,
            'benchmarks' => ['MMLU' => 84.0, 'HumanEval' => 82.0, 'GSM8K' => 91.2],
        ],
        'deepseek-v3' => [
            'name' => 'DeepSeek V3',
            'provider' => 'DeepSeek',
            'parameters' => 671,
            'context_window' => 128000,
            'input_price' => 0.27,
            'output_price' => 1.10,
            'benchmarks' => ['MMLU' => 87.1, 'HumanEval' => 82.6, 'GSM8K' => 89.3, 'GPQA' => 59.1],
        ],
    ];

    /**
     * Benchmark categories tracked by BenchGecko.
     */
    private static array $categories = [
        'reasoning' => [
            'name' => 'Reasoning',
            'benchmarks' => ['MMLU', 'MMLU-Pro', 'ARC-Challenge', 'HellaSwag', 'WinoGrande', 'GPQA'],
            'description' => 'Logical reasoning, knowledge, and common sense',
        ],
        'coding' => [
            'name' => 'Coding',
            'benchmarks' => ['HumanEval', 'MBPP', 'SWE-bench', 'LiveCodeBench', 'BigCodeBench'],
            'description' => 'Code generation, debugging, and software engineering',
        ],
        'math' => [
            'name' => 'Mathematics',
            'benchmarks' => ['GSM8K', 'MATH', 'AIME', 'AMC', 'Competition-Math'],
            'description' => 'Mathematical problem solving from arithmetic to olympiad',
        ],
        'instruction' => [
            'name' => 'Instruction Following',
            'benchmarks' => ['IFEval', 'MT-Bench', 'AlpacaEval', 'Chatbot-Arena'],
            'description' => 'Following complex instructions and conversational ability',
        ],
        'safety' => [
            'name' => 'Safety',
            'benchmarks' => ['TruthfulQA', 'BBQ', 'ToxiGen', 'BOLD'],
            'description' => 'Truthfulness, bias, and safety alignment',
        ],
        'multimodal' => [
            'name' => 'Multimodal',
            'benchmarks' => ['MMMU', 'MathVista', 'VQAv2', 'TextVQA', 'DocVQA'],
            'description' => 'Vision, document understanding, and cross-modal reasoning',
        ],
        'multilingual' => [
            'name' => 'Multilingual',
            'benchmarks' => ['MGSM', 'XL-Sum', 'FLORES'],
            'description' => 'Performance across languages and translation',
        ],
        'long_context' => [
            'name' => 'Long Context',
            'benchmarks' => ['RULER', 'NIAH', 'InfiniteBench', 'LongBench'],
            'description' => 'Retrieval and reasoning over long documents',
        ],
    ];

    /**
     * Retrieve a model by its identifier.
     *
     * @param string $modelId The model identifier (e.g., "gpt-4o")
     * @return Model|null The model object or null if not found
     */
    public static function getModel(string $modelId): ?Model
    {
        $data = self::$models[$modelId] ?? null;
        if ($data === null) {
            return null;
        }
        return new Model(id: $modelId, ...$data);
    }

    /**
     * List all available model identifiers.
     *
     * @return string[]
     */
    public static function listModels(): array
    {
        $keys = array_keys(self::$models);
        sort($keys);
        return $keys;
    }

    /**
     * Compare two models across benchmarks and pricing.
     *
     * Positive diff values mean model A scores higher.
     *
     * @return array{model_a: array, model_b: array, benchmark_diff: array, cheaper: ?string, cost_ratio: ?float}|null
     */
    public static function compareModels(string $idA, string $idB): ?array
    {
        $a = self::getModel($idA);
        $b = self::getModel($idB);
        if ($a === null || $b === null) {
            return null;
        }

        $allBenchmarks = array_unique(
            array_merge(array_keys($a->benchmarks), array_keys($b->benchmarks))
        );

        $benchmarkDiff = [];
        foreach ($allBenchmarks as $bench) {
            $scoreA = $a->score($bench);
            $scoreB = $b->score($bench);
            $benchmarkDiff[$bench] = ($scoreA !== null && $scoreB !== null)
                ? round($scoreA - $scoreB, 2)
                : null;
        }

        $costA = $a->costPerMillion();
        $costB = $b->costPerMillion();
        $cheaper = null;
        if ($costA !== null && $costB !== null) {
            $cheaper = $costA <= $costB ? $idA : $idB;
        }
        $costRatio = ($costA !== null && $costB !== null && $costB > 0)
            ? round($costA / $costB, 2)
            : null;

        return [
            'model_a' => $a->toSummary(),
            'model_b' => $b->toSummary(),
            'benchmark_diff' => $benchmarkDiff,
            'cheaper' => $cheaper,
            'cost_ratio' => $costRatio,
        ];
    }

    /**
     * Estimate inference cost for a given token volume.
     *
     * @return array{model: string, input_tokens: int, output_tokens: int, input_cost: float, output_cost: float, total: float}|null
     */
    public static function estimateCost(
        string $modelId,
        int $inputTokens,
        int $outputTokens = 0
    ): ?array {
        $model = self::getModel($modelId);
        if ($model === null || $model->input_price === null || $model->output_price === null) {
            return null;
        }

        $inputCost = round($model->input_price * $inputTokens / 1_000_000, 4);
        $outputCost = round($model->output_price * $outputTokens / 1_000_000, 4);

        return [
            'model' => $model->name,
            'input_tokens' => $inputTokens,
            'output_tokens' => $outputTokens,
            'input_cost' => $inputCost,
            'output_cost' => $outputCost,
            'total' => round($inputCost + $outputCost, 4),
        ];
    }

    /**
     * List all benchmark categories.
     *
     * @return array<string, array{name: string, benchmarks: string[], description: string}>
     */
    public static function benchmarkCategories(): array
    {
        return self::$categories;
    }

    /**
     * Find models scoring above a threshold on a given benchmark.
     *
     * @param string $benchmark Benchmark name (e.g., "MMLU")
     * @param float $minScore Minimum score threshold
     * @return Model[] Sorted by score descending
     */
    public static function topModels(string $benchmark, float $minScore = 0): array
    {
        $results = [];
        foreach (self::$models as $id => $data) {
            $score = $data['benchmarks'][$benchmark] ?? null;
            if ($score !== null && $score >= $minScore) {
                $results[] = self::getModel($id);
            }
        }
        usort($results, fn(Model $a, Model $b) =>
            ($b->score($benchmark) ?? 0) <=> ($a->score($benchmark) ?? 0)
        );
        return $results;
    }

    /**
     * Find the cheapest model meeting a minimum benchmark score.
     *
     * @return Model|null
     */
    public static function cheapestAbove(string $benchmark, float $minScore): ?Model
    {
        $candidates = array_filter(
            self::topModels($benchmark, $minScore),
            fn(Model $m) => $m->costPerMillion() !== null
        );
        if (empty($candidates)) {
            return null;
        }
        usort($candidates, fn(Model $a, Model $b) =>
            $a->costPerMillion() <=> $b->costPerMillion()
        );
        return $candidates[0];
    }
}

/**
 * Represents an AI model with benchmark scores, pricing, and metadata.
 */
class Model
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $provider,
        public readonly ?int $parameters = null,
        public readonly ?int $context_window = null,
        public readonly ?float $input_price = null,
        public readonly ?float $output_price = null,
        public readonly array $benchmarks = [],
        public readonly array $metadata = [],
    ) {}

    /**
     * Get the score for a specific benchmark.
     */
    public function score(string $benchmarkName): ?float
    {
        return $this->benchmarks[$benchmarkName] ?? null;
    }

    /**
     * Cost per million tokens (average of input and output pricing).
     */
    public function costPerMillion(): ?float
    {
        if ($this->input_price === null || $this->output_price === null) {
            return null;
        }
        return round(($this->input_price + $this->output_price) / 2.0, 4);
    }

    /**
     * Summary array for comparison tables.
     */
    public function toSummary(): array
    {
        return [
            'name' => $this->name,
            'provider' => $this->provider,
            'parameters' => $this->parameters,
            'context_window' => $this->context_window,
            'cost_per_million' => $this->costPerMillion(),
        ];
    }

    public function __toString(): string
    {
        return "{$this->name} ({$this->provider}) - {$this->parameters}B params";
    }
}

/**
 * Represents an AI agent with capabilities and evaluation scores.
 */
class Agent
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $category,
        public readonly string $provider,
        public readonly array $modelsUsed = [],
        public readonly array $scores = [],
        public readonly array $capabilities = [],
        public readonly array $metadata = [],
    ) {}

    /**
     * Check if the agent supports a specific capability.
     */
    public function supports(string $capability): bool
    {
        return in_array($capability, $this->capabilities, true);
    }

    public function __toString(): string
    {
        return "{$this->name} ({$this->category}) by {$this->provider}";
    }
}
