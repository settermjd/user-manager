<?php

declare(strict_types=1);

namespace UserManager\Form;

use Laminas\Db\Adapter\AdapterInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Mezzio\Helper\UrlHelper;
use Psr\Container\ContainerInterface;

class LoginFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): Login
    {
        $form = new Login();
        $form->setUrlHelper($container->get(UrlHelper::class));
        $form->setDbAdapter($container->get(AdapterInterface::class));
        return $form;
    }
}
