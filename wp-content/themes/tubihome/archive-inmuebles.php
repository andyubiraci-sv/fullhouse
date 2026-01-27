<?php
/**
 * Plantilla para el Catálogo de Inmuebles (Split-View)
 */

get_header(); ?>

<style>
    /* Estructura Principal tipo Airbnb */
    .main-content-split {
        display: flex;
        height: calc(100vh - 80px); /* Ajusta según tu header */
        overflow: hidden;
    }

    .results-column {
        flex: 0 0 55%;
        overflow-y: auto;
        padding: 20px;
        background: #fff;
    }

    .map-section {
        flex: 0 0 45%;
        position: relative;
        background: #eee;
    }

    #property-map {
        width: 100%;
        height: 100%;
        position: sticky;
        top: 0;
    }

    /* Grilla de tarjetas */
    .inmuebles-grilla {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 20px;
    }

    .splitview-card {
        border: 1px solid #ddd;
        border-radius: 12px;
        overflow: hidden;
        transition: 0.3s;
    }

    .splitview-card:hover { box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
    
    .card-info { padding: 15px; }
    
    .price { color: #145a37; font-weight: bold; font-size: 1.2rem; }

    @media (max-width: 768px) {
        .main-content-split { flex-direction: column; height: auto; }
        .results-column, .map-section { flex: 0 0 100%; height: 50vh; }
    }
</style>

<div class="main-content-split">
    <div class="results-column">
        <h1 class="tipo-title">Catálogo de Inmuebles</h1>
        
        <div class="inmuebles-grilla" id="inmuebles-grilla">
            <?php
            // CONSULTA LIMPIA: Solo publicados, sin borradores
            $args = array(
                'post_type'      => 'inmueble',
                'post_status'    => 'publish',
                'posts_per_page' => 12,
            );
            $query = new WP_Query($args);

            if ($query->have_posts()) :
                while ($query->have_posts()) : $query->the_post();
                    // Obtener coordenadas de los campos meta
                    $lat = get_post_meta(get_the_ID(), '_at_lat', true);
                    $lng = get_post_meta(get_the_ID(), '_at_lng', true);
                    $precio = get_post_meta(get_the_ID(), '_at_precio', true);
                    ?>
                    
                    <article class="splitview-card" 
                             data-lat="<?php echo esc_attr($lat); ?>" 
                             data-lng="<?php echo esc_attr($lng); ?>" 
                             data-price="<?php echo esc_attr($precio); ?>">
                        
                        <?php if (has_post_thumbnail()) : ?>
                            <div class="card-img"><?php the_post_thumbnail('medium'); ?></div>
                        <?php endif; ?>

                        <div class="card-info">
                            <h3><?php the_title(); ?></h3>
                            <p class="price">$<?php echo number_format($precio); ?></p>
                            <a href="<?php the_permalink(); ?>" class="btn-view">Ver Detalles</a>
                        </div>
                    </article>

                <?php endwhile;
                wp_reset_postdata();
            else :
                echo '<p>No se encontraron inmuebles disponibles.</p>';
            endif;
            ?>
        </div>
    </div>

    <section class="map-section">
        <div id="property-map"></div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar mapa centrado en El Salvador por defecto
    var map = L.map('property-map').setView([13.6929, -89.2182], 12);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    var bounds = L.latLngBounds();
    var hasMarkers = false;

    // Leer las tarjetas para poner los pines
    document.querySelectorAll('.splitview-card').forEach(function(card) {
        var lat = parseFloat(card.dataset.lat);
        var lng = parseFloat(card.dataset.lng);
        var title = card.querySelector('h3').innerText;

        if (lat && lng) {
            var marker = L.marker([lat, lng]).addTo(map);
            marker.bindPopup('<b>' + title + '</b><br><a href="'+card.querySelector('a').href+'">Ver más</a>');
            bounds.extend([lat, lng]);
            hasMarkers = true;
        }
    });

    // Ajustar mapa si hay inmuebles, si no, usar el setTimeout para corregir tamaño
    if (hasMarkers) {
        map.fitBounds(bounds, { padding: [50, 50] });
    }

    setTimeout(function() {
        map.invalidateSize();
    }, 600);
});
</script>

<?php get_footer(); ?>