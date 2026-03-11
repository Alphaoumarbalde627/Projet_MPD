<?php

namespace App\EventSubscriber;

use App\Security\RecaptchaVerifier;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;

final class LoginRecaptchaSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly RecaptchaVerifier $recaptchaVerifier,
        private readonly RequestStack $requestStack,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CheckPassportEvent::class => 'onCheckPassport',
        ];
    }

    public function onCheckPassport(CheckPassportEvent $event): void
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request === null) {
            return;
        }

        if ($request->attributes->get('_route') !== 'app_login' || !$request->isMethod('POST')) {
            return;
        }

        // Restrict check to form login flow carrying password credentials.
        if (!$event->getPassport()->hasBadge(PasswordCredentials::class)) {
            return;
        }

        $token = (string) $request->request->get('recaptcha_token', '');

        if (!$this->recaptchaVerifier->verify($token, $request->getClientIp())) {
            throw new CustomUserMessageAuthenticationException('La verification reCAPTCHA a echoue. Veuillez reessayer.');
        }
    }
}
