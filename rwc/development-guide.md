# Panduan Pengembangan

Dokumen ini menjelaskan cara mengembangkan dan memperluas RWC Indonesian Operational System Monitoring Dashboard.

## Lingkungan Pengembangan

### Persyaratan

- PHP 7.4 atau lebih baru
- Server web (Apache/Nginx)
- Git (untuk manajemen versi)
- Editor kode (VSCode, PHPStorm, dll)
- Browser modern dengan DevTools

### Menyiapkan Lingkungan Lokal

1. **Clone repositori**
   ```bash
   git clone [URL_REPOSITORI]
   cd [NAMA_FOLDER]
   ```

2. **Konfigurasi server web lokal**
   - Arahkan document root ke direktori proyek
   - Pastikan PHP dapat mengakses dan menulis ke direktori `logs/` dan `data/`

3. **Konfigurasi pengembangan**
   - Buka `config/config.php` dan pastikan `$isDev = true`
   - Sesuaikan `SITE_URL` jika diperlukan untuk lingkungan lokal

## Struktur Kode

### Backend (PHP)

```
api/                 - Endpoint API
config/              - File konfigurasi
  ├── config.php     - Konfigurasi utama
  └── functions/     - Fungsi utilitas
gbon/                - Modul GBON
includes/            - Template yang dapat digunakan kembali
logs/                - File log
```

### Frontend (JavaScript/CSS)

```
assets/
  ├── css/           - File CSS
  ├── images/        - Gambar dan ikon
  └── js/            - File JavaScript
      ├── config.js  - Konfigurasi frontend
      └── ...        - File JavaScript lainnya
```

## Konvensi Kode

### PHP

- Gunakan camelCase untuk nama fungsi dan variabel
- Gunakan UPPERCASE untuk konstanta
- Gunakan indentasi 4 spasi
- Selalu validasi input pengguna
- Gunakan try-catch untuk penanganan error

### JavaScript

- Gunakan camelCase untuk nama fungsi dan variabel
- Gunakan konstanta untuk nilai yang tidak berubah
- Kelompokkan fungsi terkait bersama
- Dokumentasikan fungsi dengan komentar
- Gunakan try-catch untuk penanganan error

### CSS

- Gunakan kebab-case untuk nama kelas
- Organisasikan CSS berdasarkan komponen
- Gunakan variabel CSS untuk nilai yang digunakan kembali
- Ikuti pendekatan mobile-first untuk responsivitas

## Alur Kerja Pengembangan

### 1. Menambahkan Fitur Baru

#### Menambahkan Endpoint API Baru

1. **Buat file PHP baru di direktori `api/`**
   ```php
   <?php
   // api/new_feature.php
   
   // Konfigurasi header
   header('Content-Type: application/json');
   header('Access-Control-Allow-Origin: *');
   header('Access-Control-Allow-Methods: GET');
   header('Access-Control-Allow-Headers: Content-Type');
   
   // Konfigurasi log
   $log_file = __DIR__ . '/../logs/new_feature.log';
   
   try {
       // Validasi parameter
       $param = isset($_GET['param']) ? $_GET['param'] : null;
       if (!$param) {
           throw new Exception('Parameter required');
       }
       
       // Logika bisnis
       $data = [/* data yang akan dikembalikan */];
       
       // Kembalikan respons
       echo json_encode([
           'status' => 'success',
           'data' => $data
       ]);
   } catch (Exception $e) {
       // Log error
       $error_message = date('Y-m-d H:i:s') . " - Error: " . $e->getMessage() . "\n";
       file_put_contents($log_file, $error_message, FILE_APPEND);
       
       // Kembalikan respons error
       echo json_encode([
           'error' => true,
           'message' => $e->getMessage()
       ]);
   }
   ?>
   ```

2. **Uji endpoint API**
   - Gunakan browser atau alat seperti Postman untuk menguji endpoint
   - Verifikasi bahwa respons sesuai dengan yang diharapkan
   - Periksa log untuk memastikan tidak ada error

#### Menambahkan Komponen Frontend Baru

1. **Buat file JavaScript baru di `assets/js/`**
   ```javascript
   // assets/js/new_feature.js
   
   // Konfigurasi dan konstanta
   const API_ENDPOINT = '/api/new_feature.php';
   
   // Fungsi utama
   async function loadNewFeatureData() {
       try {
           const response = await fetch(`${API_ENDPOINT}?param=value`);
           if (!response.ok) {
               throw new Error(`HTTP error! status: ${response.status}`);
           }
           
           const data = await response.json();
           displayNewFeatureData(data);
       } catch (error) {
           console.error('Error loading data:', error);
           showAlert('error', `Failed to load data: ${error.message}`);
       }
   }
   
   function displayNewFeatureData(data) {
       // Logika untuk menampilkan data
       const container = document.getElementById('new-feature-container');
       // Perbarui UI
   }
   
   // Event listeners dan inisialisasi
   document.addEventListener('DOMContentLoaded', function() {
       // Inisialisasi komponen
       loadNewFeatureData();
       
       // Tambahkan event listeners
       document.getElementById('refresh-btn').addEventListener('click', loadNewFeatureData);
   });
   ```

2. **Buat file CSS jika diperlukan**
   ```css
   /* assets/css/new_feature.css */
   
   .new-feature-container {
       padding: 1rem;
       background-color: #fff;
       border-radius: 0.5rem;
       box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
   }
   
   .new-feature-item {
       margin-bottom: 1rem;
       padding: 0.5rem;
       border-bottom: 1px solid #eee;
   }
   ```

3. **Buat halaman atau tambahkan ke halaman yang ada**
   ```php
   <?php
   // pages/new_feature.php
   require_once '../config/config.php';
   $pageTitle = "New Feature";
   include '../includes/header.php';
   include '../includes/navigation.php';
   ?>
   
   <div class="container mx-auto px-4 py-8">
       <h1 class="text-2xl font-bold mb-4">New Feature</h1>
       
       <div id="new-feature-container" class="new-feature-container">
           <!-- Content will be loaded here -->
           <div class="loading">Loading...</div>
       </div>
       
       <button id="refresh-btn" class="btn btn-primary mt-4">
           <i class="fas fa-sync-alt"></i> Refresh
       </button>
   </div>
   
   <script src="<?= asset('js/new_feature.js') ?>"></script>
   
   <?php include '../includes/footer.php'; ?>
   ```

### 2. Memodifikasi Fitur yang Ada

#### Mengubah Logika API

1. **Identifikasi file yang akan diubah**
   - Temukan file API yang sesuai di direktori `api/`
   - Pahami logika yang ada sebelum melakukan perubahan

2. **Buat perubahan dengan hati-hati**
   - Tambahkan komentar untuk menjelaskan perubahan
   - Pertahankan kompatibilitas mundur jika memungkinkan
   - Perbarui validasi parameter jika diperlukan

3. **Uji perubahan**
   - Verifikasi bahwa API masih berfungsi dengan benar
   - Periksa log untuk memastikan tidak ada error baru

#### Mengubah Tampilan Frontend

1. **Identifikasi file yang akan diubah**
   - Temukan file JavaScript dan CSS yang sesuai
   - Pahami struktur HTML yang terkait

2. **Buat perubahan dengan hati-hati**
   - Perbarui JavaScript untuk logika baru
   - Sesuaikan CSS untuk tampilan baru
   - Pertahankan responsivitas

3. **Uji perubahan**
   - Verifikasi tampilan di berbagai ukuran layar
   - Pastikan interaksi pengguna berfungsi dengan benar

## Praktik Terbaik

### Keamanan

1. **Validasi Input**
   - Selalu validasi dan sanitasi semua input pengguna
   - Gunakan fungsi `sanitize()` dari `config/config.php`
   - Hindari SQL injection dengan prepared statements

2. **Penanganan Error**
   - Gunakan try-catch untuk menangkap dan menangani error
   - Jangan tampilkan detail error teknis kepada pengguna
   - Log semua error untuk debugging

3. **Keamanan API**
   - Validasi parameter API dengan ketat
   - Batasi akses API jika diperlukan
   - Pertimbangkan untuk menambahkan autentikasi untuk API sensitif

### Performa

1. **Optimasi Database**
   - Gunakan indeks untuk query yang sering dijalankan
   - Batasi jumlah data yang diambil
   - Gunakan caching untuk data yang jarang berubah

2. **Optimasi Frontend**
   - Minify file JavaScript dan CSS untuk produksi
   - Gunakan lazy loading untuk konten yang tidak segera terlihat
   - Optimalkan gambar dan aset lainnya

3. **Caching**
   - Implementasikan caching untuk respons API yang sering diminta
   - Gunakan cache browser dengan header yang sesuai
   - Pertimbangkan untuk menggunakan CDN untuk aset statis

### Pengujian

1. **Pengujian API**
   - Uji semua endpoint API dengan berbagai parameter
   - Verifikasi format respons dan kode status
   - Uji penanganan error dan kasus edge

2. **Pengujian Frontend**
   - Uji di berbagai browser dan perangkat
   - Verifikasi responsivitas dan tata letak
   - Uji interaksi pengguna dan alur kerja

3. **Pengujian Integrasi**
   - Uji integrasi antara frontend dan backend
   - Verifikasi bahwa data mengalir dengan benar
   - Uji skenario pengguna end-to-end

## Deployment

### Persiapan Produksi

1. **Konfigurasi Produksi**
   - Set `$isDev = false` di `config/config.php`
   - Nonaktifkan debugging dan tampilan error
   - Perbarui URL dan pengaturan lainnya untuk produksi

2. **Optimasi Aset**
   - Minify file JavaScript dan CSS
   - Optimalkan gambar dan aset lainnya
   - Gabungkan file jika memungkinkan untuk mengurangi permintaan HTTP

3. **Keamanan Produksi**
   - Pastikan direktori sensitif tidak dapat diakses publik
   - Terapkan header keamanan yang sesuai
   - Perbarui izin file dan direktori

### Proses Deployment

1. **Backup Data yang Ada**
   - Backup database jika digunakan
   - Backup file konfigurasi dan konten yang dihasilkan pengguna

2. **Transfer File**
   - Gunakan FTP, SCP, atau Git untuk mentransfer file
   - Pastikan izin file dan direktori dipertahankan

3. **Verifikasi Deployment**
   - Periksa log untuk error
   - Uji fungsionalitas utama
   - Verifikasi bahwa semua fitur berfungsi dengan benar

## Pemeliharaan

### Monitoring

1. **Log Monitoring**
   - Periksa file log secara berkala
   - Perhatikan pola error atau perilaku yang tidak biasa
   - Implementasikan sistem peringatan jika memungkinkan

2. **Monitoring Performa**
   - Pantau waktu respons API
   - Pantau penggunaan sumber daya server
   - Identifikasi dan atasi bottleneck

### Pembaruan

1. **Pembaruan Dependensi**
   - Perbarui library dan framework secara berkala
   - Uji pembaruan di lingkungan pengembangan sebelum produksi
   - Perhatikan perubahan yang merusak kompatibilitas

2. **Pembaruan Fitur**
   - Dokumentasikan perubahan fitur
   - Ikuti proses pengembangan yang dijelaskan di atas
   - Komunikasikan perubahan kepada pengguna jika perlu

### Backup

1. **Backup Rutin**
   - Jadwalkan backup rutin untuk data penting
   - Simpan backup di lokasi yang aman
   - Uji proses pemulihan secara berkala 