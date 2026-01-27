<?php
/*
Template Name: Debug Taxonomía
*/
get_header();
$queried = get_queried_object();
?>
<div style="max-width:700px;margin:40px auto;padding:32px;background:#fff;border-radius:12px;box-shadow:0 2px 16px 0 rgba(20,90,55,0.07);">
    <h2 style="color:#145a37;">Depuración de plantilla</h2>
    <p><strong>Plantilla usada:</strong> page-debug-tax.php</p>
    <p><strong>Objeto consultado:</strong></p>
    <pre style="background:#f6f6f6;padding:16px;border-radius:8px;font-size:0.98rem;overflow-x:auto;"><?php print_r($queried); ?></pre>
    <p><strong>is_tax('tipo-propiedad'):</strong> <?php var_export(is_tax('tipo-propiedad')); ?></p>
    <p><strong>is_tax():</strong> <?php var_export(is_tax()); ?></p>
    <p><strong>is_archive():</strong> <?php var_export(is_archive()); ?></p>
    <p><strong>is_singular():</strong> <?php var_export(is_singular()); ?></p>
    <p><strong>$_SERVER['REQUEST_URI']:</strong> <?php echo esc_html($_SERVER['REQUEST_URI']); ?></p>
</div>
<?php get_footer(); ?>
