<?php
// admin/classes/Promocode.php

class Promocode {
    private $conn;
    private $table_name = "promocodes";
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Получить список промокодов
    public function getPromocodes($filters = [], $limit = 20, $offset = 0) {
        $query = "SELECT p.*, 
                        a.full_name as created_by_name,
                        COUNT(DISTINCT pu.id) as total_usage
                 FROM " . $this->table_name . " p
                 LEFT JOIN admins a ON p.created_by = a.id
                 LEFT JOIN promocode_usage pu ON p.id = pu.promocode_id
                 WHERE 1=1";
        
        // Применяем фильтры
        if (isset($filters['is_active'])) {
            $query .= " AND p.is_active = :is_active";
        }
        
        if (!empty($filters['status'])) {
            $now = date('Y-m-d H:i:s');
            switch ($filters['status']) {
                case 'active':
                    $query .= " AND p.is_active = 1 AND p.valid_from <= '{$now}' 
                               AND (p.valid_until IS NULL OR p.valid_until >= '{$now}')
                               AND (p.usage_limit IS NULL OR p.usage_count < p.usage_limit)";
                    break;
                case 'expired':
                    $query .= " AND p.valid_until < '{$now}'";
                    break;
                case 'depleted':
                    $query .= " AND p.usage_limit IS NOT NULL AND p.usage_count >= p.usage_limit";
                    break;
            }
        }
        
        if (!empty($filters['search'])) {
            $query .= " AND (p.code LIKE :search OR p.description LIKE :search)";
        }
        
        if (!empty($filters['discount_type'])) {
            $query .= " AND p.discount_type = :discount_type";
        }
        
        $query .= " GROUP BY p.id ORDER BY p.created_at DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        
        if (isset($filters['is_active'])) {
            $stmt->bindParam(":is_active", $filters['is_active']);
        }
        
        if (!empty($filters['search'])) {
            $search = "%{$filters['search']}%";
            $stmt->bindParam(":search", $search);
        }
        
        if (!empty($filters['discount_type'])) {
            $stmt->bindParam(":discount_type", $filters['discount_type']);
        }
        
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    // Получить количество промокодов
    public function getPromocodesCount($filters = []) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " p WHERE 1=1";
        
        if (isset($filters['is_active'])) {
            $query .= " AND p.is_active = :is_active";
        }
        
        if (!empty($filters['search'])) {
            $query .= " AND (p.code LIKE :search OR p.description LIKE :search)";
        }
        
        $stmt = $this->conn->prepare($query);
        
        if (isset($filters['is_active'])) {
            $stmt->bindParam(":is_active", $filters['is_active']);
        }
        
        if (!empty($filters['search'])) {
            $search = "%{$filters['search']}%";
            $stmt->bindParam(":search", $search);
        }
        
        $stmt->execute();
        $result = $stmt->fetch();
        
        return $result['total'];
    }
    
    // Получить промокод по ID
    public function getPromocodeById($id) {
        $query = "SELECT p.*, 
                        a.full_name as created_by_name
                 FROM " . $this->table_name . " p
                 LEFT JOIN admins a ON p.created_by = a.id
                 WHERE p.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        $promocode = $stmt->fetch();
        
        if ($promocode) {
            // Получаем историю использования
            $usage_query = "SELECT pu.*, u.name as user_name, o.order_number
                           FROM promocode_usage pu
                           LEFT JOIN users u ON pu.user_id = u.id
                           LEFT JOIN orders o ON pu.order_id = o.id
                           WHERE pu.promocode_id = :id
                           ORDER BY pu.used_at DESC
                           LIMIT 10";
            
            $usage_stmt = $this->conn->prepare($usage_query);
            $usage_stmt->bindParam(":id", $id);
            $usage_stmt->execute();
            
            $promocode['usage_history'] = $usage_stmt->fetchAll();
        }
        
        return $promocode;
    }
    
    // Создать промокод
    public function createPromocode($data) {
        // Проверяем уникальность кода
        if ($this->promocodeExists($data['code'])) {
            throw new Exception('Промокод с таким кодом уже существует');
        }
        
        $query = "INSERT INTO " . $this->table_name . " 
                 (code, description, discount_type, discount_value, min_order_amount,
                  max_discount_amount, usage_limit, user_usage_limit, valid_from, 
                  valid_until, is_active, created_by) 
                 VALUES (:code, :description, :discount_type, :discount_value, 
                         :min_order_amount, :max_discount_amount, :usage_limit, 
                         :user_usage_limit, :valid_from, :valid_until, :is_active, :created_by)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":code", $data['code']);
        $stmt->bindParam(":description", $data['description']);
        $stmt->bindParam(":discount_type", $data['discount_type']);
        $stmt->bindParam(":discount_value", $data['discount_value']);
        $stmt->bindParam(":min_order_amount", $data['min_order_amount']);
        $stmt->bindParam(":max_discount_amount", $data['max_discount_amount']);
        $stmt->bindParam(":usage_limit", $data['usage_limit']);
        $stmt->bindParam(":user_usage_limit", $data['user_usage_limit']);
        $stmt->bindParam(":valid_from", $data['valid_from']);
        $stmt->bindParam(":valid_until", $data['valid_until']);
        $stmt->bindParam(":is_active", $data['is_active']);
        $stmt->bindParam(":created_by", $data['created_by']);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        
        return false;
    }
    
    // Обновить промокод
    public function updatePromocode($id, $data) {
        // Если меняется код, проверяем уникальность
        if (isset($data['code'])) {
            $current = $this->getPromocodeById($id);
            if ($current['code'] != $data['code'] && $this->promocodeExists($data['code'])) {
                throw new Exception('Промокод с таким кодом уже существует');
            }
        }
        
        $allowed_fields = [
            'code', 'description', 'discount_type', 'discount_value', 
            'min_order_amount', 'max_discount_amount', 'usage_limit', 
            'user_usage_limit', 'valid_from', 'valid_until', 'is_active'
        ];
        
        $update_fields = [];
        $params = [':id' => $id];
        
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
                 SET " . implode(', ', $update_fields) . " 
                 WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        return $stmt->execute();
    }
    
    // Проверить существование промокода
    private function promocodeExists($code) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                 WHERE code = :code";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":code", $code);
        $stmt->execute();
        
        return $stmt->fetch()['count'] > 0;
    }
    
    // Активировать/деактивировать промокод
    public function toggleActive($id, $is_active) {
        $query = "UPDATE " . $this->table_name . " 
                 SET is_active = :is_active 
                 WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":is_active", $is_active);
        $stmt->bindParam(":id", $id);
        
        return $stmt->execute();
    }
    
    // Удалить промокод
    public function deletePromocode($id) {
        // Проверяем, использовался ли промокод
        $usage_query = "SELECT COUNT(*) as count FROM promocode_usage 
                       WHERE promocode_id = :id";
        $usage_stmt = $this->conn->prepare($usage_query);
        $usage_stmt->bindParam(":id", $id);
        $usage_stmt->execute();
        
        if ($usage_stmt->fetch()['count'] > 0) {
            throw new Exception('Невозможно удалить использованный промокод');
        }
        
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        
        return $stmt->execute();
    }
    
    // Получить статистику по промокодам
    public function getStats() {
        $stats = [];
        
        // Общее количество
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->query($query);
        $stats['total'] = $stmt->fetch()['total'];
        
        // Активные
        $now = date('Y-m-d H:i:s');
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                 WHERE is_active = 1 AND valid_from <= '{$now}' 
                 AND (valid_until IS NULL OR valid_until >= '{$now}')
                 AND (usage_limit IS NULL OR usage_count < usage_limit)";
        $stmt = $this->conn->query($query);
        $stats['active'] = $stmt->fetch()['count'];
        
        // Общая сумма скидок
        $query = "SELECT SUM(discount_amount) as total_discount 
                 FROM promocode_usage";
        $stmt = $this->conn->query($query);
        $stats['total_discount'] = $stmt->fetch()['total_discount'] ?: 0;
        
        // Количество использований
        $query = "SELECT COUNT(*) as total_usage FROM promocode_usage";
        $stmt = $this->conn->query($query);
        $stats['total_usage'] = $stmt->fetch()['total_usage'];
        
        return $stats;
    }
    
    // Генерировать уникальный код
    public function generateCode($length = 8) {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        
        do {
            $code = '';
            for ($i = 0; $i < $length; $i++) {
                $code .= $characters[rand(0, strlen($characters) - 1)];
            }
        } while ($this->promocodeExists($code));
        
        return $code;
    }
}