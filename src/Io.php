<?php declare(strict_types = 1);

namespace ShipMonk\PHPStan\Errors;

use function file;
use function file_put_contents;
use function stream_get_contents;
use const STDIN;

class Io
{

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
    public function writeFile(string $filePath, string $contents): void
    {
        if (file_put_contents($filePath, $contents) === false) {
            throw new FailureException('Could not write file ' . $filePath);
        }
    }

}
