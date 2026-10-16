<?php

namespace Tests\Architecture\Support;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

final class ArchitectureRules
{
    public static function domainViolations(string $directory): array
    {
        return self::scan($directory, [
            'App\\Application\\' => 'Application',
            'App\\Infrastructure\\' => 'Infrastructure',
            'App\\Interfaces\\' => 'Interfaces',
            'App\\Models\\' => 'App Models',
            'Illuminate\\' => 'Laravel',
            'Laravel\\' => 'Laravel',
        ], true, true);
    }

    public static function applicationViolations(string $directory): array
    {
        return self::scan($directory, [
            'App\\Infrastructure\\' => 'Infrastructure',
            'App\\Interfaces\\' => 'Interfaces',
            'App\\Models\\' => 'App Models',
            'Illuminate\\' => 'Laravel',
            'Laravel\\' => 'Laravel',
        ], true, true);
    }

    public static function interfacePersistenceViolations(string $directory): array
    {
        return self::scan($directory, [
            'App\\Infrastructure\\Persistence\\' => 'Infrastructure persistence',
            'App\\Models\\' => 'App Models',
            'Illuminate\\Database\\Eloquent\\' => 'Eloquent',
        ], false, false);
    }

    public static function eloquentOutsideInfrastructureViolations(string $appDirectory): array
    {
        $violations = [];
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($appDirectory));

        foreach ($files as $file) {
            if (! $file instanceof SplFileInfo || ! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $path = $file->getPathname();

            if (str_contains($path, DIRECTORY_SEPARATOR.'Infrastructure'.DIRECTORY_SEPARATOR)) {
                continue;
            }

            $contents = file_get_contents($path);

            if ($contents !== false && preg_match(
                '/(?:Illuminate\\\\Database\\\\Eloquent\\\\|Illuminate\\\\Foundation\\\\Auth\\\\User|App\\\\Infrastructure\\\\Persistence\\\\Eloquent\\\\Models\\\\)/',
                $contents,
            ) === 1) {
                $violations[] = self::relativePath($path).': Eloquent fora da Infraestrutura';
            }
        }

        sort($violations);

        return $violations;
    }

    private static function scan(
        string $directory,
        array $forbiddenDependencies,
        bool $forbidLaravelHelpers,
        bool $forbidMixed,
    ): array {
        $violations = [];
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));

        foreach ($files as $file) {
            if (! $file instanceof SplFileInfo || ! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $contents = file_get_contents($file->getPathname());

            if ($contents === false) {
                continue;
            }

            foreach ($forbiddenDependencies as $dependency => $label) {
                if (str_contains($contents, $dependency)) {
                    $violations[] = self::relativePath($file->getPathname()).": dependencia proibida de {$label}";
                }
            }

            if ($forbidLaravelHelpers && preg_match('/(?<!->)(?<!::)\b(?:app|config|request|response|resolve)\s*\(/', $contents) === 1) {
                $violations[] = self::relativePath($file->getPathname()).': helper Laravel proibido';
            }

            if ($forbidMixed && preg_match('/\bmixed\b/', $contents) === 1) {
                $violations[] = self::relativePath($file->getPathname()).': tipo mixed proibido';
            }
        }

        sort($violations);

        return $violations;
    }

    private static function relativePath(string $path): string
    {
        $root = dirname(__DIR__, 3).DIRECTORY_SEPARATOR;

        return str_replace($root, '', $path);
    }
}
