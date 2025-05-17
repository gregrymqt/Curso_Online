
<?php
require 'vendor/autoload.php';
require_once 'C:/xampp/htdocs/Curso_Online/includes/db_connect.php';

use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\MercadoPagoConfig;

abstract class PaymentHandler {
    protected $user_id;
    protected $payment_id;
    protected $conn;
    protected $paymentClient;
    
    // Configuração do SDK
    public function __construct($user_id = null) {
        MercadoPagoConfig::setAccessToken("APP_USR-684965893709349-051020-d7bbb2b795616739aa11e087c8171dc6-527496844");
        
        $this->user_id = $user_id;
        $this->conn = Conexao::getConnection();
        $this->paymentClient = new PaymentClient();
    }
    
    // Métodos abstratos que devem ser implementados
    abstract public function createPayment($amount, $payer_data);
    abstract protected function preparePaymentData($amount, $payer_data);
    
    // Métodos comuns a todos os tipos de pagamento
    public function getPayment() {
        $query = $this->conn->prepare("SELECT * FROM payment WHERE payment_id = :id");
        $query->bindValue(':id', $this->payment_id);
        
        if ($query->execute()) {
            $row = $query->fetchAll(PDO::FETCH_OBJ);
            return (count($row) > 0) ? $row[0] : false;
        }
        return false;
    }
    
    public function verifyPayment($payment_id) {
        try {
            $payment = $this->paymentClient->get($payment_id);
            
            if ($payment->status === 'approved') {
                $this->updatePaymentStatus($payment_id, 'approved');
                return true;
            }
            
            return false;
            
        } catch (Exception $e) {
            throw new Exception("Erro ao verificar pagamento: " . $e->getMessage());
        }
    }
    
    protected function updatePaymentStatus($payment_id, $status) {
        $stmt = $this->conn->prepare(
            "UPDATE pagamentos SET status = :status 
             WHERE payment_id = :payment_id"
        );
        
        $stmt->execute([
            ':status' => $status,
            ':payment_id' => $payment_id
        ]);
        
        if ($stmt->rowCount() === 0) {
            throw new Exception("Nenhum pagamento atualizado. Verifique o ID.");
        }
    }
    
    protected function savePaymentToDatabase($payment_data, $payment_result) {
        $query = $this->conn->prepare(
            "INSERT INTO pagamentos 
            (payment_id, usuario_id, valor, status, metodo, parcelas, dados_pagador) 
            VALUES (:mp_id, :user_id, :valor, :status, :metodo, :parcelas, :pagador)"
        );
        
        $query->execute([
            ':mp_id' => $payment_result->id,
            ':user_id' => $this->user_id,
            ':valor' => $payment_data['transaction_amount'],
            ':status' => $payment_result->status,
            ':metodo' => $payment_result->payment_method_id,
            ':parcelas' => $payment_result->installments ?? null,
            ':pagador' => json_encode([
                'email' => $payment_result->payer->email,
                'nome' => $payment_result->payer->first_name,
                'documento' => $payment_result->payer->identification->number
            ])
        ]);
        
        $this->payment_id = $payment_result->id;
    }
    
    protected function validateUser() {
        if (empty($this->user_id)) {
            throw new Exception("ID do usuário não fornecido");
        }

        $checkUser = $this->conn->prepare("SELECT usuario_id FROM usuarios WHERE usuario_id = ?");
        $checkUser->execute([$this->user_id]);
        
        if ($checkUser->rowCount() === 0) {
            throw new Exception("Usuário não encontrado com ID: " . $this->user_id);
        }
    }
}
