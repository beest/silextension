<?php

namespace Silextension\Provider\Doctrine;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\MemcacheCache;
use RuntimeException;
use Memcache;

class CacheFactory
{
    /**
     * Create a cache with the specified driver and options.
     *
     * @param string $driver The driver type of the cache to be created (array or memcached)
     * @param Memcache $memcache A Memcache object to be used by the cache
     * @param array $options The driver options
     * @return Cache object
     */
    public function createCache($driver, Memcache $memcache = null)
    {
        switch (strtolower($driver))
        {
            case 'array':
                return $this->createArrayCache();

            case 'memcached':
            case 'memcache':

                // If a memcache(d) cache is being create then we need to have a Memcache object
                if ($memcache === null) {
                    throw new RuntimeException('No Memcache object provided');
                }

                return $this->createMemcachedCache($memcache);

            default:
                throw new RuntimeException(sprintf("Cache driver '%s' is not supported", $driver));
        }
    }

    /**
     * Create an array cache.
     *
     * @return ArrayCache object
     */
    public function createArrayCache()
    {
        return new ArrayCache;
    }

    /**
     * Create a memcached cache.
     *
     * @param array $options The memcached driver options
     * @param Memcache $memcache A Memcache object to be used by the cache
     * @return ArrayCache object
     */
    public function createMemcachedCache(Memcache $memcache)
    {
        $cache = new MemcacheCache;
        $cache->setMemcache($memcache);

        return $cache;
    }
}
