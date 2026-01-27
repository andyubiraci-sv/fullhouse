<?php
// Incluir shortcodes personalizados
require_once __DIR__ . '/includes/shortcodes.php';
// Regla personalizada para /inmueble/{tipo}
add_action('init', function() {
    add_rewrite_rule(
        '^inmueble/([^/]+)/?$',
        'index.php?tipo-propiedad=$matches[1]',
        'top'
    );
});

// Forzar flush_rewrite_rules al activar el plugin
register_activation_hook(__FILE__, function() {
    flush_rewrite_rules();
});


// Forzar flush de reglas de reescritura al activar el plugin o al cargar el tema (temporal)
add_action('init', function() {
    if (get_option('tubihome_flush_needed') !== 'no') {
        flush_rewrite_rules();
        update_option('tubihome_flush_needed', 'no');
    }
});
register_activation_hook(__FILE__, function() {
    update_option('tubihome_flush_needed', 'yes');
});

// Cambiar el texto del enlace de imagen destacada para indicar que es requerida
function tubihome_featured_image_required_text($content) {
    // Ya no es requerida, no modificar el texto
    return $content;
}



add_filter('admin_post_thumbnail_html', 'tubihome_featured_image_required_text');
// Validación previa al guardado: fuerza a borrador si faltan campos requeridos
function tubihome_inmueble_force_draft($data, $postarr) {
    if ($data['post_type'] !== 'inmueble') return $data;
    $faltantes = [];
    $post_id = isset($postarr['ID']) ? $postarr['ID'] : (isset($_POST['post_ID']) ? $_POST['post_ID'] : 0);
    // Usar $_POST si existe, si no, get_post_meta
    $campos = [
        'Precio' => isset($_POST['tubihome_price']) ? $_POST['tubihome_price'] : get_post_meta($post_id, '_price', true),
        'Municipio' => isset($_POST['municipio']) ? $_POST['municipio'] : get_post_meta($post_id, 'municipio', true),
        'Distrito' => isset($_POST['distrito']) ? $_POST['distrito'] : get_post_meta($post_id, 'distrito', true),
        'Latitud' => isset($_POST['tubihome_geo_lat']) ? $_POST['tubihome_geo_lat'] : get_post_meta($post_id, '_geo_lat', true),
        'Longitud' => isset($_POST['tubihome_geo_lng']) ? $_POST['tubihome_geo_lng'] : get_post_meta($post_id, '_geo_lng', true),
        'Dirección completa' => isset($_POST['tubihome_address']) ? $_POST['tubihome_address'] : get_post_meta($post_id, '_address', true),
    ];
    foreach ($campos as $label => $valor) {
        // Permitir 0 como valor válido en campos numéricos
        if ($valor === '' || $valor === null) $faltantes[] = $label;
    }
    // Si faltan campos y se intenta publicar, forzar a borrador
    if (!empty($faltantes) && $data['post_status'] === 'publish') {
        $data['post_status'] = 'draft';
    }
    // Si NO faltan campos, siempre forzar a publicado
    if (empty($faltantes)) {
        $data['post_status'] = 'publish';
    }
    return $data;
}
add_filter('wp_insert_post_data', 'tubihome_inmueble_force_draft', 10, 2);
// Mostrar aviso si el inmueble fue guardado como borrador por falta de campos requeridos
function tubihome_inmueble_admin_notice() {
    global $post;
    if (!is_admin() || !isset($post) || $post->post_type !== 'inmueble') return;
    // Solo mostrar en la pantalla de edición del inmueble
    $screen = get_current_screen();
    if ($screen->base !== 'post' || $screen->post_type !== 'inmueble') return;
    if ($post->post_status === 'draft') {
        $faltantes = [];
        $campos = [
            'Precio' => isset($_POST['tubihome_price']) ? $_POST['tubihome_price'] : get_post_meta($post->ID, '_price', true),
            'Municipio' => isset($_POST['municipio']) ? $_POST['municipio'] : get_post_meta($post->ID, 'municipio', true),
            'Distrito' => isset($_POST['distrito']) ? $_POST['distrito'] : get_post_meta($post->ID, 'distrito', true),
            'Latitud' => isset($_POST['tubihome_geo_lat']) ? $_POST['tubihome_geo_lat'] : get_post_meta($post->ID, '_geo_lat', true),
            'Longitud' => isset($_POST['tubihome_geo_lng']) ? $_POST['tubihome_geo_lng'] : get_post_meta($post->ID, '_geo_lng', true),
            'Dirección completa' => isset($_POST['tubihome_address']) ? $_POST['tubihome_address'] : get_post_meta($post->ID, '_address', true),
        ];
        foreach ($campos as $label => $valor) {
            if (empty($valor)) $faltantes[] = $label;
        }
        if ((isset($_POST['post_ID']) && empty(get_post_thumbnail_id($_POST['post_ID']))) || !get_post_thumbnail_id($post->ID)) $faltantes[] = 'Imagen principal';
        if (!empty($faltantes)) {
            echo '<div class="notice notice-warning is-dismissible"><p>El inmueble se guardó como <strong>Borrador</strong> porque faltan campos requeridos:<br><ul style="margin-top:8px;">';
            foreach ($faltantes as $campo) {
                echo '<li><strong>' . esc_html($campo) . '</strong></li>';
            }
            echo '</ul></p></div>';
        }
    }
}
add_action('admin_notices', 'tubihome_inmueble_admin_notice');
// Gutenberg: Agregar '(requerida)' junto al enlace de imagen destacada para CPT inmueble
add_action('enqueue_block_editor_assets', function() {
    global $post;
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    $is_inmueble = false;
    if ($screen && $screen->post_type === 'inmueble') {
        $is_inmueble = true;
    } elseif (!empty($post) && isset($post->post_type) && $post->post_type === 'inmueble') {
        $is_inmueble = true;
    }
    if ($is_inmueble) {
        wp_enqueue_script(
            'tubihome-gutenberg-featured-image-required',
            plugins_url('gutenberg-featured-image-required.js', __FILE__),
            array('wp-dom-ready', 'wp-edit-post'),
            '1.0',
            true
        );
    }
});
/*
Plugin Name: Tubihome Core
Description: CPT y taxonomías para inmuebles, sin dependencias externas.
Version: 1.0
Author: Tu Nombre
*/

// Evitar acceso directo
if ( ! defined( 'ABSPATH' ) ) exit;


// 1. Registrar Custom Post Type 'inmueble'
function tubihome_register_cpt_inmueble() {
    $labels = array(
        'name' => 'Inmuebles',
        'singular_name' => 'Inmueble',
        'add_new' => 'Añadir Nuevo',
        'add_new_item' => 'Añadir Nuevo Inmueble',
        'edit_item' => 'Editar Inmueble',
        'new_item' => 'Nuevo Inmueble',
        'view_item' => 'Ver Inmueble',
        'search_items' => 'Buscar Inmuebles',
        'not_found' => 'No se encontraron inmuebles',
        'not_found_in_trash' => 'No hay inmuebles en la papelera',
        'menu_name' => 'Inmuebles',
    );
    $args = array(
        'labels' => $labels,
        'public' => true,
        'publicly_queryable' => true,
        'has_archive' => true,
        'rewrite' => array('slug' => 'propiedad'), // Cambia temporalmente el slug
        'supports' => array('title', 'editor', 'thumbnail'),
        'show_in_rest' => true,
    );
    register_post_type('inmueble', $args);
}
add_action('init', 'tubihome_register_cpt_inmueble');

// 2. Registrar taxonomías: tipo-operacion, tipo-propiedad, ciudad, colonia, amenidades
function tubihome_register_taxonomies() {
    $taxonomies = [
        'tipo-operacion' => 'Tipo de Operación',
        'tipo-propiedad' => 'Tipo de Propiedad',
        'amenidades' => 'Amenidades',
    ];
    foreach ($taxonomies as $slug => $label) {
        $args = array(
            'label' => $label,
            'hierarchical' => true,
            'show_in_rest' => true,
        );
        // Ocultar panel lateral y evitar agregar nuevas categorías en el editor
        if ($slug === 'amenidades' || $slug === 'tipo-operacion') {
            $args['show_ui'] = false;
            $args['meta_box_cb'] = false;
            $args['rewrite'] = array('slug' => $slug);
        }
        // Limitar términos de tipo-propiedad y asegurar visibilidad y rewrite
        if ($slug === 'tipo-propiedad') {
            $args['public'] = false; // No permitir agregar desde el editor
            $args['show_ui'] = true;
            $args['show_in_quick_edit'] = false;
            $args['meta_box_cb'] = false; // Quitar el metabox de añadir categorías
            // Cambiar el slug para que sea hijo de /inmueble/
            $args['rewrite'] = array(
                'slug' => 'inmueble',
                'with_front' => false,
                'hierarchical' => false
            );
            $args['query_var'] = 'tipo-propiedad';
        }
        register_taxonomy($slug, 'inmueble', $args);
    }
    // Registrar los términos fijos para tipo-propiedad
    $tipos = [
        'Casas',
        'Apartamentos',
        'Terrenos',
        'Proyectos Nuevos',
        'Residencial',
        'Locales Comerciales',
        'Oficinas'
    ];
    foreach ($tipos as $tipo) {
        if (!term_exists($tipo, 'tipo-propiedad')) {
            wp_insert_term($tipo, 'tipo-propiedad');
        }
    }
    // Eliminar metabox de ciudad y colonia en el editor de inmueble
    add_action('add_meta_boxes_inmueble', function() {
        remove_meta_box('tagsdiv-ciudad', 'inmueble', 'side');
        remove_meta_box('tagsdiv-colonia', 'inmueble', 'side');
    });
}
add_action('init', 'tubihome_register_taxonomies');

// 3. Registrar metaboxes y campos personalizados
function tubihome_register_meta_boxes() {
    add_meta_box('tubihome_inmueble_meta', 'Detalles del Inmueble', 'tubihome_inmueble_meta_callback', 'inmueble', 'normal', 'high');
}
add_action('add_meta_boxes', 'tubihome_register_meta_boxes');

function tubihome_inmueble_meta_callback($post) {
    // Información financiera y estado
    $precio = get_post_meta($post->ID, '_price', true);
    $moneda = get_post_meta($post->ID, '_currency', true);
    if (!$moneda) { $moneda = 'USD'; }
    $estado_prop = get_post_meta($post->ID, '_status', true);
    $mantenimiento = get_post_meta($post->ID, '_maintenance', true);
    // Características físicas
    $superficie_total = get_post_meta($post->ID, '_area_total', true);
    $superficie_construida = get_post_meta($post->ID, '_area_built', true);

    // Amenidades principales
    $amenidades_lista = [
        'Aire acondicionado',
        'Agua caliente',
        'Conexión a Internet (Fibra óptica)',
        'Sistema de gas centralizado',
        'Clósets empotrados',
        'Pisos de cerámica / porcelanato',
        'Línea blanca incluida',
        'Piscina',
        'Terraza o Balcón',
        'Jardín privado',
        'Área de barbacoa / Grill',
        'Gimnasio',
        'Cancha de deportes (Fútbol/Tenis/Pádel)',
        'Parque infantil',
        'Rooftop / Azotea social',
        'Seguridad privada 24/7',
        'Cámaras de vigilancia (CCTV)',
        'Cochera techada',
        'Parqueo para visitas',
        'Portón eléctrico',
        'Cisterna de agua',
        'Planta eléctrica de emergencia',
        'Ascensor (en caso de apartamentos)',
        'Pet Friendly (Mascotas permitidas)',
        'Casa Club o Salón de usos múltiples',
        'Bodega de almacenamiento',
        'Área de lavandería',
        'Senderos para caminar',
        'Paneles solares',
        'Vista panorámica (Montaña/Ciudad/Mar)'
    ];
    $amenidades_seleccionadas = get_post_meta($post->ID, '_amenidades', true);
    if (!is_array($amenidades_seleccionadas)) {
        $amenidades_seleccionadas = $amenidades_seleccionadas ? explode(',', $amenidades_seleccionadas) : [];
    }

    $recamaras = get_post_meta($post->ID, '_rooms', true);
    $banos = get_post_meta($post->ID, '_baths', true);
    $estacionamientos = get_post_meta($post->ID, '_parking', true);
    $antiguedad = get_post_meta($post->ID, '_year_built', true);
    // Ubicación
    $direccion = get_post_meta($post->ID, '_address', true);
    $lat = get_post_meta($post->ID, '_geo_lat', true);
    $lng = get_post_meta($post->ID, '_geo_lng', true);
    $maps_url = get_post_meta($post->ID, '_maps_url', true);
    // Multimedia
    $galeria = get_post_meta($post->ID, '_gallery', true);
    if (is_array($galeria)) {
        $galeria = implode(',', $galeria);
    }
    $video = get_post_meta($post->ID, '_video', true);
    ?>
    <h4>Información Financiera y Estado</h4>
    <p><label>Precio <span style="color:red">*</span>: <input type="number" name="tubihome_price" value="<?php echo esc_attr($precio); ?>" required /></label></p>
    <p><label>Moneda:
        <select name="tubihome_currency">
            <?php $currencies = ['MXN','USD','EUR'];
            foreach($currencies as $c) echo '<option value="'.$c.'"'.selected($moneda,$c,false).'>'.$c.'</option>'; ?>
        </select>
    </label></p>
    <p>Estado:
        <label><input type="radio" name="tubihome_status" value="disponible" <?php checked($estado_prop,'disponible'); ?>> Disponible</label>
        <label><input type="radio" name="tubihome_status" value="reservada" <?php checked($estado_prop,'reservada'); ?>> Reservada</label>
        <label><input type="radio" name="tubihome_status" value="vendida" <?php checked($estado_prop,'vendida'); ?>> Vendida</label>
    </p>
    <p><label>Mantenimiento/Expensas: <input type="number" name="tubihome_maintenance" value="<?php echo esc_attr($mantenimiento); ?>" /></label></p>
    <h4>Características Físicas</h4>
    <h4>Amenidades principales</h4>
    <div style="display:flex;flex-wrap:wrap;gap:18px 32px;margin-bottom:16px;">
    <?php foreach ($amenidades_lista as $amenidad): ?>
        <label style="min-width:220px;display:inline-block;">
            <input type="checkbox" name="tubihome_amenidades[]" value="<?php echo esc_attr($amenidad); ?>" <?php checked(in_array($amenidad, $amenidades_seleccionadas)); ?> />
            <?php echo esc_html($amenidad); ?>
        </label>
    <?php endforeach; ?>
    </div>
    <p><label>Superficie Total (m²): <input type="number" name="tubihome_area_total" value="<?php echo esc_attr($superficie_total); ?>" /></label></p>
    <p><label>Superficie Construida (m²): <input type="number" name="tubihome_area_built" value="<?php echo esc_attr($superficie_construida); ?>" /></label></p>
    <p><label>Recámaras: <input type="number" name="tubihome_rooms" value="<?php echo esc_attr($recamaras); ?>" /></label></p>
    <p><label>Baños: <input type="text" name="tubihome_baths" value="<?php echo esc_attr($banos); ?>" /></label></p>
    <p><label>Estacionamientos: <input type="number" name="tubihome_parking" value="<?php echo esc_attr($estacionamientos); ?>" /></label></p>
    <p><label>Año de construcción: <input type="number" name="tubihome_year_built" value="<?php echo esc_attr($antiguedad); ?>" /></label></p>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var municipioSelect = document.getElementById('tubihome_municipio');
        var distritoSelect = document.getElementById('tubihome_distrito');
        var currentDistrito = "<?php echo esc_js(get_post_meta($post->ID, 'distrito', true)); ?>";
        function updateDistritos() {
            var municipio = municipioSelect.value;
            distritoSelect.innerHTML = '<option value="">Selecciona distrito</option>';
            distritoSelect.disabled = !municipio;
            if (municipio) {
                fetch('<?php echo get_template_directory_uri(); ?>/data/distritos/' + municipio + '.json')
                    .then(response => response.ok ? response.json() : [])
                    .then(distritos => {
                        if (Array.isArray(distritos)) {
                            distritos.forEach(dis => {
                                var opt = document.createElement('option');
                                opt.value = dis;
                                opt.textContent = dis;
                                if (dis === currentDistrito) opt.selected = true;
                                distritoSelect.appendChild(opt);
                            });
                        }
                    });
            }
        }
        municipioSelect.addEventListener('change', updateDistritos);
        // Al cargar, solo habilitar distrito si municipio ya está seleccionado
        if (municipioSelect.value) {
            distritoSelect.disabled = false;
            updateDistritos();
        } else {
            distritoSelect.disabled = true;
        }
    });
    </script>
    <p><label>Dirección completa <span style="color:red">*</span>: <input type="text" name="tubihome_address" value="<?php echo esc_attr($direccion); ?>" size="50" required /></label></p>
    <p><label>Latitud <span style="color:red">*</span>: <input type="text" name="tubihome_geo_lat" value="<?php echo esc_attr($lat); ?>" required /></label></p>
    <p><label>Longitud <span style="color:red">*</span>: <input type="text" name="tubihome_geo_lng" value="<?php echo esc_attr($lng); ?>" required /></label></p>
    <p><label>Link Google Maps: <input type="url" name="tubihome_maps_url" value="<?php echo esc_attr($maps_url); ?>" size="50" /></label></p>
    <h4>Multimedia y Valor Agregado</h4>
    <p><label>Imagen destacada</label></p>
    <p>
        <label>Galería de Imágenes:</label>
        <input type="hidden" id="tubihome_gallery" name="tubihome_gallery" value="<?php echo esc_attr($galeria); ?>" />
        <button type="button" class="button" id="tubihome_gallery_btn">Seleccionar Imágenes</button>
        <div id="tubihome_gallery_preview" style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap;"></div>
    </p>
    <p><label>Video/Tour Virtual (URL): <input type="url" name="tubihome_video" value="<?php echo esc_attr($video); ?>" size="50" /></label></p>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof wp !== 'undefined' && wp.media) {
            var galleryBtn = document.getElementById('tubihome_gallery_btn');
            var galleryInput = document.getElementById('tubihome_gallery');
            var galleryPreview = document.getElementById('tubihome_gallery_preview');
            var ids = galleryInput.value ? galleryInput.value.split(',').filter(Boolean) : [];

            function renderPreview() {
                galleryPreview.innerHTML = '';
                ids.forEach(function(id) {
                    if (!id) return;
                    var attachment = wp.media.attachment(id);
                    if (attachment) {
                        attachment.fetch().then(function(){
                            var url = attachment.get('url');
                            var img = document.createElement('img');
                            img.src = url;
                            img.style.width = '60px';
                            img.style.height = '60px';
                            img.style.objectFit = 'cover';
                            img.style.borderRadius = '8px';
                            img.title = 'Eliminar';
                            img.style.cursor = 'pointer';
                            img.onclick = function() {
                                ids = ids.filter(function(val){ return val != id; });
                                galleryInput.value = ids.join(',');
                                renderPreview();
                            };
                            galleryPreview.appendChild(img);
                        });
                    }
                });
            }
            renderPreview();

            galleryBtn.addEventListener('click', function(e) {
                e.preventDefault();
                var frame = wp.media({
                    title: 'Selecciona imágenes para la galería',
                    button: { text: 'Usar estas imágenes' },
                    multiple: true
                });
                frame.on('select', function() {
                    var selection = frame.state().get('selection');
                    ids = selection.map(function(attachment){ return attachment.id.toString(); });
                    galleryInput.value = ids.join(',');
                    renderPreview();
                });
                frame.open();
            });
        }
    });
    </script>
    <?php
}

function tubihome_save_inmueble_meta($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!isset($_POST['post_type']) || $_POST['post_type'] !== 'inmueble') return;
    // Sanitizar y guardar cada campo
    $fields = [
        '_price' => 'tubihome_price',
        '_currency' => 'tubihome_currency',
        '_status' => 'tubihome_status',
        '_maintenance' => 'tubihome_maintenance',
        '_area_total' => 'tubihome_area_total',
        '_area_built' => 'tubihome_area_built',
        '_rooms' => 'tubihome_rooms',
        '_baths' => 'tubihome_baths',
        '_parking' => 'tubihome_parking',
        '_year_built' => 'tubihome_year_built',
        '_address' => 'tubihome_address',
        '_geo_lat' => 'tubihome_geo_lat',
        '_geo_lng' => 'tubihome_geo_lng',
        '_maps_url' => 'tubihome_maps_url',
        '_gallery' => 'tubihome_gallery',
        '_video' => 'tubihome_video',
    ];

    // Guardar amenidades seleccionadas y asignar términos de taxonomía
    if (isset($_POST['tubihome_amenidades']) && is_array($_POST['tubihome_amenidades'])) {
        $amenidades = array_map('sanitize_text_field', $_POST['tubihome_amenidades']);
        $amenidades_str = implode(',', $amenidades);
        update_post_meta($post_id, '_amenidades', $amenidades_str);
        // Asignar términos de la taxonomía 'amenidades'
        wp_set_object_terms($post_id, $amenidades, 'amenidades', false);
    } else {
        delete_post_meta($post_id, '_amenidades');
        wp_set_object_terms($post_id, [], 'amenidades', false);
    }
    // Validación de campos requeridos
    $required = [
        'tubihome_price' => 'El precio es obligatorio.',
        'tubihome_address' => 'La dirección completa es obligatoria.',
        'tubihome_geo_lat' => 'La latitud es obligatoria.',
        'tubihome_geo_lng' => 'La longitud es obligatoria.'
    ];
    $faltantes = false;
    foreach ($required as $input => $msg) {
        if (empty($_POST[$input])) {
            $faltantes = true;
        }
    }
    // Ya no se valida imagen principal como obligatoria
    // Si falta algún campo requerido, forzar a borrador pero no interrumpir el proceso
    if ($faltantes) {
        remove_action('save_post_inmueble', 'tubihome_save_inmueble_meta');
        wp_update_post([
            'ID' => $post_id,
            'post_status' => 'draft'
        ]);
        add_action('save_post_inmueble', 'tubihome_save_inmueble_meta');
        // No guardar metadatos si faltan campos
    } else {
        foreach ($fields as $meta_key => $input_name) {
            if (isset($_POST[$input_name])) {
                $value = $_POST[$input_name];
                // Guardar _price y _rooms como números reales
                if ($meta_key === '_price' || $meta_key === '_rooms') {
                    $value = preg_replace('/[^0-9.]/', '', $value); // Solo números y punto decimal
                    $value = $value !== '' ? (float)$value : '';
                } elseif ($meta_key === '_gallery') {
                    // Asegurar que siempre se guarde como string de IDs separados por coma
                    if (is_array($value)) {
                        $value = implode(',', array_map('intval', $value));
                    } else {
                        // Limpiar espacios y asegurar formato string
                        $value = trim($value);
                        $value = preg_replace('/[^0-9,]/', '', $value);
                    }
                } else {
                    $value = sanitize_text_field($value);
                }
                update_post_meta($post_id, $meta_key, $value);
            }
        }
    }
}
add_action('save_post_inmueble', 'tubihome_save_inmueble_meta');
