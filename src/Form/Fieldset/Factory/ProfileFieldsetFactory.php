<?php

declare(strict_types=1);

namespace UserManager\Form\Fieldset\Factory;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use User\Form\Fieldset\ProfileFieldset;

final class ProfileFieldsetFactory implements FactoryInterface
{
    /** @param string $requestedName */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): ProfileFieldset
    {
        return new ProfileFieldset($container->get('config')['app_settings'], $options);
    }
}
