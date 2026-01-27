
<?php get_header(); ?>
<main>
    <h1>Bienvenido a Tubihome</h1>
    <p>Este es el index.php del tema minimalista.</p>

    <div style="background:#fffbe6;border:2px solid #ffe58f;padding:24px;margin:32px 0 0 0;max-width:800px;">
        <h2 style="color:#d48806;">DEBUG WordPress Query</h2>
        <p><strong>Plantilla usada:</strong> index.php</p>
        <p><strong>is_tax('tipo-propiedad'):</strong> <?php var_export(is_tax('tipo-propiedad')); ?></p>
        <p><strong>is_tax():</strong> <?php var_export(is_tax()); ?></p>
        <p><strong>is_archive():</strong> <?php var_export(is_archive()); ?></p>
        <p><strong>is_singular():</strong> <?php var_export(is_singular()); ?></p>
        <p><strong>Objeto consultado (get_queried_object()):</strong></p>
        <pre style="background:#f6f6f6;padding:12px;border-radius:8px;font-size:0.98rem;overflow-x:auto;"><?php print_r(get_queried_object()); ?></pre>
        <p><strong>REQUEST_URI:</strong> <?php echo esc_html($_SERVER['REQUEST_URI']); ?></p>
        <p><strong>Template hierarchy:</strong> taxonomy-tipo-propiedad.php → taxonomy.php → archive.php → index.php</p>
    </div>
</main>
<?php get_footer(); ?>
