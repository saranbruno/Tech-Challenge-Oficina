<?php

namespace App\Domain\ServiceOrder;

final class ServiceOrderExecutionTimeCalculator
{
    public function calculate(array $serviceOrders): array
    {
        if ($serviceOrders === []) {
            return [
                'eligible_orders' => 0,
                'average_total_seconds' => null,
                'average_seconds_by_status' => $this->emptyStatusDurations(),
            ];
        }

        $totalDurations = [];
        $statusDurations = array_fill_keys(array_keys($this->emptyStatusDurations()), []);

        foreach ($serviceOrders as $serviceOrder) {
            $instants = [
                $serviceOrder->receivedAt,
                $serviceOrder->diagnosisStartedAt,
                $serviceOrder->awaitingApprovalAt,
                $serviceOrder->executionStartedAt,
                $serviceOrder->finalizedAt,
                $serviceOrder->deliveredAt,
            ];

            if (in_array(null, $instants, true)) {
                continue;
            }

            $totalDurations[] = $instants[5]->getTimestamp() - $instants[0]->getTimestamp();

            foreach (array_keys($statusDurations) as $index => $status) {
                $statusDurations[$status][] = $instants[$index + 1]->getTimestamp() - $instants[$index]->getTimestamp();
            }
        }

        if ($totalDurations === []) {
            return [
                'eligible_orders' => 0,
                'average_total_seconds' => null,
                'average_seconds_by_status' => $this->emptyStatusDurations(),
            ];
        }

        return [
            'eligible_orders' => count($totalDurations),
            'average_total_seconds' => $this->average($totalDurations),
            'average_seconds_by_status' => array_map($this->average(...), $statusDurations),
        ];
    }

    private function emptyStatusDurations(): array
    {
        return [
            'received' => null,
            'in_diagnosis' => null,
            'awaiting_approval' => null,
            'in_execution' => null,
            'finalized' => null,
        ];
    }

    private function average(array $durations): int
    {
        return (int) round(array_sum($durations) / count($durations));
    }
}
