<?php

namespace Tests\Unit\Domain;

use PHPUnit\Framework\TestCase;

final class PipelineFailureProbeTest extends TestCase
{
    public function test_controlled_failure_blocks_pipeline(): void
    {
        $this->fail('Falha controlada para validar o bloqueio da CI no Dia 25.');
    }
}
