<?php

declare(strict_types=1);

namespace UserManager\User;

use ArrayObject;
use Axleus\Db;
use Mezzio\Authentication\UserInterface;
use Ramsey\Uuid\Lazy\LazyUuidFromString;
use Webinertia\Filter\PasswordHash;

final class UserEntity extends ArrayObject implements Db\EntityInterface, UserInterface
{
    public function __construct(
    ) {
        parent::__construct([], self::ARRAY_AS_PROPS);
    }

    public function getIdentity(): string
    {
        return $this->getEmail();
    }

    public function getRoles(): iterable
    {
        return (array) $this->offsetGet('roleId');
    }

    public function getDetail(string $name, $default = null)
    {
        return $this->offsetGet($name) ?? $default;
    }

    public function getDetails(): array
    {
        return $this->getArrayCopy();
    }

    public function setId(int $id): self
    {
        $this->offsetSet('id', $id);
        return $this;
    }

    public function getId(): ?int
    {
        return $this->offsetGet('id');
    }

    public function setEmail(string $email): self
    {
        $this->offsetSet('email', $email);
        return $this;
    }

    public function getEmail(): string
    {
        return $this->offsetGet('email');
    }

    public function setPassword(string $password): self
    {
        $this->offsetSet('password', $password);
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->offsetGet('password');
    }

    public function setToken(LazyUuidFromString|string $token): self
    {
        $this->offsetSet('verificationToken', $token);
        return $this;
    }

    public function getToken(): LazyUuidFromString|string
    {
        return $this->offsetGet('verificationToken');
    }

    public function setPasswordResetToken(LazyUuidFromString|string $token): self
    {
        $this->offsetSet('passwordResetToken', $token);
        return $this;
    }

    public function getPasswordResetToken(): LazyUuidFromString|string
    {
        return $this->offsetGet('passwordResetToken');
    }

    public function hashPassword(): string
    {
        $filter = new PasswordHash();
        $this->offsetSet('password', $filter->filter($this->offsetGet('password')));
        unset($filter);
        return $this->offsetGet('password');
    }
}
