<?php

declare(strict_types=1);

namespace UserManager\Handler;

use App\HandlerTrait;
use Fig\Http\Message\RequestMethodInterface as Http;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\View\Model\ModelInterface;
use Mailer\Adapter\AdapterInterface;
use Mailer\Adapter\PhpMailer;
use Mailer\ConfigProvider as MailerConfigProvider;
use Mailer\Mailer;
use Mailer\MailerInterface;
use Mezzio\Authentication\UserRepositoryInterface;
use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use UserManager\ConfigProvider;
use UserManager\Form\ResendVerification;
use UserManager\Helper\VerificationHelper;
use UserManager\User\UserRepository;
use UserManager\UserRepository\UserEntity;

class VerifyAccountHandler implements RequestHandlerInterface
{
    use HandlerTrait;

    public function __construct(
        private TemplateRendererInterface $renderer,
        private UserRepositoryInterface&UserRepository $userRepositoryInterface,
        private MailerInterface&Mailer $mailer,
        private VerificationHelper $verifyHelper,
        private ResendVerification $form,
        private UrlHelper $urlHelper,
        private array $config
    ) {
    }

    public function handleGet(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $model = $request->getAttribute(ModelInterface::class);
            $model->setVariable('form', $this->form);
            if (
                ! $this->verifyHelper->verifyToken(
                    $request,
                    VerificationHelper::VERIFICATION_TOKEN,
                    $this->config['app_settings'][ConfigProvider::TOKEN_KEY][VerificationHelper::VERIFICATION_TOKEN]
                )
            ) {
                /** @var UserEntity */
                $target = $this->verifyHelper->getTarget();
                // unset this to allow a new token to be generated
                $target->offsetUnset(VerificationHelper::VERIFICATION_TOKEN);
                // send form to resend email
                $this->form->bind($target);
                $this->form->setAttributes([
                    'action' => $this->urlHelper->generate(
                        routeName: 'Verify Account',
                        options: ['reuse_result_params' => false]
                    ),
                    'method' => Http::METHOD_POST,
                ]);
                return new HtmlResponse($this->renderer->render(
                    'user-manager::verify-account',
                    $model
                ));
            }
        } catch (\Throwable $th) {
            throw $th;
        }
        // todo: Add System Message to workflow for notification
        return new RedirectResponse(
            $this->urlHelper->generate('Home')
        );
    }

    public function handlePost(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $body = $request->getParsedBody();
            $this->form->setData($body);
            if ($this->form->isValid()) {
                $userEntity = $this->form->getData();
                $serverParams = $request->getServerParams();
                $result = $this->userRepositoryInterface->save($userEntity, 'id');
                $result = $this->userRepositoryInterface->findOneBy('id', $result->id);
                /** @var Mailer */
                $mailer = $request->getAttribute(MailerInterface::class);
                /** @var PhpMailer */
                $adapter = $mailer->getAdapter();
                $mailConfig = $this->config[MailerConfigProvider::class][AdapterInterface::class] ?? null;
                $adapter?->to(
                    $result->email,
                    $result->firstName . ' ' . $result->lastName
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
                        $this->config['app_settings']['account_verification_token_max_lifetime'],
                        $serverParams['REQUEST_SCHEME'] . '://' . $serverParams['HTTP_HOST'],
                        $this->urlHelper->generate(
                            routeName: 'Verify Account',
                            routeParams: [
                                'id'    => $result->id,
                                'token' => $result->verificationToken,
                            ],
                            options: ['reuse_query_params' => false]
                        )
                    )
                );
                $mailer?->send($adapter);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
        return new RedirectResponse(
            $this->urlHelper->generate('Home')
        );
    }
}
