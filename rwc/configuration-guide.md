# Panduan Konfigurasi

Dokumen ini menjelaskan cara mengonfigurasi dan menyesuaikan RWC Indonesian Operational System Monitoring Dashboard.

## Konfigurasi Dasar

### Konfigurasi Server

Sistem ini dibangun menggunakan PHP dan dapat dijalankan pada server web standar dengan konfigurasi berikut:

- PHP 7.4 atau lebih baru
- Ekstensi cURL PHP
- Ekstensi JSON PHP
- Akses tulis ke direktori `logs/` dan `data/`

### File Konfigurasi Utama

File konfigurasi utama terletak di `config/config.php`. File ini berisi pengaturan dasar untuk aplikasi, termasuk:

```php
// config/config.php
define('SITE_TITLE', 'Indonesian Operational System Monitoring');
define('SUPPORT_PHONE', '192');

// Deteksi URL situs
function detectSiteUrl() {
    // Logika deteksi URL
}

define('SITE_URL', detectSiteUrl());
define('BASE_PATH', realpath(dirname(__DIR__)));

// Mode pengembangan
$isDev = true; // Ubah ke false untuk produksi

// Konfigurasi lainnya
```

### Konfigurasi Frontend

Konfigurasi frontend utama terletak di `assets/js/config.js`:

```javascript
// Default API endpoint
const API_ENDPOINT = '/api/';

// Default map settings
const DEFAULT_CENTER = [-2.5, 118];
const DEFAULT_ZOOM = 5;
const DEFAULT_REGION = 'IDN';

// Debug mode
const DEBUG_MODE = false;

// Other configuration
```

## Penyesuaian Sistem

### Menambahkan Wilayah Baru

Untuk menambahkan wilayah baru ke sistem:

1. **Tambahkan kode wilayah ke dropdown pemilihan wilayah**

   Edit file `gbon/land_surface/index.php` dan `gbon/land_upper-air/index.php`:

   ```html
   <select class="select" id="regionSelect">
       <!-- Tambahkan wilayah baru di sini -->
       <option value="NEW_REGION">New Region Name (NEW_REGION)</option>
   </select>
   ```

2. **Tambahkan koordinat pusat wilayah**

   Edit file `assets/js/territories_center.js`:

   ```javascript
   const territoryCenters = {
       // Wilayah yang sudah ada
       
       // Tambahkan wilayah baru
       'NEW_REGION': {
           center: [latitude, longitude],
           zoom: 6,
           bounds: [[lat1, lng1], [lat2, lng2]] // Opsional
       }
   };
   ```

3. **Tambahkan wilayah ke daftar yang valid di API**

   Edit file `api/land_surface_stations.php` dan `api/upper_air_stations.php`:

   ```php
   // Tambahkan ke daftar wilayah yang valid jika diperlukan
   if ($territory === 'ALL_COMBINED') {
       $territory = 'SGP,IDN,BRN,PHL,TLS,PNG,MYS,NEW_REGION';
   }
   ```

### Menyesuaikan Tampilan

#### Mengubah Warna Status

Warna status stasiun dapat diubah di beberapa file:

1. **Di JavaScript untuk marker peta**

   Edit file `assets/js/land_surface.js` dan file JavaScript serupa:

   ```javascript
   const colors = {
       'complete': '#10b981',    // hijau
       'issues-high': '#ef4444', // merah
       'issues-low': '#f59e0b',  // oranye
       'not-received': '#374151' // abu-abu
   };
   ```

2. **Di CSS untuk legenda dan indikator**

   Edit file CSS yang sesuai di direktori `assets/css/`.

#### Mengubah Tata Letak

Tata letak halaman dapat disesuaikan dengan mengedit file template:

- `includes/header.php` - Header halaman
- `includes/footer.php` - Footer halaman
- `includes/navigation.php` - Navigasi halaman

### Menyesuaikan Logika Status

Untuk mengubah logika penentuan status stasiun:

1. Edit fungsi `determineStationStatus()` di file JavaScript yang sesuai:

   ```javascript
   function determineStationStatus(station) {
       const totalCoverage = calculateTotalCoverage(station);
       
       // Ubah logika di sini
       if (totalCoverage >= 80) return 'complete';
       if (totalCoverage >= 30) return 'issues-low';
       if (totalCoverage > 0) return 'issues-high';
       return 'not-received';
   }
   ```

2. Pastikan juga untuk memperbarui logika serupa di API PHP jika diperlukan.

## Konfigurasi API

### Mengubah Sumber Data

Sistem ini menggunakan WMO WDQMS API sebagai sumber data utama. Untuk mengubah sumber data:

1. Edit file API yang sesuai (`api/land_surface_stations.php`, dll)
2. Ubah URL API dan parameter yang digunakan:

   ```php
   $url = "https://new-api-url.example.com/endpoint?" . 
          "param1=$param1&param2=$param2";
   ```

3. Sesuaikan logika pemrosesan respons untuk format data baru

### Menambahkan Endpoint API Baru

Untuk menambahkan endpoint API baru:

1. Buat file PHP baru di direktori `api/`
2. Ikuti pola yang ada untuk memproses permintaan dan mengembalikan respons JSON
3. Tambahkan header CORS dan tipe konten yang sesuai:

   ```php
   header('Content-Type: application/json');
   header('Access-Control-Allow-Origin: *');
   header('Access-Control-Allow-Methods: GET');
   header('Access-Control-Allow-Headers: Content-Type');
   ```

## Konfigurasi Logging

Sistem menggunakan file log untuk mencatat aktivitas API dan error. Konfigurasi logging dapat disesuaikan:

1. **Mengubah lokasi file log**

   Edit variabel `$log_file` di file API:

   ```php
   $log_file = __DIR__ . '/../logs/custom_log_name.log';
   ```

2. **Mengubah level logging**

   Sesuaikan informasi yang dicatat dalam log:

   ```php
   $log_message = date('Y-m-d H:i:s') . " - Custom log entry\n";
   // Tambahkan informasi yang diperlukan
   file_put_contents($log_file, $log_message, FILE_APPEND);
   ```

## Konfigurasi Keamanan

### Mode Produksi

Untuk menjalankan sistem dalam mode produksi:

1. Edit `config/config.php`:

   ```php
   $isDev = false; // Ubah ke false untuk produksi
   
   if ($isDev) {
       error_reporting(E_ALL);
       ini_set('display_errors', 1);
   } else {
       error_reporting(0);
       ini_set('display_errors', 0);
   }
   ```

2. Pastikan direktori `logs/` dan `data/` memiliki izin tulis yang sesuai tetapi tidak dapat diakses langsung melalui web.

### Header Keamanan

Tambahkan header keamanan di `.htaccess` atau konfigurasi server:

```
# Keamanan dasar
Header set X-Content-Type-Options "nosniff"
Header set X-Frame-Options "SAMEORIGIN"
Header set X-XSS-Protection "1; mode=block"
Header set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' cdnjs.cloudflare.com; img-src 'self' data:;"
```

## Pemecahan Masalah

### Masalah Umum

1. **API Tidak Dapat Diakses**
   - Periksa koneksi internet server
   - Verifikasi bahwa ekstensi cURL diaktifkan
   - Periksa file log untuk detail error

2. **Data Tidak Ditampilkan di Peta**
   - Buka konsol browser untuk melihat error JavaScript
   - Periksa respons API menggunakan alat pengembang browser
   - Verifikasi bahwa format data sesuai dengan yang diharapkan

3. **Tampilan Tidak Responsif**
   - Pastikan file CSS dimuat dengan benar
   - Periksa media queries di file `assets/css/responsive.css`
   - Uji di berbagai perangkat dan browser

### Log Debugging

Untuk debugging yang lebih mendetail, aktifkan mode debug:

1. Di `config/config.php`:
   ```php
   $isDev = true;
   ```

2. Di `assets/js/config.js` atau file JavaScript yang sesuai:
   ```javascript
   window.debugMode = true;
   ``` 