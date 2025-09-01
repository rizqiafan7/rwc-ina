# Alur Kerja Sistem

Dokumen ini menjelaskan alur kerja sistem RWC Indonesian Operational System Monitoring Dashboard, dari pengambilan data hingga visualisasi di dashboard.

## Diagram Alur Data

```
┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐
│                 │     │                 │     │                 │     │                 │
│  WMO WDQMS API  │────▶│  API Proxy PHP  │────▶│  Frontend JS    │────▶│  Visualisasi    │
│                 │     │                 │     │                 │     │                 │
└─────────────────┘     └─────────────────┘     └─────────────────┘     └─────────────────┘
                              │                        ▲
                              │                        │
                              ▼                        │
                        ┌─────────────────┐            │
                        │                 │            │
                        │  Log & Cache    │────────────┘
                        │                 │
                        └─────────────────┘
```

## Tahapan Alur Kerja

### 1. Pengambilan Data dari WMO WDQMS API

**Proses:**
1. Sistem memulai permintaan data berdasarkan parameter yang ditentukan (wilayah, tanggal, periode, dll)
2. Permintaan dikirim ke WMO WDQMS API menggunakan cURL
3. API eksternal mengembalikan data dalam format CSV atau JSON

**Komponen Terkait:**
- `api/land_surface_stations.php`
- `api/upper_air_stations.php`

**Contoh Kode:**
```php
$url = "https://wdqms.wmo.int/wdqmsapi/v1/download/gbon/synop/$period/availability/?" . 
       "date=$date&period=$time_period&variable=$variable&centers=DWD,ECMWF,JMA,NCEP&countries=$territory";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// ... konfigurasi cURL lainnya ...
$response = curl_exec($ch);
```

### 2. Pemrosesan dan Transformasi Data

**Proses:**
1. Data mentah dari WMO WDQMS API diterima
2. Sistem memvalidasi format data (CSV atau JSON)
3. Data diproses dan ditransformasikan ke format yang seragam
4. Sistem menghitung statistik tambahan (persentase ketersediaan, status operasional)
5. Data dikonversi ke format JSON yang konsisten untuk frontend

**Komponen Terkait:**
- Fungsi pemrosesan di `api/land_surface_stations.php` dan `api/upper_air_stations.php`

**Contoh Transformasi Data:**
- Menghitung status stasiun berdasarkan ketersediaan data:
  - `operational`: Ketersediaan ≥ 80%
  - `issues`: Ketersediaan antara 0% dan 80%
  - `critical`: Kondisi khusus (semua pusat melaporkan 0 atau 1 observasi)
  - `not_received`: Tidak ada data (0% ketersediaan)

### 3. Pencatatan Log dan Caching

**Proses:**
1. Sistem mencatat permintaan API dan respons dalam file log
2. Log menyimpan informasi tentang parameter permintaan, kode HTTP, dan respons
3. Data dapat di-cache untuk meningkatkan performa pada permintaan yang sering dilakukan

**Komponen Terkait:**
- File log: `logs/wmo_api.log`

**Contoh Kode:**
```php
$log_message = date('Y-m-d H:i:s') . " - Request started\n";
$log_message .= "Territory: $territory\n";
$log_message .= "Date: $date\n";
// ... informasi log lainnya ...
file_put_contents($log_file, $log_message, FILE_APPEND);
```

### 4. Pengiriman Data ke Frontend

**Proses:**
1. Data yang sudah diproses dikirim ke frontend dalam format JSON
2. Respons JSON berisi array stasiun dan metadata tambahan
3. Frontend menerima dan memproses data untuk visualisasi

**Komponen Terkait:**
- Output JSON dari API PHP
- Fungsi `loadStationData()` di JavaScript

**Contoh Respons:**
```json
{
  "stations": [ ... array stasiun ... ],
  "metadata": {
    "total_stations": 184,
    "operational_count": 150,
    "issues_count": 20,
    "critical_count": 10,
    "not_received_count": 4,
    "territory": "IDN",
    "territory_name": "Indonesia"
  }
}
```

### 5. Pemrosesan Data di Frontend

**Proses:**
1. JavaScript menerima data JSON dari API
2. Data diproses untuk visualisasi (penentuan warna marker, dll)
3. Marker stasiun dibuat dengan status visual yang sesuai
4. Statistik dihitung untuk panel ringkasan

**Komponen Terkait:**
- `assets/js/land_surface.js`
- `assets/js/upper_air.js`

**Contoh Kode:**
```javascript
function determineStationStatus(station) {
    const totalCoverage = calculateTotalCoverage(station);
    
    if (totalCoverage >= 80) return 'complete';
    if (totalCoverage === 0) return 'not-received';
    
    // Logika penentuan status lainnya...
    return allZeroOrOne ? 'issues-high' : 'issues-low';
}
```

### 6. Visualisasi Data di Dashboard

**Proses:**
1. Marker stasiun ditampilkan di peta dengan warna sesuai status
2. Panel status diperbarui dengan statistik terbaru
3. Pengguna dapat berinteraksi dengan peta dan kontrol filter
4. Perubahan filter memicu permintaan data baru ke API

**Komponen Terkait:**
- `assets/js/map.js`
- Leaflet.js untuk visualisasi peta

**Contoh Kode:**
```javascript
function displayStations(stations) {
    // Bersihkan marker yang ada
    stationMarkers.forEach(marker => marker.remove());
    stationMarkers = [];

    // Tambahkan marker baru
    stations.forEach(station => {
        const status = determineStationStatus(station);
        const marker = createStationMarker(station, status);
        marker.addTo(map);
        stationMarkers.push(marker);
    });
    
    // Perbarui statistik
    updateStatistics();
}
```

## Alur Interaksi Pengguna

1. **Pengguna Membuka Dashboard**
   - Sistem memuat nilai default untuk filter
   - Sistem mengambil data untuk nilai default tersebut
   - Dashboard menampilkan peta dengan stasiun dan status

2. **Pengguna Mengubah Filter**
   - Pengguna memilih wilayah, tanggal, variabel, atau periode yang berbeda
   - Event listener mendeteksi perubahan
   - Sistem memicu permintaan data baru ke API
   - Dashboard diperbarui dengan data baru

3. **Pengguna Berinteraksi dengan Peta**
   - Pengguna dapat memperbesar/memperkecil peta
   - Pengguna dapat mengklik marker untuk melihat detail stasiun
   - Pengguna dapat memfilter stasiun berdasarkan status
   - Pengguna dapat mengekspor data yang ditampilkan

## Penanganan Error

1. **Error Koneksi API Eksternal**
   - Sistem mencatat error dalam log
   - Sistem menampilkan pesan error yang informatif ke pengguna
   - Sistem dapat mencoba menggunakan data cache jika tersedia

2. **Error Format Data**
   - Sistem mencoba memproses data dalam format alternatif (JSON/CSV)
   - Sistem mencatat error parsing dalam log
   - Sistem menampilkan pesan error yang sesuai

3. **Error Frontend**
   - JavaScript menangkap error dengan try-catch
   - Sistem menampilkan pesan error yang user-friendly
   - Konsol browser menyimpan detail error untuk debugging

## Optimasi Performa

1. **Caching Data**
   - Data yang jarang berubah dapat di-cache
   - Parameter permintaan disimpan untuk menghindari permintaan duplikat

2. **Marker Clustering**
   - Marker stasiun dikelompokkan untuk performa yang lebih baik pada peta
   - Clustering membantu menangani banyak stasiun tanpa mengorbankan performa

3. **Lazy Loading**
   - Komponen UI dimuat sesuai kebutuhan
   - Data tambahan dimuat hanya ketika diperlukan (misalnya, saat mengklik marker) 