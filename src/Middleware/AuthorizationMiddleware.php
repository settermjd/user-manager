<?php

declare(strict_types=1);

namespace UserManager\Middleware;

use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\View\Model\ModelInterface;
use Mezzio\Authentication\UserInterface;
use Mezzio\Authorization\AuthorizationInterface;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthorizationMiddleware implements MiddlewareInterface
{
    public function __construct(
        private AuthorizationInterface $authorization,
        private TemplateRendererInterface $renderer
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $user  = $request->getAttribute(UserInterface::class, false);
        $model = $request->getAttribute(ModelInterface::class);

        // todo: Add logging

        if (! $user instanceof UserInterface) {
            return new HtmlResponse(
                $this->renderer->render(
                    'user-manager::401',
                    $model
                ),
                401
            );
        }

        foreach ($user->getRoles() as $role) {
            if ($this->authorization->isGranted($role, $request)) {
                return $handler->handle($request);
            }
        }

        return new HtmlResponse(
            $this->renderer->render(
                'user-manager::403',
                $model
            ),
            403
        );
    }
}
