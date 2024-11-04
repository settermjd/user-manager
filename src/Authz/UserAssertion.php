<?php

declare(strict_types=1);

namespace UserManager\Authz;

use Laminas\Permissions\Rbac\Rbac;
use Laminas\Permissions\Rbac\RoleInterface;
use Mezzio\Authentication\UserInterface;
use Mezzio\Authentication\DefaultUser;
use Mezzio\Authorization\Rbac\LaminasRbacAssertionInterface;
use Mezzio\Router\RouteResult;
use Psr\Http\Message\ServerRequestInterface;
use UserManager\UserRepository\UserEntity;

final class UserAssertion implements LaminasRbacAssertionInterface
{
    private ServerRequestInterface $request;

    public function setRequest(ServerRequestInterface $request): void
    {
        $this->request = $request;
    }

    public function assert(Rbac $rbac, RoleInterface $role, string $permission): bool
    {
        $result = match ($permission) {
            'Login'           => $this->assertUserIsGuest($role),
            'Change Password' => $this->changePasswordAssertion($role),
            default           => true,
        };

        return $result;
    }

    private function assertUserIsGuest(RoleInterface $role): bool
    {
        /** @var DefaultUser|UserEntity */
        $userInterface = $this->request->getAttribute(UserInterface::class, false);
        $result = $userInterface->getIdentity() === 'guest';
        return $result;
    }

    private function changePasswordAssertion($role): bool
    {
        /** @var DefaultUser|UserEntity */
        $userInterface = $this->request->getAttribute(UserInterface::class);
        /** @var RouteResult */
        $routeResult = $this->request->getAttribute(RouteResult::class);
        /** @var array */
        $params      = $routeResult->getMatchedParams();
        /** @var bool */
        $result      = match($role->getName()) {
            'Guest' => isset($params['id']) && isset($params['token']),
            'User'  => isset($params['id']) && $userInterface->getDetail('id') === (int) $params['id'],
            default => true,
        };

        return $result;
    }
}
