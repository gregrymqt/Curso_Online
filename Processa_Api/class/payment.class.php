<?php
require_once 'C:/xampp/htdocs/Curso_Online/includes/db_connect.php';
class payment
{
    private $user_id = null;
    public $payment_id = null;
    private $conn;
    public function __construct($user_id = null)
    {
        $this->user_id = $user_id;
        $this->conn = Conexao::getConnection();
    }
    public function get()
    {
        $query = $this->conn->prepare("SELECT * from payment 
        where payment_id = :id");
        $query->bindValue(':id', $this->payment_id);
        if ($query->execute()) {
            $row = $query->fetchAll(PDO::FETCH_OBJ);
            if (count($row) > 0) {
                return $row[0];
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    public function addPayment($valor)
    {
        // Primeiro verifique se user_id está definido e é válido
        if (empty($this->user_id)) {
            throw new Exception("ID do usuário não fornecido");
        }
        // Verifique se o usuário existe
        $checkUser = $this->conn->prepare("SELECT usuario_id FROM usuarios WHERE usuario_id = ?");
        $checkUser->execute([$this->user_id]);
        if ($checkUser->rowCount() === 0) {
            throw new Exception("Usuário não encontrado com ID: " . $this->user_id);
        }
        // Corrigindo a ordem dos parâmetros
        $query = $this->conn->prepare("INSERT INTO pagamentos (usuario_id, valor) VALUES (:user_id, :valor)");
        $query->bindValue(':user_id', $this->user_id, PDO::PARAM_INT);
        $query->bindValue(':valor', $valor);

        if ($query->execute()) {
            return $this->conn->lastInsertId();
        } else {
            // Para debug - mostre o erro específico
            $error = $query->errorInfo();
            throw new Exception("Erro ao criar pagamento: " . $error[2]);
        }
    }
    public function updadtePayment($status)
    {

        $query = $this->conn->prepare("UPDATE  pagamentos set status= :status where id = :id ");
        $query->bindValue(':status', $status);
        $query->bindvalue(':id', $this->payment_id);
        if ($query->execute()) {
            return true;
        } else {
            return false;
        }
    }
}