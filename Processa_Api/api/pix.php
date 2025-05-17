<?php
session_start();

// 1. Configurações de Segurança
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 2. Inclusão de dependências
require __DIR__ . '/vendor/autoload.php';
require_once 'C:/xampp/htdocs/Curso_Online/includes/db_connect.php';
$config = require_once('../config.php');

use MercadoPago\MercadoPagoConfig;

// 3. Configuração do SDK
MercadoPagoConfig::setAccessToken($config['accesstoken']);

// 4. Validação do Método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  die(json_encode(['error' => 'Método não permitido']));
}

// 5. Validação do CSRF Token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
  http_response_code(403);
  die(json_encode(['error' => 'Token CSRF inválido']));
}

// 6. Obter e validar dados da requisição
$required_fields = ['transactionAmount', 'email', 'payerFirstName', 'identificationType',
 'identificationNumber', 'payerLastName', 'description'];
foreach ($required_fields as $field) {
  if (empty($_POST[$field])) {
    http_response_code(400);
    die(json_encode(['error' => "O campo $field é obrigatório"]));
  }
}

try {
  // 7. Preparar dados para o PIX
  $pix_data = (object) [
    'description' => htmlspecialchars($_POST['description']),
    "payment_method_id" => "pix", // Forçando método PIX
    'payer' => (object) [
      'email' => filter_var($_POST['email'], FILTER_SANITIZE_EMAIL),
      'first_name' => htmlspecialchars($_POST['payerFirstName']),
      'last_name' => htmlspecialchars($_POST['payerLastName']),
      'identification' => (object) [
        'type' => $_POST['identificationType'],
        'number' => preg_replace('/[^0-9]/', '', $_POST['identificationNumber'])
      ]
    ]
  ];

  // 8. Processar pagamento
  $payment = new PixPayment($_SESSION['user_id'] ?? null);
  $result = $payment->createPayment(
    floatval(filter_var($_POST['amount'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION)),
    $pix_data
  );

  // 9. Resposta com dados do PIX
  $ticket_url = $result->point_of_interaction->transaction_data->ticket_url ?? null;
  $qr_code_base64 = $result->point_of_interaction->transaction_data->qr_code_base64 ?? null;
  $qr_code = $result->point_of_interaction->transaction_data->qr_code ?? null;
  $payment_id = $result->id;
  $expiration_date = $result->date_of_expiration;


} catch (Exception $e) {
  http_response_code(500);
  echo json_encode([
    'error' => 'Erro ao processar PIX',
    'details' => $e->getMessage()
  ]);
}

class PixPayment extends PaymentHandler
{
  public function createPayment($amount, $payer_data)
  {
    $this->validateUser();

    try {
      $payment_data = $this->preparePaymentData($amount, $payer_data);
      $payment = $this->paymentClient->create($payment_data);

      $this->savePaymentToDatabase($payment_data, $payment);

      return $payment;

    } catch (Exception $e) {
      throw new Exception("Erro ao criar pagamento com PIX: " . $e->getMessage());
    }
  }

  protected function preparePaymentData($amount, $payer_data)
  {
    return [
      "transaction_amount" => (float) $amount,
      "description" => "Pagamento do curso",
      "payment_method_id" => "pix",
      "payer" => [
        "email" => $payer_data->payer->email,
        "first_name" => $payer_data->payer->first_name,
        "identification" => [
          "type" => $payer_data->payer->identification->type,
          "number" => $payer_data->payer->identification->number
        ]
      ],
      "notification_url" => "https://lucianavenanciopsipp.com.br"
    ];
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
          <input type="hidden" name="description" id="description" value="Curso ">
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
      <img id="qr-code-img" src="data:image/jpeg;base64,<?php echo htmlspecialchars($qr_code_base64); ?>"
        alt="QR Code PIX">

      <!-- Adicionando a data de expiração -->
      <?php if (!empty($expiration_date)): ?>
        <p class="expiration-warning">⏰ Válido até: <?php
        $date = new DateTime($expiration_date);
        echo htmlspecialchars($date->format('d/m/Y H:i:s'));
        ?></p>
      <?php endif; ?>

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