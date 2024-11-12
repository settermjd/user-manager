<?php

declare(strict_types=1);

namespace UserManager\Container;

use Laminas\EventManager\EventManager;
use Laminas\EventManager\SharedEventManagerInterface;
use Psr\Container\ContainerInterface;
use UserManager\User\MessageListener;

final class EventManagerFactory
{
    public function __invoke(ContainerInterface $container): EventManager
    {
        $listener = $container->get(MessageListener::class);
        $em       = new EventManager($container->get(SharedEventManagerInterface::class));
        $listener->attach($em);
        return $em;
    }
}
