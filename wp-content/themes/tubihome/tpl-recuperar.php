<?php
/*
Template Name: Recuperar Password
*/
get_header();
if (is_user_logged_in()) {
    wp_redirect(site_url('/panel-control/'));
    exit;
}
if(isset($_POST['recover_submit'])){
    $user = get_user_by('email', sanitize_email($_POST['user_email']));
    if($user){
        retrieve_password();
        echo '<div class="recover-success">Si el correo existe, recibirás instrucciones para restablecer tu clave.</div>';
    } else {
        echo '<div class="recover-error">No se encontró ese correo.</div>';
    }
}
?>
<div class="recover-container">
    <h2>Recuperar Contraseña</h2>
    <form method="post">
        <input type="email" name="user_email" placeholder="Correo electrónico" required>
        <button type="submit" name="recover_submit">Recuperar clave</button>
    </form>
</div>
<?php get_footer(); ?>
