import L from "leaflet";
import * as turf from "@turf/turf";

// Fix Leaflet's default icon path issues
import markerIcon2x from "leaflet/dist/images/marker-icon-2x.png";
import markerIcon from "leaflet/dist/images/marker-icon.png";
import markerShadow from "leaflet/dist/images/marker-shadow.png";

delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: markerIcon2x,
    iconUrl: markerIcon,
    shadowUrl: markerShadow,
});

/**
 * Initialize the Leaflet map
 * @param {string} id - The DOM ID of the map container
 * @returns {L.Map} The Leaflet map instance
 */
window.initMap = function (id) {
    const map = L.map(id).setView([-2.5489, 118.0149], 5);

    // 1. Define Base Layers
    const osmLayer = L.tileLayer(
        "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",
        {
            maxZoom: 19,
            attribution: "© OpenStreetMap",
        }
    );

    const googleSatLayer = L.tileLayer(
        "http://{s}.google.com/vt/lyrs=s,h&x={x}&y={y}&z={z}",
        {
            maxZoom: 20,
            subdomains: ["mt0", "mt1", "mt2", "mt3"],
            attribution: "&copy; Google Maps",
        }
    );

    // CartoDB Tiles Configuration
    const cartoTiles = {
        light: "https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png",
        dark: "https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png",
    };
    const cartoAttribution =
        '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>';

    // Create CartoDB layer (initially with light or dark based on current theme)
    const isDark = document.documentElement.classList.contains("dark");
    let cartoUrl = isDark ? cartoTiles.dark : cartoTiles.light;

    const cartoLayer = L.tileLayer(cartoUrl, {
        maxZoom: 19,
        attribution: cartoAttribution,
    });

    // 2. Add Default Layer (OSM)
    osmLayer.addTo(map);

    // 3. Add Layer Control
    const baseMaps = {
        "Standard (OSM)": osmLayer,
        "Professional (CartoDB)": cartoLayer,
        "Satellite (Google)": googleSatLayer,
    };

    L.control.layers(baseMaps).addTo(map);

    // 4. Dynamic Theme Logic for CartoDB
    function updateCartoLayer() {
        const isDark = document.documentElement.classList.contains("dark");
        const newUrl = isDark ? cartoTiles.dark : cartoTiles.light;
        cartoLayer.setUrl(newUrl);
    }

    // Watch for theme changes
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (
                mutation.type === "attributes" &&
                mutation.attributeName === "class"
            ) {
                updateCartoLayer();
            }
        });
    });

    observer.observe(document.documentElement, {
        attributes: true,
    });

    return map;
};

/**
 * Add a marker to the map
 * @param {L.Map} map
 * @param {number} lat
 * @param {number} lng
 * @param {string} popupContent
 * @returns {L.Marker}
 */
window.addMarker = function (map, lat, lng, popupContent) {
    const marker = L.marker([lat, lng]).addTo(map);
    if (popupContent) {
        marker.bindPopup(popupContent);
    }
    return marker;
};

/**
 * Generate "Color Contours" using IDW Interpolation and Convex Hull clipping.
 * Renders to a static image overlay to support smooth zooming.
 *
 * @param {L.Map} map
 * @param {Array} data - Array of objects with latitude, longitude, altitude
 * @returns {Object} { layer, breaks, palette }
 */
window.generateContours = function (map, data) {
    if (!data || data.length < 3) {
        console.warn(
            "Not enough points for contour generation (need at least 3)."
        );
        return null;
    }

    // 1. Prepare Data & Calculate Min/Max
    let minAlt = Infinity;
    let maxAlt = -Infinity;
    const points = [];

    data.forEach((d) => {
        const lat = parseFloat(d.latitude);
        const lng = parseFloat(d.longitude);
        const alt = parseFloat(d.altitude);

        if (!isNaN(lat) && !isNaN(lng) && !isNaN(alt)) {
            points.push({ lat, lng, alt });
            if (alt < minAlt) minAlt = alt;
            if (alt > maxAlt) maxAlt = alt;
        }
    });

    if (points.length < 3) return null;
    if (maxAlt === minAlt) maxAlt = minAlt + 1;

    // 2. Calculate Bounds & Convex Hull
    const turfPoints = turf.featureCollection(
        points.map((p) => turf.point([p.lng, p.lat]))
    );
    const hull = turf.convex(turfPoints);
    if (!hull) return null;

    const bbox = turf.bbox(turfPoints); // [minLng, minLat, maxLng, maxLat]
    // Add some padding to the bounds
    const lngSpan = bbox[2] - bbox[0];
    const latSpan = bbox[3] - bbox[1];
    const padding = 0.1; // 10% padding
    const bounds = {
        west: bbox[0] - lngSpan * padding,
        south: bbox[1] - latSpan * padding,
        east: bbox[2] + lngSpan * padding,
        north: bbox[3] + latSpan * padding,
    };

    // 3. Setup Canvas for IDW
    // Use a fixed high resolution for the image
    const maxDim = 800;
    const aspect = (bounds.east - bounds.west) / (bounds.north - bounds.south);

    let width, height;
    if (aspect > 1) {
        width = maxDim;
        height = Math.round(maxDim / aspect);
    } else {
        height = maxDim;
        width = Math.round(maxDim * aspect);
    }

    const canvas = document.createElement("canvas");
    canvas.width = width;
    canvas.height = height;
    const ctx = canvas.getContext("2d");

    // Helper to map LatLng to Canvas X/Y (Linear)
    function toCanvasPoint(lat, lng) {
        const x = ((lng - bounds.west) / (bounds.east - bounds.west)) * width;
        const y =
            ((bounds.north - lat) / (bounds.north - bounds.south)) * height; // Lat goes up, Y goes down
        return { x, y };
    }

    // 4. Draw IDW
    const imgData = ctx.createImageData(width, height);
    const pixels = imgData.data;

    // Pre-calculate point canvas coordinates
    const canvasPoints = points.map((p) => {
        const pt = toCanvasPoint(p.lat, p.lng);
        return { ...pt, alt: p.alt };
    });

    const p = 2; // Power parameter

    for (let y = 0; y < height; y++) {
        for (let x = 0; x < width; x++) {
            // Convert pixel back to approximate Lat/Lng for distance calc?
            // Or just use pixel distance?
            // Using pixel distance is faster and visually consistent with the canvas projection.
            // Since the canvas is linear in Lat/Lng, pixel distance is Euclidean in Lat/Lng space.

            let numerator = 0;
            let denominator = 0;
            let minDist = Infinity;
            let closestAlt = 0;

            for (const point of canvasPoints) {
                const dx = x - point.x;
                const dy = y - point.y;
                const distSq = dx * dx + dy * dy;

                // Optimization: if distSq is very small, just take the value
                if (distSq < 1) {
                    closestAlt = point.alt;
                    minDist = 0;
                    break;
                }

                const weight = 1 / Math.pow(distSq, p / 2); // dist^p = (sqrt(distSq))^p = distSq^(p/2)
                numerator += weight * point.alt;
                denominator += weight;
            }

            let val;
            if (minDist === 0) {
                val = closestAlt;
            } else {
                val = numerator / denominator;
            }

            // Color Mapping
            const ratio = Math.max(
                0,
                Math.min(1, (val - minAlt) / (maxAlt - minAlt))
            );
            // Topographic: Green (120) -> Red (0)
            const hue = (1 - ratio) * 120;
            const [r, g, b] = hslToRgb(hue / 360, 1, 0.5);

            const index = (y * width + x) * 4;
            pixels[index] = r;
            pixels[index + 1] = g;
            pixels[index + 2] = b;
            pixels[index + 3] = 255; // Full opacity, we will clip later
        }
    }

    ctx.putImageData(imgData, 0, 0);

    // 5. Clip with Convex Hull
    // We need to use 'destination-in' composite operation or just draw the hull as a mask.
    // Let's use a second canvas to draw the mask, then composite.
    // Actually, simpler: Set composite operation to 'destination-in' and draw the hull.
    // Only the overlapping parts (hull) will remain.

    ctx.globalCompositeOperation = "destination-in";
    ctx.beginPath();
    const hullCoords = hull.geometry.coordinates[0];
    hullCoords.forEach((coord, index) => {
        // coord is [lng, lat]
        const pt = toCanvasPoint(coord[1], coord[0]);
        if (index === 0) ctx.moveTo(pt.x, pt.y);
        else ctx.lineTo(pt.x, pt.y);
    });
    ctx.closePath();
    ctx.fill();

    // Reset composite op
    ctx.globalCompositeOperation = "source-over";

    // 6. Create Image Overlay
    const imageUrl = canvas.toDataURL();
    const imageBounds = [
        [bounds.south, bounds.west],
        [bounds.north, bounds.east],
    ];

    const layer = L.imageOverlay(imageUrl, imageBounds, {
        opacity: 0.7,
        interactive: false,
    });

    layer.addTo(map);

    return {
        layer: layer,
        breaks: [minAlt, (minAlt + maxAlt) / 2, maxAlt],
        palette: [],
    };
};

/**
 * Add a legend to the map
 * @param {L.Map} map
 * @param {Array} breaks
 * @returns {L.Control}
 */
window.addLegend = function (map, breaks) {
    const legend = L.control({ position: "bottomright" });

    legend.onAdd = function (map) {
        const div = L.DomUtil.create(
            "div",
            "info legend bg-card p-3 rounded shadow-md text-sm border border-border text-card-foreground"
        );
        div.style.lineHeight = "1.5";

        div.innerHTML = '<strong class="block mb-2">Altitude (m)</strong>';

        div.innerHTML += `
            <div style="
                background: linear-gradient(to right, #006400, #32CD32, #FFFF00, #FFA500, #FF0000);
                height: 10px;
                width: 100%;
                border-radius: 2px;
                margin-bottom: 5px;
            "></div>
            <div class="flex justify-between text-xs text-muted-foreground">
                <span>${Math.round(breaks[0])}</span>
                <span>${Math.round(breaks[2])}</span>
            </div>
        `;

        return div;
    };

    legend.addTo(map);
    return legend;
};

// Helper: Get Color based on value (Topographic)
function getColor(value, min, max) {
    const ratio = (value - min) / (max - min);

    // Topographic style: Green (Low) -> Yellow (Mid) -> Red (High)
    if (ratio < 0.2) return "#006400"; // Dark Green
    if (ratio < 0.4) return "#32CD32"; // Lime Green
    if (ratio < 0.6) return "#FFFF00"; // Yellow
    if (ratio < 0.8) return "#FFA500"; // Orange
    return "#FF0000"; // Red
}

// Helper: HSL to RGB
function hslToRgb(h, s, l) {
    let r, g, b;

    if (s === 0) {
        r = g = b = l; // achromatic
    } else {
        const hue2rgb = (p, q, t) => {
            if (t < 0) t += 1;
            if (t > 1) t -= 1;
            if (t < 1 / 6) return p + (q - p) * 6 * t;
            if (t < 1 / 2) return q;
            if (t < 2 / 3) return p + (q - p) * (2 / 3 - t) * 6;
            return p;
        };

        const q = l < 0.5 ? l * (1 + s) : l + s - l * s;
        const p = 2 * l - q;
        r = hue2rgb(p, q, h + 1 / 3);
        g = hue2rgb(p, q, h);
        b = hue2rgb(p, q, h - 1 / 3);
    }

    return [Math.round(r * 255), Math.round(g * 255), Math.round(b * 255)];
}
