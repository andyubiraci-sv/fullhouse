// --- FUNCIONES LÓGICAS ---

function updateResultCount() {
    const countEl = document.getElementById('splitview-result-count');
    const grid = document.getElementById('inmuebles-grilla');
    if (!countEl || !grid) return;
    const total = grid.children.length;
    countEl.textContent = total === 0 ? 'Cargando...' : total + ' propiedades encontradas';
}

function syncMapWithCards() {
    const mapDiv = document.getElementById('splitview-map-container');
    if (!mapDiv || !mapDiv._leafletMap) return;
    const map = mapDiv._leafletMap;

    if (map._cardMarkers) { map._cardMarkers.forEach(m => map.removeLayer(m)); }
    map._cardMarkers = [];
    const bounds = [];
    
    const cards = document.querySelectorAll('.splitview-card');
    cards.forEach((card, idx) => {
        const precio = card.querySelector('.card-price')?.textContent || '';
        const lat = card.getAttribute('data-lat');
        const lng = card.getAttribute('data-lng');
        if (!lat || !lng) return;

        const markerHtml = `<div class="splitview-pin-style" style="background:#145a37;color:#fff;padding:4px 12px;border-radius:16px;font-weight:700;">${precio}</div>`;
        const icon = L.divIcon({html: markerHtml, className:'splitview-pin', iconSize:[60,32], iconAnchor:[30,32]});
        const coords = [parseFloat(lat), parseFloat(lng)];
        const marker = L.marker(coords, {icon}).addTo(map);
        
        marker._cardIdx = idx;
        map._cardMarkers.push(marker);
        bounds.push(coords);

        // Interacciones vinculadas
        marker.on('mouseover', () => card.classList.add('card-highlight'));
        marker.on('mouseout', () => card.classList.remove('card-highlight'));
        card.addEventListener('mouseenter', () => { marker.setZIndexOffset(1000); });
        card.addEventListener('mouseleave', () => { marker.setZIndexOffset(0); });
        card.addEventListener('click', () => { map.setView(coords, 15); });
    });

    if (bounds.length > 0) {
        map.fitBounds(bounds, {padding: [50, 50]});
    }
}

function initCardCarousels() {
    document.querySelectorAll('.splitview-card .card-carousel').forEach(function(carousel) {
        if (carousel.dataset.initialized) return; // No inicializar dos veces
        const inner = carousel.querySelector('.carousel-inner');
        const imgs = inner ? Array.from(inner.children) : [];
        let idx = 0;
        const show = (n) => {
            idx = Math.max(0, Math.min(imgs.length - 1, n));
            imgs.forEach((img, i) => img.style.display = (i === idx) ? 'block' : 'none');
        };
        show(0);
        carousel.querySelector('.carousel-prev').onclick = (e) => { e.preventDefault(); show(idx - 1); };
        carousel.querySelector('.carousel-next').onclick = (e) => { e.preventDefault(); show(idx + 1); };
        carousel.dataset.initialized = "true";
    });
}

function afterCardsLoaded() {
    syncMapWithCards();
    updateResultCount();
    initCardCarousels();
}

// --- UN SOLO EVENTO DE CARGA PRINCIPAL ---

document.addEventListener('DOMContentLoaded', function() {
    const grid = document.getElementById('inmuebles-grilla');
    const loader = document.getElementById('infinite-loader');

    // 1. Lógica Móvil (Toggle Mapa)
    if (window.innerWidth <= 600) {
        const toggleBtn = document.getElementById('splitview-toggle-map') || document.createElement('button');
        if (!toggleBtn.parentNode) {
            toggleBtn.id = 'splitview-toggle-map';
            toggleBtn.textContent = 'Ver mapa';
            document.querySelector('.main-content-split')?.parentNode.insertBefore(toggleBtn, document.querySelector('.main-content-split'));
        }
        toggleBtn.onclick = function() {
            const isMap = document.querySelector('.map-section').classList.toggle('active');
            document.querySelector('.results-column').classList.toggle('hide-on-mobile');
            toggleBtn.textContent = isMap ? 'Ver lista' : 'Ver mapa';
        };
    }

    // 2. Observador de Cambios (Para cuando AJAX carga más casas)
    if (grid) {
        const observer = new MutationObserver(() => afterCardsLoaded());
        observer.observe(grid, { childList: true });
        afterCardsLoaded(); // Ejecución inicial
    }

    // 3. Scroll Infinito
    if (grid && loader && window.tubihomeShortcodeAjax) {
        let page = 1, loading = false, finished = false;
        const term = grid.getAttribute('data-term');
        const operacion = grid.getAttribute('data-operacion') || '';

        const loadMore = async () => {
            if (loading || finished) return;
            loading = true;
            loader.style.display = 'block';
            try {
                const res = await fetch(`${window.tubihomeShortcodeAjax.ajax_url}?tipo=${term}&page=${page}&operacion=${operacion}`);
                const html = await res.text();
                if (!html.trim() || html.trim() === 'END') {
                    finished = true;
                } else {
                    grid.insertAdjacentHTML('beforeend', html);
                    page++;
                }
            } catch (e) { console.error("Error cargando más inmuebles:", e); }
            loading = false;
            loader.style.display = 'none';
        };

        window.addEventListener('scroll', () => {
            if (window.innerHeight + window.scrollY >= document.body.offsetHeight - 500) loadMore();
        });
        loadMore(); // Carga inicial si está vacío
    }

    // 4. Modales y Amenidades (Simplificado)
    const modal = document.getElementById('modal-amenidades');
    document.getElementById('btn-amenidades')?.addEventListener('click', () => modal.style.display = 'flex');
    document.getElementById('close-modal-amenidades')?.addEventListener('click', () => modal.style.display = 'none');
    
    const amenidades = document.getElementById('amenidades-container');
    if (amenidades) {
        amenidades.innerHTML = '<button class="accordion">Amenidades generales</button><div class="panel">...</div>';
        // Tu lógica de acordeón aquí...
    }
});
