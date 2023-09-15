<?php

namespace Adebipe\Cli;

use Adebipe\Services\Container;
use Adebipe\Services\Dotenv;
use Adebipe\Services\Injector;
use Adebipe\Services\Interfaces\CreatorInterface;
use Adebipe\Services\Interfaces\RegisterServiceInterface;
use Adebipe\Services\Interfaces\StarterServiceInterface;
use Adebipe\Services\Logger;
use ReflectionClass;

/**
 * Make the classes from the Application namespace
 * 
 * @package Adebipe\Cli
 */
class MakeClasses {
    public static Injector $injector;
    public static Container $container;

    /**
     * Make the classes from the Application namespace
     * 
     * @param array<string> $classes The list of the classes
     */
    public static function makeClasses(array $classes): array
    {
        $dotenv = new Dotenv();
        $logger = new Logger();
        $logger->info('Initialize the services');
        $injector = new Injector($logger);
        MakeClasses::$injector = $injector;
        $injector->addService($logger);
        

        $container = $injector->create_class(new ReflectionClass(Container::class));
        MakeClasses::$container = $container;
        $injector->addService($container);
        $injector->addService($injector);

        $container->addService($dotenv);
        $container->addService($logger);
        $container->addService($injector);
        $container->addService($container);

        $all_class = array();
        $atStart = array();
        foreach ($classes as $class) {
            $reflection = new ReflectionClass($class);
            $all_class[] = $reflection;
            $container->addReflection($reflection);
            if (strpos($class, 'Adebipe\\Services\\') !== 0)
            {
                continue;
            }
            if (in_array($class, [
                Dotenv::class,
                Logger::class,
                Injector::class,
                Container::class
            ])) {
                $logger->info('Skip service: ' . $class);
                continue;
            }
            if ($reflection->isAbstract() || $reflection->isInterface() || $reflection->isTrait())
            {
                continue;
            }
            if ($reflection->implementsInterface(CreatorInterface::class)) {
                $class = $injector->create_class($reflection);
                if ($reflection->implementsInterface(RegisterServiceInterface::class))
                {
                    $injector->addService($class);
                }
                if ($reflection->implementsInterface(StarterServiceInterface::class))
                {
                    $atStart_function = $reflection->getMethod('atStart');
                    $atStart[] = [$atStart_function, $class];
                }
                $container->addService($class);
            }
        }
        foreach ($atStart as $function) {
            $injector->execute($function[0], $function[1]);
        }
        $logger->atStart();
        return $all_class;
    }

    /**
     * Stop all the services
     */
    public static function stopServices(): void
    {
        $logger = MakeClasses::$injector->getService(Logger::class);
        $logger->info('Stopping the services');
        foreach (MakeClasses::$container->getSubclassInterfaces(StarterServiceInterface::class) as $service) {
            $reflection = new ReflectionClass($service);
            $atEnd = $reflection->getMethod('atEnd');
            MakeClasses::$injector->execute($atEnd, $service);
        }
    }
}