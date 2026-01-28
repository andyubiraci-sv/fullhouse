<?php
/*
Template Name: Publicar Inmueble (Wizard)
*/
get_header();
if (!is_user_logged_in()) {
    wp_redirect(site_url('/acceso/'));
    exit;
}
$user_id = get_current_user_id();
// Determinar el paso actual correctamente
if (isset($_POST['wizard_step'])) {
    $step = intval($_POST['wizard_step']);
} else {
    $step = isset($_GET['step']) ? intval($_GET['step']) : 1;
}
if(isset($_POST['wizard_next'])) {
    // Validar paso 2: Ubicación
    if($step == 2) {
        $lat = isset($_POST['field_lat']) ? trim($_POST['field_lat']) : '';
        $lng = isset($_POST['field_lng']) ? trim($_POST['field_lng']) : '';
        // DEBUG temporal
        error_log('Paso 2: lat='.$lat.' lng='.$lng);
        if($lat === '' || $lng === '' || !is_numeric($lat) || !is_numeric($lng)) {
            echo '<div class="wizard-error">Debes seleccionar una ubicación válida en el mapa.<br>Latitud: '.htmlspecialchars($lat).' | Longitud: '.htmlspecialchars($lng).'</div>';
            // $step no cambia
        } else {
            $step++;
        }
    } else {
        $step++;
    }
}
if(isset($_POST['wizard_prev'])) $step--;
if($step < 1) $step = 1;
if($step > 4) $step = 4;

// Variables para mantener datos entre pasos
$data = isset($_POST['wizard_data']) ? json_decode(stripslashes($_POST['wizard_data']), true) : [];
if(!$data) $data = [];
foreach($_POST as $k=>$v) {
    if(strpos($k,'field_')===0) {
        if($k === 'field_amenidades') {
            // Si es array, guardar como array, si es string, convertir a array
            $data[$k] = is_array($v) ? array_map('sanitize_text_field', $v) : (strlen($v) ? [sanitize_text_field($v)] : []);
        } else {
            $data[$k] = sanitize_text_field($v);
        }
    }
}

// Guardar inmueble al finalizar
if(isset($_POST['wizard_submit'])) {
    $post_id = wp_insert_post([
        'post_type'=>'inmueble',
        'post_title'=>sanitize_text_field($data['field_titulo']),
        'post_content'=>sanitize_textarea_field($data['field_descripcion']),
        'post_status'=>'pending', // Forzar siempre pendiente
        'post_author'=>$user_id
    ]);
    if($post_id && !is_wp_error($post_id)) {
        update_post_meta($post_id,'_price',floatval($data['field_precio']));
        update_post_meta($post_id,'_geo_lat',sanitize_text_field($data['field_lat']));
        update_post_meta($post_id,'_geo_lng',sanitize_text_field($data['field_lng']));
        update_post_meta($post_id,'_address',sanitize_text_field($data['field_address']??''));
        // Guardar municipio y distrito como meta
        if(!empty($data['field_municipio'])) {
            update_post_meta($post_id, 'municipio', sanitize_text_field($data['field_municipio']));
        }
        if(!empty($data['field_distrito'])) {
            update_post_meta($post_id, 'distrito', sanitize_text_field($data['field_distrito']));
        }
        // Asignar taxonomía tipo-operacion
        if(!empty($data['field_operacion'])) {
            $slug = sanitize_title($data['field_operacion']);
            wp_set_object_terms($post_id, [$slug], 'tipo-operacion');
        }
        // Asignar taxonomía tipo-propiedad
        if(!empty($data['field_tipo_propiedad'])) {
            wp_set_object_terms($post_id, [$data['field_tipo_propiedad']], 'tipo-propiedad');
        }
        // Guardar amenidades como meta (array a string)
        if(!empty($data['field_amenidades']) && is_array($data['field_amenidades'])) {
            update_post_meta($post_id, '_amenidades', implode(',', array_map('sanitize_text_field', $data['field_amenidades'])));
        } else {
            update_post_meta($post_id, '_amenidades', '');
        }
        // Procesar imágenes
        if(!empty($_FILES['field_fotos']['name'][0])){
            require_once(ABSPATH.'wp-admin/includes/file.php');
            $gallery_ids = [];
            foreach($_FILES['field_fotos']['name'] as $i=>$name){
                $_FILES['img'] = [
                    'name' => $name,
                    'type' => $_FILES['field_fotos']['type'][$i],
                    'tmp_name' => $_FILES['field_fotos']['tmp_name'][$i],
                    'error' => $_FILES['field_fotos']['error'][$i],
                    'size' => $_FILES['field_fotos']['size'][$i]
                ];
                $attach_id = media_handle_upload('img', $post_id);
                if(!is_wp_error($attach_id)) $gallery_ids[] = $attach_id;
            }
            update_post_meta($post_id,'_gallery',implode(',',$gallery_ids));
            if($gallery_ids) set_post_thumbnail($post_id,$gallery_ids[0]);
        }
        // Redirigir a página de agradecimiento
        wp_redirect(site_url('/gracias/?id='.$post_id));
        exit;
    } else {
        echo '<div class="wizard-error">Error al guardar el inmueble.</div>';
    }
}
?>
<div class="wizard-container">
    <form method="post" enctype="multipart/form-data" autocomplete="off">
        <input type="hidden" name="wizard_data" value='<?php echo json_encode($data); ?>'>
        <input type="hidden" name="wizard_step" value="<?php echo $step; ?>">
        <?php if(isset($_POST['wizard_next']) || isset($_POST['wizard_prev']) || isset($_POST['wizard_submit'])): ?>
            <pre style="color:red; font-size:14px; background:#fffbe6; border:1px solid #e0c97f; padding:10px;">DEBUG POST:
<?php print_r($_POST); ?></pre>
        <?php endif; ?>
        <?php if($step==1): ?>
            <h2>Paso 1: Información Básica</h2>
            <input type="text" name="field_titulo" placeholder="Título" value="<?php echo esc_attr($data['field_titulo']??''); ?>" required>
            <textarea name="field_descripcion" placeholder="Descripción" required><?php echo esc_textarea($data['field_descripcion']??''); ?></textarea>
            <input type="number" name="field_precio" placeholder="Precio" value="<?php echo esc_attr($data['field_precio']??''); ?>" required>
            <input type="text" name="field_address" placeholder="Dirección completa" value="<?php echo esc_attr($data['field_address']??''); ?>" required>
            <?php
            $operaciones = get_terms([
                'taxonomy'=>'tipo-operacion',
                'hide_empty'=>false
            ]);
            $oper_sel = $data['field_operacion'] ?? '';
            ?>
            <label>Operación:
                <select name="field_operacion" required>
                    <option value="">Selecciona operación</option>
                    <?php foreach($operaciones as $op):
                        $slug = sanitize_title($op->name);
                        if(!in_array($slug, ['vender', 'alquilar'])) continue;
                    ?>
                        <option value="<?php echo esc_attr($slug); ?>" <?php if($oper_sel==$slug) echo 'selected'; ?>><?php echo esc_html($op->name); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <br><br>
            <!-- MUNICIPIO Y DISTRITO -->
            <?php
            // Cargar municipios y distritos desde el JSON igual que en el admin
            $json_path = get_template_directory() . '/data/municipios_distritos.json';
            $data_mun = file_exists($json_path) ? json_decode(file_get_contents($json_path), true) : [];
            $municipios = [];
            foreach ($data_mun as $dis => $muns) {
                foreach ($muns as $mun) {
                    if (!isset($municipios[$mun])) $municipios[$mun] = [];
                    $municipios[$mun][] = $dis;
                }
            }
            $mun_sel = $data['field_municipio'] ?? '';
            $dis_sel = $data['field_distrito'] ?? '';
            ?>
            <label>Municipio / Zona:
                <select name="field_municipio" id="field_municipio" required>
                    <option value="">Selecciona municipio</option>
                    <?php foreach($municipios as $mun=>$dists): ?>
                        <option value="<?php echo esc_attr($mun); ?>" <?php if($mun_sel==$mun) echo 'selected'; ?>><?php echo esc_html($mun); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Departamento / Distrito:
                <select name="field_distrito" id="field_distrito" required <?php if(!$mun_sel) echo 'disabled'; ?>>
                    <option value="">Selecciona distrito</option>
                    <?php if($mun_sel && isset($municipios[$mun_sel])): foreach($municipios[$mun_sel] as $dist): ?>
                        <option value="<?php echo esc_attr($dist); ?>" <?php if($dis_sel==$dist) echo 'selected'; ?>><?php echo esc_html($dist); ?></option>
                    <?php endforeach; endif; ?>
                </select>
            </label>
            <script>
            document.addEventListener('DOMContentLoaded',function(){
                var municipios = <?php echo json_encode($municipios); ?>;
                var munSel = document.getElementById('field_municipio');
                var disSel = document.getElementById('field_distrito');
                munSel.addEventListener('change',function(){
                    var mun = munSel.value;
                    disSel.innerHTML = '<option value="">Selecciona distrito</option>';
                    if(mun && municipios[mun]){
                        municipios[mun].forEach(function(dist){
                            var opt = document.createElement('option');
                            opt.value = dist;
                            opt.textContent = dist;
                            disSel.appendChild(opt);
                        });
                        disSel.disabled = false;
                    }else{
                        disSel.disabled = true;
                    }
                });
            });
            </script>
            <br>
            <!-- TIPO DE PROPIEDAD -->
            <?php
            $tipos_prop = get_terms([
                'taxonomy'=>'tipo-propiedad',
                'hide_empty'=>false
            ]);
            $tipo_sel = $data['field_tipo_propiedad'] ?? '';
            ?>
            <label>Tipo de propiedad:
                <select name="field_tipo_propiedad" required>
                    <option value="">Selecciona tipo</option>
                    <?php foreach($tipos_prop as $tipo): ?>
                        <option value="<?php echo esc_attr($tipo->name); ?>" <?php if($tipo_sel==$tipo->name) echo 'selected'; ?>><?php echo esc_html($tipo->name); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <br>
            <!-- AMENIDADES -->
            <?php
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
            $amen_sel = $data['field_amenidades'] ?? [];
            if(!is_array($amen_sel)) $amen_sel = explode(',', $amen_sel);
            ?>
            <fieldset style="margin-top:10px;">
                <legend>Amenidades:</legend>
                <div style="display:flex;flex-wrap:wrap;gap:12px 24px;">
                <?php foreach($amenidades_lista as $am): ?>
                    <label style="min-width:200px;display:inline-block;">
                        <input type="checkbox" name="field_amenidades[]" value="<?php echo esc_attr($am); ?>" <?php if(in_array($am,$amen_sel)) echo 'checked'; ?>>
                        <?php echo esc_html($am); ?>
                    </label>
                <?php endforeach; ?>
                </div>
            </fieldset>
        <?php elseif($step==2): ?>
            <h2>Paso 2: Ubicación</h2>
            <div id="wizard-map" style="height:300px;"></div>
            <input type="hidden" name="field_lat" id="field_lat" value="<?php echo esc_attr($data['field_lat']??''); ?>">
            <input type="hidden" name="field_lng" id="field_lng" value="<?php echo esc_attr($data['field_lng']??''); ?>">
            <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
            <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
            <script>
            document.addEventListener('DOMContentLoaded',function(){
                var lat = <?php echo isset($data['field_lat']) ? floatval($data['field_lat']) : '13.6929'; ?>;
                var lng = <?php echo isset($data['field_lng']) ? floatval($data['field_lng']) : '-89.2182'; ?>;
                var map = L.map('wizard-map').setView([lat,lng],13);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
                var marker = L.marker([lat,lng],{draggable:true}).addTo(map);
                // Inicializar los campos hidden al cargar
                document.getElementById('field_lat').value = lat;
                document.getElementById('field_lng').value = lng;
                marker.on('dragend',function(e){
                    var pos = marker.getLatLng();
                    document.getElementById('field_lat').value = pos.lat;
                    document.getElementById('field_lng').value = pos.lng;
                });
            });
            </script>
        <?php elseif($step==3): ?>
            <h2>Paso 3: Imágenes</h2>
            <input type="file" name="field_fotos[]" multiple required>
            <small>Puedes subir varias imágenes. Se ajustarán al diseño automáticamente.</small>
        <?php elseif($step==4): ?>
            <h2>Paso 4: Confirmación</h2>
            <p>Revisa tus datos y haz clic en "Publicar" para enviar tu inmueble a revisión.</p>
            <ul>
                <li><b>Título:</b> <?php echo esc_html($data['field_titulo']??''); ?></li>
                <li><b>Descripción:</b> <?php echo esc_html($data['field_descripcion']??''); ?></li>
                <li><b>Precio:</b> <?php echo esc_html($data['field_precio']??''); ?></li>
                <li><b>Tipo de operación:</b> <?php echo esc_html($data['field_operacion']??''); ?></li>
                <li><b>Municipio:</b> <?php echo esc_html($data['field_municipio']??''); ?></li>
                <li><b>Distrito:</b> <?php echo esc_html($data['field_distrito']??''); ?></li>
                <li><b>Tipo de propiedad:</b> <?php echo esc_html($data['field_tipo_propiedad']??''); ?></li>
                <li><b>Amenidades:</b> <?php
                    $amens = $data['field_amenidades'] ?? [];
                    if (is_string($amens)) {
                        $amens = strlen($amens) ? explode(',', $amens) : [];
                    }
                    $amens = array_filter(array_map('trim', (array)$amens));
                    echo $amens ? esc_html(implode(', ', $amens)) : 'Ninguna';
                ?></li>
                <li><b>Ubicación:</b> Lat: <?php echo esc_html($data['field_lat']??''); ?>, Lng: <?php echo esc_html($data['field_lng']??''); ?></li>
            </ul>
        <?php endif; ?>
        <div class="wizard-nav">
            <?php if($step>1): ?><button type="submit" name="wizard_prev">Anterior</button><?php endif; ?>
            <?php if($step<4): ?><button type="submit" name="wizard_next">Siguiente</button><?php else: ?><button type="submit" name="wizard_submit">Publicar</button><?php endif; ?>
        </div>
    </form>
</div>
<?php get_footer(); ?>
