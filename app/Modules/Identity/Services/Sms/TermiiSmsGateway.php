<?php

namespace App\Modules\Identity\Services\Sms;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Termii, using the DND route so OTPs reach numbers on Do-Not-Disturb.
 * Check the endpoint and payload against Termii's current docs before going live.
 */
class TermiiSmsGateway implements SmsGateway
{
    /**
     * @param  array{base_url: string, api_key: ?string, sender_id: string, channel: string}  $config
     */
    public function __construct(private readonly array $config) {}

    public function send(string $toE164, string $message): void
    {
        if (empty($this->config['api_key'])) {
            throw new RuntimeException('TERMII_API_KEY is not set.');
        }

        Http::baseUrl($this->config['base_url'])
            ->timeout(10)
            ->retry(2, 500, throw: false)
            ->post('/api/sms/send', [
                'api_key' => $this->config['api_key'],
                'to' => ltrim($toE164, '+'),
                'from' => $this->config['sender_id'],
                'sms' => $message,
                'type' => 'plain',
                'channel' => $this->config['channel'],
            ])
            ->throw();
    }
}
