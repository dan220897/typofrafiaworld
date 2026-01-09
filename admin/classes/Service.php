<?php
// admin/classes/Service.php

class Service {
    private $conn;
    private $table_name = "services";
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Получить список услуг
    public function getServices($filters = [], $limit = 50, $offset = 0) {
        $query = "SELECT s.*,
                        COUNT(DISTINCT sp.id) as parameters_count,
                        COUNT(DISTINCT spr.id) as rules_count,
                        (SELECT COUNT(*) FROM order_items WHERE service_id = s.id) as usage_count
                 FROM " . $this->table_name . " s
                 LEFT JOIN service_parameters sp ON s.id = sp.service_id AND sp.is_active = 1
                 LEFT JOIN service_price_rules spr ON s.id = spr.service_id AND spr.is_active = 1
                 WHERE 1=1";
        
        // Применяем фильтры
        if (!empty($filters['search'])) {
            $query .= " AND (s.name LIKE :search OR s.description LIKE :search)";
        }
        
        if (!empty($filters['category'])) {
            $query .= " AND s.category = :category";
        }
        
        if (isset($filters['is_active'])) {
            $query .= " AND s.is_active = :is_active";
        }
        
        $query .= " GROUP BY s.id
                   ORDER BY s.sort_order ASC, s.name ASC
                   LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        
        if (!empty($filters['search'])) {
            $search = "%{$filters['search']}%";
            $stmt->bindParam(":search", $search);
        }
        
        if (!empty($filters['category'])) {
            $stmt->bindParam(":category", $filters['category']);
        }
        
        if (isset($filters['is_active'])) {
            $stmt->bindParam(":is_active", $filters['is_active']);
        }
        
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    public function getActiveServices() {
    $query = "SELECT s.id, s.name, s.category, s.base_price, s.min_quantity, s.production_time_days
             FROM " . $this->table_name . " s
             WHERE s.is_active = 1
             ORDER BY s.sort_order ASC, s.name ASC";
    
    $stmt = $this->conn->prepare($query);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
    
    // Получить услугу по ID
    public function getServiceById($service_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $service_id);
        $stmt->execute();
        
        $service = $stmt->fetch();
        
        if ($service) {
            // Получаем параметры
            $service['parameters'] = $this->getServiceParameters($service_id);
            
            // Получаем правила ценообразования
            $service['price_rules'] = $this->getServicePriceRules($service_id);
        }
        
        return $service;
    }
    
    // Создать услугу
    public function createService($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                 (name, description, category, base_price, min_quantity, production_time_days, is_active, sort_order) 
                 VALUES (:name, :description, :category, :base_price, :min_quantity, :production_time_days, :is_active, :sort_order)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":name", $data['name']);
        $stmt->bindParam(":description", $data['description']);
        $stmt->bindParam(":category", $data['category']);
        $stmt->bindParam(":base_price", $data['base_price']);
        $stmt->bindParam(":min_quantity", $data['min_quantity']);
        $stmt->bindParam(":production_time_days", $data['production_time_days']);
        $stmt->bindValue(":is_active", $data['is_active'] ?? 1);
        $stmt->bindValue(":sort_order", $data['sort_order'] ?? 0);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        
        return false;
    }
    
    // Обновить услугу
    public function updateService($service_id, $data) {
        $allowed_fields = ['name', 'description', 'category', 'base_price', 
                          'min_quantity', 'production_time_days', 'is_active', 'sort_order'];
        $update_fields = [];
        $params = [':id' => $service_id];
        
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
    
    // Удалить услугу (soft delete)
    public function deleteService($service_id) {
        // Проверяем, используется ли услуга в заказах
        $check_query = "SELECT COUNT(*) as count FROM order_items WHERE service_id = :service_id";
        $check_stmt = $this->conn->prepare($check_query);
        $check_stmt->bindParam(":service_id", $service_id);
        $check_stmt->execute();
        $result = $check_stmt->fetch();
        
        if ($result['count'] > 0) {
            // Если используется, только деактивируем
            return $this->updateService($service_id, ['is_active' => 0]);
        }
        
        // Если не используется, можно удалить
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $service_id);
        
        return $stmt->execute();
    }
    
    // Получить параметры услуги
    public function getServiceParameters($service_id) {
        $query = "SELECT * FROM service_parameters 
                 WHERE service_id = :service_id 
                 ORDER BY parameter_type, parameter_name";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":service_id", $service_id);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    // Добавить параметр услуги
    public function addServiceParameter($service_id, $data) {
        $query = "INSERT INTO service_parameters 
                 (service_id, parameter_type, parameter_name, parameter_value, price_modifier, price_multiplier, is_active) 
                 VALUES (:service_id, :parameter_type, :parameter_name, :parameter_value, :price_modifier, :price_multiplier, :is_active)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":service_id", $service_id);
        $stmt->bindParam(":parameter_type", $data['parameter_type']);
        $stmt->bindParam(":parameter_name", $data['parameter_name']);
        $stmt->bindParam(":parameter_value", $data['parameter_value']);
        $stmt->bindValue(":price_modifier", $data['price_modifier'] ?? 0);
        $stmt->bindValue(":price_multiplier", $data['price_multiplier'] ?? 1);
        $stmt->bindValue(":is_active", $data['is_active'] ?? 1);
        
        return $stmt->execute();
    }
    
    // Обновить параметр услуги
    public function updateServiceParameter($param_id, $data) {
        $allowed_fields = ['parameter_type', 'parameter_name', 'parameter_value', 
                          'price_modifier', 'price_multiplier', 'is_active'];
        $update_fields = [];
        $params = [':id' => $param_id];
        
        foreach ($allowed_fields as $field) {
            if (isset($data[$field])) {
                $update_fields[] = "$field = :$field";
                $params[":$field"] = $data[$field];
            }
        }
        
        if (empty($update_fields)) {
            return false;
        }
        
        $query = "UPDATE service_parameters 
                 SET " . implode(', ', $update_fields) . "
                 WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        return $stmt->execute();
    }
    
    // Удалить параметр услуги
    public function deleteServiceParameter($param_id) {
        $query = "DELETE FROM service_parameters WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $param_id);
        
        return $stmt->execute();
    }
    
    // Получить правила ценообразования услуги
    public function getServicePriceRules($service_id) {
        $query = "SELECT * FROM service_price_rules 
                 WHERE service_id = :service_id 
                 ORDER BY rule_type, min_quantity";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":service_id", $service_id);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    // Добавить правило ценообразования
    public function addPriceRule($service_id, $data) {
        $query = "INSERT INTO service_price_rules 
                 (service_id, rule_type, min_quantity, max_quantity, discount_percent, 
                  fixed_discount, surcharge_percent, conditions, is_active) 
                 VALUES (:service_id, :rule_type, :min_quantity, :max_quantity, :discount_percent,
                        :fixed_discount, :surcharge_percent, :conditions, :is_active)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":service_id", $service_id);
        $stmt->bindParam(":rule_type", $data['rule_type']);
        $stmt->bindParam(":min_quantity", $data['min_quantity']);
        $stmt->bindParam(":max_quantity", $data['max_quantity']);
        $stmt->bindParam(":discount_percent", $data['discount_percent']);
        $stmt->bindParam(":fixed_discount", $data['fixed_discount']);
        $stmt->bindParam(":surcharge_percent", $data['surcharge_percent']);
        $stmt->bindParam(":conditions", $data['conditions'] ? json_encode($data['conditions']) : null);
        $stmt->bindValue(":is_active", $data['is_active'] ?? 1);
        
        return $stmt->execute();
    }
    
    // Расчет цены с учетом параметров и правил
    public function calculatePrice($service_id, $quantity, $parameters = [], $options = []) {
        // Получаем базовую услугу
        $service = $this->getServiceById($service_id);
        
        if (!$service || !$service['is_active']) {
            throw new Exception('Услуга не найдена или неактивна');
        }
        
        if ($quantity < $service['min_quantity']) {
            throw new Exception("Минимальное количество: {$service['min_quantity']}");
        }
        
        $base_price = $service['base_price'];
        $price_modifier = 0;
        $price_multiplier = 1;
        
        // Применяем модификаторы параметров
        if (!empty($parameters)) {
            $param_ids = array_map('intval', $parameters);
            $placeholders = implode(',', array_fill(0, count($param_ids), '?'));
            
            $query = "SELECT price_modifier, price_multiplier 
                     FROM service_parameters 
                     WHERE id IN ($placeholders) AND is_active = 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute($param_ids);
            
            while ($param = $stmt->fetch()) {
                $price_modifier += $param['price_modifier'];
                $price_multiplier *= $param['price_multiplier'];
            }
        }
        
        // Базовая цена с модификаторами
        $unit_price = ($base_price + $price_modifier) * $price_multiplier;
        
        // Применяем правила ценообразования
        $discount_percent = 0;
        $fixed_discount = 0;
        $surcharge_percent = 0;
        
        // Скидка за объем
        $volume_query = "SELECT * FROM service_price_rules 
                        WHERE service_id = :service_id 
                        AND rule_type = 'volume_discount'
                        AND min_quantity <= :quantity 
                        AND (max_quantity IS NULL OR max_quantity >= :quantity)
                        AND is_active = 1
                        ORDER BY discount_percent DESC, fixed_discount DESC
                        LIMIT 1";
        
        $stmt = $this->conn->prepare($volume_query);
        $stmt->bindParam(":service_id", $service_id);
        $stmt->bindParam(":quantity", $quantity);
        $stmt->execute();
        
        if ($volume_rule = $stmt->fetch()) {
            $discount_percent = $volume_rule['discount_percent'] ?: 0;
            $fixed_discount = $volume_rule['fixed_discount'] ?: 0;
        }
        
        // Наценка за срочность
        if (!empty($options['urgent'])) {
            $urgent_query = "SELECT * FROM service_price_rules 
                           WHERE service_id = :service_id 
                           AND rule_type = 'urgency_surcharge'
                           AND is_active = 1
                           LIMIT 1";
            
            $stmt = $this->conn->prepare($urgent_query);
            $stmt->bindParam(":service_id", $service_id);
            $stmt->execute();
            
            if ($urgent_rule = $stmt->fetch()) {
                $surcharge_percent = $urgent_rule['surcharge_percent'] ?: 0;
            }
        }
        
        // Итоговый расчет
        $unit_price = $unit_price * (1 - $discount_percent / 100) * (1 + $surcharge_percent / 100);
        $unit_price -= $fixed_discount;
        
        // Не может быть меньше 0
        $unit_price = max(0, $unit_price);
        
        $total_price = $unit_price * $quantity;
        
        return [
            'base_price' => $base_price,
            'unit_price' => round($unit_price, 2),
            'total_price' => round($total_price, 2),
            'quantity' => $quantity,
            'discount_percent' => $discount_percent,
            'fixed_discount' => $fixed_discount,
            'surcharge_percent' => $surcharge_percent,
            'parameters' => $parameters
        ];
    }
    
    // Получить категории услуг
    public function getCategories() {
        $query = "SELECT DISTINCT category, COUNT(*) as count 
                 FROM " . $this->table_name . " 
                 WHERE category IS NOT NULL 
                 GROUP BY category 
                 ORDER BY category";
        
        $stmt = $this->conn->query($query);
        
        return $stmt->fetchAll();
    }
    
    // Получить статистику услуг
    public function getServiceStats($service_id = null) {
        if ($service_id) {
            // Статистика конкретной услуги
            $query = "SELECT 
                        s.*,
                        COUNT(DISTINCT oi.order_id) as orders_count,
                        SUM(oi.quantity) as total_quantity,
                        SUM(oi.total_price) as total_revenue,
                        AVG(oi.total_price) as avg_order_value,
                        (SELECT COUNT(*) FROM service_parameters WHERE service_id = s.id) as params_count,
                        (SELECT COUNT(*) FROM service_price_rules WHERE service_id = s.id) as rules_count
                     FROM " . $this->table_name . " s
                     LEFT JOIN order_items oi ON s.id = oi.service_id
                     WHERE s.id = :service_id
                     GROUP BY s.id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":service_id", $service_id);
            $stmt->execute();
            
            return $stmt->fetch();
        } else {
            // Общая статистика
            $query = "SELECT 
                        COUNT(*) as total_services,
                        COUNT(CASE WHEN is_active = 1 THEN 1 END) as active_services,
                        COUNT(DISTINCT category) as categories_count,
                        (SELECT COUNT(*) FROM order_items) as total_orders,
                        (SELECT SUM(total_price) FROM order_items) as total_revenue
                     FROM " . $this->table_name;
            
            $stmt = $this->conn->query($query);
            $stats = $stmt->fetch();
            
            // Топ услуг
            $top_query = "SELECT s.*, 
                                COUNT(oi.id) as orders_count,
                                SUM(oi.total_price) as revenue
                         FROM " . $this->table_name . " s
                         JOIN order_items oi ON s.id = oi.service_id
                         GROUP BY s.id
                         ORDER BY revenue DESC
                         LIMIT 10";
            
            $stmt = $this->conn->query($top_query);
            $stats['top_services'] = $stmt->fetchAll();
            
            return $stats;
        }
    }
    
    // Копировать услугу
    public function copyService($service_id) {
        $this->conn->beginTransaction();
        
        try {
            // Получаем оригинальную услугу
            $service = $this->getServiceById($service_id);
            
            if (!$service) {
                throw new Exception('Услуга не найдена');
            }
            
            // Создаем копию
            $service['name'] = $service['name'] . ' (копия)';
            $new_service_id = $this->createService($service);
            
            if (!$new_service_id) {
                throw new Exception('Ошибка создания копии услуги');
            }
            
            // Копируем параметры
            foreach ($service['parameters'] as $param) {
                unset($param['id']);
                $this->addServiceParameter($new_service_id, $param);
            }
            
            // Копируем правила ценообразования
            foreach ($service['price_rules'] as $rule) {
                unset($rule['id']);
                $this->addPriceRule($new_service_id, $rule);
            }
            
            $this->conn->commit();
            return $new_service_id;
            
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }
}
?>