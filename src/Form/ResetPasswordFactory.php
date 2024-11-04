<?php

declare(strict_types=1);

namespace UserManager\Form;

use Mezzio\Helper\UrlHelper;
use Psr\Container\ContainerInterface;

final class ResetPasswordFactory
{
    public function __invoke(ContainerInterface $container): ResetPassword
    {
        $form = new ResetPassword();
        $form->setUrlHelper($container->get(UrlHelper::class));
        return $form;
    }
}
