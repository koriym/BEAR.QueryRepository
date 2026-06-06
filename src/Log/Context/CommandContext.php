<?php

declare(strict_types=1);

namespace BEAR\QueryRepository\Log\Context;

/**
 * Open: a write/command scope (#[Refresh]/#[Purge]); purges nest under it.
 */
final class CommandContext extends AbstractRepositoryContext
{
    public const TYPE = 'command';
    public const SCHEMA_URL = 'https://bearsunday.github.io/BEAR.QueryRepository/schemas/context/command.json';

    /** @param list<array{class: string, uri: string}> $annotations */
    public function __construct(
        public readonly string $method,
        public readonly array $annotations,
    ) {
    }
}
