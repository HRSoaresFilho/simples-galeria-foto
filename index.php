<?php
$dir = __DIR__ . '/fotos';
$images = array_diff(scandir($dir), ['.', '..']);

$extensoesExcluidas = ['.html', '.pdf'];

$images = array_filter($images, function ($file) use ($extensoesExcluidas) {
    $extensao = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    return !in_array('.' . $extensao, $extensoesExcluidas);
});
rsort($images);
$likes = json_decode(file_get_contents(__DIR__ . '/dados/likes.json'), true) ?? [];
$modoEscuro = isset($_COOKIE['modo_escuro']) && $_COOKIE['modo_escuro'] === '1';
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galeria IA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/glightbox/dist/css/glightbox.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>

<body class="<?= $modoEscuro ? 'dark-mode' : '' ?>">
    <div class="container-fluid py-3 py-md-4">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center mb-4">
            <h1 class="display-4 display-md-3 mb-3 mb-sm-0">🪐 Galeria IA</h1>
            <button class="btn btn-sm btn-dark px-3 py-2" onclick="alternarModo()">🌙 Modo</button>
        </div>

        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3 g-md-4" id="gallery-container">
            <?php foreach ($images as $img):
                $comentarios = file_exists("dados/comentarios/$img.json") ? json_decode(file_get_contents("dados/comentarios/$img.json"), true) : [];
            ?>
                <div class="col">
                    <div class="image-container ratio ratio-1x1">
                        <a href="fotos/<?= $img ?>" class="glightbox" data-gallery="gallery">
                            <img src="fotos/<?= $img ?>" alt="Imagem" class="img-cover">
                        </a>
                        <div class="overlay">
                            <button class="btn-like" onclick="curtir('<?= $img ?>')">
                                ❤️ <span id="likes-<?= $img ?>"><?= $likes[$img] ?? 0 ?></span>
                            </button>
                            <button class="btn-comment" onclick="abrirComentarios('<?= $img ?>')">
                                💬 <span id="comments-<?= $img ?>"><?= count($comentarios) ?></span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Modal de Comentários -->
                <div class="modal fade" id="modal-<?= $img ?>" tabindex="-1" aria-labelledby="comentariosLabel<?= $img ?>" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="comentariosLabel<?= $img ?>">Comentários da imagem</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                            </div>
                            <div class="modal-body">
                                <?php foreach ($comentarios as $c): ?>
                                    <div class="comentario mb-2">
                                        <small><b><?= $c['data'] ?></b></small><br>
                                        <?= $c['texto'] ?>
                                    </div>
                                <?php endforeach; ?>
                                <hr>
                                <form action="comentario.php" method="POST">
                                    <input type="hidden" name="imagem" value="<?= $img ?>">
                                    <div class="mb-2">
                                        <textarea name="comentario" class="form-control" rows="3" placeholder="Escreva seu comentário..." required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100">Enviar comentário</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/glightbox/dist/js/glightbox.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log("Iniciando GLightbox...");

            const lightbox = GLightbox({
                selector: '.glightbox',
                touchNavigation: true,
                loop: true,
                autoplayVideos: true,
                zoomable: true
            });

            console.log("GLightbox iniciado", lightbox);
        });

        function curtir(imagem) {
            const curtido = localStorage.getItem('curtido_' + imagem);
            const acao = curtido ? 'remover' : 'adicionar';

            fetch('curtir.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'imagem=' + encodeURIComponent(imagem) + '&acao=' + acao
                })
                .then(res => res.json())
                .then(data => {
                    if (data.sucesso) {
                        if (acao === 'adicionar') {
                            localStorage.setItem('curtido_' + imagem, true);
                        } else {
                            localStorage.removeItem('curtido_' + imagem);
                        }
                        document.getElementById('likes-' + imagem).innerText = data.likes;
                    }
                });
        }

        function alternarModo() {
            document.body.classList.toggle('dark-mode');
            document.cookie = "modo_escuro=" + (document.body.classList.contains('dark-mode') ? '1' : '0') + ";path=/";
        }

        function abrirComentarios(imgId) {
            const modal = new bootstrap.Modal(document.getElementById('modal-' + imgId));
            modal.show();
        }

        // No seu formulário de comentários, substitua por:
        document.querySelectorAll('form[action="comentario.php"]').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(this);
                const imgId = formData.get('imagem');

                fetch('comentario.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            // Atualiza a contagem de comentários
                            document.getElementById('comments-' + imgId).innerText = data.count;
                            // Recarrega os comentários no modal (opcional)
                            const modalBody = document.querySelector('#modal-' + imgId + ' .modal-body');
                            // Aqui você pode adicionar lógica para atualizar a lista de comentários
                            // ou simplesmente recarregar a página
                            location.reload();
                        }
                    });
            });
        });
    </script>
</body>

</html>