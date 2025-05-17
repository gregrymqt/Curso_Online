<?php
$card = true;
session_start();

// Inicializa CSRF token se não existir
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once __DIR__ . '/../config.php';
require_once 'C:/xampp/htdocs/Curso_Online/includes/db_connect.php';
require_once 'C:/xampp/htdocs/Curso_Online/Processa_Api/class/payment.class.php';
require_once 'preferences.php';
use MercadoPago\MercadoPagoConfig;

// Verifica método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  die(json_encode(['error' => 'Método não permitido']));
}

// Valida CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
  http_response_code(403);
  die(json_encode(['error' => 'Token CSRF inválido']));
}

if (!isset($body) || !is_object($body)) {
  http_response_code(400);
  die(json_encode(['error' => 'Dados do pagamento não encontrados']));
}

$requiredFields = [
  'token',
  'payment_method_id',
  'installments',
  'transaction_amount',
  'payer->email',
  'payer->first_name',
  'payer->identification->type',
  'payer->identification->number'
];

foreach ($requiredFields as $field) {
  if (strpos($field, '->') !== false) {
    $nested = explode('->', $field);
    $current = $body;

    foreach ($nested as $prop) {
      if (!isset($current->$prop)) {
        http_response_code(400);
        die(json_encode(['error' => "Campo obrigatório faltando: {$field}"]));
      }
      $current = $current->$prop;
    }
  } elseif (!isset($body->$field)) {
    http_response_code(400);
    die(json_encode(['error' => "Campo obrigatório faltando: {$field}"]));
  }
}

try {
  $dados = (object) [
    'token' => htmlspecialchars($body->token, ENT_QUOTES, 'UTF-8'),
    'payment_method_id' => htmlspecialchars($body->payment_method_id, ENT_QUOTES, 'UTF-8'),
    'installments' => filter_var($body->installments, FILTER_VALIDATE_INT, [
      'options' => ['min_range' => 1, 'max_range' => 12]
    ]),
    'description' => htmlspecialchars($body->description, ENT_QUOTES, 'UTF-8') ,
    'payer' => (object) [
      'email' => filter_var($body->payer->email, FILTER_SANITIZE_EMAIL),
      'identification' => (object) [
        'type' => htmlspecialchars($body->payer->identification->type, ENT_QUOTES, 'UTF-8'),
        'number' => preg_replace('/[^0-9]/', '', $body->payer->identification->number)
      ]
    ],
    'issuer_id' => isset($body->issuer_id) ?
      htmlspecialchars($body->issuer_id, ENT_QUOTES, 'UTF-8') : null,
    'transaction_amount' => filter_var($body->transaction_amount, FILTER_VALIDATE_FLOAT, [
      'options' => ['min_range' => 0.01]
    ])
  ];

  $payment = new CreditCardPayment($_SESSION['user_id'] ?? null);
  $result = $payment->createPayment($dados->transaction_amount, $dados);

  echo $result;

} catch (Exception $e) {
  http_response_code(500);
  echo json_encode([
    'error' => 'Erro no processamento do pagamento',
    'details' => $e->getMessage()
  ]);
}

class CreditCardPayment extends PaymentHandler
{
  private $config;

  public function __construct($user_id = null)
  {
    parent::__construct($user_id);
    $this->config = require('../config.php');
    MercadoPagoConfig::setAccessToken($this->config['accesstoken']);
  }

  public function createPayment($amount, $payer_data)
  {
    $this->validateUser();

    try {
      $payment_data = $this->preparePaymentData($amount, $payer_data);
      $payment = $this->createMercadoPagoCartaoCredito($payment_data);

      $this->savePaymentToDatabase($payment_data, $payment);
      return $payment;

    } catch (Exception $e) {
      throw new Exception("Erro ao criar pagamento com cartão: " . $e->getMessage());
    }
  }

  protected function preparePaymentData($amount, $payer_data)
  {
    return [
      'transaction_amount' => (float) $amount,
      'description' => 'Pagamento com Cartão de Crédito',
      'installments' => $payer_data->installments,
      'payer' => [
        'email' => $payer_data->payer->email,
        'first_name' => $payer_data->payer->first_name ?? 'Cliente',
        'identification' => [
          'type' => $payer_data->payer->identification->type,
          'number' => $payer_data->payer->identification->number
        ]
      ],
      'payment_method_id' => $payer_data->payment_method_id,
      'token' => $payer_data->token,
      'issuer_id' => $payer_data->issuer_id ?? null
    ];
  }

  protected function createMercadoPagoCartaoCredito($payment_data)
  {
    $ch = curl_init();

    curl_setopt_array($ch, [
      CURLOPT_URL => 'https://api.mercadopago.com/v1/payments',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_POST => true,
      CURLOPT_POSTFIELDS => json_encode($payment_data),
      CURLOPT_HTTPHEADER => [
        'accept: application/json',
        'Content-Type: application/json',
        'Authorization: Bearer ' . $this->config['accesstoken'],
        'X-Idempotency-Key: ' . bin2hex(random_bytes(16))
      ]
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
      throw new Exception("Erro na comunicação com o Mercado Pago: " . $error);
    }

    $response_data = json_decode($response);

    if ($http_code !== 200) {
      $error_msg = $response_data->message ?? "Erro ao processar pagamento (HTTP $http_code)";
      if (isset($response_data->cause)) {
        $error_msg .= ". Causa: " . json_encode($response_data->cause);
      }
      throw new Exception($error_msg);
    }

    return $response_data;
  }

}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pagamento</title>
  <link rel="stylesheet" href="../css/style.css">
</head>

<body>
  <input type="hidden" id="valor_payment"
    value="<?= htmlspecialchars($body->transaction_amount ?? '', ENT_QUOTES, 'UTF-8') ?>">
  <input type="hidden" id="preference_id" value="<?= htmlspecialchars($preference_id ?? '', ENT_QUOTES, 'UTF-8') ?>">

  <div class="card_page_payment">
    <div id="statusScreenBrick_container"></div>
    <div id="paymentBrick_container"></div>
  </div>

  <script src="https://sdk.mercadopago.com/js/v2"></script>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="../js/card.js"></script>
</body>

</html>