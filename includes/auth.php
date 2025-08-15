<?php
require_once 'database.php';

class Auth {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function register($nome, $email, $senha) {
        if ($this->emailExists($email)) {
            return ['success' => false, 'message' => 'Email já cadastrado'];
        }

        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)";
        $this->db->query($sql, [$nome, $email, $senhaHash]);
        
        return ['success' => true, 'message' => 'Usuário cadastrado com sucesso'];
    }

    public function login($email, $senha) {
        $sql = "SELECT * FROM usuarios WHERE email = ? AND ativo = 1";
        $usuario = $this->db->fetch($sql, [$email]);
        
        if (!$usuario || !password_verify($senha, $usuario['senha'])) {
            return ['success' => false, 'message' => 'Email ou senha inválidos'];
        }

        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        $_SESSION['usuario_email'] = $usuario['email'];
        $_SESSION['usuario_tipo'] = $usuario['tipo'];
        
        return ['success' => true, 'message' => 'Login realizado com sucesso'];
    }

    public function logout() {
        session_destroy();
        return ['success' => true, 'message' => 'Logout realizado com sucesso'];
    }

    public function isLoggedIn() {
        return isset($_SESSION['usuario_id']);
    }

    public function isAdmin() {
        return $this->isLoggedIn() && $_SESSION['usuario_tipo'] === 'admin';
    }

    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        $sql = "SELECT id, nome, email, tipo FROM usuarios WHERE id = ?";
        return $this->db->fetch($sql, [$_SESSION['usuario_id']]);
    }

    private function emailExists($email) {
        $sql = "SELECT COUNT(*) as count FROM usuarios WHERE email = ?";
        $result = $this->db->fetch($sql, [$email]);
        return $result['count'] > 0;
    }

    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: login.php');
            exit;
        }
    }

    public function requireAdmin() {
        $this->requireLogin();
        if (!$this->isAdmin()) {
            header('Location: index.php');
            exit;
        }
    }
}
?>
