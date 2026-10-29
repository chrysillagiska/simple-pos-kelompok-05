<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\TransactionDetail;
use App\Exports\SalesReportExport;
use App\Jobs\ProcessProductImport;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        [$from, $to] = $this->resolveDateRange($request);
        $categoryId = $this->selectedCategoryId($request);

        $query = $this->filteredQuery($from, $to, $categoryId);

        return view('reports.index', [
            'totalPenjualan' => $this->categoryTotal($from, $to, $categoryId),
            'jumlahTransaksi' => (clone $query)->count(),
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'categories' => Category::orderBy('name')->get(),
            'categoryId' => $categoryId,
        ]);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        [$from, $to] = $this->resolveDateRange($request);

        $categoryId = $this->selectedCategoryId($request);

        $query = $this->filteredQuery($from, $to, $categoryId);

        return response()->streamDownload(function () use ($query, $categoryId) {
            $out = fopen('php://output', 'w');

            // Header CSV
            fputcsv($out, ['ID', 'Tanggal', 'Kasir', 'Total']);

            // Membaca transaksi secara bertahap
            foreach ($query->lazyById() as $transaction) {

                if ($categoryId === null) {
                    $total = $transaction->total;
                } else {
                    $total = $transaction->details()
                        ->whereHas('product', function (Builder $productQuery) use ($categoryId) {
                            $productQuery->where('category_id', $categoryId);
                        })
                        ->sum('subtotal');
                }

                fputcsv($out, [
                    $transaction->id,
                    $transaction->created_at->format('Y-m-d H:i:s'),
                    $transaction->user?->name,
                    $total,
                ]);
            }

            fclose($out);
        }, 'laporan-penjualan.csv');
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        [$from, $to] = $this->resolveDateRange($request);

        $categoryId = $this->selectedCategoryId($request);

        return Excel::download(
            new SalesReportExport(
                $this->filteredQuery($from, $to, $categoryId),
                $categoryId
            ),
            'laporan-penjualan.xlsx',
        );
    }

    public function exportPdf(Request $request): Response
    {
        [$from, $to] = $this->resolveDateRange($request);
        $categoryId = $this->selectedCategoryId($request);

        $query = $this->filteredQuery($from, $to, $categoryId);

        $transactions = (clone $query)
            ->latest()
            ->limit(50)
            ->get();

        if ($categoryId !== null) {
            foreach ($transactions as $transaction) {
                $transaction->total = $transaction->details()
                    ->whereHas('product', fn (Builder $q) =>
                        $q->where('category_id', $categoryId)
                    )
                    ->sum('subtotal');
            }
        }

        $pdf = Pdf::loadView('reports.pdf', [
            'totalPenjualan' => $this->categoryTotal($from, $to, $categoryId),
            'jumlahTransaksi' => (clone $query)->count(),
            'transactions' => $transactions,
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'categoryId' => $categoryId,
            'categoryName' => $categoryId === null
                ? 'Semua Kategori'
                : Category::find($categoryId)?->name,
        ]);

        return $pdf->download('laporan-penjualan.pdf');
    }

    public function importForm(): View
    {
        return view('reports.import');
    }

    public function import(Request $request): RedirectResponse
        {
            $request->validate([
            'csv' => ['required', 'file', 'mimes:csv,txt'],
            ]);

            $path = $request->file('csv')->store('imports');

            ProcessProductImport::dispatch($path);

            return back()->with('success', 'Import sedang diproses di background.');
        }

    private function resolveDateRange(Request $request): array
    {
        $from = $request->date('from')?->startOfDay() ?? now()->startOfMonth();
        $to = $request->date('to')?->endOfDay() ?? now()->endOfDay();
        return [$from, $to];
    }
    
    private function selectedCategoryId(Request $request): ?int
    {
        $validated = $request->validate([
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
        ]);

        return isset($validated['category_id']) ? (int) $validated['category_id']: null;
    }

    private function filteredQuery($from, $to, ?int $categoryId = null): Builder
    {
        return Transaction::with('user')->whereBetween('created_at', [$from, $to])->when($categoryId !== null, function (Builder $query) use ($categoryId) {
                $query->whereHas('details.product', function (Builder $productQuery) use ($categoryId) {$productQuery->where('category_id', $categoryId);
                });
            });
    }

    private function categoryTotal($from, $to, ?int $categoryId): int|float
    {
        if ($categoryId === null) {
            return $this->filteredQuery($from, $to)->sum('total');
        }

        return TransactionDetail::query()
            ->whereHas('transaction', function (Builder $query) use ($from, $to) {
                $query->whereBetween('created_at', [$from, $to]);
            })
            ->whereHas('product', function (Builder $query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->sum('subtotal');
    }
}