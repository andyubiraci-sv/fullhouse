<?php
/*
Template Name: Dashboard Usuario
*/
get_header();
if (!is_user_logged_in()) {
    wp_redirect(site_url('/acceso/'));
    exit;
}
$user_id = get_current_user_id();
// Eliminar inmueble si se solicita y el usuario es el autor
if (isset($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    $post = get_post($del_id);
    if ($post && $post->post_type === 'inmueble' && $post->post_author == $user_id) {
        wp_trash_post($del_id);
        // Redirigir para evitar re-envío
        wp_redirect(remove_query_arg('delete'));
        exit;
    }
}
echo '<link rel="stylesheet" href="'.get_template_directory_uri().'/css/wizard.css?v=1.0.0" type="text/css" media="all">';
$args = array(
    'post_type' => 'inmueble',
    'author' => $user_id,
    'post_status' => array('publish','pending','draft'), // Excluir 'trash'
    'posts_per_page' => -1
);
$inmuebles = get_posts($args);
?>
<div class="dashboard-container">
    <h1>Mi Panel</h1>
    <a href="/publicar-inmueble/" class="btn btn-primary">Publicar Inmueble</a>
    <div class="dashboard-list">
        <?php if($inmuebles): foreach($inmuebles as $post): setup_postdata($post); ?>
            <?php
            $estado = get_post_status($post);
            $badge = $estado=='publish' ? 'Publicado' : ($estado=='pending' ? 'En Revisión' : ($estado=='draft' ? 'Rechazado' : 'Eliminado'));
            $badge_class = $estado=='publish' ? 'badge-success' : ($estado=='pending' ? 'badge-warning' : ($estado=='draft' ? 'badge-danger' : 'badge-secondary'));
            ?>
            <div class="dashboard-card">
                <h2><?php the_title(); ?></h2>
                <span class="dashboard-badge <?php echo $badge_class; ?>"><?php echo $badge; ?></span>
                <div class="dashboard-actions">
                    <a href="/editar-inmueble/?id=<?php echo $post->ID; ?>" class="btn btn-sm">Editar</a>
                    <a href="?delete=<?php echo $post->ID; ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Seguro que deseas eliminar este inmueble?')">Eliminar</a>
                </div>
            </div>
        <?php endforeach; wp_reset_postdata(); else: ?>
            <p>No tienes inmuebles publicados aún.</p>
        <?php endif; ?>
    </div>
    <div class="dashboard-profile">
        <h3>Mi Perfil</h3>
        <?php $user = wp_get_current_user(); ?>
        <form method="post" enctype="multipart/form-data">
            <label>Nombre: <input type="text" name="display_name" value="<?php echo esc_attr($user->display_name); ?>"></label>
            <label>WhatsApp: <input type="text" name="whatsapp" value="<?php echo esc_attr(get_user_meta($user_id,'whatsapp',true)); ?>"></label>
            <label>Foto de perfil: <input type="file" name="profile_pic"></label>
            <button type="submit" name="update_profile">Actualizar Perfil</button>
        </form>
        <?php
        if(isset($_POST['update_profile'])){
            wp_update_user(['ID'=>$user_id,'display_name'=>sanitize_text_field($_POST['display_name'])]);
            update_user_meta($user_id,'whatsapp',sanitize_text_field($_POST['whatsapp']));
            if(!empty($_FILES['profile_pic']['name'])){
                require_once(ABSPATH.'wp-admin/includes/file.php');
                $uploaded = media_handle_upload('profile_pic',$user_id);
                if(!is_wp_error($uploaded)){
                    update_user_meta($user_id,'profile_pic',$uploaded);
                }
            }
            echo '<div class="dashboard-success">Perfil actualizado.</div>';
        }
        ?>
    </div>
</div>
<?php get_footer(); ?>
