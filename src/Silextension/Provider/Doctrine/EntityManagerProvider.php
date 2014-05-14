<?php

namespace Silextension\Provider\Doctrine;

use Doctrine\Common\Annotations\SimpleAnnotationReader;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\Cache;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use RuntimeException;
use Silex\Application;
use Silextension\Provider\Doctrine\EntityManagerFactory;

class EntityManagerProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        if (!isset($container['config'])) {
            throw new RuntimeException('No config for ORM');
        }

        $config = $container['config'];

        if (isset($config['orm'])) {
            // The proxy namespace should always be provided
            if (array_key_exists('proxyNamespace', $config['orm'])) {
                // Single entity manager e.g. $container['doctrine.em']
                $container['doctrine.em'] = $this->createEntityManager($container, $config['orm']);
            } else {
                // Multiple entity managers e.g. $container['doctrine.em']['name']
                // These are loaded on-demand via a Pimple container
                $ems = new Container;

                $self = $this;

                foreach ($config['orm'] as $name => $options) {
                    $ems[$name] = function($container) use ($self, $container, $name, $options) {
                        return $self->createEntityManager($container, $options);
                    };
                }

                $container['doctrine.em'] = $ems;
            }
        }
    }

    /**
     * Create a Doctrine Entity Manager object with the specified options.
     *
     * @param Pimple\Container $container        The Silex application in which dependencies can be injected
     * @param array             $options    The Entity Manager options
     * @return Entity Manager object
     */
    public function createEntityManager(Container $container, array $options = array())
    {
        // Check that we have a database and cache available

        if (!isset($container['database'])) {
            throw new RuntimeException('No database connection available for ORM');
        }

        if (!isset($container['cache'])) {
            throw new RuntimeException('No cache available for ORM');
        }

        // If the 'database' option is provided then use the database connection with a matching name e.g. $container['database']['foo']
        // If not then use the single top-level database connection e.g. $container['database']

        if (isset($options['database'])) {
            $databaseName = $options['database'];

            if (!isset($container['database'][$databaseName]) || !($container['database'][$databaseName] instanceof Connection)) {
                throw new RuntimeException(sprintf("No database connection '%s' provided for ORM", $databaseName));
            }

            $connection = $container['database'][$databaseName];
        } else {
            if (!($container['database'] instanceof Connection)) {
                throw new RuntimeException('No default database connection available for ORM');
            }

            $connection = $container['database'];
        }

        // If there is no entity manager factory defined then instantiate a default factory
        // If a different factory is required then it can be injected via doctrine.entity_manager_factory

        if (!isset($container['doctrine.entity_manager_factory'])) {
            $container['doctrine.entity_manager_factory'] = new EntityManagerFactory;
        }

        $entityManagerFactory = $container['doctrine.entity_manager_factory'];

        // If the 'cache' option is provided then use the cache with a matching name e.g. $container['cache']['foo']
        // If not then use the single top-level cache e.g. $container['cache']

        if (isset($options['cache'])) {
            $cacheName = $options['cache'];

            if (!isset($container['cache'][$cacheName]) || !($container['cache'][$cacheName] instanceof Cache)) {
                throw new RuntimeException(sprintf("No cache '%s' available for ORM", $cacheName));
            }

            $cache = $container['cache'][$cacheName];
        } else {
            if (!($container['cache'] instanceof Cache)) {
                throw new RuntimeException('No default cache available for ORM');
            }

            $cache = $container['cache'];
        }

        // If there is a Doctrine annotation driver then use it
        // If not then a default driver will be used

        $annotationDriver = isset($container['doctrine.annotation_driver']) ? $container['doctrine.annotation_driver'] : null;

        // First create the Doctrine entity manager Configuration object
        // Then use it to create the EntityManager object itself

        $configuration = $entityManagerFactory->createConfiguration($options, $cache, $annotationDriver);

        return $container['doctrine.entity_manager_factory']->createEntityManager($connection, $configuration);
    }
}
