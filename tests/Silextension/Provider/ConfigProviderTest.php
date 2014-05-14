<?php

use PHPUnit_Framework_TestCase as TestCase;
use Mockery as Mockery;
use Silex\Application;
use Silextension\Provider\ConfigProvider;

class ConfigProviderTest extends TestCase
{
    public function testLoadMultiple()
    {
        $app = new Application();

        $fooBar = array(
            'foo' => 'bar'
        );

        $fooBaz = array(
            'foo' => 'baz'
        );

        $loader = Mockery::mock('Symfony\Component\Config\Loader\LoaderInterface');
        $loader
            ->shouldReceive('load')
            ->once()
            ->with('/foo/bar')
            ->andReturn($fooBar);
        $loader
            ->shouldReceive('load')
            ->once()
            ->with('/foo/baz')
            ->andReturn($fooBaz);

        $app['config.path'] = '/foo';
        $app['config.loader'] = $loader;
        $app['config.files'] = array(
            'bar' => 'bar',
            'baz' => 'baz'
        );

        $app->register(new ConfigProvider);

        $this->assertEquals($app['config']['bar'], $fooBar);
        $this->assertEquals($app['config']['baz'], $fooBaz);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testNoFiles()
    {
        $app = new Application();

        $loader = Mockery::mock('Symfony\Component\Config\Loader\LoaderInterface');
        $app['config.loader'] = $loader;

        $app->register(new ConfigProvider);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testNoLoader()
    {
        $app = new Application();

        $app->register(new ConfigProvider);
    }
}
