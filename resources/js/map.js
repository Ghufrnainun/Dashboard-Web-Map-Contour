import L from 'leaflet';
import * as turf from '@turf/turf';

// Fix for default marker icon issues in Leaflet with Webpack/Vite
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon-2x.png',
    iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon.png',
    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
});

export function initMap(elementId, center = [-6.200000, 106.816666], zoom = 13) {
    const map = L.map(elementId).setView(center, zoom);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    return map;
}

export function addMarker(map, lat, lng, popupContent) {
    const marker = L.marker([lat, lng]).addTo(map);
    if (popupContent) {
        marker.bindPopup(popupContent);
    }
    return marker;
}

// Example Turf usage (can be expanded)
export function calculateDistance(point1, point2) {
    const from = turf.point(point1);
    const to = turf.point(point2);
    return turf.distance(from, to, { units: 'kilometers' });
}

// Expose functions to global scope if needed for inline scripts
window.initMap = initMap;
window.addMarker = addMarker;
window.calculateDistance = calculateDistance;
window.L = L;
window.turf = turf;

export function generateContours(map, data, options = {}) {
    if (!data || data.length < 3) {
        console.warn("Not enough data points to generate contours (min 3 needed).");
        return null;
    }

    // 1. Convert data to Turf FeatureCollection
    const points = turf.featureCollection(
        data.map(item => turf.point([parseFloat(item.longitude), parseFloat(item.latitude)], { altitude: parseFloat(item.altitude) }))
    );

    // 2. IDW Interpolation
    // Create a grid of points with estimated altitude
    // options.gridType: 'point', 'square', 'hex', 'triangle'
    // options.property: property name to interpolate
    // options.units: units for grid cell size
    const bounds = turf.bbox(points);
    const cellSize = options.cellSize || 0.05; // km
    const grid = turf.interpolate(points, cellSize, {
        gridType: 'point',
        property: 'altitude',
        units: 'kilometers',
        weight: 1 // IDW exponent
    });

    // 3. Generate Isobands (Contour Polygons)
    // Define breaks for altitude ranges
    const minAlt = Math.min(...data.map(d => parseFloat(d.altitude)));
    const maxAlt = Math.max(...data.map(d => parseFloat(d.altitude)));

    // Create dynamic breaks (e.g., 5 levels)
    const steps = 6;
    const stepSize = (maxAlt - minAlt) / steps;
    const breaks = [];
    for (let i = 0; i <= steps; i++) {
        breaks.push(minAlt + (i * stepSize));
    }

    const isobands = turf.isobands(grid, breaks, { zProperty: 'altitude' });

    // 4. Style and Add to Map
    const contourLayer = L.geoJSON(isobands, {
        style: function (feature) {
            // Ensure value is a number. turf.isobands might return a string range "10-20" or a number.
            let value = feature.properties.altitude;
            if (typeof value === 'string') {
                // If it's a range "10-20", parseFloat will return 10.
                value = parseFloat(value);
            }

            const color = getColor(value, minAlt, maxAlt);

            return {
                fillColor: color,
                weight: 1,
                opacity: 1,
                color: 'white', // border color
                dashArray: '3',
                fillOpacity: 0.7
            };
        },
        onEachFeature: function (feature, layer) {
            let val = feature.properties.altitude;
            let displayVal = val;

            if (typeof val === 'number') {
                displayVal = `> ${val.toFixed(2)} m`;
            } else if (typeof val === 'string') {
                // If it looks like a range, keep it, otherwise try to format
                if (!val.includes('-')) {
                    const parsed = parseFloat(val);
                    if (!isNaN(parsed)) {
                        displayVal = `> ${parsed.toFixed(2)} m`;
                    }
                }
            }

            layer.bindPopup(`Altitude Region: ${displayVal}`);
        }
    });

    contourLayer.addTo(map);

    // Return layer and stats for legend
    return { layer: contourLayer, min: minAlt, max: maxAlt };
}

function getColor(value, min, max) {
    const ratio = (value - min) / (max - min);

    // Heatmap style: Indigo -> Blue -> Cyan -> Green -> Yellow -> Orange -> Red
    if (ratio < 0.15) return '#4b0082'; // Indigo
    if (ratio < 0.30) return '#0000ff'; // Blue
    if (ratio < 0.45) return '#00ffff'; // Cyan
    if (ratio < 0.60) return '#00ff00'; // Green
    if (ratio < 0.75) return '#ffff00'; // Yellow
    if (ratio < 0.90) return '#ffa500'; // Orange
    return '#ff0000'; // Red
}

export function addLegend(map, min, max) {
    const legend = L.control({ position: 'bottomright' });

    legend.onAdd = function (map) {
        const div = L.DomUtil.create('div', 'info legend');
        // Add some basic styles for the legend
        div.style.backgroundColor = 'white';
        div.style.padding = '10px';
        div.style.borderRadius = '5px';
        div.style.boxShadow = '0 0 15px rgba(0,0,0,0.2)';

        const grades = [0, 0.15, 0.30, 0.45, 0.60, 0.75, 0.90];
        const labels = [];
        const range = max - min;

        div.innerHTML = '<h4 style="margin:0 0 5px;font-size:14px;">Altitude (m)</h4>';

        for (let i = 0; i < grades.length; i++) {
            const val = min + (grades[i] * range);
            const color = getColor(val, min, max);

            div.innerHTML +=
                '<i style="background:' + color + '; width: 18px; height: 18px; float: left; margin-right: 8px; opacity: 0.7;"></i> ' +
                val.toFixed(1) + (grades[i + 1] ? '&ndash;' + (min + (grades[i + 1] * range)).toFixed(1) + '<br>' : '+');
        }

        return div;
    };

    legend.addTo(map);
    return legend;
}

window.generateContours = generateContours;
window.addLegend = addLegend;
