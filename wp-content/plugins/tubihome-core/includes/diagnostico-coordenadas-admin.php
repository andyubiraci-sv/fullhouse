<?php
// --- Diagnóstico de coordenadas en el admin ---
if (php_sapi_name() !== 'cli') {
  if (is_admin()) {
    add_action('admin_menu', function() {
      add_menu_page(
        'Diagnóstico Coordenadas',
        'Diag. Coordenadas',
        'manage_options',
        'diagnostico-coordenadas',
        'tubihome_diagnostico_coordenadas_page'
      );
    });
    function tubihome_diagnostico_coordenadas_page() {
      echo '<div class="wrap"><h1>Diagnóstico de coordenadas de inmuebles</h1>';
      $args = array(
        'post_type' => 'inmueble',
        'posts_per_page' => -1,
        'post_status' => 'any',
      );
      $query = new WP_Query($args);
      echo '<table class="widefat"><thead><tr><th>ID</th><th>Título</th><th>latitud</th><th>longitud</th><th>_geo_lat</th><th>_geo_lng</th></tr></thead><tbody>';
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
      echo '</tbody></table></div>';
    }
  }
}
