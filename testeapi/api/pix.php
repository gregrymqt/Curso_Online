<?php
session_start();
$config = require_once('../config.php');
require_once('../class/conn.class.php');
if (!isset($_SESSION['valor_passado'])) {
  echo "O valor não existe!";
  die;
} else {
  if (empty($_SESSION['valor_passado']) || !is_numeric($_SESSION['valor_passado'])) {
    die("O valor não poder ser vazio e tem que ser numerico! ");
  } else {
    if ($_SESSION['valor_passado'] < 1) {
      die("O valor não pode ser menor que 1 ");
    }
  }
}

$accestoken = $config['accestoken'];
$amount = (float) trim($_SESSION['valor_passado']);

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
  "external_reference": "MP0001", 
  "notification_url": "https://lucianavenanciopsipp.com.br",
  "payer": {
    "email": "test_user_123@testuser.com",
    "identification": {
      "type": "CPF",
      "number": "95749019047"
    }
  },
  "payment_method_id": "pix",
  "transaction_amount": '.$amount.'
}',
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json',
    'Authorization: Bearer ' . $accestoken
  ),
));

$response = curl_exec($curl);

curl_close($curl);

$obj = json_decode($response);
if (isset($obj->id)) {
  if ($obj->id != null) {
    $copia_cola = $obj->point_of_interaction->transaction_data->qr_code;
    $img_qrcode = $obj->point_of_interaction->transaction_data->qr_code_base64;
    $link_externo = $obj->point_of_interaction->transaction_data->ticket_url;
    $transaction_amount = $obj->transaction_amount;

    echo "<h3>{$transaction_amount}</h3> <br />";
    echo "<img src='data:image/png;base64,{$img_qrcode}' width='200' /><br>";
    echo "<textarea>{$copia_cola}</textarea><br>";
    echo "<a href='{$link_externo}' target='_blank'>Link Externo</a>";
  }
}
