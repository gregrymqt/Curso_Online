<?php
session_start();
require_once 'includes/db_connect.php';
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
        $query = $this->conn->prepare("INSERT Into payment (user_id, valor) values(:valor, :user_id) ");
        $query->bindValue(':valor', $valor);
        $query->bindvalue(':user_id', $this->user_id);
        if ($query->execute()) {
            return $this->conn->lastInsertId();           
        } else {
            return false;
        }
    }
    public function updadtePayment($status)
    {

        $query = $this->conn->prepare("UPDATE  payment set status= :status where id_payment = :id ");
        $query->bindValue(':status', $status);
        $query->bindvalue(':payment_id', $this->payment_id);
        if ($query->execute()) {
            return true;
        } else {
            return false;
        }
    }
}