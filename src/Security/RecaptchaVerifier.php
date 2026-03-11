<?php

namespace App\Security;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class RecaptchaVerifier
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        #[Autowire('%recaptcha.secret_key%')]
        private readonly string $secretKey,
    ) {
    }

    public function verify(string $token, ?string $remoteIp = null, string $expectedAction = 'login_submit', float $minimumScore = 0.5): bool
    {
        if ($token === '' || $this->secretKey === '') {
            return false;
        }

        try {
            $response = $this->httpClient->request('POST', 'https://www.google.com/recaptcha/api/siteverify', [
                'body' => [
                    'secret' => $this->secretKey,
                    'response' => $token,
                    'remoteip' => $remoteIp,
                ],
            ]);

            $payload = $response->toArray(false);
        } catch (\Throwable) {
            return false;
        }

        if (($payload['success'] ?? false) !== true) {
            return false;
        }

        $score = (float) ($payload['score'] ?? 0.0);
        $action = (string) ($payload['action'] ?? '');

        return $score >= $minimumScore && $action === $expectedAction;
    }
}
