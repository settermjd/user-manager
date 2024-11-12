<?php

declare(strict_types=1);

namespace UserManager\User;

use Laminas\EventManager\AbstractListenerAggregate;
use Laminas\EventManager\EventManagerInterface;
use Mailer\Adapter\AdapterInterface;
use Mailer\ConfigProvider as MailConfigProvider;
use Mailer\Event\MessageEvent as EmailMessage;
use Mailer\MailerInterface;
use Mezzio\Helper\UrlHelper;
use UserManager\ConfigProvider;
use UserManager\Event\MessageEvent as UserMessage;
use UserManager\Helper\VerificationHelper;
use UserManager\User\Message;

use function sprintf;

final class MessageListener extends AbstractListenerAggregate
{
    public function __construct(
        private MailerInterface $mailer,
        private UrlHelper $urlHelper,
        private array $config
    ) {
    }

    public function attach(EventManagerInterface $events, $priority = 1)
    {
        //$this->listeners[] = $events->attach(Message::Email->value, [$this, 'onEmailMessage'], $priority);
        $this->listeners[] = $events->attach(Message::Ui->value, [$this, 'onUiMessage'], $priority);
        $this->listeners[] = $events->attach(Message::VerifyAccount->value, [$this, 'onVerifyAccountMessage'], $priority);
    }

    public function onVerifyAccountMessage(UserMessage $e)
    {
        $target = $e->getTarget();
        // handle email messages via mail adapter
        $adapter = $this->mailer->getAdapter();
        $mailConfig = $this->config[MailConfigProvider::class][AdapterInterface::class] ?? null;
        $adapter?->to(
            $target->email,
            $target->firstName . ' ' . $target->lastName
        );
        $adapter?->isHtml();
        $adapter?->subject(
            sprintf(
                $mailConfig[ConfigProvider::MAIL_MESSAGE_TEMPLATES][ConfigProvider::MAIL_VERIFY_SUBJECT],
                $this->config['app_settings']['app_name']
            )
        );
        $adapter?->body(
            sprintf(
                $mailConfig[ConfigProvider::MAIL_MESSAGE_TEMPLATES][ConfigProvider::MAIL_VERIFY_MESSAGE_BODY],
                $this->config['app_settings'][ConfigProvider::TOKEN_KEY][VerificationHelper::VERIFICATION_TOKEN],
                $e->getParam('host'),
                $this->urlHelper->generate(
                    routeName: 'Verify Account',
                    routeParams: [
                        'id'    => $target->id,
                        'token' => $target->verificationToken,
                    ],
                    options: ['reuse_query_params' => false]
                )
            )
        );
        try {
            $status = $this->mailer?->send($adapter);
            return $status;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function onUiMessage(UserMessage $e)
    {
        // handle ui messages via flash messages
    }
}
