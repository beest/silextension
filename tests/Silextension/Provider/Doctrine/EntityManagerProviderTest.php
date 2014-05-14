<?php

use PHPUnit_Framework_TestCase as TestCase;
use Mockery as Mockery;
use Silex\Application;
use Silextension\Provider\Doctrine\EntityManagerProvider;

class EntityManagerProviderTest extends TestCase
{
    public function tearDown()
    {
        Mockery::close();
    }

    public function testSingle()
    {
        $app = new Application();

        $options = array(
            // Use the top-level database and cache
            'proxyNamespace' => 'Test\Entity'
        );

        $app['config'] = array(
            'orm' => $options
        );

        $cache = Mockery::mock('Doctrine\Common\Cache\Cache');
        $app['cache'] = $cache;

        $annotationDriver = Mockery::mock('Doctrine\ORM\Mapping\Driver\AnnotationDriver');
        $app['doctrine.annotation_driver'] = $annotationDriver;

        $connection = Mockery::mock('Doctrine\DBAL\Connection');
        $app['database'] = $connection;

        $configuration = Mockery::mock('Doctrine\ORM\Configuration');

        $entityManagerFactory = Mockery::mock('Silextension\Provider\Doctrine\EntityManagerFactory');
        $entityManagerFactory
            ->shouldReceive('createConfiguration')
            ->once()
            ->with($options, $cache, $annotationDriver)
            ->andReturn($configuration);

        $entityManager = Mockery::mock('Doctrine\ORM\EntityManager');

        $entityManagerFactory
            ->shouldReceive('createEntityManager')
            ->once()
            ->with($connection, $configuration)
            ->andReturn($entityManager);

        $app['doctrine.entity_manager_factory'] = $entityManagerFactory;

        $app->register(new EntityManagerProvider);

        $this->assertEquals($app['doctrine.em'], $entityManager);
    }

    public function testMultiple()
    {
        $app = new Application();

        $options1 = array(
            'proxyNamespace' => 'Test\Entity',
            'database' => 'test1',
            'cache' => 'test1'
        );

        $options2 = array(
            'proxyNamespace' => 'Test\Entity',
            'database' => 'test2',
            'cache' => 'test2'
        );

        $app['config'] = array(
            'orm' => array(
                'test1' => $options1,
                'test2' => $options2
            )
        );

        $cache1 = Mockery::mock('Doctrine\Common\Cache\Cache');
        $cache2 = Mockery::mock('Doctrine\Common\Cache\Cache');
        $app['cache'] = array(
            'test1' => $cache1,
            'test2' => $cache2
        );

        $annotationDriver = Mockery::mock('Doctrine\ORM\Mapping\Driver\AnnotationDriver');
        $app['doctrine.annotation_driver'] = $annotationDriver;

        $connection1 = Mockery::mock('Doctrine\DBAL\Connection');
        $connection2 = Mockery::mock('Doctrine\DBAL\Connection');
        $app['database'] = array(
            'test1' => $connection1,
            'test2' => $connection2
        );

        $configuration1 = Mockery::mock('Doctrine\ORM\Configuration');
        $configuration2 = Mockery::mock('Doctrine\ORM\Configuration');

        $entityManagerFactory = Mockery::mock('Silextension\Provider\Doctrine\EntityManagerFactory');
        $entityManagerFactory
            ->shouldReceive('createConfiguration')
            ->once()
            ->with($options1, $cache1, $annotationDriver)
            ->andReturn($configuration1);
        $entityManagerFactory
            ->shouldReceive('createConfiguration')
            ->once()
            ->with($options2, $cache2, $annotationDriver)
            ->andReturn($configuration2);

        $entityManager1 = Mockery::mock('Doctrine\ORM\EntityManager');
        $entityManager2 = Mockery::mock('Doctrine\ORM\EntityManager');

        $entityManagerFactory
            ->shouldReceive('createEntityManager')
            ->once()
            ->with($connection1, $configuration1)
            ->andReturn($entityManager1);
        $entityManagerFactory
            ->shouldReceive('createEntityManager')
            ->once()
            ->with($connection2, $configuration2)
            ->andReturn($entityManager2);

        $app['doctrine.entity_manager_factory'] = $entityManagerFactory;

        $app->register(new EntityManagerProvider);

        $this->assertEquals($app['doctrine.em']['test1'], $entityManager1);
        $this->assertEquals($app['doctrine.em']['test2'], $entityManager2);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testNoConfig()
    {
        $app = new Application;
        $app->register(new EntityManagerProvider);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testNoDatabase()
    {
        $app = new Application;

        $options = array(
            // Use the top-level database and cache
            'proxyNamespace' => 'Test\Entity'
        );

        $app['config'] = array(
            'orm' => $options
        );

        $app->register(new EntityManagerProvider);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testNoCache()
    {
        $app = new Application;

        $options = array(
            // Use the top-level database and cache
            'proxyNamespace' => 'Test\Entity'
        );

        $app['config'] = array(
            'orm' => $options
        );

        $app['database'] = array();

        $app->register(new EntityManagerProvider);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testNotInstanceOfDatabaseSingle()
    {
        $app = new Application;

        $options = array(
            'proxyNamespace' => 'Test\Entity'
        );

        $app['config'] = array(
            'orm' => $options
        );

        $app['database'] = 'test';

        $app['cache'] = Mockery::mock('Doctrine\Common\Cache\Cache');

        $app->register(new EntityManagerProvider);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testNoDatabaseMultiple()
    {
        $app = new Application;

        $options = array(
            // Use the top-level database and cache
            'proxyNamespace' => 'Test\Entity',
            'database' => 'test'
        );

        $app['config'] = array(
            'orm' => $options
        );

        $app['database'] = array();

        $app['cache'] = Mockery::mock('Doctrine\Common\Cache\Cache');

        $app->register(new EntityManagerProvider);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testNotInstanceOfDatabaseMultiple()
    {
        $app = new Application;

        $options = array(
            'proxyNamespace' => 'Test\Entity',
            'database' => 'test'
        );

        $app['config'] = array(
            'orm' => $options
        );

        $app['database'] = array(
            'test' => 'test'
        );

        $app['cache'] = Mockery::mock('Doctrine\Common\Cache\Cache');

        $app->register(new EntityManagerProvider);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testNotInstanceOfCacheSingle()
    {
        $app = new Application;

        $options = array(
            'proxyNamespace' => 'Test\Entity'
        );

        $app['config'] = array(
            'orm' => $options
        );

        $app['database'] = Mockery::mock('Doctrine\DBAL\Connection');

        $app['cache'] = 'test';

        $app->register(new EntityManagerProvider);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testNoCacheMultiple()
    {
        $app = new Application;

        $options = array(
            // Use the top-level database and cache
            'proxyNamespace' => 'Test\Entity',
            'cache' => 'test'
        );

        $app['config'] = array(
            'orm' => $options
        );

        $app['database'] = Mockery::mock('Doctrine\DBAL\Connection');

        $app['cache'] = array();

        $app->register(new EntityManagerProvider);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testNotInstanceOfCacheMultiple()
    {
        $app = new Application;

        $options = array(
            'proxyNamespace' => 'Test\Entity',
            'cache' => 'test'
        );

        $app['config'] = array(
            'orm' => $options
        );

        $app['database'] = Mockery::mock('Doctrine\DBAL\Connection');

        $app['cache'] = array(
            'test' => 'test'
        );

        $app->register(new EntityManagerProvider);
    }
}
