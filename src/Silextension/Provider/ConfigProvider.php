<?php

namespace Silextension\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use RuntimeException;
use Silex\Application;
use Symfony\Component\Config\Loader\LoaderInterface;

class ConfigProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        if (!isset($container['config.loader']) || !($container['config.loader'] instanceof LoaderInterface)) {
            throw new RuntimeException('Config loader not provided');
        }

        if (!isset($container['config.files'])) {
            throw new RuntimeException('No config files');
        }

        $container['config'] = function ($container) {
            // Here we are lazy loading each individual config file via another Pimple instance
            // $container['config'] will return the Pimple instance
            // $container['config']['key'] will load and parse a single config file on-demand

            $config = new Container;

            $files = (array)$container['config.files'];
            $path = isset($container['config.path']) ? $container['config.path'] : '';

            foreach ($files as $key => $file) {
                $config[$key] = function ($config) use ($container, $path, $file) {
                    return $container['config.loader']->load($path . '/' . $file);
                };
            }

            return $config;
        };
    }
}
