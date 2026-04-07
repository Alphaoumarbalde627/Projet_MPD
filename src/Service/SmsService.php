<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class SmsService
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $orangeClientId,
        private string $orangeClientSecret,
        private string $orangeSender,
        private string $orangeRecipientAdmin,
        private readonly \Psr\Log\LoggerInterface $logger,
    ) {
    }

    public function sendOrderNotification(string $orderId, string $customerName): void
    {
        $message = "Nouvelle commande #$orderId passée par $customerName. Veuillez vous connecter pour valider.";

        $this->sendSms($this->orangeRecipientAdmin, $message);
    }

    private function sendSms(string $recipient, string $message): void
    {
        $accessToken = $this->getAccessToken();

        $senderAddress = $this->normalizeAddress($this->orangeSender, true);
        $recipientAddress = $this->normalizeAddress($recipient);

        $this->logger->info('Sending SMS', [
            'sender' => $senderAddress,
            'recipient' => $recipientAddress,
            'message' => $message,
        ]);

        $response = $this->httpClient->request('POST', 'https://api.orange.com/smsmessaging/v1/outbound/' . $senderAddress . '/requests', [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'outboundSMSMessageRequest' => [
                    'address' => $recipientAddress,
                    'senderAddress' => $senderAddress,
                    'outboundSMSTextMessage' => [
                        'message' => $message,
                    ],
                ],
            ],
        ]);

        $statusCode = $response->getStatusCode();
        $content = $response->getContent(false);

        $this->logger->info('SMS response', [
            'status_code' => $statusCode,
            'content' => $content,
        ]);

        if ($statusCode !== 201) {
            throw new \Exception('Failed to send SMS: ' . $content);
        }
    }

    private function getAccessToken(): string
    {
        $response = $this->httpClient->request('POST', 'https://api.orange.com/oauth/v3/token', [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode($this->orangeClientId . ':' . $this->orangeClientSecret),
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'body' => 'grant_type=client_credentials',
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new \Exception('Failed to get access token: ' . $response->getContent(false));
        }

        $data = $response->toArray();
        return $data['access_token'];
    }

    private function normalizeAddress(string $address, bool $allowShortCode = false): string
    {
        if (str_starts_with($address, 'tel:')) {
            return $address;
        }

        $address = trim($address);
        $numeric = preg_replace('/[^0-9+]/', '', $address);

        if ($allowShortCode && !preg_match('/^\+?\d+$/', $address)) {
            return 'tel:' . $address;
        }

        $normalized = ltrim($numeric, '+');
        return 'tel:+' . $normalized;
    }
}