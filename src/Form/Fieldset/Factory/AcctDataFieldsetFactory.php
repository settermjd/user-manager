<?php

declare(strict_types=1);

namespace UserManager\Form\Fieldset\Factory;

use Laminas\Db\Adapter\AdapterInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use UserManager\Form\Fieldset\AcctDataFieldset;

final class AcctDataFieldsetFactory implements FactoryInterface
{
    /**
     * @param string $requestedName
     * @param null|mixed[] $options
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): AcctDataFieldset
    {
        return new AcctDataFieldset();
    }
}
