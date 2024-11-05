<?php

declare(strict_types=1);

namespace UserManager\Middleware;

use Laminas\EventManager\EventManager;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\SharedEventManager;
use Psr\Container\ContainerInterface;

final class EventManagerMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): EventManagerMiddleware
    {
        /** @var EventManager */
        $eventManager = $container->has(EventManagerInterface::class)
                        ? $container->get(EventManagerInterface::class)
                        : new EventManager(new SharedEventManager());

        return new EventManagerMiddleware($eventManager);
    }
}
