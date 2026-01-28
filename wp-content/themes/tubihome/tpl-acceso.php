<?php
/*
Template Name: Acceso (Login, Registro, Recuperación)
*/
get_header();

// Redirigir si ya está logueado
if (is_user_logged_in()) {
    wp_redirect(site_url('/panel-control/'));
    exit;
}

$action = isset($_GET['action']) ? $_GET['action'] : 'login';

?>
<div class="acceso-container">
    <div class="acceso-tabs">
        <a href="?action=login" class="acceso-tab <?php if($action=='login') echo 'active'; ?>">Iniciar Sesión</a>
        <a href="?action=register" class="acceso-tab <?php if($action=='register') echo 'active'; ?>">Registrarse</a>
        <a href="?action=recover" class="acceso-tab <?php if($action=='recover') echo 'active'; ?>">¿Olvidaste tu clave?</a>
    </div>
    <div class="acceso-form">
        <?php if($action=='login') : ?>
            <?php // Formulario de Login ?>
            <form method="post">
                <input type="text" name="log" placeholder="Correo electrónico" required>
                <input type="password" name="pwd" placeholder="Contraseña" required>
                <?php wp_nonce_field('acceso_login','acceso_nonce'); ?>
                <button type="submit" name="acceso_login_btn">Entrar</button>
            </form>
            <?php
            if (isset($_POST['acceso_login_btn']) && wp_verify_nonce($_POST['acceso_nonce'],'acceso_login')) {
                $creds = array(
                    'user_login'    => sanitize_user($_POST['log']),
                    'user_password' => $_POST['pwd'],
                    'remember'      => true
                );
                $user = wp_signon($creds, false);
                if (is_wp_error($user)) {
                    echo '<div class="acceso-error">'.esc_html($user->get_error_message()).'</div>';
                } else {
                    wp_redirect(site_url('/panel-control/'));
                    exit;
                }
            }
            ?>
        <?php elseif($action=='register') : ?>
            <?php // Formulario de Registro ?>
            <form method="post">
                <input type="text" name="user_login" placeholder="Nombre de usuario" required>
                <input type="email" name="user_email" placeholder="Correo electrónico" required>
                <input type="password" name="user_pass" placeholder="Contraseña" required>
                <label><input type="checkbox" name="terms" required> Acepto términos y condiciones</label>
                <?php wp_nonce_field('acceso_register','acceso_nonce'); ?>
                <button type="submit" name="acceso_register_btn">Registrarme</button>
            </form>
            <?php
            if (isset($_POST['acceso_register_btn']) && wp_verify_nonce($_POST['acceso_nonce'],'acceso_register')) {
                if (!isset($_POST['terms'])) {
                    echo '<div class="acceso-error">Debes aceptar los términos.</div>';
                } else {
                    $user_id = wp_create_user(
                        sanitize_user($_POST['user_login']),
                        $_POST['user_pass'],
                        sanitize_email($_POST['user_email'])
                    );
                    if (is_wp_error($user_id)) {
                        echo '<div class="acceso-error">'.esc_html($user_id->get_error_message()).'</div>';
                    } else {
                        // Asignar rol y enviar email de confirmación aquí
                        wp_update_user(['ID'=>$user_id,'role'=>'subscriber']);
                        echo '<div class="acceso-success">¡Registro exitoso! Revisa tu correo para confirmar tu cuenta.</div>';
                    }
                }
            }
            ?>
        <?php elseif($action=='recover') : ?>
            <?php // Formulario de Recuperación ?>
            <form method="post">
                <input type="email" name="user_email" placeholder="Correo electrónico" required>
                <?php wp_nonce_field('acceso_recover','acceso_nonce'); ?>
                <button type="submit" name="acceso_recover_btn">Recuperar clave</button>
            </form>
            <?php
            if (isset($_POST['acceso_recover_btn']) && wp_verify_nonce($_POST['acceso_nonce'],'acceso_recover')) {
                $user = get_user_by('email', sanitize_email($_POST['user_email']));
                if ($user) {
                    // Enviar email con enlace de recuperación
                    $reset = retrieve_password();
                    echo '<div class="acceso-success">Si el correo existe, recibirás instrucciones.</div>';
                } else {
                    echo '<div class="acceso-error">No se encontró ese correo.</div>';
                }
            }
            ?>
        <?php endif; ?>
    </div>
</div>
<?php get_footer(); ?>
