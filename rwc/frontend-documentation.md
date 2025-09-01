# Dokumentasi Frontend

Dokumen ini menjelaskan komponen-komponen frontend yang digunakan dalam sistem RWC Indonesian Operational System Monitoring Dashboard.

## Struktur Frontend

Frontend sistem ini dibangun dengan pendekatan modular menggunakan HTML, CSS (dengan Tailwind CSS), dan JavaScript. Berikut adalah struktur utama dari frontend:

```
assets/
├── css/
│   ├── base.css         - Gaya dasar untuk seluruh aplikasi
│   ├── controls.css     - Gaya untuk elemen kontrol (tombol, dropdown, dll)
│   ├── map.css          - Gaya untuk komponen peta
│   ├── monitoring.css   - Gaya khusus untuk halaman monitoring
│   ├── panels.css       - Gaya untuk panel informasi
│   ├── popup.css        - Gaya untuk popup
│   ├── responsive.css   - Gaya responsif untuk berbagai ukuran layar
│   ├── status.css       - Gaya untuk indikator status
│   ├── style.css        - File gaya utama yang mengimpor semua gaya lainnya
│   └── utils.css        - Utilitas CSS
├── images/              - Gambar dan ikon
└── js/
    ├── config.js        - Konfigurasi aplikasi
    ├── land_surface.js  - Logika untuk monitoring permukaan darat
    ├── map.js           - Konfigurasi dan fungsi peta Leaflet
    ├── nwp_land_surface.js - Logika untuk monitoring NWP permukaan darat
    ├── nwp_upper_air.js - Logika untuk monitoring NWP udara atas
    ├── script.js        - Script utama aplikasi
    ├── territories_center.js - Data koordinat pusat wilayah
    ├── territories.js   - Data batas wilayah GeoJSON
    ├── upper_air.js     - Logika untuk monitoring udara atas
    └── utility.js       - Fungsi utilitas
```

## Komponen Utama

### 1. Peta Interaktif

Peta interaktif dibuat menggunakan Leaflet.js dan menampilkan stasiun-stasiun meteorologi dengan status operasionalnya.

**File Terkait:**
- `assets/js/map.js`
- `assets/css/map.css`

**Fitur:**
- Zoom in/out
- Tampilan fullscreen
- Pengelompokan marker (clustering)
- Tooltip dan popup informasi stasiun
- Filter berdasarkan status stasiun
- Penyesuaian tampilan berdasarkan wilayah

**Penggunaan:**

```javascript
// Inisialisasi peta
const map = L.map('map', {
    center: DEFAULT_CENTER,
    zoom: DEFAULT_ZOOM,
    maxBounds: [[-90, -180], [90, 180]],
    fullscreenControl: true
});

// Menambahkan marker stasiun
const marker = L.marker([latitude, longitude], {
    icon: customIcon
}).addTo(map);

// Menambahkan popup informasi
marker.bindPopup(popupContent);
```

### 2. Panel Kontrol

Panel kontrol memungkinkan pengguna untuk memfilter dan mengonfigurasi tampilan data.

**File Terkait:**
- `assets/css/controls.css`

**Komponen:**
- Selector periode (six-hour, daily, monthly)
- Selector variabel (pressure, temperature, dll)
- Selector wilayah
- Selector tanggal
- Tombol periode waktu (00, 06, 12, 18)

**Contoh HTML:**

```html
<div class="controls">
    <div class="control">
        <label class="label"><i class="fas fa-clock"></i> Period Type</label>
        <select class="select" id="periodType">
            <option value="six-hour">Six_hour</option>
            <option value="daily">Daily</option>
            <option value="monthly">Monthly</option>
        </select>
    </div>
    
    <!-- Kontrol lainnya -->
</div>
```

### 3. Panel Status

Panel status menampilkan ringkasan statistik dari stasiun yang ditampilkan.

**File Terkait:**
- `assets/css/status.css`
- `assets/css/panels.css`

**Informasi yang Ditampilkan:**
- Total stasiun
- Jumlah stasiun dengan masalah
- Persentase stasiun dengan masalah

### 4. Panel Legenda

Panel legenda menjelaskan arti dari warna-warna yang digunakan pada marker stasiun.

**File Terkait:**
- `assets/css/panels.css`

**Kategori Status:**
- Complete (≥ 80%)
- Availability Issues (≥ 30%)
- Availability Issues (< 30%)
- Not Received in Period

### 5. Popup Informasi Stasiun

Popup yang muncul ketika pengguna mengklik marker stasiun, menampilkan informasi detail tentang stasiun tersebut.

**File Terkait:**
- `assets/css/popup.css`

**Informasi yang Ditampilkan:**
- Nama stasiun
- WIGOS ID
- Negara
- Koordinat
- Data dari setiap pusat (DWD, ECMWF, JMA, NCEP)
- Total ketersediaan data
- Variabel yang dipantau
- Waktu pembaruan terakhir

## Alur Kerja Frontend

1. **Inisialisasi Aplikasi**
   - Load konfigurasi dari `config.js`
   - Inisialisasi peta dari `map.js`
   - Set nilai default untuk kontrol

2. **Interaksi Pengguna**
   - Pengguna memilih filter (wilayah, tanggal, variabel, dll)
   - Event listener mendeteksi perubahan dan memanggil `loadStationData()`

3. **Pengambilan Data**
   - `loadStationData()` membuat request ke API
   - Data stasiun diterima dalam format JSON

4. **Pemrosesan Data**
   - Data diproses untuk menentukan status setiap stasiun
   - Marker dibuat untuk setiap stasiun dengan warna sesuai status

5. **Pembaruan UI**
   - Marker ditampilkan di peta
   - Panel status diperbarui dengan statistik terbaru
   - Filter status diaktifkan untuk interaksi pengguna

## Responsivitas

Aplikasi dirancang untuk responsif di berbagai ukuran layar:

- **Desktop**: Tampilan penuh dengan semua panel dan kontrol
- **Tablet**: Tata letak yang dioptimalkan dengan beberapa penyesuaian
- **Mobile**: Tata letak vertikal dengan kontrol yang disederhanakan

File `assets/css/responsive.css` mengatur gaya responsif untuk berbagai breakpoint.

## Praktik Terbaik

1. **Performa**
   - Gunakan marker clustering untuk menangani banyak stasiun
   - Batasi pembaruan UI yang tidak perlu
   - Gunakan caching untuk data yang jarang berubah

2. **Pengalaman Pengguna**
   - Tampilkan indikator loading saat mengambil data
   - Berikan pesan error yang jelas jika terjadi masalah
   - Simpan preferensi pengguna menggunakan URL parameter

3. **Pemeliharaan**
   - Ikuti struktur modular untuk JavaScript
   - Gunakan konstanta untuk nilai yang sering digunakan
   - Dokumentasikan fungsi dan komponen dengan baik 