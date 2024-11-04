<?php

declare(strict_types=1);

namespace UserManager\Form\Fieldset\Factory;

use App\ConfigProvider as AppProvider;
use Laminas\Db\Adapter\AdapterAwareInterface;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use UserManager\Form\Fieldset\ChangePasswordFieldset;
use UserManager\Form\Fieldset\PasswordFieldset;
use Webinertia\Validator\PasswordRequirement;

final class PasswordFieldsetFactory implements FactoryInterface
{
    /** @inheritDoc */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): PasswordFieldset|ChangePasswordFieldset
    {
        $passwordOptions = $container->get('config')[AppProvider::APP_SETTINGS_KEY][PasswordRequirement::class]['options'];
        $instance = new $requestedName(
            options: [
                'password_options' => $passwordOptions
            ]
        );

        if ($instance instanceof AdapterAwareInterface) {
            $instance->setDbAdapter($container->get(AdapterInterface::class));
        }

        return $instance;
    }
}
