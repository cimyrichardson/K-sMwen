<?php
class AuthController {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function register($data) {
        // Validation
        if(empty($data['username']) || empty($data['email']) || empty($data['password']) || empty($data['confirm_password'])) {
            return ['success' => false, 'message' => 'Veuillez remplir tous les champs'];
        }
        
        if($data['password'] !== $data['confirm_password']) {
            return ['success' => false, 'message' => 'Les mots de passe ne correspondent pas'];
        }
        
        if(strlen($data['password']) < 6) {
            return ['success' => false, 'message' => 'Le mot de passe doit contenir au moins 6 caractères'];
        }
        
        // Vérifier si l'email existe déjà
        $this->db->query('SELECT id FROM users WHERE email = :email');
        $this->db->bind(':email', $data['email']);
        $this->db->execute();
        
        if($this->db->rowCount() > 0) {
            return ['success' => false, 'message' => 'Cet email est déjà utilisé'];
        }
        
        // Hasher le mot de passe
        $hashedPassword = password_hash($data['password'] . PEPPER, PASSWORD_DEFAULT);
        
        // Insérer l'utilisateur
        $this->db->query('INSERT INTO users (username, email, password) VALUES (:username, :email, :password)');
        $this->db->bind(':username', trim($data['username']));
        $this->db->bind(':email', trim($data['email']));
        $this->db->bind(':password', $hashedPassword);
        
        if($this->db->execute()) {
            // Créer des catégories par défaut
            $userId = $this->db->lastInsertId();
            $this->createDefaultCategories($userId);
            
            // Attribuer le badge Débutant
            $this->assignBadge($userId, 3); // ID 3 = badge Débutant
            
            return ['success' => true, 'message' => 'Inscription réussie! Vous pouvez maintenant vous connecter.'];
        } else {
            return ['success' => false, 'message' => 'Une erreur est survenue lors de l\'inscription'];
        }
    }
    
    public function login($email, $password) {
        // Trouver l'utilisateur par email
        $this->db->query('SELECT * FROM users WHERE email = :email');
        $this->db->bind(':email', $email);
        $user = $this->db->single();
        
        if($user) {
            // Vérifier le mot de passe
            if(password_verify($password . PEPPER, $user->password)) {
                // Mettre à jour la session
                $_SESSION['user_id'] = $user->id;
                $_SESSION['user_email'] = $user->email;
                $_SESSION['user_username'] = $user->username;
                $_SESSION['user_language'] = $user->language;
                $_SESSION['dark_mode'] = $user->dark_mode;
                
                // Vérifier les badges (streak, etc.)
                $this->checkLoginBadges($user->id);
                
                return ['success' => true, 'message' => 'Connexion réussie!'];
            }
        }
        
        return ['success' => false, 'message' => 'Email ou mot de passe incorrect'];
    }
    
    private function createDefaultCategories($userId) {
        $defaultCategories = [
            ['name' => 'Salaire', 'type' => 'income', 'color' => '#2ecc71', 'icon' => 'fa-money-bill-wave'],
            ['name' => 'Cadeau', 'type' => 'income', 'color' => '#27ae60', 'icon' => 'fa-gift'],
            ['name' => 'Nourriture', 'type' => 'expense', 'color' => '#e74c3c', 'icon' => 'fa-utensils'],
            ['name' => 'Transport', 'type' => 'expense', 'color' => '#3498db', 'icon' => 'fa-bus'],
            ['name' => 'Loisirs', 'type' => 'expense', 'color' => '#9b59b6', 'icon' => 'fa-gamepad'],
            ['name' => 'Éducation', 'type' => 'expense', 'color' => '#1abc9c', 'icon' => 'fa-book'],
            ['name' => 'Santé', 'type' => 'expense', 'color' => '#e67e22', 'icon' => 'fa-heartbeat']
        ];
        
        foreach($defaultCategories as $category) {
            $this->db->query('INSERT INTO categories (user_id, name, type, color, icon) VALUES (:user_id, :name, :type, :color, :icon)');
            $this->db->bind(':user_id', $userId);
            $this->db->bind(':name', $category['name']);
            $this->db->bind(':type', $category['type']);
            $this->db->bind(':color', $category['color']);
            $this->db->bind(':icon', $category['icon']);
            $this->db->execute();
        }
    }
    
    private function assignBadge($userId, $badgeId) {
        // Vérifier si l'utilisateur a déjà ce badge
        $this->db->query('SELECT id FROM user_badges WHERE user_id = :user_id AND badge_id = :badge_id');
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':badge_id', $badgeId);
        $this->db->execute();
        
        if($this->db->rowCount() === 0) {
            // Attribuer le badge
            $this->db->query('INSERT INTO user_badges (user_id, badge_id) VALUES (:user_id, :badge_id)');
            $this->db->bind(':user_id', $userId);
            $this->db->bind(':badge_id', $badgeId);
            return $this->db->execute();
        }
        
        return false;
    }
    
    private function checkLoginBadges($userId) {
        // Vérifier le streak de connexion
        // (Implémentation simplifiée - à compléter avec un système de suivi des connexions)
        $this->assignBadge($userId, 4); // ID 4 = badge Régulier (simplifié)
    }
}
?>