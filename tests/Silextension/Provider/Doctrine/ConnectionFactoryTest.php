<?php

use PHPUnit_Framework_TestCase as TestCase;
use Mockery as Mockery;
use Silex\Application;
use Silextension\Provider\Doctrine\ConnectionFactory;

class ConnectionFactoryTest extends TestCase
{
    public function tearDown()
    {
        Mockery::close();
    }

    public function testCreateConnectionXYZ()
    {
        $options = array(
            'driver' => 'pdo_mysql'
        );

        $connectionMock = Mockery::mock('Doctrine\DBAL\Driver\PDOMySql\Driver');

        $factory = Mockery::mock('Silextension\Provider\Doctrine\ConnectionFactory[stubDriverManagerGetConnection]');
        $factory
            ->shouldReceive('stubDriverManagerGetConnection')
            ->once()
            ->with($options, null, null)
            ->andReturn($connectionMock);

        $connection = $factory->createConnection($options, null, null);

        $this->assertEquals($connection, $connectionMock);
    }
}
