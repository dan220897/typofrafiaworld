<?php
// admin/classes/User.php

class User {
    private $conn;
    private $table_name = "users";
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Получить список пользователей с фильтрацией
    public function getUsers($filters = [], $limit = 50, $offset = 0) {
        $query = "SELECT u.*,
                        COUNT(DISTINCT o.id) as orders_count,
                        COALESCE(SUM(o.final_amount), 0) as total_spent,
                        MAX(o.created_at) as last_order_date,
                        (SELECT COUNT(*) FROM chats WHERE user_id = u.id AND status = 'active') as active_chats
                 FROM " . $this->table_name . " u
                 LEFT JOIN orders o ON u.id = o.user_id AND o.payment_status = 'paid'
                 WHERE 1=1";
        
        // Применяем фильтры
        if (!empty($filters['search'])) {
            $query .= " AND (u.name LIKE :search 
                           OR u.phone LIKE :search 
                           OR u.email LIKE :search
                           OR u.company_name LIKE :search
                           OR u.inn LIKE :search)";
        }
        
        if (!empty($filters['verified'])) {
            $query .= " AND u.is_verified = :verified";
        }
        
        if (!empty($filters['blocked'])) {
            $query .= " AND u.is_blocked = :blocked";
        }
        
        if (!empty($filters['date_from'])) {
            $query .= " AND u.created_at >= :date_from";
        }
        
        if (!empty($filters['date_to'])) {
            $query .= " AND u.created_at <= :date_to";
        }
        
        $query .= " GROUP BY u.id
                   ORDER BY u.created_at DESC
                   LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        
        // Привязываем параметры
        if (!empty($filters['search'])) {
            $search = "%{$filters['search']}%";
            $stmt->bindParam(":search", $search);
        }
        
        if (!empty($filters['verified'])) {
            $stmt->bindParam(":verified", $filters['verified']);
        }
        
        if (!empty($filters['blocked'])) {
            $stmt->bindParam(":blocked", $filters['blocked']);
        }
        
        if (!empty($filters['date_from'])) {
            $stmt->bindParam(":date_from", $filters['date_from']);
        }
        
        if (!empty($filters['date_to'])) {
            $stmt->bindParam(":date_to", $filters['date_to']);
        }
        
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    public function getActiveUsers() {
        $query = "SELECT u.id, u.name, u.phone, u.email, u.company_name, u.created_at,
                 (SELECT COUNT(*) FROM orders WHERE user_id = u.id) as orders_count
                 FROM " . $this->table_name . " u
                 WHERE u.is_blocked = 0
                 ORDER BY u.name ASC, u.phone ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Получить пользователя по номеру телефона
    public function getUserByPhone($phone) {
        // Нормализуем номер телефона - убираем все кроме цифр и знака +
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        
        $query = "SELECT * FROM " . $this->table_name . " WHERE phone = :phone LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":phone", $phone);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Получить пользователя по ID
    public function getUserById($user_id) {
        $query = "SELECT u.*,
                        COUNT(DISTINCT o.id) as orders_count,
                        COALESCE(SUM(o.final_amount), 0) as total_spent,
                        COALESCE(AVG(o.final_amount), 0) as avg_order_value,
                        MAX(o.created_at) as last_order_date,
                        (SELECT COUNT(*) FROM chats WHERE user_id = u.id) as total_chats,
                        (SELECT COUNT(*) FROM reviews WHERE user_id = u.id) as reviews_count
                 FROM " . $this->table_name . " u
                 LEFT JOIN orders o ON u.id = o.user_id AND o.payment_status = 'paid'
                 WHERE u.id = :user_id
                 GROUP BY u.id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    // Создать пользователя
    public function createUser($data) {
        // Нормализуем номер телефона
        $phone = preg_replace('/[^0-9+]/', '', $data['phone']);
        
        // Проверяем, существует ли уже пользователь с таким телефоном
        $existingUser = $this->getUserByPhone($phone);
        if ($existingUser) {
            return $existingUser['id'];
        }
        
        $query = "INSERT INTO " . $this->table_name . " 
                 (phone, email, name, company_name, inn, is_verified, created_at, updated_at) 
                 VALUES (:phone, :email, :name, :company_name, :inn, :is_verified, NOW(), NOW())";
        
        $stmt = $this->conn->prepare($query);
        
        // Обязательное поле
        $stmt->bindParam(":phone", $phone);
        
        // Необязательные поля
        $email = !empty($data['email']) ? $data['email'] : null;
        $name = !empty($data['name']) ? $data['name'] : null;
        $company_name = !empty($data['company_name']) ? $data['company_name'] : null;
        $inn = !empty($data['inn']) ? $data['inn'] : null;
        $is_verified = isset($data['is_verified']) ? $data['is_verified'] : 1;
        
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":company_name", $company_name);
        $stmt->bindParam(":inn", $inn);
        $stmt->bindParam(":is_verified", $is_verified);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        
        return false;
    }
    
    // Обновить пользователя
    public function updateUser($user_id, $data) {
        $allowed_fields = ['name', 'email', 'company_name', 'inn', 'is_verified', 'is_blocked'];
        $update_fields = [];
        $params = [':id' => $user_id];
        
        foreach ($allowed_fields as $field) {
            if (isset($data[$field])) {
                $update_fields[] = "$field = :$field";
                $params[":$field"] = $data[$field];
            }
        }
        
        if (empty($update_fields)) {
            return false;
        }
        
        $query = "UPDATE " . $this->table_name . " 
                 SET " . implode(', ', $update_fields) . ", updated_at = NOW()
                 WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        return $stmt->execute();
    }
    
    // Заблокировать/разблокировать пользователя
    public function toggleBlockUser($user_id, $block = true) {
        $query = "UPDATE " . $this->table_name . " 
                 SET is_blocked = :is_blocked 
                 WHERE id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(":is_blocked", $block ? 1 : 0);
        $stmt->bindParam(":user_id", $user_id);
        
        if ($stmt->execute()) {
            // Если блокируем, закрываем активные чаты
            if ($block) {
                $close_chats = "UPDATE chats SET status = 'closed' 
                               WHERE user_id = :user_id AND status = 'active'";
                $close_stmt = $this->conn->prepare($close_chats);
                $close_stmt->bindParam(":user_id", $user_id);
                $close_stmt->execute();
            }
            
            return true;
        }
        
        return false;
    }
    
    // Получить заказы пользователя
    public function getUserOrders($user_id, $limit = 10) {
        $query = "SELECT o.*,
                        (SELECT GROUP_CONCAT(s.name SEPARATOR ', ')
                         FROM order_items oi
                         JOIN services s ON oi.service_id = s.id
                         WHERE oi.order_id = o.id
                         LIMIT 3) as services_list
                 FROM orders o
                 WHERE o.user_id = :user_id
                 ORDER BY o.created_at DESC
                 LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    // Получить статистику пользователей
    public function getUsersStats($period = 'month') {
        $stats = [];
        
        // Определяем период
        switch ($period) {
            case 'today':
                $date_condition = "DATE(created_at) = CURDATE()";
                break;
            case 'week':
                $date_condition = "created_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
                break;
            case 'month':
                $date_condition = "created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
                break;
            case 'year':
                $date_condition = "created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
                break;
            default:
                $date_condition = "1=1";
        }
        
        // Общая статистика
        $query = "SELECT 
                    COUNT(*) as total_users,
                    COUNT(CASE WHEN is_verified = 1 THEN 1 END) as verified_users,
                    COUNT(CASE WHEN is_blocked = 1 THEN 1 END) as blocked_users,
                    COUNT(CASE WHEN {$date_condition} THEN 1 END) as new_users,
                    COUNT(CASE WHEN company_name IS NOT NULL THEN 1 END) as companies
                 FROM " . $this->table_name;
        
        $stmt = $this->conn->query($query);
        $stats = $stmt->fetch();
        
        // Топ клиентов по выручке
        $query = "SELECT u.*, 
                        COUNT(o.id) as orders_count,
                        SUM(o.final_amount) as total_revenue
                 FROM " . $this->table_name . " u
                 JOIN orders o ON u.id = o.user_id AND o.payment_status = 'paid'
                 GROUP BY u.id
                 ORDER BY total_revenue DESC
                 LIMIT 10";
        
        $stmt = $this->conn->query($query);
        $stats['top_customers'] = $stmt->fetchAll();
        
        // График регистраций
        $query = "SELECT DATE(created_at) as date, COUNT(*) as count
                 FROM " . $this->table_name . "
                 WHERE {$date_condition}
                 GROUP BY DATE(created_at)
                 ORDER BY date";
        
        $stmt = $this->conn->query($query);
        $stats['registrations_chart'] = $stmt->fetchAll();
        
        return $stats;
    }
    
    // Экспорт пользователей
    public function exportUsers($filters = [], $format = 'csv') {
        $users = $this->getUsers($filters, 10000, 0); // Большой лимит для экспорта
        
        if ($format == 'csv') {
            $output = "ID,Имя,Телефон,Email,Компания,ИНН,Верифицирован,Заказов,Потрачено,Регистрация\n";
            
            foreach ($users as $user) {
                $output .= sprintf(
                    "%d,%s,%s,%s,%s,%s,%s,%d,%.2f,%s\n",
                    $user['id'],
                    $user['name'] ?? '',
                    $user['phone'],
                    $user['email'] ?? '',
                    $user['company_name'] ?? '',
                    $user['inn'] ?? '',
                    $user['is_verified'] ? 'Да' : 'Нет',
                    $user['orders_count'],
                    $user['total_spent'],
                    $user['created_at']
                );
            }
            
            return $output;
        }
        
        // Можно добавить другие форматы (Excel, PDF)
        return false;
    }
    
    // Поиск пользователей
    public function searchUsers($search_term, $limit = 20) {
        $query = "SELECT u.*, 
                        COUNT(DISTINCT o.id) as orders_count
                 FROM " . $this->table_name . " u
                 LEFT JOIN orders o ON u.id = o.user_id
                 WHERE u.name LIKE :search 
                    OR u.phone LIKE :search 
                    OR u.email LIKE :search
                    OR u.company_name LIKE :search
                 GROUP BY u.id
                 ORDER BY u.created_at DESC
                 LIMIT :limit";
        
        $search_pattern = "%{$search_term}%";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":search", $search_pattern);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    // Проверка существования пользователя
    public function userExists($phone = null, $email = null) {
        $conditions = [];
        $params = [];
        
        if ($phone) {
            $conditions[] = "phone = :phone";
            $params[':phone'] = $phone;
        }
        
        if ($email) {
            $conditions[] = "email = :email";
            $params[':email'] = $email;
        }
        
        if (empty($conditions)) {
            return false;
        }
        
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                 WHERE " . implode(' OR ', $conditions);
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        $result = $stmt->fetch();
        
        return $result['count'] > 0;
    }
    
    // Получить активность пользователя
    public function getUserActivity($user_id, $days = 30) {
        $query = "SELECT 
                    DATE(created_at) as date,
                    'order' as type,
                    CONCAT('Заказ #', order_number, ' на сумму ', final_amount, ' руб.') as description
                 FROM orders
                 WHERE user_id = :user_id 
                 AND created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                 
                 UNION ALL
                 
                 SELECT 
                    DATE(created_at) as date,
                    'message' as type,
                    CONCAT('Сообщение в чате') as description
                 FROM messages
                 WHERE sender_type = 'user' 
                 AND sender_id = :user_id
                 AND created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                 
                 ORDER BY date DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":days", $days, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function getUsersCount() {
        $query = "SELECT COUNT(*) FROM " . $this->table_name;
        $stmt = $this->conn->query($query);
        return (int)$stmt->fetchColumn();
    }
}
?>