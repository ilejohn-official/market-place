<?php

namespace Tests\Feature;

use Tests\TestCase;

class WebhookTest extends TestCase
{
    public function test_payment_webhook_receives_payload(): void
    {
        $response = $this->postJson('/api/payments/webhook', [
            'event' => 'charge.success',
            'data' => ['reference' => 'test_ref'],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'received');
    }
}
