<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

final class AdminLoginRedirectSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly UrlGeneratorInterface $urlGenerator)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccess',
        ];
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        if ($event->getRequest()->attributes->get('_route') !== 'app_login') {
            return;
        }

        if (!in_array('ROLE_ADMIN', $event->getAuthenticatedToken()->getRoleNames(), true)) {
            return;
        }

        $response = $event->getResponse();
        $targetUrl = $response instanceof RedirectResponse ? $response->getTargetUrl() : '';
        $homePath = $this->urlGenerator->generate('app_home');
        $homeAbsoluteUrl = $this->urlGenerator->generate('app_home', [], UrlGeneratorInterface::ABSOLUTE_URL);

        if ($targetUrl !== '' && $targetUrl !== $homePath && $targetUrl !== $homeAbsoluteUrl) {
            return;
        }

        $event->setResponse(new RedirectResponse($this->urlGenerator->generate('app_admin_dashboard')));
    }
}
