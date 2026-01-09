<?php
// admin/classes/Order.php (дополнение к существующему классу)
// Добавьте эти методы в класс Order после существующих методов

class Admin {
    private $conn;
    private $table_name = "admins";
    
    public $id;
    public $username;
    public $password_hash;
    public $full_name;
    public $email;
    public $role;
    public $is_active;
    public $last_login_at;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function login($username, $password) {
        $query = "SELECT id, username, password_hash, full_name, email, role, is_active 
                 FROM " . $this->table_name . " 
                 WHERE username = :username AND is_active = 1 
                 LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch();
            
            if (password_verify($password, $row['password_hash'])) {
                // Обновляем время последнего входа
                $update_query = "UPDATE " . $this->table_name . " 
                               SET last_login_at = NOW() 
                               WHERE id = :id";
                $update_stmt = $this->conn->prepare($update_query);
                $update_stmt->bindParam(":id", $row['id']);
                $update_stmt->execute();
                
                return $row;
            }
        }
        
        return false;
    }
    
    // Статический метод для проверки прав доступа
    public static function hasPermission($permission, $admin_id = null) {
        // Если admin_id не передан, берем из сессии
        if ($admin_id === null) {
            if (!isset($_SESSION['admin_id'])) {
                return false;
            }
            $admin_id = $_SESSION['admin_id'];
        }
        
        // Если это супер-админ, у него есть все права
        if (isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'super_admin') {
            return true;
        }
        
        // Для базовых ролей проверяем стандартные права
        $rolePermissions = [
            'super_admin' => ['*'], // Все права
            'manager' => [
                'view_orders', 'edit_orders', 'view_users', 'view_chats', 
                'send_messages', 'view_services', 'view_reports'
            ],
            'operator' => [
                'view_orders', 'view_chats', 'send_messages'
            ]
        ];
        
        $userRole = $_SESSION['admin_role'] ?? '';
        
        // Проверяем права роли
        if (isset($rolePermissions[$userRole])) {
            if (in_array('*', $rolePermissions[$userRole]) || 
                in_array($permission, $rolePermissions[$userRole])) {
                return true;
            }
        }
        
        // Если нужна проверка в БД для кастомных прав
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            $query = "SELECT ap.permission 
                     FROM admin_permissions ap 
                     WHERE ap.admin_id = :admin_id 
                     AND ap.permission = :permission
                     LIMIT 1";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(":admin_id", $admin_id);
            $stmt->bindParam(":permission", $permission);
            $stmt->execute();
            
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            // В случае ошибки возвращаем false
            return false;
        }
    }
    
    public function getAll() {
        $query = "SELECT id, username, full_name, email, role, is_active, last_login_at 
                 FROM " . $this->table_name . " 
                 ORDER BY id DESC";
        
        $stmt = $this->conn->query($query);
        return $stmt->fetchAll();
    }
    
    public function getById($id) {
        $query = "SELECT id, username, full_name, email, role, is_active 
                 FROM " . $this->table_name . " 
                 WHERE id = :id 
                 LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                 (username, password_hash, full_name, email, role, is_active) 
                 VALUES (:username, :password_hash, :full_name, :email, :role, :is_active)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":password_hash", $this->password_hash);
        $stmt->bindParam(":full_name", $this->full_name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":role", $this->role);
        $stmt->bindParam(":is_active", $this->is_active);
        
        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                 SET username = :username, 
                     full_name = :full_name, 
                     email = :email, 
                     role = :role, 
                     is_active = :is_active";
        
        if (!empty($this->password_hash)) {
            $query .= ", password_hash = :password_hash";
        }
        
        $query .= " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":full_name", $this->full_name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":role", $this->role);
        $stmt->bindParam(":is_active", $this->is_active);
        $stmt->bindParam(":id", $this->id);
        
        if (!empty($this->password_hash)) {
            $stmt->bindParam(":password_hash", $this->password_hash);
        }
        
        return $stmt->execute();
    }
    
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        
        return $stmt->execute();
    }
    
    // Получить роли администраторов
    public static function getRoles() {
        return [
            'super_admin' => 'Супер администратор',
            'manager' => 'Менеджер',
            'operator' => 'Оператор'
        ];
    }
    
    // Получить список прав доступа
    public static function getPermissions() {
        return [
            // Заказы
            'view_orders' => 'Просмотр заказов',
            'edit_orders' => 'Редактирование заказов',
            'delete_orders' => 'Удаление заказов',
            
            // Пользователи
            'view_users' => 'Просмотр пользователей',
            'edit_users' => 'Редактирование пользователей',
            'delete_users' => 'Удаление пользователей',
            
            // Чаты
            'view_chats' => 'Просмотр чатов',
            'send_messages' => 'Отправка сообщений',
            
            // Услуги
            'view_services' => 'Просмотр услуг',
            'edit_services' => 'Редактирование услуг',
            
            // Администраторы
            'manage_admins' => 'Управление администраторами',
            
            // Отчеты
            'view_reports' => 'Просмотр отчетов',
            
            // Настройки
            'manage_settings' => 'Управление настройками'
        ];
    }
}