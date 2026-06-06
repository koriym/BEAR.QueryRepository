<?php

declare(strict_types=1);

namespace BEAR\QueryRepository\Log\Context;

use JsonSerializable;
use Override;

/**
 * Close of a manual_store scope: whether the resource was stored.
 */
final class ManualStoreResultContext extends AbstractRepositoryContext implements JsonSerializable
{
    public const TYPE = 'manual_store_result';
    public const SCHEMA_URL = 'https://bearsunday.github.io/BEAR.QueryRepository/schemas/context/manual_store_result.json';

    public function __construct(
        public readonly bool $stored,
    ) {
    }

    /** @return array<string, mixed> */
    #[Override]
    public function jsonSerialize(): array
    {
        return ['result' => $this->stored ? 'stored' : 'failed'];
    }
}
