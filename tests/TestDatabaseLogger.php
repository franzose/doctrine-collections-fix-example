<?php
declare(strict_types=1);

namespace Hierarchy\Tests;

use Psr\Log\AbstractLogger;
use Stringable;

final class TestDatabaseLogger extends AbstractLogger
{
    private array $queries = [];
    
    public function log($level, Stringable|string $message, array $context = []): void
    {
        if (array_key_exists('sql', $context)) {
            $this->queries[] = $context;
        }
    }

    /**
     * @return string[]
     */
    public function selectQueries(): array
    {
        $filter = static fn (string $query) => str_starts_with($query, 'SELECT');
        
        return array_values(array_filter(array_column($this->queries, 'sql'), $filter));
    }
}
