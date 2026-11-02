
@extends('layouts.app')

@section('title', 'Import Produk')

@section('content')
    <h1 class="text-lg font-semibold mb-4">Import Produk dari CSV</h1>

    @if (session('success'))
        <div class="bg-green-50 text-green-700 p-3 rounded-md mb-4">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('import.store') }}"
        enctype="multipart/form-data" class="max-w-md">
        @csrf

        <label class="block mb-3">
            <span class="text-sm font-medium">File CSV</span>
            <input type="file" name="csv" accept=".csv"
                class="mt-1 block w-full text-sm">

            @error('csv')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </label>

        <p class="text-xs text-slate-500 mb-3">
            Kolom yang dibutuhkan:
            <code>name</code>, <code>category_id</code>, <code>price</code>.
        </p>

        <button type="submit"
            class="bg-slate-900 text-white px-4 py-2 rounded-md text-sm">
            Import
        </button>
    </form>

    <div class="mt-8">
        <h2 class="text-lg font-semibold mb-3">Hasil Import yang Gagal</h2>

        @if ($importErrors->isEmpty())
            <p class="text-sm text-slate-500">
                Belum ada row yang gagal diimpor.
            </p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm border-collapse">
                    <thead>
                        <tr class="bg-slate-100 text-left">
                            <th class="border p-3">Nomor Row</th>
                            <th class="border p-3">Pesan Error</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($importErrors as $error)
                            <tr>
                                <td class="border p-3">
                                    {{ $error->row_number }}
                                </td>
                                <td class="border p-3 text-red-600">
                                    {{ $error->message }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
