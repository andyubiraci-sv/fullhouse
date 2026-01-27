<?php
// Shortcode: [reporte_inmuebles propiedad="casas"]
if (!defined('ABSPATH')) exit;


require_once __DIR__ . '/render-inmuebles-splitview.php';
function tubihome_shortcode_reporte($atts) {
    $atts = shortcode_atts([
        'propiedad' => '',
        'operacion' => ''
    ], $atts);
    $tipo = sanitize_title($atts['propiedad']);
    $operacion = sanitize_text_field($atts['operacion']);
    if (!$tipo) return '<p>Debes especificar el tipo de propiedad.</p>';
    $term = get_term_by('slug', $tipo, 'tipo-propiedad');
    if (!$term) return '<p>No existe ese tipo de propiedad.</p>';
    // Query de inmuebles filtrados
    $args = [
        'post_type' => 'inmueble',
        'post_status' => 'publish',
        'posts_per_page' => 30,
        'tax_query' => [
            [
                'taxonomy' => 'tipo-propiedad',
                'field' => 'slug',
                'terms' => $term->slug
            ]
        ]
    ];
    if ($operacion) {
        $args['tax_query'][] = [
            'taxonomy' => 'tipo-operacion',
            'field' => 'slug',
            'terms' => $operacion
        ];
    }
    $q = new WP_Query($args);
    return tubihome_render_inmuebles_splitview($q, [
        'title' => $term->name,
        'term' => $term->slug,
        'operacion' => $operacion,
        'show_filters' => true
    ]);
}
add_shortcode('reporte_inmuebles', 'tubihome_shortcode_reporte');


// Encolar JS y datos del shortcode solo si el shortcode está presente en el contenido
add_action('wp_enqueue_scripts', function() {
    if (is_admin()) return;
    global $post;
    if (isset($post->post_content) && strpos($post->post_content, '[reporte_inmuebles') !== false) {
        $ajax_url = get_template_directory_uri() . '/ajax/ajax-inmuebles-tipo.php';

        
        wp_enqueue_script('tubihome-shortcode-inmuebles', plugins_url('includes/shortcode-inmuebles.js', __DIR__), [], '1.0', true);
        wp_localize_script('tubihome-shortcode-inmuebles', 'tubihomeShortcodeAjax', [
            'ajax_url' => $ajax_url
        ]);
        wp_enqueue_script('tubihome-leaflet-init', plugins_url('includes/leaflet-init.js', __DIR__), [], '1.0', true);
        wp_enqueue_style('tubihome-leaflet-zoom-fix', plugins_url('includes/leaflet-zoom-fix.css', __DIR__), [], '1.0');
        wp_enqueue_style('tubihome-splitview-modern', plugins_url('includes/splitview-modern.css', __DIR__), [], '1.0');
        wp_enqueue_style('tubihome-splitview-modern-modal', plugins_url('includes/splitview-modern-modal.css', __DIR__), [], '1.0');
    }
});

// file_put_contents(WP_CONTENT_DIR . '/debug-shortcodes.txt', 'Shortcodes cargado: ' . time() . "\n", FILE_APPEND);

