<?php

declare(strict_types=1);

namespace UserManager\Handler;

use App\HandlerTrait;
use App\SystemMessageInterface;
use Htmx\HtmxHandlerTrait;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Form\Exception\InvalidArgumentException;
use Laminas\Form\Exception\DomainException;
use Laminas\View\Model\ModelInterface;
use Mailer\Adapter\AdapterInterface;
use Mailer\ConfigProvider as MailConfigProvider;
use Mailer\Adapter\PhpMailer;
use Mailer\Mailer;
use Mailer\MailerInterface;
use Mezzio\Authentication\UserRepositoryInterface;
use Mezzio\Flash\Exception\InvalidHopsValueException;
use Mezzio\Flash\FlashMessages;
use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;
use UserManager\ConfigProvider;
use UserManager\Form\ResetPassword;
use UserManager\Helper\VerificationHelper;
use UserManager\User\UserEntity;
use UserManager\User\UserRepository;

class ResetPasswordHandler implements RequestHandlerInterface
{
    use HandlerTrait;
    use HtmxHandlerTrait;

    public final const MESSAGE_KEY = 'reset_notice_message';

    public function __construct(
        private TemplateRendererInterface $renderer,
        private UserRepositoryInterface&UserRepository $userRepositoryInterface,
        private UrlHelper $url,
        private VerificationHelper $verifyHelper,
        private ResetPassword $form,
        private array $config
    ) {
    }

    public function handleGet(ServerRequestInterface $request): ResponseInterface
    {
        $model = $request->getAttribute(ModelInterface::class);
        $model->setVariable('form', $this->form);
        return new HtmlResponse(
            $this->renderer->render(
                'user-manager::reset-password',
                $model
            )
        );
    }

    /**
     * todo: switch out email message config for reset message templates
     * todo: code Email Message abstraction
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws InvalidArgumentException
     * @throws DomainException
     * @throws Throwable
     * @throws InvalidHopsValueException
     */
    public function handlePost(ServerRequestInterface $request): ResponseInterface
    {
        $body = $request->getParsedBody();
        $this->form->setData($body);
        if ($this->form->isValid()) {
            $userEntity = $this->form->getData();
            $uri = $request->getUri();
            $host = $uri->getScheme() . '://' . $uri->getHost();
            $host .= $uri->getPort() !== null ? ':' . $uri->getPort() : '';
            try {
                /** @var UserEntity */
                $result = $this->userRepositoryInterface->findOneBy('email', $userEntity->email);
                $result->setPasswordResetToken($userEntity->getPasswordResetToken());
                $result = $this->userRepositoryInterface->save($result, 'id');
                /** @var Mailer */
                $mailer = $request->getAttribute(MailerInterface::class);
                /** @var PhpMailer */
                $adapter = $mailer->getAdapter();
                $mailConfig = $this->config[MailConfigProvider::class][AdapterInterface::class] ?? null;
                $adapter?->to(
                    $result->email,
                    $result->firstName . ' ' . $result->lastName
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
                        $host, // host
                        $this->url->generate(
                            routeName: 'Change Password',
                            routeParams: [
                                'id'    => $result->id,
                                'token' => $result->passwordResetToken,
                            ],
                            options: ['reuse_query_params' => false]
                        )
                    )
                );
                $mailer?->send($adapter);
            } catch (\Throwable $th) {
                throw $th;
            }
        }
        /** @var FlashMessages */
        $messenger = $request->getAttribute(SystemMessageInterface::class);
        $messenger->flash(
            static::MESSAGE_KEY,
            'If the requested email address was found a reset email will be sent to that account.'
        );
        $model = $request->getAttribute(ModelInterface::class);
        return new HtmlResponse(
            html: $this->renderer->render(
                'user-manager::reset-password',
                $model
            )
        );
    }
}
