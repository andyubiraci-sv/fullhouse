


<?php
// Shortcode de diagnóstico: [debug_inmuebles]
function tubihome_debug_inmuebles() {
    $args = [
        'post_type' => 'inmueble',
        'post_status' => 'publish',
        'posts_per_page' => 50
    ];
    $q = new WP_Query($args);
    if (!$q->have_posts()) return '<p>No hay inmuebles publicados.</p>';
    $out = '<table style="width:100%;border-collapse:collapse;"><tr><th>ID</th><th>Título</th><th>Propiedad</th><th>Operación</th></tr>';
    foreach ($q->posts as $p) {
        $prop = wp_get_object_terms($p->ID, 'tipo-propiedad', ['fields'=>'slugs']);
        $op = wp_get_object_terms($p->ID, 'tipo-operacion', ['fields'=>'slugs']);
        $out .= '<tr><td>' . $p->ID . '</td><td>' . esc_html($p->post_title) . '</td><td>' . implode(',', $prop) . '</td><td>' . implode(',', $op) . '</td></tr>';
    }
    $out .= '</table>';
    return $out;
}
add_shortcode('debug_inmuebles', 'tubihome_debug_inmuebles');

// Debug: Forzar plantilla de depuración para tipo-propiedad
add_filter('template_include', function($template) {
    if (is_tax('tipo-propiedad')) {
        $debug = get_template_directory() . '/template-debug-tax.php';
        if (file_exists($debug)) return $debug;
    }
    return $template;
}, 99);




// Forzar template archive-inmueble.php si la URL contiene /inmueble y parámetros GET
add_filter('template_include', function($template) {
    // Solo forzar archive-inmueble.php en el archivo/listado, nunca en single
    if (is_post_type_archive('inmueble')) {
        $custom = get_template_directory() . '/archive-inmueble.php';
        if (file_exists($custom)) return $custom;
    }
    return $template;
});
/**
 * Tubihome Functions - Optimización de Filtros y UX
 */

// 1. SOPORTE Y SCRIPTS
function tubihome_setup() {
    add_theme_support('post-thumbnails');
    add_theme_support('title-tag');
    add_theme_support('custom-logo', [
        'height'      => 80,
        'width'       => 300,
        'flex-height' => true,
        'flex-width'  => true,
    ]);
    add_image_size('inmueble-thumb', 400, 300, true);
    add_action('after_setup_theme', 'tubihome_setup');
    register_nav_menus(['menu-principal' => 'Menú Principal']);
}
add_action('after_setup_theme', 'tubihome_setup');


function tubihome_enqueue_scripts() {
    wp_enqueue_style('tubihome-style', get_stylesheet_uri(), [], '1.3');
    global $post;
    $has_shortcode = false;
    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content ?? '', 'reporte_inmuebles')) {
        $has_shortcode = true;
    }
    if (is_post_type_archive('inmueble') || $has_shortcode) {
        wp_enqueue_style('leaflet-css', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css');
        wp_enqueue_script('leaflet-js', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', [], null, true);
        // Asegúrate de que el archivo js/map-script.js existe en tu tema
        wp_enqueue_script('tubihome-map-js', get_template_directory_uri() . '/js/map-script.js', ['leaflet-js'], '1.0', true);
    }
}
add_action('wp_enqueue_scripts', 'tubihome_enqueue_scripts');

/**
 * 2. LÓGICA DETALLADA DEL BUSCADOR (Query Maestra)
 */
function tubihome_filtrar_inmuebles($query) {
        // El filtro de operación se maneja solo por taxonomía tipo-operacion
    // Solo actuar en el query principal del catálogo de inmuebles en el frontend
    if (is_admin() || !$query->is_main_query() || !is_post_type_archive('inmueble')) {
        return;
    }

    // --- CAPA 1: SANITIZACIÓN ---
    $min_price    = !empty($_GET['min_price']) ? floatval($_GET['min_price']) : 0;
    $max_price    = !empty($_GET['max_price']) ? floatval($_GET['max_price']) : 0;
    $habitaciones = !empty($_GET['habitaciones']) ? intval($_GET['habitaciones']) : 0;
    $distrito     = !empty($_GET['distrito']) ? sanitize_text_field($_GET['distrito']) : '';
    $municipio    = !empty($_GET['municipio']) ? sanitize_text_field($_GET['municipio']) : '';
    $operacion    = !empty($_GET['tipo-operacion']) ? sanitize_text_field($_GET['tipo-operacion']) : '';
    $propiedad    = !empty($_GET['tipo-propiedad']) ? sanitize_text_field($_GET['tipo-propiedad']) : '';
    
    // Capturamos amenidades (vienen de checkboxes en el form)
    $amenidades = [];
    if (isset($_GET['amenidades'])) {
        if (is_array($_GET['amenidades'])) {
            $amenidades = array_map('sanitize_text_field', $_GET['amenidades']);
        } elseif (is_string($_GET['amenidades']) && strlen($_GET['amenidades'])) {
            $amenidades = array_map('sanitize_text_field', explode(',', $_GET['amenidades']));
        }
        // Convertir los nombres a slugs para la consulta
        if (!empty($amenidades)) {
            $slugs = [];
            foreach ($amenidades as $nombre) {
                $term = get_term_by('name', $nombre, 'amenidades');
                if ($term) $slugs[] = $term->slug;
            }
            $amenidades = $slugs;
        }
    }
    // Log temporal para depuración
    if (!empty($amenidades)) {
        error_log('Amenidades recibidas en filtro: ' . print_r($amenidades, true));
    }

    // --- CAPA 2: TAXONOMÍAS (Filtros Rápidos) ---
    $tax_query = ['relation' => 'AND'];

    if ($operacion) {
        $tax_query[] = ['taxonomy' => 'tipo-operacion', 'field' => 'slug', 'terms' => $operacion];
    }
    if ($propiedad) {
        $tax_query[] = ['taxonomy' => 'tipo-propiedad', 'field' => 'slug', 'terms' => $propiedad];
    }

    // --- CAPA 3: METADATOS (Datos Numéricos y Ubicación) ---
    $meta_query = ['relation' => 'AND'];

    // Amenidades como taxonomía (tax_query OR)
    if (!empty($amenidades)) {
        // Filtrado OR: al menos una amenidad (por slug)
        $tax_query[] = [
            'taxonomy' => 'amenidades',
            'field'    => 'slug',
            'terms'    => $amenidades,
            'operator' => 'IN'
        ];
        $tax_query['relation'] = 'AND';
    }

    // Lógica de Precios (Rangos Dinámicos)
    if ($min_price > 0 && $max_price > 0) {
        $meta_query[] = ['key' => '_price', 'value' => [$min_price, $max_price], 'type' => 'NUMERIC', 'compare' => 'BETWEEN'];
    } elseif ($min_price > 0) {
        $meta_query[] = ['key' => '_price', 'value' => $min_price, 'type' => 'NUMERIC', 'compare' => '>='];
    } elseif ($max_price > 0) {
        $meta_query[] = ['key' => '_price', 'value' => $max_price, 'type' => 'NUMERIC', 'compare' => '<='];
    }

    if ($habitaciones > 0) {
        $meta_query[] = ['key' => '_rooms', 'value' => $habitaciones, 'type' => 'NUMERIC', 'compare' => '>='];
    }

    // Ubicación exacta del CSV
    if ($distrito) {
        $meta_query[] = ['key' => 'distrito', 'value' => $distrito, 'compare' => '='];
    }
    if ($municipio) {
        $meta_query[] = ['key' => 'municipio', 'value' => $municipio, 'compare' => '='];
    }

    if (count($meta_query) > 1) {
        $query->set('meta_query', $meta_query);
    }
    if (count($tax_query) > 1) {
        $query->set('tax_query', $tax_query);
    }

    // --- CAPA 4: ORDENAMIENTO (Sorting) ---
    if (isset($_GET['sort'])) {
        switch ($_GET['sort']) {
            case 'precio_bajo':
                $query->set('meta_key', '_price');
                $query->set('orderby', 'meta_value_num');
                $query->set('order', 'ASC');
                break;
            case 'precio_alto':
                $query->set('meta_key', '_price');
                $query->set('orderby', 'meta_value_num');
                $query->set('order', 'DESC');
                break;
            default:
                $query->set('orderby', 'date');
                $query->set('order', 'DESC');
        }
    }
}
add_action('pre_get_posts', 'tubihome_filtrar_inmuebles');

/**
 * 3. METABOX ADMIN (Ubicación con carga JSON)
 */

function tubihome_add_metabox_ubicacion() {
    // Metabox lateral de ubicación (MANTENER)
    add_meta_box('tubi_loc', 'Ubicación Geográfica', 'tubihome_loc_callback', 'inmueble', 'side');
}
add_action('add_meta_boxes', 'tubihome_add_metabox_ubicacion');

function tubihome_loc_callback($post) {
    // Cargamos el JSON unificado para evitar múltiples peticiones fetch en el admin
    $json_path = get_template_directory() . '/data/municipios_distritos.json';
    $data = file_exists($json_path) ? json_decode(file_get_contents($json_path), true) : [];
    
    $sel_mun = get_post_meta($post->ID, 'municipio', true);
    $sel_dis = get_post_meta($post->ID, 'distrito', true);

    wp_nonce_field('tubi_save_loc', 'tubi_loc_nonce');

    // 1. Obtener lista completa de municipios y su relación con distritos
    $municipios = [];
    foreach ($data as $dis => $muns) {
        foreach ($muns as $mun) {
            if (!isset($municipios[$mun])) $municipios[$mun] = [];
            $municipios[$mun][] = $dis;
        }
    }

    echo '<p><label>Municipio / Zona:</label><br>';
    echo '<select id="admin_municipio" name="municipio" style="width:100%">';
    echo '<option value="">Seleccione municipio...</option>';
    foreach (array_keys($municipios) as $mun) {
        printf('<option value="%s" %s>%s</option>', esc_attr($mun), selected($sel_mun, $mun, false), esc_html($mun));
    }
    echo '</select></p>';

    echo '<p><label>Departamento / Distrito:</label><br>';
    echo '<select id="admin_distrito" name="distrito" style="width:100%">';
    echo '<option value="">Seleccione distrito...</option>';
    // Si ya hay municipio seleccionado, mostrar solo los distritos correspondientes
    if ($sel_mun && isset($municipios[$sel_mun])) {
        foreach ($municipios[$sel_mun] as $dis) {
            printf('<option value="%s" %s>%s</option>', esc_attr($dis), selected($sel_dis, $dis, false), esc_html($dis));
        }
    }
    echo '</select></p>';
    // Campo Operación (checkboxes)
    $operacion = get_post_meta($post->ID, 'operacion', true);
    $operacion = is_array($operacion) ? $operacion : [];
    echo '<fieldset style="border:1px solid #eee;padding:12px 18px;border-radius:8px;margin-bottom:12px;">';
    echo '<legend style="font-weight:bold;color:#145a37;">Operación</legend>';
    echo '<label style="margin-right:16px;"><input type="checkbox" name="operacion[]" value="Vender"' . (in_array('Vender', $operacion) ? ' checked' : '') . '> Vender</label>';
    echo '<label><input type="checkbox" name="operacion[]" value="Alquilar"' . (in_array('Alquilar', $operacion) ? ' checked' : '') . '> Alquilar</label>';
    echo '</fieldset>';

    ?>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const data = <?php echo json_encode($data); ?>;
        const municipios = <?php echo json_encode($municipios); ?>;
        const munSel = document.getElementById("admin_municipio");
        const disSel = document.getElementById("admin_distrito");
        const currentDis = "<?php echo esc_js($sel_dis); ?>";

        function updateDis() {
            const mun = munSel.value;
            disSel.innerHTML = '<option value="">Seleccione distrito...</option>';
            if(municipios[mun]) {
                municipios[mun].forEach(dis => {
                    const opt = document.createElement("option");
                    opt.value = opt.textContent = dis;
                    if(dis === currentDis) opt.selected = true;
                    disSel.appendChild(opt);
                });
            }
        }
        munSel.addEventListener("change", updateDis);
        // Si hay municipio guardado, actualizar el select de distrito
        if(munSel.value && disSel.options.length <= 1) updateDis();
    });
    </script>
    <?php
}


function tubihome_save_post_inmueble($post_id) {
    // Solo guardar si es el CPT inmueble
    if (get_post_type($post_id) !== 'inmueble') return;
    if (!isset($_POST['tubi_loc_nonce']) || !wp_verify_nonce($_POST['tubi_loc_nonce'], 'tubi_save_loc')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (isset($_POST['distrito'])) update_post_meta($post_id, 'distrito', sanitize_text_field($_POST['distrito']));
    if (isset($_POST['municipio'])) update_post_meta($post_id, 'municipio', sanitize_text_field($_POST['municipio']));
    // Guardar operación como array y asignar taxonomía
    if (isset($_POST['operacion']) && is_array($_POST['operacion'])) {
        $ops = array_map('sanitize_text_field', $_POST['operacion']);
        update_post_meta($post_id, 'operacion', $ops);
        // Asignar términos a la taxonomía tipo-operacion por slug
        $slugs = [];
        foreach ($ops as $op) {
            // Usar directamente el valor del checkbox como slug
            $slugs[] = sanitize_title($op);
        }
        if (!empty($slugs)) {
            wp_set_object_terms($post_id, $slugs, 'tipo-operacion');
        } else {
            wp_set_object_terms($post_id, [], 'tipo-operacion');
        }
    } else {
        delete_post_meta($post_id, 'operacion');
        wp_set_object_terms($post_id, [], 'tipo-operacion');
    }
}
add_action('save_post', 'tubihome_save_post_inmueble');