<?php declare(strict_types = 1);

namespace ShipMonk\PHPStan\Errors;

use function array_slice;
use function file;
use function file_put_contents;
use function getopt;
use function in_array;
use function is_array;
use function is_string;
use function str_starts_with;
use function stream_get_contents;
use const STDIN;

class Io
{

    /**
     * @param array<string> $argv
     *
     * @throws FailureException
     */
    public function readCliComment(array $argv): ?string
    {
        foreach (array_slice($argv, 1) as $arg) {
            if (str_starts_with($arg, '--') && !str_starts_with($arg, '--comment')) {
                throw new FailureException('Unexpected option: ' . $arg);
            }
        }

        $options = getopt('', ['comment:']);
        $comment = $options['comment'] ?? null;

        if (is_string($comment)) {
            return $comment;
        }

        if (is_array($comment)) {
            throw new FailureException('Only one comment can be provided.');
        }

        if (in_array('--comment', $argv, true)) {
            throw new FailureException('Missing comment value for --comment option.');
        }

        return null;
    }

    /**
     * @throws FailureException
     */
    public function readInput(): string
    {
        $stdInput = stream_get_contents(STDIN);

        if ($stdInput === '') {
            throw new FailureException('Nothing found on input.');
        }

        if ($stdInput === false) {
            throw new FailureException('Could not read from input.');
        }

        return $stdInput;
    }

    /**
     * @return list<string>
     *
     * @throws FailureException
     */
    public function readFile(string $filePath): array
    {
        $lines = file($filePath);

        if ($lines === false) {
            throw new FailureException('Could not read file ' . $filePath);
        }

        return $lines;
    }

    /**
     * @throws FailureException
     */
    public function writeFile(
        string $filePath,
        string $contents,
    ): void
    {
        if (file_put_contents($filePath, $contents) === false) {
            throw new FailureException('Could not write file ' . $filePath);
        }
    }

}
