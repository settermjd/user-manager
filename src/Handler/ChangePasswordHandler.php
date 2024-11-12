<?php

declare(strict_types=1);

namespace UserManager\Handler;

use App\HandlerTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\EventManager\EventManagerInterface;
use Laminas\View\Model\ModelInterface;
use Laminas\View\Model\ViewModel;
use Message\SystemMessage;
use Mezzio\Authentication\UserInterface;
use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use Mezzio\Router\RouteResult;
use UserManager\ConfigProvider;
use UserManager\Form\ChangePassword;
use UserManager\Form\Fieldset\ChangePasswordFieldset;
use UserManager\Helper\VerificationHelper;
use UserManager\User\UserRepository;
use UserManager\User\UserEntity;

class ChangePasswordHandler implements RequestHandlerInterface
{
    use HandlerTrait;

    public function __construct(
        private TemplateRendererInterface $renderer,
        private UserRepository $userRepository,
        private ChangePassword $form,
        private VerificationHelper $helper,
        private UrlHelper $urlHelper,
        private array $config
    ) {
    }

    public function handleGet(ServerRequestInterface $request): ResponseInterface
    {
        /** @var RouteResult */
        $routeResult   = $request->getAttribute(RouteResult::class);
        /** @var array */
        $params        = $routeResult->getMatchedParams();
        $hasToken      = isset($params['id']) && isset($params['token']);
        $isValid       = false;
        $eventManager  = $request->getAttribute(EventManagerInterface::class);
        $systemMessage = new SystemMessage(SystemMessage::EVENT_SYSTEM_MESSAGE);
        if ($hasToken) {
            $isValid = $this->helper->verifyToken(
                $request,
                VerificationHelper::PASSWORD_RESET_TOKEN,
                $this->config['app_settings'][ConfigProvider::TOKEN_KEY][VerificationHelper::PASSWORD_RESET_TOKEN]
            );
        }
        if ($hasToken && $isValid) {

            /** @var ChangePasswordFieldset */
            $fieldset = $this->form->get('acct-data');
            $fieldset->remove('current_password');
            $this->form->setData(['acct-data' => ['id' => $params['id'], 'isTokenReset' => 1]]);
        } elseif($hasToken && ! $isValid) {

            $systemMessage->setSystemMessage(
                'Your reset link has expired, please use the form to request a new reset link.'
            );
            $eventManager->triggerEvent($systemMessage);
            return new RedirectResponse(
                $this->urlHelper->generate('Reset Password')
            );
        } elseif(! $hasToken) {

            /** @var UserInterface&UserEntity */
            $userInterface = $request->getAttribute(UserInterface::class);
            $this->form->setData(['acct-data' => ['id' => $userInterface->getDetail('id'), 'isTokenReset' => 0]]);
        }

        /** @var ViewModel */
        $model = $request->getAttribute(ModelInterface::class);
        $model->setVariable('form', $this->form);

        return new HtmlResponse($this->renderer->render(
            'user-manager::change-password',
            $model
        ));
    }

    public function handlePost(ServerRequestInterface $request): ResponseInterface
    {
        /** @var ViewModel */
        $model = $request->getAttribute(ModelInterface::class);
        $model->setVariable('form', $this->form);
        $body = $request->getParsedBody();
        /** @var ChangePasswordFieldset */
        $fieldset = $this->form->get('acct-data');
        if ((bool) $body['acct-data']['isTokenReset']) {
            $fieldset->remove('current_password');
        } else {
            $userInterface = $request->getAttribute(UserInterface::class);
            $userId        = $userInterface->getDetail('id');
            $fieldset->setOption('userId', $userId);
        }

        $this->form->setData($body);

        if ($this->form->isValid()) {
            /** @var UserEntity */
            $userEntity = $this->form->getData();
            $userEntity->offsetUnset('conf_password');
            if ($userEntity->offsetExists('isTokenReset')) {
                $userEntity->offsetUnset('isTokenReset');
            }
            if ($userEntity->offsetExists('current_password')) {
                $userEntity->offsetUnset('current_password');
            }
            $userEntity->hashPassword();
            $userEntity = $this->userRepository->save($userEntity, 'id');
        }

        return new HtmlResponse($this->renderer->render(
            'user-manager::change-password',
            $model
        ));
    }
}
