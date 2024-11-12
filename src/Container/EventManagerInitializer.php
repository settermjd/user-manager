<?php

declare(strict_types=1);

namespace UserManager\Container;

use Laminas\EventManager\EventManagerAwareInterface;
use Laminas\EventManager\EventManagerInterface;
use Laminas\ServiceManager\Initializer\InitializerInterface;
use Psr\Container\ContainerInterface;

final class EventManagerInitializer implements InitializerInterface
{
    /** @inheritDoc */
    public function __invoke(ContainerInterface $container, $instance)
    {
        if (! $instance instanceof EventManagerAwareInterface) {
            return;
        }
        $instance->setEventManager($container->get(EventManagerInterface::class));
    }
}
