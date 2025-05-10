<?php
require_once 'includes/db_connect.php';

// Busca conteúdo de marketing do banco (opcional)
// $stmt = $pdo->query("SELECT * FROM marketing_content");
// $contents = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Curso Incrível - Assine Já!</title>
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- JS Bundle (inclui Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="assets/css/curso_index.css">
</head>

<body>

  <header class="cabecalho-curso">
    <div class="cabecalho-curso__container">
        <img class="cabecalho-curso__banner" src="assets/images/banner.jpg" alt="Banner do curso">
        <h3 class="cabecalho-curso__titulo">Links Importantes</h3>
        <h6 class="cabecalho-curso__subtitulo">@lucianavenanciopsi</h6>
    </div>
</header>

<div class="marketing-page">
    <main class="marketing-content">
        <section>
            <img src="assets/images/beneficio1.jpg" alt="Benefício 1">
            <h2>Aprenda com os melhores</h2>
            <p>Nossos instrutores são especialistas no mercado com anos de experiência.</p>
            <a href="register.php" class="section-button">Clique aqui para saber mais</a>
        </section>

        <section>
            <img src="assets/images/beneficio2.jpg" alt="Benefício 2">
            <h2>Conteúdo exclusivo</h2>
            <p>Acesso a materiais que você não encontra em nenhum outro lugar.</p>
            <a href="register.php" class="section-button">Clique aqui para saber mais</a>
        </section>

        <section>
            <img src="assets/images/beneficio3.jpg" alt="Benefício 3">
            <h2>Suporte personalizado</h2>
            <p>Tire todas suas dúvidas diretamente com nossa equipe.</p>
            <a href="register.php" class="section-button">Clique aqui para saber mais</a>
        </section>

       
    </main>

    <footer>
        <p>© <?php echo date('Y'); ?> Curso Incrível. Todos os direitos reservados.</p>
    </footer>
</div>

    <script src="assets/js/script.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
</body>

</html>