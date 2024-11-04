<?php

declare(strict_types=1);

namespace UserManager\Handler;

use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;

class AccountHandlerFactory
{
    public function __invoke(ContainerInterface $container) : AccountHandler
    {
        return new AccountHandler($container->get(TemplateRendererInterface::class));
    }
}
