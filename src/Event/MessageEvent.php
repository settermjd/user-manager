<?php

declare(strict_types=1);

namespace UserManager\Event;

use Laminas\EventManager\Event;
use Mezzio\Authentication\UserInterface;
use Webmozart\Assert\Assert;

class MessageEvent extends Event
{
    public final const EVENT_UI_MESSAGE                 = 'uiMessage';
    public final const EVENT_EMAIL_MESSAGE              = 'emailMessage';
    public final const EVENT_EMAIL_ACCOUNT_VERIFICATION = 'emailVerificationMessage';

    public function setTarget($target)
    {
        assert::isInstanceOf(
            $target,
            UserInterface::class,
            '$target must be an instance of: ' . UserInterface::class
        );
        $this->target = $target;
    }
}
