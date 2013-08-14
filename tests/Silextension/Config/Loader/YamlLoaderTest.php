<?php

use PHPUnit_Framework_TestCase as TestCase;
use Mockery as Mockery;
use Silextension\Config\Loader\YamlLoader;
use Symfony\Component\Yaml\Parser as YamlParser;

class YamlLoaderTest extends TestCase
{
    public function testLoad()
    {
        $parser = new YamlParser;

        $loader = new YamlLoader;
        $loader->setParser($parser);

        $loader->load(__DIR__ . '/test.yml');
    }

    /**
     * @expectedException Silextension\Config\Exception\NoParserException
     */
    public function testNoParser()
    {
        $loader = new YamlLoader;
        $loader->load('test');
    }

    /**
     * @expectedException Silextension\Config\Exception\NotSupportedException
     */
    public function testResourceNotSupported()
    {
        $parser = Mockery::mock('Symfony\Component\Yaml\Parser');

        $loader = Mockery::mock('Silextension\Config\Loader\YamlLoader[supports]');
        $loader
            ->shouldReceive('supports')
            ->once()
            ->with('test.yml')
            ->andReturn(false);

        $loader->setParser($parser);

        $loader->load('test.yml');
    }

    /**
     * @expectedException Silextension\Config\Exception\NotFoundException
     */
    public function testResourceDoesNotExist()
    {
        $parser = Mockery::mock('Symfony\Component\Yaml\Parser');

        $loader = new YamlLoader;
        $loader->setParser($parser);

        $loader->load('does_not_exist.yml');
    }
}
