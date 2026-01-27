<?php
// Script de diagnóstico para revisar coordenadas de inmuebles
// Coloca este archivo en /wp-content/plugins/tubihome-core/ y accede desde el navegador solo para depuración

require_once('../../../wp-load.php');

$args = array(
    'post_type' => 'inmueble',
    'posts_per_page' => -1,
    'post_status' => 'any',
);
$query = new WP_Query($args);

echo '<h2>Diagnóstico de coordenadas de inmuebles</h2>';
echo '<table border="1" cellpadding="5"><tr><th>ID</th><th>Título</th><th>latitud</th><th>longitud</th><th>_geo_lat</th><th>_geo_lng</th></tr>';
foreach ($query->posts as $post) {
    $lat = get_post_meta($post->ID, 'latitud', true);
    $lng = get_post_meta($post->ID, 'longitud', true);
    $geo_lat = get_post_meta($post->ID, '_geo_lat', true);
    $geo_lng = get_post_meta($post->ID, '_geo_lng', true);
    echo '<tr>';
    echo '<td>' . $post->ID . '</td>';
    echo '<td>' . esc_html(get_the_title($post->ID)) . '</td>';
    echo '<td>' . esc_html($lat) . '</td>';
    echo '<td>' . esc_html($lng) . '</td>';
    echo '<td>' . esc_html($geo_lat) . '</td>';
    echo '<td>' . esc_html($geo_lng) . '</td>';
    echo '</tr>';
}
echo '</table>';
