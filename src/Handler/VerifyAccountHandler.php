<?php

declare(strict_types=1);

namespace UserManager\Handler;

use App\HandlerTrait;
use Fig\Http\Message\RequestMethodInterface as Http;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\EventManager\EventManagerInterface;
use Laminas\View\Model\ModelInterface;
use Message\SystemMessage;
use Mezzio\Authentication\UserRepositoryInterface;
use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use UserManager\ConfigProvider;
use UserManager\Form\ResendVerification;
use UserManager\Helper\VerificationHelper;
use UserManager\Message\VerificationEmail;
use UserManager\User\UserRepository;
use UserManager\UserRepository\UserEntity;

class VerifyAccountHandler implements RequestHandlerInterface
{
    use HandlerTrait;

    public function __construct(
        private TemplateRendererInterface $renderer,
        private UserRepositoryInterface&UserRepository $userRepositoryInterface,
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
        $eventManager  = $request->getAttribute(EventManagerInterface::class);
        $systemMessage = new SystemMessage(SystemMessage::EVENT_SYSTEM_MESSAGE);
        $systemMessage->setSystemMessage('Verification successful! You can now login.');
        $eventManager->triggerEvent($systemMessage);
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
                $userEntity   = $this->form->getData();
                $result       = $this->userRepositoryInterface->save($userEntity, 'id');
                $result       = $this->userRepositoryInterface->findOneBy('id', $result->id);
                $eventManager = $request->getAttribute(EventManagerInterface::class);
                $email        = new VerificationEmail((VerificationEmail::EVENT_VERIFY_ACCOUNT_EMAIL));
                // set flag to send systemMessage notification
                $email->setNotify(true);
                $uri   = $request->getUri();
                $host  = $uri->getScheme() . '://' . $uri->getHost();
                $host  .= $uri->getPort() !== null ? ':' . $uri->getPort() : '';
                $email->setParam('host', $host);
                $email->setTarget($result);
                $messengerResult = $eventManager->triggerEvent($email);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
        return new RedirectResponse(
            $this->urlHelper->generate('Home')
        );
    }
}
