<?php

declare(strict_types=1);

namespace KayStrobach\Invoice\Service;

/**
 * Finds gaps and duplicates per number series (prefix + postfix)
 */
class InvoiceNumberSequenceAnalyzer
{
    /**
     * @param iterable<array{prefix: string, postfix: string, number: int|string, invoice: string}> $rows
     * @return array<string, array{prefix: string, postfix: string, count: int, highest: int, gaps: int[], duplicates: array<int, string[]>}>
     */
    public function analyze(iterable $rows): array
    {
        $series = [];
        foreach ($rows as $row) {
            $key = $row['prefix'] . "\0" . $row['postfix'];
            $series[$key] ??= ['prefix' => $row['prefix'], 'postfix' => $row['postfix'], 'numbers' => []];
            $series[$key]['numbers'][(int)$row['number']][] = (string)$row['invoice'];
        }

        $result = [];
        foreach ($series as $key => $data) {
            $numbers = $data['numbers'];
            ksort($numbers);
            $highest = (int)max(array_keys($numbers));
            $result[$key] = [
                'prefix' => $data['prefix'],
                'postfix' => $data['postfix'],
                'count' => array_sum(array_map('count', $numbers)),
                'highest' => $highest,
                'gaps' => array_values(array_diff(range(1, max(1, $highest)), array_keys($numbers))),
                'duplicates' => array_filter($numbers, static fn (array $invoices) => count($invoices) > 1),
            ];
        }
        ksort($result);
        return $result;
    }

    /**
     * [3, 4, 5, 9] → "3–5, 9"
     *
     * @param int[] $numbers
     */
    public static function compress(array $numbers): string
    {
        sort($numbers);
        $parts = [];
        $start = $previous = null;
        foreach ($numbers as $number) {
            if ($previous !== null && $number === $previous + 1) {
                $previous = $number;
                continue;
            }
            if ($start !== null) {
                $parts[] = $start === $previous ? (string)$start : $start . '–' . $previous;
            }
            $start = $previous = $number;
        }
        if ($start !== null) {
            $parts[] = $start === $previous ? (string)$start : $start . '–' . $previous;
        }
        return implode(', ', $parts);
    }
}
