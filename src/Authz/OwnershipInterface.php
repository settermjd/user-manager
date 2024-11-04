<?php

declare(strict_types=1);

namespace UserManager\Authz;

use Mezzio\Authentication\UserInterface;
use Psr\Http\Message\ServerRequestInterface;

interface OwnershipInterface
{
    public function assertOwnership(ServerRequestInterface $request, ?UserInterface $userInterface = null): bool;
    public function getOwnerId(): int|string|null;
}
