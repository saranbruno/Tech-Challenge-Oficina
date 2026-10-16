<?php

namespace App\Application\Notification;

use App\Application\Notification\Data\ServiceOrderStatusNotification;
use App\Domain\ServiceOrder\Enums\ServiceOrderStatus;

final readonly class ServiceOrderStatusNotificationFactory
{
    public function make(int $serviceOrderId, ServiceOrderStatus $status): ServiceOrderStatusNotification
    {
        $label = match ($status) {
            ServiceOrderStatus::Received => 'recebida',
            ServiceOrderStatus::InDiagnosis => 'em diagnostico',
            ServiceOrderStatus::AwaitingApproval => 'aguardando aprovacao',
            ServiceOrderStatus::InExecution => 'em execucao',
            ServiceOrderStatus::Finalized => 'finalizada',
            ServiceOrderStatus::Delivered => 'entregue',
            ServiceOrderStatus::Cancelled => 'cancelada',
        };

        return new ServiceOrderStatusNotification(
            $serviceOrderId,
            $status,
            "Atualizacao da ordem de servico #{$serviceOrderId}",
            "A ordem de servico #{$serviceOrderId} agora esta com o status: {$label}.",
        );
    }
}
