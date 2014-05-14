<?php

namespace Silextension\Provider\Doctrine;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use RuntimeException;
use Silex\Application;
use Silextension\Provider\Doctrine\CacheFactory;

class CacheProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        if (!isset($container['cache.cache_factory'])) {
            // Create the default cache factory
            $container['cache.cache_factory'] = function($container) {
                return new CacheFactory;
           };
        }

        if (!isset($container['config'])) {
            throw new RuntimeException('No config for ORM');
        }

        $config = $container['config'];

        if (isset($config['cache'])) {
            if (array_key_exists('driver', $config['cache'])) {
                // Single cache e.g. $container['cache']
                $container['cache'] = $this->createCache($container, $config['cache']);
            } else {
                // Multiple caches e.g. $container['cache']['name']
                // These are loaded on-demand via a Pimple container
                $caches = new Container;

                $self = $this;

                foreach ($config['cache'] as $name => $options) {
                    $caches[$name] = function($container) use ($self, $container, $options) {
                        return $self->createCache($container, $options);
                    };
                }

                $container['cache'] = $caches;
            }
        }
    }

    public function createCache(Container $container, array $options = array())
    {
        // If no driver specified then default to array cache
        $driver = isset($options['driver']) ? $options['driver'] : 'array';

        // Optionally provide a Memcache object
        $memcache = isset($container['cache.memcache']) ? $container['cache.memcache'] : null;

        return $container['cache.cache_factory']->createCache($driver, $memcache);
    }
}
