<?php
session_start();

// 1. Configuração de segurança CSRF
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 1. Carregar configurações e dependências
require_once __DIR__ . '/../config.php';
require_once 'C:/xampp/htdocs/Curso_Online/includes/db_connect.php';
require_once 'C:/xampp/htdocs/Curso_Online/Processa_Api/class/payment.class.php';
use MercadoPago\MercadoPagoConfig;

// 2. Verificar método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  die(json_encode(['error' => 'Método não permitido']));
}

// 3. Obter e validar dados da requisição
$body = json_decode(file_get_contents("php://input"));
if (json_last_error() !== JSON_ERROR_NONE) {
  http_response_code(400);
  die(json_encode(['error' => 'JSON inválido']));
}

if (!isset($body->csrf_token) || $body->csrf_token !== $_SESSION['csrf_token']) {
  http_response_code(403);
  die(json_encode(['error' => 'Token CSRF inválido']));
}

if (!isset($body->token)) {
  try {
    // Validar sessão do usuário
    if (!isset($_SESSION['mercado_pago_data']['user_id'])) {
      throw new Exception('Usuário não autenticado');
    }
    $user_id = $_SESSION['mercado_pago_data']['user_id'];
    $amount = isset($body->amount) ? (float) $body->amount : 100.00;

    // Criar e processar preferência
    $preferences = new Preferences($user_id);
    $result = $preferences->createPreference($amount);

    // Retornar resposta
    echo json_encode($result);
  } catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
      'success' => false,
      'error' => $e->getMessage()
    ]);
  }
}

class Preferences extends PaymentHandler
{
  private $config;
  private $baseUrl = 'https://lucianavenanciopsipp.com.br';

  public function __construct($user_id = null)
  {
    parent::__construct($user_id);
    $this->config = require('../config.php');
    MercadoPagoConfig::setAccessToken($this->config['accesstoken']);
  }
  /**
   * Implementação obrigatória da classe abstrata (não usada para preferências)
   */
  public function createPayment($amount, $payer_data)
  {
    throw new Exception("Método não utilizado para preferências");
  }
  /**
   * Implementação obrigatória da classe abstrata (não usada para preferências)
   */
  protected function preparePaymentData($amount, $payer_data)
  {
    throw new Exception("Método não utilizado para preferências");
  }
  /**
   * Cria uma preferência de pagamento no Mercado Pago
   * 
   * @param float $amount Valor do pagamento
   * @param array $options Opções adicionais
   * @return array Resposta formatada
   * @throws Exception
   */
  public function createPreference($amount, $options = []) {
    $this->validateUser();

    try {
        // 1. Criar registro inicial no banco de dados
        $payment_id = $this->createPaymentRecord($amount);
        
        // 2. Configurar dados da preferência
        $preference_data = array_merge([
            'back_urls' => [
                'success' => $this->baseUrl . '/success',
                'pending' => $this->baseUrl . '/pending',
                'failure' => $this->baseUrl . '/failure'
            ],
            'external_reference' => $payment_id,
            'notification_url' => $this->baseUrl . '/webhook',
            'auto_return' => 'approved',
            'items' => [
                [
                    'title' => 'Curso Online',
                    'description' => 'Pagamento do curso',
                    'quantity' => 1,
                    'currency_id' => 'BRL',
                    'unit_price' => (float)$amount
                ]
            ],
            'payment_methods' => [
                'excluded_payment_methods' => [
                    ['id' => 'pix']
                ],
                'excluded_payment_types' => [
                    ['id' => 'ticket']
                ]
            ]
        ], $options);

        // 3. Criar preferência na API do Mercado Pago
        $preference = $this->createMercadoPagoPreference($preference_data);

        // 5. Retornar resposta formatada
        return [
            'success' => true,
            'preference_id' => $preference->id,
            'payment_id' => $payment_id,
            'init_point' => $preference->init_point ?? null,
            'sandbox_init_point' => $preference->sandbox_init_point ?? null
        ];

    } catch (Exception $e) {
        throw new Exception("Erro ao criar preferência: " . $e->getMessage());
    }
}

private function createPaymentRecord($amount) {
    $query = $this->conn->prepare(
        "INSERT INTO pagamentos 
        (usuario_id, valor, status, metodo) 
        VALUES (:user_id, :valor, 'pending', 'checkout')"
    );
    
    $query->execute([
        ':user_id' => $this->user_id,
        ':valor' => $amount
    ]);
    
    return $this->conn->lastInsertId();
}
  /**
   * Comunicação com a API do Mercado Pago
   */
  private function createMercadoPagoPreference($data)
  {
    $ch = curl_init();

    curl_setopt_array($ch, [
      CURLOPT_URL => 'https://api.mercadopago.com/checkout/preferences',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => json_encode($data),
      CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $this->config['accesstoken']
      ]
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $response_data = json_decode($response);

    if ($http_code !== 200) {
      throw new Exception($response_data->message ?? "Erro ao criar preferência");
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
    <title>Checkout de Pagamento</title>
  
</head>
<body>
    <div class="container">
        <h1>Finalize seu Pagamento</h1>
        
        <div class="brick-container">
            <h2>Métodos de Pagamento</h2>
            <div id="paymentBrick_container"></div>
        </div>
        
        <div id="paymentLinkContainer">
            <h3>Ou pague pelo link:</h3>
            <a id="paymentLink" href="#" target="_blank"></a>
        </div>
        
        <div class="brick-container">
            <button id="toggleStatusBrick" class="toggle-brick">Ver Status do Pagamento</button>
            <div id="statusScreenBrick_container" style="display: none;"></div>
        </div>
    </div>

<script src="https://sdk.mercadopago.com/js/v2"></script>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="../js/preferences.js"></script>
    </body>
</html>