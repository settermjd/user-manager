<?php

declare(strict_types=1);

namespace UserManager\Form\Fieldset\Factory;

use Laminas\Db\Adapter\AdapterInterface;
use Psr\Container\ContainerInterface;
use UserManager\Form\Fieldset\ResetPasswordFieldset;
use UserManager\ConfigProvider;

final class ResetPasswordFieldsetFactory
{
    public function __invoke(ContainerInterface $container): ResetPasswordFieldset
    {
        $config = $container->get('config');
        $fieldset = new ResetPasswordFieldset(
            targetTable: $config[ConfigProvider::class][ConfigProvider::USERMANAGER_TABLE_NAME],
            targetColumn: $config['authentication']['username']
        );
        $fieldset->setDbAdapter($container->get(AdapterInterface::class));
        return $fieldset;
    }
}
