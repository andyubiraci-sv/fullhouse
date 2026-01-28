<?php
/*
Template Name: Cambios Guardados Inmueble
*/
get_header();
echo '<link rel="stylesheet" href="'.get_template_directory_uri().'/css/wizard.css?v=1.0.0" type="text/css" media="all">';
$post_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$post = get_post($post_id);
if(!$post || $post->post_type!='inmueble') {
    echo '<div class="edit-success-error">No se encontró el inmueble.</div>';
    get_footer();
    exit;
}
$titulo = $post->post_title;
?>
<div class="edit-success-container">
    <h1>Cambios guardados</h1>
    <p>El inmueble <b><?php echo esc_html($titulo); ?></b> será revisado nuevamente por el equipo antes de publicarse.</p>
    <div class="edit-success-actions">
        <a href="/panel-control/" class="btn">Ver todas mis publicaciones</a>
    </div>
</div>
<?php get_footer(); ?>
