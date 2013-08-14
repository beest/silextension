<?php

namespace Silextension\Provider\Doctrine;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Silextension\Provider\Doctrine\ConnectionFactory;
use Pimple;

class ConnectionProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        if (!isset($app['doctrine.connection_factory'])) {
            $app['doctrine.connection_factory'] = $app->share(function($app) {
                return new ConnectionFactory;
            });
        }
    }

    public function boot(Application $app)
    {
        $config = isset($app['config']) ? $app['config'] : array();

        if (isset($config['database'])) {
            if (array_key_exists('driver', $config['database'])) {
                // Single database e.g. $app['database']
                $options = $config['database'];

                $app['database'] = $this->createDriver($app, $options);
            } else {
                // Multiple databases e.g. $app['database']['name']
                // These are loaded on-demand via a Pimple container
                $container = new Pimple;

                $self = $this;

                foreach ($config['database'] as $name => $options) {
                    $container[$name] = $container->share(function($container) use ($self, $app, $options) {
                        return $self->createDriver($app, $options);
                    });
                }

                $app['database'] = $container;
            }
        }
    }

    public function createDriver(Application $app, array $options = array())
    {
        return $app['doctrine.connection_factory']->createConnection($options);
    }
}
