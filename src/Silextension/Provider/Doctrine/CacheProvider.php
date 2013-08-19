<?php

namespace Silextension\Provider\Doctrine;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Silextension\Provider\Doctrine\CacheFactory;
use RuntimeException;
use Pimple;

class CacheProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        if (!isset($app['cache.cache_factory'])) {
            // Create the default cache factory
            $app['cache.cache_factory'] = $app->share(function($app) {
                return new CacheFactory;
           });
        }
    }

    public function boot(Application $app)
    {
        if (!isset($app['config'])) {
            throw new RuntimeException('No config for ORM');
        }

        $config = $app['config'];

        if (isset($config['cache'])) {
            if (array_key_exists('driver', $config['cache'])) {
                // Single cache e.g. $app['cache']
                $app['cache'] = $this->createCache($app, $config['cache']);
            } else {
                // Multiple caches e.g. $app['cache']['name']
                // These are loaded on-demand via a Pimple container
                $container = new Pimple;

                $self = $this;

                foreach ($config['cache'] as $name => $options) {
                    $container[$name] = $container->share(function($container) use ($self, $app, $options) {
                        return $self->createCache($app, $options);
                    });
                }

                $app['cache'] = $container;
            }
        }
    }

    public function createCache(Application $app, array $options = array())
    {
        // If no driver specified then default to array cache
        $driver = isset($options['driver']) ? $options['driver'] : 'array';

        // Optionally provide a Memcache object
        $memcache = isset($app['cache.memcache']) ? $app['cache.memcache'] : null;

        return $app['cache.cache_factory']->createCache($driver, $memcache);
    }
}
