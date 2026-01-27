// Inicialización de Leaflet para Split-View
// Este script debe cargarse junto con el shortcode

document.addEventListener('DOMContentLoaded', function() {
  const mapDiv = document.getElementById('splitview-map-container');
  if (!mapDiv || !window.L || !mapDiv._leafletMap) return;
  // Botón 'Buscar en esta área'
  const btnBuscar = document.getElementById('btn-buscar-area');
  let moved = false;
  const map = mapDiv._leafletMap;
  if (!map) return;
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
});
