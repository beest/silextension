<?php

namespace Silextension\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\Config\Loader\LoaderInterface;
use RuntimeException;
use Pimple;

class ConfigProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
    }

    public function boot(Application $app)
    {
        if (!isset($app['config.loader']) || !($app['config.loader'] instanceof LoaderInterface)) {
            throw new RuntimeException('Config loader not provided');
        }

        if (!isset($app['config.files'])) {
            throw new RuntimeException('No config files');
        }

        $app['config'] = $app->share(function ($app) {
            // Here we are lazy loading each individual config file via another Pimple instance
            // $app['config'] will return the Pimple instance
            // $app['config']['key'] will load and parse a single config file on-demand

            $config = new Pimple;

            $files = (array)$app['config.files'];
            $path = isset($app['config.path']) ? $app['config.path'] : '';

            foreach ($files as $key => $file) {
                $config[$key] = $config->share(function ($config) use ($app, $path, $file) {
                    return $app['config.loader']->load($path . '/' . $file);
                });
            }

            return $config;
        });
    }
}
