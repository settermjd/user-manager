<?php

declare(strict_types=1);

namespace UserManager\Authz;

use Laminas\Permissions\Rbac\Rbac as LaminasRbac;
use Mezzio\Authorization\AuthorizationInterface;
use Mezzio\Authorization\Rbac\LaminasRbacAssertionInterface;
use Mezzio\Authorization\Exception;
use Mezzio\Router\RouteResult;
use Psr\Http\Message\ServerRequestInterface;
use UserManager\ConfigProvider;
use UserManager\Filter\HttpMethodToRbacPermissionFilter;

use function assert;
use function in_array;
use function is_string;
use function sprintf;

class Rbac implements AuthorizationInterface
{
    // private bool $overrideUserInterface   = false;
    private bool $authorizeRequestedRoute = true;
    private ?string $routeName            = null;

    public function __construct(
        private LaminasRbac $rbac,
        private array $config,
        private HttpMethodToRbacPermissionFilter $filter,
        private ?LaminasRbacAssertionInterface $assertion = null,
    ) {
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception\RuntimeException
     */
    public function isGranted(string $role, ServerRequestInterface $request): bool
    {
        if ($this->authorizeRequestedRoute) {
            $routeResult = $request->getAttribute(RouteResult::class, false);
            if (! $routeResult instanceof RouteResult) {
                throw new Exception\RuntimeException(sprintf(
                    'The %s attribute is missing in the request; cannot perform authorizations',
                    RouteResult::class
                ));
            }

            // No matching route. Everyone can access.
            if ($routeResult->isFailure()) {
                return true;
            }

            $routeName = $routeResult->getMatchedRouteName();

            // append mapped http method to the routeName to match the crud permissions.
            if ($this->config[ConfigProvider::APPEND_HTTP_METHOD_TO_PERMS]) {
                if (
                    $this->config[ConfigProvider::APPEND_ONLY_MAPPED]
                    && ! empty($this->config[ConfigProvider::RBAC_MAPPED_ROUTES])
                    && in_array($routeName, $this->config[ConfigProvider::RBAC_MAPPED_ROUTES])
                ) {
                    $routeName = $this->filter->filter($routeName, $request->getMethod());
                } elseif(! $this->config[ConfigProvider::APPEND_ONLY_MAPPED]) {
                    $routeName = $this->filter->filter($routeName, $request->getMethod());
                }
            }
        } else {
            $routeName = $this->getRouteName();
        }

        assert(is_string($routeName));
        if (null !== $this->assertion) {
            $this->assertion->setRequest($request);
        }

        return $this->rbac->isGranted($role, $routeName, $this->assertion);
    }

    public function setRouteName(string $routeName): void
    {
        $this->routeName = $routeName;
    }

    public function getRouteName(): ?string
    {
        return $this->routeName;
    }

    public function setAuthorizeRequestedRoute(bool $authorizeRequestedRoute = true): void
    {
        $this->authorizeRequestedRoute = $authorizeRequestedRoute;
    }

    public function getAuthorizeRequestedRoute(): bool
    {
        return $this->authorizeRequestedRoute;
    }
}
