<?php

declare(strict_types=1);

namespace BEAR\QueryRepository\Log\Context;

use Koriym\SemanticLogger\AbstractContext;

/** @SuppressWarnings("PHPMD.NumberOfChildren") */
abstract class AbstractRepositoryContext extends AbstractContext
{
    /**
     * Upstream declares this as the literal empty string; concrete contexts provide semantic type names.
     *
     * @psalm-var string
     * @psalm-suppress LessSpecificClassConstantType
     */
    public const TYPE = '';

    /**
     * Upstream declares this as the literal empty string; concrete contexts provide schema URLs.
     *
     * @psalm-var string
     * @psalm-suppress LessSpecificClassConstantType
     */
    public const SCHEMA_URL = '';
}
