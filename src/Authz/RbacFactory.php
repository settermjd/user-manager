<?php

declare(strict_types=1);

namespace UserManager\Authz;

use ArrayAccess;
use Laminas\Permissions\Rbac\Exception\ExceptionInterface as RbacExceptionInterface;
use Laminas\Permissions\Rbac\Rbac as BaseRbac;
use Mezzio\Authorization\AuthorizationInterface;
use Mezzio\Authorization\Rbac\LaminasRbacAssertionInterface;
use Mezzio\Authorization\Exception;
use Psr\Container\ContainerInterface;
use UserManager\ConfigProvider;
use UserManager\Filter\HttpMethodToRbacPermissionFilter;

use function is_array;
use function sprintf;

class RbacFactory
{
    /**
     * @throws Exception\InvalidConfigException
     */
    public function __invoke(ContainerInterface $container): AuthorizationInterface
    {
        $config = $container->get('config')['mezzio-authorization-rbac'] ?? null;
        if (! is_array($config) && ! $config instanceof ArrayAccess) {
            throw new Exception\InvalidConfigException(sprintf(
                'Cannot create %s instance; no "mezzio-authorization-rbac" config key present',
                Rbac::class
            ));
        }
        if (! isset($config['roles'])) {
            throw new Exception\InvalidConfigException(sprintf(
                'Cannot create %s instance; no mezzio-authorization-rbac.roles configured',
                Rbac::class
            ));
        }
        if (! isset($config['permissions'])) {
            throw new Exception\InvalidConfigException(sprintf(
                'Cannot create %s instance; no mezzio-authorization-rbac.permissions configured',
                Rbac::class
            ));
        }

        $rbac = new BaseRbac();
        $this->injectRoles($rbac, $config['roles']);
        $this->injectPermissions($rbac, $config['permissions']);

        $assertion = $container->has(LaminasRbacAssertionInterface::class) ? $container->get(LaminasRbacAssertionInterface::class) : null;

        return new Rbac(
            rbac: $rbac,
            config: $container->get('config')[ConfigProvider::class],
            filter: new HttpMethodToRbacPermissionFilter(),
            assertion: $assertion
        );
    }

    /**
     * @throws Exception\InvalidConfigException
     */
    private function injectRoles(BaseRbac $rbac, array $roles): void
    {
        $rbac->setCreateMissingRoles(true);

        // Roles and parents
        foreach ($roles as $role => $parents) {
            try {
                $rbac->addRole($role, $parents);
            } catch (RbacExceptionInterface $e) {
                throw new Exception\InvalidConfigException($e->getMessage(), $e->getCode(), $e);
            }
        }
    }

    /**
     * @throws Exception\InvalidConfigException
     */
    private function injectPermissions(BaseRbac $rbac, array $specification): void
    {
        foreach ($specification as $role => $permissions) {
            foreach ($permissions as $permission) {
                try {
                    $rbac->getRole($role)->addPermission($permission);
                } catch (RbacExceptionInterface $e) {
                    throw new Exception\InvalidConfigException($e->getMessage(), $e->getCode(), $e);
                }
            }
        }
    }
}
