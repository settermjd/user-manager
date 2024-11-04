<?php

declare(strict_types=1);

namespace UserManager\Authz;

use Mezzio\Authentication\UserInterface;
use Psr\Http\Message\ServerRequestInterface;

use function method_exists;

/**
 * For use in standard Stdlib\ArrayObject entity implementations
 */
trait OwnershipInterfaceTrait
{
    public function assertOwnership(ServerRequestInterface $request, ?UserInterface $userInterface = null): bool
    {
        /** @var UserInterface */
        $identity = $userInterface ?? $request->getAttribute(UserInterface::class);
        return $identity->getDetail('userId') === $this->getOwnerId();
    }

    public function getOwnerId(): int|string|null
    {
        if (method_exists(static::class, 'offsetGet')) {
            return $this->offsetGet('userId');
        }
    }
}
