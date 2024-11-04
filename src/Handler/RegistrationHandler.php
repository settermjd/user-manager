<?php

declare(strict_types=1);

namespace UserManager\Handler;

use App\HandlerTrait;
use Fig\Http\Message\RequestMethodInterface as Http;
use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\View\Model\ModelInterface;
use Mailer\Adapter\AdapterInterface;
use Mailer\ConfigProvider as MailConfigProvider;
use Mailer\Adapter\PhpMailer;
use Mailer\Mailer;
use Mailer\MailerInterface;
use Mezzio\Authentication\UserRepositoryInterface;
use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use UserManager\ConfigProvider;
use UserManager\Form\Register;
use UserManager\Helper\VerificationHelper;
use UserManager\User\UserRepository;
use Webinertia\Filter\PasswordHash;

use function sprintf;

class RegistrationHandler implements RequestHandlerInterface
{
    use HandlerTrait;

    public function __construct(
        private TemplateRendererInterface $renderer,
        private UserRepositoryInterface&UserRepository $userRepositoryInterface,
        private Register $form,
        private UrlHelper $urlHelper,
        private array $config
    ) {
    }

    public function handleGet(ServerRequestInterface $request): ResponseInterface
    {
        $model = $request->getAttribute(ModelInterface::class);
        $model->setVariable('form', $this->form);
        return new HtmlResponse($this->renderer->render(
            'user-manager::registration',
            $model
        ));
    }

    public function handlePost(ServerRequestInterface $request): ResponseInterface
    {
        $model = $request->getAttribute(ModelInterface::class);
        $model->setVariable('form', $this->form);
        $body = $request->getParsedBody();
        $this->form->setData($body);
        if ($this->form->isValid()) {
            $uri = $request->getUri();
            $host = $uri->getScheme() . '://' . $uri->getHost();
            $host .= $uri->getPort() !== null ? ':' . $uri->getPort() : '';
            $userEntity = $this->form->getData();
            $userEntity->offsetUnset('conf_password');
            try {
                $userEntity->hashPassword();
                $result       = $this->userRepositoryInterface->save($userEntity, 'id');
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
                        $mailConfig[ConfigProvider::MAIL_MESSAGE_TEMPLATES][ConfigProvider::MAIL_VERIFY_SUBJECT],
                        $this->config['app_settings']['app_name']
                    )
                );
                $adapter?->body(
                    sprintf(
                        $mailConfig[ConfigProvider::MAIL_MESSAGE_TEMPLATES][ConfigProvider::MAIL_VERIFY_MESSAGE_BODY],
                        $this->config['app_settings'][ConfigProvider::TOKEN_KEY][VerificationHelper::VERIFICATION_TOKEN],
                        $host,
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
            } catch (\Throwable $th) {
                throw $th;
            }
            return new RedirectResponse(
                $this->urlHelper->generate('Home')
            );
        }
        return new HtmlResponse($this->renderer->render(
            'user-manager::registration',
            $model // parameters to pass to template
        ));
    }
}
