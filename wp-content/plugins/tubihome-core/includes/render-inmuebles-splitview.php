<?php
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
                <div class="tipo-title">
                    <?php echo esc_html($args['title'] ?? 'Propiedades'); ?>
                </div>
                <div class="inmuebles-grilla" id="inmuebles-grilla" data-term="<?php echo esc_attr($args['term'] ?? ''); ?>" data-operacion="<?php echo esc_attr($args['operacion'] ?? ''); ?>">
                    <?php if ($posts): ?>
                        <?php foreach ($posts as $post): setup_postdata($post); ?>
                            <?php
                            // Seguridad: precio limpio y formato k
                            $price_raw = get_post_meta($post->ID, 'precio', true);
                            $price_num = is_numeric($price_raw) ? (float)$price_raw : 0;
                            $price_k = $price_num >= 1000 ? number_format($price_num / 1000, 0) . 'k' : number_format($price_num, 0);
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
                                <?php if (has_post_thumbnail($post->ID)) {
                                    echo get_the_post_thumbnail($post->ID, 'medium');
                                } ?>
                                <h3><?php echo esc_html(get_the_title($post->ID)); ?></h3>
                                <p class="price">$<?php echo esc_html($price_k); ?></p>
                                <a href="<?php echo esc_url(get_permalink($post->ID)); ?>">Ver Detalles</a>
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
