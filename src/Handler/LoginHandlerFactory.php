<?php

declare(strict_types=1);

namespace UserManager\Handler;

use UserManager\Form\Login;
use Laminas\Form\FormElementManager;
use Laminas\ServiceManager\ServiceManager;
use Mezzio\Authentication\Session\PhpSession;
use Mezzio\LaminasView\LaminasViewRenderer;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Webmozart\Assert\Assert;

class LoginHandlerFactory
{
    /**
     *
     * @param ServiceManager $container
     * @return LoginHandler
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): LoginHandler
    {
        /** @var LaminasViewRenderer */
        $renderer    = $container->get(TemplateRendererInterface::class);
        /** @var FormElementManager */
        $formManager = $container->get(FormElementManager::class);
        /** @var Login */
        //$form = $formManager->get(Login::class);
        /** @var PhpSession */
        $adapter = $container->get(PhpSession::class);

        return new LoginHandler(
            $renderer,
            $adapter,
            $formManager->get(Login::class), // pull this from the form container
            $container->get('config')['authentication']
        );
    }
}
