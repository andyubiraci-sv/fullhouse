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
        <div class="splitview-root" style="height:100vh;display:flex;flex-direction:column;">
            <!-- Barra de filtros flotante -->
            <div class="splitview-filtros" style="position:sticky;top:0;z-index:10;background:#fff;padding:12px 0 8px 0;box-shadow:0 2px 12px 0 rgba(20,90,55,0.04);display:flex;align-items:center;gap:12px;">
                <div class="chips-filtros" style="display:flex;gap:8px;flex-wrap:wrap;margin-left:24px;">
                    <button class="chip-filtro" id="chip-precio">Precio</button>
                    <button class="chip-filtro" id="chip-tipo">Tipo</button>
                    <button class="chip-filtro" id="chip-operacion">Operación</button>
                    <button class="chip-filtro" id="btn-mas-filtros">Más filtros</button>
                </div>
                <div class="result-count" id="splitview-result-count" style="margin-left:auto;margin-right:24px;color:#145a37;font-weight:600;font-size:1.05rem;">Cargando...</div>
            </div>
            <div id="panel-amenidades" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(20,90,55,0.08);z-index:1000;align-items:center;justify-content:center;"></div>
            <div class="splitview-main" style="flex:1;display:flex;min-height:0;">
                <!-- Panel Izquierdo: Tarjetas -->
                <div class="splitview-list" style="width:45%;min-width:340px;max-width:600px;height:100%;overflow-y:auto;box-shadow:0 0 0 1px #eee;background:#fff;">
                    <h1 class="tipo-title" style="margin:24px 0 18px 0; color:#145a37; font-size:2rem; font-weight:700; text-align:center;">
                        <?php echo esc_html($term->name); ?>
                    </h1>
                    <div id="inmuebles-grilla" class="inmuebles-grilla" data-term="<?php echo esc_attr($term->slug); ?>" data-operacion="<?php echo esc_attr($operacion); ?>" style="display:flex;flex-direction:column;gap:32px 0;padding:0 24px;"></div>
                    <div id="infinite-loader" style="text-align:center;margin:32px 0;display:none;">
                        <span>Cargando más propiedades...</span>
                    </div>
                </div>
                <!-- Panel Derecho: Mapa -->
                <div class="splitview-map" style="flex:1;position:relative;height:100%;min-width:340px;background:#f6f6f6;">
                    <div id="splitview-map-container" style="position:absolute;top:0;left:0;width:100%;height:100%;"></div>
                    <button id="btn-buscar-area" style="position:absolute;top:18px;right:18px;z-index:20;padding:10px 22px;background:#145a37;color:#fff;border:none;border-radius:24px;font-weight:600;box-shadow:0 2px 12px 0 rgba(20,90,55,0.10);display:none;">Buscar en esta área</button>
                </div>
            </div>
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
    }
});

file_put_contents(WP_CONTENT_DIR . '/debug-shortcodes.txt', 'Shortcodes cargado: ' . time() . "\n", FILE_APPEND);

