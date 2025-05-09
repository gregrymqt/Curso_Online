<?php
require_once 'includes/auth.php';
require_once 'includes/db_connect.php';

// Verifica login e pagamento
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT status_pagamento FROM usuarios WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user || !$user['status_pagamento']) {
    header('Location: payment.php');
    exit;
}

// Busca conteúdo do curso
$stmt = $pdo->query("SELECT * FROM conteudo ORDER BY ordem ASC");
$conteudos = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conteúdo do Curso</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <h1>Conteúdo do Curso</h1>
        <a href="profile.php" class="btn">Voltar ao Perfil</a>
    </header>
    
    <div class="content-grid">
        <?php foreach ($conteudos as $conteudo): ?>
            <div class="content-item">
                <?php if ($conteudo['tipo'] === 'video'): ?>
                    <video controls>
                        <source src="uploads/course_content/videos/<?php echo htmlspecialchars($conteudo['caminho_arquivo']); ?>" type="video/mp4">
                        Seu navegador não suporta vídeos.
                    </video>
                <?php else: ?>
                    <img src="uploads/course_content/images/<?php echo htmlspecialchars($conteudo['caminho_arquivo']); ?>" alt="<?php echo htmlspecialchars($conteudo['titulo']); ?>">
                <?php endif; ?>
                <h3><?php echo htmlspecialchars($conteudo['titulo']); ?></h3>
                <p><?php echo htmlspecialchars($conteudo['descricao']); ?></p>
            </div>
        <?php endforeach; ?>
    </div>
    
    <script src="assets/js/script.js"></script>
</body>
</html>