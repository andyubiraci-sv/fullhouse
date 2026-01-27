<?php
/**
 * Plantilla para el Catálogo de Inmuebles (Split-View)
 */

get_header(); ?>

<style>
    /* Estructura Principal tipo Airbnb */
    .main-content-split {
        display: flex;
        height: calc(100vh - 80px); /* Ajusta según tu header */
        overflow: hidden;
    }

    .results-column {
        flex: 0 0 55%;
        overflow-y: auto;
        padding: 20px;
        background: #fff;
    }

    .map-section {
        flex: 0 0 45%;
        position: relative;
        background: #eee;
    }

    #property-map {
        width: 100%;
        height: 100%;
        position: sticky;
        top: 0;
    }

    /* Grilla de tarjetas */
    .inmuebles-grilla {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 20px;
    }

    .splitview-card {
        border: 1px solid #ddd;
        border-radius: 12px;
        overflow: hidden;
        transition: 0.3s;
    }

    .splitview-card:hover { box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
    
    .card-info { padding: 15px; }
    
    .price { color: #145a37; font-weight: bold; font-size: 1.2rem; }

    @media (max-width: 768px) {
        .main-content-split { flex-direction: column; height: auto; }
        .results-column, .map-section { flex: 0 0 100%; height: 50vh; }
    }
</style>

<div class="main-content-split">
    <div class="results-column">
        <h1 class="tipo-title">Catálogo de Inmuebles</h1>
        
        <div class="inmuebles-grilla" id="inmuebles-grilla">
            <?php
            // CONSULTA LIMPIA: Solo publicados, sin borradores
            $args = array(
                'post_type'      => 'inmueble',
                'post_status'    => 'publish',
                'posts_per_page' => 12,
            );
            $query = new WP_Query($args);

            if ($query->have_posts()) :
                while ($query->have_posts()) : $query->the_post();
                    // Obtener coordenadas de los campos meta
                    $lat = get_post_meta(get_the_ID(), '_at_lat', true);
                    $lng = get_post_meta(get_the_ID(), '_at_lng', true);
                    $precio = get_post_meta(get_the_ID(), '_at_precio', true);
                    ?>
                    
                    <article class="splitview-card" 
                             data-lat="<?php echo esc_attr($lat); ?>" 
                             data-lng="<?php echo esc_attr($lng); ?>" 
                             data-price="<?php echo esc_attr($precio); ?>">
                        
                        <?php if (has_post_thumbnail()) : ?>
                            <div class="card-img"><?php the_post_thumbnail('medium'); ?></div>
                        <?php endif; ?>

                        <div class="card-info">
                            <h3><?php the_title(); ?></h3>
                            <p class="price">$
                                <?php
                                if (is_numeric($precio) && $precio !== '') {
                                    echo number_format((float)$precio);
                                } else {
                                    echo 'N/A';
                                }
                                ?>
                            </p>
                            <a href="<?php the_permalink(); ?>" class="btn-view">Ver Detalles</a>
                        </div>
                    </article>

                <?php endwhile;
                wp_reset_postdata();
            else :
                echo '<p>No se encontraron inmuebles disponibles.</p>';
            endif;
            ?>
        </div>
    </div>

    <section class="map-section">
        <div id="property-map"></div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar mapa centrado en El Salvador por defecto
    var map = L.map('property-map').setView([13.6929, -89.2182], 12);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    var bounds = L.latLngBounds();
    var hasMarkers = false;

    // Leer las tarjetas para poner los pines
    document.querySelectorAll('.splitview-card').forEach(function(card) {
        var lat = parseFloat(card.dataset.lat);
        var lng = parseFloat(card.dataset.lng);
        var title = card.querySelector('h3').innerText;

        if (lat && lng) {
            var marker = L.marker([lat, lng]).addTo(map);
            marker.bindPopup('<b>' + title + '</b><br><a href="'+card.querySelector('a').href+'">Ver más</a>');
            bounds.extend([lat, lng]);
            hasMarkers = true;
        }
    });

    // Ajustar mapa si hay inmuebles, si no, usar el setTimeout para corregir tamaño
    if (hasMarkers) {
        map.fitBounds(bounds, { padding: [50, 50] });
    }

    setTimeout(function() {
        map.invalidateSize();
    }, 600);
});
</script>

<?php get_footer(); ?><?php get_header(); ?>
<main>
    <h1>Catálogo de Inmuebles</h1>
    <form class="inmuebles-filtros" role="search" method="get" action="<?php echo esc_url( get_post_type_archive_link('inmueble') ); ?>">
        <label>
            Operación:
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
        </label>
        <div style="display:flex; gap:10px; align-items:center; margin:10px 0;">
            <button type="submit" style="padding:6px 18px;">Buscar</button>
            <button type="button" id="reset-filtros" style="padding:6px 18px; background:#eee; color:#145a37; border:1px solid #145a37;">Limpiar filtros</button>
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
        <label>
            Tipo de Propiedad:
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
        </label>
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
            Municipio / Zona:
            <select id="filtro-municipio" name="municipio">
                <option value="">Todos</option>
                <?php
                foreach (array_keys($municipios) as $mun) {
                    $selected = (isset($_GET['municipio']) && $_GET['municipio'] === $mun) ? 'selected' : '';
                    echo '<option value="' . esc_attr($mun) . '" ' . $selected . '>' . esc_html($mun) . '</option>';
                }
                ?>
            </select>
        </label>
        <label>
            Departamento / Distrito:
            <select id="filtro-distrito" name="distrito">
                <option value="">Seleccione municipio primero</option>
                <?php
                if (isset($_GET['municipio']) && $_GET['municipio'] && isset($municipios[$_GET['municipio']])) {
                    foreach ($municipios[$_GET['municipio']] as $dis) {
                        $selected = (isset($_GET['distrito']) && $_GET['distrito'] === $dis) ? 'selected' : '';
                        echo '<option value="' . esc_attr($dis) . '" ' . $selected . '>' . esc_html($dis) . '</option>';
                    }
                }
                ?>
            </select>
        </label>
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
        <button type="submit">Buscar</button>
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
