<?php

declare(strict_types=1);

namespace UserManager\Message\Listener;

use Laminas\EventManager\AbstractListenerAggregate;
use Laminas\EventManager\EventManagerInterface;
use Mailer\Adapter\AdapterInterface;
use Mailer\ConfigProvider as MailConfigProvider;
use Mailer\MailerInterface;
use Message\SystemMessage;
use Mezzio\Helper\UrlHelper;
use UserManager\ConfigProvider;
use UserManager\Message;
use UserManager\Helper\VerificationHelper;

final class MessageListener extends AbstractListenerAggregate
{
    private const NOTIFY_MESSAGE = <<<'EOM'
    Verification email sent!
    EOM;
    private EventManagerInterface $events;

    public function __construct(
        private MailerInterface $mailer,
        private UrlHelper $urlHelper,
        private array $config
    ) {
    }

    /** attach listener methods */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->events = $events;

        $this->listeners[] = $events->attach(
            Message\VerificationEmail::EVENT_VERIFY_ACCOUNT_EMAIL,
            [$this, 'onVerifyAccountEmail'],
            $priority
        );

        $this->listeners[] = $events->attach(
            Message\VerificationEmail::EVENT_VERIFY_ACCOUNT_EMAIL,
            [$this, 'onNotifyVerifyEmailSent'],
            0
        );

        $this->listeners[] = $events->attach(
            Message\PasswordResetEmail::EVENT_PASSWORD_RESET_EMAIL,
            [$this, 'onPasswordResetEmail'],
            $priority
        );

        $this->listeners[] = $events->attach(
            Message\PasswordResetEmail::EVENT_PASSWORD_RESET_EMAIL,
            [$this, 'onNotifyPasswordResetEmailSent'],
            0
        );
    }

    /** Account Verification Email */
    public function onVerifyAccountEmail(Message\VerificationEmail $e)
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
            $status = $this->mailer->send($adapter);
            if ($status && ! $e->getNotify()) {
                $e->stopPropagation();
            }
            return $status;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /** Password Reset Email */
    public function onPasswordResetEmail(Message\PasswordResetEmail $e)
    {
        $adapter    = $this->mailer->getAdapter();
        $mailConfig = $this->config[MailConfigProvider::class][AdapterInterface::class] ?? null;
        $target     = $e->getTarget();
        $adapter?->to(
            $target->email,
            $target->firstName . ' ' . $target->lastName
        );
        $adapter?->isHtml();
        $adapter?->subject(
            sprintf(
                $mailConfig[ConfigProvider::MAIL_MESSAGE_TEMPLATES][ConfigProvider::MAIL_RESET_PASSWORD_SUBJECT],
                $this->config['app_settings']['app_name']
            )
        );
        $adapter?->body(
            sprintf(
                $mailConfig[ConfigProvider::MAIL_MESSAGE_TEMPLATES][ConfigProvider::MAIL_RESET_PASSWORD_MESSAGE_BODY],
                $this->config['app_settings'][ConfigProvider::TOKEN_KEY][VerificationHelper::PASSWORD_RESET_TOKEN],
                $e->getParam('host'), // host
                $this->urlHelper->generate(
                    routeName: 'Change Password',
                    routeParams: [
                        'id'    => $target->id,
                        'token' => $target->passwordResetToken,
                    ],
                    options: ['reuse_query_params' => false]
                )
            )
        );
        try {
            $status = $this->mailer->send($adapter);
            if ($status && ! $e->getNotify()) {
                $e->stopPropagation();
            }
            return $status;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function onNotifyVerifyEmailSent(Message\VerificationEmail $e)
    {
        if ($e->getNotify()) {
            $e->setName($e::EVENT_SYSTEM_MESSAGE);
            $e->setSystemMessage($e::SYSTEM_MESSAGE ?? static::NOTIFY_MESSAGE);
            return $this->events->triggerEvent($e);
        }

        return false;
    }

    public function onNotifyPasswordResetEmailSent(Message\PasswordResetEmail $e)
    {
        if ($e->getNotify()) {
            $e->setName($e::EVENT_SYSTEM_MESSAGE);
            $e->setSystemMessage($e::SYSTEM_MESSAGE ?? static::NOTIFY_MESSAGE);
            return $this->events->triggerEvent($e);
        }

        return false;
    }
}
