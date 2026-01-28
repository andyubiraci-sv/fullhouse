<?php get_header(); ?>
<main>
   
<style>
#buscadorgeneral {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
    background: #f5f5f5;
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 20px;
}


.filter-wrapper {
    max-width: 1200px;
    margin: 0px auto;
    padding: 15px;
}

.mockup-search-bar {
    display: flex;
    align-items: flex-end;
    background: #fff;
    border: 1px solid #ccc;
    border-radius: 15px;
    padding: 20px 25px;
    gap: 20px;
}

.filter-field {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.filter-field label {
    font-size: 14px;
    color: #666;
    font-family: Arial, sans-serif;
}

.select-custom {
    background: #f8f9fa; /* Gris claro del mockup */
    border-radius: 8px;
    padding: 10px;
    position: relative;
    display: flex;
    align-items: center;
}

.select-custom select {
    width: 100%;
    border: none;
    background: transparent;
    outline: none;
    appearance: none; /* Quita la flecha por defecto */
    font-size: 14px;
    color: #333;
    cursor: pointer;
}

/* Flecha gris del mockup */
.select-custom::after {
    content: '▼';
    font-size: 12px;
    color: #999;
    position: absolute;
    right: 15px;
    pointer-events: none;
}

/* Selector de Vista (Circulitos) */
.view-selector {
    border: 2px solid #777;
    border-radius: 30px;
    padding: 8px 15px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 2px;
}

.view-option {
    display: flex;
    align-items: center;
    gap: 5px;
}

.view-option .dot {
    width: 10px;
    height: 10px;
    border: 1px solid #777;
    border-radius: 50%;
}

.view-option .line {
    width: 20px;
    height: 6px;
    border: 1px solid #777;
}

/* Botón Negro */
.btn-buscar {
    background: #000;
    color: #fff;
    border: none;
    padding: 15px 40px;
    border-radius: 30px;
    font-weight: bold;
    font-size: 16px;
    cursor: pointer;
    letter-spacing: 1px;
    transition: transform 0.2s;
}

.btn-buscar:hover {
    transform: scale(1.03);
}

.filter-wrapper {
    display: flex;
    flex-direction: row;
    flex-wrap: nowrap;
    justify-content: space-evenly;
    align-items: flex-end;
}

/* Ajuste para Móviles */
@media (max-width: 992px) {
    .mockup-search-bar {
        flex-direction: column;
        align-items: stretch;
        border-radius: 20px;
    }
    .view-selector {
        flex-direction: row;
        justify-content: center;
    }
}


</style>

<form role="search" method="get" action="<?php echo esc_url( get_post_type_archive_link('inmueble') ); ?>">
<div class="filter-wrapper">
   
        
        <div class="filter-field">
           <label>
            Operación: </label>
            <div class="select-custom">
            <select name="tipo-operacion">
                <option value="">Todas</option>
                <?php
                $ops = get_terms(['taxonomy' => 'tipo-operacion', 'hide_empty' => false]);
                foreach ($ops as $op) {
                    $selected = (isset($_GET['tipo-operacion']) && $_GET['tipo-operacion'] === $op->slug) ? 'selected' : '';
                    echo '<option value="' . esc_attr($op->slug) . '" ' . $selected . '>' . esc_html($op->name) . '</option>';
                }
                ?>
            </select>
            </div>
        </div>

        <div class="filter-field">
            <label>
            Tipo de Propiedad: </label>
            <div class="select-custom">
            <select name="tipo-propiedad">
                <option value="">Todas</option>
                <?php
                $tipos_fijos = [
                    'Casas',
                    'Apartamentos',
                    'Terrenos',
                    'Proyectos Nuevos',
                    'Residencial',
                    'Locales Comerciales',
                    'Oficinas'
                ];
                foreach ($tipos_fijos as $tipo) {
                    $term = get_term_by('name', $tipo, 'tipo-propiedad');
                    if ($term) {
                        $selected = (isset($_GET['tipo-propiedad']) && $_GET['tipo-propiedad'] === $term->slug) ? 'selected' : '';
                        echo '<option value="' . esc_attr($term->slug) . '" ' . $selected . '>' . esc_html($term->name) . '</option>';
                    }
                }
                ?>
            </select>
       </div>
        </div>

        <div class="filter-field">
        
       
        <?php
        // Unificar municipios y distritos usando el mismo JSON que el metabox
        $json_path = get_template_directory() . '/data/municipios_distritos.json';
        $data = file_exists($json_path) ? json_decode(file_get_contents($json_path), true) : [];
        $municipios = [];
        foreach ($data as $dis => $muns) {
            foreach ($muns as $mun) {
                if (!isset($municipios[$mun])) $municipios[$mun] = [];
                $municipios[$mun][] = $dis;
            }
        }
        ?>
        <label>
          Zona </label>
             <div class="select-custom">
            <select id="filtro-municipio" name="municipio">
                <option value="">Todos</option>
                <?php
                foreach (array_keys($municipios) as $mun) {
                    $selected = (isset($_GET['municipio']) && $_GET['municipio'] === $mun) ? 'selected' : '';
                    echo '<option value="' . esc_attr($mun) . '" ' . $selected . '>' . esc_html($mun) . '</option>';
                }
                ?>
            </select>
             </div>
        </div>

        <div class="filter-field">
            <label>
           Distrito: </label> <div class="select-custom">
            <select id="filtro-distrito" name="distrito">
                <option value="">Todos</option>
                <?php
                if (isset($_GET['municipio']) && $_GET['municipio'] && isset($municipios[$_GET['municipio']])) {
                    foreach ($municipios[$_GET['municipio']] as $dis) {
                        $selected = (isset($_GET['distrito']) && $_GET['distrito'] === $dis) ? 'selected' : '';
                        echo '<option value="' . esc_attr($dis) . '" ' . $selected . '>' . esc_html($dis) . '</option>';
                    }
                }
                ?>
            </select>
       </div>
        <script>
        document.addEventListener("DOMContentLoaded", function() {
            const municipios = <?php echo json_encode($municipios); ?>;
            const municipioSelect = document.getElementById("filtro-municipio");
            const distritoSelect = document.getElementById("filtro-distrito");
            const currentDistrito = "<?php echo isset($_GET['distrito']) ? esc_js($_GET['distrito']) : ''; ?>";
            function updateDistritos() {
                const municipio = municipioSelect.value;
                distritoSelect.innerHTML = '<option value="">Seleccione distrito</option>';
                if (municipios[municipio]) {
                    municipios[municipio].forEach(dis => {
                        const opt = document.createElement("option");
                        opt.value = opt.textContent = dis;
                        if (dis === currentDistrito) opt.selected = true;
                        distritoSelect.appendChild(opt);
                    });
                }
            }
            municipioSelect.addEventListener("change", updateDistritos);
            if (municipioSelect.value && distritoSelect.options.length <= 1) updateDistritos();
        });
        </script>
        </div>

       

        <div>
                       
            <button id="btn-fltrado" type="submit"  style="border: 0px; background: none; color: #555; cursor: pointer; font-size: 14px; text-decoration: underline; padding: 0; margin-left: 10px;">
            
<svg xmlns="http://www.w3.org/2000/svg" id="Layer_2" data-name="Layer 2" viewBox="0 0 16 16" width="30" height="30"><path d="M13,.5H3A2.5026,2.5026,0,0,0,.5,3V13A2.5026,2.5026,0,0,0,3,15.5H13A2.5026,2.5026,0,0,0,15.5,13V3A2.5026,2.5026,0,0,0,13,.5ZM14.5,13A1.5017,1.5017,0,0,1,13,14.5H3A1.5017,1.5017,0,0,1,1.5,13V3A1.5017,1.5017,0,0,1,3,1.5H13A1.5017,1.5017,0,0,1,14.5,3Z"/><path d="M10.2933,9.5863A3.4662,3.4662,0,0,0,11,7.5,3.5,3.5,0,1,0,7.5,11a3.4662,3.4662,0,0,0,2.0863-.7067l1.56,1.56a.5.5,0,0,0,.707-.707ZM5,7.5A2.5,2.5,0,1,1,9.2682,9.2655l-.0016.0011-.001.0016A2.4988,2.4988,0,0,1,5,7.5Z"/></svg>
    

            </button>
            <button type="button" id="reset-filtros" style="border: 0px; background: none; color: #555; cursor: pointer; font-size: 14px; text-decoration: underline; padding: 0; margin-left: 10px;">
                <svg height="30" viewBox="0 -14 512.00128 512" width="30" xmlns="http://www.w3.org/2000/svg"><path d="m508.910156 79.617188-37.320312-27.132813v-35.746094c0-6.890625-5.609375-12.5-12.5-12.5h-19.90625c-6.894532 0-12.5 5.609375-12.5 12.5v3.097657l-23.671875-17.207032c-4.820313-3.503906-12.089844-3.503906-16.90625 0l-105.898438 76.988282c-3.351562 2.4375-4.09375 7.125-1.65625 10.476562 1.46875 2.019531 3.753907 3.09375 6.070313 3.09375 1.53125 0 3.074218-.46875 4.40625-1.4375l11.8125-8.585938v91.230469h-10.949219c-4.144531 0-7.5 3.355469-7.5 7.5 0 4.140625 3.355469 7.5 7.5 7.5h142.109375c4.144531 0 7.5-3.359375 7.5-7.5 0-4.144531-3.355469-7.5-7.5-7.5h-5.871094v-71.574219c0-6.195312-5.039062-11.238281-11.238281-11.238281h-40.667969c-6.195312 0-11.234375 5.042969-11.234375 11.238281v71.574219h-47.148437l-.003906-100.894531c.050781-.519531.613281-1.628906.957031-1.933594l77.765625-56.539062 77.726562 56.507812c.390625.355469.945313 1.445313.992188 1.902344v100.957031h-5.277344c-4.140625 0-7.5 3.355469-7.5 7.5s3.359375 7.5 7.5 7.5h31.226562c4.144532 0 7.5-3.355469 7.5-7.5s-3.355468-7.5-7.5-7.5h-10.945312v-91.230469l11.8125 8.585938c3.351562 2.4375 8.039062 1.695312 10.476562-1.65625 2.433594-3.351562 1.691407-8.039062-1.660156-10.476562zm-130.925781 26.964843h33.144531v67.8125h-33.144531zm78.605469-65.003906-14.90625-10.835937v-11.503907h14.90625zm0 0"/><path d="m479.425781 325.355469-39.636719-28.816407c-.003906 0-.007812-.003906-.011718-.007812l-17.910156-13.019531v-58.054688c0-6.894531-5.605469-12.5-12.5-12.5h-34.878907c-6.890625 0-12.5 5.605469-12.5 12.5v14.523438l-43.507812-31.632813c-4.820313-3.503906-12.089844-3.503906-16.90625 0l-56.4375 41.03125v-74.476562l15.921875 11.578125c3.351562 2.433593 8.039062 1.691406 10.476562-1.65625 2.433594-3.351563 1.691406-8.042969-1.65625-10.480469l-31.859375-23.15625c-.003906-.007812-.011719-.011719-.019531-.015625l-12.445312-9.050781v-43.460938c0-6.890625-5.605469-12.5-12.5-12.5h-25.082032c-6.894531 0-12.5 5.609375-12.5 12.5v7.050782l-30.535156-22.195313c-.296875-.21875-.605469-.421875-.925781-.613281-.042969-.027344-.089844-.046875-.132813-.074219-2.160156-1.265625-4.738281-1.960937-7.394531-1.960937-2.644531 0-5.214844.691406-7.367187 1.945312-.054688.03125-.109376.058594-.164063.089844-.316406.191406-.625.394531-.925781.613281l-93.0625 67.65625c-.003906.003906-.011719.007813-.019532.015625l-31.855468 23.160156c-3.347656 2.4375-4.089844 7.125-1.65625 10.476563 1.46875 2.019531 3.753906 3.089843 6.074218 3.089843 1.527344 0 3.070313-.464843 4.402344-1.433593l15.921875-11.574219v110.878906h-14.144531c-4.140625 0-7.5 3.355469-7.5 7.5 0 4.140625 3.359375 7.5 7.5 7.5h160.742188l-33.796876 24.570313c-3.351562 2.4375-4.09375 7.128906-1.65625 10.480469 2.433594 3.347656 7.125 4.089843 10.476563 1.652343l23.6875-17.222656v148.035156h-20.1875c-4.140625 0-7.5 3.355469-7.5 7.5 0 4.140625 3.359375 7.5 7.5 7.5h314.148437c4.144532 0 7.5-3.359375 7.5-7.5 0-4.144531-3.355468-7.5-7.5-7.5h-20.1875v-41.660156c0-4.140625-3.359374-7.5-7.5-7.5-4.144531 0-7.5 3.359375-7.5 7.5v41.660156h-78.261718v-111.160156c0-7.230469-5.882813-13.113281-13.109375-13.113281h-61.027344c-7.230469 0-13.109375 5.882812-13.109375 13.113281v111.160156h-78.261719l-.003906-157.699219c.050781-.519531.605469-1.617187.953125-1.929687l120.9375-87.925781 120.890625 87.890625c.390625.355469.945313 1.445312.992187 1.902343v80.097657c0 4.144531 3.355469 7.5 7.5 7.5 4.144532 0 7.5-3.355469 7.5-7.5v-70.371094l23.691407 17.222656c1.332031.96875 2.875 1.4375 4.40625 1.4375 2.316406 0 4.605469-1.074219 6.070312-3.09375 2.433594-3.351562 1.691407-8.039062-1.65625-10.476562zm-198.023437 33.671875h57.25v109.273437h-57.25zm-90.929688-257.863282h20.082032v30.054688l-20.082032-14.601562zm-147.640625 64.078126c.046875-.523438.609375-1.625.953125-1.933594l92.699219-67.394532 92.660156 67.363282c.390625.355468.945313 1.445312.992188 1.898437v95.105469l-35.078125 25.5h-22.832032v-29.144531c0-4.140625-3.359374-7.5-7.5-7.5-4.140624 0-7.5 3.359375-7.5 7.5v29.144531h-41.484374v-82.152344h41.484374v14.011719c0 4.140625 3.359376 7.5 7.5 7.5 4.140626 0 7.5-3.359375 7.5-7.5v-17.125c0-6.554687-5.332031-11.886719-11.886718-11.886719h-47.710938c-6.554687 0-11.886718 5.332032-11.886718 11.886719v85.269531h-57.910157zm334.160157 62.714843h29.878906v44.648438l-29.878906-21.722657zm0 0"/><path d="m379.460938 344.027344c-6.894532 0-12.5 5.605468-12.5 12.5v65.167968c0 6.894532 5.605468 12.5 12.5 12.5h24.9375c6.894531 0 12.5-5.605468 12.5-12.5v-65.167968c0-6.894532-5.605469-12.5-12.5-12.5zm22.4375 75.167968h-19.9375v-60.167968h19.9375zm0 0"/><path d="m215 344.027344c-6.890625 0-12.5 5.605468-12.5 12.5v65.167968c0 6.894532 5.609375 12.5 12.5 12.5h24.941406c6.890625 0 12.5-5.605468 12.5-12.5v-65.167968c0-6.894532-5.609375-12.5-12.5-12.5zm22.441406 75.167968h-19.941406v-60.167968h19.941406zm0 0"/></svg>
            </button>
             <button id="btn-pop-filtro" style="background:none;border:none;padding:0;margin:0;cursor:pointer;vertical-align:middle;" aria-label="Abrir filtros">
                            <svg id="Layer_1" height="30" viewBox="0 0 512 512" width="30" xmlns="http://www.w3.org/2000/svg"><path d="m16 133.612h260.513c7.186 29.034 33.45 50.627 64.673 50.627s57.487-21.593 64.673-50.627h90.141c8.836 0 16-7.164 16-16s-7.164-16-16-16h-90.142c-7.185-29.034-33.449-50.628-64.673-50.628s-57.488 21.594-64.673 50.628h-260.512c-8.836 0-16 7.164-16 16s7.164 16 16 16zm325.186-50.628c19.094 0 34.628 15.534 34.628 34.627 0 19.094-15.534 34.628-34.628 34.628s-34.628-15.534-34.628-34.628c0-19.093 15.534-34.627 34.628-34.627zm-325.186 189.016h90.142c7.186 29.034 33.449 50.627 64.673 50.627s57.487-21.593 64.673-50.627h260.512c8.836 0 16-7.164 16-16s-7.164-16-16-16h-260.513c-7.186-29.034-33.449-50.628-64.673-50.628s-57.487 21.594-64.673 50.628h-90.141c-8.836 0-16 7.164-16 16s7.163 16 16 16zm154.814-50.628c19.094 0 34.628 15.534 34.628 34.628 0 19.093-15.534 34.627-34.628 34.627s-34.628-15.534-34.628-34.627c0-19.094 15.534-34.628 34.628-34.628zm325.186 157.016h-90.142c-7.186-29.034-33.449-50.628-64.673-50.628s-57.487 21.594-64.673 50.628h-260.512c-8.836 0-16 7.164-16 16s7.164 16 16 16h260.513c7.186 29.034 33.449 50.628 64.673 50.628s57.487-21.594 64.673-50.628h90.141c8.836 0 16-7.164 16-16s-7.163-16-16-16zm-154.814 50.628c-19.094 0-34.628-15.534-34.628-34.628s15.534-34.628 34.628-34.628 34.628 15.534 34.628 34.628-15.534 34.628-34.628 34.628z"/></svg>
                        </button>
        </div>

         
</div>
<div id="chips-filtro" style="display:flex;flex-wrap:wrap;gap:8px;margin:8px 0 10px 0;"></div>
<style>
.chip-filtro {
    display: inline-flex;
    align-items: center;
    background: #f5f5f5;
    color: #145a37;
    border-radius: 16px;
    padding: 5px 14px 5px 10px;
    font-size: 14px;
    margin: 0 4px 4px 0;
    border: 1px solid #e0e0e0;
    box-shadow: 0 1px 4px #145a3708;
    transition: background 0.2s;
}
.chip-filtro .chip-x {
    margin-left: 8px;
    color: #888;
    font-weight: bold;
    cursor: pointer;
    font-size: 15px;
    border: none;
    background: none;
    padding: 0;
}
.chip-filtro:hover {
    background: #e8f5ee;
}
</style>
<script>
// Muestra los filtros activos como chips y permite quitarlos
function renderChipsFiltro() {
    const chipsDiv = document.getElementById('chips-filtro');
    if (!chipsDiv) return;
    chipsDiv.innerHTML = '';
    const params = new URLSearchParams(window.location.search);
    const labels = {
        'tipo-operacion': 'Operación',
        'tipo-propiedad': 'Propiedad',
        'municipio': 'Zona',
        'distrito': 'Distrito',
        'min_price': 'Precio mínimo',
        'max_price': 'Precio máximo',
        'habitaciones': 'Habitaciones',
        'amenidades[]': 'Amenidad'
    };
    // Amenidades (pueden ser varias)
    const amenidades = params.getAll('amenidades[]');
    amenidades.forEach(val => {
        const chip = document.createElement('span');
        chip.className = 'chip-filtro';
        chip.textContent = 'Amenidad: ' + val;
        const x = document.createElement('button');
        x.className = 'chip-x';
        x.type = 'button';
        x.innerHTML = '&times;';
        x.setAttribute('data-param','amenidades[]');
        x.setAttribute('data-value', val);
        chip.appendChild(x);
        chipsDiv.appendChild(chip);
    });
    // Otros filtros
    Object.keys(labels).forEach(key => {
        if (key === 'amenidades[]') return;
        const val = params.get(key);
        if (val && val !== '') {
            const chip = document.createElement('span');
            chip.className = 'chip-filtro';
            chip.textContent = labels[key] + ': ' + val;
            const x = document.createElement('button');
            x.className = 'chip-x';
            x.type = 'button';
            x.innerHTML = '&times;';
            x.setAttribute('data-param', key);
            chip.appendChild(x);
            chipsDiv.appendChild(chip);
        }
    });
    // Delegación de eventos para quitar chips
    chipsDiv.addEventListener('click', function(e) {
        if (e.target.classList.contains('chip-x')) {
            const param = e.target.getAttribute('data-param');
            const value = e.target.getAttribute('data-value');
            if (param === 'amenidades[]' && value) {
                // Eliminar solo esa amenidad
                let newParams = new URLSearchParams(window.location.search);
                let all = newParams.getAll('amenidades[]').filter(a => a !== value);
                newParams.delete('amenidades[]');
                all.forEach(a => newParams.append('amenidades[]', a));
                window.location.search = newParams.toString();
            } else if (param) {
                let newParams = new URLSearchParams(window.location.search);
                newParams.delete(param);
                window.location.search = newParams.toString();
            }
        }
    });
}
document.addEventListener('DOMContentLoaded', renderChipsFiltro);
window.addEventListener('popstate', renderChipsFiltro);
</script>


   
       
       
<div id="modal-btn-pop-filtro" style="display:none;position:fixed;z-index:9999;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.35);justify-content:center;align-items:center;">
    <div id="modal-filtros-content" style="background:#fff;max-width:600px;width:95vw;padding:32px 24px 24px 24px;border-radius:18px;box-shadow:0 8px 32px #0002;position:relative;">
        <button id="cerrar-modal-filtros" style="position:absolute;top:12px;right:12px;background:#eee;border:none;border-radius:50%;width:32px;height:32px;font-size:20px;cursor:pointer;">&times;</button>
    <fieldset style="border:1px solid #eee;padding:12px 18px;border-radius:8px;margin-bottom:12px;">
            <legend style="font-weight:bold;color:#145a37;">Amenidades</legend>
            <?php
            // Obtener todos los términos de la taxonomía 'amenidades'
            $terms = get_terms([
                'taxonomy' => 'amenidades',
                'hide_empty' => false
            ]);
            // Separar top6 y resto por slug (puedes personalizar el top6 aquí)
            $top6_slugs = ['agua-caliente','aire-acondicionado','conexion-a-internet-fibra-optica','jardin-privado','linea-blanca-incluida','terraza-o-balcon'];
            $top6 = [];
            $resto = [];
            foreach ($terms as $term) {
                $icono = '';
                switch ($term->slug) {
                    case 'agua-caliente': $icono = '💧'; break;
                    case 'aire-acondicionado': $icono = '❄️'; break;
                    case 'conexion-a-internet-fibra-optica': $icono = '🌐'; break;
                    case 'jardin-privado': $icono = '🌳'; break;
                    case 'linea-blanca-incluida': $icono = '🧺'; break;
                    case 'terraza-o-balcon': $icono = '🌅'; break;
                    // Puedes agregar más iconos según tus términos
                }
                if (in_array($term->slug, $top6_slugs)) {
                    $top6[] = [$term, $icono];
                } else {
                    $resto[] = [$term, $icono];
                }
            }
            $amenidades_get = isset($_GET['amenidades']) ? (array)$_GET['amenidades'] : [];
            ?>
            <label for="filtro-amenidades" style="display:block;margin-bottom:8px;">Amenidades:</label>
            <div class="chips-amenidades" style="display:flex;flex-wrap:wrap;gap:8px;">
                <?php foreach ($top6 as [$term, $icono]): ?>
                    <?php $checked = in_array($term->slug, $amenidades_get) ? 'checked' : ''; ?>
                    <label class="chip-amenidad">
                        <input type="checkbox" name="amenidades[]" value="<?php echo esc_attr($term->slug); ?>" style="display:none;" <?php echo $checked; ?> />
                        <span class="chip-icon"><?php echo $icono; ?></span>
                        <span class="chip-label"><?php echo esc_html($term->name); ?></span>
                        <?php if ($checked): ?><span class="chip-check">✔️</span><?php endif; ?>
                    </label>
                <?php endforeach; ?>
            </div>
            <button type="button" id="btn-mas-amenidades" style="margin:12px 0 8px 0;padding:6px 18px;border-radius:16px;border:1px solid #145a37;background:#fff;color:#145a37;cursor:pointer;">Más filtros <span id="contador-mas-filtros" style="display:none;"></span></button>
            <div id="panel-mas-amenidades" style="display:none;flex-direction:column;gap:16px;margin-top:8px;">
                <div class="grupo-amenidades">
                    <div style="font-weight:600;color:#145a37;margin-bottom:4px;font-size:15px;">Otras amenidades</div>
                    <div style="display:flex;flex-wrap:wrap;gap:8px;">
                        <?php foreach ($resto as [$term, $icono]): ?>
                            <?php $checked = in_array($term->slug, $amenidades_get) ? 'checked' : ''; ?>
                            <label class="chip-amenidad">
                                <input type="checkbox" name="amenidades[]" value="<?php echo esc_attr($term->slug); ?>" style="display:none;" <?php echo $checked; ?> />
                                <span class="chip-icon"><?php echo $icono; ?></span>
                                <span class="chip-label"><?php echo esc_html($term->name); ?></span>
                                <?php if ($checked): ?><span class="chip-check">✔️</span><?php endif; ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <button type="button" id="limpiar-amenidades" style="margin:10px 0 0 0;padding:6px 18px;border-radius:16px;border:1px solid #ccc;background:#fff;color:#888;cursor:pointer;">Limpiar todo</button>
            </div>
            <div id="amenidades-resumen" style="margin:8px 0 0 0;font-size:14px;color:#145a37;"></div>
            <script>
            // Panel avanzado y contador
            const btnMasAmenidades = document.getElementById('btn-mas-amenidades');
            const panelMasAmenidades = document.getElementById('panel-mas-amenidades');
            const contadorMasFiltros = document.getElementById('contador-mas-filtros');
            const checkboxesAmenidades = document.querySelectorAll('.chip-amenidad input[type="checkbox"]');
            const limpiarBtn = document.getElementById('limpiar-amenidades');
            function actualizarContadorMasFiltros() {
                let total = 0;
                document.querySelectorAll('#panel-mas-amenidades .chip-amenidad input[type="checkbox"]:checked').forEach(()=>total++);
                if (total > 0) {
                    contadorMasFiltros.textContent = '('+total+')';
                    contadorMasFiltros.style.display = 'inline';
                } else {
                    contadorMasFiltros.textContent = '';
                    contadorMasFiltros.style.display = 'none';
                }
            }
            btnMasAmenidades.addEventListener('click', function() {
                const visible = panelMasAmenidades.style.display === 'flex' || panelMasAmenidades.style.display === 'block';
                panelMasAmenidades.style.display = visible ? 'none' : 'flex';
                this.setAttribute('aria-expanded', !visible);
            });
            checkboxesAmenidades.forEach(function(chk){
                chk.addEventListener('change', actualizarContadorMasFiltros);
            });
            if (limpiarBtn) {
                limpiarBtn.addEventListener('click', function(){
                    checkboxesAmenidades.forEach(function(chk){ chk.checked = false; });
                    actualizarContadorMasFiltros();
                    actualizarResumenAmenidades();
                    actualizarURLAmenidades();
                });
            }
            actualizarContadorMasFiltros();
            // Resumen de selección
            function actualizarResumenAmenidades() {
                var chips = document.querySelectorAll('.chip-amenidad input[type="checkbox"]:checked');
                var nombres = Array.from(chips).map(function(chk){ return chk.value; });
                var resumen = document.getElementById('amenidades-resumen');
                if (nombres.length > 0) {
                    resumen.textContent = 'Amenidades seleccionadas: ' + nombres.join(', ');
                } else {
                    resumen.textContent = '';
                }
            }
            checkboxesAmenidades.forEach(function(chk){
                chk.addEventListener('change', function(){
                    actualizarResumenAmenidades();
                    actualizarURLAmenidades();
                });
            });
            actualizarResumenAmenidades();

            // Sincronización con la URL (pushState)
            function actualizarURLAmenidades() {
                const form = document.querySelector('.inmuebles-filtros');
                const params = new URLSearchParams(new FormData(form));
                // Solo mantener los amenidades seleccionados
                let amenidades = [];
                checkboxesAmenidades.forEach(function(chk){
                    if (chk.checked) amenidades.push(chk.value);
                });
                if (amenidades.length > 0) {
                    params.delete('amenidades[]');
                    amenidades.forEach(a => params.append('amenidades[]', a));
                } else {
                    params.delete('amenidades[]');
                }
                const url = window.location.pathname + '?' + params.toString();
                window.history.replaceState({}, '', url);
            }

            // Restaurar estado desde la URL al cargar (por si el usuario comparte el link)
            document.addEventListener('DOMContentLoaded', function() {
                const params = new URLSearchParams(window.location.search);
                const amenidades = params.getAll('amenidades[]');
                if (amenidades.length > 0) {
                    checkboxesAmenidades.forEach(function(chk){
                        chk.checked = amenidades.includes(chk.value);
                    });
                    actualizarContadorMasFiltros();
                    actualizarResumenAmenidades();
                }
            });
            </script>
            <style>
            .chip-amenidad input[type="checkbox"]:checked + .chip-label {
                font-weight:bold;
            }
            .chip-amenidad input[type="checkbox"]:checked ~ span {
                color:#fff;
            }
            .chip-amenidad:hover {
                box-shadow:0 2px 12px #145a3722;
                border-color:#145a37;
            }
            </style>
                </fieldset>
     <div id="filtro-de-rangos">
         <label>
            Precio mínimo:
            <input type="number" name="min_price" value="<?php echo isset($_GET['min_price']) ? esc_attr($_GET['min_price']) : ''; ?>" min="0">
        </label>
        <label>
            Precio máximo:
            <input type="number" name="max_price" value="<?php echo isset($_GET['max_price']) ? esc_attr($_GET['max_price']) : ''; ?>" min="0">
        </label>
        <label>
            Habitaciones mínimas:
            <input type="number" name="habitaciones" value="<?php echo isset($_GET['habitaciones']) ? esc_attr($_GET['habitaciones']) : ''; ?>" min="0">
        </label>
    </div> 
            </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var modal = document.getElementById('modal-btn-pop-filtro');
    var btn = document.getElementById('btn-pop-filtro');
    var cerrar = document.getElementById('cerrar-modal-filtros');
    if(btn && modal) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            modal.style.display = 'flex';
        });
    }
    if(cerrar && modal) {
        cerrar.addEventListener('click', function() {
            modal.style.display = 'none';
        });
    }
    // Cerrar modal al hacer click fuera del contenido
    modal.addEventListener('click', function(e) {
        if(e.target === modal) modal.style.display = 'none';
    });
});
</script>

         
</div>
       
    
       
         <script>
        document.getElementById('reset-filtros').addEventListener('click', function() {
            const form = this.closest('form');
            Array.from(form.elements).forEach(el => {
                if (el.tagName === 'SELECT' || el.tagName === 'INPUT') {
                    if (el.type === 'hidden') return;
                    el.value = '';
                }
            });
            window.location.href = '<?php echo get_post_type_archive_link('inmueble'); ?>';
        });
        </script>
    </form>
    <?php
    // Usar la función centralizada de renderizado splitview
    require_once WP_CONTENT_DIR . '/plugins/tubihome-core/includes/render-inmuebles-splitview.php';
    echo tubihome_render_inmuebles_splitview($wp_query, [
        'title' => 'Catálogo de Inmuebles',
        'term' => isset($_GET['tipo-propiedad']) ? sanitize_text_field($_GET['tipo-propiedad']) : '',
        'operacion' => isset($_GET['tipo-operacion']) ? sanitize_text_field($_GET['tipo-operacion']) : '',
        'show_filters' => false
    ]);
    ?>
</main>
<?php get_footer(); ?>
