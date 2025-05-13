<?php
session_start();
$config = require_once('../config.php');
require_once 'C:/xampp/htdocs/Curso_Online/includes/db_connect.php';
require_once 'C:/xampp/htdocs/Curso_Online/Processa_Api/class/payment.class.php';

if (!isset($_SESSION['mercado_pago_data']['produto_preco'])) {
  echo "O valor não existe!";
  die;
} else {
  if (empty($_SESSION['mercado_pago_data']['produto_preco']) || !is_numeric($_SESSION['mercado_pago_data']['produto_preco'])) {
    die("O valor não poder ser vazio e tem que ser numerico! ");
  } else {
    if ($_SESSION['mercado_pago_data']['produto_preco'] < 1) {
      die("O valor não pode ser menor que 1 ");
    }
  }
}

$amount = (float) trim($_SESSION['mercado_pago_data']['produto_preco']);
$email= $_SESSION['mercado_pago_data']['email'];
$user_id= $_SESSION['mercado_pago_data']['user_id'];
$payment = new payment($user_id);

$payCreate = $payment->addPayment($amount);

if ($payCreate) {

  $accesstoken = $config['accesstoken'];

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
  CURLOPT_POSTFIELDS =>'{
  "description": "Payment for product",
  "external_reference":  "'.$payCreate.'", 
  "notification_url": "https://google.com",
  "payer": {
    "email": "test_user_123@testuser.com",
    "identification": {
      "type": "CPF",
      "number": "95749019047"
    }
  },
  "payment_method_id": "pix",
  "transaction_amount": ' . $amount . '
}',
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json',
    'X-Idempotency-Key: 0d5020ed-1af6-469c-ae06-c3bec19954bb',
    'Authorization: Bearer ' .$accesstoken
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
      $external_reference = $obj->external_reference;

      echo "<h3>{$transaction_amount} #{$external_reference}</h3> <br />";
      echo "<img src='data:image/png;base64,{$img_qrcode}' width='200' /><br>";
      echo "<textarea>{$copia_cola}</textarea><br>";
      echo "<a href='{$link_externo}' target='_blank'>Link Externo</a>";
    }
  }
}

