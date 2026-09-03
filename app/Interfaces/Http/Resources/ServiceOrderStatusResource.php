<?php

namespace App\Interfaces\Http\Resources;

use App\Domain\ServiceOrder\Enums\ServiceOrderStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceOrderStatusResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $status = $this->resource;

        return [
            'service_order_id' => $status->serviceOrderId,
            'status' => $status->status->value,
            'status_label' => self::label($status->status),
            'last_transition_at' => $status->lastTransitionAt->format(DATE_ATOM),
        ];
    }

    private static function label(ServiceOrderStatus $status): string
    {
        return match ($status) {
            ServiceOrderStatus::Received => 'Recebida',
            ServiceOrderStatus::InDiagnosis => 'Em diagnóstico',
            ServiceOrderStatus::AwaitingApproval => 'Aguardando aprovação',
            ServiceOrderStatus::InExecution => 'Em execução',
            ServiceOrderStatus::Finalized => 'Finalizada',
            ServiceOrderStatus::Delivered => 'Entregue',
            ServiceOrderStatus::Cancelled => 'Cancelada',
        };
    }
}
