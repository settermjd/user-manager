<?php

declare(strict_types=1);

namespace UserManager\Message;

use Message\EmailMessage;

final class VerificationEmail extends EmailMessage
{
    public const EVENT_VERIFY_ACCOUNT_EMAIL = 'verifyAccountEmail';
    public const SYSTEM_MESSAGE = <<<'EOM'
        We have sent a verification email to the address provided.
        Please follow the instructions to verify your email address.
    EOM;
}
