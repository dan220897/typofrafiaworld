<?php
// admin/classes/AdminLog.php

class AdminLog {
    private $conn;
    private $table_name = "admin_logs";
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Записать действие в лог
    public function log($admin_id, $action, $details = null, $entity_type = null, $entity_id = null) {
        $query = "INSERT INTO " . $this->table_name . " 
                 (admin_id, action, entity_type, entity_id, details, ip_address, user_agent) 
                 VALUES (:admin_id, :action, :entity_type, :entity_id, :details, :ip, :user_agent)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":admin_id", $admin_id);
        $stmt->bindParam(":action", $action);
        $stmt->bindParam(":entity_type", $entity_type);
        $stmt->bindParam(":entity_id", $entity_id);
        $stmt->bindParam(":details", $details);
        $stmt->bindValue(":ip", $this->getClientIP());
        $stmt->bindValue(":user_agent", $_SERVER['HTTP_USER_AGENT'] ?? null);
        
        return $stmt->execute();
    }
    
    // Получить логи с фильтрацией
    public function getLogs($filters = [], $limit = 50, $offset = 0) {
        $query = "SELECT l.*, a.full_name as admin_name, a.username 
                 FROM " . $this->table_name . " l
                 LEFT JOIN admins a ON l.admin_id = a.id
                 WHERE 1=1";
        
        // Применяем фильтры
        if (!empty($filters['admin_id'])) {
            $query .= " AND l.admin_id = :admin_id";
        }
        
        if (!empty($filters['action'])) {
            $query .= " AND l.action = :action";
        }
        
        if (!empty($filters['entity_type'])) {
            $query .= " AND l.entity_type = :entity_type";
        }
        
        if (!empty($filters['entity_id'])) {
            $query .= " AND l.entity_id = :entity_id";
        }
        
        if (!empty($filters['date_from'])) {
            $query .= " AND l.created_at >= :date_from";
        }
        
        if (!empty($filters['date_to'])) {
            $query .= " AND l.created_at <= :date_to";
        }
        
        if (!empty($filters['search'])) {
            $query .= " AND (l.details LIKE :search OR l.action LIKE :search)";
        }
        
        $query .= " ORDER BY l.created_at DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        
        if (!empty($filters['admin_id'])) {
            $stmt->bindParam(":admin_id", $filters['admin_id']);
        }
        
        if (!empty($filters['action'])) {
            $stmt->bindParam(":action", $filters['action']);
        }
        
        if (!empty($filters['entity_type'])) {
            $stmt->bindParam(":entity_type", $filters['entity_type']);
        }
        
        if (!empty($filters['entity_id'])) {
            $stmt->bindParam(":entity_id", $filters['entity_id']);
        }
        
        if (!empty($filters['date_from'])) {
            $stmt->bindParam(":date_from", $filters['date_from']);
        }
        
        if (!empty($filters['date_to'])) {
            $stmt->bindParam(":date_to", $filters['date_to']);
        }
        
        if (!empty($filters['search'])) {
            $search = "%{$filters['search']}%";
            $stmt->bindParam(":search", $search);
        }
        
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    // Получить количество логов
    public function getLogsCount($filters = []) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " l WHERE 1=1";
        
        if (!empty($filters['admin_id'])) {
            $query .= " AND l.admin_id = :admin_id";
        }
        
        if (!empty($filters['action'])) {
            $query .= " AND l.action = :action";
        }
        
        if (!empty($filters['entity_type'])) {
            $query .= " AND l.entity_type = :entity_type";
        }
        
        if (!empty($filters['date_from'])) {
            $query .= " AND l.created_at >= :date_from";
        }
        
        if (!empty($filters['date_to'])) {
            $query .= " AND l.created_at <= :date_to";
        }
        
        if (!empty($filters['search'])) {
            $query .= " AND (l.details LIKE :search OR l.action LIKE :search)";
        }
        
        $stmt = $this->conn->prepare($query);
        
        if (!empty($filters['admin_id'])) {
            $stmt->bindParam(":admin_id", $filters['admin_id']);
        }
        
        if (!empty($filters['action'])) {
            $stmt->bindParam(":action", $filters['action']);
        }
        
        if (!empty($filters['entity_type'])) {
            $stmt->bindParam(":entity_type", $filters['entity_type']);
        }
        
        if (!empty($filters['date_from'])) {
            $stmt->bindParam(":date_from", $filters['date_from']);
        }
        
        if (!empty($filters['date_to'])) {
            $stmt->bindParam(":date_to", $filters['date_to']);
        }
        
        if (!empty($filters['search'])) {
            $search = "%{$filters['search']}%";
            $stmt->bindParam(":search", $search);
        }
        
        $stmt->execute();
        $result = $stmt->fetch();
        
        return $result['total'];
    }
    
    // Получить уникальные действия
    public function getUniqueActions() {
        $query = "SELECT DISTINCT action FROM " . $this->table_name . " 
                 ORDER BY action";
        
        $stmt = $this->conn->query($query);
        
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    // Получить уникальные типы сущностей
    public function getEntityTypes() {
        $query = "SELECT DISTINCT entity_type FROM " . $this->table_name . " 
                 WHERE entity_type IS NOT NULL 
                 ORDER BY entity_type";
        
        $stmt = $this->conn->query($query);
        
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    // Получить статистику по администраторам
    public function getAdminStats($date_from = null, $date_to = null) {
        $query = "SELECT a.id, a.full_name, a.username,
                        COUNT(l.id) as actions_count,
                        MAX(l.created_at) as last_action
                 FROM admins a
                 LEFT JOIN " . $this->table_name . " l ON a.id = l.admin_id";
        
        if ($date_from || $date_to) {
            $query .= " AND 1=1";
            
            if ($date_from) {
                $query .= " AND l.created_at >= :date_from";
            }
            
            if ($date_to) {
                $query .= " AND l.created_at <= :date_to";
            }
        }
        
        $query .= " GROUP BY a.id ORDER BY actions_count DESC";
        
        $stmt = $this->conn->prepare($query);
        
        if ($date_from) {
            $stmt->bindParam(":date_from", $date_from);
        }
        
        if ($date_to) {
            $stmt->bindParam(":date_to", $date_to);
        }
        
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    // Получить статистику по действиям
    public function getActionStats($date_from = null, $date_to = null) {
        $query = "SELECT action, COUNT(*) as count 
                 FROM " . $this->table_name . " 
                 WHERE 1=1";
        
        if ($date_from) {
            $query .= " AND created_at >= :date_from";
        }
        
        if ($date_to) {
            $query .= " AND created_at <= :date_to";
        }
        
        $query .= " GROUP BY action ORDER BY count DESC";
        
        $stmt = $this->conn->prepare($query);
        
        if ($date_from) {
            $stmt->bindParam(":date_from", $date_from);
        }
        
        if ($date_to) {
            $stmt->bindParam(":date_to", $date_to);
        }
        
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    // Очистка старых логов
    public function cleanOldLogs($days = 90) {
        $query = "DELETE FROM " . $this->table_name . " 
                 WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":days", $days, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            return $stmt->rowCount();
        }
        
        return false;
    }
    
    // Получить IP клиента
    private function getClientIP() {
        $ipKeys = ['HTTP_CF_CONNECTING_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (isset($_SERVER[$key])) {
                $ip = trim(explode(',', $_SERVER[$key])[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    // Экспорт логов
    public function exportLogs($filters = [], $format = 'csv') {
        $logs = $this->getLogs($filters, 10000, 0); // Получаем больше записей для экспорта
        
        if ($format === 'csv') {
            $output = "Дата,Администратор,Действие,Тип объекта,ID объекта,Детали,IP адрес\n";
            
            foreach ($logs as $log) {
                $output .= sprintf(
                    "%s,%s,%s,%s,%s,%s,%s\n",
                    $log['created_at'],
                    $log['admin_name'],
                    $log['action'],
                    $log['entity_type'] ?: '-',
                    $log['entity_id'] ?: '-',
                    str_replace(',', ';', $log['details'] ?: '-'),
                    $log['ip_address']
                );
            }
            
            return $output;
        }
        
        return $logs;
    }
}