<?php declare(strict_types = 1);

namespace ShipMonk\PHPStan\Errors;

use function file_get_contents;
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
     * @throws FailureException
     */
    public function readFile(string $filePath): string
    {
        $contents = file_get_contents($filePath);

        if ($contents === false) {
            throw new FailureException('Could not read file ' . $filePath);
        }

        return $contents;
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
