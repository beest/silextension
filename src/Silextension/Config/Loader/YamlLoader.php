<?php

namespace Silextension\Config\Loader;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Yaml\Parser as YamlParser;
use Silextension\Config\Exception\NoParserException;
use Silextension\Config\Exception\NotSupportedException;
use Silextension\Config\Exception\NotFoundException;

class YamlLoader extends Loader
{
    protected $parser;

    /**
     * Set the yaml parser.
     *
     * @param Symfony\Component\Yaml\Parser A YamlParser instance
     */
    public function setParser(YamlParser $parser)
    {
        $this->parser = $parser;
    }

    /**
     * Loads a yaml config file.
     *
     * @param string $filename The filename of the yaml config file
     * @param string $type The resource type
     */
    public function load($filename, $type = null)
    {
        if (!($this->parser instanceof YamlParser)) {
            throw new NoParserException('No parser provided');
        }

        if (!$this->supports($filename)) {
            throw new NotSupportedException(sprintf('Config file %s is not supported', $filename));
        }

        if (!$this->exists($filename)) {
            throw new NotFoundException(sprintf('Config file %s does not exist', $filename));
        }

        return $this->parser->parse(file_get_contents($filename));
    }

    /**
     * Returns true if this class supports the given resource.
     *
     * @param string $resource A resource
     * @param string $type The resource type
     *
     * @return Boolean true if this class supports the given resource, false otherwise
     */
    public function supports($resource, $type = null)
    {
        $filename = (string)$resource;
        $extension = strtolower(substr($filename, -4));

        if ($extension != '.yml') {
            return false;
        }

        return true;
    }

    /**
     * Check whether the given resource exists.
     *
     * @param string $resource A resource
     *
     * @return Boolean true if the resource exsists, false otherwise
     */
    public function exists($resource) {
        $filename = (string)$resource;

        if (!file_exists($filename)) {
            return false;
        }

        return true;
    }
}
