<?php declare(strict_types = 1);

namespace ShipMonk\PHPStan\Errors;

use PHPUnit\Framework\TestCase;
use function fclose;
use function fwrite;
use function is_array;
use function is_resource;
use function proc_close;
use function proc_open;
use function stream_get_contents;

class IoTest extends TestCase
{

    /**
     * @param list<string> $args
     *
     * @dataProvider optionsProvider
     */
    public function testCliOptions(
        int $exitCode,
        string $input,
        array $args,
        string $expectedOutput
    ): void
    {
        $result = $this->runCliCommand($args, $input);
        self::assertSame($exitCode, $result['exitCode']);
        self::assertStringContainsString($expectedOutput, $result['stdout']);
    }

    /**
     * @return array<string, array{int, string, array<string>, string}>
     */
    public static function optionsProvider(): array
    {
        $validInput = '{"files": {}}';

        return [
            'no input' => [1, '', [], 'ERROR: Nothing found on input'],
            'invalid json' => [1, 'invalid json', [], 'ERROR: Syntax error'],

            'no options' => [0, $validInput, [], 'Done, 0 errors processed'],
            'with comment' => [0, $validInput, ['--comment=test comment'], 'Done, 0 errors processed'],
            'with single word comment' => [0, $validInput, ['--comment=test'], 'Done, 0 errors processed'],

            'unexpected option' => [1, $validInput, ['--invalid'], 'ERROR: Unexpected option: --invalid'],
            'comment without value' => [1, $validInput, ['--comment'], 'ERROR: Missing comment value for --comment option'],
            'multiple comments' => [1, $validInput, ['--comment=first', '--comment=second'], 'ERROR: Only one comment can be provided'],
        ];
    }

    /**
     * @param list<string> $args
     * @return array{exitCode: int, stdout: string, stderr: string}
     */
    private function runCliCommand(
        array $args,
        string $input
    ): array
    {
        $binaryPath = __DIR__ . '/../bin/inline-phpstan-ignores';
        $command = ['php', $binaryPath, ...$args];

        $process = proc_open(
            $command,
            [
                0 => ['pipe', 'r'], // stdin
                1 => ['pipe', 'w'], // stdout
                2 => ['pipe', 'w'], // stderr
            ],
            $pipes,
        );

        if (!is_resource($process)) {
            self::fail('Failed to start process');
        }

        if (!is_array($pipes) || !isset($pipes[0], $pipes[1], $pipes[2])) {
            self::fail('Failed to create pipes');
        }

        /** @var array{0: resource, 1: resource, 2: resource} $pipes */
        fwrite($pipes[0], $input);
        fclose($pipes[0]);

        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);

        self::assertNotFalse($stdout, 'Failed to read stdout');
        self::assertNotFalse($stderr, 'Failed to read stderr');

        return [
            'exitCode' => $exitCode,
            'stdout' => $stdout,
            'stderr' => $stderr,
        ];
    }

}
