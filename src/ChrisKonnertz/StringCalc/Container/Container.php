<?php

namespace ChrisKonnertz\StringCalc\Container;

use ChrisKonnertz\StringCalc\Exceptions\ContainerException;
use ChrisKonnertz\StringCalc\Exceptions\NotFoundException;

/**
 * This class is a PSR-11 compatible container for all the services that this library uses.
 * ( https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-11-container.md )
 * Instead of directly creating objects the library uses services that create these objects.
 * Container can be replaced from outside the library so they make it easy to replace
 * classes with custom implementations.
 *
 * @package ChrisKonnertz\StringCalc\Container
 */
class Container implements ContainerInterface
{

    /**
     * Array with the registered service providers.
     * They key of an item is the service name and
     * the value is the class name of a service provider.
     *
     * @var string[]
     */
    protected $serviceProviders;

    /**
     * Container constructor.
     *
     * @param ServiceProviderRegistry $serviceProviderRegistry
     * @throws ContainerException
     */
    public function __construct(ServiceProviderRegistry $serviceProviderRegistry)
    {
        $serviceProviders = $serviceProviderRegistry->getServiceProviders();

        if (! is_array($serviceProviders)) {
            throw new ContainerException('Error: Service provider registry delivered an invalid result.');
        }
        if (sizeof($serviceProviders) == 0) {
            throw new ContainerException('Error: Service provider registry delivered zero service providers.');
        }

        foreach ($serviceProviders as $serviceProvider) {
            if (! is_a($serviceProvider, AbstractServiceProvider::class)) {
                throw new ContainerException('Error: Invalid value for entry in service providers array.');
            }
        }

        $this->serviceProviders = $serviceProviders;
    }

    /**
     * Returns a service. The service name has to be registered.
     *
     * @param string $serviceName
     * @return object
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function get($serviceName)
    {
        if (! $this->has($serviceName)) {
            throw new NotFoundException('Error: Could not find service.');
        }

        $serviceProvider = $this->serviceProviders[$serviceName];

        if (! is_string($serviceProvider)) {
            throw new ContainerException(
                'Error: Expected class name of service provider as string but got something else.'
            );
        }

        try {
            // The name of the service provider class is stored, try to create an object from it
            $reflectionClass = new \ReflectionClass($serviceProvider);
        } catch (\ReflectionException $exception) {
            throw new ContainerException('Error: Could not find class to create service provider.');
        }

        try {
            /** @var AbstractServiceProvider $serviceProvider */
            $serviceProvider = $reflectionClass->newInstance();
        } catch (\ReflectionException $exception) {
            throw new ContainerException('Error: Could not create service provider via reflection.');
        }

        if (! is_a($serviceProvider, AbstractServiceProvider::class)) {
            throw new ContainerException(
                'Error: Service provider object does not inherit from AbstractServiceProvider.'
            );
        }

        try {
            // Try to make the provider provide the service. Might throw an exception.
            $object = $serviceProvider->provide();
        } catch (\Exception $exception) {
            if (is_a($exception, ContainerException::class)) {
                /** @var ContainerException $exception */
                throw $exception;
            } else {
                throw new ContainerException('Error: Service provider could not create service.');
            }
        }

        if ($object === null or ! is_object($object)) {
            throw new ContainerException('Error: Service provider did not provide a valid service.');
        }

        return $object;
    }

    /**
     * Returns true if a service exists.
     *
     * @param string $serviceName
     * @return bool
     */
    public function has($serviceName)
    {
        return array_key_exists($serviceName, $this->serviceProviders);
    }

    /**
     * Adds a service provider to the array with registered service providers.
     * If the service name is in use, the old entry will be replaced by the new.
     *
     * @param string                  $serviceName
     * @param AbstractServiceProvider $serviceProvider
     */
    public function add($serviceName, AbstractServiceProvider $serviceProvider)
    {
        $this->serviceProviders[$serviceName] = $serviceProvider;
    }

    /**
     * Returns the names of all registered services
     *
     * @return string[]
     */
    public function getNames()
    {
        return array_keys($this->serviceProviders);
    }

    /**
     * Returns the number of services
     *
     * @return int
     */
    public function size()
    {
        return sizeof($this->serviceProviders);
    }

}