// Territory centers for map positioning
const territoryCenters = {
    // Regional WMO V
    'IDN': { center: [-2.5, 118], zoom: 5, bounds: [[-11.0, 95.0], [6.0, 141.0]] },      // Indonesia
    'MYS': { center: [4.2, 108], zoom: 6, bounds: [[0.8, 99.6], [7.4, 119.3]] },         // Malaysia
    'SGP': { center: [1.35, 103.8], zoom: 11, bounds: [[1.15, 103.6], [1.47, 104.1]] },  // Singapore
    'PHL': { center: [12.8, 121.8], zoom: 6, bounds: [[4.5, 116.0], [21.0, 127.0]] },    // Philippines
    'BRN': { center: [4.5, 114.5], zoom: 9, bounds: [[4.0, 114.0], [5.1, 115.4]] },      // Brunei
    'TLS': { center: [-8.8, 125.7], zoom: 9, bounds: [[-9.5, 124.0], [-8.1, 127.3]] },   // Timor Leste
    'PNG': { center: [-6.3, 143.9], zoom: 6, bounds: [[-12.0, 140.0], [-1.0, 157.0]] },  // Papua New Guinea
    
    // Regional USA States
    'LIH': { center: [21.9789, -159.3385], zoom: 11, bounds: [[21.9, -159.4], [22.1, -159.2]] }, // LIHUE, KAUAI, HAWAII
    'HNL': { center: [21.3187, -157.9236], zoom: 11, bounds: [[21.2, -158.0], [21.4, -157.8]] }, // HONOLULU, OAHU, HAWAII
    'OGG': { center: [20.8986, -156.4305], zoom: 11, bounds: [[20.8, -156.5], [21.0, -156.3]] }, // KAHULUI AIRPORT, MAUI, HAWAII
    'ITO': { center: [19.7203, -155.0485], zoom: 11, bounds: [[19.6, -155.1], [19.8, -155.0]] }, // HILO HI, HAWAII
    'GUM': { center: [13.4, 144.8], zoom: 11, bounds: [[13.2, 144.6], [13.7, 145.0]] },  // WEATHER FORECAST OFFICE, GUAM, MARIANA IS.
    'PPG': { center: [-14.3, -170.7], zoom: 11, bounds: [[-14.4, -170.9], [-14.2, -170.5]] }, // PAGO PAGO/INT.AIRP. AMERICAN SAMOA
    
    // USA Pacific Region combined view
    'USA_PACIFIC': { center: [15, -160], zoom: 4, bounds: [[-15.0, -175.0], [25.0, -145.0]] }, // USA Pacific Region combined view
    
    // All stations view
    'ALL': { center: [0, -175], zoom: 3, bounds: [[-20.0, 95.0], [25.0, -150.0]] } // Combined Region V + USA view
}; 