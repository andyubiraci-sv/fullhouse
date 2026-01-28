
<?php
/*
Template Name: Editar Inmueble
*/
get_header();
if (!is_user_logged_in()) {
    wp_redirect(site_url('/acceso/'));
    exit;
}
$user_id = get_current_user_id();
$post_id = 0;
if (isset($_GET['id'])) {
    $post_id = intval($_GET['id']);
}
$post = get_post($post_id);
if(!$post || $post->post_type!='inmueble' || $post->post_author!=$user_id) {
    echo '<div class="edit-error">No tienes permiso para editar este inmueble.</div>';
    get_footer();
    exit;
}
// Obtener datos actuales
$titulo = $post->post_title;
$descripcion = $post->post_content;
$precio = get_post_meta($post_id,'_price',true);
$lat = get_post_meta($post_id,'_geo_lat',true);
$lng = get_post_meta($post_id,'_geo_lng',true);
$gallery = get_post_meta($post_id,'_gallery',true);
$gallery_ids = $gallery ? explode(',',$gallery) : [];
if(isset($_POST['edit_submit'])) {
    $nuevo_estado = isset($_POST['publicar']) ? 'publish' : 'pending';
    wp_update_post([
        'ID'=>$post_id,
        'post_title'=>sanitize_text_field($_POST['field_titulo']),
        'post_content'=>sanitize_textarea_field($_POST['field_descripcion']),
        'post_status'=>$nuevo_estado
    ]);
    update_post_meta($post_id,'_price',floatval($_POST['field_precio']));
    update_post_meta($post_id,'_geo_lat',sanitize_text_field($_POST['field_lat']));
    update_post_meta($post_id,'_geo_lng',sanitize_text_field($_POST['field_lng']));
    update_post_meta($post_id,'_address',sanitize_text_field($_POST['field_address']??''));
    // Guardar municipio y distrito
    if(!empty($_POST['field_municipio'])) {
        update_post_meta($post_id, 'municipio', sanitize_text_field($_POST['field_municipio']));
    }
    if(!empty($_POST['field_distrito'])) {
        update_post_meta($post_id, 'distrito', sanitize_text_field($_POST['field_distrito']));
    }
    // Asignar taxonomía tipo-operacion
    if(!empty($_POST['field_operacion'])) {
        wp_set_object_terms($post_id, [sanitize_text_field($_POST['field_operacion'])], 'tipo-operacion');
    }
    // Asignar taxonomía tipo-propiedad
    if(!empty($_POST['field_tipo_propiedad'])) {
        wp_set_object_terms($post_id, [sanitize_text_field($_POST['field_tipo_propiedad'])], 'tipo-propiedad');
    }
    // Guardar amenidades
    if(!empty($_POST['field_amenidades']) && is_array($_POST['field_amenidades'])) {
        update_post_meta($post_id, '_amenidades', implode(',', array_map('sanitize_text_field', $_POST['field_amenidades'])));
    } else {
        update_post_meta($post_id, '_amenidades', '');
    }
    // Procesar nuevas imágenes
    if(!empty($_FILES['field_fotos']['name'][0])){
        if(!function_exists('media_handle_upload')) {
            require_once(ABSPATH.'wp-admin/includes/file.php');
            require_once(ABSPATH.'wp-admin/includes/media.php');
            require_once(ABSPATH.'wp-admin/includes/image.php');
        }
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
    wp_redirect(site_url('/cambios-guardados/?id='.$post_id));
    exit;
}
?>
<div class="edit-container">
    <h2>Editar Inmueble</h2>
    <form method="post" enctype="multipart/form-data">
                <?php
        // OPERACION (tipo-operacion)
        $operaciones = get_terms([
            'taxonomy'=>'tipo-operacion',
            'hide_empty'=>false
        ]);
        $oper_sel = '';
        $terms = wp_get_object_terms($post_id, 'tipo-operacion', ['fields'=>'names']);
        if($terms && is_array($terms)) $oper_sel = $terms[0];
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
        <input type="text" name="field_titulo" placeholder="Título" value="<?php echo esc_attr($titulo); ?>" required>
        <textarea name="field_descripcion" placeholder="Descripción" required><?php echo esc_textarea($descripcion); ?></textarea>
        <input type="number" name="field_precio" placeholder="Precio" value="<?php echo esc_attr($precio); ?>" required>
            <input type="text" name="field_address" placeholder="Dirección completa" value="<?php echo esc_attr(get_post_meta($post_id,'_address',true)); ?>" required>
        <?php
        // MUNICIPIO Y DISTRITO (igual que en publicar)
        $json_path = get_template_directory() . '/data/municipios_distritos.json';
        $data_mun = file_exists($json_path) ? json_decode(file_get_contents($json_path), true) : [];
        $municipios = [];
        foreach ($data_mun as $dis => $muns) {
            foreach ($muns as $mun) {
                if (!isset($municipios[$mun])) $municipios[$mun] = [];
                $municipios[$mun][] = $dis;
            }
        }
        $mun_sel = get_post_meta($post_id, 'municipio', true);
        $dis_sel = get_post_meta($post_id, 'distrito', true);
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
        <?php
        // TIPO DE PROPIEDAD
        $tipos_prop = get_terms([
            'taxonomy'=>'tipo-propiedad',
            'hide_empty'=>false
        ]);
        $tipo_sel = '';
        $terms = wp_get_object_terms($post_id, 'tipo-propiedad', ['fields'=>'names']);
        if($terms && is_array($terms)) $tipo_sel = $terms[0];
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
        <?php
        // AMENIDADES
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
        $amen_sel = get_post_meta($post_id, '_amenidades', true);
        $amen_sel = $amen_sel ? explode(',', $amen_sel) : [];
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
        <div id="edit-map" style="height:300px;"></div>
        <input type="hidden" name="field_lat" id="field_lat" value="<?php echo esc_attr($lat); ?>">
        <input type="hidden" name="field_lng" id="field_lng" value="<?php echo esc_attr($lng); ?>">
        <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
        <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
        <script>
        document.addEventListener('DOMContentLoaded',function(){
            var lat = <?php echo floatval($lat); ?>;
            var lng = <?php echo floatval($lng); ?>;
            var map = L.map('edit-map').setView([lat,lng],13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
            var marker = L.marker([lat,lng],{draggable:true}).addTo(map);
            marker.on('dragend',function(e){
                var pos = marker.getLatLng();
                document.getElementById('field_lat').value = pos.lat;
                document.getElementById('field_lng').value = pos.lng;
            });
        });
        </script>
        <input type="file" name="field_fotos[]" multiple>
        <div class="edit-gallery-preview">
            <?php foreach($gallery_ids as $id): $img=wp_get_attachment_image_url($id,'thumbnail'); if($img): ?>
                <img src="<?php echo esc_url($img); ?>" style="height:60px; margin:2px;">
            <?php endif; endforeach; ?>
        </div>
        <button type="submit" name="edit_submit">Guardar como Borrador</button>
        <button type="submit" name="edit_submit" value="1" formaction="" formmethod="post" name="publicar">Publicar</button>
    </form>
</div>
<?php get_footer(); ?>
