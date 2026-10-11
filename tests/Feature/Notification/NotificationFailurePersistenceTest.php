<?php

namespace Tests\Feature\Notification;

use App\Application\Notification\DispatchServiceOrderStatusNotification;
use App\Application\Notification\ServiceOrderStatusNotificationFactory;
use App\Domain\Customer\ValueObjects\Phone;
use App\Domain\ServiceOrder\Enums\ServiceOrderStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\Notification\FakeEmailNotificationSender;
use Tests\Support\Notification\FakeNotificationFailureReporter;
use Tests\Support\Notification\FakeSmsNotificationSender;
use Tests\TestCase;

final class NotificationFailurePersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_sms_failure_does_not_change_the_persisted_status(): void
    {
        $customerId = DB::table('customers')->insertGetId([
            'name' => 'Cliente com SMS',
            'document' => '52998224725',
            'document_type' => 'cpf',
            'phone' => '+5511999999999',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $vehicleId = DB::table('vehicles')->insertGetId([
            'customer_id' => $customerId,
            'license_plate' => 'ABC1D23',
            'brand' => 'Marca',
            'model' => 'Modelo',
            'year' => 2020,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $serviceOrderId = DB::table('service_orders')->insertGetId([
            'customer_id' => $customerId,
            'vehicle_id' => $vehicleId,
            'status' => ServiceOrderStatus::InExecution->value,
            'received_at' => now()->subHour(),
            'execution_started_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $sms = new FakeSmsNotificationSender(true);
        $failures = new FakeNotificationFailureReporter;
        $dispatcher = new DispatchServiceOrderStatusNotification(
            new FakeEmailNotificationSender,
            $sms,
            $failures,
            new ServiceOrderStatusNotificationFactory,
        );

        $dispatcher->execute(
            $serviceOrderId,
            ServiceOrderStatus::InExecution,
            null,
            new Phone('+5511999999999'),
        );

        self::assertSame([], $sms->deliveries);
        self::assertCount(1, $failures->failures);
        $this->assertDatabaseHas('service_orders', [
            'id' => $serviceOrderId,
            'status' => ServiceOrderStatus::InExecution->value,
        ]);
    }
}
