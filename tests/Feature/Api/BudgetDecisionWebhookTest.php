<?php

namespace Tests\Feature\Api;

use DateTimeImmutable;
use Tests\TestCase;

class BudgetDecisionWebhookTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.service_order_webhook.secret' => 'local-webhook-secret',
            'services.service_order_webhook.timestamp_tolerance_seconds' => 0,
        ]);
    }

    public function test_webhook_requires_a_signature(): void
    {
        $this->postJson('/api/webhooks/service-orders/budget-decision', $this->payload())
            ->assertUnauthorized()
            ->assertJsonPath('error.code', 'invalid_webhook_signature');
    }

    public function test_webhook_rejects_an_invalid_signature(): void
    {
        $body = $this->encodedPayload();

        $this->call(
            'POST',
            '/api/webhooks/service-orders/budget-decision',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_WEBHOOK_SIGNATURE' => 'sha256='.hash_hmac('sha256', $body, 'wrong-secret'),
            ],
            content: $body,
        )
            ->assertUnauthorized()
            ->assertJsonPath('error.code', 'invalid_webhook_signature');
    }

    public function test_webhook_accepts_a_signature_calculated_from_the_raw_body(): void
    {
        $body = $this->encodedPayload();

        $this->call(
            'POST',
            '/api/webhooks/service-orders/budget-decision',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_WEBHOOK_SIGNATURE' => 'sha256='.hash_hmac('sha256', $body, 'local-webhook-secret'),
            ],
            content: $body,
        )
            ->assertStatus(202)
            ->assertJsonPath('data.accepted', true);
    }

    public function test_webhook_rejects_decisions_outside_the_contract(): void
    {
        $payload = $this->payload();
        $payload['decision'] = 'pending';
        $body = json_encode($payload, JSON_THROW_ON_ERROR);

        $this->call(
            'POST',
            '/api/webhooks/service-orders/budget-decision',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_WEBHOOK_SIGNATURE' => 'sha256='.hash_hmac('sha256', $body, 'local-webhook-secret'),
            ],
            content: $body,
        )
            ->assertUnprocessable()
            ->assertJsonPath('error.code', 'validation_error');
    }

    public function test_webhook_rejects_an_occurred_at_outside_the_configured_window(): void
    {
        config(['services.service_order_webhook.timestamp_tolerance_seconds' => 60]);
        $payload = $this->payload();
        $payload['occurred_at'] = (new DateTimeImmutable('-2 minutes'))->format(DATE_ATOM);
        $body = json_encode($payload, JSON_THROW_ON_ERROR);

        $this->call(
            'POST',
            '/api/webhooks/service-orders/budget-decision',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_WEBHOOK_SIGNATURE' => 'sha256='.hash_hmac('sha256', $body, 'local-webhook-secret'),
            ],
            content: $body,
        )
            ->assertUnprocessable()
            ->assertJsonPath('error.code', 'validation_error');
    }

    private function payload(): array
    {
        return [
            'service_order_id' => 1,
            'decision' => 'approved',
            'occurred_at' => (new DateTimeImmutable)->format(DATE_ATOM),
        ];
    }

    private function encodedPayload(): string
    {
        return json_encode($this->payload(), JSON_THROW_ON_ERROR);
    }
}
