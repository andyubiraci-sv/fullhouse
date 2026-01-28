<?php
/*
Template Name: Gracias Publicación
*/
get_header();
echo '<link rel="stylesheet" href="'.get_template_directory_uri().'/css/wizard.css?v=1.0.0" type="text/css" media="all">';
$post_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$post = get_post($post_id);
if(!$post || $post->post_type!='inmueble') {
    echo '<div class="gracias-error">No se encontró la publicación.</div>';
    get_footer();
    exit;
}
$titulo = $post->post_title;
?>
<div class="gracias-container">
    <h1>¡Recibido! Tu inmueble está en camino</h1>
    <p>Gracias por confiar en nosotros. Nuestro equipo revisará la información en un plazo máximo de <b>24 horas</b>.</p>
    <p><b>ID de seguimiento:</b> <?php echo $post_id; ?><br>
    <b>Título:</b> <?php echo esc_html($titulo); ?></p>
    <div class="gracias-actions">
        <a href="/publicar-inmueble/" class="btn">Publicar otro inmueble</a>
        <a href="/panel-control/" class="btn">Ir a mis propiedades</a>
        <a href="https://wa.me/503XXXXXXXX" class="btn" target="_blank">¿Tienes dudas? Contáctanos</a>
    </div>
</div>
<?php get_footer(); ?>
