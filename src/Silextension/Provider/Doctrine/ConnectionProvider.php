<?php

namespace Silextension\Provider\Doctrine;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Application;
use Silextension\Provider\Doctrine\ConnectionFactory;

class ConnectionProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        if (!isset($container['doctrine.connection_factory'])) {
            $container['doctrine.connection_factory'] = function($container) {
                return new ConnectionFactory;
            };
        }

        $config = isset($container['config']) ? $container['config'] : array();

        if (isset($config['database'])) {
            if (array_key_exists('driver', $config['database'])) {
                // Single database e.g. $container['database']
                $options = $config['database'];

                $container['database'] = $this->createDriver($container, $options);
            } else {
                // Multiple databases e.g. $container['database']['name']
                // These are loaded on-demand via a Pimple container
                $databases = new Container;

                $self = $this;

                foreach ($config['database'] as $name => $options) {
                    $databases[$name] = function($container) use ($self, $container, $options) {
                        return $self->createDriver($container, $options);
                    };
                }

                $container['database'] = $databases;
            }
        }
    }

    public function createDriver(Container $container, array $options = array())
    {
        return $container['doctrine.connection_factory']->createConnection($options);
    }
}
