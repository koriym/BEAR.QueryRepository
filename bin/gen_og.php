<?php

/**
 * Generate object graph diagram for the documentation
 *
 * @see https://github.com/ray-di/Ray.ObjectGrapher
 */

use BEAR\QueryRepository\CacheableModule;
use BEAR\QueryRepository\DonutCacheModule;
use BEAR\QueryRepository\QueryRepositoryModule;
use Ray\ObjectGrapher\ObjectGrapher;

require dirname(__DIR__) . '/vendor/autoload.php';

$og = new ObjectGrapher;

$dir = dirname(__DIR__) . '/docs/diagrams';
is_dir($dir) || mkdir($dir, 0755, true);
chdir($dir);

file_put_contents('qr.dot', $og(new QueryRepositoryModule()));
passthru('dot -T svg qr.dot > qr.svg');

file_put_contents('cacheable.dot', $og(new CacheableModule()));
passthru('dot -T svg cacheable.dot > cacheable.svg');

file_put_contents('donut.dot', $og(new DonutCacheModule()));
passthru('dot -T svg donut.dot > donut.svg');
