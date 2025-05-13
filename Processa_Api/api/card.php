<?php
session_start();
if (isset($_POST['token'])) {
    $config = require_once('../config.php');
    require_once 'C:/xampp/htdocs/Curso_Online/includes/db_connect.php';
    require_once 'C:/xampp/htdocs/Curso_Online/Processa_Api/class/payment.class.php';
    $accesstoken = $config['accesstoken'];

   

    if ($payCreate) {
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
  "external_reference": "' . $payCreate . '",
    "notification_url": "https://lucianavenanciopsipp.com.br",
  "installments": 1,
  "payer": {
    "email": "' . $_POST['<EMAIL>'] . '",
    "identification": {
      "type": "' . $_POST['<IDENTIFICATION_TYPE'] . '",
      "number": "' . $_POST['<NUMBER>'] . '"
    }
  },
  "issuer_id": "' . $_POST['<ISSUER>'] . '",
  "payment_method_id": "' . $_POST['<PAYMENT_METHOD_ID'] . '",
  "token": "' . $_POST['<TOKEN>'] . '",
  "transaction_amount": ' . $amount . '
}',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $accesstoken
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        echo $response;

    }
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

    <div class="card_page_payment">
        <div id="statusScreenBrick_container"></div>
        <div id="paymentBrick_container"></div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="../js/card.js"></script>
</body>

</html>