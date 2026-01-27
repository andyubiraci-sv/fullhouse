<?php
// Script para listar todos los términos actuales de la taxonomía 'amenidades'
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

$terms = get_terms([
    'taxonomy' => 'amenidades',
    'hide_empty' => false
]);
if (empty($terms)) {
    echo "No hay términos en la taxonomía 'amenidades'.";
    exit;
}
echo "<h2>Términos actuales en la taxonomía 'amenidades':</h2><ul>";
foreach ($terms as $term) {
    echo '<li>' . esc_html($term->name) . '</li>';
}
echo '</ul>';
