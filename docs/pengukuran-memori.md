# Laporan Pengukuran Penggunaan Memori

# 1. Tujuan

Pengujian dilakukan untuk membandingkan penggunaan memori antara metode `get()` dan `lazyById()` pada Laravel ketika membaca data transaksi.

# 2. Data Pengujian

- Jumlah transaksi awal: 2.500
- Jumlah transaksi dummy yang ditambahkan: 50.000
- Total transaksi: 52.500
- Lingkungan pengujian: Laravel menggunakan PHP 8.4.12.

# 3. Hasil Pengukuran

| Metode                    | Peak Memory  |
|---------------------------|--------------|
| `Transaction::lazyById()` | 2 MB         |
| `Transaction::get()`      | 60 MB        |

Selisih penggunaan memori yang terukur adalah 58 MB. Pada pengujian ini, metode `lazyById()` menunjukkan penggunaan memori yang lebih rendah daripada metode `get()`.

# 4. Analisis

Metode `get()` mengambil seluruh data transaksi sekaligus ke dalam koleksi di memori. Sebaliknya, `lazyById()` membaca data secara bertahap sehingga tidak perlu menyimpan seluruh hasil query sekaligus.

Hasil pengujian menunjukkan bahwa `lazyById()` lebih hemat memori untuk pembacaan data transaksi dalam jumlah besar pada lingkungan pengujian ini.

# 5. Kesimpulan

Berdasarkan pengukuran terhadap 52.500 transaksi, peak memory yang tercatat adalah 2 MB untuk `lazyById()` dan 60 MB untuk `get()`. Dengan demikian, `lazyById()` lebih efisien dari sisi penggunaan memori pada pengujian ini.

Catatan: hasil pengukuran merupakan hasil pengujian lokal dan dapat berbeda bergantung pada lingkungan PHP, konfigurasi memori, serta kondisi eksekusi.