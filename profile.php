<?php
require_once 'includes/auth.php';
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

// Verifica se o usuário está logado
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT nome_usuario, email_usuario, foto_usuario, status_pagamento FROM usuarios WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    die("Usuário não encontrado");
}

// Processar upload da foto
$upload_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['foto_perfil'])) {
    $target_dir = "uploads/profile_pics/";
    
    // Verifica se o diretório existe, se não, cria
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $upload_result = uploadFile($_FILES['foto_perfil'], $target_dir);
    
    if ($upload_result['success']) {
        // Remove a foto antiga se não for a padrão
        if ($user['foto_usuario'] !== 'default.jpg' && file_exists($target_dir . $user['foto_usuario'])) {
            unlink($target_dir . $user['foto_usuario']);
        }
        
        // Atualiza no banco de dados
        $stmt = $pdo->prepare("UPDATE usuarios SET foto_usuario = ? WHERE id = ?");
        if ($stmt->execute([$upload_result['filename'], $user_id])) {
            // Atualiza a variável $user para mostrar a nova foto
            $user['foto_usuario'] = $upload_result['filename'];
            $success_message = "Foto atualizada com sucesso!";
        } else {
            $upload_error = "Erro ao atualizar no banco de dados.";
        }
    } else {
        $upload_error = $upload_result['error'];
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="profile-container">
        <div class="profile-header">
            <div class="profile-pic-container">
                <img src="uploads/profile_pics/<?php echo htmlspecialchars($user['foto_usuario']); ?>" alt="Foto do usuário" class="profile-pic" id="profile-pic-preview">
                <form method="POST" enctype="multipart/form-data" class="upload-form">
                    <label for="foto_perfil" class="upload-label">
                        <span class="upload-icon">+</span>
                        <span class="upload-text">Alterar Foto</span>
                    </label>
                    <input type="file" id="foto_perfil" name="foto_perfil" accept="image/*" style="display: none;">
                    <button type="submit" class="btn-upload" style="display: none;">Enviar</button>
                </form>
            </div>
            <h1><?php echo htmlspecialchars($user['nome_usuario']); ?></h1>
        </div>
        
        <?php if (!empty($upload_error)): ?>
            <div class="alert error"><?php echo htmlspecialchars($upload_error); ?></div>
        <?php elseif (!empty($success_message)): ?>
            <div class="alert success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        
        <div class="profile-info">
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email_usuario']); ?></p>
            <p><strong>Status do Pagamento:</strong> 
                <?php echo $user['status_pagamento'] ? 'Ativo' : 'Inativo'; ?>
            </p>
        </div>
        
        <?php if ($user['status_pagamento']): ?>
            <a href="videos.php" class="btn">Acessar Conteúdo</a>
        <?php else: ?>
            <a href="payment.php" class="btn">Assinar Agora</a>
        <?php endif; ?>
        
        <a href="logout.php" class="btn btn-logout">Sair</a>
    </div>
    
    <script src="assets/js/script.js"></script>
    <script>
        // Preview da imagem antes de enviar
        document.getElementById('foto_perfil').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    document.getElementById('profile-pic-preview').src = event.target.result;
                    // Envia o formulário automaticamente após selecionar a imagem
                    document.querySelector('.upload-form').submit();
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>