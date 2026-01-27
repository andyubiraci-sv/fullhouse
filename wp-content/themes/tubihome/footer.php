</div> <footer id="colophon" class="site-footer">
        <div class="container footer-container">
            
            <div class="footer-column branding">
                <div class="footer-logo">
                    <?php if ( has_custom_logo() ) { the_custom_logo(); } else { echo '<h3>Tubihome</h3>'; } ?>
                </div>
                <p class="footer-description">
                    Encuentra tu hogar ideal en El Salvador. Conectamos sueños con propiedades de forma segura y moderna.
                </p>
                <div class="footer-socials">
                    <a href="#" aria-label="Facebook"><i class="icon-facebook"></i></a>
                    <a href="#" aria-label="Instagram"><i class="icon-instagram"></i></a>
                    <a href="#" aria-label="WhatsApp"><i class="icon-whatsapp"></i></a>
                </div>
            </div>

            <div class="footer-column">
                <h4 class="footer-title">Navegación</h4>
                <?php if ( is_active_sidebar( 'footer-1' ) ) : ?>
                    <?php dynamic_sidebar( 'footer-1' ); ?>
                <?php else : ?>
                    <ul>
                        <li><a href="/comprar">Comprar</a></li>
                        <li><a href="/alquilar">Alquilar</a></li>
                        <li><a href="/vender">Vende tu propiedad</a></li>
                    </ul>
                <?php endif; ?>
            </div>

            <div class="footer-column">
                <h4 class="footer-title">Soporte</h4>
                <ul>
                    <li><a href="/terminos">Términos y Condiciones</a></li>
                    <li><a href="/privacidad">Política de Privacidad</a></li>
                    <li><a href="/contacto">Contacto</a></li>
                </ul>
            </div>

            <div class="footer-column">
                <h4 class="footer-title">Contáctanos</h4>
                <p>📍 San Salvador, El Salvador</p>
                <p>📞 +503 0000-0000</p>
                <p>✉️ info@tubihome.com</p>
            </div>

        </div>

        <div class="footer-bottom">
            <div class="container bottom-container">
                <p>&copy; <?php echo date('Y'); ?> **Tubihome**. Todos los derechos reservados.</p>
                <p>Hecho con ❤️ en El Salvador</p>
            </div>
        </div>
    </footer>

    <?php wp_footer(); ?>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" id="leaflet-js"></script>
<script>
function initLeafletMap() {
    if (typeof L === 'undefined') return;
    var mapDivId = 'property-map';
    var oldMapDiv = document.getElementById(mapDivId);
    if (oldMapDiv) {
        // Eliminar el div si ya existe
        oldMapDiv.parentNode.removeChild(oldMapDiv);
    }
    // Crear nuevo div para el mapa
    var parent = document.querySelector('.sidebar-box') || document.body;
    var newDiv = document.createElement('div');
    newDiv.id = mapDivId;
    newDiv.className = 'map-placeholder';
    parent.insertBefore(newDiv, parent.firstChild.nextSibling);
    var mapDiv = document.getElementById(mapDivId);
    if (window.propertyMap && typeof window.propertyMap.remove === 'function') {
        window.propertyMap.remove();
        window.propertyMap = null;
    }
    if (mapDiv) {
        mapDiv.style.height = '320px';
        mapDiv.style.borderRadius = '16px';
        var lat = <?php echo json_encode(get_post_meta(get_the_ID(), '_latitud', true) ?: 19.4326); ?>;
        var lng = <?php echo json_encode(get_post_meta(get_the_ID(), '_longitud', true) ?: -99.1332); ?>;
        window.propertyMap = L.map(mapDiv).setView([lat, lng], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap',
            maxZoom: 19
        }).addTo(window.propertyMap);
        L.marker([lat, lng]).addTo(window.propertyMap)
            .bindPopup('Ubicación del inmueble')
            .openPopup();
    }
}
</script>

</body>
</html>
