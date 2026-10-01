@extends('layouts.app')

@section('title', 'Produk')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-lg font-semibold">Daftar Produk</h1>

        <a
            href="{{ route('products.create') }}"
            class="bg-blue-600 text-white px-4 py-2 rounded-md"
        >
            Tambah Produk
        </a>
    </div>

    @if (session('status'))
        <div class="mb-4 p-3 rounded-md bg-green-100 text-green-700">
            {{ session('status') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 p-3 rounded-md bg-red-100 text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="w-full border-collapse border">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border px-3 py-2 text-left">Nama</th>
                    <th class="border px-3 py-2 text-left">Kategori</th>
                    <th class="border px-3 py-2 text-left">Harga</th>
                    <th class="border px-3 py-2 text-left">Stok</th>
                    <th class="border px-3 py-2 text-left">Status</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($products as $product)
                    <tr>
                        <td class="border px-3 py-2">
                            {{ $product->name }}
                        </td>

                        <td class="border px-3 py-2">
                            {{ $product->category?->name ?? '-' }}
                        </td>

                        <td class="border px-3 py-2">
                            Rp {{ number_format($product->price) }}
                        </td>

                        <td class="border px-3 py-2">
                            {{ $product->stock }}
                        </td>

                        <td class="border px-3 py-2">
                            {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td
                            colspan="5"
                            class="border px-3 py-4 text-center"
                        >
                            Belum ada produk.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $products->links() }}
    </div>
@endsection