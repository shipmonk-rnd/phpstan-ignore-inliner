<?php declare(strict_types = 1);

namespace ShipMonk\PHPStan\Errors;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use function file;
use function file_get_contents;
use function file_put_contents;
use function json_decode;
use function str_replace;
use function sys_get_temp_dir;
use function uniqid;

class InlineIgnoreInlinerTest extends TestCase
{

    #[DataProvider('lineEndingProvider')]
    public function testInlineErrors(string $lineEnding): void
    {
        $tmpFilePath = sys_get_temp_dir() . '/' . uniqid('ignore', true) . '.php';
        $tmpExpectedPath = sys_get_temp_dir() . '/' . uniqid('ignore-expected', true) . '.php';

        $testContent = $this->getTestFileContent('test.php', $lineEnding);
        $expectedContent = $this->getTestFileContent('test.fixed.php', $lineEnding);

        self::assertNotFalse(file_put_contents($tmpFilePath, $testContent));
        self::assertNotFalse(file_put_contents($tmpExpectedPath, $expectedContent));

        $ioMock = $this->createMock(Io::class);
        $ioMock->expects(self::exactly(2))
            ->method('writeFile')
            ->willReturnCallback(static function (string $filePath, string $contents) use ($tmpFilePath): void {
                self::assertNotFalse(file_put_contents($tmpFilePath, $contents));
            });
        $ioMock->expects(self::exactly(2))
            ->method('readFile')
            ->willReturnCallback(static function (string $filePath) use ($tmpFilePath): array|false {
                return file($tmpFilePath);
            });

        $testJson = file_get_contents(__DIR__ . '/data/errors.json');
        $testData = json_decode($testJson, associative: true)['files']; // @phpstan-ignore argument.type

        $inliner = new InlineIgnoreInliner($ioMock);
        $inliner->inlineErrors($testData);

        self::assertFileEquals($tmpExpectedPath, $tmpFilePath);
    }

    private function getTestFileContent(string $filename, string $lineEnding): string
    {
        $content = file_get_contents(__DIR__ . '/data/' . $filename);
        self::assertNotFalse($content);

        return str_replace("\n", $lineEnding, $content);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function lineEndingProvider(): array
    {
        return [
            'Unix line endings' => ["\n"],
            'Windows line endings' => ["\r\n"],
        ];
    }

}
