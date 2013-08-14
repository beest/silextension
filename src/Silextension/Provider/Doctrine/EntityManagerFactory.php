<?php

namespace Silextension\Provider\Doctrine;

use Doctrine\Common\Cache\Cache;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;

class EntityManagerFactory
{
    /**
     * Create a Doctrine entity manager Configuration object.
     * This can subsequently be used to create the EntityManager object via a call to createEntityManager.
     *
     * @param array $options An array of entity manager options.
     * @param Doctrine\Common\Cache\Cache $cache The cache to be used for metadata and queries.
     * @param Doctrine\ORM\Mapping\Driver\AnnotationDriver $annotationDriver The annotation driver to be used for entity metadata. If null then the default annotation driver is created and used.
     * @return Entity manager Configuration object.
     */
    public function createConfiguration(array $options, Cache $cache, AnnotationDriver $annotationDriver = null)
    {
        $options = array_merge(array(
            'proxyDir' => '/tmp',
            'proxyNamespace' => 'Entity',
            'autoGenerateProxyClasses' => true
        ), $options);

        $configuration = new Configuration;
        $configuration->setMetadataCacheImpl($cache);
        $configuration->setQueryCacheImpl($cache);

        if ($annotationDriver instanceof AnnotationDriver) {
            $configuration->setMetadataDriverImpl($annotationDriver);
        } else {
            $configuration->setMetadataDriverImpl($configuration->newDefaultAnnotationDriver());
        }

        $configuration->setProxyDir($options['proxyDir']);
        $configuration->setProxyNamespace($options['proxyNamespace']);
        $configuration->setAutoGenerateProxyClasses($options['autoGenerateProxyClasses']);

        return $configuration;
    }

    /**
     * Stub method to call the static method EntityManager::create.
     *
     * @param Doctrine\DBAL\Connection $connection The connection to be used by the entity manager.
     * @param Doctrine\ORM\Configuration $configuration The configuration of the entity manager.
     * @return Entity Manager object.
     */
    public function stubEntityManagerCreate(Connection $connection, Configuration $configuration)
    {
        return EntityManager::create($connection, $configuration);
    }

    /**
     * Create a Doctrine EntityManager object using the specified connection and configuration.
     *
     * @param Doctrine\DBAL\Connection $connection The connection to be used by the entity manager.
     * @param Doctrine\ORM\Configuration $configuration The configuration of the entity manager.
     * @return Entity Manager object.
     */
    public function createEntityManager(Connection $connection, Configuration $configuration)
    {
        // Call the DriverManager via a stub method that can be mocked
        // If we ever add additional code to this method then it will remain testable
        return $this->stubEntityManagerCreate($connection, $configuration);
    }
}
