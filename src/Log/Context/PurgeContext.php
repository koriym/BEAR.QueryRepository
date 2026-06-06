<?php

declare(strict_types=1);

namespace BEAR\QueryRepository\Log\Context;

/**
 * Event: an explicit purge of a URI was requested.
 */
final class PurgeContext extends AbstractRepositoryContext
{
    public const TYPE = 'purge';
    public const SCHEMA_URL = 'https://bearsunday.github.io/BEAR.QueryRepository/schemas/context/purge.json';

    public function __construct(
        public readonly string $uri,
    ) {
    }
}
