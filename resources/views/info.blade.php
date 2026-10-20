<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Informasi Simple POS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 flex items-center justify-center p-6">
    <div class="max-w-lg rounded-lg bg-white p-8 shadow">
        <h1 class="mb-4 text-2xl font-bold">Selamat Datang di Simple POS</h1>

        <p class="mb-4 text-slate-600">
            Aplikasi Point of Sale untuk membantu pengelolaan transaksi,
            produk, dan kategori.
        </p>

        <a href="{{ route('login') }}"
           class="inline-block rounded bg-slate-900 px-4 py-2 text-white">
            Masuk ke Aplikasi
        </a>
    </div>
</body>
</html>