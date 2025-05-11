<?php
session_start();
require_once('class/conn.class.php');
require_once('class/User.class.php');


$user = new User(1);
$dados_user = $user->get();

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
            <div>Username: <?= $dados_user->username ?></div>
            <div class="balance-amount">R$ <?= number_format($dados_user->balance, 2, ',', '.') ?></div>
            <div>Atualizado agora</div>
        </div>
        <form method="post">
            <!-- Campo de input para valor do PIX -->
            <div class="input-group" style="margin-bottom: 15px;">
                <label for="valor-pix"
                    style="display: block; text-align: left; margin-bottom: 5px; font-weight: 500;">Valor do PIX
                    (R$)</label>
                <input type="number" id="valor_deposito" name="valor_deposito" min="1" step="0.01" style="
                    width: 100%;
                    padding: 12px;
                    border: 1px solid #ddd;
                    border-radius: 8px;
                    font-size: 16px;
                    box-sizing: border-box;
                " required>
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

        $valor_redirecionamento = $_POST['valor_deposito'];

        if (isset($_POST['pix'])) {
            $_SESSION['valor_passado'] = $valor_redirecionamento;
            header('Location: api/pix.php');
            exit();
        } elseif (isset($_POST['preference'])) {
            $_SESSION['valor_passado'] = $valor_redirecionamento;
            header('Location: api/preference.php');
            exit();
        } elseif (isset($_POST['cartao'])) {
            $_SESSION['valor_passado'] = $valor_redirecionamento;
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