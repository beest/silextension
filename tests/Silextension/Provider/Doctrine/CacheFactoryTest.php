<?php

use PHPUnit_Framework_TestCase as TestCase;
use Mockery as Mockery;
use Silex\Application;
use Silextension\Provider\Doctrine\CacheFactory;

class CacheFactoryTest extends TestCase
{
    public function tearDown()
    {
        Mockery::close();
    }

    /**
     * @expectedException RuntimeException
     */
    public function testDriverNotSupported()
    {
        $cacheFactory = new CacheFactory;

        $cacheFactory->createCache('test');
    }

    public function testArray()
    {
        $cacheFactory = new CacheFactory;

        $cache = $cacheFactory->createCache('array');

        $this->assertInstanceOf('Doctrine\Common\Cache\ArrayCache', $cache);
    }

    public function testMemcached()
    {
        $cacheFactory = new CacheFactory;

        $memcache = Mockery::mock('Memcache');

        $cache = $cacheFactory->createCache('memcached', $memcache);

        $this->assertInstanceOf('Doctrine\Common\Cache\MemcacheCache', $cache);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testMemcachedNoMemcache()
    {
        $cacheFactory = new CacheFactory;

        $cache = $cacheFactory->createCache('memcached', null);

        $this->assertInstanceOf('Doctrine\Common\Cache\MemcacheCache', $cache);
    }
}
