<?php
session_start();
$card = true;
require_once 'preferences.php';
require_once __DIR__ . '/../vendor/autoload.php';

if (isset($body->token)) {

  $curl = curl_init();



  curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://api.mercadopago.com/v1/payments',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => '{
  "description": "Payment for product",
  "installments":' . $body->installments . ',
  "payer": {
    "email": "' . $body->payer->email . '",
    "identification": {
      "type": "' . $body->payer->identification->type . '",
      "number": "' . $body->payer->identification->number . '"
    }
  },
  "issuer_id": "' . $body->issuer_id . '",
  "payment_method_id": "' . $body->payment_method_id . '",
  "token": "' . $body->token . '",
  "transaction_amount": ' . $body->transaction_amount . '
}',
    CURLOPT_HTTPHEADER => array(
      'Content-Type: application/json',
      'Authorization: Bearer ' . $accesstoken,
      'X-Idempotency-Key: 0d5020ed-1af6-469c-ae06-c3bec19954bb',
    ),
  ));

  // Executa a requisição cURL
  header('Content-Type: application/json');

// 1. Executa a requisição cURL
$response = curl_exec($curl);
$error = curl_error($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

// 2. Tratamento de erros
if ($response === false) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erro na comunicação com o gateway de pagamento',
          ]);
    exit;
}

// 3. Validação da resposta
if (empty($response)) {
    http_response_code(502);
    echo json_encode(['success' => false, 'error' => 'Resposta inválida do servidor']);
    exit;
}

$paymentData = json_decode($response);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(502);
    echo json_encode(['success' => false, 'error' => 'Dados de pagamento corrompidos']);
    exit;
}

// 4. Filtra dados sensíveis (OBRIGATÓRIO)
$safeData = [
    'id' => $paymentData->id ?? null,
    'status' => $paymentData->status ?? null,
    'amount' => $paymentData->transaction_amount ?? null,
    'payment_method' => $paymentData->payment_method_id ?? null,
    'date_approved' => $paymentData->date_approved ?? null,
    'payer' => [
        'email' => $paymentData->payer->email ?? null
    ]
];

// 5. Retorno seguro para o frontend
http_response_code($httpCode);
echo json_encode([
    'success' => true,
    'payment' => $safeData,
]);
exit;

}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../css/style.css">
  <title>Document</title>
  <script src="https://sdk.mercadopago.com/js/v2"></script>

</head>

<body>

  <input type="hidden" id="valor_payment" value="<?= $amount; ?>">
  <input type="hidden" id="preference_id" value="<?= $preference_id; ?>">




  <div class="card_page_payment">
    <div id="statusScreenBrick_container"></div>
    <div id="paymentBrick_container"></div>
  </div>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="../js/card.js"></script>
</body>

</html>