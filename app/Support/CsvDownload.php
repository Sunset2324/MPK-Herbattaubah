<?php

namespace App\Support;

use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvDownload
{
    /** @param iterable<array<int,mixed>> $rows */
    public static function make(string $filename, array $header, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($header, $rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, $header);
            foreach ($rows as $r) {
                fputcsv($out, array_values($r));
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=utf-8']);
    }
}
