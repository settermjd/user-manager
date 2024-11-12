<?php

declare(strict_types=1);

namespace UserManager\Handler;

use Laminas\Form\FormElementManager;
use Mezzio\Authentication\UserRepositoryInterface;
use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;
use UserManager\Form\ResendVerification;
use UserManager\Helper\VerificationHelper;

class VerifyAccountHandlerFactory
{
    public function __invoke(ContainerInterface $container): VerifyAccountHandler
    {
        $manager = $container->get(FormElementManager::class);
        return new VerifyAccountHandler(
            $container->get(TemplateRendererInterface::class),
            $container->get(UserRepositoryInterface::class),
            $container->get(VerificationHelper::class),
            $manager->get(ResendVerification::class),
            $container->get(UrlHelper::class),
            $container->get('config')
        );
    }
}
