
# SOP-Indomaret

## Deskripsi Proyek
Proyek ini adalah dokumentasi Standard Operating Procedure (SOP) untuk sistem manajemen Indomaret. Proyek ini dirancang untuk memberikan panduan lengkap dalam implementasi dan penggunaan sistem.

## Implementasi

### Prasyarat
- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- XAMPP atau web server lokal

### Langkah Instalasi
1. Clone atau download proyek ke folder `htdocs`
2. Buat database MySQL dengan nama `indomaret`
3. Import file SQL yang tersedia:
  ```sql
  mysql -u root < indomaret.sql
  ```
4. Konfigurasi koneksi database di file konfigurasi proyek
5. Jalankan proyek melalui `http://localhost/SOP-Indomaret`

### Database
Database `indomaret` akan digunakan di MySQL untuk menyimpan semua data operasional sistem.

## Fitur Utama
- Manajemen inventori toko
- Laporan penjualan real-time
- Manajemen inventori toko
- Kelola data karyawan
- Monitoring stok barang

## Clone dari GitHub
```bash
git clone https://github.com/erza-503/SOP-Indomaret.git
cd SOP-Indomaret
```
