<?php
/**
 * Plantilla optimizada para el detalle de un Inmueble
 */

get_header(); 

// 1. Cargamos Dashicons de WordPress (por si el tema no los carga por defecto)
wp_enqueue_style('dashicons');
?>

<style>
    .single-property-container { max-width: 1200px; margin: 40px auto; padding: 0 20px; font-family: 'Segoe UI', Roboto, sans-serif; }
    .property-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 40px; }
    
    /* Galería */
    .property-main-image img { width: 100%; height: 500px; object-fit: cover; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
    
    /* Header e Info */
    .property-header { margin-top: 30px; padding-bottom: 20px; border-bottom: 1px solid #eee; }
    .price-tag { font-size: 2.5rem; font-weight: 800; color: #145a37; margin: 10px 0; }
    
    /* Iconos de Detalles */
    .property-details-bar { display: flex; flex-wrap: wrap; gap: 20px; margin: 25px 0; padding: 20px; background: #f8f9fa; border-radius: 12px; }
    .detail-item { display: flex; align-items: center; gap: 8px; font-weight: 600; color: #444; }
    .detail-item .dashicons { color: #145a37; font-size: 20px; width: 20px; height: 20px; }

    /* Sidebar y Mapa */
    .sidebar-box {
        position: relative; /* FUNDAMENTAL */
        width: 100%;
    }
    #property-map {
        height: 350px; /* Dale una altura fija */
        width: 100% !important;
        border-radius: 12px;
        overflow: hidden; /* Evita que las capas de Leaflet se asomen */
        position: relative;
    }

    @media (max-width: 992px) { 
        .property-grid { grid-template-columns: 1fr; } 
        .property-main-image img { height: 300px; }
        .sidebar-box { position: static; margin-top: 20px; }
    }
</style>

<main class="single-property-container">
    <?php while (have_posts()) : the_post(); 
            $post_id = get_the_ID();
            // Metadatos reales: probar variantes de nombres
            $lat = get_post_meta($post_id, '_geo_lat', true);
            $lng = get_post_meta($post_id, '_geo_lng', true);
            $precio = get_post_meta($post_id, '_price', true);
            $operacion_terms = wp_get_object_terms($post_id, 'tipo-operacion', ['fields'=>'names']);
            $operacion = !empty($operacion_terms) ? implode(', ', $operacion_terms) : '';
            $tipo_terms = wp_get_object_terms($post_id, 'tipo-propiedad', ['fields'=>'names']);
            $tipo = !empty($tipo_terms) ? implode(', ', $tipo_terms) : '';

            // Amenidades: probar variantes de nombres
            $habitaciones = get_post_meta($post_id, '_rooms', true);
            if (!$habitaciones) $habitaciones = get_post_meta($post_id, '_recamaras', true);
            if (!$habitaciones) $habitaciones = get_post_meta($post_id, '_at_rooms', true);

            $banos = get_post_meta($post_id, '_baths', true);
            if (!$banos) $banos = get_post_meta($post_id, '_banos', true);
            if (!$banos) $banos = get_post_meta($post_id, '_at_baths', true);

            $area = get_post_meta($post_id, '_area_total', true);
            if (!$area) $area = get_post_meta($post_id, '_at_area_total', true);

            $estacionamiento = get_post_meta($post_id, '_parking', true);
            if (!$estacionamiento) $estacionamiento = get_post_meta($post_id, '_at_parking', true);
        ?>
        
        <div class="property-grid">
            
            <div class="property-content">
                <div class="property-main-image">
                    <?php if (has_post_thumbnail()) : the_post_thumbnail('full'); else: ?>
                        <div style="width:100%; height:400px; background:#eee; border-radius:20px; display:flex; align-items:center; justify-content:center;">Sin Imagen</div>
                    <?php endif; ?>
                </div>

                <div class="property-header">
                    <span style="background:#145a37; color:#fff; padding:6px 16px; border-radius:50px; font-size:12px; font-weight:bold; text-transform:uppercase; letter-spacing: 1px;">
                        <?php echo esc_js($tipo); ?> en <?php echo esc_js($operacion); ?>
                    </span>
                    <h1 style="margin-top:15px;"><?php the_title(); ?></h1>
                    <p class="price-tag">$<?php echo is_numeric($precio) ? number_format((float)$precio) : $precio; ?></p>
                </div>

                <div class="property-details-bar">
                    <?php if($habitaciones): ?>
                        <div class="detail-item"><span class="dashicons dashicons-admin-home"></span> <?php echo $habitaciones; ?> Hab.</div>
                    <?php endif; ?>
                    <?php if($banos): ?>
                        <div class="detail-item"><span class="dashicons dashicons-id-alt"></span> <?php echo $banos; ?> Baños</div>
                    <?php endif; ?>
                    <?php if($area): ?>
                        <div class="detail-item"><span class="dashicons dashicons-move"></span> <?php echo $area; ?> m²</div>
                    <?php endif; ?>
                    <?php if($estacionamiento): ?>
                        <div class="detail-item"><span class="dashicons dashicons-car"></span> <?php echo $estacionamiento; ?> Estac.</div>
                    <?php endif; ?>
                </div>

                <div class="property-description">
                    <h2 style="font-size:1.6rem; margin-bottom:15px; color:#222;">Sobre esta propiedad</h2>
                    <div style="line-height:1.8; color:#444; font-size:1.05rem;">
                        <?php the_content(); ?>
                    </div>
                </div>
            </div>

            <aside class="property-sidebar">
                <div class="sidebar-box">
                    <h3">Ubicación</h3>
                    <div id="property-map"></div>
                    
                    <div style="margin-top:25px;">
                        <p style="font-size:14px; color:#666; margin-bottom:15px;">¿Deseas visitar esta propiedad?</p>
                        <a href="https://wa.me/503XXXXXXXX" target="_blank" style="display:block; text-align:center; background:#25D366; color:white; padding:16px; border-radius:12px; text-decoration:none; font-weight:bold; font-size:1.1rem; transition: 0.3s; box-shadow: 0 4px 12px rgba(37,211,102,0.3);">
                           Enviar WhatsApp
                        </a>
                    </div>
                </div>
            </aside>

        </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    /**
     * 1. CONFIGURACIÓN DE DATOS
     * Usamos las variables de PHP. Si no existen, por defecto El Salvador.
     */
    var lat = <?php echo !empty($lat) ? esc_js($lat) : '13.6929'; ?>;
    var lng = <?php echo !empty($lng) ? esc_js($lng) : '-89.2182'; ?>;
    var title = "<?php echo esc_js(get_the_title()); ?>";

    // Función principal para inicializar el mapa
    function renderPropertyMap() {
        var mapContainer = document.getElementById('property-map');

        // Verificamos que el div exista y que Leaflet esté cargado
        if (!mapContainer || typeof L === 'undefined') {
            console.error('No se encontró el contenedor #property-map o Leaflet no está cargado.');
            return;
        }

        // 2. LIMPIEZA
        // Si ya hay un mapa instanciado en la ventana, lo removemos para evitar el error "Map container is already initialized"
        if (window.propertyMap) {
            window.propertyMap.remove();
        }

        // 3. INICIALIZACIÓN
        // Desactivamos scrollWheelZoom para que el usuario no se quede "atrapado" haciendo scroll
        window.propertyMap = L.map('property-map', {
            scrollWheelZoom: false,
            dragging: !L.Browser.mobile, // Opcional: desactivar arrastre en móviles para facilitar el scroll
            tap: !L.Browser.mobile
        }).setView([lat, lng], 15);

        // 4. CAPA DE MAPA (Tiles)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(window.propertyMap);

        // 5. MARCADOR
        var marker = L.marker([lat, lng]).addTo(window.propertyMap);
        marker.bindPopup("<b>" + title + "</b>").openPopup();

        // 6. EL TRUCO PARA EL POSICIONAMIENTO
        // Forzamos a Leaflet a recalcular el tamaño del div después de que el CSS se haya asentado
        setTimeout(function() {
            window.propertyMap.invalidateSize();
            console.log('Mapa reajustado correctamente',marker);
        }, 500);
    }

    // Ejecutamos la función
    renderPropertyMap();

    // Extra: Si cambias el tamaño de la ventana, el mapa se ajusta solo
    window.addEventListener('resize', function() {
        if (window.propertyMap) {
            window.propertyMap.invalidateSize();
        }
    });
});
</script>
    <?php endwhile; ?>
</main>

<?php get_footer(); ?>
