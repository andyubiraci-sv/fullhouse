<?php
get_header();
$term = get_queried_object();
?>
<div class="container">
    <h1 class="tipo-title" style="margin:32px 0 24px 0; color:#145a37; font-size:2.2rem; font-weight:700; text-align:center;">
        <?php echo esc_html($term->name); ?>
    </h1>
    <div id="inmuebles-grilla" class="inmuebles-grilla" style="display:grid;grid-template-columns:repeat(3,1fr);gap:32px 24px;">
        <!-- Cards AJAX aquí -->
    </div>
    <div id="infinite-loader" style="text-align:center;margin:32px 0;display:none;">
        <span>Cargando más propiedades...</span>
    </div>
</div>
<script>
(function(){
    let page = 1;
    let loading = false;
    let finished = false;
    const grid = document.getElementById('inmuebles-grilla');
    const loader = document.getElementById('infinite-loader');
    const term = <?php echo json_encode($term->slug); ?>;
    async function loadMore() {
        if (loading || finished) return;
        loading = true;
        loader.style.display = 'block';
        const res = await fetch('<?php echo get_template_directory_uri(); ?>/ajax/ajax-inmuebles-tipo.php?tipo=' + encodeURIComponent(term) + '&page=' + page);
        const html = await res.text();
        if (html.trim() === '' || html.trim() === 'END') {
            finished = true;
            loader.style.display = 'none';
            return;
        }
        const temp = document.createElement('div');
        temp.innerHTML = html;
        Array.from(temp.children).forEach(card => grid.appendChild(card));
        page++;
        loading = false;
        loader.style.display = 'none';
    }
    function onScroll() {
        if (finished) return;
        const scrollY = window.scrollY || window.pageYOffset;
        const viewport = window.innerHeight;
        const full = document.body.offsetHeight;
        if (scrollY + viewport > full - 400) {
            loadMore();
        }
    }
    window.addEventListener('scroll', onScroll);
    loadMore();
})();
</script>
<?php get_footer(); ?>
