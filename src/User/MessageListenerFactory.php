<?php

declare(strict_types=1);

namespace UserManager\User;

use Mailer\MailerInterface;
use Mezzio\Helper\UrlHelper;
use Psr\Container\ContainerInterface;

final class MessageListenerFactory
{
    public function __invoke(ContainerInterface $container): MessageListener
    {
        return new MessageListener(
            $container->get(MailerInterface::class),
            $container->get(UrlHelper::class),
            $container->get('config')
        );
    }
}
