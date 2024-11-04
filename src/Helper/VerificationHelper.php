<?php

declare(strict_types=1);

namespace UserManager\Helper;

use DateInterval;
use DateTimeImmutable;
use Laminas\Db\Sql\Select;
use Mezzio\Authentication\UserRepositoryInterface;
use Mezzio\Router\RouteResult;
use Psr\Http\Message\ServerRequestInterface;
use UserManager\ConfigProvider;
use UserManager\User\UserRepository;
use UserManager\User\UserEntity;
use UserManager\Validator\UuidV7TokenValidator as TokenValidator;

final class VerificationHelper
{
    final public const VERIFICATION_TOKEN   = 'verificationToken';
    final public const PASSWORD_RESET_TOKEN = 'passwordResetToken';
    final public const DEFAULT_LIFETIME     = '1 Hour';

    private UserEntity $target;

    public function __construct(
        private UserRepositoryInterface&UserRepository $userRepositoryInterface,
        private array $config
    ) {
    }

    public function verifyToken(
        ServerRequestInterface $request,
        ?string $type = self::VERIFICATION_TOKEN,
        ?string $tokenLifetime = null,
        ?bool   $returnTarget = false
    ): UserEntity|bool {
        $routeResult   = $request->getAttribute(RouteResult::class);
        $matchedParams = $routeResult->getMatchedParams();
        $data = [];
        try {

            if (empty($this->target)) {
                $this->target = $this->userRepositoryInterface->findOneBy('id', $matchedParams['id']);
            }

            if ($matchedParams['token'] === $this->target->offsetGet($type)) {
                $tokenValidator = new TokenValidator([
                        'max_lifetime' => $tokenLifetime ?? $this->config[ConfigProvider::TOKEN_KEY][$type],
                    ]);
                if ($tokenValidator->isValid($this->target->offsetGet($type))) {
                    $data = $this->target->getArrayCopy();
                    $now                 = new DateTimeImmutable();
                    $data['dateUpdated'] = $now->format($this->config['app_settings']['datetime_format']);
                    if ($type === self::VERIFICATION_TOKEN) {
                        $data['dateVerified'] = $data['dateUpdated'];
                        $data['verified']     = 1;
                    }
                    $data[$type] = null;
                    $this->target->exchangeArray($data);
                    $this->target = $this->userRepositoryInterface->save($this->target, 'id');
                    if ($returnTarget) {
                        return $this->target;
                    }
                    return true;
                }
            }
        } catch (\Throwable $th) {
            throw $th;
        }
        return false;
    }

    public function setTarget(UserEntity $target): void
    {
        $this->target = $target;
    }

    public function getTarget(): UserEntity
    {
        return $this->target;
    }

}
