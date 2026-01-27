<?php
// Shortcode: [reporte_inmuebles propiedad="casas"]
if (!defined('ABSPATH')) exit;


function tubihome_shortcode_reporte_inmuebles($atts) {
    $atts = shortcode_atts([
        'propiedad' => '',
        'operacion' => ''
    ], $atts);
    $tipo = sanitize_title($atts['propiedad']);
    $operacion = sanitize_text_field($atts['operacion']);
    if (!$tipo) return '<p>Debes especificar el tipo de propiedad.</p>';

    $term = get_term_by('slug', $tipo, 'tipo-propiedad');
    if (!$term) return '<p>No existe ese tipo de propiedad.</p>';

        ob_start();
        ?>
        <div class="splitview-root">
            <!-- Barra superior de filtros -->
            <div class="splitview-filtros">
                <input type="text" id="buscador-texto" class="buscador-texto" placeholder="Buscar por palabra clave..." />
                <button class="chip-filtro" id="chip-tipo">Tipo</button>
                <button class="chip-filtro" id="chip-operacion">Operación</button>
                <button class="chip-filtro" id="btn-amenidades">Amenidades</button>
                <div class="result-count" id="splitview-result-count">Cargando...</div>
            </div>
            <!-- Modal de Amenidades -->
            <div id="modal-amenidades" class="modal-amenidades" style="display:none;">
                <div class="modal-content">
                    <span class="close-modal" id="close-modal-amenidades">&times;</span>
                    <h3>Amenidades</h3>
                    <div id="amenidades-container"></div>
                </div>
            </div>
            <main class="main-content-split">
                <section class="results-column">
                    <h1 class="tipo-title"><?php echo esc_html($term->name); ?></h1>
                    <div id="inmuebles-grilla" class="inmuebles-grilla" data-term="<?php echo esc_attr($term->slug); ?>" data-operacion="<?php echo esc_attr($operacion); ?>"></div>
                    <div id="infinite-loader"><span>Cargando más propiedades...</span></div>
                </section>
                <aside class="map-section">
                    <div id="splitview-map-container"></div>
                    <button id="btn-buscar-area">Buscar en esta área</button>
                </aside>
            </main>
        </div>
        <script>window.tubihomeShortcodeOperacion = <?php echo json_encode($operacion); ?>;</script>
        <?php
        return ob_get_clean();
}
add_shortcode('reporte_inmuebles', 'tubihome_shortcode_reporte_inmuebles');


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

