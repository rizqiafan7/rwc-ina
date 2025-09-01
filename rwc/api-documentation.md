# Dokumentasi API

Dokumen ini menjelaskan endpoint API yang tersedia dalam sistem RWC Indonesian Operational System Monitoring Dashboard.

## Endpoint API

### 1. Land Surface Stations API

**Endpoint:** `/api/land_surface_stations.php`

**Deskripsi:** Mengambil data stasiun permukaan darat dari WMO WDQMS API.

**Metode:** GET

**Parameter:**

| Parameter   | Tipe   | Wajib | Deskripsi                                                                                                |
|-------------|--------|-------|----------------------------------------------------------------------------------------------------------|
| territory   | string | Ya    | Kode wilayah/negara (contoh: IDN, SGP, MYS, ALL_COMBINED)                                                |
| date        | string | Ya    | Tanggal data dalam format YYYY-MM-DD untuk periode harian/6 jam atau YYYY-MM untuk periode bulanan        |
| period      | string | Ya    | Periode data (six_hour, daily, monthly)                                                                  |
| time_period | string | Tidak | Periode waktu untuk data 6 jam (00, 06, 12, 18). Diperlukan jika period=six_hour                         |
| variable    | string | Ya    | Variabel yang diambil (pressure, temperature, zonal_wind, meridional_wind, humidity)                     |

**Contoh Request:**
```
GET /api/land_surface_stations.php?territory=IDN&date=2023-05-01&period=six_hour&time_period=00&variable=pressure
```

**Response Format:**
```json
{
  "stations": [
    {
      "id": "0-20000-0-96749",
      "name": "JAKARTA/OBSERVATORY",
      "wigosId": "0-20000-0-96749",
      "countryCode": "IDN",
      "inOSCAR": "True",
      "latitude": -6.18,
      "longitude": 106.83,
      "territory": "IDN",
      "territoryCode": "IDN",
      "stationTypeName": "SYNOP",
      "stationStatusCode": "operational",
      "dataCompleteness": 100,
      "received": 24,
      "expected": 24,
      "variable": "pressure",
      "date": "2023-05-01",
      "lastUpdated": "2023-05-01 00",
      "DWD": 6,
      "ECMWF": 6,
      "JMA": 6,
      "NCEP": 6,
      "centers": {
        "DWD": {
          "received": 6,
          "expected": 6,
          "status": "operational",
          "color_code": "green",
          "description": "All expected observations received"
        },
        "ECMWF": {
          "received": 6,
          "expected": 6,
          "status": "operational",
          "color_code": "green",
          "description": "All expected observations received"
        },
        "JMA": {
          "received": 6,
          "expected": 6,
          "status": "operational",
          "color_code": "green",
          "description": "All expected observations received"
        },
        "NCEP": {
          "received": 6,
          "expected": 6,
          "status": "operational",
          "color_code": "green",
          "description": "All expected observations received"
        }
      }
    }
  ],
  "metadata": {
    "total_stations": 184,
    "operational_count": 150,
    "issues_count": 20,
    "critical_count": 10,
    "not_received_count": 4,
    "territory": "IDN",
    "territory_name": "Indonesia",
    "api_url": "https://wdqms.wmo.int/wdqmsapi/v1/download/gbon/synop/six_hour/availability/?date=2023-05-01&period=00&variable=pressure&centers=DWD,ECMWF,JMA,NCEP&countries=IDN",
    "http_code": 200,
    "api_status": "Data berhasil diambil (HTTP 200)"
  }
}
```

### 2. Upper Air Stations API

**Endpoint:** `/api/upper_air_stations.php`

**Deskripsi:** Mengambil data stasiun udara atas dari WMO WDQMS API.

**Metode:** GET

**Parameter:**

| Parameter   | Tipe   | Wajib | Deskripsi                                                                                                |
|-------------|--------|-------|----------------------------------------------------------------------------------------------------------|
| territory   | string | Ya    | Kode wilayah/negara (contoh: IDN, SGP, MYS, ALL_COMBINED)                                                |
| date        | string | Ya    | Tanggal data dalam format YYYY-MM-DD untuk periode harian/12 jam atau YYYY-MM untuk periode bulanan       |
| period      | string | Ya    | Periode data (twelve_hour, daily, monthly)                                                               |
| time_period | string | Tidak | Periode waktu untuk data 12 jam (00, 12). Diperlukan jika period=twelve_hour                             |
| variable    | string | Ya    | Variabel yang diambil (temperature, zonal_wind, meridional_wind, humidity)                               |

**Contoh Request:**
```
GET /api/upper_air_stations.php?territory=IDN&date=2023-05-01&period=twelve_hour&time_period=00&variable=temperature
```

**Response Format:** Serupa dengan Land Surface Stations API

## Kode Status

| Status Code | Deskripsi                                                                                      |
|-------------|------------------------------------------------------------------------------------------------|
| operational | Stasiun beroperasi normal dengan ketersediaan data ≥ 80%                                       |
| issues      | Stasiun memiliki masalah dengan ketersediaan data antara 0% dan 80%                            |
| critical    | Stasiun dalam kondisi kritis (semua pusat melaporkan 0 atau 1 observasi)                       |
| not_received| Tidak ada data yang diterima dari stasiun (0% ketersediaan)                                    |

## Penanganan Error

API akan mengembalikan respons JSON dengan format berikut jika terjadi kesalahan:

```json
{
  "error": true,
  "message": "Pesan error",
  "debug_info": {
    "url": "URL API yang dipanggil",
    "http_code": "Kode HTTP yang diterima",
    "response": "Respons dari API eksternal (jika ada)"
  }
}
```

## Catatan Penting

1. API ini bertindak sebagai proxy untuk WMO WDQMS API, dengan pemrosesan dan transformasi data tambahan.
2. Data disimpan dalam log di direktori `/logs/wmo_api.log` untuk keperluan debugging.
3. Untuk parameter territory, nilai khusus seperti `ALL_COMBINED` akan menggabungkan data dari wilayah Regional V dan stasiun USA di Pasifik.
4. Nilai `USA_PACIFIC` akan mengambil hanya stasiun USA di wilayah Pasifik (Hawaii, Guam, dll). 