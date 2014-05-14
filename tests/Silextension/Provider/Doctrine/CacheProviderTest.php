<?php

use PHPUnit_Framework_TestCase as TestCase;
use Mockery as Mockery;
use Silex\Application;
use Silextension\Provider\Doctrine\CacheProvider;
use Doctrine\Common\Cache\Cache;

class CacheProviderTest extends TestCase
{
    public function tearDown()
    {
        Mockery::close();
    }

    public function testSingle()
    {
        $app = new Application();

        $app['config'] = array(
            'cache' => array(
                'driver' => 'array'
            )
        );

        $arrayCache = Mockery::mock('Doctrine\Common\Cache\ArrayCache');

        $cacheFactory = Mockery::mock('Silextension\Provider\Doctrine\CacheFactory');
        $cacheFactory
            ->shouldReceive('createCache')
            ->with('array', null)
            ->once()
            ->andReturn($arrayCache);

        $app['cache.cache_factory'] = $cacheFactory;

        $app->register(new CacheProvider);

        $this->assertInstanceOf('Doctrine\Common\Cache\ArrayCache', $app['cache']);
        $this->assertEquals($app['cache'], $arrayCache);
    }

    public function testMultiple()
    {
        $app = new Application();

        $app['config'] = array(
            'cache' => array(
                'primary' => array(
                    'driver' => 'array'
                ),
                'secondary' => array(
                    'driver' => 'array'
                )
            )
        );

        $app->register(new CacheProvider);

        $arrayCachePrimary = Mockery::mock('Doctrine\Common\Cache\ArrayCache');
        $arrayCacheSecondary = Mockery::mock('Doctrine\Common\Cache\ArrayCache');

        $app['cache.cache_factory'] = function($app) use ($arrayCachePrimary, $arrayCacheSecondary) {

            $cacheFactory = Mockery::mock('Silextension\Provider\Doctrine\CacheFactory');
            $cacheFactory
                ->shouldReceive('createCache')
                ->with('array', null)
                ->once()
                ->andReturn($arrayCachePrimary);
            $cacheFactory
                ->shouldReceive('createCache')
                ->with('array', null)
                ->once()
                ->andReturn($arrayCacheSecondary);

            return $cacheFactory;
        };

        $this->assertInstanceOf('Pimple', $app['cache']);
        $this->assertEquals($app['cache']['primary'], $arrayCachePrimary);
        $this->assertEquals($app['cache']['secondary'], $arrayCacheSecondary);
    }
}
