<?php
require_once 'includes/db_connect.php';
require_once 'class/Validator.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Initialize validator and error array
    $validator = new Validator();
    $errors = [];

    // Validate inputs
    $nome = $validator->validateName($_POST['nome'] ?? '');
    $email = $validator->validateEmail($_POST['email'] ?? '');
    $senha = $validator->validatePassword($_POST['senha'] ?? '', $_POST['confirmar_senha'] ?? '');
    $telefone = $validator->validatePhone($_POST['telefone'] ?? '');

    $errors = $validator->getErrors();

    if (empty($errors)) {
        try {
            // Check database connection
            if (!$conn) {
                throw new Exception("Database connection failed");
            }

            // Check if email exists
            $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email_usuario = ?");
            if (!$stmt) {
                throw new Exception("Prepare statement failed");
            }

            $stmt->execute([$email]);

            if ($stmt->rowCount() > 0) {
                $errors['email'] = "Este email já está cadastrado.";
            } else {
                // Hash password
                $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

                // Insert new user
                $stmt = $conn->prepare("INSERT INTO usuarios (nome_usuario, email_usuario, senha, telefone) VALUES (?, ?, ?, ?)");
                if (!$stmt) {
                    throw new Exception("Prepare statement failed");
                }

                if ($stmt->execute([$nome, $email, $senha_hash, $telefone])) {
                    $user_id = $conn->lastInsertId(); // Changed from $pdo to $conn

                    if ($stmt->execute([$token, $user_id])) {
                        // Armazena os dados do usuário e produto na sessão para uso posterior
                        $_SESSION['mercado_pago_data'] = [
                            'user_id' => $user_id,
                            'username' => $nome,
                            'email' => $email,
                            'produto_nome' => "Curso Online", // Substitua pelo nome real
                            'produto_preco' => 100.00, // Substitua pelo preço real
                            'token' => $token
                        ];

                     header("Location: Processa_Api.php/Metodo_Pagamento.php");
                        exit;
                    }
                } else {
                    $errors['db'] = "Erro ao cadastrar. Tente novamente mais tarde.";
                }
            }
        } catch (Exception $e) {
            error_log("Database error: " . $e->getMessage());
            $errors['db'] = "Ocorreu um erro inesperado. Por favor, tente novamente.";
        }
    }

    
    $_SESSION['form_errors'] = $errors;
    $_SESSION['form_data'] = $_POST;
    header("Location: register.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro</title>
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- JS Bundle (inclui Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="assets/css/register.css">
</head>

<body>
    <div class="auth-form-wrapper">
        <div class="auth-form-container">
            <h1 class="auth-form-title">Crie sua conta</h1>

            <?php if (!empty($errors)): ?>
                <div class="auth-form-alert auth-form-alert--error">
                    <?php foreach ($errors as $error): ?>
                        <p class="auth-form-alert__message"><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="register.php" class="auth-form">
                <div class="auth-form__group">
                    <label for="nome" class="auth-form__label">Nome Completo</label>
                    <input type="text" id="nome" name="nome" class="auth-form__input" required
                        value="<?php echo htmlspecialchars($_POST['nome'] ?? ''); ?>">
                </div>

                <div class="auth-form__group">
                    <label for="email" class="auth-form__label">Email</label>
                    <input type="email" id="email" name="email" class="auth-form__input" required
                        value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>

                <div class="auth-form__group">
                    <label for="telefone" class="auth-form__label">Telefone (WhatsApp)</label>
                    <input type="tel" id="telefone" name="telefone" class="auth-form__input" required
                        value="<?php echo htmlspecialchars($_POST['telefone'] ?? ''); ?>">
                </div>

                <div class="auth-form__group">
                    <label for="senha" class="auth-form__label">Senha</label>
                    <input type="password" id="senha" name="senha" class="auth-form__input" required>
                </div>

                <div class="auth-form__group">
                    <label for="confirmar_senha" class="auth-form__label">Confirmar Senha</label>
                    <input type="password" id="confirmar_senha" name="confirmar_senha" class="auth-form__input"
                        required>
                </div>

                <button type="submit" class="auth-form__button auth-form__button--primary">Cadastrar</button>
            </form>

            <p class="auth-form-footer">Já tem uma conta? <a href="login.php" class="auth-form-footer__link">Faça
                    login</a></p>
        </div>
    </div>

    <script src="assets/js/script.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>

</body>

</html>