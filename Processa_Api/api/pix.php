<?php
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
require __DIR__ . '/vendor/autoload.php'; // Caminho correto para o autoload
$config = require_once('../config.php');
$accesstoken = $config['accesstoken'];

use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Client\Common\RequestOptions;
use MercadoPago\MercadoPagoConfig;

MercadoPagoConfig::setAccessToken($accesstoken);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // No processamento:
if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("Token inválido!");
}
  try {
    $client = new PaymentClient();
    $request_options = new RequestOptions();
    $request_options->setCustomHeaders(["X-Idempotency-Key: " . bin2hex(random_bytes(16))]);

    $payment = $client->create([
      "transaction_amount" => (float) $_POST['transactionAmount'],
      "payment_method_id" => "pix", // Forçando método PIX
      "payer" => [
        "first_name" => $_POST['payerFirstName'],
        "last_name" => $_POST['payerLastName'],
        "email" => $_POST['email'],
        "identification" => [
          "type" => $_POST['identificationType'],
          "number" => $_POST['identificationNumber']
        ]
      ]
    ], $request_options);

    // Extrai os dados do PIX
    $ticket_url = $payment->point_of_interaction->transaction_data->ticket_url ?? null;
    $qr_code_base64 = $payment->point_of_interaction->transaction_data->qr_code_base64 ?? null;
    $qr_code = $payment->point_of_interaction->transaction_data->qr_code ?? null;
    $payment_id = $payment->id;

  } catch (Exception $e) {
    $error = $e->getMessage();
  }
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
<link rel="stylesheet" href="../css/pix.css">
</head>

<body>
  <?php if (!isset($payment)): ?>
    <form id="form-checkout" method="post">
      <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
      <div>
        <div>
          <label for="payerFirstName">Nome</label>
          <input id="form-checkout__payerFirstName" name="payerFirstName" type="text" required
            placeholder="Digite seu nome">
        </div>
        <div>
          <label for="payerLastName">Sobrenome</label>
          <input id="form-checkout__payerLastName" name="payerLastName" type="text" required
            placeholder="Digite seu sobrenome">
        </div>
        <div>
          <label for="email">E-mail</label>
          <input id="form-checkout__email" name="email" type="email" required placeholder="seu@email.com">
        </div>
        <div>
          <label for="identificationType">Tipo de documento</label>
          <select id="form-checkout__identificationType" name="identificationType" required>
            <option value="" disabled selected>Selecione...</option>
          </select>
        </div>
        <div>
          <label for="identificationNumber">Número do documento</label>
          <input id="form-checkout__identificationNumber" name="identificationNumber" type="text" required
            placeholder="Digite seu documento">
        </div>
      </div>
      <div>
        <div>
          <input type="hidden" name="transactionAmount" id="transactionAmount" value="100">
          <input type="hidden" name="description" id="description" value="Nome do Produto">
          <button type="submit" id="submit-button">Pagar</button>
        </div>
      </div>
    </form>

  <?php elseif (isset($error)): ?>
    <div class="error">
      <h3>Erro no processamento do pagamento</h3>
      <p><?php echo htmlspecialchars($error); ?></p>
      <a href="<?php echo $_SERVER['PHP_SELF']; ?>">Tentar novamente</a>
    </div>

  <?php else: ?>
    <div id="pix-result">
      <h3>Pagamento via PIX criado com sucesso!</h3>
      <p>ID do pagamento: <?php echo htmlspecialchars($payment_id); ?></p>
      <p>Escaneie o QR Code abaixo ou clique no link para pagar:</p>
      <a href="<?php echo htmlspecialchars($ticket_url); ?>" target="_blank">Abrir pagamento PIX</a>
      <img src="data:image/jpeg;base64,<?php echo htmlspecialchars($qr_code_base64); ?>" alt="QR Code PIX">
      <div>
        <label for="pix-code">Código PIX (copie e cole no seu app):</label>
        <input type="text" id="pix-code" value="<?php echo htmlspecialchars($qr_code); ?>" readonly>
        <button onclick="copyPixCode()">Copiar Código</button>
      </div>
    </div>

    <script>
      function copyPixCode() {
        const input = document.getElementById('pix-code');
        input.select();
        document.execCommand('copy');
        alert('Código PIX copiado!');
      }
    </script>
  <?php endif; ?>

  <script src="https://sdk.mercadopago.com/js/v2"></script>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="../js/pix.js"></script>
</body>

</html>