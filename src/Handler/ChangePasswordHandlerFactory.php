<?php

declare(strict_types=1);

namespace UserManager\Handler;

use Laminas\Form\FormElementManager;
use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;
use UserManager\Form\ChangePassword;
use UserManager\Helper\VerificationHelper;
use UserManager\User\UserRepository;

class ChangePasswordHandlerFactory
{
    public function __invoke(ContainerInterface $container): ChangePasswordHandler
    {
        $fm = $container->get(FormElementManager::class);

        return new ChangePasswordHandler(
            $container->get(TemplateRendererInterface::class),
            $container->get(UserRepository::class),
            $fm->get(ChangePassword::class),
            $container->get(VerificationHelper::class),
            $container->get(UrlHelper::class),
            $container->get('config')
        );
    }
}
