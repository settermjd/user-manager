<?php

declare(strict_types=1);

namespace UserManager\Middleware;

use Laminas\EventManager\EventManagerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use UserManager\User\MessageListener;

final class EventManagerMiddleware implements MiddlewareInterface
{
    public function __construct(
        private EventManagerInterface $em,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $handler->handle($request->withAttribute(EventManagerInterface::class, $this->em));
    }
}
