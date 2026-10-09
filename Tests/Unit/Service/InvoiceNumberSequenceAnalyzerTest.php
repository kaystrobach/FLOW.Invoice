<?php
namespace KayStrobach\Invoice\Tests\Unit\Service;

use KayStrobach\Invoice\Service\InvoiceNumberSequenceAnalyzer;

class InvoiceNumberSequenceAnalyzerTest extends \Neos\Flow\Tests\UnitTestCase
{
    private static function row(string $prefix, int $number, string $invoice, string $postfix = ''): array
    {
        return ['prefix' => $prefix, 'postfix' => $postfix, 'number' => $number, 'invoice' => $invoice];
    }

    /**
     * @test
     */
    public function findsGapsAndDuplicatesPerSeries(): void
    {
        $result = (new InvoiceNumberSequenceAnalyzer())->analyze([
            self::row('R-2025-', 1, 'a'),
            self::row('R-2025-', 2, 'b'),
            self::row('R-2025-', 5, 'c'),
            self::row('R-2025-', 5, 'd'),
            self::row('R-2026-', 1, 'e'),
            self::row('R-2026-', 2, 'f'),
            self::row('R-2026-', 1, 'g', '-S'),
        ]);

        $r2025 = $result["R-2025-\0"];
        self::assertSame(4, $r2025['count']);
        self::assertSame(5, $r2025['highest']);
        self::assertSame([3, 4], $r2025['gaps']);
        self::assertSame([5 => ['c', 'd']], $r2025['duplicates']);

        self::assertSame([], $result["R-2026-\0"]['gaps']);
        self::assertSame([], $result["R-2026-\0"]['duplicates']);
        self::assertSame(1, $result["R-2026-\0-S"]['count']);
    }

    /**
     * @test
     */
    public function compressesRanges(): void
    {
        self::assertSame('3–5, 9, 11–12', InvoiceNumberSequenceAnalyzer::compress([9, 3, 4, 5, 12, 11]));
        self::assertSame('', InvoiceNumberSequenceAnalyzer::compress([]));
    }
}
