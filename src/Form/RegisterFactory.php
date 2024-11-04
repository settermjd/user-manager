<?php

declare(strict_types=1);

namespace UserManager\Form;

use Mezzio\Helper\UrlHelper;
use Psr\Container\ContainerInterface;

final class RegisterFactory
{
    public function __invoke(ContainerInterface $container): Register
    {
        $form = new Register();
        $form->setUrlHelper($container->get(UrlHelper::class));
        return $form;
    }
}
