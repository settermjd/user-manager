<?php

declare(strict_types=1);

namespace UserManager\View\Helper;

use Mezzio\Authorization\AuthorizationInterface;
use Psr\Container\ContainerInterface;

final class RbacHelperFactory
{
    public function __invoke(ContainerInterface $container): RbacHelper
    {
        return new RbacHelper($container->get(AuthorizationInterface::class));
    }
}
