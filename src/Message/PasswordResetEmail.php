<?php

declare(strict_types=1);

namespace UserManager\Message;

use Message\EmailMessage;

final class PasswordResetEmail extends EmailMessage
{
    public const EVENT_PASSWORD_RESET_EMAIL = 'passwordResetEmail';
    public const SYSTEM_MESSAGE = <<<'EOM'
        If the email provided was valid there will be an email sent.
        Please follow the instructions to reset your password.
    EOM;
}
