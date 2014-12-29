<?php

namespace BEAR\QueryRepository;

use BEAR\Resource\ResourceClientFactory;

class GetInterceptorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Resource
     */
    private $resource;

    public function setUp()
    {
        $this->resource = (new ResourceClientFactory)->newClient($_ENV['TMP_DIR'], 'FakeVendor\HelloWorld', new QueryRepositoryModule('FakeVendor\HelloWorld'));
        parent::setUp();
    }

    public function testInvoke()
    {
        $user = $this->resource->get->uri('app://self/user')->withQuery(['id' => 1])->eager->request();
        // put
        $expect = 'Last-Modified';
        $this->assertArrayHasKey($expect, $user->headers);
        $time = $user['time'];
        // get
        $user = $this->resource->get->uri('app://self/user')->withQuery(['id' => 1])->eager->request();
        $this->assertArrayHasKey($expect, $user->headers);
        $expect = $time;
        $this->assertSame($expect, $user['time']);
    }

}
