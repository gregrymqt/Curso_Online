<?php
session_start();
require_once '../includes/db_connect.php';


$user_id = isset($_SESSION['mercado_pago_data']['user_id']) ? $_SESSION['mercado_pago_data']['user_id'] : 'Nenhum id passado';
$amount = isset($_SESSION['mercado_pago_data']['produto_preco']) ? $_SESSION['mercado_pago_data']['produto_preco'] : 0;
$email = isset($_SESSION['mercado_pago_data']['email']) ? $_SESSION['mercado_pago_data']['email'] : 'Nenhum email passado';
$username = isset($_SESSION['mercado_pago_data']['username']) ? $_SESSION['mercado_pago_data']['username'] : 'Nenhum username passado';
$produto_nome = isset($_SESSION['mercado_pago_data']['produto_nome']) ? $_SESSION['mercado_pago_data']['produto_nome'] : 'Nenhum produto passado';


?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Adicionar Saldo</title>
</head>
<body>
    <div class="container">
        <div class="balance-box">
            <div>Username: <?= $username ?></div>
            <div>Atualizado agora</div>
        </div>
        <form method="post">
            <!-- Campo de input para valor do PIX -->
            <div class="input-group" style="margin-bottom: 15px;">
                <label for="valor-pix"
                    style="display: block; text-align: left; margin-bottom: 5px; font-weight: 500;">Valor a pagar
                    (R$)<?= number_format($amount, 2, ',', '.') ?></label>
            </div>
            <button class="btn btn-pix" name="pix">
                <i class="fas fa-qrcode"></i> Adicionar saldo por PIX
            </button>

            <button class="btn btn-preference" name="preference">
                <i class="fas fa-barcode"></i> Adicionar saldo por Link
            </button>

            <button class="btn btn-card" name="cartao">
                <i class="fas fa-credit-card"></i> Adicionar saldo por Cartão
            </button>
        </form>
    </div>
    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        if (isset($_POST['pix'])) {
            $_SESSION['mercado_pago_data'] = [
                'user_id' => $user_id,
                'username' => $username,
                'email' => $email,
                'produto_nome' => $produto_nome, // Substitua pelo nome real
                'produto_preco' => $amount, // Substitua pelo preço real
                'token' => $token
            ];
            header('Location: api/pix.php');
            exit();
        } elseif (isset($_POST['preference'])) {
            $_SESSION['mercado_pago_data'] = [
                'user_id' => $user_id,
                'username' => $username,
                'email' => $email,
                'produto_nome' => $produto_nome, // Substitua pelo nome real
                'produto_preco' => $amount, // Substitua pelo preço real
                'token' => $token
            ];
            header('Location: api/preference.php');
            exit();
        } elseif (isset($_POST['cartao'])) {
            $_SESSION['mercado_pago_data'] = [
                'user_id' => $user_id,
                'username' => $username,
                'email' => $email,
                'produto_nome' => $produto_nome, // Substitua pelo nome real
                'produto_preco' => $amount, // Substitua pelo preço real
                'token' => $token
            ];
            header('Location: api/cartao.php');
            exit();
        } else {
            // Nenhum método selecionado
            header('Location: index.php?erro=metodo_nao_selecionado');
            exit();
        }
    }
    ?>
    <!-- Adicione Font Awesome para ícones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</body>

</html>