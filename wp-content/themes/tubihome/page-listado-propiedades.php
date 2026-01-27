<?php
/*
Template Name: Listado de Propiedades
Description: Página especial para mostrar el shortcode de reporte de inmuebles con scroll infinito y filtros.
*/
get_header();
?>
<main class="container" style="min-height:60vh;">
    <?php
    while (have_posts()) : the_post();
        echo '<h1 style="margin:32px 0 24px 0; color:#145a37; font-size:2.2rem; font-weight:700; text-align:center;">' . esc_html(get_the_title()) . '</h1>';
        the_content(); // Aquí puedes insertar el shortcode desde el editor
    endwhile;
    ?>
</main>
<?php get_footer(); ?>
