<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;
use Tests\Architecture\Support\ArchitectureRules;

final class LayerDependencyTest extends TestCase
{
    public function test_domain_has_no_outward_dependencies_laravel_helpers_or_mixed_types(): void
    {
        self::assertSame([], ArchitectureRules::domainViolations($this->appPath('Domain')));
    }

    public function test_application_has_no_infrastructure_interface_laravel_or_mixed_dependencies(): void
    {
        self::assertSame([], ArchitectureRules::applicationViolations($this->appPath('Application')));
    }

    public function test_interface_does_not_depend_on_eloquent_persistence(): void
    {
        self::assertSame([], ArchitectureRules::interfacePersistenceViolations($this->appPath('Interfaces')));
    }

    public function test_eloquent_exists_only_in_infrastructure(): void
    {
        self::assertSame([], ArchitectureRules::eloquentOutsideInfrastructureViolations($this->appPath('')));
    }

    public function test_controlled_fixture_is_rejected_by_application_rules(): void
    {
        $violations = ArchitectureRules::applicationViolations(__DIR__.'/Fixtures');

        self::assertCount(3, $violations);
        self::assertStringContainsString('Infrastructure', implode("\n", $violations));
        self::assertStringContainsString('helper Laravel', implode("\n", $violations));
        self::assertStringContainsString('mixed', implode("\n", $violations));
    }

    private function appPath(string $layer): string
    {
        return dirname(__DIR__, 2).'/app/'.$layer;
    }
}
