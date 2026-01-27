<?php get_header(); ?>
<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
<?php
$tipo = wp_get_object_terms(get_the_ID(), 'tipo-propiedad', ['fields'=>'slugs']);
$schemaType = 'Residence';
if (in_array('casas', $tipo)) $schemaType = 'House';
if (in_array('apartamentos', $tipo)) $schemaType = 'Apartment';
?>
<div class="property-container" itemscope itemtype="https://schema.org/<?php echo $schemaType; ?>">
    <main class="details-section">
         <header class="property-header">
            <div>
                <h1 itemprop="name"><?php the_title(); ?></h1>

                <?php $estado = get_post_meta(get_the_ID(), '_status', true); if ($estado): ?>
                    <span class="status-pill"><?php echo esc_html(ucfirst($estado)); ?></span>
                <?php endif; ?>
                <?php
                $precio = get_post_meta(get_the_ID(), '_price', true);
                $area = get_post_meta(get_the_ID(), '_area_total', true);
                $precio_m2 = ($precio && $area && $area > 0) ? $precio / $area : false;
                ?>

                                <div class="header-price">
    <?php $moneda = get_post_meta(get_the_ID(), '_currency', true); ?>
    <div class="main-price" itemprop="price" content="<?php echo esc_attr($precio); ?>">
        <?php echo '$' . number_format((float)$precio, 0, '.', ',') . ($moneda ? ' ' . $moneda : ''); ?>
        <meta itemprop="priceCurrency" content="<?php echo esc_attr($moneda ? $moneda : 'USD'); ?>" />
    </div>
    <div class="price-m2"><?php echo ($precio_m2 ? ('$' . number_format((float)$precio_m2, 0, '.', ',') . ($moneda ? ' ' . $moneda : '') . ' / m²') : ''); ?></div>
</div>
            </div>
        </header>
         <section class="features-row">
    <?php 
    // 1. Extraer los metadatos existentes
    $m2 = get_post_meta(get_the_ID(), '_area_total', true);
    $recamaras = get_post_meta(get_the_ID(), '_rooms', true);
    $banos = get_post_meta(get_the_ID(), '_baths', true);
    $garajes = get_post_meta(get_the_ID(), '_parking', true);
    $direccion = get_post_meta(get_the_ID(), '_address', true);
    $lat = get_post_meta(get_the_ID(), '_geo_lat', true);
    $lng = get_post_meta(get_the_ID(), '_geo_lng', true);
    // 2. Lógica para el precio por m2
    $precio = get_post_meta(get_the_ID(), '_price', true);
    $precio_m2 = 0;
    if ( !empty($precio) && !empty($m2) && $m2 > 0 ) {
        $precio_m2 = $precio / $m2;
    }
    ?>

    <?php if ($precio_m2 > 0) : ?>
    <div class="feature-item feature-item--highlight">
        <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="#145a37" stroke-width="2"><path d="M9 7h6m-6 4h6m-6 4h6M5 19h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
        <div>
            <strong>$<?php echo number_format($precio_m2, 0, '.', ','); ?></strong>
            <span>m² construido</span>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($m2) : ?>
    <div class="feature-item">
        <svg width="20" height="20" fill="none" viewBox="0 0 24 24"><rect x="3" y="7" width="18" height="10" rx="3" stroke="#145a37" stroke-width="2"></rect><path d="M7 17v2M17 17v2" stroke="#145a37" stroke-width="2" stroke-linecap="round"></path></svg>
        <div><strong itemprop="floorSize" content="<?php echo esc_attr($m2); ?>"><?php echo esc_html($m2); ?> m²</strong><span>Totales</span></div>
    </div>
    <?php endif; ?>

    <?php if ($recamaras) : ?>
    <div class="feature-item">
        <svg width="20" height="20" fill="none" viewBox="0 0 24 24"><rect x="2" y="10" width="20" height="8" rx="3" stroke="#145a37" stroke-width="2"></rect><rect x="6" y="6" width="4" height="4" rx="2" stroke="#145a37" stroke-width="2"></rect><rect x="14" y="6" width="4" height="4" rx="2" stroke="#145a37" stroke-width="2"></rect></svg>
        <div><strong itemprop="numberOfRooms" content="<?php echo esc_attr($recamaras); ?>"><?php echo esc_html($recamaras); ?></strong><span>Recámaras</span></div>
    </div>
    <?php endif; ?>

    <?php if ($banos) : ?>
    <div class="feature-item">
        <svg width="20" height="20" fill="none" viewBox="0 0 24 24"><rect x="4" y="10" width="16" height="8" rx="4" stroke="#145a37" stroke-width="2"></rect><path d="M8 10V7a4 4 0 1 1 8 0v3" stroke="#145a37" stroke-width="2"></path></svg>
        <div><strong itemprop="numberOfBathroomsTotal" content="<?php echo esc_attr($banos); ?>"><?php echo esc_html($banos); ?></strong><span>Baños</span></div>
    </div>
    <?php endif; ?>

    <?php if ($garajes) : ?>
    <div class="feature-item">
        <svg width="20" height="20" fill="none" viewBox="0 0 24 24"><rect x="2" y="12" width="20" height="6" rx="3" stroke="#145a37" stroke-width="2"></rect><circle cx="7" cy="18" r="2" stroke="#145a37" stroke-width="2"></circle><circle cx="17" cy="18" r="2" stroke="#145a37" stroke-width="2"></circle></svg>
        <div><strong><?php echo esc_html($garajes); ?></strong><span>Garajes</span></div>
    </div>
    <?php endif; ?>
</section>
        <section class="description-text">
            <p><?php the_content(); ?></p>
        </section>
        <section class="gallery-container">
            <?php
            $gallery_ids = get_post_meta(get_the_ID(), '_galeria_inmueble', true);
            if (!$gallery_ids) {
                $gallery_ids = get_post_meta(get_the_ID(), '_gallery', true);
            }
            $gallery_ids = $gallery_ids ? array_filter(explode(',', $gallery_ids)) : [];
            $main_img_id = get_post_thumbnail_id();
            $side_imgs = array_values(array_filter($gallery_ids, function($id) use ($main_img_id) { return $id && $id != $main_img_id; }));
            $max_thumbs = 4;
            $total_imgs = count($side_imgs);
            // Microdata para imágenes
            $all_imgs = $main_img_id ? array_merge([$main_img_id], $side_imgs) : $side_imgs;
            foreach ($all_imgs as $img_id) {
                $img_url = wp_get_attachment_url($img_id);
                if ($img_url) {
                    echo '<meta itemprop="image" content="' . esc_url($img_url) . '" />';
                }
            }
            ?>
            <!-- Galería ESCRITORIO -->
            <div class="gallery-desktop">
                <div class="main-photo">
                    <?php if (has_post_thumbnail()) {
                        the_post_thumbnail('inmueble-large', ['alt' => 'Vista principal']);
                    } ?>
                </div>
                <div class="thumbnails">
                    <?php
                    foreach (array_slice($side_imgs, 0, $max_thumbs) as $i => $img_id) {
                        $is_last = ($i === $max_thumbs - 1 && $total_imgs > $max_thumbs);
                        $img_html = wp_get_attachment_image($img_id, 'propiedad-mini', false, [
                            'alt' => 'Interior ' . ($i+1),
                            'loading' => 'lazy',
                            'style' => 'aspect-ratio:1/1; width:100%; border-radius:10px; object-fit:cover;'
                        ]);
                        if ($is_last) {
                            $rest = $total_imgs - $max_thumbs;
                            echo '<div class="more-photos">' . $img_html . '<div class="overlay">+' . $rest . ' fotos</div></div>';
                        } else {
                            echo $img_html;
                        }
                    }
                    ?>
                </div>
            </div>
            <!-- Galería MÓVIL -->
            <div class="gallery-carousel">
                <?php
                $max_mobile = 3;
                // Mostrar la foto principal
                if (has_post_thumbnail()) {
                    echo '<div class="carousel-slide"><img src="' . esc_url(wp_get_attachment_url($main_img_id)) . '" alt="Vista principal" loading="eager" /></div>';
                }
                // Mostrar hasta 2 secundarias
                foreach (array_slice($side_imgs, 0, $max_mobile - 1) as $i => $img_id) {
                    $img_url = esc_url(wp_get_attachment_url($img_id));
                    echo '<div class="carousel-slide"><img src="' . $img_url . '" alt="Interior ' . ($i+1) . '" loading="lazy" /></div>';
                }
                // Última miniatura con overlay '+N fotos'
                if ($total_imgs > ($max_mobile - 1)) {
                    $last_img_id = $side_imgs[$max_mobile - 1];
                    $img_url = esc_url(wp_get_attachment_url($last_img_id));
                    $rest = $total_imgs - ($max_mobile - 1);
                    echo '<div class="carousel-slide more-photos"><img src="' . $img_url . '" alt="Más fotos" loading="lazy" /><div class="overlay">+' . $rest . ' fotos</div></div>';
                }
                ?>
                <div class="carousel-counter"></div>
            </div>
        </section>
       
       
    </main>
    <aside class="sidebar-section">
        <div class="sidebar-box">
            <h3>Ubicación</h3>
            <div id="property-map" class="map-placeholder"></div>
            <?php if ($direccion): ?>
            <div class="property-address" itemprop="address" itemscope itemtype="https://schema.org/PostalAddress" style="margin:12px 0 0 0; color:#145a37; font-weight:600; font-size:1.05rem;">
                <svg width="18" height="18" style="vertical-align:middle;margin-right:6px;" fill="none" viewBox="0 0 24 24"><path d="M12 21s-6-5.686-6-10A6 6 0 0 1 18 11c0 4.314-6 10-6 10Z" stroke="#145a37" stroke-width="2"/><circle cx="12" cy="11" r="2.5" stroke="#145a37" stroke-width="2"/></svg>
                <span itemprop="streetAddress"><?php echo nl2br(esc_html(trim($direccion))); ?></span>
            </div>
            <?php endif; ?>
            <?php if ($lat && $lng): ?>
            <meta itemprop="geo" itemscope itemtype="https://schema.org/GeoCoordinates" />
            <meta itemprop="latitude" content="<?php echo esc_attr($lat); ?>" />
            <meta itemprop="longitude" content="<?php echo esc_attr($lng); ?>" />
            <?php endif; ?>
            <?php
                $latitud = get_post_meta(get_the_ID(), '_latitud', true);
                $longitud = get_post_meta(get_the_ID(), '_longitud', true);
                $latitud = $latitud ? floatval($latitud) : 19.4326;
                $longitud = $longitud ? floatval($longitud) : -99.1332;
            ?>
            <div class="agent-card">
                <div class="agent-photo"></div>
                <div class="agent-info">
                    <strong>Nombre Agente</strong>
                    <p>Agente Certificado</p>
                </div>
            </div>
        </div>
    </aside>
</div>
<div class="sticky-bar">
    <div class="sticky-content">
        <div class="sticky-price">
            <?php if (has_post_thumbnail()) {
                the_post_thumbnail('thumbnail', ['alt' => 'Mini']);
            } ?>
            <div class="price-text">
                <?php 
                $precio = get_post_meta(get_the_ID(), '_price', true); 
                $moneda = get_post_meta(get_the_ID(), '_currency', true);
                $precio_str = $precio ? number_format($precio, 0, '.', ',') : false;
                echo $precio_str !== false ? esc_html($precio_str . ($moneda ? ' ' . $moneda : '')) : '<span style="color:#bbb;">Precio no disponible</span>'; ?>
                <?php
                $area = get_post_meta(get_the_ID(), '_area_total', true);
                $precio_m2 = ($precio && $area && $area > 0) ? $precio / $area : false;
                ?>
                <?php if ($precio_m2): ?>
                    <div class="precio-m2" style="font-size:0.9rem;color:var(--text-muted,#888);margin-top:2px;">
                        <?php echo '$' . number_format($precio_m2, 0, '.', ',') . ($moneda ? ' ' . $moneda : '') . ' / m²'; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <a href="#" class="btn-agendar">Agendar Visita</a>
    </div>
</div>
<!-- Lightbox Modal para galería -->
<div id="galleryLightbox" class="gallery-lightbox" style="display:none;position:fixed;z-index:9999;top:0;left:0;width:100vw;height:100vh;background:rgba(20,20,20,0.92);align-items:center;justify-content:center;">
    <button id="lightboxClose" style="position:absolute;top:32px;right:32px;font-size:2.2rem;color:#fff;background:none;border:none;cursor:pointer;z-index:10001;">&times;</button>
    <button id="lightboxPrev" style="position:absolute;left:32px;top:50%;transform:translateY(-50%);font-size:2.2rem;color:#fff;background:none;border:none;cursor:pointer;z-index:10001;">&#60;</button>
    <img id="lightboxImg" src="" alt="" style="max-width:90vw;max-height:80vh;border-radius:18px;box-shadow:0 8px 48px 0 rgba(0,0,0,0.18);">
    <button id="lightboxNext" style="position:absolute;right:32px;top:50%;transform:translateY(-50%);font-size:2.2rem;color:#fff;background:none;border:none;cursor:pointer;z-index:10001;">&#62;</button>
</div>
<script>
// Carrusel móvil: contador flotante, pinch-to-zoom y abrir lightbox desde '+N fotos'
document.addEventListener('DOMContentLoaded', function() {
    const carousel = document.querySelector('.gallery-carousel');
    if (!carousel) return;
    const slides = Array.from(carousel.querySelectorAll('.carousel-slide'));
    const counter = carousel.querySelector('.carousel-counter');
    let current = 0;

    // Actualiza el contador flotante
    function updateCounter(idx) {
        counter.textContent = (idx + 1) + ' / ' + slides.length;
    }

    // Detecta el slide centrado usando scroll
    function getCurrentSlide() {
        let minDist = Infinity;
        let idx = 0;
        const carouselRect = carousel.getBoundingClientRect();
        slides.forEach((slide, i) => {
            const rect = slide.getBoundingClientRect();
            const dist = Math.abs(rect.left + rect.width/2 - (carouselRect.left + carouselRect.width/2));
            if (dist < minDist) {
                minDist = dist;
                idx = i;
            }
        });
        return idx;
    }

    carousel.addEventListener('scroll', function() {
        current = getCurrentSlide();
        updateCounter(current);
    });
    // Inicializa contador
    updateCounter(0);

    // Pinch-to-zoom en imágenes
    slides.forEach(slide => {
        const img = slide.querySelector('img');
        let scale = 1, startDist = 0;
        img.addEventListener('touchstart', function(e) {
            if (e.touches.length === 2) {
                startDist = Math.hypot(
                    e.touches[0].clientX - e.touches[1].clientX,
                    e.touches[0].clientY - e.touches[1].clientY
                );
            }
        });
        img.addEventListener('touchmove', function(e) {
            if (e.touches.length === 2) {
                const newDist = Math.hypot(
                    e.touches[0].clientX - e.touches[1].clientX,
                    e.touches[0].clientY - e.touches[1].clientY
                );
                scale = Math.min(3, Math.max(1, newDist / startDist));
                img.style.transform = 'scale(' + scale + ')';
                e.preventDefault();
            }
        }, {passive: false});
        img.addEventListener('touchend', function(e) {
            scale = 1;
            img.style.transform = '';
        });
    });

    // Abrir lightbox al tocar '+N fotos'
    const morePhotos = carousel.querySelector('.more-photos');
    const lightbox = document.getElementById('galleryLightbox');
    const lightboxImg = document.getElementById('lightboxImg');
    if (morePhotos && lightbox && lightboxImg) {
        morePhotos.addEventListener('click', function(e) {
            // Buscar la imagen de '+N fotos' y mostrarla en el lightbox
            const img = morePhotos.querySelector('img');
            if (img && img.src) {
                lightboxImg.src = img.src;
                lightboxImg.alt = img.alt || '';
            }
            lightbox.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            e.stopPropagation();
        });

        // Swipe down para cerrar el lightbox en móvil SOLO si existe
        let startY = null;
        let isSwiping = false;
        lightbox.addEventListener('touchstart', function(e) {
            if (e.touches.length === 1) {
                startY = e.touches[0].clientY;
                isSwiping = true;
            }
        });
        lightbox.addEventListener('touchmove', function(e) {
            if (!isSwiping || e.touches.length !== 1) return;
            const deltaY = e.touches[0].clientY - startY;
            if (deltaY > 80) { // umbral para swipe
                lightbox.style.display = 'none';
                document.body.style.overflow = '';
                isSwiping = false;
            }
        });
        lightbox.addEventListener('touchend', function() {
            isSwiping = false;
        });
    }
});
document.addEventListener('DOMContentLoaded', function() {
    const mainPhoto = document.querySelector('.main-photo img');
    const thumbs = document.querySelectorAll('.thumbnails img');
    const images = [mainPhoto, ...thumbs].filter(Boolean);
    const lightbox = document.getElementById('galleryLightbox');
    const lightboxImg = document.getElementById('lightboxImg');
    const btnClose = document.getElementById('lightboxClose');
    const btnPrev = document.getElementById('lightboxPrev');
    const btnNext = document.getElementById('lightboxNext');
    let current = 0;

    function openLightbox(idx) {
        if (!images[idx] || !lightboxImg || !lightbox) return;
        lightboxImg.src = images[idx].src;
        lightboxImg.alt = images[idx].alt;
        lightbox.style.display = 'flex';
        current = idx;
        document.body.style.overflow = 'hidden';
    }
    function closeLightbox() {
        if (!lightbox) return;
        lightbox.style.display = 'none';
        document.body.style.overflow = '';
    }
    function showPrev() {
        current = (current - 1 + images.length) % images.length;
        openLightbox(current);
    }
    function showNext() {
        current = (current + 1) % images.length;
        openLightbox(current);
    }

    if (mainPhoto) {
        mainPhoto.addEventListener('click', function() { openLightbox(0); });
    }
    if (thumbs.length) {
        thumbs.forEach(function(thumb, i) {
            thumb.addEventListener('click', function(e) {
                if (mainPhoto && mainPhoto.src !== thumb.src) {
                    mainPhoto.src = thumb.src;
                    mainPhoto.alt = thumb.alt;
                }
                openLightbox(i + 1);
                e.stopPropagation();
            });
        });
    }
    const morePhotos = document.querySelector('.more-photos');
    if (morePhotos) {
        morePhotos.addEventListener('click', function(e) {
            openLightbox(images.length - 1);
            e.stopPropagation();
        });
    }
    if (btnClose) btnClose.addEventListener('click', closeLightbox);
    if (btnPrev) btnPrev.addEventListener('click', showPrev);
    if (btnNext) btnNext.addEventListener('click', showNext);
    if (lightbox) {
        lightbox.addEventListener('click', function(e) {
            if (e.target === lightbox) closeLightbox();
        });
    }
    document.addEventListener('keydown', function(e) {
        if (lightbox && lightbox.style.display === 'flex') {
            if (e.key === 'Escape') closeLightbox();
            if (e.key === 'ArrowLeft') showPrev();
            if (e.key === 'ArrowRight') showNext();
        }
    });
});
</script>
<?php endwhile; endif; ?>
<script>
// Inicialización Leaflet después de cargar leaflet.js
document.addEventListener('DOMContentLoaded', function() {
    if (typeof L === 'undefined') return;
    var mapDiv = document.getElementById('property-map');
    if (mapDiv) {
        mapDiv.style.height = '320px';
        mapDiv.style.borderRadius = '16px';
        // Destruir instancia previa si existe
        if (window.propertyMap && window.propertyMap.remove) {
            window.propertyMap.remove();
            window.propertyMap = null;
        }
        var lat = <?php echo json_encode($latitud); ?>;
        var lng = <?php echo json_encode($longitud); ?>;
        window.propertyMap = L.map(mapDiv).setView([lat, lng], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap',
            maxZoom: 19
        }).addTo(window.propertyMap);
        L.marker([lat, lng]).addTo(window.propertyMap)
            .bindPopup('Ubicación del inmueble')
            .openPopup();
        // Forzar recalculo de tamaño tras render
        setTimeout(function() {
            window.propertyMap.invalidateSize();
        }, 400);
    }
});
</script>
<?php get_footer(); ?>
