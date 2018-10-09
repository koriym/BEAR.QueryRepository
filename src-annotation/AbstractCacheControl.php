<?php
/**
 * This file is part of the BEAR.QueryRepository package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\RepositoryModule\Annotation;

abstract class AbstractCacheControl
{
    /**
     * Is private cache
     *
     * true: Indicates that the response is intended for a single user and must not be stored by a shared cache. A private cache may store the response.
     * false: Indicates that the response may be cached by any cache.
     *
     * @var bool
     */
    public $isPrivate;

    /**
     * No cache without validation
     *
     * Forces caches to submit the request to the origin server for validation before releasing a cached copy.
     * This is *not* no-cache flag.
     *
     * @var bool
     */
    public $noCache;

    /**
     * No Store
     *
     * The cache should not store anything about the client request or server response.
     *
     * @var bool
     */
    public $noStore;

    /**
     * Must revalidate when cache is expired
     *
     * The cache must verify the status of the stale resources before using it and expired ones should not be used.
     *
     * @var bool
     */
    public $mustRevalidate;

    /**
     * Max time
     *
     * Specifies the maximum amount of time a resource will be considered fresh. Contrary to Expires, this directive is relative to the time of the request.
     *
     * @var int
     */
    public $maxAge;

    /**
     * Shared cache max time
     *
     * Takes precedence over max-age or the Expires header, but it only applies to shared caches (e.g., proxies) and is ignored by a private cache.
     *
     * @var int
     */
    public $sMaxAge;
}
