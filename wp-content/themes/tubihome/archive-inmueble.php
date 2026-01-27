<?php get_header(); ?>
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
    <?php if (have_posts()) : ?>
        <div class="inmuebles-listado" itemscope itemtype="https://schema.org/CollectionPage">
            <?php while (have_posts()) : the_post(); ?>
                <article <?php post_class('inmueble'); ?> itemscope itemtype="https://schema.org/Residence">
                    <a class="inmueble__link" href="<?php the_permalink(); ?>" itemprop="url">
                        <?php if (has_post_thumbnail()) {
                            the_post_thumbnail('inmueble-thumb', ['class' => 'inmueble__img']);
                        } ?>
                        <h2 class="inmueble__title" itemprop="name"><?php the_title(); ?></h2>
                        <div class="inmueble__meta inmueble__precio" itemprop="price" content="<?php echo esc_attr(get_post_meta(get_the_ID(), '_price', true)); ?>">
                            <?php 
                            $precio = get_post_meta(get_the_ID(), '_price', true);
                            $moneda = get_post_meta(get_the_ID(), '_currency', true);
                            echo esc_html($precio ? number_format($precio, 0, '.', ',') : '');
                            echo $moneda ? ' ' . esc_html($moneda) : '';
                            ?>
                            <meta itemprop="priceCurrency" content="<?php echo esc_attr($moneda ? $moneda : 'USD'); ?>" />
                        </div>
                        <div class="inmueble__meta">
                            <?php 
                            $area = get_post_meta(get_the_ID(), '_area_total', true);
                            if ($area) {
                                echo '<span itemprop="floorSize" content="' . esc_attr($area) . '">' . esc_html($area) . ' m²</span>';
                            }
                            ?>
                        </div>
                        <div class="inmueble__meta">
                            <?php 
                            $rec = get_post_meta(get_the_ID(), '_rooms', true);
                            $banos = get_post_meta(get_the_ID(), '_baths', true);
                            if ($rec) echo '<span itemprop="numberOfRooms" content="' . esc_attr($rec) . '">' . esc_html($rec) . ' recámaras</span>';
                            if ($rec && $banos) echo ', ';
                            if ($banos) echo '<span itemprop="numberOfBathroomsTotal" content="' . esc_attr($banos) . '">' . esc_html($banos) . ' baños</span>';
                            ?>
                        </div>
                        <div class="inmueble__meta">
                            <?php 
                            $ciudad = get_the_terms(get_the_ID(), 'ciudad');
                            $colonia = get_the_terms(get_the_ID(), 'colonia');
                            if ($colonia && !is_wp_error($colonia)) {
                                echo esc_html($colonia[0]->name);
                                echo $ciudad && !is_wp_error($ciudad) ? ', ' : '';
                            }
                            if ($ciudad && !is_wp_error($ciudad)) {
                                echo esc_html($ciudad[0]->name);
                            }
                            ?>
                        </div>
                        <div class="inmueble__meta">
                            <?php 
                            $amenidades = get_the_terms(get_the_ID(), 'amenidades');
                            if ($amenidades && !is_wp_error($amenidades)) {
                                echo '<span>Amenidades: </span>';
                                $nombres = array_map(function($a){ return esc_html($a->name); }, $amenidades);
                                echo implode(', ', $nombres);
                            }
                            ?>
                        </div>
                    </a>
                </article>
            <?php endwhile; ?>
        </div>
        <?php the_posts_pagination(); ?>
    <?php else : ?>
        <p>No hay inmuebles disponibles con los filtros seleccionados.</p>
        <?php
        // Fallback: sugerir inmuebles similares relajando el filtro de amenidades
        $fallback_args = [
            'post_type' => 'inmueble',
            'posts_per_page' => 6,
            'orderby' => 'date',
            'order' => 'DESC',
        ];
        // Mantener filtros de municipio, distrito, tipo-operacion, tipo-propiedad, pero quitar amenidades
        $meta_query = [];
        if (!empty($_GET['min_price'])) {
            $meta_query[] = ['key' => '_price', 'value' => floatval($_GET['min_price']), 'type' => 'NUMERIC', 'compare' => '>='];
        }
        if (!empty($_GET['max_price'])) {
            $meta_query[] = ['key' => '_price', 'value' => floatval($_GET['max_price']), 'type' => 'NUMERIC', 'compare' => '<='];
        }
        if (!empty($_GET['habitaciones'])) {
            $meta_query[] = ['key' => '_rooms', 'value' => intval($_GET['habitaciones']), 'type' => 'NUMERIC', 'compare' => '>='];
        }
        if (!empty($_GET['distrito'])) {
            $meta_query[] = ['key' => 'distrito', 'value' => sanitize_text_field($_GET['distrito']), 'compare' => '='];
        }
        if (!empty($_GET['municipio'])) {
            $meta_query[] = ['key' => 'municipio', 'value' => sanitize_text_field($_GET['municipio']), 'compare' => '='];
        }
        if (!empty($meta_query)) $fallback_args['meta_query'] = $meta_query;
        $tax_query = [];
        if (!empty($_GET['tipo-operacion'])) {
            $tax_query[] = [
                'taxonomy' => 'tipo-operacion',
                'field'    => 'slug',
                'terms'    => sanitize_text_field($_GET['tipo-operacion']),
            ];
        }
        if (!empty($_GET['tipo-propiedad'])) {
            $tax_query[] = [
                'taxonomy' => 'tipo-propiedad',
                'field'    => 'slug',
                'terms'    => sanitize_text_field($_GET['tipo-propiedad']),
            ];
        }
        if (!empty($tax_query)) $fallback_args['tax_query'] = $tax_query;
        $fallback = new WP_Query($fallback_args);
        if ($fallback->have_posts()) : ?>
            <div class="inmuebles-listado" style="margin-top:32px;">
                <h3>Otras propiedades similares en la zona:</h3>
                <?php while ($fallback->have_posts()) : $fallback->the_post(); ?>
                    <article <?php post_class('inmueble'); ?>>
                        <a class="inmueble__link" href="<?php the_permalink(); ?>">
                            <?php if (has_post_thumbnail()) {
                                the_post_thumbnail('inmueble-thumb', ['class' => 'inmueble__img']);
                            } ?>
                            <h2 class="inmueble__title"><?php the_title(); ?></h2>
                            <div class="inmueble__meta inmueble__precio">
                                <?php 
                                $precio = get_post_meta(get_the_ID(), '_price', true);
                                $moneda = get_post_meta(get_the_ID(), '_currency', true);
                                echo esc_html($precio ? number_format($precio, 0, '.', ',') : '');
                                echo $moneda ? ' ' . esc_html($moneda) : '';
                                ?>
                            </div>
                            <div class="inmueble__meta">
                                <?php 
                                $area = get_post_meta(get_the_ID(), '_area_total', true);
                                echo $area ? esc_html($area) . ' m²' : '';
                                ?>
                            </div>
                            <div class="inmueble__meta">
                                <?php 
                                $rec = get_post_meta(get_the_ID(), '_rooms', true);
                                $banos = get_post_meta(get_the_ID(), '_baths', true);
                                echo $rec ? esc_html($rec) . ' recámaras' : '';
                                echo ($rec && $banos) ? ', ' : '';
                                echo $banos ? esc_html($banos) . ' baños' : '';
                                ?>
                            </div>
                        </a>
                    </article>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</main>
<?php get_footer(); ?>
