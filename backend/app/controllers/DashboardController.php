<?php
class DashboardController {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function getDashboardData($userId) {
        $data = [];
        
        // Solde actuel
        $data['balance'] = $this->getCurrentBalance($userId);
        
        // Revenus et dépenses du mois
        $currentMonth = date('Y-m');
        $data['monthly_income'] = $this->getMonthlyTotal($userId, 'income', $currentMonth);
        $data['monthly_expenses'] = $this->getMonthlyTotal($userId, 'expense', $currentMonth);
        
        // Dépenses par catégorie (pour le camembert)
        $data['expenses_by_category'] = $this->getExpensesByCategory($userId, $currentMonth);
        
        // Évolution du solde sur 6 mois (pour le graphique)
        $data['balance_history'] = $this->getBalanceHistory($userId, 6);
        
        // Badges de l'utilisateur
        $data['badges'] = $this->getUserBadges($userId);
        
        // Vérifier et attribuer les badges automatiques
        $this->checkAutomaticBadges($userId, $data['monthly_income'], $data['monthly_expenses']);
        
        // Top 10 des épargnants
        $data['top_savers'] = $this->getTopSavers();
        
        return $data;
    }
    
    private function getCurrentBalance($userId) {
        $this->db->query('
            SELECT 
                (SELECT IFNULL(SUM(amount), 0) FROM transactions t 
                JOIN categories c ON t.category_id = c.id 
                WHERE t.user_id = :user_id AND c.type = "income") -
                (SELECT IFNULL(SUM(amount), 0) FROM transactions t 
                JOIN categories c ON t.category_id = c.id 
                WHERE t.user_id = :user_id AND c.type = "expense") AS balance
        ');
        $this->db->bind(':user_id', $userId);
        $result = $this->db->single();
        return $result ? $result->balance : 0;
    }
    
    private function getMonthlyTotal($userId, $type, $month) {
        $this->db->query('
            SELECT IFNULL(SUM(t.amount), 0) AS total 
            FROM transactions t 
            JOIN categories c ON t.category_id = c.id 
            WHERE t.user_id = :user_id 
            AND c.type = :type 
            AND DATE_FORMAT(t.date, "%Y-%m") = :month
        ');
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':type', $type);
        $this->db->bind(':month', $month);
        $result = $this->db->single();
        return $result ? $result->total : 0;
    }
    
    private function getExpensesByCategory($userId, $month) {
        $this->db->query('
            SELECT c.id, c.name, c.color, c.icon, IFNULL(SUM(t.amount), 0) AS total 
            FROM categories c 
            LEFT JOIN transactions t ON c.id = t.category_id 
            AND t.user_id = :user_id 
            AND DATE_FORMAT(t.date, "%Y-%m") = :month
            WHERE c.user_id = :user_id 
            AND c.type = "expense"
            GROUP BY c.id
            ORDER BY total DESC
        ');
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':month', $month);
        return $this->db->resultSet();
    }
    
    private function getBalanceHistory($userId, $months) {
        $history = [];
        $currentDate = new DateTime();
        
        for($i = $months - 1; $i >= 0; $i--) {
            $date = clone $currentDate;
            $date->modify("-$i months");
            $monthYear = $date->format('Y-m');
            
            $this->db->query('
                SELECT 
                    (SELECT IFNULL(SUM(amount), 0) FROM transactions t 
                    JOIN categories c ON t.category_id = c.id 
                    WHERE t.user_id = :user_id AND c.type = "income" AND DATE_FORMAT(t.date, "%Y-%m") <= :month_year) -
                    (SELECT IFNULL(SUM(amount), 0) FROM transactions t 
                    JOIN categories c ON t.category_id = c.id 
                    WHERE t.user_id = :user_id AND c.type = "expense" AND DATE_FORMAT(t.date, "%Y-%m") <= :month_year) AS balance
            ');
            $this->db->bind(':user_id', $userId);
            $this->db->bind(':month_year', $monthYear);
            $result = $this->db->single();
            
            $history[] = [
                'month' => $date->format('M Y'),
                'balance' => $result ? $result->balance : 0
            ];
        }
        
        return $history;
    }
    
    private function getUserBadges($userId) {
        $this->db->query('
            SELECT b.id, b.name, b.description, b.icon, b.color, ub.earned_at 
            FROM badges b 
            JOIN user_badges ub ON b.id = ub.badge_id 
            WHERE ub.user_id = :user_id 
            ORDER BY ub.earned_at DESC
        ');
        $this->db->bind(':user_id', $userId);
        return $this->db->resultSet();
    }
    
    private function checkAutomaticBadges($userId, $income, $expenses) {
        if($income > 0) {
            // Badge Épargnant (épargne > 20% des revenus)
            $savingsPercentage = (($income - $expenses) / $income) * 100;
            if($savingsPercentage >= 20) {
                $this->assignBadge($userId, 1); // ID 1 = badge Épargnant
            }
            
            // Badge Économe (dépenses < 50% des revenus)
            $expensePercentage = ($expenses / $income) * 100;
            if($expensePercentage <= 50) {
                $this->assignBadge($userId, 2); // ID 2 = badge Économe
            }
        }
    }
    
    private function getTopSavers() {
        $this->db->query('
            SELECT 
                u.id, u.username, 
                (SELECT IFNULL(SUM(amount), 0) FROM transactions t 
                JOIN categories c ON t.category_id = c.id 
                WHERE t.user_id = u.id AND c.type = "income" AND DATE_FORMAT(t.date, "%Y-%m") = DATE_FORMAT(NOW(), "%Y-%m")) -
                (SELECT IFNULL(SUM(amount), 0) FROM transactions t 
                JOIN categories c ON t.category_id = c.id 
                WHERE t.user_id = u.id AND c.type = "expense" AND DATE_FORMAT(t.date, "%Y-%m") = DATE_FORMAT(NOW(), "%Y-%m")) AS savings
            FROM users u
            ORDER BY savings DESC
            LIMIT 10
        ');
        return $this->db->resultSet();
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
            $this->db->execute();
            
            // Ajouter une notification
            $this->addBadgeNotification($userId, $badgeId);
        }
    }
    
    private function addBadgeNotification($userId, $badgeId) {
        // Récupérer les infos du badge
        $this->db->query('SELECT name, icon, color FROM badges WHERE id = :badge_id');
        $this->db->bind(':badge_id', $badgeId);
        $badge = $this->db->single();
        
        if($badge) {
            // Enregistrer la notification (implémentation simplifiée)
            // Dans une vraie application, vous auriez une table notifications
            $_SESSION['new_badge'] = [
                'name' => $badge->name,
                'icon' => $badge->icon,
                'color' => $badge->color
            ];
        }
    }
}
?>