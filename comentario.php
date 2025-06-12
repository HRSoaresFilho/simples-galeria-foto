<?php
$imagem = basename($_POST['imagem'] ?? '');
$comentario = trim($_POST['comentario'] ?? '');
if (!$imagem || !$comentario) exit;

$arquivo = __DIR__ . "/dados/comentarios/{$imagem}.json";
$comentarios = file_exists($arquivo) ? json_decode(file_get_contents($arquivo), true) : [];
$comentarios[] = ['texto' => htmlspecialchars($comentario), 'data' => date('d/m/Y H:i')];
file_put_contents($arquivo, json_encode($comentarios, JSON_PRETTY_PRINT));

header('Location: index.php#img-' . $imagem);
exit;