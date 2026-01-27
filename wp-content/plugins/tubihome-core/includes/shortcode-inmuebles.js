// Actualiza el contador de resultados en Split-View
function updateResultCount() {
    const countEl = document.getElementById('splitview-result-count');
    const grid = document.getElementById('inmuebles-grilla');
    if (!countEl || !grid) return;
    const total = grid.children.length;
    if (total === 0) {
        countEl.textContent = 'Cargando...';
    } else {
        countEl.textContent = total + ' propiedades encontradas';
    }
}

// Hook: actualizar contador tras cargar inmuebles
document.addEventListener('DOMContentLoaded', function() {
    // --- Alternar mapa/lista en móvil ---
    if (window.innerWidth <= 600) {
        // Crear botón para alternar
        let toggleBtn = document.getElementById('splitview-toggle-map');
        if (!toggleBtn) {
            toggleBtn = document.createElement('button');
            toggleBtn.id = 'splitview-toggle-map';
            toggleBtn.textContent = 'Ver mapa';
            const main = document.querySelector('.main-content-split');
            if (main) main.parentNode.insertBefore(toggleBtn, main);
        }
        const mapSection = document.querySelector('.map-section');
        const resultsCol = document.querySelector('.results-column');
        let mapVisible = false;
        function updateView() {
            if (mapVisible) {
                mapSection?.classList.add('active');
                resultsCol?.classList.add('hide-on-mobile');
                toggleBtn.textContent = 'Ver lista';
            } else {
                mapSection?.classList.remove('active');
                resultsCol?.classList.remove('hide-on-mobile');
                toggleBtn.textContent = 'Ver mapa';
            }
        }
        toggleBtn.onclick = function() {
            mapVisible = !mapVisible;
            updateView();
        };
        updateView();
    }
    const grid = document.getElementById('inmuebles-grilla');
    if (grid) {
        const observer = new MutationObserver(function(){
            initCardCarousels();
            afterCardsLoaded();
            updateResultCount();
        });
        observer.observe(grid, {childList:true});
        updateResultCount();
    }
});
// Sincronización de pines y tarjetas Split-View
function syncMapWithCards() {
    const mapDiv = document.getElementById('splitview-map-container');
    if (!mapDiv || !mapDiv._leafletMap) return;
    const map = mapDiv._leafletMap;
    // Elimina pines previos
    if (map._cardMarkers) { map._cardMarkers.forEach(m => map.removeLayer(m)); }
    map._cardMarkers = [];
    const cards = document.querySelectorAll('.splitview-card');
    cards.forEach((card, idx) => {
        // Extraer datos
        const precio = card.querySelector('.card-price')?.textContent || '';
        const titulo = card.querySelector('.card-title')?.textContent || '';
        const lat = card.getAttribute('data-lat');
        const lng = card.getAttribute('data-lng');
        if (!lat || !lng) return;
        // Crear pin personalizado con precio
        const markerHtml = `<div style="background:#145a37;color:#fff;padding:4px 12px;border-radius:16px;font-weight:700;font-size:1rem;box-shadow:0 2px 8px 0 rgba(20,90,55,0.10);">${precio}</div>`;
        const icon = L.divIcon({html: markerHtml, className:'splitview-pin', iconSize:[60,32], iconAnchor:[30,32]});
        const marker = L.marker([parseFloat(lat),parseFloat(lng)], {icon}).addTo(map);
        marker._cardIdx = idx;
        map._cardMarkers.push(marker);
        // Interacción: hover/click
        marker.on('mouseover', function(){
            card.classList.add('card-highlight');
        });
        marker.on('mouseout', function(){
            card.classList.remove('card-highlight');
        });
        marker.on('click', function(){
            card.scrollIntoView({behavior:'smooth',block:'center'});
            card.classList.add('card-highlight');
            setTimeout(()=>card.classList.remove('card-highlight'),1200);
        });
        card.addEventListener('mouseenter', function(){ marker.setZIndexOffset(1000); marker.openPopup && marker.openPopup(); });
        card.addEventListener('mouseleave', function(){ marker.setZIndexOffset(0); marker.closePopup && marker.closePopup(); });
        card.addEventListener('click', function(){ map.setView([parseFloat(lat),parseFloat(lng)], 15); });
    });
}

// Recolectar lat/lng de cada tarjeta al cargar (debe estar en el HTML)
function injectLatLngToCards(dataArr) {
    const cards = document.querySelectorAll('.splitview-card');
    cards.forEach((card, idx) => {
        if (dataArr[idx]) {
            card.setAttribute('data-lat', dataArr[idx].lat);
            card.setAttribute('data-lng', dataArr[idx].lng);
        }
    });
}

// Hook: después de cargar inmuebles, sincronizar mapa
function afterCardsLoaded() {
    // Aquí deberías obtener los datos de lat/lng de los inmuebles vía AJAX
    // Por ahora, simula con datos de ejemplo
    // Ejemplo: [{lat:13.7,lng:-89.2}, ...]
    // TODO: Reemplazar con datos reales
    const dummy = Array.from(document.querySelectorAll('.splitview-card')).map((c,i)=>({lat:13.7+0.01*i,lng:-89.2+0.01*i}));
    injectLatLngToCards(dummy);
    syncMapWithCards();
}

document.addEventListener('DOMContentLoaded', function() {
    // ...existing code...
    // Al cargar más tarjetas, sincronizar mapa
    const grid = document.getElementById('inmuebles-grilla');
    if (grid) {
        const observer = new MutationObserver(function(){
            initCardCarousels();
            afterCardsLoaded();
        });
        observer.observe(grid, {childList:true});
        afterCardsLoaded();
    }
});
// Carrusel de imágenes en tarjetas Split-View
function initCardCarousels() {
    document.querySelectorAll('.splitview-card .card-carousel').forEach(function(carousel) {
        const inner = carousel.querySelector('.carousel-inner');
        const imgs = inner ? Array.from(inner.children) : [];
        let idx = 0;
        function show(idxNew) {
            idx = Math.max(0, Math.min(imgs.length-1, idxNew));
            imgs.forEach((img, i) => {
                img.style.display = (i === idx) ? 'block' : 'none';
            });
        }
        show(0);
        const prev = carousel.querySelector('.carousel-prev');
        const next = carousel.querySelector('.carousel-next');
        if (prev) prev.onclick = function(e){ e.preventDefault(); show(idx-1); };
        if (next) next.onclick = function(e){ e.preventDefault(); show(idx+1); };
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // ...existing code...
    // Al cargar más tarjetas, inicializar carruseles
    const observer = new MutationObserver(function(){
        initCardCarousels();
    });
    const grid = document.getElementById('inmuebles-grilla');
    if (grid) observer.observe(grid, {childList:true});
    initCardCarousels();
});
// JS para scroll infinito de inmuebles por tipo de propiedad (shortcode)
document.addEventListener('DOMContentLoaded', function() {
    var grid = document.getElementById('inmuebles-grilla');
    var loader = document.getElementById('infinite-loader');
    if (!grid || !loader || !window.tubihomeShortcodeAjax) return;
    let page = 1;
    let loading = false;
    let finished = false;
    const term = grid.getAttribute('data-term');
    const operacion = grid.getAttribute('data-operacion') || (window.tubihomeShortcodeOperacion || '');
    const ajaxUrl = window.tubihomeShortcodeAjax.ajax_url;
    async function loadMore() {
        if (loading || finished) return;
        loading = true;
        loader.style.display = 'block';
        let url = ajaxUrl + '?tipo=' + encodeURIComponent(term) + '&page=' + page;
        if (operacion) url += '&operacion=' + encodeURIComponent(operacion);
        const res = await fetch(url);
        const html = await res.text();
        if (html.trim() === '' || html.trim() === 'END') {
            finished = true;
            loader.style.display = 'none';
            return;
        }
        const temp = document.createElement('div');
        temp.innerHTML = html;
        Array.from(temp.children).forEach(card => grid.appendChild(card));
        page++;
        loading = false;
        loader.style.display = 'none';
    }
    function onScroll() {
        if (finished) return;
        const scrollY = window.scrollY || window.pageYOffset;
        const viewport = window.innerHeight;
        const full = document.body.offsetHeight;
        if (scrollY + viewport > full - 400) {
            loadMore();
        }
    }
    window.addEventListener('scroll', onScroll);
    loadMore();
});

// Modal Amenidades
var btnAmenidades = document.getElementById('btn-amenidades');
var modalAmenidades = document.getElementById('modal-amenidades');
var closeModal = document.getElementById('close-modal-amenidades');
if (btnAmenidades && modalAmenidades && closeModal) {
    btnAmenidades.onclick = function() {
        modalAmenidades.style.display = 'flex';
    };
    closeModal.onclick = function() {
        modalAmenidades.style.display = 'none';
    };
    window.addEventListener('click', function(e) {
        if (e.target === modalAmenidades) {
            modalAmenidades.style.display = 'none';
        }
    });
}
// Acordeón para amenidades
var amenidades = document.getElementById('amenidades-container');
if (amenidades) {
    // Ejemplo de acordeón: puedes reemplazar esto por tu contenido dinámico
    amenidades.innerHTML = '<button class="accordion">Amenidades generales</button><div class="panel"><label><input type="checkbox"> Piscina</label><br><label><input type="checkbox"> Jardín</label><br><label><input type="checkbox"> Gimnasio</label></div>';
    var acc = amenidades.getElementsByClassName('accordion');
    for (var i = 0; i < acc.length; i++) {
        acc[i].onclick = function() {
            this.classList.toggle('active');
            var panel = this.nextElementSibling;
            if (panel.classList.contains('show')) {
                panel.classList.remove('show');
            } else {
                panel.classList.add('show');
            }
        };
    }
})
