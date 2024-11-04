<?php

declare(strict_types=1);

namespace UserManager\Form;

use Mezzio\Helper\UrlHelper;
use Psr\Container\ContainerInterface;

final class ResendVerificationFactory
{
    public function __invoke(ContainerInterface $container): ResendVerification
    {
        $form = new ResendVerification();
        $form->setUrlHelper($container->get(UrlHelper::class));
        return $form;
    }
}
