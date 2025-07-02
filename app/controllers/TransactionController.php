<?php
class TransactionController {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function addTransaction($userId, $data) {
        // Validation
        if(empty($data['category_id']) || empty($data['amount']) || empty($data['date'])) {
            return ['success' => false, 'message' => 'Veuillez remplir tous les champs obligatoires'];
        }
        
        if(!is_numeric($data['amount']) || $data['amount'] <= 0) {
            return ['success' => false, 'message' => 'Le montant doit être un nombre positif'];
        }
        
        // Vérifier que la catégorie appartient bien à l'utilisateur
        $this->db->query('SELECT id FROM categories WHERE id = :category_id AND user_id = :user_id');
        $this->db->bind(':category_id', $data['category_id']);
        $this->db->bind(':user_id', $userId);
        $category = $this->db->single();
        
        if(!$category) {
            return ['success' => false, 'message' => 'Catégorie invalide'];
        }
        
        // Insérer la transaction
        $this->db->query('
            INSERT INTO transactions (user_id, category_id, amount, description, date) 
            VALUES (:user_id, :category_id, :amount, :description, :date)
        ');
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':category_id', $data['category_id']);
        $this->db->bind(':amount', $data['amount']);
        $this->db->bind(':description', $data['description'] ?? null);
        $this->db->bind(':date', $data['date']);
        
        if($this->db->execute()) {
            // Vérifier les notifications de dépenses inhabituelles
            $this->checkUnusualSpending($userId, $data['category_id'], $data['amount']);
            
            return ['success' => true, 'message' => 'Transaction ajoutée avec succès'];
        } else {
            return ['success' => false, 'message' => 'Erreur lors de l\'ajout de la transaction'];
        }
    }
    
    public function getTransactions($userId, $filters = []) {
        $query = '
            SELECT t.id, t.amount, t.description, t.date, t.created_at, 
                   c.id AS category_id, c.name AS category_name, c.type AS category_type, c.color, c.icon
            FROM transactions t
            JOIN categories c ON t.category_id = c.id
            WHERE t.user_id = :user_id
        ';
        
        // Appliquer les filtres
        $params = [':user_id' => $userId];
        
        if(!empty($filters['type'])) {
            $query .= ' AND c.type = :type';
            $params[':type'] = $filters['type'];
        }
        
        if(!empty($filters['category_id'])) {
            $query .= ' AND c.id = :category_id';
            $params[':category_id'] = $filters['category_id'];
        }
        
        if(!empty($filters['month'])) {
            $query .= ' AND DATE_FORMAT(t.date, "%Y-%m") = :month';
            $params[':month'] = $filters['month'];
        }
        
        if(!empty($filters['search'])) {
            $query .= ' AND (t.description LIKE :search OR c.name LIKE :search)';
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        
        // Tri
        $orderBy = 't.date DESC, t.created_at DESC';
        if(!empty($filters['sort'])) {
            switch($filters['sort']) {
                case 'amount_asc':
                    $orderBy = 't.amount ASC';
                    break;
                case 'amount_desc':
                    $orderBy = 't.amount DESC';
                    break;
                case 'date_asc':
                    $orderBy = 't.date ASC';
                    break;
                case 'category':
                    $orderBy = 'c.name ASC';
                    break;
            }
        }
        
        $query .= ' ORDER BY ' . $orderBy;
        
        // Limite pour la pagination
        if(!empty($filters['limit'])) {
            $query .= ' LIMIT :limit';
            $params[':limit'] = (int)$filters['limit'];
            
            if(!empty($filters['offset'])) {
                $query .= ' OFFSET :offset';
                $params[':offset'] = (int)$filters['offset'];
            }
        }
        
        $this->db->query($query);
        
        foreach($params as $key => $value) {
            $this->db->bind($key, $value);
        }
        
        return $this->db->resultSet();
    }
    
    public function updateTransaction($userId, $transactionId, $data) {
        // Vérifier que la transaction appartient à l'utilisateur
        $this->db->query('SELECT id FROM transactions WHERE id = :id AND user_id = :user_id');
        $this->db->bind(':id', $transactionId);
        $this->db->bind(':user_id', $userId);
        $transaction = $this->db->single();
        
        if(!$transaction) {
            return ['success' => false, 'message' => 'Transaction non trouvée'];
        }
        
        // Validation
        if(empty($data['category_id']) || empty($data['amount']) || empty($data['date'])) {
            return ['success' => false, 'message' => 'Veuillez remplir tous les champs obligatoires'];
        }
        
        if(!is_numeric($data['amount']) || $data['amount'] <= 0) {
            return ['success' => false, 'message' => 'Le montant doit être un nombre positif'];
        }
        
        // Vérifier que la catégorie appartient bien à l'utilisateur
        $this->db->query('SELECT id FROM categories WHERE id = :category_id AND user_id = :user_id');
        $this->db->bind(':category_id', $data['category_id']);
        $this->db->bind(':user_id', $userId);
        $category = $this->db->single();
        
        if(!$category) {
            return ['success' => false, 'message' => 'Catégorie invalide'];
        }
        
        // Mettre à jour la transaction
        $this->db->query('
            UPDATE transactions 
            SET category_id = :category_id, 
                amount = :amount, 
                description = :description, 
                date = :date 
            WHERE id = :id AND user_id = :user_id
        ');
        $this->db->bind(':id', $transactionId);
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':category_id', $data['category_id']);
        $this->db->bind(':amount', $data['amount']);
        $this->db->bind(':description', $data['description'] ?? null);
        $this->db->bind(':date', $data['date']);
        
        if($this->db->execute()) {
            return ['success' => true, 'message' => 'Transaction mise à jour avec succès'];
        } else {
            return ['success' => false, 'message' => 'Erreur lors de la mise à jour de la transaction'];
        }
    }
    
    public function deleteTransaction($userId, $transactionId) {
        // Vérifier que la transaction appartient à l'utilisateur
        $this->db->query('SELECT id FROM transactions WHERE id = :id AND user_id = :user_id');
        $this->db->bind(':id', $transactionId);
        $this->db->bind(':user_id', $userId);
        $transaction = $this->db->single();
        
        if(!$transaction) {
            return ['success' => false, 'message' => 'Transaction non trouvée'];
        }
        
        // Supprimer la transaction
        $this->db->query('DELETE FROM transactions WHERE id = :id AND user_id = :user_id');
        $this->db->bind(':id', $transactionId);
        $this->db->bind(':user_id', $userId);
        
        if($this->db->execute()) {
            return ['success' => true, 'message' => 'Transaction supprimée avec succès'];
        } else {
            return ['success' => false, 'message' => 'Erreur lors de la suppression de la transaction'];
        }
    }
    
    private function checkUnusualSpending($userId, $categoryId, $amount) {
        // Vérifier si la dépense est inhabituelle par rapport aux habitudes
        // 1. Comparer avec la moyenne des dépenses dans cette catégorie
        $this->db->query('
            SELECT AVG(amount) AS avg_amount, MAX(amount) AS max_amount 
            FROM transactions 
            WHERE user_id = :user_id AND category_id = :category_id
        ');
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':category_id', $categoryId);
        $stats = $this->db->single();
        
        if($stats && $stats->avg_amount > 0) {
            $threshold = $stats->avg_amount * 2; // 2x la moyenne
            $maxThreshold = $stats->max_amount * 1.5; // 1.5x le maximum historique
            
            if($amount > $threshold || $amount > $maxThreshold) {
                // Enregistrer une notification de dépense inhabituelle
                $this->addUnusualSpendingNotification($userId, $categoryId, $amount);
            }
        }
    }
    
    private function addUnusualSpendingNotification($userId, $categoryId, $amount) {
        // Récupérer le nom de la catégorie
        $this->db->query('SELECT name FROM categories WHERE id = :category_id');
        $this->db->bind(':category_id', $categoryId);
        $category = $this->db->single();
        
        if($category) {
            // Enregistrer la notification (implémentation simplifiée)
            // Dans une vraie application, vous auriez une table notifications
            $_SESSION['unusual_spending'] = [
                'category' => $category->name,
                'amount' => $amount
            ];
        }
    }
}
?>