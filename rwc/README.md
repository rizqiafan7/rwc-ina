# RWC Indonesian Operational System Monitoring Dashboard

Dashboard monitoring operasional untuk memantau kinerja semua komponen observasi meteorologi dan klimatologi di wilayah WMO Regional V dan area sekitarnya.

## Deskripsi Proyek

Sistem ini dikembangkan untuk memantau ketersediaan dan kualitas data observasi meteorologi dari berbagai stasiun di wilayah Asia Pasifik. Dashboard ini menyediakan visualisasi interaktif untuk memantau status stasiun, ketersediaan data, dan kualitas data dari berbagai pusat meteorologi global.

## Fitur Utama

- **Global Basic Observing Network (GBON)**
  - Monitoring stasiun observasi permukaan darat
  - Monitoring stasiun observasi udara atas
  
- **Monitoring NWP (Numerical Weather Prediction) secara real-time**
  - Observasi permukaan darat
  - Observasi udara atas
  - Observasi permukaan laut
  
- **Monitoring Sistem Observasi Iklim**
  - Observasi permukaan darat
  - Observasi udara atas

## Struktur Proyek

Proyek ini menggunakan arsitektur berbasis PHP dengan komponen frontend menggunakan JavaScript, HTML, dan CSS. Berikut struktur utama proyek:

- `api/` - Endpoint API untuk mengambil data dari berbagai sumber
- `assets/` - File statis seperti CSS, JavaScript, dan gambar
- `config/` - File konfigurasi dan fungsi utilitas
- `data/` - Direktori penyimpanan data
- `gbon/` - Modul untuk monitoring GBON (Global Basic Observing Network)
- `includes/` - Komponen yang dapat digunakan kembali seperti header dan footer
- `nwp/` - Modul untuk monitoring NWP (Numerical Weather Prediction)
- `pages/` - Halaman statis tambahan

## Teknologi yang Digunakan

- **Backend**: PHP
- **Frontend**: JavaScript, HTML5, CSS3, Tailwind CSS
- **Peta Interaktif**: Leaflet.js
- **Visualisasi Data**: Custom JavaScript
- **Sumber Data**: WMO WDQMS API

## Dokumentasi Tambahan

Untuk informasi lebih detail tentang setiap komponen sistem, silakan lihat dokumentasi berikut:

- [Dokumentasi API](api-documentation.md)
- [Dokumentasi Frontend](frontend-documentation.md)
- [Alur Kerja Sistem](system-workflow.md)
- [Panduan Konfigurasi](configuration-guide.md)
- [Panduan Pengembangan](development-guide.md) 