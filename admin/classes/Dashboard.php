<?php
// admin/classes/Dashboard.php

class Dashboard {
    private $conn;
    private $debug = true; // Включить отладку
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function getStats() {
        $stats = [];
        
        try {
            // Проверяем подключение
            if (!$this->conn) {
                throw new Exception("Нет подключения к базе данных");
            }
            
            // Заказы
            $stats['orders'] = $this->getOrderStats();
            
            // Выручка
            $stats['revenue'] = $this->getRevenueStats();
            
            // Пользователи
            $stats['users'] = $this->getUserStats();
            
            // Чаты
            $stats['chats'] = $this->getChatStats();
            
            // Последние заказы
            $stats['recent_orders'] = $this->getRecentOrders();
            
            // Активные чаты
            $stats['active_chats'] = $this->getActiveChats();
            
            // Данные для графиков
            $chartData = $this->getChartData();
            $stats['chart_labels'] = $chartData['labels'];
            $stats['orders_chart_data'] = $chartData['orders'];
            $stats['revenue_chart_data'] = $chartData['revenue'];
            
        } catch (Exception $e) {
            if ($this->debug) {
                throw $e;
            }
            error_log("Dashboard error: " . $e->getMessage());
        }
        
        return $stats;
    }
    
    private function getOrderStats() {
        try {
            // Всего заказов
            $query = "SELECT COUNT(*) as total FROM orders";
            $stmt = $this->conn->query($query);
            if (!$stmt) {
                throw new Exception("Ошибка запроса заказов: " . implode(' ', $this->conn->errorInfo()));
            }
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $total = $result['total'] ?? 0;
            
            // Заказы за текущий месяц
            $query = "SELECT COUNT(*) as count FROM orders 
                     WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) 
                     AND YEAR(created_at) = YEAR(CURRENT_DATE())";
            $stmt = $this->conn->query($query);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $thisMonth = $result['count'] ?? 0;
            
            // Заказы за прошлый месяц
            $query = "SELECT COUNT(*) as count FROM orders 
                     WHERE MONTH(created_at) = MONTH(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH))
                     AND YEAR(created_at) = YEAR(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH))";
            $stmt = $this->conn->query($query);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $lastMonth = $result['count'] ?? 0;
            
            $change = $this->calculatePercentChange($thisMonth, $lastMonth);
            
            return [
                'total' => $total,
                'this_month' => $thisMonth,
                'last_month' => $lastMonth,
                'change' => $change
            ];
            
        } catch (Exception $e) {
            if ($this->debug) {
                echo "Ошибка getOrderStats: " . $e->getMessage() . "<br>";
            }
            return ['total' => 0, 'change' => 0];
        }
    }
    
    private function getRevenueStats() {
        try {
            // Общая выручка
            $query = "SELECT IFNULL(SUM(final_amount), 0) as total FROM orders WHERE payment_status = 'paid'";
            $stmt = $this->conn->query($query);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $total = $result['total'] ?? 0;
            
            // Выручка за текущий месяц
            $query = "SELECT IFNULL(SUM(final_amount), 0) as amount FROM orders 
                     WHERE payment_status = 'paid'
                     AND MONTH(created_at) = MONTH(CURRENT_DATE()) 
                     AND YEAR(created_at) = YEAR(CURRENT_DATE())";
            $stmt = $this->conn->query($query);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $thisMonth = $result['amount'] ?? 0;
            
            // Выручка за прошлый месяц
            $query = "SELECT IFNULL(SUM(final_amount), 0) as amount FROM orders 
                     WHERE payment_status = 'paid'
                     AND MONTH(created_at) = MONTH(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH))
                     AND YEAR(created_at) = YEAR(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH))";
            $stmt = $this->conn->query($query);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $lastMonth = $result['amount'] ?? 0;
            
            $change = $this->calculatePercentChange($thisMonth, $lastMonth);
            
            return [
                'total' => $total,
                'this_month' => $thisMonth,
                'last_month' => $lastMonth,
                'change' => $change
            ];
            
        } catch (Exception $e) {
            if ($this->debug) {
                echo "Ошибка getRevenueStats: " . $e->getMessage() . "<br>";
            }
            return ['total' => 0, 'change' => 0];
        }
    }
    
    private function getUserStats() {
        try {
            // Всего пользователей
            $query = "SELECT COUNT(*) as total FROM users";
            $stmt = $this->conn->query($query);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $total = $result['total'] ?? 0;
            
            // Новые за месяц
            $query = "SELECT COUNT(*) as count FROM users 
                     WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) 
                     AND YEAR(created_at) = YEAR(CURRENT_DATE())";
            $stmt = $this->conn->query($query);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $newThisMonth = $result['count'] ?? 0;
            
            return [
                'total' => $total,
                'new_this_month' => $newThisMonth
            ];
            
        } catch (Exception $e) {
            if ($this->debug) {
                echo "Ошибка getUserStats: " . $e->getMessage() . "<br>";
            }
            return ['total' => 0, 'new_this_month' => 0];
        }
    }
    
    private function getChatStats() {
        try {
            // Активные чаты
            $query = "SELECT COUNT(*) as count FROM chats WHERE status = 'active'";
            $stmt = $this->conn->query($query);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $active = $result['count'] ?? 0;
            
            // Непрочитанные сообщения
            $query = "SELECT COUNT(*) as count FROM messages 
                     WHERE sender_type = 'user' 
                     AND is_read_admin = 0";
            $stmt = $this->conn->query($query);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $unread = $result['count'] ?? 0;
            
            return [
                'active' => $active,
                'unread' => $unread
            ];
            
        } catch (Exception $e) {
            if ($this->debug) {
                echo "Ошибка getChatStats: " . $e->getMessage() . "<br>";
            }
            return ['active' => 0, 'unread' => 0];
        }
    }
    
    private function getRecentOrders($limit = 5) {
        try {
            $query = "SELECT o.*, u.name as user_name, u.phone as user_phone
                     FROM orders o
                     LEFT JOIN users u ON o.user_id = u.id
                     ORDER BY o.created_at DESC
                     LIMIT :limit";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            if ($this->debug) {
                echo "Ошибка getRecentOrders: " . $e->getMessage() . "<br>";
            }
            return [];
        }
    }
    
    private function getActiveChats($limit = 5) {
        try {
            $query = "SELECT c.*, u.name as user_name, u.phone as user_phone,
                            m.message_text as last_message,
                            m.created_at as last_message_time,
                            c.unread_admin_count as unread_count
                     FROM chats c
                     LEFT JOIN users u ON c.user_id = u.id
                     LEFT JOIN messages m ON m.id = (
                         SELECT id FROM messages 
                         WHERE chat_id = c.id 
                         ORDER BY created_at DESC 
                         LIMIT 1
                     )
                     WHERE c.status = 'active'
                     ORDER BY m.created_at DESC
                     LIMIT :limit";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            if ($this->debug) {
                echo "Ошибка getActiveChats: " . $e->getMessage() . "<br>";
            }
            return [];
        }
    }
    
    private function getChartData($days = 30) {
        try {
            $labels = [];
            $orders = [];
            $revenue = [];
            
            // Генерируем даты за последние N дней
            for ($i = $days - 1; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-$i days"));
                $labels[] = date('d.m', strtotime($date));
                
                // Заказы за день
                $query = "SELECT COUNT(*) as count FROM orders WHERE DATE(created_at) = :date";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':date', $date);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $orders[] = $result['count'] ?? 0;
                
                // Выручка за день
                $query = "SELECT IFNULL(SUM(final_amount), 0) as amount FROM orders 
                         WHERE DATE(created_at) = :date AND payment_status = 'paid'";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':date', $date);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $revenue[] = floatval($result['amount'] ?? 0);
            }
            
            return [
                'labels' => $labels,
                'orders' => $orders,
                'revenue' => $revenue
            ];
            
        } catch (Exception $e) {
            if ($this->debug) {
                echo "Ошибка getChartData: " . $e->getMessage() . "<br>";
            }
            // Возвращаем пустые массивы для графиков
            $labels = [];
            $orders = [];
            $revenue = [];
            for ($i = 0; $i < $days; $i++) {
                $labels[] = '';
                $orders[] = 0;
                $revenue[] = 0;
            }
            return [
                'labels' => $labels,
                'orders' => $orders,
                'revenue' => $revenue
            ];
        }
    }
    
    private function calculatePercentChange($current, $previous) {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }
        return round((($current - $previous) / $previous) * 100, 1);
    }
    
    // Метод для проверки таблиц
    public function checkTables() {
        $tables = ['orders', 'users', 'chats', 'messages', 'admins'];
        $missing = [];
        
        foreach ($tables as $table) {
            try {
                $query = "SELECT 1 FROM $table LIMIT 1";
                $this->conn->query($query);
            } catch (Exception $e) {
                $missing[] = $table;
            }
        }
        
        if (!empty($missing)) {
            throw new Exception("Отсутствуют таблицы: " . implode(', ', $missing));
        }
        
        return true;
    }
}
?>