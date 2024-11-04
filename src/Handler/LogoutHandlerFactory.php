<?php

declare(strict_types=1);

namespace UserManager\Handler;

use Mezzio\LaminasView\LaminasViewRenderer;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;

class LogoutHandlerFactory
{
    public function __invoke(ContainerInterface $container): LogoutHandler
    {
        /** @var LaminasViewRenderer */
        $renderer = $container->get(TemplateRendererInterface::class);
        return new LogoutHandler(
            $renderer
        );
    }
}
