

<?php
// Incluir AJAX handler para scroll infinito
require_once __DIR__ . '/splitview-infinite-ajax.php';
// Encolar CSS y JS de splitview siempre que se use este render
add_action('wp_enqueue_scripts', function() {
    if (is_admin()) return;
    wp_enqueue_style('tubihome-leaflet-zoom-fix', plugins_url('includes/leaflet-zoom-fix.css', __DIR__), [], '1.0');
    wp_enqueue_style('tubihome-splitview-modern', plugins_url('includes/splitview-modern.css', __DIR__), [], '1.0');
    wp_enqueue_script('tubihome-splitview-infinite', plugins_url('includes/splitview-infinite.js', __DIR__), ['jquery'], '1.0', true);
    wp_localize_script('tubihome-splitview-infinite', 'tubihome_splitview', [
        'ajaxurl' => admin_url('admin-ajax.php')
    ]);
}, 20);
// Función centralizada para renderizar la vista dividida de inmuebles (lista + mapa)
// $query: instancia de WP_Query o array de posts
// $args: argumentos opcionales para personalización (por ejemplo, mostrar filtros, títulos, etc)
function tubihome_render_inmuebles_splitview($query, $args = []) {
    if ($query instanceof WP_Query) {
        $posts = $query->posts;
    } elseif (is_array($query)) {
        $posts = $query;
    } else {
        $posts = [];
    }
    // Filtrar: solo mostrar posts con tipo de propiedad definido
    $posts = array_filter($posts, function($post) {
        $tipos = wp_get_post_terms($post->ID, 'tipo-propiedad', ['fields'=>'ids']);
        return !empty($tipos);
    });
    ob_start();
    ?>
    <div class="results-map-container splitview-root">
        <div class="splitview-filtros">
            <!-- Aquí puedes incluir filtros, chips, etc. -->
            <?php if (!empty($args['show_filters'])): ?>
                <?php /* Renderiza tus filtros aquí */ ?>
            <?php endif; ?>
            <span class="result-count" id="splitview-result-count"></span>
        </div>
        <div class="main-content-split">
            <div class="results-column">
               
                <div class="inmuebles-grilla-split" id="inmuebles-grilla" data-term="<?php echo esc_attr($args['term'] ?? ''); ?>" data-operacion="<?php echo esc_attr($args['operacion'] ?? ''); ?>">
                    <?php if ($posts): ?>
                        <?php foreach ($posts as $post): setup_postdata($post); ?>
                            <?php
                            // Seguridad: precio limpio y formato k
                            $price_raw = get_post_meta($post->ID, '_price', true);
                            $price_num = is_numeric($price_raw) ? (float)$price_raw : 0;
                            $price_k = $price_num >= 1000 ? number_format($price_num / 1000, 0) . 'k' : number_format($price_num, 0);
                            $area_post = get_post_meta($post->ID, '_area_total', true);
                            $aprox_m2 = is_numeric($area_post) ? number_format((float)$area_post) . ' m²' : 'N/A';
                            $valor_m2 = $area_post > 0 ? '$' . number_format($price_num / (float)$area_post, 2) . ' / m²' : 'N/A';
                            ?>
                            <?php
                            $lat = get_post_meta($post->ID, 'latitud', true);
                            $lng = get_post_meta($post->ID, 'longitud', true);
                            // Si están vacíos, buscar en _geo_lat/_geo_lng
                            if (empty($lat)) {
                                $lat = get_post_meta($post->ID, '_geo_lat', true);
                            }
                            if (empty($lng)) {
                                $lng = get_post_meta($post->ID, '_geo_lng', true);
                            }
                            ?>
                            <article class="inmueble-card splitview-card" data-lat="<?php echo esc_attr($lat); ?>" data-lng="<?php echo esc_attr($lng); ?>" data-price="<?php echo esc_attr($price_k); ?>">
               
                            <a href="<?php echo esc_url(get_permalink($post->ID)); ?>">
                              

                                <div class="card-image-container">

        <h4>
            <div class="svg-cover-split">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512.001 512.001" width="20" height="20">
        <g>
            <path d="M490.134,185.472L338.966,34.304c-45.855-45.737-120.076-45.737-165.931,0L21.867,185.472 C7.819,199.445-0.055,218.457,0,238.272v221.397C0.047,488.568,23.475,511.976,52.374,512h407.253 c28.899-0.023,52.326-23.432,52.373-52.331V238.272C512.056,218.457,504.182,199.445,490.134,185.472z M448,448H341.334v-67.883 c0-44.984-36.467-81.451-81.451-81.451c0,0,0,0,0,0h-7.765c-44.984,0-81.451,36.467-81.451,81.451l0,0V448H64V238.272 c0.007-2.829,1.125-5.541,3.115-7.552L218.283,79.552c20.825-20.831,54.594-20.835,75.425-0.01c0.003,0.003,0.007,0.007,0.01,0.01 L444.886,230.72c1.989,2.011,3.108,4.723,3.115,7.552V448z"/>
        </g>    
    </svg>
    </div>
    <span><?php echo esc_html(get_the_title($post->ID)); ?>
<?php 

$distrito   = get_post_meta($post->ID, 'distrito', true);
$distrito = $distrito ? $distrito : 'Sin distrito';
            echo "<bold>" . esc_html($distrito) . "</bold>";
        
$localidad   = get_post_meta($post->ID, 'municipio', true);
$localidad = $localidad ? $localidad : 'Sin localidad';
            echo "<bold>" . esc_html($localidad) . "</bold>";

        ?>
</span>
    
</h4>

                              <?php 
                                $thumb_id = get_post_thumbnail_id($post->ID);
                                if ($thumb_id) {
                                    $img_html = wp_get_attachment_image($thumb_id, 'medium');
                                    if ($img_html) {
                                        echo $img_html;
                                    } else {
                                        echo '<img class="attachment-medium size-medium" src="https://placehold.co/380x380?text='.esc_attr(get_the_title($post->ID)).'" alt="Imagen temporal">';
                                    }
                                } else {
                                    echo '<img class="attachment-medium size-medium" src="https://placehold.co/380x380?text='.esc_attr(get_the_title($post->ID)).'" alt="Imagen temporal">';
                                }
                              ?>
                               
                                <p class="price">$<?php echo esc_html($price_k); ?></p>
                                <div class="splitview-microdetalles">
                                    <span class="microdetalles-chip"> 
   Superficie: <?php echo esc_html($aprox_m2); ?>

                                    </span>
<span class="microdetalles-chip">
 Valor Unitario: <?php echo esc_html($valor_m2); ?>
</span>

                                 
                                </div>
                                
                                </div>
                               </a>
                            </article>
                        <?php endforeach; wp_reset_postdata(); ?>
                    <?php else: ?>
                        <p>No se encontraron inmuebles.</p>
                    <?php endif; ?>
                </div>
            </div>
            <section class="map-section">
                <div id="splitview-map-container"></div>
                <button id="btn-buscar-area" style="display:none;">Buscar en esta área</button>
            </section>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
