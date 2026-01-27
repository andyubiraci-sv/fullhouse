// Mapa de inmuebles estilo Airbnb
// Requiere Leaflet y un contenedor #splitview-map-container

document.addEventListener('DOMContentLoaded', function() {
  const mapDiv = document.getElementById('splitview-map-container');
  if (!mapDiv) return;
  if (!window.L) return;
  // Centrar en Ciudad de México para pruebas
  const map = L.map(mapDiv, { zoomControl: true }).setView([19.4326, -99.1332], 11);
  map.zoomControl.setPosition('topleft');
  L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
    attribution: '&copy; OpenStreetMap contributors & CartoDB',
    maxZoom: 18,
  }).addTo(map);

  // Agrupador de marcadores y ajuste automático
  const markersGroup = L.featureGroup();
  const cards = document.querySelectorAll('.inmueble-card');
  cards.forEach(card => {
    const lat = parseFloat(card.getAttribute('data-lat'));
    const lng = parseFloat(card.getAttribute('data-lng'));
    const price = card.getAttribute('data-price') || '';
    const title = card.querySelector('h3')?.textContent || '';
    const link = card.querySelector('a')?.href || '#';
    if (lat && lng) {
      // Crear un icono que contiene el precio
      const priceIcon = L.divIcon({
        className: 'custom-price-marker',
        html: `<span>$${price}</span>`,
        iconSize: [60, 30],
        iconAnchor: [30, 15]
      });
      const marker = L.marker([lat, lng], { icon: priceIcon })
        .bindPopup(`
          <div style="width:150px">
            <h4 style="margin:0; font-size:14px;">${title}</h4>
            <a href="${link}" style="color:#145a37; font-size:12px;">Ver detalles</a>
          </div>
        `);
      markersGroup.addLayer(marker);
      // Sincronizar hover
      card.addEventListener('mouseenter', () => marker.openPopup());
      card.addEventListener('mouseleave', () => marker.closePopup());
    }
  });
  markersGroup.addTo(map);
  // Ajuste automático de vista
  if (cards.length > 0 && markersGroup.getLayers().length > 0) {
    map.fitBounds(markersGroup.getBounds(), { padding: [50, 50] });
  }
});
