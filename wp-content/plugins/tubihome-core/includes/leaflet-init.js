// Inicialización de Leaflet para Split-View
// Este script debe cargarse junto con el shortcode

document.addEventListener('DOMContentLoaded', function() {
  const mapDiv = document.getElementById('splitview-map-container');
  if (!mapDiv) return;
  // Evita doble inicialización
  if (mapDiv.dataset.leafletInitialized) return;
  mapDiv.dataset.leafletInitialized = '1';
  // Cargar Leaflet CSS y JS si no están presentes
  if (!window.L) {
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
    document.head.appendChild(link);
    const script = document.createElement('script');
    script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
    script.onload = function() { initMap(mapDiv); };
    document.body.appendChild(script);
  } else {
    initMap(mapDiv);
  }

  function initMap(mapDiv) {
    // Centrar en El Salvador por defecto
    const map = L.map(mapDiv, { zoomControl: true }).setView([13.7, -89.2], 11);
    map.zoomControl.setPosition('topleft');
    // Capa visual moderna tipo CartoDB Positron
    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
      attribution: '&copy; OpenStreetMap contributors & CartoDB',
      maxZoom: 18,
    }).addTo(map);
    // Guarda la instancia del mapa en el div
    mapDiv._leafletMap = map;
    // Botón 'Buscar en esta área'
    const btnBuscar = document.getElementById('btn-buscar-area');
    let moved = false;
    map.on('moveend zoomend', function() {
      moved = true;
      if (btnBuscar) btnBuscar.style.display = 'block';
    });
    if (btnBuscar) {
      btnBuscar.onclick = function() {
        const bounds = map.getBounds();
        const ne = bounds.getNorthEast();
        const sw = bounds.getSouthWest();
        btnBuscar.style.display = 'none';
        moved = false;
        // Actualizar inmuebles por AJAX
        const grid = document.getElementById('inmuebles-grilla');
        if (!grid) return;
        grid.innerHTML = '';
        const term = grid.getAttribute('data-term');
        const operacion = grid.getAttribute('data-operacion') || '';
        let url = window.tubihomeShortcodeAjax.ajax_url + '?tipo=' + encodeURIComponent(term) + '&page=1';
        if (operacion) url += '&operacion=' + encodeURIComponent(operacion);
        url += '&ne_lat=' + encodeURIComponent(ne.lat) + '&ne_lng=' + encodeURIComponent(ne.lng);
        url += '&sw_lat=' + encodeURIComponent(sw.lat) + '&sw_lng=' + encodeURIComponent(sw.lng);
        fetch(url).then(res => res.text()).then(html => {
          if (html.trim() === '' || html.trim() === 'END') return;
          const temp = document.createElement('div');
          temp.innerHTML = html;
          Array.from(temp.children).forEach(card => grid.appendChild(card));
        });
      };
    }
  }
});
