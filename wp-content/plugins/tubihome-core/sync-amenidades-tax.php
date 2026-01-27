<?php
// Script para sincronizar el meta _amenidades con la taxonomía 'amenidades' en todos los inmuebles
// Ejecutar una sola vez desde el navegador o WP-CLI
$max_up = 8;
$dir = __DIR__;
$wp_load = '';
for ($i = 0; $i < $max_up; $i++) {
    $try = realpath($dir . str_repeat('/..', $i) . '/wp-load.php');
    if ($try && file_exists($try)) {
        $wp_load = $try;
        break;
    }
}
if ($wp_load) {
    require_once($wp_load);
} else {
    exit("No se encontró wp-load.php. Ejecuta este script desde la raíz del sitio WordPress.");
}

$args = [
    'post_type' => 'inmueble',
    'posts_per_page' => -1,
    'post_status' => 'any',
];
$query = new WP_Query($args);
$count = 0;
foreach ($query->posts as $post) {
    $amenidades_str = get_post_meta($post->ID, '_amenidades', true);
    if ($amenidades_str) {
        $amenidades = array_map('trim', explode(',', $amenidades_str));
        // Solo asignar si hay amenidades
        if (!empty($amenidades)) {
            wp_set_object_terms($post->ID, $amenidades, 'amenidades', false);
            $count++;
        }
    }
}
echo "Sincronización completada. Amenidades asignadas a $count inmuebles.";
