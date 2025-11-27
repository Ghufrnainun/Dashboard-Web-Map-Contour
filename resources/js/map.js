import L from 'leaflet';
import * as turf from '@turf/turf';

// Fix Leaflet's default icon path issues
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';

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

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap'
    }).addTo(map);

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
 * 
 * @param {L.Map} map 
 * @param {Array} data - Array of objects with latitude, longitude, altitude
 * @returns {Object} { layer, breaks, palette }
 */
window.generateContours = function (map, data) {
    if (!data || data.length < 3) {
        console.warn("Not enough points for contour generation (need at least 3).");
        return null;
    }

    // 1. Prepare Data & Calculate Min/Max
    let minAlt = Infinity;
    let maxAlt = -Infinity;
    const points = [];

    data.forEach(d => {
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

    // 2. Calculate Convex Hull (Boundary)
    const turfPoints = turf.featureCollection(
        points.map(p => turf.point([p.lng, p.lat])) // Turf uses [lng, lat]
    );
    const hull = turf.convex(turfPoints);
    if (!hull) return null;

    // 3. Color Function (HSL: Blue -> Red)
    function getColor(value) {
        const ratio = Math.max(0, Math.min(1, (value - minAlt) / (maxAlt - minAlt)));
        const hue = (1 - ratio) * 240; // 240 (Blue) -> 0 (Red)
        return `hsla(${hue}, 100%, 50%, 0.7)`;
    }

    // 4. Create Custom Canvas Layer
    const CanvasLayer = L.Layer.extend({
        onAdd: function (map) {
            this._map = map;
            this._canvas = L.DomUtil.create('canvas', 'leaflet-heatmap-layer');
            this._canvas.style.pointerEvents = 'none';
            this._canvas.style.zIndex = 100;

            const size = this._map.getSize();
            this._canvas.width = size.x;
            this._canvas.height = size.y;

            const animated = this._map.options.zoomAnimation && L.Browser.any3d;
            L.DomUtil.addClass(this._canvas, 'leaflet-zoom-' + (animated ? 'animated' : 'hide'));

            map.getPanes().overlayPane.appendChild(this._canvas);
            map.on('moveend', this._reset, this);
            map.on('resize', this._resize, this);

            if (map.options.zoomAnimation && L.Browser.any3d) {
                map.on('zoomanim', this._animateZoom, this);
            }

            this._reset();
        },

        onRemove: function (map) {
            map.getPanes().overlayPane.removeChild(this._canvas);
            map.off('moveend', this._reset, this);
            map.off('resize', this._resize, this);
            if (map.options.zoomAnimation) {
                map.off('zoomanim', this._animateZoom, this);
            }
        },

        _resize: function () {
            const size = this._map.getSize();
            this._canvas.width = size.x;
            this._canvas.height = size.y;
            this._reset();
        },

        _reset: function () {
            const topLeft = this._map.containerPointToLayerPoint([0, 0]);
            L.DomUtil.setPosition(this._canvas, topLeft);
            this._draw();
        },

        _animateZoom: function (e) {
            const scale = this._map.getZoomScale(e.zoom);
            const offset = this._map._latLngToNewLayerPoint(this._map.getBounds().getNorthWest(), e.zoom, e.center);
            L.DomUtil.setTransform(this._canvas, offset, scale);
        },

        _draw: function () {
            const ctx = this._canvas.getContext('2d');
            const width = this._canvas.width;
            const height = this._canvas.height;

            ctx.clearRect(0, 0, width, height);

            // A. Clip to Convex Hull
            // Convert Hull coordinates to pixel coordinates
            const hullCoords = hull.geometry.coordinates[0]; // Ring of coordinates
            if (hullCoords.length > 0) {
                ctx.beginPath();
                hullCoords.forEach((coord, index) => {
                    // coord is [lng, lat]
                    const latLng = new L.LatLng(coord[1], coord[0]);
                    const point = this._map.latLngToContainerPoint(latLng);
                    if (index === 0) {
                        ctx.moveTo(point.x, point.y);
                    } else {
                        ctx.lineTo(point.x, point.y);
                    }
                });
                ctx.closePath();
                ctx.clip(); // Restrict drawing to inside the hull
            }

            // B. IDW Interpolation on a Low-Res Grid
            // We draw to a small offscreen canvas then scale it up for performance + smoothing
            const resolution = 0.1; // 1/10th of screen size (e.g., 100x100 for 1000x1000 screen)
            const smallW = Math.ceil(width * resolution);
            const smallH = Math.ceil(height * resolution);

            const offCanvas = document.createElement('canvas');
            offCanvas.width = smallW;
            offCanvas.height = smallH;
            const offCtx = offCanvas.getContext('2d');
            const imgData = offCtx.createImageData(smallW, smallH);
            const pixels = imgData.data;

            const bounds = this._map.getBounds();
            const north = bounds.getNorth();
            const south = bounds.getSouth();
            const east = bounds.getEast();
            const west = bounds.getWest();
            const latSpan = north - south;
            const lngSpan = east - west;

            // Pre-calculate point pixel coordinates for IDW (optimization)
            // Actually, IDW is based on distance. 
            // Distance in pixels is better for visual smoothness in screen space.
            // Distance in LatLng is better for geographic accuracy.
            // Let's use pixel distance for visual consistency with the view.
            const pixelPoints = points.map(p => {
                const pt = this._map.latLngToContainerPoint([p.lat, p.lng]);
                return { x: pt.x * resolution, y: pt.y * resolution, alt: p.alt }; // Scale to small canvas
            });

            // IDW Power
            const p = 2;

            for (let y = 0; y < smallH; y++) {
                for (let x = 0; x < smallW; x++) {
                    let numerator = 0;
                    let denominator = 0;
                    let minDist = Infinity;
                    let closestAlt = 0;

                    for (const point of pixelPoints) {
                        const dx = x - point.x;
                        const dy = y - point.y;
                        const distSq = dx * dx + dy * dy;
                        const dist = Math.sqrt(distSq);

                        if (dist < 0.5) { // Close enough to a point
                            closestAlt = point.alt;
                            minDist = 0;
                            break;
                        }

                        const weight = 1 / Math.pow(dist, p);
                        numerator += weight * point.alt;
                        denominator += weight;
                    }

                    let val;
                    if (minDist === 0) {
                        val = closestAlt;
                    } else {
                        val = numerator / denominator;
                    }

                    // Convert val to Color
                    const ratio = Math.max(0, Math.min(1, (val - minAlt) / (maxAlt - minAlt)));
                    const hue = (1 - ratio) * 240;

                    // HSLA to RGBA conversion (simplified or using helper)
                    // For speed, let's do a simple HSL to RGB conversion or use string style if we weren't manipulating pixel data directly.
                    // Since we are manipulating pixel data, we need RGB.
                    const [r, g, b] = hslToRgb(hue / 360, 1, 0.5);

                    const index = (y * smallW + x) * 4;
                    pixels[index] = r;
                    pixels[index + 1] = g;
                    pixels[index + 2] = b;
                    pixels[index + 3] = 180; // Alpha (0-255), ~0.7 opacity
                }
            }

            offCtx.putImageData(imgData, 0, 0);

            // Draw scaled up offscreen canvas to main canvas
            // The clipping region is already applied to 'ctx'
            ctx.drawImage(offCanvas, 0, 0, smallW, smallH, 0, 0, width, height);
        }
    });

    const layer = new CanvasLayer();
    map.addLayer(layer);

    return {
        layer: layer,
        breaks: [minAlt, (minAlt + maxAlt) / 2, maxAlt],
        palette: [] // Not used by legend anymore since we hardcoded the gradient
    };
};

/**
 * Add a legend to the map
 * @param {L.Map} map 
 * @param {Array} breaks 
 * @returns {L.Control}
 */
window.addLegend = function (map, breaks) {
    const legend = L.control({ position: 'bottomright' });

    legend.onAdd = function (map) {
        const div = L.DomUtil.create('div', 'info legend bg-white p-3 rounded shadow-md text-sm');
        div.style.lineHeight = '1.5';

        div.innerHTML = '<strong class="block mb-2">Altitude (m)</strong>';

        div.innerHTML += `
            <div style="
                background: linear-gradient(to right, blue, cyan, lime, yellow, red);
                height: 10px;
                width: 100%;
                border-radius: 2px;
                margin-bottom: 5px;
            "></div>
            <div class="flex justify-between text-xs text-gray-600">
                <span>${Math.round(breaks[0])}</span>
                <span>${Math.round(breaks[2])}</span>
            </div>
        `;

        return div;
    };

    legend.addTo(map);
    return legend;
};

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
