<?php

declare(strict_types=1);

namespace UserManager\View\Helper;

use Laminas\View\Helper\AbstractHelper;
use Mezzio\Authentication\UserInterface;
use Psr\Http\Message\ServerRequestInterface;
use UserManager\Authz\Rbac;

final class RbacHelper extends AbstractHelper
{
    private ServerRequestInterface $request;

    public function __construct(
        private Rbac $rbac
    ) {
    }

    public function __invoke(ServerRequestInterface $request): self
    {
        $this->request = $request;
        return $this;
    }

    public function isGranted(string $routeName, ?UserInterface $userInterface = null): bool
    {
        $this->rbac->setAuthorizeRequestedRoute(false);
        $this->rbac->setRouteName($routeName);

        if (empty($userInterface)) {
            $userInterface = $this->request->getAttribute(UserInterface::class);
        }
        $roles = $userInterface->getRoles();
        foreach ($roles as $role) {
            if ($this->rbac->isGranted($role, $this->request)) {
                return true;
            }
        }
        return false;
    }
}
