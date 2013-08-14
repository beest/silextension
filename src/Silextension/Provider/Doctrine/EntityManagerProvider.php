<?php

namespace Silextension\Provider\Doctrine;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Silextension\Provider\Doctrine\EntityManagerFactory;
use RuntimeException;
use Pimple;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\Common\Annotations\SimpleAnnotationReader;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\ArrayCache;

class EntityManagerProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
    }

    public function boot(Application $app)
    {
        if (!isset($app['config'])) {
            throw new RuntimeException('No config for ORM');
        }

        $config = $app['config'];

        if (isset($config['orm'])) {
            // The proxy namespace should always be provided
            if (array_key_exists('proxyNamespace', $config['orm'])) {
                // Single entity manager e.g. $app['doctrine.em']
                $app['doctrine.em'] = $this->createEntityManager($app, $config['orm']);
            } else {
                // Multiple entity managers e.g. $app['doctrine.em']['name']
                // These are loaded on-demand via a Pimple container
                $container = new Pimple;

                $self = $this;

                foreach ($config['orm'] as $name => $options) {
                    $container[$name] = $container->share(function($container) use ($self, $app, $name, $options) {
                        return $self->createEntityManager($app, $options);
                    });
                }

                $app['doctrine.em'] = $container;
            }
        }
    }

    /**
     * Create a Doctrine Entity Manager object with the specified options.
     *
     * @param Silex\Application $app        The Silex application in which dependencies can be injected
     * @param array             $options    The Entity Manager options
     * @return Entity Manager object
     */
    public function createEntityManager(Application $app, array $options = array())
    {
        // Check that we have a database and cache available

        if (!isset($app['database'])) {
            throw new RuntimeException('No database connection available for ORM');
        }

        if (!isset($app['cache'])) {
            throw new RuntimeException('No cache available for ORM');
        }

        // If the 'database' option is provided then use the database connection with a matching name e.g. $app['database']['foo']
        // If not then use the single top-level database connection e.g. $app['database']

        if (isset($options['database'])) {
            $databaseName = $options['database'];

            if (!isset($app['database'][$databaseName]) || !($app['database'][$databaseName] instanceof Connection)) {
                throw new RuntimeException(sprintf("No database connection '%s' provided for ORM", $databaseName));
            }

            $connection = $app['database'][$databaseName];
        } else {
            if (!($app['database'] instanceof Connection)) {
                throw new RuntimeException('No default database connection available for ORM');
            }

            $connection = $app['database'];
        }

        // If there is no entity manager factory defined then instantiate a default factory
        // If a different factory is required then it can be injected via doctrine.entity_manager_factory

        if (!isset($app['doctrine.entity_manager_factory'])) {
            $app['doctrine.entity_manager_factory'] = new EntityManagerFactory;
        }

        $entityManagerFactory = $app['doctrine.entity_manager_factory'];

        // If the 'cache' option is provided then use the cache with a matching name e.g. $app['cache']['foo']
        // If not then use the single top-level cache e.g. $app['cache']

        if (isset($options['cache'])) {
            $cacheName = $options['cache'];

            if (!isset($app['cache'][$cacheName]) || !($app['cache'][$cacheName] instanceof Cache)) {
                throw new RuntimeException(sprintf("No cache '%s' available for ORM", $cacheName));
            }

            $cache = $app['cache'][$cacheName];
        } else {
            if (!($app['cache'] instanceof Cache)) {
                throw new RuntimeException('No default cache available for ORM');
            }

            $cache = $app['cache'];
        }

        // If there is a Doctrine annotation driver then use it
        // If not then a default driver will be used

        $annotationDriver = isset($app['doctrine.annotation_driver']) ? $app['doctrine.annotation_driver'] : null;

        // First create the Doctrine entity manager Configuration object
        // Then use it to create the EntityManager object itself

        $configuration = $entityManagerFactory->createConfiguration($options, $cache, $annotationDriver);

        return $app['doctrine.entity_manager_factory']->createEntityManager($connection, $configuration);
    }
}
