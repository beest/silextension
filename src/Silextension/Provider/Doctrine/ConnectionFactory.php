<?php

namespace Silextension\Provider\Doctrine;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\EventManager;

class ConnectionFactory
{
    /**
     * Stub method to call the static method DriverManager::getConnection.
     *
     * @param array         $options        The connection options
     * @param Configuration $config         An optional Doctrine DBAL Configuration object
     * @param EventManager  $EventManager   An optional Doctrine DBAL EventManager object
     * @return Connection object
     */
    public function stubDriverManagerGetConnection(array $options, Configuration $config = null, EventManager $eventManager = null)
    {
        return DriverManager::getConnection($options, $config, $eventManager);
    }

    /**
     * Create a Doctrine DBAL database connection with the specified options.
     *
     * @param array         $options        The connection options
     * @param Configuration $config         An optional Doctrine DBAL Configuration object
     * @param EventManager  $EventManager   An optional Doctrine DBAL EventManager object
     * @return Connection object
     */
    public function createConnection(array $options, Configuration $config = null, EventManager $eventManager = null)
    {
        // Call the DriverManager via a stub method that can be mocked
        // If we ever add additional code to this method then it will remain testable
        return $this->stubDriverManagerGetConnection($options, $config, $eventManager);
    }
}
