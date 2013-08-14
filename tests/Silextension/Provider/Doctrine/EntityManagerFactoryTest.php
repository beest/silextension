<?php

use PHPUnit_Framework_TestCase as TestCase;
use Mockery as Mockery;
use Silex\Application;
use Silextension\Provider\Doctrine\EntityManagerFactory;

class EntityManagerFactoryTest extends TestCase
{
    public function tearDown()
    {
        Mockery::close();
    }

    public function testCreateConfiguration()
    {
        $options = array(
            'proxyDir' => '/test',
            'proxyNamespace' => 'Test\Entity',
            'autoGenerateProxyClasses' => false
        );

        $cache = Mockery::mock('Doctrine\Common\Cache\Cache');
        $annotationDriver = Mockery::mock('Doctrine\ORM\Mapping\Driver\AnnotationDriver');

        $factory = new EntityManagerFactory;
        $configuration = $factory->createConfiguration($options, $cache, $annotationDriver);

        $this->assertEquals($configuration->getProxyDir(), $options['proxyDir']);
        $this->assertEquals($configuration->getProxyNamespace(), $options['proxyNamespace']);
        $this->assertEquals($configuration->getAutoGenerateProxyClasses(), $options['autoGenerateProxyClasses']);
    }

    public function testCreateEntityManager()
    {
        $connection = Mockery::mock('Doctrine\DBAL\Connection');
        $configuration = Mockery::mock('Doctrine\ORM\Configuration');

        $entityManagerMock = Mockery::mock('Doctrine\ORM\EntityManager');

        $factory = Mockery::mock('Silextension\Provider\Doctrine\EntityManagerFactory[stubEntityManagerCreate]');
        $factory
            ->shouldReceive('stubEntityManagerCreate')
            ->once()
            ->with($connection, $configuration)
            ->andReturn($entityManagerMock);

        $entityManager = $factory->createEntityManager($connection, $configuration);

        $this->assertEquals($entityManager, $entityManagerMock);
    }
}
