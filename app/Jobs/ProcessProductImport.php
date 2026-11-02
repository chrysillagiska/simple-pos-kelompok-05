<?php

namespace App\Jobs;

use App\Models\ImportError;
use App\Models\Product;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProcessProductImport implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public array $backoff = [10, 60];
    public int $timeout = 60;

    public function __construct(private string $csvPath) {}

    public function handle(): void
    {
        $lines = file(
            Storage::path($this->csvPath),
            FILE_IGNORE_NEW_LINES
        );

        $rows = array_map('str_getcsv', $lines);
        $header = array_shift($rows);

        // Hapus error dari import sebelumnya agar yang tampil
        // hanya error dari import terbaru.
        ImportError::query()->delete();

        foreach ($rows as $index => $row) {
            // Header berada di baris pertama CSV.
            $rowNumber = $index + 2;

            // Lewati baris kosong.
            if (count($row) === 1 && trim($row[0]) === '') {
                continue;
            }

            // Simpan error jika jumlah kolom tidak sesuai.
            if (count($row) !== count($header)) {
                ImportError::create([
                    'row_number' => $rowNumber,
                    'message' => 'Jumlah kolom tidak sesuai dengan header CSV.',
                ]);

                continue;
            }

            $validator = Validator::make(
                array_combine($header, $row),
                [
                    'name' => ['required', 'string'],
                    'category_id' => ['required', 'exists:categories,id'],
                    'price' => ['required', 'integer', 'min:0'],
                ]
            );

            // Simpan pesan validasi untuk row yang gagal.
            if ($validator->fails()) {
                ImportError::create([
                    'row_number' => $rowNumber,
                    'message' => implode(
                        ' ',
                        $validator->errors()->all()
                    ),
                ]);

                continue;
            }

            $data = $validator->validated();

            Product::updateOrCreate(
                [
                    'name' => $data['name'],
                    'category_id' => $data['category_id'],
                ],
                [
                    'price' => $data['price'],
                ],
            );
        }

        Storage::delete($this->csvPath);
    }
}
