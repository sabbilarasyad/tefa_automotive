<?php

namespace App\Services;

use App\Models\DocumentNumberCounter;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

/** Generates monthly document numbers while holding the counter row lock. */
class NumberGeneratorService
{
    public function next(string $documentType = 'PKB', ?CarbonInterface $date = null): string
    {
        $date ??= now();
        $year = (int) $date->format('Y');
        $month = (int) $date->format('m');

        return DB::transaction(function () use ($documentType, $year, $month): string {
            DB::table('document_number_counters')->insertOrIgnore([
                'document_type' => $documentType,
                'year' => $year,
                'month' => $month,
                'last_number' => 0,
            ]);

            $counter = DocumentNumberCounter::query()
                ->where('document_type', $documentType)
                ->where('year', $year)
                ->where('month', $month)
                ->lockForUpdate()
                ->firstOrFail();

            $nextNumber = $counter->last_number + 1;
            $counter->update(['last_number' => $nextNumber]);

            $prefix = $documentType === 'INVOICE' ? 'INV' : $documentType;

            return sprintf('%s/%04d/%02d/%04d', $prefix, $year, $month, $nextNumber);
        });
    }
}
