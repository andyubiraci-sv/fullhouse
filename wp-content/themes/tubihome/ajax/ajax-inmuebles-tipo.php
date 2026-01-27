<?php
// Carga parcial de cards para scroll infinito por tipo de propiedad
require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');
$tipo = isset($_GET['tipo']) ? sanitize_text_field($_GET['tipo']) : '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 12;
$args = [
    'post_type' => 'inmueble',
    'posts_per_page' => $per_page,
    'paged' => $page,
    'tax_query' => [
        [
            'taxonomy' => 'tipo-propiedad',
            'field' => 'slug',
            'terms' => $tipo,
        ]
    ],
    'post_status' => 'publish',
    'orderby' => 'date',
    'order' => 'DESC',
    'no_found_rows' => true,
    'fields' => 'ids',
];

// Filtro por bounds (área del mapa)
if (!empty($_GET['ne_lat']) && !empty($_GET['ne_lng']) && !empty($_GET['sw_lat']) && !empty($_GET['sw_lng'])) {
    $meta_query = [
        'relation' => 'AND',
        [
            'key' => '_geo_lat',
            'value' => [floatval($_GET['sw_lat']), floatval($_GET['ne_lat'])],
            'type' => 'NUMERIC',
            'compare' => 'BETWEEN'
        ],
        [
            'key' => '_geo_lng',
            'value' => [floatval($_GET['sw_lng']), floatval($_GET['ne_lng'])],
            'type' => 'NUMERIC',
            'compare' => 'BETWEEN'
        ]
    ];
    $args['meta_query'] = $meta_query;
}
// Filtro por operación si se pasa por GET
if (!empty($_GET['operacion'])) {
    $args['tax_query'][] = [
        'taxonomy' => 'tipo-operacion',
        'field' => 'slug',
        'terms' => sanitize_text_field($_GET['operacion'])
    ];
}
error_log('AJAX INMUEBLES args: ' . print_r($args, true));
$query = new WP_Query($args);
if (empty($query->posts)) {
    echo 'END';
    exit;
}
foreach ($query->posts as $post_id) {
        $titulo = get_the_title($post_id);
        $link = get_permalink($post_id);
        $precio = get_post_meta($post_id, '_price', true);
        $moneda = get_post_meta($post_id, '_currency', true);
        $area = get_post_meta($post_id, '_area_total', true);
        $rec = get_post_meta($post_id, '_rooms', true);
        $ban = get_post_meta($post_id, '_baths', true);
        $park = get_post_meta($post_id, '_parking', true);
        $badge = get_post_meta($post_id, '_status', true);
        $gallery = get_post_meta($post_id, '_gallery', true);
        $gallery_ids = $gallery ? array_filter(explode(',', $gallery)) : [];
        $main_img = get_the_post_thumbnail_url($post_id, 'inmueble-thumb');
        $imgs = $main_img ? array_merge([$main_img], array_map('wp_get_attachment_url', $gallery_ids)) : array_map('wp_get_attachment_url', $gallery_ids);
        $imgs = array_unique(array_filter($imgs));
        $lat = get_post_meta($post_id, '_geo_lat', true);
        $lng = get_post_meta($post_id, '_geo_lng', true);
        echo '<div class="inmueble-card splitview-card" itemscope itemtype="https://schema.org/Residence" style="background:#fff;border-radius:14px;box-shadow:0 2px 16px 0 rgba(20,90,55,0.07);overflow:hidden;transition:box-shadow .2s;position:relative;display:flex;flex-direction:column;" data-lat="' . esc_attr($lat) . '" data-lng="' . esc_attr($lng) . '">
            <a href="' . esc_url($link) . '" style="text-decoration:none;color:inherit;">
                <div class="card-carousel" style="position:relative;width:100%;height:220px;overflow:hidden;">
                    <div class="carousel-inner" style="width:100%;height:100%;display:flex;">
                        ';
        foreach ($imgs as $i => $img_url) {
            echo '<div class="carousel-img" style="flex:1 0 100%;height:100%;position:relative;">
                <img src="' . esc_url($img_url) . '" alt="Foto ' . ($i+1) . '" style="width:100%;height:100%;object-fit:cover;">';
            if ($i === 0 && $badge) {
                echo '<span class="card-badge" style="position:absolute;top:12px;left:12px;background:#145a37;color:#fff;padding:4px 12px;border-radius:12px;font-size:0.95rem;font-weight:600;box-shadow:0 2px 8px 0 rgba(20,90,55,0.10);">' . esc_html(ucfirst($badge)) . '</span>';
            }
            echo '</div>';
        }
        if (count($imgs) > 1) {
            echo '<button class="carousel-prev" style="position:absolute;top:50%;left:8px;transform:translateY(-50%);background:#fff;border:none;border-radius:50%;width:32px;height:32px;box-shadow:0 2px 8px 0 rgba(20,90,55,0.10);font-size:1.3rem;cursor:pointer;">&#60;</button>';
            echo '<button class="carousel-next" style="position:absolute;top:50%;right:8px;transform:translateY(-50%);background:#fff;border:none;border-radius:50%;width:32px;height:32px;box-shadow:0 2px 8px 0 rgba(20,90,55,0.10);font-size:1.3rem;cursor:pointer;">&#62;</button>';
        }
        echo '</div>
                </div>
                <div class="card-info" style="padding:18px 16px 12px 16px;display:flex;flex-direction:column;gap:8px;">
                    <div class="card-title" style="font-size:1.1rem;font-weight:700;color:#145a37;">' . esc_html($titulo) . '</div>
                    <div class="card-icons" style="display:flex;gap:14px;align-items:center;font-size:1.05rem;color:#888;">
                        <span title="Habitaciones"><svg width="18" height="18" fill="none" viewBox="0 0 24 24"><rect x="2" y="10" width="20" height="8" rx="3" stroke="#145a37" stroke-width="2"></rect></svg> ' . ($rec ? esc_html($rec) : '-') . '</span>
                        <span title="Baños"><svg width="18" height="18" fill="none" viewBox="0 0 24 24"><rect x="4" y="10" width="16" height="8" rx="4" stroke="#145a37" stroke-width="2"></rect></svg> ' . ($ban ? esc_html($ban) : '-') . '</span>
                        <span title="Parqueos"><svg width="18" height="18" fill="none" viewBox="0 0 24 24"><rect x="2" y="12" width="20" height="6" rx="3" stroke="#145a37" stroke-width="2"></rect><circle cx="7" cy="18" r="2" stroke="#145a37" stroke-width="2"></circle><circle cx="17" cy="18" r="2" stroke="#145a37" stroke-width="2"></circle></svg> ' . ($park ? esc_html($park) : '-') . '</span>
                    </div>
                    <div class="card-area" style="font-size:0.98rem;color:#888;">' . ($area ? esc_html($area) . ' m²' : '') . '</div>
                    <div class="card-price" style="font-size:1.15rem;font-weight:700;color:#145a37;text-align:right;">$' . ($precio ? number_format((float)$precio, 0, '.', ',') : '-') . ($moneda ? ' ' . esc_html($moneda) : '') . '</div>
                </div>
            </a>
        </div>';
}
