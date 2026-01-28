<?php
add_action('wp_ajax_tubihome_splitview_load', 'tubihome_splitview_load_cb');
add_action('wp_ajax_nopriv_tubihome_splitview_load', 'tubihome_splitview_load_cb');
function tubihome_splitview_load_cb(){
    $paged = isset($_POST['paged']) ? max(1, intval($_POST['paged'])) : 1;
    $term = isset($_POST['term']) ? sanitize_text_field($_POST['term']) : '';
    $operacion = isset($_POST['operacion']) ? sanitize_text_field($_POST['operacion']) : '';
    $args = [
        'post_type' => 'inmueble',
        'posts_per_page' => 6,
        'paged' => $paged,
        'post_status' => ['publish'],
    ];
    if($term) {
        $args['tax_query'][] = [
            'taxonomy' => 'tipo-propiedad',
            'field' => 'slug',
            'terms' => $term
        ];
    }
    if($operacion) {
        $args['tax_query'][] = [
            'taxonomy' => 'tipo-operacion',
            'field' => 'slug',
            'terms' => $operacion
        ];
    }
    $q = new WP_Query($args);
    if($q->have_posts()){
        foreach($q->posts as $post){
            setup_postdata($post);
            // --- Copia fiel del render principal ---
            $price_raw = get_post_meta($post->ID, '_price', true);
            $price_num = is_numeric($price_raw) ? (float)$price_raw : 0;
            $price_k = $price_num >= 1000 ? number_format($price_num / 1000, 0) . 'k' : number_format($price_num, 0);
            $area_post = get_post_meta($post->ID, '_area_total', true);
            $aprox_m2 = is_numeric($area_post) ? number_format((float)$area_post) . ' m²' : 'N/A';
            $valor_m2 = $area_post > 0 ? '$' . number_format($price_num / (float)$area_post, 2) . ' / m²' : 'N/A';
            $lat = get_post_meta($post->ID, 'latitud', true);
            $lng = get_post_meta($post->ID, 'longitud', true);
            if (empty($lat)) { $lat = get_post_meta($post->ID, '_geo_lat', true); }
            if (empty($lng)) { $lng = get_post_meta($post->ID, '_geo_lng', true); }
            $distrito = get_post_meta($post->ID, 'distrito', true);
            $distrito = $distrito ? $distrito : 'Sin distrito';
            $localidad = get_post_meta($post->ID, 'municipio', true);
            $localidad = $localidad ? $localidad : 'Sin localidad';
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
                                <bold><?php echo esc_html($distrito); ?></bold>
                                <bold><?php echo esc_html($localidad); ?></bold>
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
                            <span class="microdetalles-chip">Superficie: <?php echo esc_html($aprox_m2); ?></span>
                            <span class="microdetalles-chip">Valor Unitario: <?php echo esc_html($valor_m2); ?></span>
                        </div>
                    </div>
                </a>
            </article>
            <?php
        }
        wp_reset_postdata();
    }
    wp_die();
}
