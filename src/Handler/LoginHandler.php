<?php

declare(strict_types=1);

namespace UserManager\Handler;

use UserManager\Form\Login;
use Fig\Http\Message\RequestMethodInterface as Http;
use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\Diactoros\Uri;
use Laminas\View\Model\ModelInterface;
use Mezzio\Authentication\Session\PhpSession;
use Mezzio\Authentication\UserInterface;
use Mezzio\Session\SessionInterface;
use Mezzio\Session\LazySession;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class LoginHandler implements RequestHandlerInterface
{
    private const REDIRECT_ATTRIBUTE = 'authentication:redirect';

    public function __construct(
        private TemplateRendererInterface $renderer,
        private PhpSession $adapter,
        private Login $form,
        private $config
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // if (! $request->getAttribute('enableLogin', false)) {
        //     return new RedirectResponse('/');
        // }
        /** @var LazySession */
        $session  = $request->getAttribute('session');
        $redirect = $this->getRedirect($request, $session);
        // handle supported Http Method types, proxy to the correct handler method
        return match($request->getMethod()) {
            Http::METHOD_GET    => $this->handleGet($request),
            Http::METHOD_POST   => $this->handlePost($request, $session, $redirect),
            default => new EmptyResponse(405), // defaults to a method not allowed
        };
    }

    public function handlePost(
        ServerRequestInterface $request,
        SessionInterface $session,
        string $redirect
    ): ResponseInterface {
        // remove this before we attempt to authenticate since user session takes precendence
        $session->unset(UserInterface::class);

        $this->form->setValidationGroup(['email', 'password']);
        $this->form->setData($request->getParsedBody());

        $model = $request->getAttribute(ModelInterface::class);
        $model->setVariable('form', $this->form);

        if (! $this->form->isValid()) {
            return new HtmlResponse($this->renderer->render(
                    'user-manager::login',
                    $model
            ));
        }

        if ($this->adapter->authenticate($request)) {
            $session->unset(self::REDIRECT_ATTRIBUTE);
            return new RedirectResponse($redirect);
        }
    }

    public function handleGet(ServerRequestInterface $request): ResponseInterface
    {
        $model = $request->getAttribute(ModelInterface::class);
        $model->setVariable('form', $this->form);
        return new HtmlResponse(
            $this->renderer->render(
                'user-manager::login',
                $model
            )
        );
    }

    private function getRedirect(
        ServerRequestInterface $request,
        SessionInterface $session
    ): string {
        /** @var string */
        //$redirect = $session->get(self::REDIRECT_ATTRIBUTE);
        $redirect = $this->config['redirect'];

        if (! $redirect) {
            $uri      = new Uri($request->getHeaderLine('Referer'));
            $redirect = $uri->getPath();
        }
        return $redirect;
    }
}
