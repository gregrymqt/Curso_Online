<?php
require_once 'includes/db_connect.php';
require_once 'classes/Validator.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $validator = new Validator();
    
    // Validação
    $nome = $validator->validateName($_POST['nome'] ?? '');
    $email = $validator->validateEmail($_POST['email'] ?? '');
    $senha = $validator->validatePassword($_POST['senha'] ?? '', $_POST['confirmar_senha'] ?? '');
    $telefone = $validator->validatePhone($_POST['telefone'] ?? '');
    
    $errors = $validator->getErrors();
    
    if (empty($errors)) {
        // Verifica se email já existe
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email_usuario = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            $errors['email'] = "Este email já está cadastrado.";
        } else {
            // Hash da senha
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            
            // Insere no banco
            $stmt = $pdo->prepare("INSERT INTO usuarios (nome_usuario, email_usuario, senha, telefone) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$nome, $email, $senha_hash, $telefone])) {
                $user_id = $pdo->lastInsertId();
                
                // Gera token para WhatsApp
                $token = bin2hex(random_bytes(16));
                $stmt = $pdo->prepare("UPDATE usuarios SET token_whatsapp = ? WHERE id = ?");
                $stmt->execute([$token, $user_id]);
                
                // Redireciona para WhatsApp
                $whatsapp_url = "https://wa.me/SEUNUMERODOWHATSAPP?text=" . urlencode("Olá, quero continuar com o pagamento! Token: $token (Sim ou Não)");
                header("Location: $whatsapp_url");
                exit;
            } else {
                $errors['db'] = "Erro ao cadastrar. Tente novamente mais tarde.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="form-container">
        <h1>Crie sua conta</h1>
        
        <?php if (!empty($errors)): ?>
            <div class="alert error">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="register.php">
            <div class="form-group">
                <label for="nome">Nome Completo</label>
                <input type="text" id="nome" name="nome" required value="<?php echo htmlspecialchars($_POST['nome'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="telefone">Telefone (WhatsApp)</label>
                <input type="tel" id="telefone" name="telefone" required value="<?php echo htmlspecialchars($_POST['telefone'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" required>
            </div>
            
            <div class="form-group">
                <label for="confirmar_senha">Confirmar Senha</label>
                <input type="password" id="confirmar_senha" name="confirmar_senha" required>
            </div>
            
            <button type="submit" class="btn">Cadastrar</button>
        </form>
        
        <p>Já tem uma conta? <a href="login.php">Faça login</a></p>
    </div>
    
    <script src="assets/js/script.js"></script>
</body>
</html>