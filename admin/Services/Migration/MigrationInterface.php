<?php

namespace Admin\Services\Migration;

interface MigrationInterface
{
    public function getPanelName(): string;
    public function getPanelIcon(): string;
    public function getDefaultPort(): int;
    public function getSupportedSourceTypes(): array;
    public function getSupportedMigrationTypes(): array;

    public function testConnection(string $host, int $port, string $username, string $password, ?string $apiKey = null): array;
    public function preflight(string $host, int $port, string $username, string $password, ?string $apiKey = null): array;
    public function analyzeAccounts(array $accounts, array $options = []): array;
    public function migrate(array $accounts, array $packageMap, array $options, callable $logFn): array;
    public function rollback(array $rollbackData, callable $logFn): bool;
    public function validateMigration(array $migratedIds, array $options = []): array;
    public function getConversionRules(): array;
}
