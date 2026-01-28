<?php
/**
 * Plantilla Final: Galería Moderna + Mapa en Sidebar
 */

get_header(); 
wp_enqueue_style('dashicons');
?>

<style>
    .single-property-container { max-width: 1200px; margin: 40px auto; padding: 0 20px; font-family: 'Segoe UI', Roboto, sans-serif; }
    .property-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 40px; }
    
    /* Galería Moderna */
    .gallery-hero-modern { position: relative; margin-bottom: 30px; }
    .gallery-hero-main { position: relative; overflow: hidden; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
    #gallery-hero-img { width: 100%; height: 450px; object-fit: cover; transition: transform .3s, opacity .3s; cursor: pointer; }
    .gallery-hero-badges { position: absolute; top: 15px; left: 15px; z-index: 10; display: flex; gap: 10px; }
    .badge-op { background: #145a37; color: #fff; padding: 6px 15px; border-radius: 50px; font-size: 12px; font-weight: bold; text-transform: uppercase; }
    .badge-price { background: #fff; color: #145a37; padding: 6px 15px; border-radius: 50px; font-size: 14px; font-weight: 800; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    
    .gallery-hero-thumbs { display: flex; gap: 10px; margin-top: 15px; overflow-x: auto; padding-bottom: 10px; }
    .gallery-thumb { width: 80px; height: 80px; object-fit: cover; border-radius: 10px; cursor: pointer; border: 3px solid #fff; transition: 0.2s; opacity: 0.6; }
    .gallery-thumb.active { opacity: 1; border-color: #145a37; }

    /* Info y Detalles */
    .property-header { margin-top: 20px; padding-bottom: 20px; border-bottom: 1px solid #eee; }
    .property-details-bar { display: flex; flex-wrap: wrap; gap: 20px; margin: 25px 0; padding: 20px; background: #f8f9fa; border-radius: 12px; }
    .detail-item { display: flex; align-items: center; gap: 8px; font-weight: 600; color: #444; }
    .detail-item .dashicons { color: #145a37; }

    /* Sidebar Mapa */
    .sidebar-box { background: white; border: 1px solid #eee; border-radius: 20px; padding: 0px; position: sticky; top: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
  
    @media (max-width: 992px) { .property-grid { grid-template-columns: 1fr; } #gallery-hero-img { height: 300px; } }
</style>
<style>
    /* Contenedor principal de la galería estilo Amazon */
    .amazon-gallery-container {
        display: flex;
        gap: 0px;
        margin-bottom: 30px;
        height: 50vh; /* Altura fija para alinear ambos componentes */
    }

    /* Columna de miniaturas (Izquierda) */
    .amazon-thumbs-column {
        flex: 0 0 80px; /* Ancho fijo para las miniaturas */
        display: flex;
        flex-direction: column;
        gap: 10px;
        overflow-y: auto;
        padding-right: 5px;
    }

    /* Ocultar scrollbar pero mantener funcionalidad */
    .amazon-thumbs-column::-webkit-scrollbar { width: 4px; }
    .amazon-thumbs-column::-webkit-scrollbar-thumb { background: #ccc; border-radius: 4px; }

    .amazon-thumb {
        width: 70px;
        height: 70px;
        object-fit: cover;
        border: 1px solid #ddd;
        border-radius: 4px;
        cursor: pointer;
        transition: 0.2s;
        opacity: 0.7;
    }

    .amazon-thumb:hover, .amazon-thumb.active {
        border: 2px solid #e77600; /* Naranja Amazon */
        opacity: 1;
        box-shadow: 0 0 3px rgba(231, 118, 0, 0.5);
    }

    /* Visor principal (Derecha) */
    .amazon-main-view {
        flex: 1;
        position: relative;
        border: 1px solid #eee;
        border-radius: 20px;
        overflow: hidden;
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    #amazon-main-img {
       
        max-height: 50vh;
        object-fit: contain; /* Para que no se corte la imagen como en Amazon */
        transition: opacity 0.3s;
    }

    .amazon-badges {
        position: absolute;
        top: 15px;
        right: 15px;
        z-index: 5;
        display: flex;
        flex-direction: column;
        gap: 0px;
    }

    @media (max-width: 768px) {
        .amazon-gallery-container { flex-direction: column-reverse; height: auto; }
        .amazon-thumbs-column { flex-direction: row; flex: 0 0 auto; overflow-x: auto; }
        #amazon-main-img { height: 300px; }
    }
</style>
<main class="single-property-container">
    <?php while (have_posts()) : the_post(); 
        $post_id = get_the_ID();
        
        // Carga de Metadatos
        $lat = get_post_meta($post_id, '_geo_lat', true);
        $lng = get_post_meta($post_id, '_geo_lng', true);
        $precio = get_post_meta($post_id, '_price', true);

        // Taxonomías
          $operacion =wp_get_object_terms($post_id, 'tipo-operacion', ['fields'=>'names']);
       // $operacion = !empty($op_name) ? $op_name[0] : '';
        
        // Preparar Galería
        $gallery_raw = get_post_meta($post_id, '_gallery', true);
        $gallery_ids = $gallery_raw ? explode(',', $gallery_raw) : [];
        if (has_post_thumbnail()) { array_unshift($gallery_ids, get_post_thumbnail_id()); }
        $gallery_ids = array_unique(array_filter($gallery_ids));
        
        $gallery_data = [];
        foreach ($gallery_ids as $id) {
            $url = wp_get_attachment_image_url($id, 'large');
            if ($url) $gallery_data[] = $url;
        }
    ?>
        
        <div class="property-grid">
            <div class="property-content">
                
                <?php if (!empty($gallery_data)): ?>
<div class="amazon-gallery-container">
    <div class="amazon-thumbs-column">
        <?php foreach ($gallery_data as $i => $url): ?>
            <img src="<?php echo esc_url($url); ?>" 
                 class="amazon-thumb <?php echo $i === 0 ? 'active' : ''; ?>" 
                 onmouseover="updateAmazonPhoto('<?php echo esc_url($url); ?>', this)"
                 onclick="updateAmazonPhoto('<?php echo esc_url($url); ?>', this)"
                 alt="Miniatura">
        <?php endforeach; ?>
    </div>

    <div class="amazon-main-view">
        <div class="amazon-badges">
                <?php
                // Imprimir todas las variables del array $operacion
                if (!empty($operacion) && is_array($operacion)) {
                    foreach ($operacion as $op) {
                        echo '<span class="badge-op" style="background:#e77600;">' . esc_html($op) . '</span><br>';
                    }
                } else {
                    echo '<span class="badge-op" style="background:#e77600;">' . esc_html($operacion) . '</span>';
                }
                ?>
                     <span class="badge-price">$<?php echo is_numeric($precio) ? number_format((float)$precio) : $precio; ?></span>
        </div>
        <div style="position:absolute; top:15px; left:15px; z-index:10;">
            <button id="zoom-btn" style="background:rgba(0,0,0,0.6); color:white; border:none; border-radius:50%; width:38px; height:38px; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:20px;">
                <span class="dashicons dashicons-search"></span>
            </button>
        </div>
        <img id="amazon-main-img" src="<?php echo esc_url($gallery_data[0]); ?>" alt="Producto principal" style="cursor:zoom-in;">

        <!-- Lightbox -->
        <div id="lightbox-modal" style="display:none; position:fixed; z-index:9999; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.85); align-items:center; justify-content:center;">
            <span id="lightbox-close" style="position:absolute; top:30px; right:40px; color:white; font-size:40px; cursor:pointer;">&times;</span>
            <img id="lightbox-img" src="" style="max-width:90vw; max-height:90vh; box-shadow:0 0 30px #000; border-radius:10px;">
        </div>
    </div>
</div>

               
                <?php endif; ?>

                <div class="property-header">
                    <h1><?php the_title(); ?></h1>
 <div>
                        <?php 
                        $price_raw = get_post_meta($post->ID, '_price', true);
                            $price_num = is_numeric($price_raw) ? (float)$price_raw : 0;
                            $price_k = $price_num >= 1000 ? number_format($price_num / 1000, 0) . 'k' : number_format($price_num, 0);
                            
                         $area_post = get_post_meta($post->ID, '_area_total', true);
                            $aprox_m2 = is_numeric($area_post) ? number_format((float)$area_post) . ' m²' : 'N/A';
                            $valor_m2 = $area_post > 0 ? '$' . number_format($price_num / (float)$area_post, 2) . ' / m²' : 'N/A';
                           

                       $distrito   = get_post_meta($post->ID, 'distrito', true);
$distrito = $distrito ? $distrito : 'Sin distrito';
            echo "<bold>" . esc_html($distrito) . "</bold>";
        
$localidad   = get_post_meta($post->ID, 'municipio', true);
$localidad = $localidad ? $localidad : 'Sin localidad';
            echo "<bold>" . esc_html($localidad) . "</bold>";
                        ?>
                        <div class="splitview-microdetalles">
                                    <span class="microdetalles-chip"> 
   Superficie: <?php echo esc_html($aprox_m2); ?>

                                    </span>
<span class="microdetalles-chip">
 Valor Unitario: <?php echo esc_html($valor_m2); ?>
</span>

                                 
                                </div>
                </div>

                </div>

                <div class="property-details-bar">
                    <?php 
                    $rooms = get_post_meta($post_id, '_rooms', true);
                    $baths = get_post_meta($post_id, '_baths', true);
                    $area  = get_post_meta($post_id, '_area_total', true);
                    ?>
                    <?php if($rooms): ?><div class="detail-item"><span class="dashicons dashicons-admin-home"></span> <?php echo $rooms; ?> Hab.</div><?php endif; ?>
                    <?php if($baths): ?><div class="detail-item"><span class="dashicons dashicons-id-alt"></span> <?php echo $baths; ?> Baños</div><?php endif; ?>
                    <?php if($area): ?><div class="detail-item"><span class="dashicons dashicons-move"></span> <?php echo $area; ?> m²</div><?php endif; ?>
                </div>

                <div class="property-description">
                    <h2>Descripción</h2>
                    <?php the_content(); ?>
                </div>
            </div>

            <aside class="property-sidebar">
                <div class="sidebar-box">
                  
                    <div id="property-map"></div>
                   
                </div>
            </aside>
        </div>

        <script>


            function updateAmazonPhoto(url, thumbElement) {
                const mainImg = document.getElementById('amazon-main-img');
                // Si la imagen ya es la misma, no hacer nada
                if (mainImg.src === url) return;
                mainImg.style.opacity = '0.5';
                setTimeout(() => {
                    mainImg.src = url;
                    mainImg.style.opacity = '1';
                }, 150);
                // Actualizar clase activa en miniaturas
                document.querySelectorAll('.amazon-thumb').forEach(t => t.classList.remove('active'));
                thumbElement.classList.add('active');
                // Actualizar imagen de lightbox
                document.getElementById('lightbox-img').src = url;
            }

            // Lightbox funcionalidad
            document.addEventListener('DOMContentLoaded', function() {
                const zoomBtn = document.getElementById('zoom-btn');
                const mainImg = document.getElementById('amazon-main-img');
                const lightbox = document.getElementById('lightbox-modal');
                const lightboxImg = document.getElementById('lightbox-img');
                const lightboxClose = document.getElementById('lightbox-close');

                function openLightbox() {
                    lightboxImg.src = mainImg.src;
                    lightbox.style.display = 'flex';
                }
                function closeLightbox() {
                    lightbox.style.display = 'none';
                }
                if (zoomBtn) zoomBtn.addEventListener('click', openLightbox);
                if (mainImg) mainImg.addEventListener('click', openLightbox);
                if (lightboxClose) lightboxClose.addEventListener('click', closeLightbox);
                if (lightbox) lightbox.addEventListener('click', function(e) {
                    if (e.target === lightbox) closeLightbox();
                });
            });

        // Lógica de Galería
        function changeHeroImage(url, thumb) {
            const hero = document.getElementById('gallery-hero-img');
            hero.style.opacity = '0.5';
            setTimeout(() => {
                hero.src = url;
                hero.style.opacity = '1';
            }, 200);
            document.querySelectorAll('.gallery-thumb').forEach(t => t.classList.remove('active'));
            thumb.classList.add('active');
        }

        // Lógica de Mapa
        document.addEventListener('DOMContentLoaded', function() {
            var lat = <?php echo !empty($lat) ? esc_js($lat) : '13.6929'; ?>;
            var lng = <?php echo !empty($lng) ? esc_js($lng) : '-89.2182'; ?>;
            
            if (typeof L !== 'undefined') {
                var map = L.map('property-map', { scrollWheelZoom: false }).setView([lat, lng], 15);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
                L.marker([lat, lng]).addTo(map).bindPopup("<b><?php the_title(); ?></b>").openPopup();
                
                setTimeout(function() { map.invalidateSize(); }, 600);
            }
        });
        </script>

    <?php endwhile; ?>
</main>

<?php get_footer(); ?>