<?php
// admin/classes/Category.php

class Category {
    private $conn;
    private $table_name = "categories";

    public function __construct($db) {
        $this->conn = $db;
        $this->ensureTableExists();
    }

    // Проверка и создание таблицы если не существует
    private function ensureTableExists() {
        $query = "CREATE TABLE IF NOT EXISTS " . $this->table_name . " (
            id INT(11) NOT NULL AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL,
            slug VARCHAR(100) NOT NULL,
            description TEXT,
            icon VARCHAR(50) DEFAULT 'fa-folder',
            sort_order INT(11) DEFAULT 0,
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY slug (slug),
            KEY idx_is_active (is_active),
            KEY idx_sort_order (sort_order)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        try {
            $this->conn->exec($query);
        } catch (PDOException $e) {
            error_log("Error creating categories table: " . $e->getMessage());
        }
    }

    // Получить все категории
    public function getAll($filters = []) {
        $query = "SELECT c.*,
                        (SELECT COUNT(*) FROM services WHERE category = c.name) as services_count
                 FROM " . $this->table_name . " c
                 WHERE 1=1";

        if (isset($filters['is_active'])) {
            $query .= " AND c.is_active = :is_active";
        }

        if (!empty($filters['search'])) {
            $query .= " AND (c.name LIKE :search OR c.description LIKE :search)";
        }

        $query .= " ORDER BY c.sort_order ASC, c.name ASC";

        $stmt = $this->conn->prepare($query);

        if (isset($filters['is_active'])) {
            $stmt->bindParam(':is_active', $filters['is_active'], PDO::PARAM_INT);
        }

        if (!empty($filters['search'])) {
            $search = "%{$filters['search']}%";
            $stmt->bindParam(':search', $search);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Получить категорию по ID
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Создать категорию
    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . "
                 (name, slug, description, icon, sort_order, is_active)
                 VALUES (:name, :slug, :description, :icon, :sort_order, :is_active)";

        $stmt = $this->conn->prepare($query);

        // Генерируем slug если не указан
        $slug = !empty($data['slug']) ? $data['slug'] : $this->generateSlug($data['name']);

        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':slug', $slug);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':icon', $data['icon']);
        $stmt->bindParam(':sort_order', $data['sort_order'], PDO::PARAM_INT);
        $stmt->bindParam(':is_active', $data['is_active'], PDO::PARAM_INT);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }

        return false;
    }

    // Обновить категорию
    public function update($id, $data) {
        $query = "UPDATE " . $this->table_name . "
                 SET name = :name,
                     slug = :slug,
                     description = :description,
                     icon = :icon,
                     sort_order = :sort_order,
                     is_active = :is_active
                 WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Генерируем slug если не указан
        $slug = !empty($data['slug']) ? $data['slug'] : $this->generateSlug($data['name']);

        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':slug', $slug);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':icon', $data['icon']);
        $stmt->bindParam(':sort_order', $data['sort_order'], PDO::PARAM_INT);
        $stmt->bindParam(':is_active', $data['is_active'], PDO::PARAM_INT);

        return $stmt->execute();
    }

    // Удалить категорию
    public function delete($id) {
        // Проверяем, есть ли услуги с этой категорией
        $category = $this->getById($id);
        if (!$category) {
            return ['success' => false, 'error' => 'Категория не найдена'];
        }

        $query = "SELECT COUNT(*) as count FROM services WHERE category = :category_name";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':category_name', $category['name']);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result['count'] > 0) {
            return ['success' => false, 'error' => 'Невозможно удалить категорию, т.к. есть связанные услуги (' . $result['count'] . ')'];
        }

        // Удаляем категорию
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return ['success' => true];
        }

        return ['success' => false, 'error' => 'Ошибка при удалении'];
    }

    // Изменить порядок сортировки
    public function reorder($categories) {
        try {
            $this->conn->beginTransaction();

            foreach ($categories as $index => $categoryId) {
                $query = "UPDATE " . $this->table_name . " SET sort_order = :order WHERE id = :id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':order', $index, PDO::PARAM_INT);
                $stmt->bindParam(':id', $categoryId, PDO::PARAM_INT);
                $stmt->execute();
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    // Переключить статус активности
    public function toggleStatus($id) {
        $query = "UPDATE " . $this->table_name . "
                 SET is_active = NOT is_active
                 WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // Генерация slug из названия
    private function generateSlug($name) {
        $translit = [
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd',
            'е' => 'e', 'ё' => 'yo', 'ж' => 'zh', 'з' => 'z', 'и' => 'i',
            'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n',
            'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't',
            'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'ts', 'ч' => 'ch',
            'ш' => 'sh', 'щ' => 'sch', 'ъ' => '', 'ы' => 'y', 'ь' => '',
            'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
            'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D',
            'Е' => 'E', 'Ё' => 'Yo', 'Ж' => 'Zh', 'З' => 'Z', 'И' => 'I',
            'Й' => 'Y', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N',
            'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T',
            'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'Ts', 'Ч' => 'Ch',
            'Ш' => 'Sh', 'Щ' => 'Sch', 'Ъ' => '', 'Ы' => 'Y', 'Ь' => '',
            'Э' => 'E', 'Ю' => 'Yu', 'Я' => 'Ya'
        ];

        $slug = strtr($name, $translit);
        $slug = strtolower($slug);
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');

        return $slug;
    }

    // Получить статистику
    public function getStats() {
        $stats = [];

        // Всего категорий
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->query($query);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['total'] = $result['total'];

        // Активных
        $query = "SELECT COUNT(*) as active FROM " . $this->table_name . " WHERE is_active = 1";
        $stmt = $this->conn->query($query);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['active'] = $result['active'];

        // Неактивных
        $stats['inactive'] = $stats['total'] - $stats['active'];

        // Всего услуг по категориям
        $query = "SELECT COUNT(DISTINCT id) as total FROM services";
        $stmt = $this->conn->query($query);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['total_services'] = $result['total'];

        return $stats;
    }

    // Миграция существующих категорий из services
    public function migrateFromServices() {
        try {
            // Получаем уникальные категории из services
            $query = "SELECT DISTINCT category FROM services WHERE category IS NOT NULL AND category != ''";
            $stmt = $this->conn->query($query);
            $serviceCategories = $stmt->fetchAll(PDO::FETCH_COLUMN);

            $migrated = 0;
            foreach ($serviceCategories as $categoryName) {
                // Проверяем, существует ли уже такая категория
                $checkQuery = "SELECT id FROM " . $this->table_name . " WHERE name = :name";
                $checkStmt = $this->conn->prepare($checkQuery);
                $checkStmt->bindParam(':name', $categoryName);
                $checkStmt->execute();

                if ($checkStmt->fetch()) {
                    continue; // Категория уже существует
                }

                // Создаем категорию
                $data = [
                    'name' => $categoryName,
                    'slug' => $this->generateSlug($categoryName),
                    'description' => '',
                    'icon' => 'fa-folder',
                    'sort_order' => 0,
                    'is_active' => 1
                ];

                if ($this->create($data)) {
                    $migrated++;
                }
            }

            return ['success' => true, 'migrated' => $migrated];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
?>
