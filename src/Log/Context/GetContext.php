<?php

declare(strict_types=1);

namespace BEAR\QueryRepository\Log\Context;

/**
 * Open: a resource (or donut) GET scope; embedded child GETs nest under it.
 */
final class GetContext extends AbstractRepositoryContext
{
    public const TYPE = 'get';
    public const SCHEMA_URL = 'https://bearsunday.github.io/BEAR.QueryRepository/schemas/context/get.json';

    public function __construct(
        public readonly string $uri,
    ) {
    }
}
