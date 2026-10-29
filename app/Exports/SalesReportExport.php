<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SalesReportExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(
        private Builder $query,
        private ?int $categoryId = null
    ) {}

    public function query(): Builder
    {
        return $this->query->with([
            'user',
            'details' => function ($query) {
                if ($this->categoryId !== null) {
                    $query->whereHas('product', function ($productQuery) {
                        $productQuery->where('category_id', $this->categoryId);
                    });
                }
            },
        ]);
    }

    public function headings(): array
    {
        return ['ID', 'Tanggal', 'Kasir', 'Total'];
    }

    public function map($transaction): array
    {
        $total = $this->categoryId === null
            ? $transaction->total
            : $transaction->details->sum('subtotal');

        return [
            $transaction->id,
            $transaction->created_at->format('Y-m-d H:i:s'),
            $transaction->user?->name,
            $total,
        ];
    }
}
