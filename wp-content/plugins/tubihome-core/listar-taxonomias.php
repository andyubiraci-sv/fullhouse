<?php
// Página temporal para listar slugs de tipo-propiedad y tipo-operacion
require_once('../../../wp-load.php');
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Slugs de Taxonomías</title>
    <style>body{font-family:sans-serif;padding:2em;}h3{margin-top:2em;}ul{line-height:1.7;}</style>
</head>
<body>
<h2>Slugs de tipo-propiedad y tipo-operacion</h2>
<?php
echo "<h3>Tipo de Propiedad</h3><ul>";
$props = get_terms(['taxonomy' => 'tipo-propiedad', 'hide_empty' => false]);
foreach ($props as $term) {
    echo "<li>{$term->name} <b>({$term->slug})</b></li>";
}
echo "</ul>";

echo "<h3>Tipo de Operación</h3><ul>";
$ops = get_terms(['taxonomy' => 'tipo-operacion', 'hide_empty' => false]);
foreach ($ops as $term) {
    echo "<li>{$term->name} <b>({$term->slug})</b></li>";
}
echo "</ul>";
?>
</body>
</html>