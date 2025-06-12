<?php
$arquivo = __DIR__ . '/dados/likes.json';
$likes = file_exists($arquivo) ? json_decode(file_get_contents($arquivo), true) : [];

$imagem = $_POST['imagem'] ?? '';
$acao = $_POST['acao'] ?? 'adicionar';

if ($imagem) {
    if (!isset($likes[$imagem])) $likes[$imagem] = 0;

    if ($acao === 'adicionar') {
        $likes[$imagem]++;
    } elseif ($acao === 'remover' && $likes[$imagem] > 0) {
        $likes[$imagem]--;
    }

    file_put_contents($arquivo, json_encode($likes));

    echo json_encode(['sucesso' => true, 'likes' => $likes[$imagem]]);
} else {
    echo json_encode(['sucesso' => false]);
}