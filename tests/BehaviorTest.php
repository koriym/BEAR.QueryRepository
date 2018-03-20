<?php
/**
 * This file is part of the BEAR.QueryRepository package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\QueryRepository;

use BEAR\QueryRepository\Exception\ReturnValueIsNotResourceObjectException;
use BEAR\QueryRepository\Exception\UnmatchedQuery;
use BEAR\Resource\Module\ResourceModule;
use BEAR\Resource\ResourceInterface;
use BEAR\Resource\ResourceObject;
use FakeVendor\HelloWorld\Resource\App\Code;
use FakeVendor\HelloWorld\Resource\App\User\Profile;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

class BehaviorTest extends TestCase
{
    /**
     * @var ResourceInterface
     */
    private $resource;

    /**
     * @var QueryRepository
     */
    private $repository;

    /**
     * @var HttpCache
     */
    private $httpCache;

    public function setUp()
    {
        $namespace = 'FakeVendor\HelloWorld';
        $injector = new Injector(new QueryRepositoryModule(new ResourceModule($namespace)), $_ENV['TMP_DIR']);
        $this->repository = $injector->getInstance(QueryRepositoryInterface::class);
        $this->resource = $injector->getInstance(ResourceInterface::class);
        $this->httpCache = $injector->getInstance(HttpCacheInterface::class);
        parent::setUp();
    }

    public function testPurgeSameResourceObjectByPatch()
    {
        /** @var $user ResourceObject */
        $user = $this->resource->get->uri('app://self/user')->withQuery(['id' => 1])->eager->request();
        $etag = $user->headers['ETag'];
        // reload (purge repository entry and re-generate by onGet)
        $this->resource->patch->uri('app://self/user')->withQuery(['id' => 1, 'name' => 'kuma'])->eager->request();
        // load from repository, not invoke onGet method
        $user = $this->resource->get->uri('app://self/user')->withQuery(['id' => 1])->eager->request();
        $newEtag = $user->headers['ETag'];
        $this->assertFalse($etag === $newEtag);
    }

    public function testPurgeSameResourceObjectByDelete()
    {
        /** @var $user ResourceObject */
        $user = $this->resource->get->uri('app://self/user')->withQuery(['id' => 1])->eager->request();
        $etag = $user->headers['ETag'];
        $server = [
            'REQUEST_METHOD' => 'GET',
            'HTTP_IF_NONE_MATCH' => $etag
        ];
        $isNotModified = $this->httpCache->isNotModified($server);
        $this->assertTrue($isNotModified);
        $this->resource->delete->uri('app://self/user')->withQuery(['id' => 1])->eager->request();
        $user = $this->resource->get->uri('app://self/user')->withQuery(['id' => 1])->eager->request();
        $newEtag = $user->headers['ETag'];
        $this->assertFalse($etag === $newEtag);
        $isNotModified = $this->httpCache->isNotModified($server);
        $this->assertFalse($isNotModified);
    }

    public function testPurgeByAnnotation()
    {
        $this->resource->put->uri('app://self/user')->withQuery(['id' => 1, 'age' => 10, 'name' => 'Sunday'])->eager->request();
        $this->assertTrue(Profile::$requested);
    }

    public function testReturnValueIsNotResourceObjectException()
    {
        $this->expectException(ReturnValueIsNotResourceObjectException::class);
        $this->resource->put->uri('app://self/invalid')->withQuery(['id' => 1, 'age' => 10, 'name' => 'Sunday'])->eager->request();
    }

    public function testUnMatchQuery()
    {
        $this->expectException(UnmatchedQuery::class);
        $this->resource->put->uri('app://self/unmatch')->withQuery(['id' => 1, 'age' => 10, 'name' => 'Sunday'])->eager->request();
    }

    public function testCacheCode()
    {
        $ro = $this->resource->get->uri('app://self/code')->withQuery([])->eager->request(); // 1
        /* @var $ro Code */
        $ro->code = 203;
        $ro->onGet(); // 2 non-caached
        $ro->code = 500;
        $ro->onGet(); // 3 non-caached
        $this->assertSame(3, Code::$i);
        $ro->code = 200;
        $ro->onGet(); // 4 cached
        $ro->onGet();
        $this->assertSame(4, Code::$i);
    }
}
