<?php

declare(strict_types=1);

namespace UserManager\Helper;

use Mezzio\Authentication\UserRepositoryInterface;
use Psr\Container\ContainerInterface;

final class VerificationHelperFactory
{
    public function __invoke(ContainerInterface $container): VerificationHelper
    {
        $helper = new VerificationHelper(
            $container->get(UserRepositoryInterface::class),
            $container->get('config')
        );

        return $helper;
    }
}
