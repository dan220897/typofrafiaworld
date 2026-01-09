<?php
// admin/classes/Settings.php

class Settings {
    private $conn;
    private $table_name = "settings";
    private static $cache = [];
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Получить значение настройки
    public function get($key, $default = null) {
        // Проверяем кэш
        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }
        
        $query = "SELECT setting_value, setting_type FROM " . $this->table_name . " 
                 WHERE setting_key = :key LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":key", $key);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch();
            $value = $this->castValue($row['setting_value'], $row['setting_type']);
            self::$cache[$key] = $value;
            return $value;
        }
        
        return $default;
    }
    
    // Установить значение настройки
    public function set($key, $value, $admin_id = null) {
        // Проверяем существование настройки
        $exists_query = "SELECT id, setting_type FROM " . $this->table_name . " 
                        WHERE setting_key = :key LIMIT 1";
        $exists_stmt = $this->conn->prepare($exists_query);
        $exists_stmt->bindParam(":key", $key);
        $exists_stmt->execute();
        
        if ($exists_stmt->rowCount() > 0) {
            // Обновляем существующую настройку
            $row = $exists_stmt->fetch();
            $type = $row['setting_type'];
            
            $query = "UPDATE " . $this->table_name . " 
                     SET setting_value = :value, 
                         updated_by = :admin_id,
                         updated_at = NOW() 
                     WHERE setting_key = :key";
        } else {
            // Создаем новую настройку
            $type = $this->detectType($value);
            
            $query = "INSERT INTO " . $this->table_name . " 
                     (setting_key, setting_value, setting_type, updated_by) 
                     VALUES (:key, :value, :type, :admin_id)";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":key", $key);
        $stmt->bindParam(":value", $this->prepareValue($value, $type));
        $stmt->bindParam(":admin_id", $admin_id);
        
        if (isset($type) && !$exists_stmt->rowCount()) {
            $stmt->bindParam(":type", $type);
        }
        
        $result = $stmt->execute();
        
        // Обновляем кэш
        if ($result) {
            self::$cache[$key] = $value;
        }
        
        return $result;
    }
    
    // Получить все настройки по категории
    public function getByCategory($category = null) {
        $query = "SELECT * FROM " . $this->table_name;
        
        if ($category) {
            $query .= " WHERE category = :category";
        }
        
        $query .= " ORDER BY category, setting_key";
        
        $stmt = $this->conn->prepare($query);
        
        if ($category) {
            $stmt->bindParam(":category", $category);
        }
        
        $stmt->execute();
        
        $settings = [];
        while ($row = $stmt->fetch()) {
            $settings[] = [
                'id' => $row['id'],
                'key' => $row['setting_key'],
                'value' => $this->castValue($row['setting_value'], $row['setting_type']),
                'type' => $row['setting_type'],
                'category' => $row['category'],
                'description' => $row['description'],
                'updated_at' => $row['updated_at']
            ];
        }
        
        return $settings;
    }
    
    // Получить все категории настроек
    public function getCategories() {
        $query = "SELECT DISTINCT category FROM " . $this->table_name . " 
                 ORDER BY category";
        
        $stmt = $this->conn->query($query);
        
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    // Массовое обновление настроек
    public function updateBatch($settings, $admin_id = null) {
        $this->conn->beginTransaction();
        
        try {
            foreach ($settings as $key => $value) {
                if (!$this->set($key, $value, $admin_id)) {
                    throw new Exception("Ошибка обновления настройки: {$key}");
                }
            }
            
            $this->conn->commit();
            return true;
            
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }
    
    // Приведение типов значений
    private function castValue($value, $type) {
        switch ($type) {
            case 'int':
                return (int)$value;
            case 'float':
                return (float)$value;
            case 'boolean':
                return (bool)$value;
            case 'json':
                return json_decode($value, true);
            default:
                return $value;
        }
    }
    
    // Подготовка значения для сохранения
    private function prepareValue($value, $type) {
        switch ($type) {
            case 'boolean':
                return $value ? '1' : '0';
            case 'json':
                return json_encode($value);
            default:
                return (string)$value;
        }
    }
    
    // Определение типа значения
    private function detectType($value) {
        if (is_bool($value)) {
            return 'boolean';
        } elseif (is_int($value)) {
            return 'int';
        } elseif (is_float($value)) {
            return 'float';
        } elseif (is_array($value) || is_object($value)) {
            return 'json';
        } else {
            return 'string';
        }
    }
    
    // Очистка кэша
    public static function clearCache() {
        self::$cache = [];
    }
    
    // Экспорт настроек
    public function export() {
        $query = "SELECT setting_key, setting_value, setting_type, category, description 
                 FROM " . $this->table_name . " 
                 ORDER BY category, setting_key";
        
        $stmt = $this->conn->query($query);
        
        return $stmt->fetchAll();
    }
    
    // Импорт настроек
    public function import($settings, $admin_id = null) {
        $this->conn->beginTransaction();
        
        try {
            foreach ($settings as $setting) {
                $query = "INSERT INTO " . $this->table_name . " 
                         (setting_key, setting_value, setting_type, category, description, updated_by) 
                         VALUES (:key, :value, :type, :category, :description, :admin_id)
                         ON DUPLICATE KEY UPDATE 
                         setting_value = VALUES(setting_value),
                         setting_type = VALUES(setting_type),
                         category = VALUES(category),
                         description = VALUES(description),
                         updated_by = VALUES(updated_by)";
                
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(":key", $setting['setting_key']);
                $stmt->bindParam(":value", $setting['setting_value']);
                $stmt->bindParam(":type", $setting['setting_type']);
                $stmt->bindParam(":category", $setting['category']);
                $stmt->bindParam(":description", $setting['description']);
                $stmt->bindParam(":admin_id", $admin_id);
                
                if (!$stmt->execute()) {
                    throw new Exception("Ошибка импорта настройки: {$setting['setting_key']}");
                }
            }
            
            $this->conn->commit();
            self::clearCache();
            return true;
            
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }
}