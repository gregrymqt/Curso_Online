<?php
session_start();
require_once 'mercadopago.php';

// Configuração do Mercado Pago
MercadoPago::setAccessToken('YOUR_ACCESS_TOKEN');

// Verifica os parâmetros GET
$token = $_GET['token'] ?? '';
$resposta = strtolower($_GET['resposta'] ?? '');

if (empty($token) || empty($resposta)) {
    die("Token ou resposta inválidos.");
}

try {
    // Conexão com o banco de dados
    $conn = new PDO("mysql:host=localhost;dbname=sua_db", "usuario", "senha");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Verifica o token no banco
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE token_whatsapp = ?");
    $stmt->execute([$token]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        die("Token inválido ou expirado.");
    }

    // Processa a resposta
    if ($resposta === "sim") {
        // Cria a preferência de pagamento
        $preference = new \MercadoPago\Preference();
        
        $item = new \MercadoPago\Item();
        $item->title = "Nome do Produto";
        $item->quantity = 1;
        $item->unit_price = 100.00;
        
        $preference->items = [$item];
        $preference->save();

        // Redireciona para o checkout
        header("Location: " . $preference->init_point);
        exit;
    } else {
        // Resposta negativa
        echo "Pagamento cancelado. Volte quando quiser!";
        exit;
    }
} catch (PDOException $e) {
    die("Erro no banco de dados: " . $e->getMessage());
} catch (Exception $e) {
    die("Erro no Mercado Pago: " . $e->getMessage());
}