<?php
// admin/classes/Order.php

require_once __DIR__ . '/TinkoffPayment.php';

class Order {
    private $conn;
    private $table_name = "orders";
    private $telegram;
    private $tinkoff;
    
    // Статусы заказов
    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_IN_PRODUCTION = 'in_production';
    const STATUS_READY = 'ready';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';
    
    // Статусы оплаты
    const PAYMENT_PENDING = 'pending';
    const PAYMENT_PAID = 'paid';
    const PAYMENT_PARTIALLY_PAID = 'partially_paid';
    const PAYMENT_REFUNDED = 'refunded';
    const PAYMENT_FAILED = 'failed';
    
    public function __construct($db) {
        $this->conn = $db;
        // Проверяем существование класса TelegramNotifier
        if (class_exists('TelegramNotifier')) {
            $this->telegram = new TelegramNotifier();
        }
        // Инициализируем Тинькофф
        $this->tinkoff = new TinkoffPayment();
    }
    
    // Получить список заказов с фильтрацией
    public function getOrders($filters = [], $limit = 50, $offset = 0) {
        $query = "SELECT o.*, 
                        u.name as user_name, u.phone as user_phone, u.company_name,
                        COUNT(DISTINCT oi.id) as items_count,
                        (SELECT GROUP_CONCAT(s.name SEPARATOR ', ') 
                         FROM order_items oi2 
                         JOIN services s ON oi2.service_id = s.id 
                         WHERE oi2.order_id = o.id 
                         LIMIT 3) as services_list
                 FROM " . $this->table_name . " o
                 LEFT JOIN users u ON o.user_id = u.id
                 LEFT JOIN order_items oi ON o.id = oi.order_id
                 WHERE 1=1";
        
        // Применяем фильтры
        if (!empty($filters['search'])) {
            $query .= " AND (o.order_number LIKE :search 
                           OR u.name LIKE :search 
                           OR u.phone LIKE :search)";
        }
        
        if (!empty($filters['status'])) {
            $query .= " AND o.status = :status";
        }
        
        if (!empty($filters['payment_status'])) {
            $query .= " AND o.payment_status = :payment_status";
        }
        
        if (!empty($filters['date_from'])) {
            $query .= " AND o.created_at >= :date_from";
        }
        
        if (!empty($filters['date_to'])) {
            $query .= " AND o.created_at <= :date_to";
        }
        
        if (!empty($filters['user_id'])) {
            $query .= " AND o.user_id = :user_id";
        }

        if (isset($filters['location_id'])) {
            $query .= " AND o.location_id = :location_id";
        }

        $query .= " GROUP BY o.id
                   ORDER BY o.created_at DESC
                   LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        
        // Привязываем параметры
        if (!empty($filters['search'])) {
            $search = "%{$filters['search']}%";
            $stmt->bindParam(":search", $search);
        }
        
        if (!empty($filters['status'])) {
            $stmt->bindParam(":status", $filters['status']);
        }
        
        if (!empty($filters['payment_status'])) {
            $stmt->bindParam(":payment_status", $filters['payment_status']);
        }
        
        if (!empty($filters['date_from'])) {
            $stmt->bindParam(":date_from", $filters['date_from']);
        }
        
        if (!empty($filters['date_to'])) {
            $stmt->bindParam(":date_to", $filters['date_to']);
        }
        
        if (!empty($filters['user_id'])) {
            $stmt->bindParam(":user_id", $filters['user_id']);
        }

        if (isset($filters['location_id'])) {
            $stmt->bindParam(":location_id", $filters['location_id'], PDO::PARAM_INT);
        }

        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        
        $orders = $stmt->fetchAll();
        
        // Форматируем данные для отображения
        foreach ($orders as &$order) {
            // Формируем краткое описание услуг
            $services = explode(', ', $order['services_list']);
            $order['items_summary'] = implode(', ', array_slice($services, 0, 2));
        }
        
        // Возвращаем результат с общим количеством
        return [
            'data' => $orders,
            'total' => $this->getOrdersCount($filters)
        ];
    }
    
    // Получить количество заказов с учетом фильтров
    public function getOrdersCount($filters = []) {
        $query = "SELECT COUNT(DISTINCT o.id) as total
                 FROM " . $this->table_name . " o
                 LEFT JOIN users u ON o.user_id = u.id
                 WHERE 1=1";
        
        // Применяем фильтры
        if (!empty($filters['search'])) {
            $query .= " AND (o.order_number LIKE :search 
                           OR u.name LIKE :search 
                           OR u.phone LIKE :search)";
        }
        
        if (!empty($filters['status'])) {
            $query .= " AND o.status = :status";
        }
        
        if (!empty($filters['payment_status'])) {
            $query .= " AND o.payment_status = :payment_status";
        }
        
        if (!empty($filters['date_from'])) {
            $query .= " AND o.created_at >= :date_from";
        }
        
        if (!empty($filters['date_to'])) {
            $query .= " AND o.created_at <= :date_to";
        }
        
        if (!empty($filters['user_id'])) {
            $query .= " AND o.user_id = :user_id";
        }

        if (isset($filters['location_id'])) {
            $query .= " AND o.location_id = :location_id";
        }

        $stmt = $this->conn->prepare($query);

        // Привязываем параметры
        if (!empty($filters['search'])) {
            $search = "%{$filters['search']}%";
            $stmt->bindParam(":search", $search);
        }

        if (!empty($filters['status'])) {
            $stmt->bindParam(":status", $filters['status']);
        }

        if (!empty($filters['payment_status'])) {
            $stmt->bindParam(":payment_status", $filters['payment_status']);
        }

        if (!empty($filters['date_from'])) {
            $stmt->bindParam(":date_from", $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $stmt->bindParam(":date_to", $filters['date_to']);
        }

        if (!empty($filters['user_id'])) {
            $stmt->bindParam(":user_id", $filters['user_id']);
        }

        if (isset($filters['location_id'])) {
            $stmt->bindParam(":location_id", $filters['location_id'], PDO::PARAM_INT);
        }
        
        $stmt->execute();
        $result = $stmt->fetch();
        
        return $result['total'] ?? 0;
    }
    
    // Получить статистику по статусам
    public function getStatusStats($location_id = null) {
        $query = "SELECT status, COUNT(*) as count
                 FROM " . $this->table_name;

        if ($location_id !== null) {
            $query .= " WHERE location_id = " . intval($location_id);
        }

        $query .= " GROUP BY status
                 ORDER BY
                    CASE status
                        WHEN 'draft' THEN 1
                        WHEN 'pending' THEN 2
                        WHEN 'confirmed' THEN 3
                        WHEN 'in_production' THEN 4
                        WHEN 'ready' THEN 5
                        WHEN 'delivered' THEN 6
                        WHEN 'cancelled' THEN 7
                        ELSE 8
                    END";

        $stmt = $this->conn->query($query);
        return $stmt->fetchAll();
    }
    
    // Получить заказ по ID
    public function getOrderById($order_id) {
        $query = "SELECT o.*, 
                        u.name as user_name, u.phone as user_phone, 
                        u.email as user_email, u.company_name, u.inn,
                        c.id as chat_id
                 FROM " . $this->table_name . " o
                 LEFT JOIN users u ON o.user_id = u.id
                 LEFT JOIN chats c ON o.chat_id = c.id
                 WHERE o.id = :order_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":order_id", $order_id);
        $stmt->execute();
        
        $order = $stmt->fetch();
        
        if ($order) {
            // Получаем позиции заказа
            $order['items'] = $this->getOrderItems($order_id);
            
            // Получаем историю статусов
            $order['status_history'] = $this->getStatusHistory($order_id);
        }
        
        return $order;
    }
    
    // Получить позиции заказа
    public function getOrderItems($order_id) {
        $query = "SELECT oi.*, s.name as service_name, s.category as service_category
                 FROM order_items oi
                 JOIN services s ON oi.service_id = s.id
                 WHERE oi.order_id = :order_id
                 ORDER BY oi.id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":order_id", $order_id);
        $stmt->execute();
        
        $items = $stmt->fetchAll();
        
        // Декодируем JSON параметры
        foreach ($items as &$item) {
            if (!empty($item['parameters'])) {
                $item['parameters'] = json_decode($item['parameters'], true);
            }
        }
        
        return $items;
    }
    
    // Создать новый заказ
    public function createOrder($user_id, $items = [], $notes = null, $generate_payment = true) {
        $this->conn->beginTransaction();
        
        try {
            // Генерируем номер заказа
            $order_number = $this->generateOrderNumber();
            
            // Создаем заказ
            $query = "INSERT INTO " . $this->table_name . " 
                     (order_number, user_id, status, notes, created_at, updated_at) 
                     VALUES (:order_number, :user_id, :status, :notes, NOW(), NOW())";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":order_number", $order_number);
            $stmt->bindParam(":user_id", $user_id);
            $stmt->bindValue(":status", self::STATUS_DRAFT);
            $stmt->bindParam(":notes", $notes);
            
            if (!$stmt->execute()) {
                throw new Exception("Ошибка при создании заказа");
            }
            
            $order_id = $this->conn->lastInsertId();
            
            // Добавляем позиции заказа
            if (!empty($items)) {
                foreach ($items as $item) {
                    $this->addOrderItem($order_id, $item);
                }
            }
            
            // Пересчитываем сумму заказа
            $this->recalculateOrderTotal($order_id);
            
            // Генерируем платежную ссылку если нужно
            if ($generate_payment) {
                $this->generatePaymentLink($order_id);
            }
            
            // Добавляем запись в историю
            $this->addStatusHistory($order_id, null, self::STATUS_DRAFT, 'system', null, 'Заказ создан');
            
            $this->conn->commit();
            
            return $order_id;
            
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
        foreach ($items as $item) {
        // Добавьте явную передачу цены
        $this->addOrderItem($order_id, [
            'service_id' => $item['service_id'],
            'quantity' => $item['quantity'],
            'unit_price' => $item['unit_price'], // Важно: передаём цену
            'parameters' => $item['parameters'] ?? [],
            'notes' => $item['notes'] ?? ''
        ]);
    }
    }
    
    // Генерация платежной ссылки Тинькофф
    public function generatePaymentLink($order_id) {
    $order = $this->getOrderById($order_id);
    if (!$order) {
        throw new Exception("Заказ не найден");
    }

    // Получаем сумму заказа
    $amount = $order['final_amount'] ?? $order['total_amount'] ?? 0;
    
    error_log("Order::generatePaymentLink - Заказ ID: $order_id, Сумма: $amount");
    
    // Если сумма равна 0, помечаем как оплаченный
    if ($amount == 0) {
        $this->updatePaymentStatus($order_id, self::PAYMENT_PAID);
        return null;
    }
    
    // Проверяем минимальную сумму
    if ($amount < 1) {
        throw new Exception("Сумма заказа должна быть не менее 1 рубля (текущая сумма: $amount руб.)");
    }
    
    $user = $this->getUserById($order['user_id']);
    
    // Создаем платеж в Тинькофф
    $payment = $this->tinkoff->createPayment(
        $order_id,
        $amount,
        "Заказ №{$order['order_number']}",
        [
            'email' => $user['email'] ?? null,
            'phone' => $user['phone'] ?? null
        ]
    );
    
    if ($payment['success']) {
        // Сохраняем информацию о платеже
        $query = "UPDATE " . $this->table_name . " SET 
                tinkoff_payment_id = :payment_id,
                tinkoff_payment_url = :payment_url
                WHERE id = :order_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':payment_id', $payment['paymentId']);
        $stmt->bindParam(':payment_url', $payment['paymentUrl']);
        $stmt->bindParam(':order_id', $order_id);
        $stmt->execute();
        
        return $payment['paymentUrl'];
    } else {
        // Логируем полную информацию об ошибке
        error_log("Order::generatePaymentLink - Ошибка от Тинькофф: " . json_encode($payment, JSON_UNESCAPED_UNICODE));
        
        // Формируем понятное сообщение об ошибке
        $errorMsg = 'Ошибка создания платежа: ' . ($payment['error'] ?? 'Unknown error');
        
        // Если есть детальный ответ, добавляем его в лог
        if (isset($payment['response'])) {
            error_log("Order::generatePaymentLink - Полный ответ Тинькофф: " . json_encode($payment['response'], JSON_UNESCAPED_UNICODE));
        }
        
        throw new Exception($errorMsg);
    }
}
    
    // Отправка SMS с ссылкой на оплату
    public function sendPaymentLinkSMS($order_id) {
        $order = $this->getOrderById($order_id);
        
        if (!$order || !$order['tinkoff_payment_url']) {
            return ['success' => false, 'error' => 'Заказ или ссылка не найдены'];
        }
        
        $message = "Ваш заказ №{$order['order_number']}. Сумма: {$order['final_amount']} руб. Оплатите по ссылке: {$order['tinkoff_payment_url']}";
        
        $params = [
            'api_id' => SMS_RU_API_KEY,
            'to' => $order['user_phone'],
            'msg' => $message,
            'json' => 1
        ];
        
        $ch = curl_init('https://sms.ru/sms/send');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        
        $result = curl_exec($ch);
        curl_close($ch);
        
        $response = json_decode($result, true);
        
        if ($response && $response['status'] == 'OK') {
            // Логируем отправку
            $this->logSMS($order_id, $order['user_phone'], $message, 'sent');
            return ['success' => true];
        }
        
        $this->logSMS($order_id, $order['user_phone'], $message, 'failed', $response['status_text'] ?? 'Unknown error');
        return ['success' => false, 'error' => 'Ошибка отправки SMS'];
    }
    
    // Логирование SMS
    private function logSMS($order_id, $phone, $message, $status, $error = null) {
        $query = "INSERT INTO sms_log (order_id, phone, message, status, error_message, created_at) 
                 VALUES (:order_id, :phone, :message, :status, :error, NOW())";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':order_id', $order_id);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':message', $message);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':error', $error);
        $stmt->execute();
    }
    
    // Проверка статуса платежа в Тинькофф
    public function checkTinkoffPaymentStatus($order_id) {
        $order = $this->getOrderById($order_id);
        
        if (!$order || !$order['tinkoff_payment_id']) {
            return ['success' => false, 'error' => 'Платеж не найден'];
        }
        
        $result = $this->tinkoff->checkPaymentStatus($order['tinkoff_payment_id']);
        
        if ($result['success']) {
            $newPaymentStatus = $this->mapTinkoffStatus($result['status']);
            
            if ($newPaymentStatus !== $order['payment_status']) {
                $this->updatePaymentStatus($order_id, $newPaymentStatus);
            }
            
            return [
                'success' => true,
                'status' => $newPaymentStatus,
                'tinkoff_status' => $result['status']
            ];
        }
        
        return ['success' => false, 'error' => 'Ошибка проверки статуса'];
    }
    
    // Маппинг статусов Тинькофф на внутренние
    private function mapTinkoffStatus($tinkoffStatus) {
        $map = [
            'NEW' => self::PAYMENT_PENDING,
            'CONFIRMED' => self::PAYMENT_PAID,
            'AUTHORIZED' => self::PAYMENT_PAID,
            'REJECTED' => self::PAYMENT_FAILED,
            'REFUNDED' => self::PAYMENT_REFUNDED,
            'CANCELLED' => self::PAYMENT_PENDING
        ];
        
        return $map[$tinkoffStatus] ?? self::PAYMENT_PENDING;
    }
    
    // Получить полную историю заказа (включая все изменения)
    public function getOrderHistory($order_id) {
        $history = [];
        
        // Получаем историю статусов
        $query = "SELECT osh.*, 
                        CASE 
                            WHEN osh.changed_by_type = 'admin' THEN a.full_name
                            WHEN osh.changed_by_type = 'user' THEN u.name
                            ELSE 'Система'
                        END as changed_by_name,
                        'status_change' as event_type
                 FROM order_status_history osh
                 LEFT JOIN admins a ON osh.changed_by_type = 'admin' AND osh.changed_by_id = a.id
                 LEFT JOIN users u ON osh.changed_by_type = 'user' AND osh.changed_by_id = u.id
                 WHERE osh.order_id = :order_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":order_id", $order_id);
        $stmt->execute();
        
        while ($row = $stmt->fetch()) {
            $row['event_description'] = "Статус изменен с " . $this->getStatusText($row['old_status'] ?? '') . 
                               " на " . $this->getStatusText($row['new_status'] ?? '');
            $row['description'] = $row['event_description'] ?? '';
            $history[] = $row;
        }
        
        // Можно добавить другие события: платежи, изменения позиций и т.д.
        
        // Сортируем по дате
        usort($history, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        return $history;
    }

    // Удаление заказа
    public function deleteOrder($order_id) {
        $this->conn->beginTransaction();
        
        try {
            // Сначала удаляем связанные записи
            
            // Удаляем позиции заказа
            $query = "DELETE FROM order_items WHERE order_id = :order_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":order_id", $order_id);
            $stmt->execute();
            
            // Удаляем историю статусов
            $query = "DELETE FROM order_status_history WHERE order_id = :order_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":order_id", $order_id);
            $stmt->execute();
            
            // Удаляем сам заказ
            $query = "DELETE FROM " . $this->table_name . " WHERE id = :order_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":order_id", $order_id);
            
            if ($stmt->execute()) {
                $this->conn->commit();
                return true;
            } else {
                throw new Exception("Ошибка при удалении заказа");
            }
            
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }
    
    public function addOrderItem($order_id, $item_data) {
        // Получаем информацию об услуге
        $service = $this->getServiceById($item_data['service_id']);
        
        if (!$service) {
            throw new Exception("Услуга не найдена");
        }
        
        // Проверяем, была ли передана кастомная цена
        if (isset($item_data['unit_price']) && $item_data['unit_price'] > 0) {
            // Используем переданную цену
            $unit_price = floatval($item_data['unit_price']);
            $total_price = $unit_price * intval($item_data['quantity']);
        } else {
            // Если цена не передана, рассчитываем стандартную
            $price_data = $this->calculateItemPrice(
                $item_data['service_id'],
                $item_data['quantity'],
                $item_data['parameters'] ?? []
            );
            $unit_price = $price_data['unit_price'];
            $total_price = $price_data['total_price'];
        }
        
        // Подготавливаем JSON для параметров
        $parameters_json = json_encode($item_data['parameters'] ?? []);
        
        $query = "INSERT INTO order_items 
                 (order_id, service_id, quantity, parameters, unit_price, total_price, notes) 
                 VALUES (:order_id, :service_id, :quantity, :parameters, :unit_price, :total_price, :notes)";
        
        $stmt = $this->conn->prepare($query);
        
        // Используем bindValue вместо bindParam для значений
        $stmt->bindValue(":order_id", $order_id);
        $stmt->bindValue(":service_id", $item_data['service_id']);
        $stmt->bindValue(":quantity", $item_data['quantity']);
        $stmt->bindValue(":parameters", $parameters_json);
        $stmt->bindValue(":unit_price", $unit_price);
        $stmt->bindValue(":total_price", $total_price);
        $stmt->bindValue(":notes", $item_data['notes'] ?? '');
        
        return $stmt->execute();
    }
    
    // Рассчитать цену позиции
    private function calculateItemPrice($service_id, $quantity, $parameters) {
        // Получаем базовую цену услуги
        $query = "SELECT base_price FROM services WHERE id = :service_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":service_id", $service_id);
        $stmt->execute();
        
        $service = $stmt->fetch();
        $base_price = $service['base_price'];
        
        // Применяем модификаторы параметров
        $price_modifier = 0;
        $price_multiplier = 1;
        
        if (!empty($parameters)) {
            $param_ids = array_map(function($p) { return $p['id']; }, $parameters);
            $placeholders = implode(',', array_fill(0, count($param_ids), '?'));
            
            $query = "SELECT price_modifier, price_multiplier 
                     FROM service_parameters 
                     WHERE id IN ($placeholders)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute($param_ids);
            
            while ($param = $stmt->fetch()) {
                $price_modifier += $param['price_modifier'];
                $price_multiplier *= $param['price_multiplier'];
            }
        }
        
        // Применяем правила ценообразования
        $discount_percent = $this->getVolumeDiscount($service_id, $quantity);
        
        // Итоговый расчет
        $unit_price = ($base_price + $price_modifier) * $price_multiplier;
        $unit_price = $unit_price * (1 - $discount_percent / 100);
        $total_price = $unit_price * $quantity;
        
        return [
            'unit_price' => round($unit_price, 2),
            'total_price' => round($total_price, 2)
        ];
    }
    
    // Получить скидку за объем
    private function getVolumeDiscount($service_id, $quantity) {
        $query = "SELECT discount_percent 
                 FROM service_price_rules 
                 WHERE service_id = :service_id 
                 AND rule_type = 'volume_discount'
                 AND min_quantity <= :quantity 
                 AND (max_quantity IS NULL OR max_quantity >= :quantity)
                 AND is_active = 1
                 ORDER BY discount_percent DESC
                 LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":service_id", $service_id);
        $stmt->bindParam(":quantity", $quantity);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return $result ? $result['discount_percent'] : 0;
    }
    
    // Пересчитать сумму заказа
    public function recalculateOrderTotal($order_id) {
        $query = "UPDATE " . $this->table_name . " o
                 SET total_amount = (
                     SELECT SUM(total_price) 
                     FROM order_items 
                     WHERE order_id = o.id
                 ),
                 final_amount = total_amount - discount_amount
                 WHERE id = :order_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":order_id", $order_id);
        
        return $stmt->execute();
    }
    
    // Изменить статус заказа
    public function updateOrderStatus($order_id, $new_status, $admin_id, $comment = null) {
        $this->conn->beginTransaction();
        
        try {
            // Получаем текущий статус
            $query = "SELECT status, order_number, user_id FROM " . $this->table_name . " WHERE id = :order_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":order_id", $order_id);
            $stmt->execute();
            
            $order = $stmt->fetch();
            $old_status = $order['status'];
            
            // Проверяем возможность смены статуса
            if (!$this->canChangeStatus($old_status, $new_status)) {
                throw new Exception("Невозможно изменить статус с {$old_status} на {$new_status}");
            }
            
            // Обновляем статус
            $query = "UPDATE " . $this->table_name . " 
                     SET status = :status";
            
            if ($new_status == self::STATUS_CONFIRMED) {
                $query .= ", confirmed_at = NOW()";
            } elseif ($new_status == self::STATUS_DELIVERED) {
                $query .= ", completed_at = NOW()";
            }
            
            $query .= " WHERE id = :order_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":status", $new_status);
            $stmt->bindParam(":order_id", $order_id);
            
            if (!$stmt->execute()) {
                throw new Exception("Ошибка при обновлении статуса");
            }
            
            // Добавляем запись в историю
            $this->addStatusHistory($order_id, $old_status, $new_status, 'admin', $admin_id, $comment);
            
            // Отправляем уведомление пользователю
            $this->sendStatusNotification($order_id, $new_status);
            
            // Уведомление в Telegram для важных статусов
            if ($this->telegram && in_array($new_status, [self::STATUS_CONFIRMED, self::STATUS_READY])) {
                $user = $this->getUserById($order['user_id']);
                $status_text = $this->getStatusText($new_status);
                
                $message = "📦 Заказ #{$order['order_number']}\n";
                $message .= "Статус изменен на: {$status_text}\n";
                $message .= "Клиент: {$user['name']}";
                
                $this->telegram->sendMessage($message);
            }
            
            $this->conn->commit();
            return true;
            
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }
    
    // Проверка возможности смены статуса
    // Проверка возможности смены статуса
private function canChangeStatus($old_status, $new_status) {
    $allowed_transitions = [
        self::STATUS_DRAFT => [self::STATUS_PENDING, self::STATUS_CONFIRMED, self::STATUS_CANCELLED], // Добавили CONFIRMED
        self::STATUS_PENDING => [self::STATUS_CONFIRMED, self::STATUS_CANCELLED],
        self::STATUS_CONFIRMED => [self::STATUS_IN_PRODUCTION, self::STATUS_CANCELLED],
        self::STATUS_IN_PRODUCTION => [self::STATUS_READY, self::STATUS_CANCELLED],
        self::STATUS_READY => [self::STATUS_DELIVERED],
        self::STATUS_DELIVERED => [],
        self::STATUS_CANCELLED => []
    ];
    
    return in_array($new_status, $allowed_transitions[$old_status] ?? []);
}

// Добавить заметку менеджера
public function addManagerNote($order_id, $note_text, $admin_id) {
    try {
        // Получаем текущие заметки
        $query = "SELECT manager_notes FROM " . $this->table_name . " WHERE id = :order_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':order_id', $order_id);
        $stmt->execute();
        
        $result = $stmt->fetch();
        $current_notes = $result['manager_notes'] ? json_decode($result['manager_notes'], true) : [];
        
        // Получаем имя администратора
        $query = "SELECT full_name FROM admins WHERE id = :admin_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':admin_id', $admin_id);
        $stmt->execute();
        $admin = $stmt->fetch();
        $admin_name = $admin['full_name'] ?? 'Администратор';
        
        // Добавляем новую заметку
        $new_note = [
            'id' => uniqid(),
            'text' => $note_text,
            'author' => $admin_name,
            'admin_id' => $admin_id,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        // Добавляем в начало массива (новые заметки сверху)
        array_unshift($current_notes, $new_note);
        
        // Сохраняем обратно в БД
        $notes_json = json_encode($current_notes, JSON_UNESCAPED_UNICODE);
        
        $query = "UPDATE " . $this->table_name . " 
                 SET manager_notes = :notes,
                     updated_at = NOW()
                 WHERE id = :order_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':notes', $notes_json);
        $stmt->bindParam(':order_id', $order_id);
        
        if ($stmt->execute()) {
            // Добавляем запись в историю
            $this->addToHistory($order_id, 'note_added', $admin_id, "Добавлена заметка: " . mb_substr($note_text, 0, 50) . (mb_strlen($note_text) > 50 ? '...' : ''));
            
            return true;
        }
        
        return false;
        
    } catch (Exception $e) {
        error_log("Ошибка добавления заметки: " . $e->getMessage());
        return false;
    }
}

// Получить заметки менеджера
public function getManagerNotes($order_id) {
    $query = "SELECT manager_notes FROM " . $this->table_name . " WHERE id = :order_id";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':order_id', $order_id);
    $stmt->execute();
    
    $result = $stmt->fetch();
    
    if ($result && $result['manager_notes']) {
        return json_decode($result['manager_notes'], true);
    }
    
    return [];
}

// Вспомогательный метод для добавления в историю
private function addToHistory($order_id, $action, $admin_id, $description) {
    try {
        // Можно использовать существующую таблицу admin_logs или создать отдельную для истории заказов
        $query = "INSERT INTO admin_logs (admin_id, action, description, entity_type, entity_id, created_at) 
                 VALUES (:admin_id, :action, :description, 'order', :order_id, NOW())";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':admin_id', $admin_id);
        $stmt->bindParam(':action', $action);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':order_id', $order_id);
        $stmt->execute();
    } catch (Exception $e) {
        error_log("Ошибка добавления в историю: " . $e->getMessage());
    }
}
    
    // Добавить запись в историю статусов
    private function addStatusHistory($order_id, $old_status, $new_status, $changed_by_type, $changed_by_id, $comment = null) {
        $query = "INSERT INTO order_status_history 
                 (order_id, old_status, new_status, changed_by_type, changed_by_id, comment, created_at) 
                 VALUES (:order_id, :old_status, :new_status, :changed_by_type, :changed_by_id, :comment, NOW())";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":order_id", $order_id);
        $stmt->bindParam(":old_status", $old_status);
        $stmt->bindParam(":new_status", $new_status);
        $stmt->bindParam(":changed_by_type", $changed_by_type);
        $stmt->bindParam(":changed_by_id", $changed_by_id);
        $stmt->bindParam(":comment", $comment);
        
        return $stmt->execute();
    }
    
    // Получить историю статусов
    public function getStatusHistory($order_id) {
        $query = "SELECT osh.*, 
                        CASE 
                            WHEN osh.changed_by_type = 'admin' THEN a.full_name
                            WHEN osh.changed_by_type = 'user' THEN u.name
                            ELSE 'Система'
                        END as changed_by_name
                 FROM order_status_history osh
                 LEFT JOIN admins a ON osh.changed_by_type = 'admin' AND osh.changed_by_id = a.id
                 LEFT JOIN users u ON osh.changed_by_type = 'user' AND osh.changed_by_id = u.id
                 WHERE osh.order_id = :order_id
                 ORDER BY osh.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":order_id", $order_id);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    // Обновить статус оплаты
    public function updatePaymentStatus($order_id, $payment_status, $manual = false) {
        $query = "UPDATE " . $this->table_name . " 
                 SET payment_status = :payment_status";
        
        if ($manual) {
            $query .= ", payment_status_manual = 1";
        }
        
        $query .= " WHERE id = :order_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":payment_status", $payment_status);
        $stmt->bindParam(":order_id", $order_id);
        
        if ($stmt->execute()) {
            // Если оплачен, можем автоматически подтвердить заказ
            if ($payment_status == self::PAYMENT_PAID) {
                $order = $this->getOrderById($order_id);
                if ($order['status'] == self::STATUS_PENDING) {
                    $this->updateOrderStatus($order_id, self::STATUS_CONFIRMED, null);
                }
                
                // Отправляем уведомление менеджеру
                if ($this->telegram) {
                    $message = "✅ Заказ #{$order['order_number']} оплачен!\n";
                    $message .= "💰 Сумма: {$order['final_amount']} руб.\n";
                    $message .= "👤 Клиент: {$order['user_name']}\n";
                    $message .= "📱 Телефон: {$order['user_phone']}";
                    
                    $this->telegram->sendMessage($message);
                }
            }
            
            return true;
        }
        
        return false;
    }
    
    // Отправить уведомление о смене статуса
    private function sendStatusNotification($order_id, $new_status) {
        $order = $this->getOrderById($order_id);
        $status_text = $this->getStatusText($new_status);
        
        $notification_data = [
            'user_id' => $order['user_id'],
            'type' => 'order_status',
            'title' => "Статус заказа #{$order['order_number']} изменен",
            'message' => "Ваш заказ теперь в статусе: {$status_text}",
            'data' => json_encode(['order_id' => $order_id, 'status' => $new_status])
        ];
        
        // Здесь добавляем уведомление в БД
        $query = "INSERT INTO notifications (user_id, type, title, message, data) 
                 VALUES (:user_id, :type, :title, :message, :data)";
        
        $stmt = $this->conn->prepare($query);
        foreach ($notification_data as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
        
        $stmt->execute();
    }
    
    // Получить текст статуса
    private function getStatusText($status) {
        $statuses = [
            self::STATUS_DRAFT => 'Черновик',
            self::STATUS_PENDING => 'Ожидает подтверждения',
            self::STATUS_CONFIRMED => 'Подтвержден',
            self::STATUS_IN_PRODUCTION => 'В производстве',
            self::STATUS_READY => 'Готов',
            self::STATUS_DELIVERED => 'Доставлен',
            self::STATUS_CANCELLED => 'Отменен'
        ];
        
        return $statuses[$status] ?? $status;
    }
    
    // Получить текст статуса оплаты
    public function getPaymentStatusText($status) {
        $statuses = [
            self::PAYMENT_PENDING => 'Ожидает оплаты',
            self::PAYMENT_PAID => 'Оплачено',
            self::PAYMENT_PARTIALLY_PAID => 'Частично оплачено',
            self::PAYMENT_REFUNDED => 'Возвращено',
            self::PAYMENT_FAILED => 'Ошибка оплаты'
        ];
        
        return $statuses[$status] ?? $status;
    }
    
    // Получить статистику заказов
    public function getOrderStats($period = 'month') {
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
            default:
                $date_condition = "1=1";
        }
        
        // Общая статистика
        $query = "SELECT 
                    COUNT(*) as total_orders,
                    COUNT(CASE WHEN status = 'in_production' THEN 1 END) as in_production,
                    COUNT(CASE WHEN payment_status = 'pending' THEN 1 END) as awaiting_payment,
                    SUM(CASE WHEN payment_status = 'paid' THEN final_amount ELSE 0 END) as revenue
                 FROM " . $this->table_name . "
                 WHERE {$date_condition}";
        
        $stmt = $this->conn->query($query);
        $stats = $stmt->fetch();
        
        // Статистика по статусам
        $query = "SELECT status, COUNT(*) as count 
                 FROM " . $this->table_name . "
                 WHERE {$date_condition}
                 GROUP BY status";
        
        $stmt = $this->conn->query($query);
        $stats['by_status'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        return $stats;
    }
    
    // Генерация номера заказа
    public function generateOrderNumber() {
        $year = date('Y');
        $query = "SELECT MAX(CAST(SUBSTRING(order_number, 6) AS UNSIGNED)) as max_number 
                 FROM " . $this->table_name . " 
                 WHERE order_number LIKE :year_prefix";
        
        $year_prefix = $year . '-%';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":year_prefix", $year_prefix);
        $stmt->execute();
        
        $result = $stmt->fetch();
        $next_number = ($result['max_number'] ?? 0) + 1;
        
        return sprintf('%s-%04d', $year, $next_number);
    }
    
    // Вспомогательные методы
    private function getServiceById($service_id) {
        $query = "SELECT * FROM services WHERE id = :service_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":service_id", $service_id);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    private function getUserById($user_id) {
        $query = "SELECT * FROM users WHERE id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        return $stmt->fetch();
    }
}
?>