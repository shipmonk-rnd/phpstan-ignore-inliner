<?php declare(strict_types = 1);

namespace ShipMonk\PHPStan\Errors;

use function explode;
use function implode;
use function rtrim;
use function str_contains;
use function strlen;
use function substr;

final class InlineIgnoreInliner
{

    public function __construct(private Io $io)
    {
    }

    /**
     * @param array<string, array{messages: array<array{line: ?int, identifier: ?string, ignorable: ?bool}>}> $errors
     * @throws FailureException
     */
    public function inlineErrors(
        array $errors
    ): void
    {
        foreach ($errors as $filePath => $fileErrors) {
            foreach ($fileErrors['messages'] as $error) {
                $line = $error['line'] ?? null;
                $identifier = $error['identifier'] ?? null;
                $ignorable = $error['ignorable'] ?? null;

                if ($line === null || $identifier === null || $ignorable === false) {
                    continue;
                }

                [$trueFilePath] = explode(' (in context of class', $filePath, 2); // solve trait "filepath" in format "src/App/MyTrait.php (in context of class App\Clazz)"

                $lines = $this->io->readFile($trueFilePath);

                $lineContent = rtrim($lines[$line - 1]);
                $lineEnding = substr($lines[$line - 1], strlen($lineContent));

                $append = str_contains($lineContent, '// @phpstan-ignore ')
                    ? ', ' . $identifier
                    : ' // @phpstan-ignore ' . $identifier;

                $lines[$line - 1] = $lineContent . $append . $lineEnding;
                $this->io->writeFile($trueFilePath, implode('', $lines));
            }
        }
    }

}
