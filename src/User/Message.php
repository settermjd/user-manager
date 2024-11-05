<?php

declare(strict_types=1);

namespace UserManager\User;

use UserManager\Event\MessageEvent;

enum Message: string
{
    case Ui            = MessageEvent::EVENT_UI_MESSAGE;
    case Email         = MessageEvent::EVENT_EMAIL_MESSAGE;
    case VerifyAccount = MessageEvent::EVENT_EMAIL_ACCOUNT_VERIFICATION;
}
