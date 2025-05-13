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
$email = $_SESSION['mercado_pago_data']['email'];
$user_id = $_SESSION['mercado_pago_data']['user_id'];
$payment = new payment($user_id);

$payCreate = $payment->addPayment($amount);

if ($payCreate) {

    $accesstoken = $config['accesstoken'];

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.mercadopago.com/checkout/preferences',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => '{
    "back_urls": {
    "success": "https://lucianavenanciopsipp.com.br",
    "pending": "https://lucianavenanciopsipp.com.br",
    "failure": "https://lucianavenanciopsipp.com.br"
  },
  "external_reference": "'.$payCreate.'",
    "notification_url": "https://lucianavenanciopsipp.com.br",
      "auto_return": "approved",
  "items": [
    {
      "id": "Sound system",
      "title": "Dummy Title",
      "description": "Dummy description",
      "picture_url": "https://www.myapp.com/myimage.jpg",
      "category_id": "car_electronics",
      "quantity": 1,
      "currency_id": "BRL",
      "unit_price": '.$amount.'
    }
  ],
  "payment_methods": {
    "excluded_payment_methods": [
      {
        "id": "visa"
      }
    ],
    "excluded_payment_types": [
      {
        "id": "ticket"
      }
    ]
  },
  "metadata": null
}',
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Authorization: Bearer ' .$accesstoken
        ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    $obj = json_decode($response);
    if (isset($obj->id)) {
        if ($obj->id != null) {

          if(isset($card)){
             $preference_id= $obj->id;

          }else{
              $link_externo = $obj->init_point;
            $external_reference = $obj->external_reference;

            echo "<h3>{$amount} #{$external_reference}</h3> <br />";
            echo "<a href='{$link_externo}' target='_blank'>Link Externo</a>";

          }
      
        }
    }
}

