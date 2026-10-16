<?php

namespace App\Domain\ServiceOrder;

final class ServiceOrderExecutionTimeCalculator
{
    public function calculate(array $serviceOrders): ServiceOrderExecutionTimeMetrics
    {
        if ($serviceOrders === []) {
            return $this->emptyMetrics();
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
            return $this->emptyMetrics();
        }

        return new ServiceOrderExecutionTimeMetrics(
            count($totalDurations),
            $this->average($totalDurations),
            array_map($this->average(...), $statusDurations),
        );
    }

    private function emptyMetrics(): ServiceOrderExecutionTimeMetrics
    {
        return new ServiceOrderExecutionTimeMetrics(0, null, $this->emptyStatusDurations());
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
