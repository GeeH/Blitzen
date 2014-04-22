<?php

namespace Blitzen;


class ApplicationFactory
{
    /**
     * @var string
     */
    protected static $defaultOptionsPath = 'config/options.php';
    /**
     * @var string
     */
    protected static $defaultRoutesPath = 'config/routes.php';
    /**
     * @var string
     */
    protected static $defaultServicesPath = 'config/services.php';

    /**
     * @return string
     */
    public static function getDefaultServicesPath()
    {
        return self::$defaultServicesPath;
    }

    /**
     * @param string $defaultServicesPath
     */
    public static function setDefaultServicesPath($defaultServicesPath)
    {
        self::$defaultServicesPath = $defaultServicesPath;
    }

    /**
     * Gives you a configures Application
     *
     * @param array $options  Array of options - null attempts to load from default file
     * @param array $routes  Array of route definitions - null attempts to load from default file
     * @param array $services  Array of service declarations - null attempts to load from default file
     * @return Application
     * @throws \InvalidArgumentException
     */
    public static function gimme(array $options = null, array $routes = null, array $services = null)
    {
        if (is_null($options) && !file_exists(self::getDefaultOptionsPath())) {
            throw new \InvalidArgumentException('No options file found at ' . self::getDefaultOptionsPath());
        }
        if (is_null($options)) {
            $options = include(self::getDefaultOptionsPath());
        }

        if (is_null($routes) && !file_exists(self::getDefaultRoutesPath())) {
            throw new \InvalidArgumentException('No routes file found at ' . self::getDefaultRoutesPath());
        }
        if (is_null($routes)) {
            $routes = include(self::getDefaultRoutesPath());
        }

        if(is_null($services) && !file_exists(self::getDefaultServicesPath())) {
            throw new \InvalidArgumentException('No services file found at ' . self::getDefaultServicesPath());
        }
        if(is_null($services)) {
            $services = include(self::getDefaultServicesPath());
        }

        return new Application($options, $routes, $services);
    }

    /**
     * @return string
     */
    public static function getDefaultOptionsPath()
    {
        return self::$defaultOptionsPath;
    }

    /**
     * @param string $defaultOptionsPath
     */
    public static function setDefaultOptionsPath($defaultOptionsPath)
    {
        self::$defaultOptionsPath = $defaultOptionsPath;
    }

    /**
     * @return string
     */
    public static function getDefaultRoutesPath()
    {
        return self::$defaultRoutesPath;
    }

    /**
     * @param string $defaultRoutesPath
     */
    public static function setDefaultRoutesPath($defaultRoutesPath)
    {
        self::$defaultRoutesPath = $defaultRoutesPath;
    }


}