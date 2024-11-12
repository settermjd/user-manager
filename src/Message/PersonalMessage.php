<?php

declare(strict_types=1);

namespace UserManager\Message;

use Message\SystemMessage;

final class PersonalMessage extends SystemMessage
{
    public const EVENT_PERSONAL_MESSAGE = 'personalMessage';
}
