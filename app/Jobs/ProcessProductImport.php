<?php

namespace App\Jobs;

use App\Imports\ProductImport;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ProcessProductImport implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public array $backoff = [10, 60];
    public int $timeout = 60;

    public function __construct(private string $csvPath)
    {
    }

    public function handle(): void
    {
        // Panggil Laravel Excel untuk mengurus semuanya
        Excel::import(new ProductImport, Storage::path($this->csvPath));

        // Hapus file dari sistem setelah selesai
        Storage::delete($this->csvPath);
    }
}