<?php

use Symfony\Component\Process\Process;

function exerciseContainerStartup(bool $migrate, bool $failMigration = false): array
{
    $directory = sys_get_temp_dir().'/betterbloq-startup-'.bin2hex(random_bytes(8));
    mkdir($directory);
    $log = $directory.'/commands.log';
    file_put_contents($directory.'/php', <<<'SH'
#!/bin/sh
printf '%s\n' "$*" >> "$STARTUP_TEST_LOG"
if [ "$STARTUP_TEST_FAIL_MIGRATION" = "true" ] && [ "$2" = "migrate" ]; then
    exit 1
fi
SH);
    chmod($directory.'/php', 0755);
    $process = new Process(['sh', base_path('deployment/start-container')], base_path(), [
        'PATH' => $directory.':'.getenv('PATH'),
        'STARTUP_TEST_LOG' => $log,
        'STARTUP_TEST_FAIL_MIGRATION' => $failMigration ? 'true' : 'false',
        'CONTAINER_MODE' => 'http',
        'OCTANE_SERVER' => 'not-a-real-server',
        'RUNNING_MIGRATIONS' => $migrate ? 'true' : 'false',
    ]);
    $process->run();
    $commands = file_exists($log) ? file($log, FILE_IGNORE_NEW_LINES) : [];
    @unlink($log);
    unlink($directory.'/php');
    rmdir($directory);

    return [$process->getExitCode(), $commands];
}

it('does not migrate or seed implicitly when a container starts', function () {
    [, $commands] = exerciseContainerStartup(false);
    expect(collect($commands)->contains(fn ($command) => str_contains($command, 'migrate') || str_contains($command, 'db:seed')))->toBeFalse()
        ->and($commands)->toContain('artisan storage:link');
});

it('fails startup when an explicitly requested isolated migration fails', function () {
    [$exitCode, $commands] = exerciseContainerStartup(true, true);
    expect($exitCode)->not->toBe(0)
        ->and($commands)->toBe(['artisan migrate --isolated --force']);
});
