<?php

declare(strict_types=1);

namespace UserManager\Validator;

use DateInterval;
use DateTimeImmutable;
use Laminas\Db\Sql;
use Laminas\Validator\AbstractValidator;
use Laminas\Validator\Exception;
use Ramsey\Uuid\Rfc4122\UuidV7;

use function array_key_exists;

/**
 *
 * @package UserManager\Validator
 *
 * Used for validating uuidv7 expiring tokens.
 */
final class UuidV7TokenValidator extends AbstractValidator
{
    public const ERROR_INVALID_TOKEN = 'invalidToken';
    public const ERROR_EXPIRED_TOKEN = 'expiredToken';
    public const ERROR_UNSUPPORTED_TOKEN_VERSION = 'unsupportedTokenType';

    /** @var array<string, string> Message templates */
    protected $messageTemplates = [
        self::ERROR_INVALID_TOKEN => 'The provided token is invalid.',
        self::ERROR_EXPIRED_TOKEN => 'The provided token has expired.',
        self::ERROR_UNSUPPORTED_TOKEN_VERSION => 'The provided token is not supported.',
    ];

    /**
     *  Expects valid values for DateInterval options
     * @var string
     */
    private string $maxLifetime;

    public function __construct($options = [])
    {
        parent::__construct($options);

        if (array_key_exists('max_lifetime', $options)) {
            $this->setMaxLifetime($options['max_lifetime']);
        }
    }

    public function isValid($value): bool
    {
        $valid = false;
        $this->setValue($value);
        try {
            /** @var UuidV7 */
            $uuid = UuidV7::fromString($value);
            if ($uuid->getFields()->getVersion() !== 7) {
                $this->error(static::ERROR_UNSUPPORTED_TOKEN_VERSION);
                return $valid;
            }
            $now           = new DateTimeImmutable();
            $tokenDateTime = $uuid->getDateTime();
            $expired       = $tokenDateTime->add(
                DateInterval::createFromDateString($this->getMaxLifetime())
            );
            if ($now < $expired) {
                $valid = true;
                return $valid;
            }
        } catch (\Throwable $th) {
            $this->error(static::ERROR_INVALID_TOKEN);
            throw $th;
        }
        return $valid;
    }

    public function setMaxLifetime(string $maxLifetime): self
    {
        $this->maxLifetime = $maxLifetime;
        return $this;
    }

    public function getMaxLifetime(): string
    {
        return $this->maxLifetime;
    }
}
