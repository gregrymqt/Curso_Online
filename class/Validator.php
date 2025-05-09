<?php
class Validator {
    private $errors = [];
    
    public function validateName($name) {
        $name = trim($name);
        if (empty($name)) {
            $this->errors['nome'] = "O nome é obrigatório.";
            return null;
        }
        
        if (strlen($name) < 3) {
            $this->errors['nome'] = "O nome deve ter pelo menos 3 caracteres.";
            return null;
        }
        
        if (!preg_match("/^[a-zA-ZÀ-ú\s]+$/", $name)) {
            $this->errors['nome'] = "O nome só pode conter letras e espaços.";
            return null;
        }
        
        return $name;
    }
    
    public function validateEmail($email) {
        $email = trim($email);
        if (empty($email)) {
            $this->errors['email'] = "O email é obrigatório.";
            return null;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors['email'] = "O email informado não é válido.";
            return null;
        }
        
        return $email;
    }
    
    public function validatePassword($password, $confirm_password) {
        if (empty($password)) {
            $this->errors['senha'] = "A senha é obrigatória.";
            return null;
        }
        
        if (strlen($password) < 6) {
            $this->errors['senha'] = "A senha deve ter pelo menos 6 caracteres.";
            return null;
        }
        
        if ($password !== $confirm_password) {
            $this->errors['confirmar_senha'] = "As senhas não coincidem.";
            return null;
        }
        
        return $password;
    }
    
    public function validatePhone($phone) {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        if (empty($phone)) {
            $this->errors['telefone'] = "O telefone é obrigatório.";
            return null;
        }
        
        if (strlen($phone) < 10 || strlen($phone) > 11) {
            $this->errors['telefone'] = "O telefone deve ter 10 ou 11 dígitos.";
            return null;
        }
        
        return $phone;
    }
    
    public function getErrors() {
        return $this->errors;
    }
}