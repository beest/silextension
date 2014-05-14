<?php

use PHPUnit_Framework_TestCase as TestCase;
use Mockery as Mockery;
use Silex\Application;
use Silextension\Provider\Doctrine\ConnectionProvider;

class ConnectionProviderTest extends TestCase
{
    public function tearDown()
    {
        Mockery::close();
    }

    public function testSingle()
    {
        $app = new Application();

        $options = array(
            'driver' => 'pdo_mysql'
        );

        $app['config'] = array(
            'database' => $options
        );

        $connection = Mockery::mock('Doctrine\DBAL\Driver\PDOMySql\Driver');

        $connectionFactory = Mockery::mock('Silextension\Provider\Doctrine\ConnectionFactory');
        $connectionFactory
            ->shouldReceive('createConnection')
            ->with($options)
            ->once()
            ->andReturn($connection);

        $app['doctrine.connection_factory'] = $connectionFactory;

        $app->register(new ConnectionProvider);

        $this->assertEquals($app['database'], $connection);
    }

    public function testMultiple()
    {
        $app = new Application();

        $primaryOptions = array(
            'driver' => 'pdo_mysql'
        );

        $secondaryOptions = array(
            'driver' => 'pdo_sqlite'
        );

        $app['config'] = array(
            'database' => array(
                'primary' => $primaryOptions,
                'secondary' => $secondaryOptions
            )
        );

        $app->register(new ConnectionProvider);

        $connection = Mockery::mock('Doctrine\DBAL\Driver\PDOMySql\Driver');
        $sqliteConnection = Mockery::mock('Doctrine\DBAL\Driver\PDOSqlite\Driver');

        $connectionFactory = Mockery::mock('Silextension\Provider\Doctrine\ConnectionFactory');
        $connectionFactory
            ->shouldReceive('createConnection')
            ->with($primaryOptions)
            ->once()
            ->andReturn($connection);
        $connectionFactory
            ->shouldReceive('createConnection')
            ->with($secondaryOptions)
            ->once()
            ->andReturn($sqliteConnection);

        $app['doctrine.connection_factory'] = $connectionFactory;

        $this->assertInstanceOf('Pimple', $app['database']);
        $this->assertEquals($app['database']['primary'], $connection);
        $this->assertEquals($app['database']['secondary'], $sqliteConnection);
    }
}
