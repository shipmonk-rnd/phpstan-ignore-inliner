<?php declare(strict_types = 1);

namespace ShipMonk\PHPStan\Errors;

use PHPUnit\Framework\TestCase;
use function file_get_contents;
use function file_put_contents;
use function json_decode;
use function sys_get_temp_dir;
use function uniqid;

class InlineIgnoreInlinerTest extends TestCase
{

    public function testInlineErrors(): void
    {
        $tmpFilePath = sys_get_temp_dir() . '/' . uniqid('ignore', true) . '.php';
        $testedFileContents = file_get_contents(__DIR__ . '/data/test.php');
        self::assertNotFalse($testedFileContents);
        self::assertNotFalse(file_put_contents($tmpFilePath, $testedFileContents));

        $ioMock = $this->createMock(Io::class);
        $ioMock->expects(self::exactly(2))
            ->method('writeFile')
            ->willReturnCallback(static function (string $filePath, string $contents) use ($tmpFilePath): void {
                self::assertNotFalse(file_put_contents($tmpFilePath, $contents));
            });
        $ioMock->expects(self::exactly(2))
            ->method('readFile')
            ->willReturnCallback(static function (string $filePath) use ($tmpFilePath): string|false {
                return file_get_contents($tmpFilePath);
            });

        $testJson = file_get_contents(__DIR__ . '/data/errors.json');
        $testData = json_decode($testJson, associative: true)['files']; // @phpstan-ignore argument.type

        $inliner = new InlineIgnoreInliner($ioMock);
        $inliner->inlineErrors($testData);

        self::assertFileEquals(__DIR__ . '/data/test.fixed.php', $tmpFilePath);
    }

}
